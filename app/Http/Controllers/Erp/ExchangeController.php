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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExchangeController extends Controller
{
    public function index(Request $request)
    {
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

        $query = PosItem::with([
            'pos.customer', 'pos.originalPos', 'pos.branch', 'product.category', 'product.brand', 'product.season', 'product.gender',
            'variation.attributeValues.attribute'
        ])->whereHas('pos', function($q) {
            $q->whereNotNull('original_pos_id');
        });

        // Date Filtering
        if ($startDate && $endDate) {
            $query->whereHas('pos', function($q) use ($startDate, $endDate) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('pos', function($q) use ($startDate) {
                $q->whereDate('sale_date', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('pos', function($q) use ($endDate) {
                $q->whereDate('sale_date', '<=', $endDate);
            });
        }
        
        // Search by sale number / original sale / invoice / customer / product
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('pos', function($pq) use ($search) {
                    $pq->where('sale_number', 'LIKE', "%$search%")
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
            $query->whereHas('pos', function($q) use ($restrictedBranchId) {
                $q->where('branch_id', $restrictedBranchId);
            });
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('pos', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }
        if ($request->filled('customer_id')) {
            $query->whereHas('pos', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }
        
        // Filter by Product/Style/Category/Brand/Season/Gender
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);

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

        $items = $query->latest()->paginate(20)->appends($request->all());
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $branches = Branch::all();
        }
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('type', 'product')->orderBy('name')->get();
        $categories = ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $seasons = \App\Models\Season::orderBy('name')->get();
        $genders = \App\Models\Gender::orderBy('name')->get();

        return view('erp.exchange.index', compact(
            'items', 'branches', 'reportType', 'startDate', 'endDate', 
            'customers', 'products', 'categories', 'brands', 'seasons', 'genders'
        ));
    }

    public function create()
    {
        return view('erp.exchange.create');
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
                'product_name' => $item->product->name,
                'style_number' => $item->product->style_number ?? '-',
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
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'customer_id' => $sale->customer_id,
                'customer_name' => $sale->customer->name ?? 'Walk-in',
                'customer_phone' => $sale->customer->phone ?? '-',
                'branch_id' => $sale->branch_id,
                'discount' => $sale->discount,
                'sub_total' => $sale->sub_total,
                'items' => $items
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'original_pos_id' => 'required|exists:pos,id',
            'return_items' => 'required|array',
            'new_items' => 'required|array',
            'exchange_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $originalSale = Pos::findOrFail($request->original_pos_id);
            
            // 1. Process Returns
            $saleReturn = new SaleReturn();
            $saleReturn->pos_sale_id = $originalSale->id;
            $saleReturn->customer_id = $originalSale->customer_id;
            $saleReturn->return_date = $request->exchange_date;
            $saleReturn->status = 'completed';
            $saleReturn->refund_type = 'exchange';
            $saleReturn->processed_by = Auth::id();
            $saleReturn->return_to_type = 'branch';
            $saleReturn->return_to_id = $originalSale->branch_id;
            $saleReturn->save();

            $totalReturnAmount = 0;
            $originalDiscountRatio = $originalSale->sub_total > 0 ? ($originalSale->discount / $originalSale->sub_total) : 0;

            foreach ($request->return_items as $item) {
                if ($item['qty'] > 0) {
                    // SERVER SIDE VALIDATION: Check original item and its return history
                    $posItemId = $item['pos_item_id'];
                    $posItem = PosItem::with('returnItems')->findOrFail($posItemId);
                    $alreadyReturned = $posItem->returnItems->sum('returned_qty');
                    $canReturn = $posItem->quantity - $alreadyReturned;

                    if ($item['qty'] > $canReturn) {
                        throw new \Exception("Cannot return more than purchased. Product: {$posItem->product->name}. Max allowed: $canReturn");
                    }

                    $variationId = ($item['variation_id'] == 'null' || $item['variation_id'] == '') ? null : $item['variation_id'];
                    
                    // Real-world logic: Apply pro-rated discount from original invoice
                    $unitPrice = $item['unit_price'];
                    $itemDiscount = $unitPrice * $originalDiscountRatio;
                    $actualReturnPrice = $unitPrice - $itemDiscount;

                    $returnItem = new SaleReturnItem();
                    $returnItem->sale_return_id = $saleReturn->id;
                    $returnItem->sale_item_id = $posItem->id;
                    $returnItem->product_id = $item['product_id'];
                    $returnItem->variation_id = $variationId;
                    $returnItem->returned_qty = $item['qty'];
                    $returnItem->unit_price = $unitPrice;
                    $returnItem->total_price = $item['qty'] * $actualReturnPrice; // Storing actual credit value
                    $returnItem->save();

                    $totalReturnAmount += $returnItem->total_price;

                    // Restore Stock
                    $this->restoreStock($item['product_id'], $variationId, $item['qty'], $originalSale->branch_id);
                }
            }

            // 2. Process New Sale (Exchange)
            $newPos = new Pos();
            $newPos->sale_number = $this->generateSaleNumber();
            $newPos->customer_id = $originalSale->customer_id;
            $newPos->branch_id = $originalSale->branch_id;
            $newPos->sold_by = Auth::id();
            $newPos->sale_date = $request->exchange_date;
            $newPos->sale_type = 'exchange';
            $newPos->original_pos_id = $originalSale->id;
            $newPos->exchange_amount = $totalReturnAmount;
            
            $subTotal = 0;
            foreach ($request->new_items as $item) {
                $subTotal += $item['qty'] * $item['unit_price'];
            }
            
            $netPurchase = ($subTotal + $newPos->delivery) - ($request->discount ?? 0);
            
            // Real-world logic: exchange_amount is the amount of return credit actually USED for this purchase
            $exchangeCreditUsed = min($netPurchase, $totalReturnAmount);
            $finalPayable = max(0, $netPurchase - $totalReturnAmount);

            $newPos->sub_total = $subTotal;
            $newPos->discount = $request->discount ?? 0;
            $newPos->delivery = $request->delivery ?? 0;
            $newPos->exchange_amount = $exchangeCreditUsed;
            $newPos->total_amount = $finalPayable;
            $newPos->status = 'delivered';
            $newPos->save();

            // Create Invoice
            $invoice = new Invoice();
            $invoice->invoice_number = $this->generateInvoiceNumber();
            $invoice->customer_id = $newPos->customer_id;
            $invoice->issue_date = $newPos->sale_date;
            $invoice->due_date = $newPos->sale_date;
            $invoice->subtotal = $newPos->sub_total;
            $invoice->total_amount = $newPos->total_amount;
            $invoice->discount_apply = $newPos->discount;
            $invoice->paid_amount = $request->paid_amount ?? 0;
            $invoice->due_amount = max(0, $newPos->total_amount - ($request->paid_amount ?? 0));
            $invoice->status = $invoice->due_amount <= 0 ? 'paid' : 'partial';
            $invoice->created_by = Auth::id();
            $invoice->operated_by = Auth::id();
            $invoice->save();

            // Update Customer Balance (Real world ledger management)
            if ($originalSale->customer_id) {
                $balance = Balance::where('source_type', 'customer')->where('source_id', $originalSale->customer_id)->first();
                if ($balance) {
                    // 1. Return credit decreases what they owe (or increases what we owe them)
                    $balance->balance -= $totalReturnAmount;
                    
                    // 2. New purchase increases what they owe
                    $balance->balance += $netPurchase;
                    
                    // 3. Cash paid decreases what they owe
                    $balance->balance -= ($request->paid_amount ?? 0);
                    
                    $balance->save();
                }
            }

            $newPos->invoice_id = $invoice->id;
            $newPos->save();

            // Save Items
            foreach ($request->new_items as $item) {
                $variationId = ($item['variation_id'] == 'null' || $item['variation_id'] == '') ? null : $item['variation_id'];
                
                PosItem::create([
                    'pos_sale_id' => $newPos->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $variationId,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['qty'] * $item['unit_price'],
                ]);
                // Deduct Stock
                $this->deductStock($item['product_id'], $variationId, $item['qty'], $newPos->branch_id);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Exchange completed successfully.', 'redirect' => route('pos.show', $newPos->id)]);
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
        $last = Pos::latest()->first();
        return 'EXC-' . str_pad(($last ? $last->id + 1 : 1), 6, '0', STR_PAD_LEFT);
    }

    private function generateInvoiceNumber() {
        $last = Invoice::latest()->first();
        return 'INV-EXC-' . str_pad(($last ? $last->id + 1 : 1), 6, '0', STR_PAD_LEFT);
    }
}
