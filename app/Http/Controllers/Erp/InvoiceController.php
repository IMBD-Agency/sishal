<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\GeneralSetting;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf; // Add this at the top

class InvoiceController extends Controller
{
    public function invoiceSearch(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view invoices') && !auth()->user()->hasPermissionTo('view internal invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $q = $request->input('q');
        $query = Invoice::query()->with('customer');

        // Branch Isolation
        $branchId = $this->getRestrictedBranchId();
        if ($branchId) {
            $query->whereHas('pos', function($pq) use ($branchId) {
                $pq->where('branch_id', $branchId);
            });
        }

        if ($q) {
            $query->where(function($sub) use ($q) {
                // Always search by invoice number (works for walk-in too)
                $sub->where('invoice_number', 'like', "%$q%");

                // Also search by customer name/phone if a customer exists
                $sub->orWhere(function($cs) use ($q) {
                    $cs->whereNotNull('customer_id')
                       ->whereHas('customer', function($cq) use ($q) {
                           $cq->where('name', 'like', "%$q%")
                              ->orWhere('phone', 'like', "%$q%");
                       });
                });
            });
        }

        $perPage = 30;
        $invoices = $query->latest()->paginate($perPage);

        return response()->json([
            'results'     => $invoices->items(),
            'total_count' => $invoices->total()
        ]);
    }
    public function templateList(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view invoices') && !auth()->user()->hasPermissionTo('view internal invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $query = InvoiceTemplate::query();

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%$search%");
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->all());
        $filters = $request->only(['search']);
        return view('erp.invoiceTemplate.invoiceTemplateList', compact('templates', 'filters'));
    }

    public function storeTemplate(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'footer_note' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);
        $validated['is_default'] = $request->has('is_default') ? 1 : 0;
        if ($validated['is_default'] == 1) {
            InvoiceTemplate::where('is_default', 1)->update(['is_default' => 0]);
        }
        InvoiceTemplate::create($validated);
        return redirect()->route('invoice.template.list')->with('success', 'Template created successfully.');
    }

    public function updateTemplate(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'footer_note' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);
        $validated['is_default'] = $request->has('is_default') ? 1 : 0;
        if ($validated['is_default'] == 1) {
            InvoiceTemplate::where('is_default', 1)->where('id', '!=', $id)->update(['is_default' => 0]);
        }
        $template = InvoiceTemplate::findOrFail($id);
        $template->update($validated);
        return redirect()->route('invoice.template.list')->with('success', 'Template updated successfully.');
    }

    public function deleteTemplate($id)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $template = InvoiceTemplate::findOrFail($id);
        $template->delete();
        return redirect()->route('invoice.template.list')->with('success', 'Template deleted successfully.');
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('view invoices') && !auth()->user()->can('view internal invoices')) {
            abort(403, 'Unauthorized action.');
        }
        
        $query = $this->getFilteredQuery($request);

        $invoices = $query->distinct()
            ->with(['order', 'pos.branch', 'customer', 'salesman'])
            ->orderBy('invoices.created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());
        $statuses = ['unpaid', 'partial', 'paid'];
        $filters = $request->only(['search', 'status', 'issue_date', 'due_date', 'customer_id']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        $customersQuery = \App\Models\Customer::query();
        if ($restrictedBranchId) {
            $customersQuery->where('branch_id', $restrictedBranchId);
        }
        $customers = $customersQuery->orderBy('name')->take(200)->get();
        return view('erp.invoices.invoicelist', compact('invoices', 'statuses', 'filters', 'customers'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $restrictedBranchId = $this->getRestrictedBranchId();
        $customersQuery = \App\Models\Customer::query();
        if ($restrictedBranchId) {
            $customersQuery->where('branch_id', $restrictedBranchId);
        }
        $customers = $customersQuery->orderBy('name')->take(200)->get();
        $products = \App\Models\Product::orderBy('name')->take(100)->get();
        $templates = \App\Models\InvoiceTemplate::orderBy('name')->get();
        $generalSettings = \App\Models\GeneralSetting::first();
        $tax_rate = $generalSettings ? $generalSettings->tax_rate : 0;
        return view('erp.invoices.create', compact('customers', 'products', 'templates', 'tax_rate'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'template_id' => 'required|exists:invoice_templates,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'send_date' => 'nullable|date',
            'note' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.variation_id' => 'nullable|integer|min:1',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'billing_address_1' => 'required|string',
            'billing_address_2' => 'nullable|string',
            'billing_city' => 'nullable|string',
            'billing_state' => 'nullable|string',
            'billing_country' => 'nullable|string',
            'billing_zip_code' => 'nullable|string',
            'shipping_address_1' => 'nullable|string',
            'shipping_address_2' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_state' => 'nullable|string',
            'shipping_country' => 'nullable|string',
            'shipping_zip_code' => 'nullable|string',
        ]);
        \DB::beginTransaction();
        try {
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Calculate subtotal from items
            $subtotal = collect($request->items)->sum(function($item) {
                return $item['total_price'];
            });
            
            // Get tax rate from general settings
            $generalSettings = GeneralSetting::first();
            $taxRate = $generalSettings ? ($generalSettings->tax_rate / 100) : 0.00;
            $tax = round($subtotal * $taxRate, 2);
            
            // Calculate total amount including tax
            $totalAmount = $subtotal + $tax - $request->input('discount_apply', 0);
            $paidAmount = $request->input('paid_amount', 0);
            $dueAmount = $totalAmount - $paidAmount;
            
            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceNumber,
                'template_id' => $request->template_id,
                'customer_id' => $request->customer_id,
                'operated_by' => auth()->id(),
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'send_date' => $request->send_date,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'discount_apply' => $request->input('discount_apply', 0),
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'status' => 'unpaid',
                'note' => $request->note,
                'footer_text' => $request->footer_text,
                'created_by' => auth()->id(),
            ]);
            foreach ($request->items as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);
            }
            
            // Create Invoice Address prioritizing request data
            \App\Models\InvoiceAddress::create([
                'invoice_id' => $invoice->id,
                'billing_address_1' => $request->billing_address_1,
                'billing_address_2' => $request->billing_address_2,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_country' => $request->billing_country,
                'billing_zip_code' => $request->billing_zip_code,
                'shipping_address_1' => $request->shipping_address_1 ?? $request->billing_address_1,
                'shipping_address_2' => $request->shipping_address_2 ?? $request->billing_address_2,
                'shipping_city' => $request->shipping_city ?? $request->billing_city,
                'shipping_state' => $request->shipping_state ?? $request->billing_state,
                'shipping_country' => $request->shipping_country ?? $request->billing_country,
                'shipping_zip_code' => $request->shipping_zip_code ?? $request->billing_zip_code,
            ]);

            // Create payment if paid_amount > 0
            if ($paidAmount > 0) {
                \App\Models\Payment::create([
                    'payment_for' => 'invoice',
                    'invoice_id' => $invoice->id,
                    'payment_date' => now(),
                    'amount' => $paidAmount,
                    'customer_id' => $invoice->customer_id,
                ]);
            }

            // Correct status logic
            if ($paidAmount == 0) {
                $invoice->status = 'unpaid';
            } elseif ($paidAmount >= $totalAmount) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            $invoice->save();

            \DB::commit();
            return redirect()->route('invoice.list')->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $invoice = Invoice::findOrFail($id);
        $this->checkGranularAccess($invoice);
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'template_id' => 'required|exists:invoice_templates,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'send_date' => 'nullable|date',
            'note' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.variation_id' => 'nullable|integer|min:1',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'billing_address_1' => 'required|string',
            'billing_address_2' => 'nullable|string',
            'billing_city' => 'nullable|string',
            'billing_state' => 'nullable|string',
            'billing_country' => 'nullable|string',
            'billing_zip_code' => 'nullable|string',
            'shipping_address_1' => 'nullable|string',
            'shipping_address_2' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_state' => 'nullable|string',
            'shipping_country' => 'nullable|string',
            'shipping_zip_code' => 'nullable|string',
        ]);
        \DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Calculate subtotal from items
            $subtotal = collect($request->items)->sum(function($item) {
                return $item['total_price'];
            });
            
            // Get tax rate from general settings
            $generalSettings = GeneralSetting::first();
            $taxRate = $generalSettings ? ($generalSettings->tax_rate / 100) : 0.00;
            $tax = round($subtotal * $taxRate, 2);
            
            // Calculate total amount including tax
            $discount = $request->input('discount_apply', 0);
            $totalAmount = $subtotal + $tax - $discount;
            $paidAmount = $invoice->paid_amount;
            $dueAmount = $totalAmount - $paidAmount;

            $invoice->update([
                'template_id' => $request->template_id,
                'customer_id' => $request->customer_id,
                'operated_by' => auth()->id(),
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'send_date' => $request->send_date,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'discount_apply' => $discount,
                'due_amount' => $dueAmount,
                'note' => $request->note,
                'footer_text' => $request->footer_text,
            ]);
            // Remove old items and add new
            $invoice->items()->delete();
            foreach ($request->items as $item) {
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);
            }
            // Update address
            $invoice->invoiceAddress()->delete();
            \App\Models\InvoiceAddress::create([
                'invoice_id' => $invoice->id,
                'billing_address_1' => $request->billing_address_1,
                'billing_address_2' => $request->billing_address_2,
                'billing_city' => $request->billing_city,
                'billing_state' => $request->billing_state,
                'billing_country' => $request->billing_country,
                'billing_zip_code' => $request->billing_zip_code,
                'shipping_address_1' => $request->shipping_address_1,
                'shipping_address_2' => $request->shipping_address_2,
                'shipping_city' => $request->shipping_city,
                'shipping_state' => $request->shipping_state,
                'shipping_country' => $request->shipping_country,
                'shipping_zip_code' => $request->shipping_zip_code,
            ]);
            // Update status
            if ($invoice->paid_amount == 0) {
                $invoice->status = 'unpaid';
            } elseif ($invoice->paid_amount >= $totalAmount) {
                $invoice->status = 'paid';
                $invoice->due_amount = 0;
            } else {
                $invoice->status = 'partial';
            }
            $invoice->save();
            \DB::commit();
            return redirect()->route('invoice.list')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $invoice = \App\Models\Invoice::with(['customer', 'invoiceAddress', 'items.product', 'items.variation'])->findOrFail($id);
        $this->checkGranularAccess($invoice);
        $templates = \App\Models\InvoiceTemplate::orderBy('name')->get();
        $generalSettings = \App\Models\GeneralSetting::first();
        $tax_rate = $generalSettings ? $generalSettings->tax_rate : 0;
        return view('erp.invoices.edit', compact('invoice', 'templates', 'tax_rate'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view invoices') && !auth()->user()->hasPermissionTo('view internal invoices')) {
            abort(403, 'Unauthorized action.');
        }
        
        $invoice = Invoice::with('pos','payments','customer','invoiceAddress','salesman','items.product','items.variation')->findOrFail($id);
        
        // Granular check for single invoice view
        $user = auth()->user();
        if (!$user->hasRole('Super Admin')) {
            $canViewPos = $user->can('view invoices');
            $canViewEcommerce = $user->can('view internal invoices');
            
            if ($canViewPos && !$canViewEcommerce && $invoice->order) {
                abort(403, 'Unauthorized to view ecommerce invoices.');
            }
            if (!$canViewPos && $canViewEcommerce && !$invoice->order) {
                abort(403, 'Unauthorized to view POS/Manual invoices.');
            }
        }
        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        $order = \App\Models\Order::where('invoice_id', $invoice?->id)->first();
        return view('erp.invoices.show', compact('invoice', 'bankAccounts', 'order'));
    }

    public function addPayment($invId, Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $invoice = Invoice::findOrFail($invId);
        $this->checkGranularAccess($invoice);

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
        $payment->payment_for = 'invoice';
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
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'unpaid';
        }
        $invoice->save();

        return response()->json(['success' => true, 'message' => 'Payment added successfully.']);
    }

    public function print(Request $request, $invoice_number)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }
        $invoice = Invoice::with('items.product', 'items.variation')->where('invoice_number', $invoice_number)->first();
        if(!$invoice)
        {
            return redirect()->route('invoice.print', ['invoice_number' => 'notfound'])->with('error', 'Invoice not found.');
        }
        $this->checkGranularAccess($invoice);
        $template = InvoiceTemplate::find($invoice->template_id);
        $general_settings = GeneralSetting::first();
        // Fetch related online order (if any) for delivery amount visibility in print view
        $order = \App\Models\Order::where('invoice_id', $invoice?->id)->first();

        $action = $request->action;

        // Calculate tax if not already calculated
        if (!$invoice->tax && $general_settings && $general_settings->tax_rate > 0) {
            $taxRate = $general_settings->tax_rate / 100;
            $invoice->tax = round($invoice->subtotal * $taxRate, 2);
        }

        // Generate QR code as SVG (no imagick required)
        $printUrl = route('invoice.print', ['invoice_number' => $invoice->invoice_number]);
        $qrCodeSvg = QrCode::format('svg')->size(60)->generate($printUrl);

        // PDF download logic
        if ($action == 'download') {
            $pdf = Pdf::loadView('erp.invoices.print', compact('invoice', 'template', 'action', 'qrCodeSvg', 'general_settings', 'order'));
            return $pdf->download('invoice-'.$invoice->invoice_number.'.pdf');
        }

        return view('erp.invoices.print', compact('invoice', 'template', 'action', 'qrCodeSvg', 'general_settings', 'order'));
    }

    /**
     * Get report data for the modal
     */
    public function getReportData(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view invoices') && !auth()->user()->hasPermissionTo('view internal invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Invoice::with(['customer', 'salesman', 'order']);
        $this->applyGranularFilters($query);

        // Date range filter for issue date
        if ($request->filled('issue_date_from')) {
            $query->whereDate('issue_date', '>=', $request->issue_date_from);
        }
        if ($request->filled('issue_date_to')) {
            $query->whereDate('issue_date', '<=', $request->issue_date_to);
        }

        // Date range filter for due date
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->get();

        // Transform data for frontend
        $transformedInvoices = $invoices->map(function ($invoice) {
            return [
                'invoice_number' => $invoice->invoice_number,
                'order_id' => $invoice->order ? $invoice->order->order_number : '-',
                'customer_name' => $invoice->order ? $invoice->order->name : (optional($invoice->customer)->name ?? 'Walk-in Customer'),
                'salesman_name' => trim((optional($invoice->salesman)->first_name ?? '') . ' ' . (optional($invoice->salesman)->last_name ?? '')) ?: 'System',
                'issue_date' => $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d-m-Y') : '-',
                'due_date' => $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') : '-',
                'status' => $invoice->status,
                'subtotal' => number_format($invoice->subtotal, 2),
                'tax' => number_format($invoice->tax, 2),
                'discount' => number_format($invoice->discount_apply, 2),
                'total_amount' => number_format($invoice->total_amount, 2),
                'paid_amount' => number_format($invoice->paid_amount, 2),
                'due_amount' => number_format($invoice->due_amount, 2),
            ];
        });

        // Calculate summary statistics
        $summary = [
            'total_invoices' => $invoices->count(),
            'total_amount' => number_format($invoices->sum('total_amount'), 2),
            'paid_invoices' => $invoices->where('status', 'paid')->count(),
            'unpaid_invoices' => $invoices->where('status', 'unpaid')->count(),
            'partial_invoices' => $invoices->where('status', 'partial')->count(),
        ];

        return response()->json([
            'invoices' => $transformedInvoices,
            'summary' => $summary
        ]);
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $query = $this->getFilteredQuery($request);
        $invoices = $query->with(['customer', 'salesman', 'order'])->orderBy('invoices.created_at', 'desc')->get();
        
        $columnMap = [
            'invoice_number' => 'Invoice Number',
            'order_id' => 'Order ID',
            'customer' => 'Customer',
            'salesman' => 'Salesman',
            'issue_date' => 'Issue Date',
            'due_date' => 'Due Date',
            'status' => 'Status',
            'subtotal' => 'Subtotal',
            'tax' => 'Tax',
            'discount' => 'Discount',
            'total' => 'Total',
            'paid_amount' => 'Paid Amount',
            'due_amount' => 'Due Amount'
        ];

        // Default to all columns if none selected
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : array_keys($columnMap);

        // Prepare data for export
        $exportData = [];
        
        // Add headers
        $headers = [];
        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }
        $exportData[] = $headers;

        // Add data rows
        foreach ($invoices as $invoice) {
            $row = [];
            foreach ($selectedColumns as $column) {
                switch ($column) {
                    case 'invoice_number':
                        $row[] = $invoice->invoice_number ?? '-';
                        break;
                    case 'order_id':
                        $row[] = $invoice->order ? $invoice->order->order_number : '-';
                        break;
                    case 'customer':
                        $row[] = $invoice->order ? $invoice->order->name : (optional($invoice->customer)->name ?? 'Walk-in Customer');
                        break;
                    case 'salesman':
                        $row[] = trim((optional($invoice->salesman)->first_name ?? '') . ' ' . (optional($invoice->salesman)->last_name ?? '')) ?: 'System';
                        break;
                    case 'issue_date':
                        $row[] = $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d-m-Y') : '-';
                        break;
                    case 'due_date':
                        $row[] = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') : '-';
                        break;
                    case 'status':
                        $row[] = ucfirst($invoice->status ?? '-');
                        break;
                    case 'subtotal':
                        $row[] = number_format($invoice->subtotal, 2);
                        break;
                    case 'tax':
                        $row[] = number_format($invoice->tax, 2);
                        break;
                    case 'discount':
                        $row[] = number_format($invoice->discount_apply, 2);
                        break;
                    case 'total':
                        $row[] = number_format($invoice->total_amount, 2);
                        break;
                    case 'paid_amount':
                        $row[] = number_format($invoice->paid_amount, 2);
                        break;
                    case 'due_amount':
                        $row[] = number_format($invoice->due_amount, 2);
                        break;
                }
            }
            $exportData[] = $row;
        }

        // Generate filename
        $filename = 'invoice_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Create Excel file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add title
        $sheet->setCellValue('A1', 'Invoice Report');
        if (count($headers) > 0) {
            $sheet->mergeCells('A1:' . chr(65 + count($headers) - 1) . '1');
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Add summary info
        $totalInvoices = $invoices->count();
        $totalAmount = $invoices->sum('total_amount');
        $paidInvoices = $invoices->where('status', 'paid')->count();
        $unpaidInvoices = $invoices->where('status', 'unpaid')->count();
        $partialInvoices = $invoices->where('status', 'partial')->count();
        
        if (count($headers) > 0) {
            $sheet->setCellValue('A2', 'Summary: Total Invoices: ' . $totalInvoices . ' | Total Amount: ৳' . number_format($totalAmount, 2) . ' | Paid: ' . $paidInvoices . ' | Unpaid: ' . $unpaidInvoices . ' | Partial: ' . $partialInvoices);
            $sheet->mergeCells('A2:' . chr(65 + count($headers) - 1) . '2');
        }
        $sheet->getStyle('A2')->getFont()->setSize(10);
        
        // Add data starting from row 4
        $row = 4;
        foreach ($exportData as $dataRow) {
            $col = 'A';
            foreach ($dataRow as $cell) {
                $sheet->setCellValue($col . $row, $cell);
                $col++;
            }
            $row++;
        }
        
        // Style header row
        $headerRow = 4;
        $sheet->getStyle('A' . $headerRow . ':' . chr(65 + count($headers) - 1) . $headerRow)
            ->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':' . chr(65 + count($headers) - 1) . $headerRow)
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Auto-size columns
        foreach (range('A', chr(65 + count($headers) - 1)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $query = $this->getFilteredQuery($request);
        $invoices = $query->with(['customer', 'salesman', 'order'])->orderBy('invoices.created_at', 'desc')->get();
        
        $columnMap = [
            'invoice_number' => 'Invoice #',
            'order_id' => 'Order ID',
            'customer' => 'Customer',
            'salesman' => 'Salesman',
            'issue_date' => 'Issue Date',
            'due_date' => 'Due Date',
            'status' => 'Status',
            'subtotal' => 'Subtotal',
            'tax' => 'Tax',
            'discount' => 'Discount',
            'total' => 'Total',
            'paid_amount' => 'Paid',
            'due_amount' => 'Due'
        ];

        // Default to all columns if none selected
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : array_keys($columnMap);

        $headers = [];
        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }

        // Calculate summary
        $summary = [
            'total_invoices' => $invoices->count(),
            'total_amount' => number_format($invoices->sum('total_amount'), 2),
            'paid_invoices' => $invoices->where('status', 'paid')->count(),
            'unpaid_invoices' => $invoices->where('status', 'unpaid')->count(),
            'partial_invoices' => $invoices->where('status', 'partial')->count(),
        ];

        // Generate filename
        $filename = 'invoice_report_' . date('Y-m-d_H-i-s') . '.pdf';

        // Create PDF using DomPDF
        $pdf = Pdf::loadView('erp.invoices.invoice-report-pdf', [
            'invoices' => $invoices,
            'headers' => $headers,
            'selectedColumns' => $selectedColumns,
            'summary' => $summary,
            'filters' => [
                'issue_date_from' => $request->issue_date_from,
                'issue_date_to' => $request->issue_date_to,
                'due_date_from' => $request->due_date_from,
                'due_date_to' => $request->due_date_to,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'customer_id' => $request->customer_id,
            ]
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($filename);
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Invoice::query();

        // Join salesman (user) and employee for phone
        $query->leftJoin('users as salesman', 'invoices.created_by', '=', 'salesman.id')
              ->leftJoin('employees as emp', 'salesman.id', '=', 'emp.user_id')
              ->select('invoices.*');

        // Join customer for search/filter
        $query->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id');

        // Search by id, customer, salesman
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('invoices.id', 'like', "%$search%")
                ->orWhere('invoices.invoice_number', 'like', "%$search%")
                    ->orWhere('customers.name', 'like', "%$search%")
                    ->orWhere('customers.email', 'like', "%$search%")
                    ->orWhere('customers.phone', 'like', "%$search%")
                    ->orWhere('salesman.first_name', 'like', "%$search%")
                    ->orWhere('salesman.last_name', 'like', "%$search%")
                    ->orWhere('salesman.email', 'like', "%$search%")
                    ->orWhere('emp.phone', 'like', "%$search%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('invoices.status', $status);
        }

        // Filter by issue_date (Exact)
        if ($issueDate = $request->input('issue_date')) {
            $query->whereDate('invoices.issue_date', $issueDate);
        }

        // Filter by due_date (Exact)
        if ($dueDate = $request->input('due_date')) {
            $query->whereDate('invoices.due_date', $dueDate);
        }

        // Filter by customer
        if ($customerId = $request->input('customer_id')) {
            $query->where('invoices.customer_id', $customerId);
        }

        $this->applyGranularFilters($query);

        // Filter by source
        if ($source = $request->input('source')) {
            if ($source == 'pos') {
                $query->whereHas('pos');
            } elseif ($source == 'ecommerce') {
                $query->whereHas('order');
            } elseif ($source == 'manual') {
                $query->whereDoesntHave('pos')->whereDoesntHave('order');
            }
        }

        return $query;
    }

    /**
     * Apply granular source-based filtering based on user permissions
     */
    private function applyGranularFilters($query)
    {
        $user = auth()->user();
        
        // ONLY bypass for real Super Admin role. (is_admin is just a general ERP login flag)
        if ($user->hasRole('Super Admin') || $user->hasRole('SuperAdmin')) {
            return;
        }

        $canViewPos = $user->can('view invoices');
        $canViewEcommerce = $user->can('view internal invoices');

        if ($canViewPos && !$canViewEcommerce) {
            // Can see shop sales and manual invoices, but NOT ecommerce orders
            $query->whereDoesntHave('order');
        } elseif (!$canViewPos && $canViewEcommerce) {
            // Can ONLY see ecommerce orders
            $query->whereHas('order');
        }
    }

    /**
     * Check if the user has granular access to a specific invoice instance
     */
    private function checkGranularAccess($invoice)
    {
        $user = auth()->user();
        // ONLY bypass for real Super Admin role.
        if ($user->hasRole('Super Admin') || $user->hasRole('SuperAdmin')) {
            return;
        }

        $canViewPos = $user->can('view invoices');
        $canViewEcommerce = $user->can('view internal invoices');

        if ($canViewPos && !$canViewEcommerce && $invoice->order) {
            abort(403, 'Unauthorized to access ecommerce invoices.');
        }
        if (!$canViewPos && $canViewEcommerce && !$invoice->order) {
            abort(403, 'Unauthorized to access POS/Manual invoices.');
        }
    }

    /**
     * Delete an invoice and all its related records.
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasPermissionTo('delete invoices')) {
            abort(403, 'Unauthorized action.');
        }

        $invoice = Invoice::with(['items', 'payments', 'pos.items', 'pos.payments', 'order.items'])->findOrFail($id);
        
        \DB::beginTransaction();
        try {
            $this->deleteSingleInvoice($invoice);
            \DB::commit();
            return redirect()->route('invoice.list')->with('success', 'Invoice #' . $invoice->invoice_number . ' and all related records deleted and reversed successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasPermissionTo('delete invoices')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No invoices selected.']);
        }

        \DB::beginTransaction();
        try {
            $invoices = Invoice::whereIn('id', $ids)->with(['items', 'payments', 'pos.items', 'pos.payments', 'order.items'])->get();
            foreach ($invoices as $invoice) {
                $this->deleteSingleInvoice($invoice);
            }
            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Selected invoices deleted successfully.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete invoices: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper to delete a single invoice, reversing all related data (POS, online orders, cash balances, journals, etc.)
     */
    private function deleteSingleInvoice($invoice)
    {
        // 1. If linked to a POS sale, delegate to POS deletion flow
        if ($invoice->pos) {
            $pos = $invoice->pos;
            
            // Restore stock for all sold items
            foreach ($pos->items as $item) {
                if ($item->parent_item_id === null) {
                    $this->restoreStock($item->product_id, $item->variation_id, $item->quantity, $pos->branch_id);
                }
            }

            // Reverse FinancialAccount balance for each payment
            foreach ($pos->payments as $payment) {
                if ($payment->account_id && $payment->amount > 0) {
                    $finAcc = \App\Models\FinancialAccount::find($payment->account_id);
                    if ($finAcc) {
                        $finAcc->balance -= $payment->amount;
                        if ($finAcc->balance < 0) $finAcc->balance = 0;
                        $finAcc->save();
                    }
                }
            }

            // Remove Customer Balance entries linked to this sale
            if ($pos->customer_id) {
                \App\Models\Balance::where('source_type', 'customer')
                    ->where('source_id', $pos->customer_id)
                    ->where('reference', $pos->sale_number)
                    ->delete();
            }

            // Delete related Journal entries (Double-Entry Accounting)
            $voucherNo = 'SAL-' . str_pad($pos->id, 6, '0', STR_PAD_LEFT);
            $journal = \App\Models\Journal::where('voucher_no', 'like', $voucherNo . '%')
                ->orWhere('reference', $pos->sale_number)
                ->first();
            if ($journal) {
                $journal->entries()->delete();
                $journal->delete();
            }
            $manualVoucherNo = 'SAL-M-' . str_pad($pos->id, 6, '0', STR_PAD_LEFT);
            $manualJournal = \App\Models\Journal::where('voucher_no', $manualVoucherNo)->first();
            if ($manualJournal) {
                $manualJournal->entries()->delete();
                $manualJournal->delete();
            }

            // Delete POS payments and items
            $pos->payments()->delete();
            $pos->items()->delete();
            
            // Delete POS record
            $pos->delete();
        }

        // 2. If linked to an online order, delegate to Order deletion flow
        elseif ($invoice->order) {
            $order = $invoice->order;
            
            // Check if order can be deleted based on status (Only pending or cancelled)
            $deletableStatuses = ['pending', 'cancelled'];
            if (!in_array($order->status, $deletableStatuses)) {
                throw new \Exception('Cannot delete invoice linked to online order ' . $order->order_number . ' with status: ' . ucfirst($order->status) . '. Only pending or cancelled orders can be deleted.');
            }

            // Restore stock for each order item (only if not already cancelled)
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $this->restoreStockForOrderItem($item);
                }
            }

            // Delete related order items and payments
            $order->items()->delete();
            $order->payments()->delete();
            $order->delete();
        }

        // 3. Otherwise (pure manual invoice), reverse manual payments
        else {
            foreach ($invoice->payments as $payment) {
                // Revert Customer Balance
                if ($payment->customer_id) {
                    $balance = \App\Models\Balance::where('source_type', 'customer')
                        ->where('source_id', $payment->customer_id)
                        ->first();
                    if ($balance) {
                        $balance->balance += $payment->amount; // Add back the amount we previously deducted
                        $balance->save();
                    }
                }

                // Revert Financial Account balance if account_id exists
                if ($payment->account_id) {
                    $account = \App\Models\FinancialAccount::find($payment->account_id);
                    if ($account) {
                        $account->balance -= $payment->amount;
                        if ($account->balance < 0) $account->balance = 0;
                        $account->save();
                    }
                }

                // Delete Associated Journal and Journal Entries
                if ($payment->payment_reference) {
                    $journal = \App\Models\Journal::where('reference', $payment->payment_reference)->first();
                    if ($journal) {
                        $journal->entries()->delete();
                        $journal->delete();
                    }
                }

                // Delete payment
                $payment->delete();
            }
        }

        // Delete invoice items
        $invoice->items()->delete();

        // Delete invoice address
        if ($invoice->invoiceAddress) {
            $invoice->invoiceAddress()->delete();
        }

        // Delete the invoice itself
        $invoice->delete();
    }

    /**
     * Restore stock for a product/variation to branch
     */
    private function restoreStock($productId, $variationId, $quantity, $branchId)
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return;
        }

        if ($product->type === 'combo') {
            foreach ($product->comboItems as $comboItem) {
                $itemVariationId = $comboItem->variation_id;
                $itemQuantity = $comboItem->quantity * $quantity;
                $this->restoreStock($comboItem->product_id, $itemVariationId, $itemQuantity, $branchId);
            }
            return;
        }

        if ($variationId) {
            // Verify that the variation actually exists
            if (!\App\Models\ProductVariation::where('id', $variationId)->exists()) {
                return;
            }

            $vStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->lockForUpdate()
                ->first();
            
            if ($vStock) {
                $vStock->quantity += $quantity;
                $vStock->save();
            } else {
                \App\Models\ProductVariationStock::create([
                    'variation_id' => $variationId,
                    'branch_id' => $branchId,
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        } else {
            $branchStock = \App\Models\BranchProductStock::where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();
            
            if ($branchStock) {
                $branchStock->quantity += $quantity;
                $branchStock->save();
            } else {
                \App\Models\BranchProductStock::create([
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Restore stock for an order item
     */
    private function restoreStockForOrderItem($item)
    {
        $productId = $item->product_id;
        $variationId = $item->variation_id;
        $quantity = $item->quantity;
        $fromType = $item->current_position_type;
        $fromId = $item->current_position_id;
        $userId = auth()->id() ?? 1;

        // If no stock source, try to find existing stock or use default branch
        if (!$fromType || !$fromId) {
            if ($variationId) {
                $existingStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                    ->whereNotNull('branch_id')
                    ->whereNull('warehouse_id')
                    ->first();
                if ($existingStock) {
                    $fromType = 'branch';
                    $fromId = $existingStock->branch_id;
                } else {
                    $fromType = 'branch';
                    $fromId = \App\Models\Branch::first()->id ?? 1;
                }
            } else {
                $existingStock = \App\Models\BranchProductStock::where('product_id', $productId)
                    ->first();
                if ($existingStock) {
                    $fromType = 'branch';
                    $fromId = $existingStock->branch_id;
                } else {
                    $fromType = 'branch';
                    $fromId = \App\Models\Branch::first()->id ?? 1;
                }
            }
        }

        // For products with variations, restore to variation-level stock
        if ($variationId) {
            if ($fromType === 'warehouse') {
                $variationStock = \App\Models\ProductVariationStock::firstOrCreate(
                    [
                        'variation_id' => $variationId,
                        'warehouse_id' => $fromId,
                        'branch_id' => null
                    ],
                    [
                        'quantity' => 0,
                        'updated_by' => $userId,
                        'last_updated_at' => now()
                    ]
                );
                $variationStock->quantity += $quantity;
                $variationStock->updated_by = $userId;
                $variationStock->last_updated_at = now();
                $variationStock->save();
            } elseif ($fromType === 'branch') {
                $variationStock = \App\Models\ProductVariationStock::firstOrCreate(
                    [
                        'variation_id' => $variationId,
                        'branch_id' => $fromId,
                        'warehouse_id' => null
                    ],
                    [
                        'quantity' => 0,
                        'updated_by' => $userId,
                        'last_updated_at' => now()
                    ]
                );
                $variationStock->quantity += $quantity;
                $variationStock->updated_by = $userId;
                $variationStock->last_updated_at = now();
                $variationStock->save();
            }
        } else {
            // For products without variations, restore to product-level stock
            if ($fromType === 'branch') {
                $stock = \App\Models\BranchProductStock::firstOrCreate(
                    ['branch_id' => $fromId, 'product_id' => $productId],
                    ['quantity' => 0, 'updated_by' => $userId]
                );
            } elseif ($fromType === 'warehouse') {
                $stock = \App\Models\WarehouseProductStock::firstOrCreate(
                    ['warehouse_id' => $fromId, 'product_id' => $productId],
                    ['quantity' => 0, 'updated_by' => $userId]
                );
            } elseif ($fromType === 'employee') {
                $stock = \App\Models\EmployeeProductStock::firstOrCreate(
                    ['employee_id' => $fromId, 'product_id' => $productId],
                    ['quantity' => 0, 'issued_by' => $userId, 'updated_by' => $userId]
                );
            } else {
                return;
            }

            $stock->quantity += $quantity;
            $stock->updated_by = $userId;
            if (isset($stock->last_updated_at)) {
                $stock->last_updated_at = now();
            }
            $stock->save();
        }
    }

    private function generateInvoiceNumber()
    {
        $generalSettings = GeneralSetting::first();
        $prefix = $generalSettings ? $generalSettings->invoice_prefix : 'INV';
        
        $lastInvoice = Invoice::latest('id')->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        
        // Format: INV-000001
        return $prefix . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
