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
        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        return view('erp.pos.addPos', compact('categories', 'branches', 'bankAccounts'));
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
                $createdItem = PosItem::create([
                    'pos_sale_id' => $pos->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'current_position_type' => 'branch', // Explicitly set to branch for POS orders
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
        $query = Pos::with(['customer', 'invoice', 'branch'])
            ->withSum('payments as payments_total', 'amount');

        // Search by sale_number, customer name, phone, email
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('pos.status', $request->input('status'));
        }

        // Filter by invoice status
        if ($request->filled('bill_status')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('status', $request->input('bill_status'));
            });
        }

        // Filter by estimated delivery date
        if ($request->filled('estimated_delivery_date')) {
            $query->whereDate('estimated_delivery_date', $request->input('estimated_delivery_date'));
        }

        // Order by latest created
        $query->orderBy('created_at', 'desc');

        $sales = $query->paginate(10)->withQueryString();
        return view('erp.pos.index', compact('sales'));
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
                $posItem = new PosItem();
                $posItem->pos_sale_id = $pos->id;
                $posItem->product_id = $item['product_id'];
                $posItem->variation_id = $item['variation_id'] ?? null;
                $posItem->quantity = $item['quantity'];
                $posItem->unit_price = $item['unit_price'];
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
        $pos = Pos::find($saleId);
        if (!$pos) {
            return response()->json(['success' => false, 'message' => 'Sale not found.'], 404);
        }
        $request->validate([
            'status' => 'required|string',
        ]);

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
        
        do {
            $number = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $fullNumber = $prefix . $number;
        } while (\App\Models\Invoice::where('invoice_number', $fullNumber)->exists());
        
        return $fullNumber;
    }


    /**
     * Get report data for the modal
     */
    public function getReportData(Request $request)
    {
        try {
            $query = Pos::with(['customer', 'invoice', 'branch']);

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('sale_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('sale_date', '<=', $request->date_to);
            }

            // Status filter
            if ($request->filled('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Payment status filter
            if ($request->filled('payment_status') && $request->payment_status !== '') {
                $query->whereHas('invoice', function ($q) use ($request) {
                    $q->where('status', $request->payment_status);
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

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = Pos::with(['customer', 'invoice', 'branch']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }

        // Branch filter

        $sales = $query->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : [];

        // Validate that at least one column is selected
        if (empty($selectedColumns)) {
            return response()->json(['error' => 'Please select at least one column to export.'], 400);
        }

        // Prepare data for export
        $exportData = [];
        
        // Add headers
        $headers = [];
        $columnMap = [
            'pos_id' => 'POS ID',
            'sale_date' => 'Sale Date',
            'customer' => 'Customer',
            'phone' => 'Phone',
            'branch' => 'Branch',
            'status' => 'Status',
            'payment_status' => 'Payment Status',
            'subtotal' => 'Subtotal',
            'discount' => 'Discount',
            'total' => 'Total',
            'paid_amount' => 'Paid Amount',
            'due_amount' => 'Due Amount'
        ];

        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }
        $exportData[] = $headers;

        // Add data rows
        foreach ($sales as $sale) {
            $row = [];
            foreach ($selectedColumns as $column) {
                switch ($column) {
                    case 'pos_id':
                        $row[] = $sale->sale_number ?? '-';
                        break;
                    case 'sale_date':
                        $row[] = $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') : '-';
                        break;
                    case 'customer':
                        $row[] = $sale->customer ? $sale->customer->name : 'Walk-in Customer';
                        break;
                    case 'phone':
                        $row[] = $sale->customer ? $sale->customer->phone : '-';
                        break;
                    case 'branch':
                        $row[] = $sale->branch ? $sale->branch->name : '-';
                        break;
                    case 'status':
                        $row[] = ucfirst($sale->status ?? '-');
                        break;
                    case 'payment_status':
                        $row[] = $sale->invoice ? ucfirst($sale->invoice->status) : '-';
                        break;
                    case 'subtotal':
                        $row[] = number_format($sale->sub_total, 2);
                        break;
                    case 'discount':
                        $row[] = number_format($sale->discount, 2);
                        break;
                    case 'total':
                        $row[] = number_format($sale->total_amount, 2);
                        break;
                    case 'paid_amount':
                        $row[] = $sale->invoice ? number_format($sale->invoice->paid_amount, 2) : '0.00';
                        break;
                    case 'due_amount':
                        $row[] = $sale->invoice ? number_format($sale->invoice->due_amount, 2) : '0.00';
                        break;
                }
            }
            $exportData[] = $row;
        }

        // Generate filename
        $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Create Excel file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add title
        $sheet->setCellValue('A1', 'Sales Report');
        if (count($headers) > 0) {
            $sheet->mergeCells('A1:' . chr(65 + count($headers) - 1) . '1');
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Add summary info
        $totalSales = $sales->count();
        $totalAmount = $sales->sum('total_amount');
        $paidSales = $sales->filter(function($sale) {
            return $sale->invoice && $sale->invoice->status === 'paid';
        })->count();
        $unpaidSales = $sales->filter(function($sale) {
            return $sale->invoice && $sale->invoice->status === 'unpaid';
        })->count();
        
        if (count($headers) > 0) {
            $sheet->setCellValue('A2', 'Summary: Total Sales: ' . $totalSales . ' | Total Amount: ৳' . number_format($totalAmount, 2) . ' | Paid: ' . $paidSales . ' | Unpaid: ' . $unpaidSales);
            $sheet->mergeCells('A2:' . chr(65 + count($headers) - 1) . '2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F0F8FF');
        }
        
        // Add filters info
        $filterInfo = [];
        if ($request->filled('date_from')) $filterInfo[] = 'From: ' . $request->date_from;
        if ($request->filled('date_to')) $filterInfo[] = 'To: ' . $request->date_to;
        if ($request->filled('status')) $filterInfo[] = 'Status: ' . ucfirst($request->status);
        if ($request->filled('payment_status')) $filterInfo[] = 'Payment Status: ' . ucfirst($request->payment_status);
        
        if (!empty($filterInfo) && count($headers) > 0) {
            $sheet->setCellValue('A3', 'Filters: ' . implode(', ', $filterInfo));
            $sheet->mergeCells('A3:' . chr(65 + count($headers) - 1) . '3');
            $sheet->getStyle('A3')->getFont()->setItalic(true);
        }
        
        // Add headers
        $headerRow = 4;
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . $headerRow, $header);
            $sheet->getStyle(chr(65 + $index) . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle(chr(65 + $index) . $headerRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        }
        
        // Add data
        $dataRow = 5;
        $totalRow = $dataRow;
        foreach ($exportData as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Skip headers as we already added them
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . $dataRow, $value);
            }
            $dataRow++;
        }
        $totalRow = $dataRow; // This will be the row after the last data row
        
        // Add totals row
        if ($sales->count() > 0) {
            $sheet->setCellValue('A' . $totalRow, 'TOTAL');
            $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
            
            // Calculate and add totals for specific columns
            $totalAmount = 0;
            $totalPaidAmount = 0;
            $totalDueAmount = 0;
            
            foreach ($sales as $sale) {
                $totalAmount += $sale->total_amount ?? 0;
                if ($sale->invoice) {
                    $totalPaidAmount += $sale->invoice->paid_amount ?? 0;
                    $totalDueAmount += $sale->invoice->due_amount ?? 0;
                }
            }
            
            // Add totals to the appropriate columns
            foreach ($selectedColumns as $colIndex => $column) {
                $cellAddress = chr(65 + $colIndex) . $totalRow;
                
                switch ($column) {
                    case 'total':
                        $sheet->setCellValue($cellAddress, number_format($totalAmount, 2));
                        $sheet->getStyle($cellAddress)->getFont()->setBold(true);
                        break;
                    case 'paid_amount':
                        $sheet->setCellValue($cellAddress, number_format($totalPaidAmount, 2));
                        $sheet->getStyle($cellAddress)->getFont()->setBold(true);
                        break;
                    case 'due_amount':
                        $sheet->setCellValue($cellAddress, number_format($totalDueAmount, 2));
                        $sheet->getStyle($cellAddress)->getFont()->setBold(true);
                        break;
                    default:
                        // For other columns, leave empty or add count if it's the first column
                        if ($colIndex === 0) {
                            $sheet->setCellValue($cellAddress, $sales->count() . ' Sales');
                            $sheet->getStyle($cellAddress)->getFont()->setBold(true);
                        }
                        break;
                }
            }
            
            // Style the totals row
            $sheet->getStyle('A' . $totalRow . ':' . chr(65 + count($headers) - 1) . $totalRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E8F4FD');
        }
        
        // Auto-size columns
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Create writer and output
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);
        
        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Pos::with(['customer', 'invoice', 'branch']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }

        // Branch filter

        $sales = $query->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : [];

        // Validate that at least one column is selected
        if (empty($selectedColumns)) {
            return response()->json(['error' => 'Please select at least one column to export.'], 400);
        }

        // Prepare data for export
        $columnMap = [
            'pos_id' => 'POS ID',
            'sale_date' => 'Sale Date',
            'customer' => 'Customer',
            'phone' => 'Phone',
            'branch' => 'Branch',
            'status' => 'Status',
            'payment_status' => 'Payment Status',
            'subtotal' => 'Subtotal',
            'discount' => 'Discount',
            'total' => 'Total',
            'paid_amount' => 'Paid Amount',
            'due_amount' => 'Due Amount'
        ];

        $headers = [];
        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }

        // Calculate summary
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

        // Generate filename
        $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.pdf';

        // Create PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.pos.report-pdf', [
            'sales' => $sales,
            'headers' => $headers,
            'selectedColumns' => $selectedColumns,
            'summary' => $summary,
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
            ]
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($filename);
    }

    private function generateSaleNumber()
    {
        $today = now();
        $dateString = $today->format('dmy');
        
        $lastSale = Pos::latest()->first();
        if (!$lastSale) {
            return "sfp-{$dateString}01";
        }
        $serialNumber = str_pad($lastSale->id + 1, 2, '0', STR_PAD_LEFT);
        
        return "sfp-{$dateString}{$serialNumber}";
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
}
