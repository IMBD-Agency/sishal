<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Pos;
use App\Models\PosItem;
use App\Models\PosExchange;
use App\Models\SaleReturn;
use App\Models\OrderReturn;
use App\Models\InvoiceItem;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CashProfitService
{
    /**
     * Calculate Cash Profit data for a date range and optional branch.
     * Matches exact cash profit report logic (collections × margins).
     */
    public function calculateCashProfitData($startDate, $endDate, $branchId = null): array
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $paymentsQuery = Payment::with(['pos', 'invoice.pos', 'account'])
            ->whereBetween('payment_date', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->whereIn('payment_for', ['invoice', 'pos', 'pos_sale', 'advance_payment', 'customer_due', 'manual_receipt']);

        if ($branchId && $branchId !== 'all' && is_numeric($branchId)) {
            $paymentsQuery->where(function ($q) use ($branchId) {
                $q->whereHas('pos', function ($pq) use ($branchId) { $pq->where('branch_id', (int)$branchId); })
                  ->orWhereHas('invoice.pos', function ($ipq) use ($branchId) { $ipq->where('branch_id', (int)$branchId); });
            });
        }
        $payments = $paymentsQuery->get();

        $returnsQuery = SaleReturn::with(['posSale', 'invoice.pos'])
            ->whereBetween('return_date', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->whereIn('status', ['completed', 'approved', 'processed']);

        if ($branchId && $branchId !== 'all' && is_numeric($branchId)) {
            $returnsQuery->where(function ($q) use ($branchId) {
                $q->whereHas('posSale', function ($pq) use ($branchId) { $pq->where('branch_id', (int)$branchId); })
                  ->orWhereHas('invoice.pos', function ($ipq) use ($branchId) { $ipq->where('branch_id', (int)$branchId); });
            });
        }
        $returns = $returnsQuery->get();

        $exchangesQuery = PosExchange::with(['originalPos', 'account'])
            ->whereBetween('exchange_date', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->where('status', 'completed');

        if ($branchId && $branchId !== 'all' && is_numeric($branchId)) {
            $exchangesQuery->where('branch_id', (int)$branchId);
        }
        $exchanges = $exchangesQuery->get();

        $activeTransactions = [];
        $addActiveTransaction = function ($type, $model, $date, $txBranchId, $ref = '') use (&$activeTransactions) {
            if (!$model) return;
            $key = "{$type}-{$model->id}";
            if (!isset($activeTransactions[$key])) {
                $activeTransactions[$key] = [
                    'type' => $type,
                    'model' => $model,
                    'branch_id' => $txBranchId,
                    'reference' => $ref,
                    'latest_date' => $date,
                    'events' => []
                ];
            } else {
                if ($date > $activeTransactions[$key]['latest_date']) {
                    $activeTransactions[$key]['latest_date'] = $date;
                }
            }
        };

        foreach ($payments as $payment) {
            if ($payment->pos) {
                $ref = $payment->pos->invoice_number ?? ('POS-' . $payment->pos->id);
                $addActiveTransaction('pos', $payment->pos, $payment->payment_date, $payment->pos->branch_id, $ref);
                $key = "pos-{$payment->pos->id}";
                $activeTransactions[$key]['events'][] = [
                    'date' => $payment->payment_date,
                    'ym_code' => Carbon::parse($payment->payment_date)->format('Y-m'),
                    'type' => 'payment',
                    'amount' => (float)$payment->amount
                ];
            } elseif ($payment->invoice) {
                $invoice = $payment->invoice;
                $bId = $invoice->pos ? $invoice->pos->branch_id : ($invoice->branch_id ?? null);
                $ref = $invoice->invoice_number ?? ('INV-' . $invoice->id);
                if ($invoice->pos) {
                    $addActiveTransaction('pos', $invoice->pos, $payment->payment_date, $bId, $ref);
                    $key = "pos-{$invoice->pos->id}";
                } elseif ($invoice->order) {
                    $addActiveTransaction('order', $invoice->order, $payment->payment_date, $bId, $ref);
                    $key = "order-{$invoice->order->id}";
                } else {
                    $addActiveTransaction('invoice', $invoice, $payment->payment_date, $bId, $ref);
                    $key = "invoice-{$invoice->id}";
                }
                $activeTransactions[$key]['events'][] = [
                    'date' => $payment->payment_date,
                    'ym_code' => Carbon::parse($payment->payment_date)->format('Y-m'),
                    'type' => 'payment',
                    'amount' => (float)$payment->amount
                ];
            }
        }

        foreach ($returns as $return) {
            if ($return->posSale) {
                $bId = $return->return_to_type === 'branch' ? $return->return_to_id : ($return->posSale->branch_id ?? $return->return_to_id);
                $addActiveTransaction('pos', $return->posSale, $return->return_date, $bId, $return->posSale->invoice_number ?? ('POS-' . $return->posSale->id));
            } elseif ($return->invoice) {
                $invoice = $return->invoice;
                $bId = $invoice->pos ? $invoice->pos->branch_id : ($invoice->branch_id ?? null);
                $ref = $invoice->invoice_number ?? ('INV-' . $invoice->id);
                if ($invoice->pos) {
                    $addActiveTransaction('pos', $invoice->pos, $return->return_date, $bId, $ref);
                } elseif ($invoice->order) {
                    $addActiveTransaction('order', $invoice->order, $return->return_date, $bId, $ref);
                } else {
                    $addActiveTransaction('invoice', $invoice, $return->return_date, $bId, $ref);
                }
            }
        }

        foreach ($exchanges as $exchange) {
            if ($exchange->originalPos) {
                $addActiveTransaction('pos', $exchange->originalPos, $exchange->exchange_date, $exchange->branch_id ?? $exchange->originalPos->branch_id, $exchange->originalPos->invoice_number ?? ('POS-' . $exchange->originalPos->id));
            }
        }

        $branchProfits = [];
        $monthlyProfits = [];
        $transactionRows = collect();

        $totalCollected = 0;
        $totalCashProfit = 0;
        $totalGrossPayments = 0;
        $totalReturnRefunds = 0;
        $totalExchangeRefunds = 0;
        $totalDeliveryCollected = 0;
        $totalVatCollected = 0;
        $totalProductCollected = 0;

        foreach ($activeTransactions as $tx) {
            $type = $tx['type'];
            $model = $tx['model'];
            $txBranchId = $tx['branch_id'] ?? 0;

            $priorInfo = $this->getTransactionMarginAt($type, $model, $startDate, true);
            $currentInfo = $this->getTransactionMarginAt($type, $model, $endDate, false);

            $paymentsQuery = Payment::where(function ($q) use ($type, $model) {
                if ($type === 'pos') {
                    $q->where('pos_id', $model->id);
                    if ($model->invoice_id) {
                        $q->orWhere('invoice_id', $model->invoice_id);
                    }
                } else {
                    $q->where('invoice_id', $model->invoice_id ?? $model->id);
                }
            });

            $priorPayments = (float)(clone $paymentsQuery)->where('payment_date', '<', $startDate)->sum('amount');
            $currentPayments = (float)(clone $paymentsQuery)->whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

            if ($type === 'pos') {
                $model = $model->fresh() ?? $model;
                $vatRate = ($model->vat_rate > 0) ? ($model->vat_rate / 100) : ((($model->sub_total - $model->discount) > 0) ? ($model->vat_amount / ($model->sub_total - $model->discount)) : 0);
                $returnsQuery = SaleReturn::where('pos_sale_id', $model->id);
            } elseif ($type === 'order') {
                $model = $model->fresh() ?? $model;
                $vatRate = 0;
                $returnsQuery = OrderReturn::where('order_id', $model->id);
            } else {
                $model = $model->fresh() ?? $model;
                $sub = floatval($model->subtotal ?? $model->total_amount ?? 0);
                $vatRate = ($sub > 0 && ($model->tax ?? 0) > 0) ? (floatval($model->tax) / $sub) : 0;
                $returnsQuery = SaleReturn::where('invoice_id', $model->invoice_id ?? $model->id);
            }

            $calcRefundAmount = function ($retList) use ($vatRate) {
                return $retList->sum(function ($r) use ($vatRate) {
                    if (!in_array($r->refund_type, ['cash', 'bank', 'mobile'])) {
                        return 0;
                    }
                    $baseAmt = $r->items->sum('total_price');
                    $retVat = round($baseAmt * $vatRate, 2);
                    return $baseAmt + $retVat;
                });
            };

            $priorReturns = (clone $returnsQuery)->whereIn('status', ['completed', 'approved', 'processed'])->where('return_date', '<', $startDate)->get();
            $priorRefunds = (float)$calcRefundAmount($priorReturns);

            $currentReturns = (clone $returnsQuery)->whereIn('status', ['completed', 'approved', 'processed'])->whereBetween('return_date', [$startDate, $endDate])->get();
            $currentRefunds = (float)$calcRefundAmount($currentReturns);

            $allCompletedReturns = (clone $returnsQuery)->whereIn('status', ['completed', 'approved', 'processed'])->get();
            $regularReturnVatTotal = 0;
            foreach ($allCompletedReturns as $ret) {
                if (($ret->refund_type ?? 'none') !== 'exchange') {
                    $baseAmt = $ret->items->sum('total_price');
                    $regularReturnVatTotal += round($baseAmt * $vatRate, 2);
                }
            }

            $priorExchangeRefunds = 0;
            $currentExchangeRefunds = 0;
            if ($type === 'pos') {
                $priorExchangeRefunds = (float)PosExchange::where('original_pos_id', $model->id)
                    ->where('status', 'completed')
                    ->where('exchange_date', '<', $startDate)
                    ->sum('refund_amount');
                    
                $currentExchangeRefunds = (float)PosExchange::where('original_pos_id', $model->id)
                    ->where('status', 'completed')
                    ->whereBetween('exchange_date', [$startDate, $endDate])
                    ->sum('refund_amount');
            }

            $priorNetCash = $priorPayments - $priorRefunds - $priorExchangeRefunds;
            $netCollectionForTx = $currentPayments - $currentRefunds - $currentExchangeRefunds;

            $deliveryAmount = floatval($type === 'pos' ? ($model->delivery ?? 0) : (($model->order ?? null) ? $model->order->delivery : 0));
            $rawVatAmount = floatval($type === 'pos' ? ($model->vat_amount ?? 0) : (($model->invoice ?? null) ? ($model->invoice->tax ?? 0) : 0));
            $vatAmount = max(0, $rawVatAmount - $regularReturnVatTotal);
            $nonProductAmount = $deliveryAmount + $vatAmount;

            $cumulativeNetCashEnd = $priorNetCash + $netCollectionForTx;
            $cumulativeProductCashEnd = min($currentInfo['revenue'], max(0, $cumulativeNetCashEnd));
            $cumulativeProductCashStart = min($priorInfo['revenue'], max(0, $priorNetCash));
            
            $currentNetProductCollection = max(0, $cumulativeProductCashEnd - $cumulativeProductCashStart);
            
            $cashProfitOnNetCollection = $currentNetProductCollection * $currentInfo['margin'];
            $priorCashAdjustment = $cumulativeProductCashStart * ($currentInfo['margin'] - $priorInfo['margin']);

            $txCashProfit = $cashProfitOnNetCollection + $priorCashAdjustment;
            $txEstimatedCogs = max(0, $currentNetProductCollection - $txCashProfit);

            // Branch aggregation
            if (!isset($branchProfits[$txBranchId])) {
                $branchProfits[$txBranchId] = [
                    'collection' => 0,
                    'cash_profit' => 0,
                    'estimated_cogs' => 0
                ];
            }
            $branchProfits[$txBranchId]['collection'] += $currentNetProductCollection;
            $branchProfits[$txBranchId]['cash_profit'] += $txCashProfit;
            $branchProfits[$txBranchId]['estimated_cogs'] += $txEstimatedCogs;

            // Monthly aggregation
            foreach ($tx['events'] as $ev) {
                $ym = $ev['ym_code'];
                if (!isset($monthlyProfits[$ym])) {
                    $monthlyProfits[$ym] = [
                        'collection' => 0,
                        'cash_profit' => 0,
                        'cogs' => 0
                    ];
                }
                $evAmt = $ev['amount'];
                $prodRatio = $currentPayments > 0 ? ($currentNetProductCollection / $currentPayments) : 1;
                $evProdAmt = $evAmt * $prodRatio;
                $evProfit = $evProdAmt * $currentInfo['margin'];
                $evCogs = max(0, $evProdAmt - $evProfit);

                $monthlyProfits[$ym]['collection'] += $evProdAmt;
                $monthlyProfits[$ym]['cash_profit'] += $evProfit;
                $monthlyProfits[$ym]['cogs'] += $evCogs;
            }

            // Totals for detailed reports
            $totalGrossPayments += $currentPayments;
            $totalReturnRefunds += $currentRefunds;
            $totalDeliveryCollected += min($deliveryAmount, max(0, $netCollectionForTx - $currentNetProductCollection));
            $totalVatCollected += min($vatAmount, max(0, $netCollectionForTx - $currentNetProductCollection - $deliveryAmount));
            $totalProductCollected += $currentNetProductCollection;
            $totalCollected += $netCollectionForTx;
            $totalCashProfit += $txCashProfit;

            $transactionRows->push((object)[
                'date' => $tx['latest_date'],
                'reference' => $tx['reference'],
                'collection_amount' => $netCollectionForTx,
                'sale_amount' => $currentInfo['sale_amount'],
                'invoice_profit' => $currentInfo['revenue'] - $currentInfo['cost'],
                'profit_margin' => $currentInfo['margin'] * 100,
                'estimated_cost' => $txEstimatedCogs,
                'cash_profit' => $txCashProfit
            ]);
        }

        $totalEstimatedCost = max(0, $totalProductCollected - $totalCashProfit);

        return [
            'branch_profit' => $branchProfits,
            'monthly_profit' => $monthlyProfits,
            'transaction_rows' => $transactionRows->sortByDesc('date'),
            'total_collected' => $totalCollected,
            'total_cash_profit' => $totalCashProfit,
            'total_estimated_cost' => $totalEstimatedCost,
            'total_gross_payments' => $totalGrossPayments,
            'total_return_refunds' => $totalReturnRefunds,
            'total_exchange_refunds' => $totalExchangeRefunds,
            'total_delivery_collected' => $totalDeliveryCollected,
            'total_vat_collected' => $totalVatCollected,
            'total_product_collected' => $totalProductCollected,
        ];
    }

    /**
     * Get transaction profit margin at dateLimit.
     */
    public function getTransactionMarginAt($type, $model, $dateLimit, $isBefore): array
    {
        $operator = $isBefore ? '<' : '<=';
        
        if ($type === 'pos') {
            $returnsQuery = SaleReturn::where('pos_sale_id', $model->id);
        } elseif ($type === 'order') {
            $returnsQuery = OrderReturn::where('order_id', $model->id);
        } else {
            $returnsQuery = SaleReturn::where('invoice_id', $model->invoice_id ?? $model->id);
        }
        $returns = $returnsQuery->whereIn('status', ['completed', 'approved', 'processed'])
            ->where('return_date', $operator, $dateLimit)
            ->with('items')
            ->get();
            
        $returnedQtyMap = [];
        foreach ($returns as $ret) {
            foreach ($ret->items as $rItem) {
                $itemId = $rItem->sale_item_id ?? $rItem->order_item_id ?? $rItem->id;
                $returnedQtyMap[$itemId] = ($returnedQtyMap[$itemId] ?? 0) + $rItem->returned_qty;
            }
        }
        
        $activeOriginalRevenue = 0;
        $activeOriginalCost = 0;
        
        if ($type === 'pos') {
            $items = PosItem::where('pos_sale_id', $model->id)
                ->whereNull('parent_item_id')
                ->with(['product', 'variation'])
                ->get();
            foreach ($items as $item) {
                $retQty = $returnedQtyMap[$item->id] ?? 0;
                $actQty = max(0, $item->quantity - $retQty);
                
                $netUnitPrice = $item->quantity > 0 ? ($item->total_price / $item->quantity) : 0;
                $activeOriginalRevenue += $actQty * $netUnitPrice;
                
                $unitCost = (float) ($item->unit_cost ?? 0);
                if ($item->product && ($unitCost <= 0 || $item->product->isCombo())) {
                    $unitCost = $item->product->calculateCost($item->variation_id);
                }
                $activeOriginalCost += $actQty * $unitCost;
            }
        } elseif ($type === 'order') {
            $items = OrderItem::where('order_id', $model->id)
                ->whereNull('parent_item_id')
                ->with(['product', 'variation'])
                ->get();
            foreach ($items as $item) {
                $retQty = $returnedQtyMap[$item->id] ?? 0;
                $actQty = max(0, $item->quantity - $retQty);
                
                $netUnitPrice = $item->quantity > 0 ? ($item->total_price / $item->quantity) : 0;
                $activeOriginalRevenue += $actQty * $netUnitPrice;
                
                $unitCost = (float) ($item->unit_cost ?? 0);
                if ($item->product && ($unitCost <= 0 || $item->product->isCombo())) {
                    $unitCost = $item->product->calculateCost($item->variation_id);
                }
                $activeOriginalCost += $actQty * $unitCost;
            }
        } elseif ($type === 'invoice') {
            $items = InvoiceItem::where('invoice_id', $model->id)
                ->with(['product', 'variation'])
                ->get();
            foreach ($items as $item) {
                $retQty = $returnedQtyMap[$item->id] ?? 0;
                $actQty = max(0, $item->quantity - $retQty);
                
                $netUnitPrice = $item->quantity > 0 ? ($item->total_price / $item->quantity) : 0;
                $activeOriginalRevenue += $actQty * $netUnitPrice;
                
                $unitCost = 0;
                if ($item->product) {
                    $unitCost = $item->product->calculateCost($item->variation_id);
                }
                $activeOriginalCost += $actQty * $unitCost;
            }
        }
        
        $exchangeNewRevenue = 0;
        $exchangeNewCost = 0;
        if ($type === 'pos') {
            $exchanges = PosExchange::with(['items.product', 'items.variation'])
                ->where('original_pos_id', $model->id)
                ->where('status', 'completed')
                ->where('exchange_date', $operator, $dateLimit)
                ->get();
                
            foreach ($exchanges as $exchange) {
                foreach ($exchange->items as $item) {
                    if ($item->type == 'new') {
                        $exchangeNewRevenue += floatval($item->total_price);
                        $cost = $item->product ? $item->product->calculateCost($item->variation_id) : 0;
                        $exchangeNewCost += $item->quantity * $cost;
                    }
                }
            }
        }
        
        $activeProductRevenue = $activeOriginalRevenue + $exchangeNewRevenue;
        $costAmount = $activeOriginalCost + $exchangeNewCost;
        
        $delivery = floatval($type === 'pos' ? ($model->delivery ?? 0) : (($model->order ?? null) ? $model->order->delivery : 0));
        $vatAmount = floatval($type === 'pos' ? ($model->vat_amount ?? 0) : (($model->invoice ?? null) ? ($model->invoice->tax ?? 0) : 0));
        $activeSaleAmount = $activeProductRevenue + $delivery + $vatAmount;
        
        $invoiceProfit = max(0, $activeProductRevenue - $costAmount);
        $profitMargin = $activeProductRevenue > 0 ? ($invoiceProfit / $activeProductRevenue) : 0;
        
        return [
            'margin' => $profitMargin,
            'cost' => $costAmount,
            'revenue' => $activeProductRevenue,
            'sale_amount' => $activeSaleAmount
        ];
    }
}
