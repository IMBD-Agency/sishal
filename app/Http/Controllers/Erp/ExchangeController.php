<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Pos;
use App\Models\PosItem;
use App\Models\Product;
use App\Models\ProductServiceCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTemplate;
use App\Models\Payment;
use App\Models\Balance;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\FinancialAccount;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ExchangeController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view exchanges')) {
            abort(403, 'Unauthorized action.');
        }
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\PosExchangeItem::with([
            'exchange.customer', 'exchange.originalPos', 'exchange.branch', 'product.category', 'product.brand', 'product.season', 'product.gender',
            'variation.attributeValues.attribute'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);

        $items = $query->latest('id')->paginate(20)->appends($request->all());
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $branches = Branch::all();
        }
        $customers = Customer::orderBy('name', 'asc')->get();
        $products = Product::where('type', 'product')->orderBy('name', 'asc')->get();
        $categories = ProductServiceCategory::whereNull('parent_id')->orderBy('name', 'asc')->get();
        $brands = \App\Models\Brand::orderBy('name', 'asc')->get();
        $seasons = \App\Models\Season::orderBy('name', 'asc')->get();
        $genders = \App\Models\Gender::orderBy('name', 'asc')->get();

        return view('erp.exchange.index', compact(
            'items', 'branches', 'reportType', 'startDate', 'endDate', 
            'customers', 'products', 'categories', 'brands', 'seasons', 'genders'
        ));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage exchanges')) {
            abort(403, 'Unauthorized action.');
        }
        $branchId = $this->getRestrictedBranchId();
        return view('erp.exchange.create', compact('branchId'));
    }

    public function latestInvoices(Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        
        $sales = Pos::with(['customer'])
                    ->when($restrictedBranchId, function($q) use ($restrictedBranchId) {
                        $q->where('branch_id', $restrictedBranchId);
                    })
                    ->latest()
                    ->take(10)
                    ->get();

        $data = $sales->map(function($sale) {
            return [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer_name' => $sale->customer?->name ?? 'Walk-in',
                'date' => \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y'),
                'amount' => number_format($sale->total_amount, 2)
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function searchInvoice(Request $request)
    {
        $invoiceNo = $request->get('invoice_no');
        $sale = Pos::with(['customer', 'items.product', 'items.variation.attributeValues.attribute', 'items.returnItems'])
                   ->where('sale_number', $invoiceNo)
                   ->first();

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Sale not found.']);
        }

        $items = $sale->items->map(function($item) {
            $color = '-'; $size = '-';
            if ($item->variation && $item->variation->attributeValues) {
                foreach($item->variation->attributeValues as $val) {
                    $attrName = strtolower($val->attribute->name ?? '');
                    if (str_contains($attrName, 'color') || str_contains($attrName, 'colour') || str_contains($attrName, 'shade')) {
                        $color = $val->value;
                    }
                    elseif (str_contains($attrName, 'size') || str_contains($attrName, 'length') || str_contains($attrName, 'width')) {
                        $size = $val->value;
                    }
                }
            }
            $returnedQty = $item->returnItems->sum('returned_qty');
            $availableQty = $item->quantity - $returnedQty;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variation_id' => $item->variation_id,
                'product_name' => $item->product?->name ?? 'Unknown',
                'style_number' => $item->product?->style_number ?? '-',
                'color' => $color,
                'size' => $size,
                'quantity' => $item->quantity,
                'returned_qty' => $returnedQty,
                'available_qty' => $availableQty,
                'unit_price' => $item->unit_price,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sale?->id,
                'sale_number' => $sale?->sale_number,
                'customer_id' => $sale?->customer_id,
                'customer_name' => $sale?->customer?->name ?? 'Walk-in',
                'customer_phone' => $sale?->customer?->phone ?? '-',
                'branch_id' => $sale?->branch_id,
                'discount' => $sale?->discount,
                'sub_total' => $sale?->sub_total,
                'items' => $items
            ]
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage exchanges')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'original_pos_id' => 'required|exists:pos,id',
            'return_items' => 'required|array',
            'new_items' => 'required|array',
            'exchange_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $originalSale = Pos::findOrFail($request->original_pos_id);
            
            // Generate Exchange Number
            $lastExchange = \App\Models\PosExchange::latest('id')->first();
            $exchangeNumber = 'EXC-' . str_pad(($lastExchange ? $lastExchange->id + 1 : 1), 6, '0', STR_PAD_LEFT);

            $posExchange = \App\Models\PosExchange::create([
                'exchange_number' => $exchangeNumber,
                'original_pos_id' => $originalSale->id,
                'customer_id'     => $originalSale->customer_id,
                'branch_id'       => $originalSale->branch_id,
                'employee_id'     => Auth::id(),
                'exchange_date'   => $request->exchange_date,
                'status'          => 'completed'
            ]);

            $totalReturnAmount = 0;
            $originalDiscountRatio = $originalSale->sub_total > 0 ? ($originalSale->discount / $originalSale->sub_total) : 0;

            // 1. Process Returns
            foreach ($request->return_items as $item) {
                if ($item['qty'] > 0) {
                    $posItemId = $item['pos_item_id'];
                    $posItem = PosItem::findOrFail($posItemId);

                    $variationId = ($item['variation_id'] == 'null' || $item['variation_id'] == '') ? null : $item['variation_id'];
                    
                    $unitPrice = $item['unit_price'];
                    $itemDiscount = $unitPrice * $originalDiscountRatio;
                    $actualReturnPrice = $unitPrice - $itemDiscount;
                    $totalReturnItemAmount = $item['qty'] * $actualReturnPrice;

                    \App\Models\PosExchangeItem::create([
                        'pos_exchange_id' => $posExchange->id,
                        'type'            => 'returned',
                        'product_id'      => $item['product_id'],
                        'variation_id'    => $variationId,
                        'quantity'        => $item['qty'],
                        'unit_price'      => $actualReturnPrice,
                        'total_price'     => $totalReturnItemAmount,
                    ]);

                    // Also create standard SaleReturnItem for the original invoice so UI calculations hold
                    if (!isset($saleReturn)) {
                        $saleReturn = \App\Models\SaleReturn::create([
                            'customer_id'    => $originalSale->customer_id,
                            'pos_sale_id'    => $originalSale->id,
                            'invoice_id'     => $originalSale->invoice_id,
                            'return_date'    => $request->exchange_date,
                            'status'         => 'completed',
                            'refund_type'    => 'exchange',
                            'reason'         => 'Exchange ' . $exchangeNumber,
                            'processed_by'   => Auth::id(),
                            'processed_at'   => now(),
                            'return_to_type' => 'branch',
                            'return_to_id'   => $originalSale->branch_id,
                        ]);
                    }

                    \App\Models\SaleReturnItem::create([
                        'sale_return_id' => $saleReturn->id,
                        'sale_item_id'   => $posItemId,
                        'product_id'     => $item['product_id'],
                        'variation_id'   => $variationId,
                        'returned_qty'   => $item['qty'],
                        'unit_price'     => $actualReturnPrice,
                        'total_price'    => $totalReturnItemAmount,
                    ]);

                    $totalReturnAmount += $totalReturnItemAmount;

                    // Restore Stock
                    $this->restoreStock($item['product_id'], $variationId, $item['qty'], $originalSale->branch_id);
                }
            }

            // 2. Process New Items
            $subTotalNew = 0;
            foreach ($request->new_items as $item) {
                $itemGross = $item['qty'] * $item['unit_price'];
                $itemDiscStr = $item['discount'] ?? 0;
                $itemDisc = 0;
                if (strpos($itemDiscStr, '%') !== false) {
                    $itemDisc = ($itemGross * floatval($itemDiscStr)) / 100;
                } else {
                    $itemDisc = floatval($itemDiscStr);
                }
                $itemNetTotal = $itemGross - $itemDisc;
                $subTotalNew += $itemNetTotal;

                $variationId = ($item['variation_id'] == 'null' || $item['variation_id'] == '') ? null : $item['variation_id'];

                \App\Models\PosExchangeItem::create([
                    'pos_exchange_id' => $posExchange->id,
                    'type'            => 'new',
                    'product_id'      => $item['product_id'],
                    'variation_id'    => $variationId,
                    'quantity'        => $item['qty'],
                    'unit_price'      => $item['unit_price'],
                    'total_price'     => $itemNetTotal,
                ]);

                // Deduct Stock
                $this->deductStock($item['product_id'], $variationId, $item['qty'], $originalSale->branch_id);
            }
            
            $globalDiscount = $request->discount ?? 0;
            $deliveryCharge = $request->delivery ?? 0;
            $netNewAmount = ($subTotalNew + $deliveryCharge) - $globalDiscount;

            $extraPayable = max(0, $netNewAmount - $totalReturnAmount);
            $refundAmount = max(0, $totalReturnAmount - $netNewAmount);

            // Determine Exchange Type
            if ($totalReturnAmount == $subTotalNew && $extraPayable == 0 && $refundAmount == 0) {
                $posExchange->exchange_type = 'variation_exchange';
            } elseif ($extraPayable > 0 || $refundAmount > 0) {
                $posExchange->exchange_type = 'price_adjustment';
            } else {
                $posExchange->exchange_type = 'product_exchange';
            }

            $posExchange->update([
                'total_return_amount' => $totalReturnAmount,
                'total_new_amount'    => $subTotalNew,
                'delivery_charge'     => $deliveryCharge,
                'discount_amount'     => $globalDiscount,
                'extra_payable'       => $extraPayable,
                'refund_amount'       => $refundAmount,
                'account_id'          => $request->account_id,
                'payment_method'      => $request->account_id ? 'account' : 'cash',
            ]);

            // Update Original POS Sale to reflect new totals
            $originalSale->exchange_amount = ($originalSale->exchange_amount ?? 0) + $subTotalNew;
            $originalSale->refund_amount = ($originalSale->refund_amount ?? 0) + $refundAmount;
            $originalSale->save();
            
            // Also update the Invoice paid_amount if there's an extra payable so due amount is correct
            if ($originalSale->invoice && $extraPayable > 0) {
                $invoice = $originalSale->invoice;
                $invoice->paid_amount += $extraPayable;
                $invoice->due_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
                $invoice->save();
            }

            // =====================================================
            // AUTO JOURNAL ENTRY (Double-Entry Accounting)
            // Only if there's a difference
            // =====================================================
            
            if ($extraPayable > 0 || $refundAmount > 0) {
                $salesAccount = ChartOfAccount::where('name', 'like', '%Sales%')->first() ?? ChartOfAccount::first();
                $returnAccount = ChartOfAccount::where('name', 'like', '%Return%')->first() ?? $salesAccount;
                
                $voucherNo = $exchangeNumber;
                while (Journal::where('voucher_no', $voucherNo)->exists()) {
                    $voucherNo = $exchangeNumber . '-' . rand(10, 99);
                }

                $journal = Journal::create([
                    'voucher_no'     => $voucherNo,
                    'entry_date'     => $posExchange->exchange_date,
                    'type'           => $extraPayable > 0 ? 'Receipt' : 'Payment',
                    'description'    => 'Exchange Diff for #' . $originalSale->sale_number,
                    'customer_id'    => $posExchange->customer_id,
                    'branch_id'      => $posExchange->branch_id,
                    'voucher_amount' => $extraPayable > 0 ? $extraPayable : $refundAmount,
                    'paid_amount'    => $extraPayable > 0 ? ($request->paid_amount ?? 0) : $refundAmount,
                    'reference'      => $posExchange->exchange_number,
                    'created_by'     => Auth::id(),
                    'updated_by'     => Auth::id(),
                ]);

                // Determine the Cash/Bank account to hit
                $financialAccount = FinancialAccount::find($request->account_id);
                $cashBankAccountId = $financialAccount ? $financialAccount->account_id : null;
                if (!$cashBankAccountId) {
                    $cashAcc = ChartOfAccount::where('name', 'like', '%Cash%')->first();
                    $cashBankAccountId = $cashAcc ? $cashAcc->id : 1;
                }

                if ($extraPayable > 0) {
                    // Customer pays us (Receipt)
                    // Debit: Cash/Bank
                    // Credit: Sales (Difference)
                    JournalEntry::create([
                        'journal_id'           => $journal->id,
                        'chart_of_account_id'  => $cashBankAccountId,
                        'financial_account_id' => $financialAccount ? $financialAccount->id : null,
                        'debit'                => $extraPayable,
                        'credit'               => 0,
                        'memo'                 => 'Exchange Extra Payable Received',
                        'created_by'           => Auth::id(),
                    ]);
                    JournalEntry::create([
                        'journal_id'           => $journal->id,
                        'chart_of_account_id'  => $salesAccount->id,
                        'debit'                => 0,
                        'credit'               => $extraPayable,
                        'memo'                 => 'Exchange Extra Sales Revenue',
                        'created_by'           => Auth::id(),
                    ]);
                } elseif ($refundAmount > 0) {
                    // We refund customer (Payment)
                    // Debit: Sales Return (Difference)
                    // Credit: Cash/Bank
                    JournalEntry::create([
                        'journal_id'           => $journal->id,
                        'chart_of_account_id'  => $returnAccount->id,
                        'debit'                => $refundAmount,
                        'credit'               => 0,
                        'memo'                 => 'Exchange Refund',
                        'created_by'           => Auth::id(),
                    ]);
                    JournalEntry::create([
                        'journal_id'           => $journal->id,
                        'chart_of_account_id'  => $cashBankAccountId,
                        'financial_account_id' => $financialAccount ? $financialAccount->id : null,
                        'debit'                => 0,
                        'credit'               => $refundAmount,
                        'memo'                 => 'Exchange Refund Paid',
                        'created_by'           => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            // Since we don't have a dedicated POS Exchange view page yet, redirect back with success
            return response()->json(['success' => true, 'message' => 'Exchange completed successfully.', 'redirect' => route('exchange.list')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function restoreStock($productId, $variationId, $qty, $branchId) {
        // Simple restoration for now
        $stock = \App\Models\BranchProductStock::where('product_id', $productId)->where('branch_id', $branchId)->first();
        if ($stock) {
            $stock->quantity += $qty;
            $stock->save();
        }
        if ($variationId) {
            $vStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)->where('branch_id', $branchId)->first();
            if ($vStock) {
                $vStock->quantity += $qty;
                $vStock->save();
            }
        }
    }

    private function deductStock($productId, $variationId, $qty, $branchId) {
        // Simple deduction
        $stock = \App\Models\BranchProductStock::where('product_id', $productId)->where('branch_id', $branchId)->first();
        if ($stock) {
            $stock->quantity -= $qty;
            $stock->save();
        }
        if ($variationId) {
            $vStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)->where('branch_id', $branchId)->first();
            if ($vStock) {
                $vStock->quantity -= $qty;
                $vStock->save();
            }
        }
    }

    private function generateSaleNumber() {
        $last = Pos::latest('id')->first();
        return 'EXC-' . str_pad(($last ? $last->id + 1 : 1), 6, '0', STR_PAD_LEFT);
    }

    private function generateInvoiceNumber() {
        $last = Invoice::latest('id')->first();
        return 'INV-EXC-' . str_pad(($last ? $last->id + 1 : 1), 6, '0', STR_PAD_LEFT);
    }

    private function applyFilters($query, Request $request, $startDate = null, $endDate = null)
    {
        // Date Filtering
        if ($startDate && $endDate) {
            $query->whereHas('exchange', function($q) use ($startDate, $endDate) {
                $q->whereBetween('exchange_date', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('exchange', function($q) use ($startDate) {
                $q->whereDate('exchange_date', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('exchange', function($q) use ($endDate) {
                $q->whereDate('exchange_date', '<=', $endDate);
            });
        }
        
        // Search by sale number / original sale / invoice / customer / product
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('exchange', function($eq) use ($search) {
                    $eq->where('exchange_number', 'LIKE', "%$search%")
                      ->orWhereHas('originalPos', function($osq) use ($search) {
                          $osq->where('sale_number', 'LIKE', "%$search%");
                      })
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->where('name', 'LIKE', "%$search%")
                            ->orWhere('phone', 'LIKE', "%$search%");
                      });
                })
                ->orWhereHas('product', function($prq) use ($search) {
                    $prq->where('name', 'LIKE', "%$search%")
                        ->orWhere('style_number', 'LIKE', "%$search%");
                });
            });
        }

        // Filters from dropdowns
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->whereHas('exchange', function($q) use ($restrictedBranchId) {
                $q->where('branch_id', $restrictedBranchId);
            });
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('exchange', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }
        if ($request->filled('customer_id')) {
            $query->whereHas('exchange', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }
        
        // Filter by Product/Style/Category/Brand/Season/Gender
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('style_number') || $request->filled('category_id') || 
            $request->filled('brand_id') || $request->filled('season_id') || $request->filled('gender_id')) {
            
            $query->whereHas('product', function($q) use ($request) {
                if ($request->filled('style_number')) $q->where('style_number', 'like', '%' . $request->style_number . '%');
                if ($request->filled('category_id')) $q->where('category_id', $request->category_id);
                if ($request->filled('brand_id')) $q->where('brand_id', $request->brand_id);
                if ($request->filled('season_id')) $q->where('season_id', $request->season_id);
                if ($request->filled('gender_id')) $q->where('gender_id', $request->gender_id);
            });
        }

        if ($request->filled('city')) {
            $query->whereHas('exchange.customer', function($cq) use ($request) {
                $cq->where('city', $request->city);
            });
        }
        if ($request->filled('country')) {
            $query->whereHas('exchange.customer', function($cq) use ($request) {
                $cq->where('country', $request->country);
            });
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view exchanges')) {
            abort(403, 'Unauthorized action.');
        }
        
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), $request->get('month', date('m')), 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\PosExchange::with([
            'customer', 'originalPos', 'branch', 'items.product.category', 'items.product.brand', 'items.product.season', 'items.product.gender',
            'items.variation.attributeValues.attribute'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Serial No', 'Exchange Invoice', 'Sale Invoice', 'Date', 'Branch', 'Customer', 'Category', 
            'Brand', 'Season', 'Gender', 'Product Name', 'Style Number', 'Color', 'Size', 
            'Quantity', 'Exchange Amt', 'Refund', 'Discount', 'Paid', 'Due'
        ];
        
        $sheet->fromArray([$headers], NULL, 'A1');
        $sheet->getStyle('A1:T1')->getFont()->setBold(true);

        $rowNum = 2;
        $tEx = 0; $tRef = 0; $tDisc = 0; $tPaid = 0; $tDue = 0;
        foreach ($items as $index => $exchange) {
            $originalSale = $exchange->originalPos;
            
            foreach ($exchange->items as $i => $item) {
                $product = $item->product;
                $variation = $item->variation;

                $color = '-'; $size = '-';
                if ($variation && $variation->attributeValues) {
                    foreach($variation->attributeValues as $val) {
                        $attrName = strtolower($val->attribute->name ?? '');
                        if (str_contains($attrName, 'color')) $color = $val->value;
                        elseif (str_contains($attrName, 'size')) $size = $val->value;
                    }
                }

                $isFirst = ($i == 0);
                if ($isFirst) {
                    $tEx += $exchange->total_new_amount;
                    $tRef += $exchange->refund_amount;
                    $tDisc += $exchange->discount_amount;
                    $tPaid += $exchange->extra_payable; // Actually paid in this transaction
                    // $tDue isn't perfectly mapped without an invoice, keeping as 0 or skipped
                }

                $data = [
                    ($index + 1) . ($isFirst ? '' : '.'.($i+1)),
                    $exchange->exchange_number,
                    $originalSale->sale_number ?? '-',
                    \Carbon\Carbon::parse($exchange->exchange_date)->format('d/m/Y'),
                    $exchange->branch->name ?? '-',
                    $exchange->customer->name ?? 'Walk-in',
                    $product->category->name ?? '-',
                    $product->brand->name ?? '-',
                    $product->season->name ?? '-',
                    $product->gender->name ?? '-',
                    $product->name,
                    $product->style_number,
                    $color,
                    $size,
                    $item->quantity . ' (' . ucfirst($item->type) . ')',
                    $isFirst ? $exchange->total_new_amount : '',
                    $isFirst ? $exchange->refund_amount : '',
                    $isFirst ? $exchange->discount_amount : '',
                    $isFirst ? $exchange->extra_payable : '',
                    '' // Due
                ];
                $sheet->fromArray([$data], NULL, 'A' . $rowNum);
                $rowNum++;
            }
        }

        // Add Totals Row
        $totalRow = [
            'GRAND TOTAL', '', '', '', '', '', '', '', '', '', '', '', '', '', 
            '', // Qty (Optional, but user usually wants financial totals)
            $tEx, 
            $tRef, 
            $tDisc, 
            $tPaid, 
            $tDue
        ];
        $sheet->fromArray([$totalRow], NULL, 'A' . $rowNum);
        $sheet->getStyle('A' . $rowNum . ':T' . $rowNum)->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'exchange_report_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view exchanges')) {
            abort(403, 'Unauthorized action.');
        }
        
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), $request->get('month', date('m')), 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = \Carbon\Carbon::createFromDate($request->get('year', date('Y')), 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\PosExchange::with([
            'customer', 'originalPos', 'branch', 'items.product.category', 'items.product.brand', 'items.product.season', 'items.product.gender',
            'items.variation.attributeValues.attribute'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->latest()->get();

        $pdf = Pdf::loadView('erp.exchange.export-pdf', compact('items', 'reportType', 'startDate', 'endDate'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'exchange_report_' . date('Ymd_His') . '.pdf';
        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }
    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view exchanges')) {
            abort(403, 'Unauthorized action.');
        }

        $exchange = \App\Models\PosExchange::with([
            'customer', 'originalPos', 'branch', 'employee',
            'items.product', 'items.variation.attributeValues.attribute'
        ])->findOrFail($id);

        return view('erp.exchange.show', compact('exchange'));
    }

    public function printReceipt($id, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view exchanges')) {
            abort(403, 'Unauthorized action.');
        }

        $exchange = \App\Models\PosExchange::with([
            'customer', 'originalPos', 'branch', 'employee',
            'items.product', 'items.variation.attributeValues.attribute'
        ])->findOrFail($id);

        $general_settings = GeneralSetting::first();
        $action = $request->get('action', 'print');

        return view('erp.exchange.print', compact('exchange', 'general_settings', 'action'));
    }
}
