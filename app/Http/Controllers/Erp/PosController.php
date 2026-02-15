<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Mail\SaleConfirmation;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Customer;
use App\Models\EmployeeProductStock;
use App\Models\GeneralSetting;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Pos;
use App\Models\PosItem;
use App\Models\Product;
use App\Models\ProductServiceCategory;
use App\Models\InvoiceTemplate;
use App\Models\ProductVariationStock;
use App\Models\WarehouseProductStock;
use App\Models\Brand;
use App\Models\Season;
use App\Models\Gender;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PosController extends Controller
{
    public function addPos()
    {
        $categories = ProductServiceCategory::all();
        $branches = Branch::all();
        
        // Branch Isolation: Check if user is an employee with a branch
        $user = auth()->user();
        if ($user && $user->employee && $user->employee->branch_id) {
            $branches = $branches->where('id', $user->employee->branch_id);
        }

        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        $shippingMethods = \App\Models\ShippingMethod::orderBy('sort_order')->get();
        $customers = Customer::orderBy('name')->get();
        return view('erp.pos.addPos', compact('categories', 'branches', 'bankAccounts', 'shippingMethods', 'customers'));
    }

    public function makeSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'sale_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date',
            'estimated_delivery_time' => 'nullable',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'delivery' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'account_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'customer_city' => 'nullable|string|required_with:customer_address',
            'customer_state' => 'nullable|string|required_with:customer_address',
            'customer_zip_code' => 'nullable|string|required_with:customer_address',
            'customer_country' => 'nullable|string',
            'sale_type' => 'nullable|string',
            'courier_id' => 'nullable|exists:shipping_methods,id',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique sale number
            $saleNumber = $this->generateSaleNumber();

            $pos = new Pos();
            $pos->sale_number = $saleNumber;
            $pos->customer_id = $request->customer_id;
            $pos->branch_id = $request->branch_id;
            $pos->sold_by = auth()->id();
            $pos->sale_date = $request->sale_date;
            $pos->sub_total = $request->sub_total;
            $pos->discount = $request->discount ?? 0;
            $pos->delivery = $request->delivery ?? 0;
            $pos->total_amount = $request->total_amount;
            $pos->estimated_delivery_date = $request->estimated_delivery_date;
            $pos->estimated_delivery_time = $request->estimated_delivery_time;
            $pos->status = 'pending'; // or 'pending' if you want manual approval
            $pos->notes = $request->notes;
            $pos->sale_type = $request->sale_type ?? 'MRP';
            $pos->courier_id = $request->courier_id;
            $pos->save();

            if($request->customer_type == 'new-customer') {
                $customer = Customer::create([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email,
                    'address_1' => $request->customer_address,
                    'city' => $request->customer_city,
                    'state' => $request->customer_state,
                    'zip_code' => $request->customer_zip_code,
                    'country' => $request->customer_country,
                    'created_by' => $pos->sold_by,
                ]);

                $pos->customer_id = $customer->id;
                $pos->save();
            }

            // --- Create Invoice ---

            $invTemplate = InvoiceTemplate::where('is_default', 1)->first();
            
            // Calculate tax
            $generalSettings = GeneralSetting::first();
            $taxRate = $generalSettings ? ($generalSettings->tax_rate / 100) : 0.00;
            $tax = round($pos->sub_total * $taxRate, 2);
            
            $invoice = new Invoice();
            $invoice->invoice_number = $this->generateInvoiceNumber();
            $invoice->template_id = $invTemplate?->id;
            $invoice->customer_id = $pos->customer_id;
            $invoice->operated_by = $pos->sold_by;
            $invoice->issue_date = now()->toDateString();
            $invoice->due_date = now()->toDateString();
            $invoice->subtotal = $pos->sub_total;
            $invoice->tax = $tax;
            $invoice->total_amount = $pos->total_amount;
            $invoice->discount_apply = $pos->discount;
            $invoice->paid_amount = 0;
            $invoice->due_amount = $pos->total_amount;
            $invoice->status = 'unpaid';
            $invoice->note = $pos->notes;
            $invoice->footer_text = $invTemplate?->footer_note;
            $invoice->created_by = $pos->sold_by;
            $invoice->save();

            $pos->invoice_id = $invoice->id;
            $pos->save();

            // --- End Invoice ---

            // Validate stock availability and deduct stock immediately
            foreach ($request->items as $item) {
                $result = $this->deductStock(
                    $item['product_id'],
                    $item['variation_id'] ?? null,
                    $item['quantity'],
                    $request->branch_id
                );
                
                if (!$result['success']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ], 400);
                }
            }

            // Save POS items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $createdItem = PosItem::create([
                    'pos_sale_id' => $pos->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $product->cost ?? 0,
                    'total_price' => $item['total_price'],
                    'current_position_type' => 'branch',
                    'current_position_id' => $request->branch_id
                ]);

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            if($request->customer_address) {
                InvoiceAddress::create([
                    'invoice_id' => $invoice->id,
                    'billing_address_1' => $request->customer_address,
                    'billing_city' => $request->customer_city,
                    'billing_state' => $request->customer_state,
                    'billing_zip_code' => $request->customer_zip_code,
                    'billing_country' => $request->customer_country,
                    'shipping_address_1' => $request->customer_address,
                    'shipping_city' => $request->customer_city,
                    'shipping_state' => $request->customer_state,
                    'shipping_zip_code' => $request->customer_zip_code,
                    'shipping_country' => $request->customer_country,
                ]);
            }


            // Save payment if paid_amount > 0
            if ($request->paid_amount > 0) {
                Payment::create([
                    'payment_for' => 'pos',
                    'pos_id' => $pos->id,
                    'invoice_id' => $invoice->id,
                    'payment_date' => now()->toDateString(),
                    'amount' => $request->paid_amount,
                    'account_id' => $request->account_id,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'reference' => null,
                    'note' => $request->notes,
                ]);
                // Update invoice paid/due/status for upfront payment
                $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $request->paid_amount;
                $invoice->due_amount = max(0, ($invoice->total_amount ?? 0) - $invoice->paid_amount);
                if ($invoice->paid_amount >= ($invoice->total_amount ?? 0)) {
                    $invoice->status = 'paid';
                    $invoice->due_amount = 0;
                    // Auto-set POS status to delivered when fully paid
                    $pos->status = 'delivered';
                    // Move items to customer (delivered) - reload items to ensure they're available
                    $pos->load('items');
                    foreach ($pos->items as $item) {
                        $item->current_position_id = null;
                        $item->save();
                    }
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'unpaid';
                }
                $invoice->save();
                $pos->save(); // Save the status change

                if ($pos->customer_id) {
                    Balance::create([
                        'source_type' => 'customer',
                        'source_id' => $pos->customer_id,
                        'balance' => $pos->total_amount - $request->paid_amount,
                        'description' => 'POS Sale',
                        'reference' => $pos->sale_number,
                    ]);
                }
            } else {
                if ($pos->customer_id) {
                    Balance::create([
                        'source_type' => 'customer',
                        'source_id' => $pos->customer_id,
                        'balance' => $pos->total_amount,
                        'description' => 'POS Sale',
                        'reference' => $pos->sale_number,
                    ]);
                }
            }

            DB::commit();
            
            // Send Sale Confirmation Email
            try {
                if ($pos->customer && $pos->customer->email) {
                    Mail::to($pos->customer->email)->send(new SaleConfirmation($pos));
                }
            } catch (\Exception $e) {
                // swallow
            }

            return response()->json(['success' => true, 'message' => 'Sale created successfully.', 'sale_id' => $pos->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

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

        $query = \App\Models\PosItem::with([
            'pos.customer',
            'pos.invoice',
            'pos.branch',
            'pos.soldBy',
            'product.category',
            'product.brand',
            'product.season',
            'product.gender',
            'variation.attributeValues.attribute',
            'returnItems'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);

        // Sums for filtered items
        $totalQty = $query->sum('quantity');
        $totalAmount = $query->sum('total_price');

        $items = $query->latest()->paginate(20)->appends($request->all());
        
        // Dropdown Data
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branches = $restrictedBranchId ? Branch::where('id', $restrictedBranchId)->get() : Branch::all();
        $customers = Customer::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $seasons = \App\Models\Season::orderBy('name')->get();
        $genders = \App\Models\Gender::orderBy('name')->get();
        $products = \App\Models\Product::where('type', 'product')->orderBy('name')->get();

        return view('erp.pos.index', compact(
            'items', 'branches', 'customers', 'categories', 'brands', 'seasons', 'genders', 'products', 
            'reportType', 'startDate', 'endDate', 'totalQty', 'totalAmount'
        ));
    }

    private function applyFilters($query, Request $request, $startDate = null, $endDate = null)
    {
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

        // Search by sale number / invoice / customer / product / salesperson
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('pos', function($pq) use ($search) {
                    $pq->where('sale_number', 'LIKE', "%$search%")
                      ->orWhereHas('customer', function($cq) use ($search) {
                          $cq->where('name', 'LIKE', "%$search%")
                            ->orWhere('phone', 'LIKE', "%$search%");
                      })
                      ->orWhereHas('soldBy', function($sq) use ($search) {
                          $sq->where('first_name', 'LIKE', "%$search%")
                            ->orWhere('last_name', 'LIKE', "%$search%");
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
        $selectedBranchId = $restrictedBranchId ?: $request->branch_id;

        if ($selectedBranchId) {
            $query->whereHas('pos', function($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
        }
        if ($request->filled('customer_id')) {
            $query->whereHas('pos', function($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('pos', function($q) use ($request) {
                $q->where('status', $request->status);
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

        return $query;
    }
    public function show($id)
    {
        $pos = Pos::where('id', $id)
            ->with([
                'customer',
                'invoice',
                'invoice.invoiceAddress',
                'branch',
                'soldBy',
                'items.product',
                'items.variation.attributeValues.attribute',
                'items.branch',
                'items.technician.user',
                'payments'
            ])
            ->first();

        if (!$pos) {
            return redirect()->route('pos.list')->with('error', 'Sale not found.');
        }

        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        return view('erp.pos.show', compact('pos', 'bankAccounts'));
    }

    /**
     * Get POS sale details as JSON (for API/AJAX calls)
     */
    public function getDetails($id)
    {
        $pos = Pos::where('id', $id)
            ->with([
                'customer',
                'invoice',
                'branch',
                'items.product',
                'items.variation'
            ])
            ->first();

        if (!$pos) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pos->id,
                'sale_number' => $pos->sale_number,
                'customer_id' => $pos->customer_id,
                'customer_name' => $pos->customer ? $pos->customer->name : null,
                'branch_id' => $pos->branch_id,
                'branch_name' => $pos->branch ? $pos->branch->name : null,
                'invoice_id' => $pos->invoice_id,
                'items' => $pos->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product ? $item->product->name : null,
                        'variation_id' => $item->variation_id,
                        'variation_name' => $item->variation ? $item->variation->name : null,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                })
            ]
        ]);
    }

    public function edit($id)
    {
        $pos = Pos::with(['customer', 'branch', 'items.product', 'items.variation.attributeValues.attribute'])->findOrFail($id);
        
        // Only allow editing if status is pending or delivered (not cancelled)
        if ($pos->status === 'cancelled') {
            return redirect()->route('pos.show', $id)->with('error', 'Cannot edit a cancelled sale.');
        }

        $categories = ProductServiceCategory::all();
        $branches = Branch::all();
        $customers = Customer::all();
        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        
        return view('erp.pos.edit', compact('pos', 'categories', 'branches', 'customers', 'bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        $pos = Pos::with(['items', 'invoice'])->findOrFail($id);
        
        // Only allow editing if status is pending or delivered (not cancelled)
        if ($pos->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cannot edit a cancelled sale.'], 400);
        }

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'sale_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date',
            'estimated_delivery_time' => 'nullable',
            'sub_total' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'delivery' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Store old items for stock restoration
            $oldItems = $pos->items;
            
            // Restore stock from old items
            foreach ($oldItems as $oldItem) {
                $this->restoreStock(
                    $oldItem->product_id,
                    $oldItem->variation_id,
                    $oldItem->quantity,
                    $pos->branch_id
                );
            }

            // Update POS sale
            $pos->customer_id = $request->customer_id;
            $pos->branch_id = $request->branch_id;
            $pos->sale_date = $request->sale_date;
            $pos->sub_total = $request->sub_total;
            $pos->discount = $request->discount ?? 0;
            $pos->delivery = $request->delivery ?? 0;
            $pos->total_amount = $request->total_amount;
            $pos->estimated_delivery_date = $request->estimated_delivery_date;
            $pos->estimated_delivery_time = $request->estimated_delivery_time;
            $pos->notes = $request->notes;
            $pos->save();

            // Delete old items
            $pos->items()->delete();

            // Add new items and deduct stock
            foreach ($request->items as $item) {
                // Validate and deduct stock
                $stockResult = $this->deductStock(
                    $item['product_id'],
                    $item['variation_id'] ?? null,
                    $item['quantity'],
                    $request->branch_id
                );

                if (!$stockResult['success']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $stockResult['message']], 400);
                }

                // Create POS item
                $product = Product::find($item['product_id']);
                $posItem = new PosItem();
                $posItem->pos_sale_id = $pos->id;
                $posItem->product_id = $item['product_id'];
                $posItem->variation_id = $item['variation_id'] ?? null;
                $posItem->quantity = $item['quantity'];
                $posItem->unit_price = $item['unit_price'];
                $posItem->unit_cost = $product->cost ?? 0;
                $posItem->total_price = $item['quantity'] * $item['unit_price'];
                $posItem->current_position_type = 'branch';
                $posItem->current_position_id = $request->branch_id;
                $posItem->save();
            }

            // Update invoice if exists
            if ($pos->invoice) {
                $generalSettings = GeneralSetting::first();
                $taxRate = $generalSettings ? ($generalSettings->tax_rate / 100) : 0.00;
                $tax = round($pos->sub_total * $taxRate, 2);

                $invoice = $pos->invoice;
                $invoice->subtotal = $pos->sub_total;
                $invoice->discount_apply = $pos->discount;
                $invoice->tax = $tax;
                $invoice->total_amount = $pos->total_amount;
                $invoice->due_amount = max(0, $invoice->total_amount - ($invoice->paid_amount ?? 0));
                
                // Update invoice status based on payment
                if ($invoice->paid_amount >= $invoice->total_amount) {
                    $invoice->status = 'paid';
                    $invoice->due_amount = 0;
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'unpaid';
                }
                $invoice->save();

                // Delete old invoice items
                $invoice->items()->delete();

                // Create new invoice items
                foreach ($request->items as $item) {
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->product_id = $item['product_id'];
                    $invoiceItem->variation_id = $item['variation_id'] ?? null;
                    $invoiceItem->quantity = $item['quantity'];
                    $invoiceItem->unit_price = $item['unit_price'];
                    $invoiceItem->total_price = $item['quantity'] * $item['unit_price'];
                    $invoiceItem->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sale updated successfully.', 'sale_id' => $pos->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $pos = Pos::with([
            'customer',
            'branch',
            'items.product',
            'items.variation.attributeValues.attribute',
            'invoice',
            'soldBy'
        ])->findOrFail($id);

        $template = InvoiceTemplate::where('is_default', 1)->first();
        $general_settings = GeneralSetting::first();
        $action = request()->get('action', 'print');

        // Calculate tax if not already calculated
        if ($pos->invoice && !$pos->invoice->tax && $general_settings && $general_settings->tax_rate > 0) {
            $taxRate = $general_settings->tax_rate / 100;
            $pos->invoice->tax = round($pos->sub_total * $taxRate, 2);
        }

        // Generate QR code as SVG
        $printUrl = route('pos.print', ['id' => $pos->id]);
        $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(60)->generate($printUrl);

        // PDF download logic
        if ($action == 'download') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.pos.print', compact('pos', 'template', 'action', 'qrCodeSvg', 'general_settings'));
            // Increased width to 260 points (~91mm) to provide breathing room and prevent clipping
            $pdf->setPaper([0, 0, 260, 1000], 'portrait');
            return $pdf->download('pos-receipt-'.$pos->sale_number.'.pdf');
        }

        return view('erp.pos.print', compact('pos', 'template', 'action', 'qrCodeSvg', 'general_settings'));
    }

    public function getMultiBranchStock($productId, $variationId = null)
    {
        $product = Product::findOrFail($productId);
        $branches = Branch::all();
        $stockData = [];

        foreach ($branches as $branch) {
            if ($variationId) {
                $stock = ProductVariationStock::where('variation_id', $variationId)
                    ->where('branch_id', $branch->id)
                    ->whereNull('warehouse_id')
                    ->first();
                $quantity = $stock ? ($stock->available_quantity ?? ($stock->quantity - ($stock->reserved_quantity ?? 0))) : 0;
            } else {
                $stock = BranchProductStock::where('branch_id', $branch->id)
                    ->where('product_id', $productId)
                    ->first();
                $quantity = $stock ? $stock->quantity : 0;
            }

            $stockData[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'quantity' => $quantity,
            ];
        }

        return response()->json(['success' => true, 'data' => $stockData]);
    }

    public function getBranchStock($productId, $branchId, $variationId = null)
    {
        if ($variationId && $variationId !== 'null') {
            $stock = ProductVariationStock::where('variation_id', $variationId)
                ->where('branch_id', $branchId)
                ->where(function($q) {
                    $q->whereNull('warehouse_id')->orWhere('warehouse_id', 0);
                })
                ->first();
            // Use available_quantity accessor or calculate manually
            $quantity = $stock ? ($stock->quantity - ($stock->reserved_quantity ?? 0)) : 0;
            
            // Double check: if no variation stock record exists, strictly return 0
            // Do NOT fall back to product stock here
        } else {
            $stock = BranchProductStock::where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->first();
            $quantity = $stock ? $stock->quantity : 0;
        }

        return response()->json(['success' => true, 'quantity' => $quantity]);
    }

    // Technician assignment removed - not needed for ecommerce-only business
    // public function assignTechnician($saleId, $techId)
    // {
    //     $pos = Pos::find($saleId);
    //     if (!$pos) {
    //         return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
    //     }
    //     $employee = \App\Models\Employee::find($techId);
    //     if (!$employee) {
    //         return response()->json(['success' => false, 'message' => 'Technician not found.'], 404);
    //     }
    //     $pos->employee_id = $techId;
    //     $pos->save();
    //     return response()->json(['success' => true, 'message' => 'Technician assigned successfully.']);
    // }

    public function updateNote($saleId, Request $request)
    {
        $pos = Pos::find($saleId);
        if (!$pos) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }
        $pos->notes = $request->input('note');
        $pos->save();
        return response()->json(['success' => true, 'message' => 'Note updated successfully.']);
    }

    public function addPayment($saleId, Request $request)
    {
        $pos = Pos::with('invoice')->find($saleId);
        if (!$pos) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }
        $invoice = $pos->invoice;
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'account_id' => 'nullable|integer',
            'note' => 'nullable|string',
        ]);
        // Create payment
        $payment = new Payment();
        $payment->payment_for = 'pos';
        $payment->pos_id = $pos->id;
        $payment->invoice_id = $invoice->id;
        $payment->payment_date = now()->toDateString();
        $payment->amount = $request->amount;
        $payment->account_id = $request->account_id;
        $payment->payment_method = $request->payment_method;
        $payment->note = $request->note;
        $payment->save();
        // Update invoice
        $invoice->paid_amount += $request->amount;
        $invoice->due_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
        if ($invoice->paid_amount >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->due_amount = 0;
                    // Auto-set POS status to delivered when fully paid
                    $pos->status = 'delivered';
                    // Move items to customer (delivered) - reload items to ensure they're available
                    $pos->load('items');
                    foreach ($pos->items as $item) {
                        $item->current_position_id = null;
                        $item->save();
                    }
            $pos->save();
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'unpaid';
        }
        $invoice->save();


        if($request->payment_method == 'cash' && $pos->customer_id)
        {
            $balance = Balance::where('source_type', 'customer')->where('source_id', $pos->customer_id)->first();
            if($balance)
            {
                $balance->balance -= $request->amount;
                $balance->save();
            }
            else
            {
                Balance::create([
                    'source_type' => 'customer',
                    'source_id' => $pos->customer_id,
                    'balance' => $invoice->due_amount,
                    'description' => 'POS Sale',
                    'reference' => $pos->sale_number,
                ]);
            }
        }

        if($request->received_by)
        {
            $balance = Balance::where('source_type', 'employee')->where('source_id', $request->received_by)->first();
            if($balance)
            {
                $balance->balance += $request->amount;
                $balance->save();
            }
            else
            {
                Balance::create([
                    'source_type' => 'employee',
                    'source_id' => $request->received_by,
                    'balance' => $request->amount,
                    'description' => 'POS Sale',
                    'reference' => $pos->sale_number,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Payment added successfully.']);
    }

    public function updateStatus($saleId, Request $request)
    {
        $pos = Pos::findOrFail($saleId);
        
        $request->validate([
            'status' => 'required|string',
        ]);

        // Prevent cancellation if already delivered
        if ($request->status == 'cancelled' && $pos->status == 'delivered') {
            return response()->json(['success' => false, 'message' => 'Cannot cancel a sale that has already been delivered.'], 400);
        }

        if ($request->status == 'pending') {
            $pos->status = $request->input('status');
        } else if ($request->status == 'delivered') {
            $pos->status = $request->input('status');
            foreach ($pos->items as $item) {
                $item->current_position_id = null;
                $item->save();
            }
        } else if ($request->status == 'cancelled') {
            $pos->status = $request->input('status');
            foreach ($pos->items as $item) {
                // Move back to branch
                $item->current_position_type = 'branch';
                $item->current_position_id = $pos->branch_id;
                $item->save();

                // Restore stock using helper method
                $this->restoreStock(
                    $item->product_id,
                    $item->variation_id,
                    $item->quantity,
                    $pos->branch_id
                );
            }
        }

        $pos->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function addAddress(Request $request, $id)
    {
        $existingInvoiceAddress = InvoiceAddress::where('invoice_id', $id)->first();

        if ($existingInvoiceAddress) {
            $existingInvoiceAddress->billing_address_1 = $request->billing_address_1;
            $existingInvoiceAddress->billing_address_2 = $request->billing_address_2;
            $existingInvoiceAddress->billing_city = $request->billing_city;
            $existingInvoiceAddress->billing_state = $request->billing_state;
            $existingInvoiceAddress->billing_country = $request->billing_country;
            $existingInvoiceAddress->billing_zip_code = $request->billing_zip_code;

            $existingInvoiceAddress->shipping_address_1 = $request->shipping_address_1;
            $existingInvoiceAddress->shipping_address_2 = $request->shipping_address_2;
            $existingInvoiceAddress->shipping_city = $request->shipping_city;
            $existingInvoiceAddress->shipping_state = $request->shipping_state;
            $existingInvoiceAddress->shipping_country = $request->shipping_country;
            $existingInvoiceAddress->shipping_zip_code = $request->shipping_zip_code;

            $existingInvoiceAddress->save();
        } else {
            $invoiceAddress = new InvoiceAddress();
            $invoiceAddress->invoice_id = $id;
            $invoiceAddress->billing_address_1 = $request->billing_address_1;
            $invoiceAddress->billing_address_2 = $request->billing_address_2;
            $invoiceAddress->billing_city = $request->billing_city;
            $invoiceAddress->billing_state = $request->billing_state;
            $invoiceAddress->billing_country = $request->billing_country;
            $invoiceAddress->billing_zip_code = $request->billing_zip_code;

            $invoiceAddress->shipping_address_1 = $request->shipping_address_1;
            $invoiceAddress->shipping_address_2 = $request->shipping_address_2;
            $invoiceAddress->shipping_city = $request->shipping_city;
            $invoiceAddress->shipping_state = $request->shipping_state;
            $invoiceAddress->shipping_country = $request->shipping_country;
            $invoiceAddress->shipping_zip_code = $request->shipping_zip_code;

            $invoiceAddress->save();
        }
    }

    public function posSearch(Request $request)
    {
        $q = $request->input('q');
        $customerId = $request->input('customer_id');
        $query = \App\Models\Pos::with('customer');
        
        // Filter by customer if provided
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        
        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('sale_number', 'like', "%$q%")
                    ->orWhereHas('customer', function ($q2) use ($q) {
                        $q2->where('name', 'like', "%$q%")
                            ->orWhere('phone', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                    });
            });
        }
        $sales = $query->orderBy('sale_number', 'desc')->limit(20)->get();
        $results = $sales->map(function ($sale) use ($customerId) {
            // If customer is already selected, just show sale number
            // Otherwise show customer info for identification
            $text = $sale->sale_number;
            if (!$customerId) {
                $customer = $sale->customer;
                if ($customer) {
                    $text .= ' - ' . $customer->name;
                    if ($customer->phone)
                        $text .= ' (' . $customer->phone . ')';
                    if ($customer->email)
                        $text .= ' [' . $customer->email . ']';
                }
            }
            return [
                'id' => $sale->id,
                'text' => $text
            ];
        });
        return response()->json($results);
    }

    // Add this function to generate a unique invoice number
    private function generateInvoiceNumber()
    {
        $generalSettings = GeneralSetting::first();
        $prefix = $generalSettings ? $generalSettings->invoice_prefix : 'INV';
        
        $nextId = (\App\Models\Invoice::max('id') ?? 0) + 1;
        $number = $prefix . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        
        while (\App\Models\Invoice::where('invoice_number', $number)->exists()) {
            $nextId++;
            $number = $prefix . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        }
        
        return $number;
    }


    /**
     * Get report data for the modal
     */
    public function getReportData(Request $request)
    {
        try {
            $query = Pos::with(['customer', 'invoice', 'branch']);

            // Date range filter
            $startDate = $request->input('start_date') ?? $request->input('date_from');
            $endDate = $request->input('end_date') ?? $request->input('date_to');
            if ($startDate) {
                $query->whereDate('sale_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('sale_date', '<=', $endDate);
            }

            // Status filter
            if ($request->filled('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Payment status filter
            $paymentStatus = $request->input('bill_status') ?? $request->input('payment_status');
            if ($paymentStatus && $paymentStatus !== '') {
                $query->whereHas('invoice', function ($q) use ($paymentStatus) {
                    $q->where('status', $paymentStatus);
                });
            }

            // Branch filter
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('sale_number', 'like', "%$search%")
                        ->orWhereHas('customer', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%$search%")
                                ->orWhere('phone', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                        });
                });
            }

            $sales = $query->orderBy('sale_date', 'desc')->get();

            // Transform data for frontend
            $transformedSales = $sales->map(function ($sale) {
                return [
                    'sale_number' => $sale->sale_number,
                    'sale_date' => $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') : '-',
                    'customer_name' => $sale->customer ? $sale->customer->name : 'Walk-in Customer',
                    'customer_phone' => $sale->customer ? $sale->customer->phone : '-',
                    'branch_name' => $sale->branch ? $sale->branch->name : '-',
                    'status' => $sale->status,
                    'payment_status' => $sale->invoice ? $sale->invoice->status : '-',
                    'sub_total' => number_format($sale->sub_total, 2),
                    'discount' => number_format($sale->discount, 2),
                    'total_amount' => number_format($sale->total_amount, 2),
                    'paid_amount' => $sale->invoice ? number_format($sale->invoice->paid_amount, 2) : '0.00',
                    'due_amount' => $sale->invoice ? number_format($sale->invoice->due_amount, 2) : '0.00',
                ];
            });

            // Calculate summary statistics
            $summary = [
                'total_sales' => $sales->count(),
                'total_amount' => number_format($sales->sum('total_amount'), 2),
                'paid_sales' => $sales->filter(function($sale) {
                    return $sale->invoice && $sale->invoice->status === 'paid';
                })->count(),
                'unpaid_sales' => $sales->filter(function($sale) {
                    return $sale->invoice && $sale->invoice->status === 'unpaid';
                })->count(),
            ];

            return response()->json([
                'sales' => $transformedSales,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getReportData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred while loading report data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
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

        $query = \App\Models\PosItem::with([
            'pos.customer', 'pos.invoice', 'pos.branch', 'pos.soldBy',
            'product.category', 'product.brand', 'product.season', 'product.gender',
            'variation.attributeValues.attribute', 'returnItems'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->latest()->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Serial No', 'Invoice', 'Date', 'Customer', 'Created', 'Category', 'Brand', 'Season', 'Gender',
            'Product Name', 'Style Number', 'Color', 'Size', 'Sales Qty', 'Total S-Qty', 'Sales Amount', 'Total Sales Amount', 
            'SR-Qty', 'Total SR-Qty', 'SR-Amount', 'Total SR-Amount', 'AS-Qty', 'Total AS-Qty',
            'Delivery Charge', 'Discount Amount', 'Exchange Amount', 'Actual Sales Amount', 'Final Total', 'Received Amount', 'Due Amount'
        ];
        
        $sheet->fromArray([$headers], NULL, 'A1');
        $sheet->getStyle('A1:AC1')->getFont()->setBold(true);

        $rowNum = 2;
        foreach ($items as $index => $item) {
            $sale = $item->pos;
            $invoice = $sale->invoice;
            $product = $item->product;
            $variation = $item->variation;
            
            $color = '-'; $size = '-';
            if ($variation && $variation->attributeValues) {
                foreach($variation->attributeValues as $val) {
                    $attrName = strtolower($val->attribute->name ?? '');
                    if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) $color = $val->value;
                    elseif (str_contains($attrName, 'size')) $size = $val->value;
                }
            }

            $retQty = $item->returnItems->sum('returned_qty');
            $retAmt = $item->returnItems->sum('total_price');
            $actualQty = $item->quantity - $retQty;
            $actualAmt = $item->total_price - $retAmt;
            $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);

            $data = [
                $index + 1,
                $sale->sale_number ?? '-',
                $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '-',
                $sale->customer->name ?? 'Walk-in',
                $sale->soldBy->name ?? '-',
                $product->category->name ?? '-',
                $product->brand->name ?? '-',
                $product->season->name ?? '-',
                $product->gender->name ?? '-',
                $product->name ?? '-',
                $product->style_number ?? '-',
                $color,
                $size,
                $item->quantity,
                $item->quantity, // Total S-Qty
                $item->total_price,
                $item->total_price, // Total Sales Amount
                $retQty,
                $retQty, // Total SR-Qty
                $retAmt,
                $retAmt, // Total SR-Amt
                $actualQty,
                $actualQty, // Total AS-Qty
                $isFirst ? $sale->delivery : '-',
                $isFirst ? $sale->discount : '-',
                $isFirst ? ($sale->exchange_amount ?? 0) : '-',
                $actualAmt,
                $isFirst ? $sale->total_amount : '-',
                $isFirst ? ($invoice->paid_amount ?? 0) : '-',
                $isFirst ? ($invoice->due_amount ?? 0) : '-'
            ];
            $sheet->fromArray([$data], NULL, 'A' . $rowNum);
            $rowNum++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'pos_sales_report_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
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

        $query = \App\Models\PosItem::with([
            'pos.customer', 'pos.invoice', 'pos.branch', 'pos.soldBy',
            'product.category', 'product.brand', 'product.season', 'product.gender',
            'variation.attributeValues.attribute', 'returnItems'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->latest()->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.pos.export-pdf', compact('items', 'reportType', 'startDate', 'endDate'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'pos_sales_report_' . date('Ymd_His') . '.pdf';
        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }

    private function generateSaleNumber()
    {
        $nextId = (Pos::max('id') ?? 0) + 1;
        $number = 'POS-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        
        while (Pos::where('sale_number', $number)->exists()) {
            $nextId++;
            $number = 'POS-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        }
        
        return $number;
    }

    /**
     * Deduct stock for a product/variation from branch
     * 
     * @param int $productId
     * @param int|null $variationId
     * @param float $quantity
     * @param int $branchId
     * @return array ['success' => bool, 'message' => string]
     */
    private function deductStock($productId, $variationId, $quantity, $branchId)
    {
        if ($variationId) {
            // Handle variation stock
            $vStock = ProductVariationStock::where('variation_id', $variationId)
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->lockForUpdate()
                ->first();
            
            $availableQty = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
            
            if (!$vStock || $availableQty < $quantity) {
                $product = Product::find($productId);
                $productName = $product ? $product->name : 'Product';
                return [
                    'success' => false,
                    'message' => "Insufficient stock for {$productName}. Available: {$availableQty}, Requested: {$quantity}"
                ];
            }
            
            // Deduct stock
            $vStock->quantity -= $quantity;
            if ($vStock->quantity < 0) $vStock->quantity = 0;
            $vStock->save();
            
            return ['success' => true];
        } else {
            // Handle regular product stock
            $branchStock = BranchProductStock::where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();
            
            if (!$branchStock || $branchStock->quantity < $quantity) {
                $availableQty = $branchStock ? $branchStock->quantity : 0;
                $product = Product::find($productId);
                $productName = $product ? $product->name : 'Product';
                return [
                    'success' => false,
                    'message' => "Insufficient stock for {$productName}. Available: {$availableQty}, Requested: {$quantity}"
                ];
            }
            
            // Deduct stock
            $branchStock->quantity -= $quantity;
            if ($branchStock->quantity < 0) $branchStock->quantity = 0;
            $branchStock->save();
            
            return ['success' => true];
        }
    }

    /**
     * Restore stock for a product/variation to branch
     * 
     * @param int $productId
     * @param int|null $variationId
     * @param float $quantity
     * @param int $branchId
     * @return void
     */
    private function restoreStock($productId, $variationId, $quantity, $branchId)
    {
        if ($variationId) {
            // Handle variation stock restoration
            $vStock = ProductVariationStock::where('variation_id', $variationId)
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->lockForUpdate()
                ->first();
            
            if ($vStock) {
                $vStock->quantity += $quantity;
                $vStock->save();
            } else {
                // Create new variation stock record if it doesn't exist
                ProductVariationStock::create([
                    'variation_id' => $variationId,
                    'branch_id' => $branchId,
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        } else {
            // Handle regular product stock restoration
            $branchStock = BranchProductStock::where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();
            
            if ($branchStock) {
                $branchStock->quantity += $quantity;
                $branchStock->save();
            } else {
                // Create new branch stock record if it doesn't exist
                BranchProductStock::create([
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }
    }

    public function manualSaleCreate()
    {
        $customers = Customer::orderBy('name')->get();
        $branches = Branch::all();
        $products = Product::where('status', 'active')->where('type', 'product')->get();
        $brands = \App\Models\Brand::all();
        $seasons = \App\Models\Season::all();
        $genders = \App\Models\Gender::all();
        $categories = ProductServiceCategory::whereNull('parent_id')->get();
        $shippingMethods = \App\Models\ShippingMethod::orderBy('sort_order')->get();
        
        // Generate next numbers
        $invoiceNo = $this->generateInvoiceNumber();
        $challanNo = str_replace('INV', 'CHA', $invoiceNo);
        $saleNo = $this->generateSaleNumber();

        return view('erp.pos.manualSale.create', compact(
            'customers', 'branches', 'products', 'brands', 'seasons', 
            'genders', 'categories', 'shippingMethods', 'invoiceNo', 
            'challanNo', 'saleNo'
        ));
    }

    public function manualSaleStore(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'sale_date' => 'required|date',
            'sale_type' => 'required|string',
            'invoice_no' => 'required|string',
            'challan_no' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric',
            'paid_amount' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1. STAGE 1: VALDIATE ALL STOCK FIRST
            // We check every item before creating any sale/invoice records
            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $variationId = $item['variation_id'] ?? null;
                $quantity = $item['quantity'];
                $branchId = $request->branch_id;

                if ($variationId) {
                    $vStock = ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $branchId)
                        ->whereNull('warehouse_id')
                        ->first();
                    $availableQty = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
                } else {
                    $branchStock = BranchProductStock::where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->first();
                    $availableQty = $branchStock ? $branchStock->quantity : 0;
                }

                if ($availableQty < $quantity) {
                    $product = Product::find($productId);
                    $productName = $product ? $product->name : 'Product';
                    return response()->json([
                        'success' => false, 
                        'message' => "Insufficient stock for {$productName}. Available: {$availableQty}, Requested: {$quantity}"
                    ], 400); // Return error before any DB records are created
                }
            }

            // 2. STAGE 2: CREATE RECORDS (Only if Stage 1 passed)
            $pos = new Pos();
            
            // Generate numbers inside transaction to ensure uniqueness
            $saleNo = $request->input('sale_no');
            if (empty($saleNo) || Pos::where('sale_number', $saleNo)->exists()) {
                $saleNo = $this->generateSaleNumber();
            }
            $pos->sale_number = $saleNo;

            $challanNo = $request->challan_no;
            if (empty($challanNo) || Pos::where('challan_number', $challanNo)->exists()) {
                $challanNo = str_replace(['INV', 'POS'], 'CHA', $saleNo);
            }
            $pos->challan_number = $challanNo;

            $pos->customer_id = $request->customer_id;
            $pos->branch_id = $request->branch_id;
            $pos->sold_by = auth()->id();
            $pos->sale_date = $request->sale_date;
            $pos->sale_type = $request->sale_type;
            $pos->sub_total = $request->sub_total;
            $pos->discount = $request->discount ?? 0;
            $pos->delivery = $request->delivery_charge ?? 0;
            $pos->total_amount = $request->total_amount;
            $pos->status = 'delivered'; 
            $pos->account_type = $request->account_type;
            $pos->account_number = $request->account_no;
            $pos->remarks = $request->remarks;
            $pos->notes = $request->note;
            $pos->courier_id = $request->courier_id;
            $pos->save();

            // Create Invoice
            $invTemplate = InvoiceTemplate::where('is_default', 1)->first();
            $invoice = new Invoice();
            
            $invoiceNo = $request->invoice_no;
            if (empty($invoiceNo) || Invoice::where('invoice_number', $invoiceNo)->exists()) {
                $invoiceNo = $this->generateInvoiceNumber();
            }
            $invoice->invoice_number = $invoiceNo;

            $invoice->template_id = $invTemplate?->id;
            $invoice->customer_id = $pos->customer_id;
            $invoice->operated_by = auth()->id();
            $invoice->issue_date = $pos->sale_date;
            $invoice->due_date = $pos->sale_date;
            $invoice->subtotal = $pos->sub_total;
            $invoice->tax = 0; 
            $invoice->total_amount = $pos->total_amount;
            $invoice->discount_apply = $pos->discount;
            $invoice->paid_amount = $request->paid_amount;
            $invoice->due_amount = $pos->total_amount - $request->paid_amount;
            $invoice->status = $invoice->due_amount <= 0 ? 'paid' : ($invoice->paid_amount > 0 ? 'partial' : 'unpaid');
            $invoice->note = $pos->notes;
            $invoice->created_by = auth()->id();
            $invoice->save();

            $pos->invoice_id = $invoice->id;
            $pos->save();

            // 3. STAGE 3: PROCESS ITEMS AND DEDUCT STOCK
            foreach ($request->items as $item) {
                // This call now strictly handles deduction because we already validated
                $result = $this->deductStock(
                    $item['product_id'],
                    $item['variation_id'] ?? null,
                    $item['quantity'],
                    $request->branch_id
                );

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }

                // Save POS Item
                PosItem::create([
                    'pos_sale_id' => $pos->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'current_position_type' => 'branch',
                    'current_position_id' => $request->branch_id
                ]);

                // Save Invoice Item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Payment Recording
            if ($request->paid_amount > 0) {
                Payment::create([
                    'customer_id' => $pos->customer_id,
                    'payment_for' => 'pos',
                    'pos_id' => $pos->id,
                    'invoice_id' => $invoice->id,
                    'payment_date' => $pos->sale_date,
                    'amount' => $request->paid_amount,
                    'payment_method' => strtolower($request->account_type) ?: 'cash',
                    'note' => $pos->notes,
                ]);
            }

            // Customer Balance Update
            if ($pos->customer_id) {
                Balance::create([
                    'source_type' => 'customer',
                    'source_id' => $pos->customer_id,
                    'balance' => $pos->total_amount - $request->paid_amount,
                    'description' => 'Manual Sale Entry',
                    'reference' => $pos->sale_number,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sale recorded successfully.', 'redirect' => route('pos.show', $pos->id)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}

