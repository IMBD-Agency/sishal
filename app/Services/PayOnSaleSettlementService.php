<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\PurchaseItem;
use App\Models\PurchaseBill;
use App\Models\SupplierPayment;
use App\Models\SupplierLedger;
use App\Models\Balance;
use App\Models\PosItem;
use App\Models\OrderItem;
use App\Models\SaleReturnItem;
use App\Models\OrderReturnItem;
use App\Models\PurchaseReturnItem;
use App\Models\FinancialAccount;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\ComboProduct;
use App\Models\PosExchangeItem;
use App\Models\PosExchange;
use App\Models\SaleReturn;
use App\Models\OrderReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PayOnSaleSettlementService
{
    /**
     * Get Pay-On-Sale (Consignment) summary for a supplier
     */
    public function getSupplierPayOnSaleSummary($supplierId, $startDate = null, $endDate = null, $search = null)
    {
        if (!$supplierId) {
            return [
                'items' => [],
                'totals' => [
                    'purchased_qty' => 0,
                    'sold_qty' => 0,
                    'in_stock_qty' => 0,
                    'sold_cost_payable' => 0,
                    'total_paid' => 0,
                    'net_due_payable' => 0,
                ]
            ];
        }

        // Fetch purchase items from this supplier (with bill for net_due calculation)
        $purchaseItemsQuery = PurchaseItem::with(['product', 'variation', 'purchase', 'purchase.bill'])
            ->whereHas('purchase', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });

        if ($endDate) {
            $endDateStr = is_string($endDate) ? $endDate : \Carbon\Carbon::parse($endDate)->format('Y-m-d');
            $purchaseItemsQuery->whereHas('purchase', function ($q) use ($endDateStr) {
                $q->where(function ($sq) use ($endDateStr) {
                    $sq->whereDate('purchase_date', '<=', $endDateStr)
                        ->orWhere(fn($ssq) => $ssq->whereNull('purchase_date')->whereDate('created_at', '<=', $endDateStr));
                });
            });
        }

        $purchaseItems = $purchaseItemsQuery->get();

        // Group purchase items by product_id and variation_id, preserving individual purchase items in FIFO order
        $grouped = [];

        foreach ($purchaseItems as $item) {
            $key = $item->product_id . '_' . ($item->variation_id ?? '0');

            if (!isset($grouped[$key])) {
                $displayName = $item->product?->name ?? 'Unknown Product';
                if ($item->variation && !empty($item->variation->variation_value)) {
                    $displayName .= ' (' . $item->variation->variation_value . ')';
                }

                $grouped[$key] = [
                    'product_id'     => $item->product_id,
                    'variation_id'   => $item->variation_id,
                    'product_name'   => $displayName,
                    'sku'            => $item->variation?->sku ?? $item->product?->sku ?? 'N/A',
                    'style_number'   => $item->product?->style_number ?? 'N/A',
                    'purchased_qty'  => 0,
                    'total_cost'     => 0,
                    'latest_unit_cost' => $item->unit_price,
                    'purchase_items' => [],
                ];
            }

            $grouped[$key]['purchased_qty'] += $item->quantity;
            $grouped[$key]['total_cost']    += ($item->quantity * $item->unit_price);
            $grouped[$key]['latest_unit_cost'] = $item->unit_price;
            $grouped[$key]['purchase_items'][] = $item;
        }

        // Apply Product / SKU / Style Search filter if provided
        if ($search && trim($search) !== '') {
            $sTerm = strtolower(trim($search));
            $grouped = array_filter($grouped, function ($data) use ($sTerm) {
                return str_contains(strtolower($data['product_name'] ?? ''), $sTerm)
                    || str_contains(strtolower($data['sku'] ?? ''), $sTerm)
                    || str_contains(strtolower($data['style_number'] ?? ''), $sTerm);
            });
        }

        // Pass 1: Compute FIFO sold allocation for every purchase item, and aggregate sold cost per bill
        $piAllocations = [];
        $billSoldCosts = [];

        foreach ($grouped as $key => $data) {
            $productId   = $data['product_id'];
            $variationId = $data['variation_id'];

            $returnedQuery = PurchaseReturnItem::where('product_id', $productId);
            if ($variationId) {
                $returnedQuery->where('variation_id', $variationId);
            }
            $returnedQty = (float) $returnedQuery->sum('returned_qty');
            $netPurchasedQty = max(0, $data['purchased_qty'] - $returnedQty);

            $rawSoldQty = $this->getProductNetSoldQuantity($productId, $variationId, $startDate, $endDate);
            $soldQty = min($rawSoldQty, $netPurchasedQty);

            $remSold = $soldQty;
            foreach ($data['purchase_items'] as $pi) {
                $piReturned = (float) PurchaseReturnItem::where('purchase_item_id', $pi->id)->sum('returned_qty');
                $piNetQty   = max(0, $pi->quantity - $piReturned);
                if ($piNetQty <= 0) continue;

                $allocSold = min($remSold, $piNetQty);
                $allocCost = round($allocSold * (float)$pi->unit_price, 2);
                $remSold -= $allocSold;

                $bill = $pi->purchase?->bill;
                $billId = $bill?->id;

                $piAllocations[$pi->id] = [
                    'alloc_sold' => $allocSold,
                    'alloc_cost' => $allocCost,
                    'bill'       => $bill,
                    'bill_id'    => $billId,
                ];

                if ($billId && $allocCost > 0) {
                    $billSoldCosts[$billId] = ($billSoldCosts[$billId] ?? 0) + $allocCost;
                }
            }
        }

        // Pass 2: Calculate AlreadyPaid and NetDue per product item and total summaries
        $items = [];
        $totalPurchasedQty    = 0;
        $totalSoldQty         = 0;
        $totalInStockQty      = 0;
        $totalSoldCostPayable = 0;
        $totalNetDuePayable   = 0;
        $totalAlreadyPaid     = 0;

        foreach ($grouped as $key => $data) {
            $productId   = $data['product_id'];
            $variationId = $data['variation_id'];

            $returnedQuery = PurchaseReturnItem::where('product_id', $productId);
            if ($variationId) {
                $returnedQuery->where('variation_id', $variationId);
            }
            $returnedQty = (float) $returnedQuery->sum('returned_qty');
            $netPurchasedQty = max(0, $data['purchased_qty'] - $returnedQty);

            $rawSoldQty = $this->getProductNetSoldQuantity($productId, $variationId, $startDate, $endDate);
            $soldQty = min($rawSoldQty, $netPurchasedQty);
            $inStockQty = max(0, $netPurchasedQty - $soldQty);

            $avgUnitCost = $data['purchased_qty'] > 0 ? ($data['total_cost'] / $data['purchased_qty']) : $data['latest_unit_cost'];

            $itemSoldCostPayable  = 0;
            $itemAlreadyPaid      = 0;
            $itemNetDue           = 0;

            foreach ($data['purchase_items'] as $pi) {
                $alloc = $piAllocations[$pi->id] ?? null;
                if (!$alloc || $alloc['alloc_cost'] <= 0) continue;

                $allocCost = $alloc['alloc_cost'];
                $bill      = $alloc['bill'];
                $billId    = $alloc['bill_id'];
                $billPaid  = $bill ? (float)$bill->paid_amount : 0;
                $billSoldCost = $billSoldCosts[$billId] ?? $allocCost;

                // Paid ratio for this bill: how much of the sold cost on this bill has been paid
                $paidRatio = $billSoldCost > 0 ? min(1.0, $billPaid / $billSoldCost) : 0;
                $allocPaid = round($allocCost * $paidRatio, 2);
                $allocNetDue = max(0, round($allocCost - $allocPaid, 2));

                $itemSoldCostPayable += $allocCost;
                $itemAlreadyPaid     += $allocPaid;
                $itemNetDue          += $allocNetDue;
            }

            $items[] = [
                'group_key'         => $key,
                'product_name'      => $data['product_name'],
                'sku'               => $data['sku'],
                'style_number'      => $data['style_number'],
                'purchased_qty'     => $netPurchasedQty,
                'sold_qty'          => $soldQty,
                'in_stock_qty'      => $inStockQty,
                'unit_cost'         => round($avgUnitCost, 2),
                'sold_cost_payable' => round($itemSoldCostPayable, 2),
                'already_paid'      => round($itemAlreadyPaid, 2),
                'net_due'           => round($itemNetDue, 2),
            ];

            $totalPurchasedQty    += $netPurchasedQty;
            $totalSoldQty         += $soldQty;
            $totalInStockQty      += $inStockQty;
            $totalSoldCostPayable += round($itemSoldCostPayable, 2);
            $totalAlreadyPaid     += round($itemAlreadyPaid, 2);
            $totalNetDuePayable   += round($itemNetDue, 2);
        }

        return [
            'items'  => $items,
            'totals' => [
                'purchased_qty'     => $totalPurchasedQty,
                'sold_qty'          => $totalSoldQty,
                'in_stock_qty'      => $totalInStockQty,
                'sold_cost_payable' => round($totalSoldCostPayable, 2),
                'total_paid'        => round($totalAlreadyPaid, 2),
                'net_due_payable'   => round($totalNetDuePayable, 2),
            ]
        ];
    }

    /**
     * Process Pay-on-Sale Settlement Payment
     * Allocates payment to the specific purchase bills that contain the sold products.
     * Each sold product's payment amount targets the bill of the purchase it came from.
     */
    public function processPayOnSaleSettlement($supplierId, $payAmount, $paymentDate, $paymentMethod, $accountId, $reference, $note, $userId)
    {
        return DB::transaction(function () use ($supplierId, $payAmount, $paymentDate, $paymentMethod, $accountId, $reference, $note, $userId) {
            $financialAccount = FinancialAccount::findOrFail($accountId);
            if ((float)$financialAccount->current_balance < (float)$payAmount) {
                $avail = number_format($financialAccount->current_balance, 2);
                $req = number_format($payAmount, 2);
                throw new \Exception("Insufficient balance in account '{$financialAccount->provider_name}'. Available: ৳{$avail}, Required: ৳{$req}");
            }
            $remainingPayment = (float) $payAmount;

            $createdPayments = [];
            $hasPaymentTypeColumn = Schema::hasColumn('supplier_payments', 'payment_type');

            // Group purchase items by product_id and variation_id (FIFO order)
            $purchaseItems = PurchaseItem::with(['purchase.bill', 'product', 'variation'])
                ->whereHas('purchase', fn($q) => $q->where('supplier_id', $supplierId))
                ->orderBy('id', 'asc')
                ->get();

            $productGroups = [];
            foreach ($purchaseItems as $pItem) {
                $key = $pItem->product_id . '_' . ($pItem->variation_id ?? '0');
                if (!isset($productGroups[$key])) {
                    $productGroups[$key] = [
                        'product_id'   => $pItem->product_id,
                        'variation_id' => $pItem->variation_id,
                        'items'        => [],
                    ];
                }
                $productGroups[$key]['items'][] = $pItem;
            }

            $billSoldCosts = [];
            $billPayableMap = [];

            // 1. Calculate sold cost per bill across products (FIFO)
            foreach ($productGroups as $group) {
                $productId   = $group['product_id'];
                $variationId = $group['variation_id'];

                $remSold = $this->getProductNetSoldQuantity($productId, $variationId);

                foreach ($group['items'] as $pi) {
                    if ($remSold <= 0) break;

                    $piReturned = (float) PurchaseReturnItem::where('purchase_item_id', $pi->id)->sum('returned_qty');
                    $piNetQty   = max(0, $pi->quantity - $piReturned);
                    if ($piNetQty <= 0) continue;

                    $allocSold = min($remSold, $piNetQty);
                    $allocCost = round($allocSold * (float)$pi->unit_price, 2);
                    $remSold -= $allocSold;

                    $bill = $pi->purchase?->bill;
                    $billId = $bill?->id;
                    if ($billId && $allocCost > 0) {
                        if (!isset($billSoldCosts[$billId])) {
                            $billSoldCosts[$billId] = [
                                'bill' => $bill,
                                'sold_cost' => 0.0,
                            ];
                        }
                        $billSoldCosts[$billId]['sold_cost'] += $allocCost;
                    }
                }
            }

            // 2. Build bill payable map: payable for sold items = max(0, sold_cost - bill->paid_amount)
            foreach ($billSoldCosts as $billId => $entry) {
                $bill     = $entry['bill'];
                $billPaid = (float) $bill->paid_amount;
                $billDue  = (float) $bill->due_amount;
                $soldDue  = max(0, round($entry['sold_cost'] - $billPaid, 2));
                $payable  = min($soldDue, $billDue);

                if ($payable > 0.009) {
                    $billPayableMap[$billId] = [
                        'bill'    => $bill,
                        'payable' => $payable,
                    ];
                }
            }

            // 3. FIFO Order: Oldest purchase bill first
            ksort($billPayableMap);

            // 4. Distribute payment to bills with sold-product payable amounts
            if (!empty($billPayableMap)) {
                foreach ($billPayableMap as $billId => $entry) {
                    if ($remainingPayment <= 0.009) break;

                    // Refresh bill from DB to get the latest due_amount
                    $bill    = PurchaseBill::find($billId);
                    if (!$bill || (float) $bill->due_amount <= 0.009) continue;
                    $billDue = (float) $bill->due_amount;

                    $allocAmount = min($remainingPayment, $billDue, $entry['payable']);
                    $allocAmount = round($allocAmount, 2);
                    if ($allocAmount <= 0.009) continue;

                    $paymentData = [
                        'supplier_id'      => $supplierId,
                        'purchase_bill_id' => $bill->id,
                        'amount'           => $allocAmount,
                        'payment_date'     => $paymentDate,
                        'payment_method'   => $paymentMethod,
                        'account_id'       => $accountId,
                        'reference'        => $reference,
                        'note'             => $note
                            ? $note . " (Pay-on-Sale for Bill #{$bill->bill_number})"
                            : "Pay-on-Sale Settlement for Bill #{$bill->bill_number}",
                        'created_by'       => $userId,
                    ];

                    if ($hasPaymentTypeColumn) {
                        $paymentData['payment_type'] = 'pay_on_sale';
                    }

                    $payment = SupplierPayment::create($paymentData);

                    // Update PurchaseBill due/paid/status
                    $bill->paid_amount += $allocAmount;
                    $bill->due_amount  -= $allocAmount;
                    if ($bill->due_amount <= 0) {
                        $bill->status     = 'paid';
                        $bill->due_amount = 0;
                    } elseif ($bill->paid_amount > 0) {
                        $bill->status = 'partial';
                    }
                    $bill->save();

                    $this->recordPaymentAccountingDetails($payment, $financialAccount, $supplierId, $allocAmount, $paymentDate, $reference, $userId);
                    $createdPayments[] = $payment;

                    $remainingPayment -= $allocAmount;
                }
            }

            // 5. Any remainder goes to any remaining due bills for this supplier
            if (round($remainingPayment, 2) > 0.009) {
                $fallbackBills = PurchaseBill::where('supplier_id', $supplierId)
                    ->where('due_amount', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($fallbackBills as $bill) {
                    if ($remainingPayment <= 0.009) break;
                    $allocAmount = round(min($remainingPayment, (float) $bill->due_amount), 2);
                    if ($allocAmount <= 0.009) continue;

                    $paymentData = [
                        'supplier_id'      => $supplierId,
                        'purchase_bill_id' => $bill->id,
                        'amount'           => $allocAmount,
                        'payment_date'     => $paymentDate,
                        'payment_method'   => $paymentMethod,
                        'account_id'       => $accountId,
                        'reference'        => $reference,
                        'note'             => ($note ? $note . ' ' : '') . "(Pay-on-Sale Remainder for Bill #{$bill->bill_number})",
                        'created_by'       => $userId,
                    ];
                    if ($hasPaymentTypeColumn) {
                        $paymentData['payment_type'] = 'pay_on_sale';
                    }

                    $payment = SupplierPayment::create($paymentData);
                    $bill->paid_amount += $allocAmount;
                    $bill->due_amount  -= $allocAmount;
                    $bill->status       = $bill->due_amount <= 0 ? 'paid' : 'partial';
                    if ($bill->due_amount < 0) $bill->due_amount = 0;
                    $bill->save();

                    $this->recordPaymentAccountingDetails($payment, $financialAccount, $supplierId, $allocAmount, $paymentDate, $reference, $userId);
                    $createdPayments[] = $payment;
                    $remainingPayment -= $allocAmount;
                }
            }

            return $createdPayments;
        });
    }

    /**
     * Record Ledger, Balance, and Auto Journal entries for supplier payment
     */
    private function recordPaymentAccountingDetails($payment, $financialAccount, $supplierId, $amount, $paymentDate, $reference, $userId)
    {
        $supplier = Supplier::find($supplierId);
        $description = "Pay-on-Sale Settlement Payment #" . $payment->id . " to " . ($supplier?->name ?? 'Supplier');

        // 1. Supplier Ledger
        SupplierLedger::recordTransaction(
            $supplierId,
            'debit',
            $amount,
            $description,
            $paymentDate,
            $payment
        );

        // 2. Supplier Balance
        $balance = Balance::where('source_type', 'supplier')->where('source_id', $supplierId)->first();
        if ($balance) {
            $balance->balance -= $amount;
            $balance->save();
        } else {
            Balance::create([
                'source_type' => 'supplier',
                'source_id' => $supplierId,
                'balance' => -$amount,
                'description' => 'Supplier Payment',
            ]);
        }

        // 3. Financial Account Balance
        $financialAccount->balance -= $amount;
        $financialAccount->save();

        // 4. Auto Journal Entry
        $paymentChartAccountId = $financialAccount->account_id;
        $payableChartAccount = ChartOfAccount::where('name', 'like', '%payable%')
            ->orWhere('name', 'like', '%creditor%')
            ->first();

        if ($paymentChartAccountId && $payableChartAccount) {
            $voucherNo = 'POS-PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
            while (Journal::where('voucher_no', $voucherNo)->exists()) {
                $voucherNo = 'POS-PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '-' . rand(10, 99);
            }

            $journal = Journal::create([
                'voucher_no'     => $voucherNo,
                'entry_date'     => $paymentDate,
                'type'           => 'Payment',
                'description'    => 'Auto: Pay-on-Sale Settlement #' . $payment->id . ' to ' . ($supplier?->name ?? 'Supplier'),
                'supplier_id'    => $supplierId,
                'branch_id'      => null,
                'voucher_amount' => $amount,
                'paid_amount'    => $amount,
                'reference'      => $reference,
                'created_by'     => $userId,
                'updated_by'     => $userId,
            ]);

            // DEBIT: Accounts Payable
            JournalEntry::create([
                'journal_id'           => $journal->id,
                'chart_of_account_id'  => $payableChartAccount->id,
                'financial_account_id' => null,
                'debit'                => $amount,
                'credit'               => 0,
                'memo'                 => 'Pay-on-Sale Settlement to ' . ($supplier?->name ?? 'Supplier'),
                'created_by'           => $userId,
                'updated_by'           => $userId,
            ]);

            // CREDIT: Bank/Cash
            JournalEntry::create([
                'journal_id'           => $journal->id,
                'chart_of_account_id'  => $paymentChartAccountId,
                'financial_account_id' => $financialAccount->id,
                'debit'                => 0,
                'credit'               => $amount,
                'memo'                 => 'Payment via ' . $financialAccount->provider_name,
                'created_by'           => $userId,
                'updated_by'           => $userId,
            ]);
        }
    }

    /**
     * Calculate net sold quantity for a product / variation across POS, Online Orders, Combos, Exchanges, and Returns.
     *
     * @param int $productId
     * @param int|null $variationId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getProductNetSoldQuantity($productId, $variationId = null, $startDate = null, $endDate = null)
    {
        $startDateStr = $startDate ? (\Carbon\Carbon::parse($startDate)->format('Y-m-d')) : null;
        $endDateStr   = $endDate ? (\Carbon\Carbon::parse($endDate)->format('Y-m-d')) : null;

        $product = Product::find($productId);
        $isCombo = $product && $product->type === 'combo';

        // -------------------------------------------------------------
        // 1. POS SALES
        // -------------------------------------------------------------
        // A. Direct POS items (Single items or combo child items or combo parent rows)
        $posDirectQuery = PosItem::where('product_id', $productId)
            ->whereHas('pos', function ($q) use ($startDateStr, $endDateStr) {
                $q->where('status', '!=', 'cancelled');
                if ($startDateStr) $q->whereDate('sale_date', '>=', $startDateStr);
                if ($endDateStr)   $q->whereDate('sale_date', '<=', $endDateStr);
            });

        if ($variationId) {
            $posDirectQuery->where('variation_id', $variationId);
        }

        if ($isCombo) {
            // For a combo product itself, only count parent rows to avoid double counting
            $posDirectQuery->whereNull('parent_item_id');
        }

        $posDirectQty = (float) $posDirectQuery->sum('quantity');

        // B. Extra combo sales in POS where child rows were NOT created in pos_items
        $comboPosExtraQty = 0;
        if (!$isCombo) {
            $comboMappings = ComboProduct::where('product_id', $productId);
            if ($variationId) {
                $comboMappings->where(function ($q) use ($variationId) {
                    $q->where('variation_id', $variationId)->orWhereNull('variation_id');
                });
            }
            $comboMappings = $comboMappings->get();

            foreach ($comboMappings as $cm) {
                $comboParentItems = PosItem::where('product_id', $cm->combo_product_id)
                    ->whereNull('parent_item_id')
                    ->whereDoesntHave('childItems')
                    ->whereHas('pos', function ($q) use ($startDateStr, $endDateStr) {
                        $q->where('status', '!=', 'cancelled');
                        if ($startDateStr) $q->whereDate('sale_date', '>=', $startDateStr);
                        if ($endDateStr)   $q->whereDate('sale_date', '<=', $endDateStr);
                    })
                    ->sum('quantity');

                $comboPosExtraQty += ((float) $comboParentItems * (float) $cm->quantity);
            }
        }

        // C. POS Exchanges (New items handed out = Sold, Returned items handed in = Returned)
        $posExchangeNewQuery = PosExchangeItem::where('product_id', $productId)
            ->where('type', 'new')
            ->whereHas('exchange', function ($q) use ($startDateStr, $endDateStr) {
                $q->where('status', 'completed');
                if ($startDateStr) $q->whereDate('exchange_date', '>=', $startDateStr);
                if ($endDateStr)   $q->whereDate('exchange_date', '<=', $endDateStr);
            });
        if ($variationId) {
            $posExchangeNewQuery->where('variation_id', $variationId);
        }
        $posExchangeNewQty = (float) $posExchangeNewQuery->sum('quantity');

        $posExchangeRetQuery = PosExchangeItem::where('product_id', $productId)
            ->where('type', 'returned')
            ->whereHas('exchange', function ($q) use ($startDateStr, $endDateStr) {
                $q->where('status', 'completed');
                if ($startDateStr) $q->whereDate('exchange_date', '>=', $startDateStr);
                if ($endDateStr)   $q->whereDate('exchange_date', '<=', $endDateStr);
            });
        if ($variationId) {
            $posExchangeRetQuery->where('variation_id', $variationId);
        }
        $posExchangeRetQty = (float) $posExchangeRetQuery->sum('quantity');

        // D. POS Sale Returns (exclude returns created from exchanges to avoid double counting)
        $posReturnQuery = SaleReturnItem::where('product_id', $productId)
            ->whereHas('saleReturn', function ($q) use ($startDateStr, $endDateStr) {
                $q->where('status', '!=', 'rejected')
                  ->where('refund_type', '!=', 'exchange'); // Exclude exchange returns
                if ($startDateStr) $q->whereDate('return_date', '>=', $startDateStr);
                if ($endDateStr)   $q->whereDate('return_date', '<=', $endDateStr);
            });
        if ($variationId) {
            $posReturnQuery->where('variation_id', $variationId);
        }
        $posReturnQty = (float) $posReturnQuery->sum('returned_qty');

        $netPosSoldQty = max(0, ($posDirectQty + $comboPosExtraQty + $posExchangeNewQty) - ($posReturnQty + $posExchangeRetQty));

        // -------------------------------------------------------------
        // 2. ONLINE ORDERS
        // -------------------------------------------------------------
        // A. Direct Order items (single items or combo child items)
        $onlineDirectQuery = OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) use ($startDateStr, $endDateStr) {
                $q->where('status', '!=', 'cancelled');
                if ($startDateStr) $q->whereDate('created_at', '>=', $startDateStr);
                if ($endDateStr)   $q->whereDate('created_at', '<=', $endDateStr);
            });

        if ($variationId) {
            $onlineDirectQuery->where('variation_id', $variationId);
        }

        if ($isCombo) {
            $onlineDirectQuery->whereNull('parent_item_id');
        }

        $onlineDirectQty = (float) $onlineDirectQuery->sum('quantity');

        // B. Online combo orders where child rows were NOT created in order_items (e.g. standard ecommerce orders)
        $comboOnlineExtraQty = 0;
        if (!$isCombo) {
            $comboMappings = ComboProduct::where('product_id', $productId);
            if ($variationId) {
                $comboMappings->where(function ($q) use ($variationId) {
                    $q->where('variation_id', $variationId)->orWhereNull('variation_id');
                });
            }
            $comboMappings = $comboMappings->get();

            foreach ($comboMappings as $cm) {
                $comboOrderItems = OrderItem::where('product_id', $cm->combo_product_id)
                    ->whereNull('parent_item_id')
                    ->whereDoesntHave('childItems')
                    ->whereHas('order', function ($q) use ($startDateStr, $endDateStr) {
                        $q->where('status', '!=', 'cancelled');
                        if ($startDateStr) $q->whereDate('created_at', '>=', $startDateStr);
                        if ($endDateStr)   $q->whereDate('created_at', '<=', $endDateStr);
                    })
                    ->sum('quantity');

                $comboOnlineExtraQty += ((float) $comboOrderItems * (float) $cm->quantity);
            }
        }

        // C. Online Order Returns
        $orderReturnQuery = OrderReturnItem::where('product_id', $productId)
            ->whereHas('orderReturn', function ($q) use ($startDateStr, $endDateStr) {
                $q->where('status', '!=', 'rejected');
                if ($startDateStr) $q->whereDate('return_date', '>=', $startDateStr);
                if ($endDateStr)   $q->whereDate('return_date', '<=', $endDateStr);
            });
        if ($variationId) {
            $orderReturnQuery->where('variation_id', $variationId);
        }
        $orderReturnQty = (float) $orderReturnQuery->sum('returned_qty');

        $netOnlineSoldQty = max(0, ($onlineDirectQty + $comboOnlineExtraQty) - $orderReturnQty);

        return $netPosSoldQty + $netOnlineSoldQty;
    }
}
