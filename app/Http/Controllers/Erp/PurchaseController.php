<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FinancialAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\Purchase;
use App\Models\PurchaseBill;
use App\Models\PurchaseItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view purchases')) {
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

        $query = \App\Models\PurchaseItem::with([
            'purchase.bill',
            'purchase.supplier',
            'product.category',
            'product.brand',
            'product.season',
            'product.gender',
            'variation.attributeValues.attribute',
            'returnItems'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);

        // Calculate Totals before pagination
        $totalQty = $query->sum('quantity');
        $totalAmount = $query->sum('total_price');

        $items = $query->latest()->paginate(20)->appends($request->all());
        
        // Dropdown Data
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $seasons = \App\Models\Season::orderBy('name')->get();
        $genders = \App\Models\Gender::orderBy('name')->get();
        $products = \App\Models\Product::where('type', 'product')->orderBy('name')->get();
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
            $warehouses = collect();
        } else {
            $branches = Branch::all();
            $warehouses = \App\Models\Warehouse::all();
        }
        $bankAccounts = \DB::table('financial_accounts')->get();

        return view('erp.purchases.purchaseList', compact(
            'items', 'suppliers', 'categories', 'brands', 'seasons', 'genders', 'products', 
            'branches', 'warehouses', 'bankAccounts', 'reportType', 'startDate', 'endDate', 'totalQty', 'totalAmount'
        ));
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view purchases')) {
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

        $query = \App\Models\PurchaseItem::with([
            'purchase.bill', 
            'purchase.supplier', 
            'product.category', 
            'product.brand', 
            'product.season', 
            'product.gender',
            'variation.attributeValues.attribute',
            'returnItems'
        ]);
        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'SL', 'Invoice #', 'Date', 'Supplier', 'Category', 'Brand', 'Season', 'Gender', 
            'Product', 'Style Ref', 'Color', 'Size', 
            'Pur. Qty', 'Pur. Value', 'Ret. Qty', 'Ret. Value', 
            'Act. Qty', 'Act. Value', 'Discount', 'Paid A/C', 'Due A/C', 'Status'
        ];
        $exportData[] = $headers;

        foreach ($items as $index => $item) {
            $purchase = $item->purchase;
            $bill = $purchase->bill;
            $product = $item->product;
            $variation = $item->variation;
            
            // Extract Color and Size
            $color = '-'; $size = '-';
            if ($variation && $variation->attributeValues) {
                foreach($variation->attributeValues as $val) {
                    $attrName = strtolower($val->attribute->name ?? '');
                    if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) {
                        $color = $val->value;
                    } elseif (str_contains($attrName, 'size')) {
                        $size = $val->value;
                    }
                }
            }

            // Calculations
            $retQty = $item->returnItems->sum('returned_qty');
            $retAmt = $item->returnItems->sum('total_price');
            $actQty = $item->quantity - $retQty;
            $actAmt = $item->total_price - $retAmt;

            $row = [
                $index + 1,
                $bill->bill_number ?? 'P-'.$purchase->id,
                $purchase->purchase_date,
                $purchase->supplier->name ?? 'N/A',
                $product->category->name ?? 'N/A',
                $product->brand->name ?? 'N/A',
                $product->season->name ?? 'N/A',
                $product->gender->name ?? 'N/A',
                $product->name ?? 'N/A',
                $product->sku ?? $product->style_number ?? 'N/A',
                $color,
                $size,
                number_format($item->quantity, 2),
                number_format($item->total_price, 2),
                number_format($retQty, 2),
                number_format($retAmt, 2),
                number_format($actQty, 2),
                number_format($actAmt, 2),
                number_format($bill->discount_amount ?? 0, 2),
                number_format($bill->paid_amount ?? 0, 2),
                number_format($bill->due_amount ?? 0, 2),
                ucfirst($purchase->status)
            ];
            $exportData[] = $row;
        }

        $filename = 'purchase_audit_report_' . date('Y-m-d_His') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        foreach ($exportData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . ($rowIndex + 1), $value);
            }
        }
        
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);
        
        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view purchases')) {
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

        $query = \App\Models\PurchaseItem::with([
            'purchase.bill', 
            'purchase.supplier', 
            'product.category', 
            'product.brand', 
            'product.season', 
            'product.gender',
            'variation.attributeValues.attribute',
            'returnItems'
        ]);
        $query = $this->applyFilters($query, $request, $startDate, $endDate);
        $items = $query->orderBy('created_at', 'desc')->get();

        $filename = 'purchase_audit_report_' . date('Y-m-d_His') . '.pdf';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.purchases.report-pdf', [
            'items' => $items,
            'filters' => $request->all()
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->download($filename);
    }

    private function applyFilters($query, Request $request, $startDate = null, $endDate = null)
    {
        // Date Filtering
        if ($startDate && $endDate) {
            $query->whereHas('purchase', function($q) use ($startDate, $endDate) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('purchase', function($q) use ($startDate) {
                $q->whereDate('purchase_date', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('purchase', function($q) use ($endDate) {
                $q->whereDate('purchase_date', '<=', $endDate);
            });
        }

        // Search by purchase id / invoice
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('purchase', function($q) use ($search) {
                $q->where('id', 'LIKE', "%$search%")
                  ->orWhereHas('bill', function($bq) use ($search) {
                      $bq->where('bill_number', 'LIKE', "%$search%");
                  });
            });
        }

        // Filters from dropdowns
        if ($request->filled('supplier_id')) {
            $query->whereHas('purchase', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('purchase', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Location Filters
        $restrictedBranchId = $this->getRestrictedBranchId();
        $selectedBranchId = $restrictedBranchId ?: $request->branch_id;

        if ($selectedBranchId) {
            $query->whereHas('purchase', function($q) use ($selectedBranchId) {
                $q->where('ship_location_type', 'branch')->where('location_id', $selectedBranchId);
            });
        }

        if (!$restrictedBranchId && $request->filled('warehouse_id')) {
            $query->whereHas('purchase', function($q) use ($request) {
                $q->where('ship_location_type', 'warehouse')->where('location_id', $request->warehouse_id);
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

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
            $warehouses = collect();
        } else {
            $branches = Branch::all();
            $warehouses = \App\Models\Warehouse::all();
        }
        $products = \App\Models\Product::all();
        $suppliers = \App\Models\Supplier::all();
        $bankAccounts = FinancialAccount::orderBy('type')->orderBy('provider_name')->get();
        return view('erp.purchases.create', compact('branches', 'warehouses', 'products', 'suppliers', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'supplier_id' => 'nullable|integer',
            'ship_location_type' => 'required|in:branch,warehouse',
            'location_id' => 'required|integer',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable', // allow string/int
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:flat,percent',
            'payment_method' => 'nullable|string',
            'account_id' => 'nullable|integer',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Calculate subtotal
            $subTotal = 0;
            foreach ($request->items as $item) {
                $subTotal += $item['quantity'] * $item['unit_price'];
            }
    
            // Calculate discount
            $discountValue = (float) $request->input('discount_value', 0);
            $discountType = $request->input('discount_type', 'flat');
            $discountAmount = 0;
            if ($discountType === 'percent') {
                $discountAmount = ($subTotal * $discountValue) / 100;
            } else {
                $discountAmount = $discountValue;
            }
    
            $totalAmount = max(0, $subTotal - $discountAmount);
    
            $restrictedBranchId = $this->getRestrictedBranchId();
            $locationType = $restrictedBranchId ? 'branch' : $request->ship_location_type;
            $locationId = $restrictedBranchId ?: $request->location_id;

            // Create Purchase (supplier is optional)
            $purchase = Purchase::create([
                'supplier_id'         => $request->supplier_id ?? null,
                'ship_location_type'  => $locationType,
                'location_id'         => $locationId,
                'purchase_date'       => $request->purchase_date,
                'status'              => 'pending',
                'created_by'          => auth()->id(),
                'notes'               => $request->notes,
            ]);
    
            // Add Purchase Items
            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $item['product_id'],
                    'variation_id' => !empty($item['variation_id']) ? $item['variation_id'] : null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['quantity'] * $item['unit_price'],
                    'description'     => $item['description'] ?? null,
                ]);
            }
    
            // Create Bill (only if supplier is provided)
            if ($request->supplier_id) {
                $paid_amount = $request->input('paid_amount', 0);
                $due_amount = max(0, $totalAmount - $paid_amount);
                $status = 'unpaid';
                if ($paid_amount >= $totalAmount) $status = 'paid';
                elseif ($paid_amount > 0) $status = 'partial';

                $bill = PurchaseBill::create([
                    'supplier_id'   => $request->supplier_id,
                    'purchase_id'   => $purchase->id,
                    'bill_date'     => now()->toDateString(),
                    'sub_total'      => $subTotal,
                    'discount_amount'=> $discountAmount,
                    'discount_type'  => $discountType,
                    'discount_value' => $discountValue,
                    'total_amount'  => $totalAmount,
                    'paid_amount'   => $paid_amount,
                    'due_amount'    => $due_amount,
                    'status'        => $status,
                    'created_by'    => auth()->id(),
                    'description'   => 'Auto-generated bill from purchase ID: ' . $purchase->id,
                ]);

                // Record Bill in Ledger (CREDIT the supplier)
                \App\Models\SupplierLedger::recordTransaction(
                    $request->supplier_id,
                    'credit',
                    $totalAmount,
                    'Purchase Bill: ' . ($bill->bill_number ?: 'P-'.$purchase->id),
                    $request->purchase_date,
                    $bill
                );

                // If payment made, record Payment and update Ledger
                if ($paid_amount > 0) {
                    $payment = \App\Models\SupplierPayment::create([
                        'supplier_id' => $request->supplier_id,
                        'purchase_bill_id' => $bill->id,
                        'payment_date' => $request->purchase_date,
                        'amount' => $paid_amount,
                        'payment_method' => $request->payment_method ?? 'cash',
                        'reference' => 'Initial payment at purchase',
                        'note' => $request->notes,
                        'created_by' => auth()->id(),
                    ]);

                    // Record Payment in Ledger (DEBIT the supplier)
                    \App\Models\SupplierLedger::recordTransaction(
                        $request->supplier_id,
                        'debit',
                        $paid_amount,
                        'Payment for Purchase Bill: ' . ($bill->bill_number ?: 'P-'.$purchase->id),
                        $request->purchase_date,
                        $payment
                    );
                }

                // =====================================================
                // AUTO JOURNAL ENTRY (Double-Entry Accounting)
                // =====================================================
                $financialAccount = $request->account_id
                    ? FinancialAccount::find($request->account_id)
                    : null;

                // The payment account must be linked to a Chart of Account
                $paymentChartAccountId = $financialAccount?->account_id ?? null;

                if ($paymentChartAccountId && $paid_amount > 0) {
                    // Ensure unique voucher number
                    $voucherNo = 'PUR-' . str_pad($purchase->id, 6, '0', STR_PAD_LEFT);
                    while (Journal::where('voucher_no', $voucherNo)->exists()) {
                        $voucherNo = 'PUR-' . str_pad($purchase->id, 6, '0', STR_PAD_LEFT) . '-' . rand(10, 99);
                    }

                    $journal = Journal::create([
                        'voucher_no'     => $voucherNo,
                        'entry_date'     => $request->purchase_date,
                        'type'           => 'Payment',
                        'description'    => 'Auto: Purchase #' . $purchase->id . ($request->notes ? ' - ' . $request->notes : ''),
                        'supplier_id'    => $request->supplier_id ?? null,
                        'branch_id'      => $locationId ?? null,
                        'voucher_amount' => $totalAmount,
                        'paid_amount'    => $paid_amount,
                        'reference'      => isset($bill) ? ($bill->bill_number ?? 'BILL-' . $bill->id) : 'PUR-' . $purchase->id,
                        'created_by'     => Auth::id(),
                        'updated_by'     => Auth::id(),
                    ]);

                    // Find a Purchases/Expense account to DEBIT
                    // Try by name first, then fall back to any Expense-type account
                    $purchaseChartAccount = ChartOfAccount::where('name', 'like', '%purchase%')
                        ->orWhere('name', 'like', '%stock%')
                        ->orWhere('name', 'like', '%inventory%')
                        ->first();

                    // If no purchase account found, use the payment account itself as a simple transfer
                    // and log the debit against a generic expense
                    if (!$purchaseChartAccount) {
                        // Use any expense-type account, or just use the payment COA as a self-balancing entry
                        $purchaseChartAccount = ChartOfAccount::whereHas('type', function($q) {
                            $q->where('name', 'like', '%expense%');
                        })->first();
                    }

                    if ($purchaseChartAccount) {
                        // DEBIT: Purchases/Expense account (goods/services received)
                        JournalEntry::create([
                            'journal_id'           => $journal->id,
                            'chart_of_account_id'  => $purchaseChartAccount->id,
                            'financial_account_id' => null,
                            'debit'                => $paid_amount,
                            'credit'               => 0,
                            'memo'                 => 'Purchase of goods - Purchase #' . $purchase->id,
                            'created_by'           => Auth::id(),
                            'updated_by'           => Auth::id(),
                        ]);

                        // CREDIT: Payment account (bank/mobile/cash)
                        JournalEntry::create([
                            'journal_id'           => $journal->id,
                            'chart_of_account_id'  => $paymentChartAccountId,
                            'financial_account_id' => $financialAccount->id,
                            'debit'                => 0,
                            'credit'               => $paid_amount,
                            'memo'                 => 'Payment via ' . ($financialAccount->provider_name ?? $request->payment_method),
                            'created_by'           => Auth::id(),
                            'updated_by'           => Auth::id(),
                        ]);
                    } else {
                        // Fallback: single-sided entry just recording the payment outflow
                        JournalEntry::create([
                            'journal_id'           => $journal->id,
                            'chart_of_account_id'  => $paymentChartAccountId,
                            'financial_account_id' => $financialAccount->id,
                            'debit'                => 0,
                            'credit'               => $paid_amount,
                            'memo'                 => 'Purchase payment via ' . ($financialAccount->provider_name ?? $request->payment_method),
                            'created_by'           => Auth::id(),
                            'updated_by'           => Auth::id(),
                        ]);
                    }

                    // CREDIT: Accounts Payable for any due/remaining amount
                    if (isset($due_amount) && $due_amount > 0) {
                        $payableChartAccount = ChartOfAccount::where('name', 'like', '%payable%')
                            ->orWhere('name', 'like', '%creditor%')
                            ->first();
                        if ($payableChartAccount) {
                            JournalEntry::create([
                                'journal_id'           => $journal->id,
                                'chart_of_account_id'  => $payableChartAccount->id,
                                'financial_account_id' => null,
                                'debit'                => 0,
                                'credit'               => $due_amount,
                                'memo'                 => 'Due to supplier - Purchase #' . $purchase->id,
                                'created_by'           => Auth::id(),
                                'updated_by'           => Auth::id(),
                            ]);
                        }
                    }
                }
                // =====================================================
            }

            DB::commit();

            return redirect()->route('purchase.list')->with('success', 'Purchase created successfully.');
    
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $purchase = Purchase::with(['bill', 'supplier', 'items.product', 'items.variation'])->findOrFail($id);

        // Safely resolve location name; the related branch/warehouse might not exist anymore
        if ($purchase->ship_location_type === 'branch') {
            $branch = Branch::find($purchase->location_id);
            $purchase->location_name = $branch?->name ?? 'Unknown Branch';
        } elseif ($purchase->ship_location_type === 'warehouse') {
            $warehouse = Warehouse::find($purchase->location_id);
            $purchase->location_name = $warehouse?->name ?? 'Unknown Warehouse';
        } else {
            $purchase->location_name = 'Unknown Location';
        }

        return view('erp.purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $purchase = Purchase::with('items')->findOrFail($id);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
            $warehouses = collect();
        } else {
            $branches = Branch::all();
            $warehouses = Warehouse::all();
        }
        $suppliers = \App\Models\Supplier::all();
        return view('erp.purchases.edit', compact('purchase', 'branches', 'warehouses', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'supplier_id' => 'nullable|integer',
            'ship_location_type' => 'required|in:branch,warehouse',
            'location_id' => 'required|integer',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:flat,percent',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($id);
            $previousStatus = $purchase->status;

            $purchase->update([
                'supplier_id'         => $request->supplier_id ?? null,
                'ship_location_type'  => $request->ship_location_type,
                'location_id'         => $request->location_id,
                'purchase_date'       => $request->purchase_date,
                'status'              => $request->status,
                'notes'               => $request->notes,
            ]);

            // Only add stock the first time we move into "received" status
            if ($request->status === 'received' && $previousStatus !== 'received') {
                foreach ($purchase->items as $item) {
                    if ($item->variation_id) {
                        // Update detailed variation stock
                        if ($purchase->ship_location_type === 'branch') {
                            $stock = \App\Models\ProductVariationStock::firstOrNew([
                                'variation_id' => $item->variation_id,
                                'branch_id' => $purchase->location_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();

                            // Also mirror into branch product stock so POS can see this product
                            $branchStock = \App\Models\BranchProductStock::firstOrNew([
                                'branch_id'  => $purchase->location_id,
                                'product_id' => $item->product_id,
                            ]);
                            $branchStock->quantity = ($branchStock->quantity ?? 0) + $item->quantity;
                            $branchStock->updated_by = auth()->id() ?? 1;
                            $branchStock->last_updated_at = now();
                            $branchStock->save();
                        } elseif ($purchase->ship_location_type === 'warehouse') {
                            $stock = \App\Models\ProductVariationStock::firstOrNew([
                                'variation_id' => $item->variation_id,
                                'warehouse_id' => $purchase->location_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();

                            // Mirror into warehouse product stock so non-variation flows can see it
                            $warehouseStock = \App\Models\WarehouseProductStock::firstOrNew([
                                'warehouse_id' => $purchase->location_id,
                                'product_id'   => $item->product_id,
                            ]);
                            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $item->quantity;
                            $warehouseStock->updated_by = auth()->id() ?? 1;
                            $warehouseStock->last_updated_at = now();
                            $warehouseStock->save();
                        }
                    } else {
                        // Simple (non-variation) products: existing behavior
                        if ($purchase->ship_location_type === 'branch') {
                            $stock = \App\Models\BranchProductStock::firstOrNew([
                                'branch_id' => $purchase->location_id,
                                'product_id' => $item->product_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();
                        } elseif ($purchase->ship_location_type === 'warehouse') {
                            $stock = \App\Models\WarehouseProductStock::firstOrNew([
                                'warehouse_id' => $purchase->location_id,
                                'product_id' => $item->product_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();
                        }
                    }
                }
            }

            // Remove old items
            $purchase->items()->delete();
            // Add new items
            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'   => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['quantity'] * $item['unit_price'],
                    'description'  => $item['description'] ?? null,
                ]);
            }
            // Calculate subtotal
            $subTotal = 0;
            foreach ($request->items as $item) {
                $subTotal += $item['quantity'] * $item['unit_price'];
            }
    
            // Calculate discount
            $discountValue = (float) $request->input('discount_value', 0);
            $discountType = $request->input('discount_type', 'flat');
            $discountAmount = 0;
            if ($discountType === 'percent') {
                $discountAmount = ($subTotal * $discountValue) / 100;
            } else {
                $discountAmount = $discountValue;
            }
    
            $totalAmount = max(0, $subTotal - $discountAmount);

            // Update bill if exists
            if ($purchase->bill) {
                $purchase->bill->update([
                    'sub_total'       => $subTotal,
                    'discount_amount' => $discountAmount,
                    'discount_type'   => $discountType,
                    'discount_value'  => $discountValue,
                    'total_amount'    => $totalAmount,
                    'due_amount'      => max(0, $totalAmount - $purchase->bill->paid_amount),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase.list')->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'status' => 'required|string',
        ]);
        $purchase = Purchase::with('items')->findOrFail($id);
        $previousStatus = $purchase->status;
        $purchase->status = $request->status;
        $purchase->save();

        // Only add stock the first time we move into "received" status
        if ($request->status === 'received' && $previousStatus !== 'received') {
            foreach ($purchase->items as $item) {
                if ($item->variation_id) {
                    if ($purchase->ship_location_type === 'branch') {
                        $stock = \App\Models\ProductVariationStock::firstOrNew([
                            'variation_id' => $item->variation_id,
                            'branch_id' => $purchase->location_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();

                        // Mirror into branch product stock so POS can see this product
                        $branchStock = \App\Models\BranchProductStock::firstOrNew([
                            'branch_id'  => $purchase->location_id,
                            'product_id' => $item->product_id,
                        ]);
                        $branchStock->quantity = ($branchStock->quantity ?? 0) + $item->quantity;
                        $branchStock->updated_by = auth()->id() ?? 1;
                        $branchStock->last_updated_at = now();
                        $branchStock->save();
                    } elseif ($purchase->ship_location_type === 'warehouse') {
                        $stock = \App\Models\ProductVariationStock::firstOrNew([
                            'variation_id' => $item->variation_id,
                            'warehouse_id' => $purchase->location_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();

                        // Mirror into warehouse product stock so other flows can see it
                        $warehouseStock = \App\Models\WarehouseProductStock::firstOrNew([
                            'warehouse_id' => $purchase->location_id,
                            'product_id'   => $item->product_id,
                        ]);
                        $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $item->quantity;
                        $warehouseStock->updated_by = auth()->id() ?? 1;
                        $warehouseStock->last_updated_at = now();
                        $warehouseStock->save();
                    }
                } else {
                    if ($purchase->ship_location_type === 'branch') {
                        $stock = \App\Models\BranchProductStock::firstOrNew([
                            'branch_id' => $purchase->location_id,
                            'product_id' => $item->product_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();
                    } elseif ($purchase->ship_location_type === 'warehouse') {
                        $stock = \App\Models\WarehouseProductStock::firstOrNew([
                            'warehouse_id' => $purchase->location_id,
                            'product_id' => $item->product_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();
                    }
                }
            }
        }
        return redirect()->back()->with('success', 'Purchase status updated successfully.');
    }

    public function delete($id)
    {
        if (!auth()->user()->hasPermissionTo('manage purchases')) {
            abort(403, 'Unauthorized action.');
        }
        DB::beginTransaction();
        try {
            $purchase = Purchase::with(['items', 'bill'])->findOrFail($id);
            // Delete related items
            $purchase->items()->delete();
            // Delete related bill
            if ($purchase->bill) {
                $purchase->bill->delete();
            }
            // Delete the purchase itself
            $purchase->delete();
            DB::commit();
            return redirect()->route('purchase.list')->with('success', 'Purchase and related data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function searchPurchase(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $search = $request->q;
        $query = Purchase::query();
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('id', 'like', "%$search%");
            });
        }
        $purchases = $query->limit(20)->get()->filter();
        $results = $purchases->filter(function($purchase) {
            return $purchase !== null;
        })->map(function($purchase) {
            $text = "#{$purchase->id} - Assign ({$purchase->purchase_date})";
            return [
                'id' => $purchase->id,
                'text' => $text
            ];
        });
        return response()->json(['results' => $results]);
    }

    public function getItemByPurchase($id)
    {
        if (!auth()->user()->hasPermissionTo('view purchases')) {
            abort(403, 'Unauthorized action.');
        }
        $purchaseItems = \App\Models\PurchaseItem::with('product')
            ->where('purchase_id', $id)
            ->get();

        $results = $purchaseItems->map(function($item) {
            return [
                'id' => $item->id,
                'text' => "#{$item->id} - {$item->product->name} (Qty: {$item->quantity})",
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'description' => $item->description,
            ];
        });

        return response()->json(['results' => $results]);
    }

}
