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
    public function templateList(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view invoice list template')) {
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
        $template = InvoiceTemplate::findOrFail($id);
        $template->delete();
        return redirect()->route('invoice.template.list')->with('success', 'Template deleted successfully.');
    }

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view invoice list')) {
            abort(403, 'Unauthorized action.');
        }
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
                    ->orWhere('emp.phone', 'like', "%$search%")
                    ;
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('invoices.status', $status);
        }

        // Filter by issue_date
        if ($issueDate = $request->input('issue_date')) {
            $query->whereDate('invoices.issue_date', $issueDate);
        }

        // Filter by due_date
        if ($dueDate = $request->input('due_date')) {
            $query->whereDate('invoices.due_date', $dueDate);
        }

        // Filter by customer
        if ($customerId = $request->input('customer_id')) {
            $query->where('invoices.customer_id', $customerId);
        }

        $invoices = $query->distinct()->with('order')->orderBy('invoices.created_at', 'desc')->paginate(10)->appends($request->all());
        $statuses = ['unpaid', 'partial', 'paid'];
        $filters = $request->only(['search', 'status', 'issue_date', 'due_date', 'customer_id']);
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('erp.invoices.invoicelist', compact('invoices', 'statuses', 'filters', 'customers'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        $products = \App\Models\Product::orderBy('name')->get();
        $templates = \App\Models\InvoiceTemplate::orderBy('name')->get();
        return view('erp.invoices.create', compact('customers', 'products', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'template_id' => 'required|exists:invoice_templates,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'send_date' => 'nullable|date',
            'note' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
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
                    'total_price' => $item['total_price'],
                ]);
            }
            $customer = Customer::find($request->customer_id);
            if($customer->address_1) {
                \App\Models\InvoiceAddress::create([
                    'invoice_id' => $invoice->id,
                    'billing_address_1' => $customer->address_1,
                    'billing_address_2' => $customer->address_2,
                    'billing_city' => $customer->city,
                    'billing_state' => $customer->state,
                    'billing_country' => $customer->country,
                    'billing_zip_code' => $customer->zip_code,
                    'shipping_address_1' => $customer->address_1,
                    'shipping_address_2' => $customer->address_2,
                    'shipping_city' => $customer->city,
                    'shipping_state' => $customer->state,
                    'shipping_country' => $customer->country,
                    'shipping_zip_code' => $customer->zip_code,
                ]);
            }
            
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

            if($dueAmount == 0)
            {
                $invoice->status = 'paid';
                $invoice->save();
            }else if($dueAmount > 0){
                $invoice->status = 'partial';
                $invoice->save();
            }else if($totalAmount == $dueAmount ){
                $invoice->status = 'unpaid';
                $invoice->save();
            }

            \DB::commit();
            return redirect()->route('invoice.list')->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'template_id' => 'required|exists:invoice_templates,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'send_date' => 'nullable|date',
            'note' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
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
                    'total_price' => $item['unit_price'] * $item['quantity'],
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
            if($dueAmount == 0)
            {
                $invoice->status = 'paid';
                $invoice->save();
            }else if($dueAmount > 0){
                $invoice->status = 'partial';
                $invoice->save();
            }else if($totalAmount == $dueAmount ){
                $invoice->status = 'unpaid';
                $invoice->save();
            }
            \DB::commit();
            return redirect()->route('invoice.list')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $invoice = \App\Models\Invoice::with(['customer', 'invoiceAddress', 'items.product', 'items.variation'])->findOrFail($id);
        $templates = \App\Models\InvoiceTemplate::orderBy('name')->get();
        return view('erp.invoices.edit', compact('invoice', 'templates'));
    }

    public function show($id)
    {
        $invoice = Invoice::with('pos','payments','customer','invoiceAddress','salesman','items.product','items.variation')->find($id);
        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        $order = \App\Models\Order::where('invoice_id', $invoice?->id)->first();
        return view('erp.invoices.show', compact('invoice', 'bankAccounts', 'order'));
    }

    public function addPayment($invId, Request $request)
    {
        $invoice = Invoice::find($invId);

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
        $invoice = Invoice::with('items.product', 'items.variation')->where('invoice_number', $invoice_number)->first();
        if(!$invoice)
        {
            return redirect()->route('invoice.print', ['invoice_number' => 'notfound'])->with('error', 'Invoice not found.');
        }
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
        if (!auth()->user()->hasPermissionTo('view invoice list')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Invoice::with(['customer', 'salesman', 'order']);

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
        if (!auth()->user()->hasPermissionTo('view invoice list')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Invoice::with(['customer', 'salesman', 'order']);

        // Apply filters
        if ($request->filled('issue_date_from')) {
            $query->whereDate('issue_date', '>=', $request->issue_date_from);
        }
        if ($request->filled('issue_date_to')) {
            $query->whereDate('issue_date', '<=', $request->issue_date_to);
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->get();
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
        if (!auth()->user()->hasPermissionTo('view invoice list')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Invoice::with(['customer', 'salesman', 'order']);

        // Apply filters
        if ($request->filled('issue_date_from')) {
            $query->whereDate('issue_date', '>=', $request->issue_date_from);
        }
        if ($request->filled('issue_date_to')) {
            $query->whereDate('issue_date', '<=', $request->issue_date_to);
        }
        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }
        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : [];

        // Validate that at least one column is selected
        if (empty($selectedColumns)) {
            return response()->json(['error' => 'Please select at least one column to export.'], 400);
        }

        // Prepare data for export
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
                'status' => $request->status,
                'customer_id' => $request->customer_id,
            ]
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($filename);
    }

    private function generateInvoiceNumber()
    {
        $generalSettings = GeneralSetting::first();
        $prefix = $generalSettings ? $generalSettings->invoice_prefix : 'INV';
        
        do {
            $number = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $fullNumber = $prefix . $number;
        } while (Invoice::where('invoice_number', $fullNumber)->exists());
        
        return $fullNumber;
    }
}
