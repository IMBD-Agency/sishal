<?php


namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Models\Pos;
use App\Models\Balance;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseBill;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductServiceCategory;
use App\Models\ProductVariation;
use App\Models\Brand;
use App\Models\Season;
use App\Models\Gender;
use App\Models\PosItem;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SaleReturn;
use App\Models\PurchaseReturn;
use App\Models\FinancialAccount;
use App\Models\JournalEntry;

class ReportController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }
        return view('erp.reports.index');
    }

    public function purchaseReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }
        
        $supplierId = $request->get('supplier_id');
        $categoryId = $request->get('category_id');
        $brandId = $request->get('brand_id');
        $seasonId = $request->get('season_id');
        $genderId = $request->get('gender_id');
        $productId = $request->get('product_id');
        $styleNumber = $request->get('style_number');
        $challanId = $request->get('challan_id');
        $branchId = $request->get('branch_id');
        $warehouseId = $request->get('warehouse_id');

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branchId = $restrictedBranchId;
        }

        $query = PurchaseItem::with(['purchase.supplier', 'purchase.bill', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation.attributeValues'])
            ->whereHas('purchase', function ($q) use ($startDate, $endDate, $branchId, $warehouseId) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
                if ($branchId) {
                    $q->where('location_id', $branchId)->where('ship_location_type', 'branch');
                }
                if ($warehouseId) {
                    $q->where('location_id', $warehouseId)->where('ship_location_type', 'warehouse');
                }
            });

        if ($supplierId) {
            $query->whereHas('purchase', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }

        if ($challanId) {
            $query->where('purchase_id', $challanId);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($styleNumber) {
            $query->whereHas('product', function ($q) use ($styleNumber) {
                $q->where('style_number', 'like', '%' . $styleNumber . '%');
            });
        }

        if ($categoryId || $brandId || $seasonId || $genderId) {
            $query->whereHas('product', function ($q) use ($categoryId, $brandId, $seasonId, $genderId) {
                if ($categoryId) $q->where('category_id', $categoryId);
                if ($brandId) $q->where('brand_id', $brandId);
                if ($seasonId) $q->where('season_id', $seasonId);
                if ($genderId) $q->where('gender_id', $genderId);
            });
        }

        if ($request->filled('export')) {
            if ($request->export == 'excel') {
                return $this->exportPurchaseExcel($query->get(), $startDate, $endDate);
            } elseif ($request->export == 'pdf') {
                return $this->exportPurchasePdf($query->get(), $startDate, $endDate);
            }
        }

        $items = $query->latest()->paginate(50)->appends($request->all());

        // Summary stats
        $summary = [
            'total_qty' => $query->sum('quantity'),
            'total_amount' => $query->sum('total_price'),
            'unique_products' => $query->distinct('product_id')->count(),
            'total_orders' => $query->distinct('purchase_id')->count()
        ];

        $suppliers = Supplier::orderBy('name')->get();
        $categories = ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $seasons = Season::orderBy('name')->get();
        $genders = Gender::orderBy('name')->get();
        $products = Product::where('type', 'product')->orderBy('name')->get();
        $challansQuery = Purchase::latest();
        if ($branchId) {
            $challansQuery->where('location_id', $branchId)->where('ship_location_type', 'branch');
        }
        if ($warehouseId) {
            $challansQuery->where('location_id', $warehouseId)->where('ship_location_type', 'warehouse');
        }
        $challans = $challansQuery->take(100)->get();
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();

        return view('erp.reports.purchase', compact(
            'items', 'summary', 'suppliers', 'categories', 'brands', 'seasons', 'genders', 'products', 'challans', 'startDate', 'endDate', 'reportType', 'branches', 'branchId', 'warehouses', 'warehouseId'
        ));
    }

    private function exportPurchaseExcel($items, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Purchase Report (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['Ref #', 'Date', 'Supplier', 'Product', 'Style #', 'Category', 'Brand', 'Variation', 'Rate', 'Qty', 'Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $col++;
        }

        $row = 4;
        foreach ($items as $item) {
            $variation = '';
            if ($item->variation) {
                $variation = $item->variation->attributeValues->pluck('value')->implode(', ');
            }

            $sheet->setCellValue('A' . $row, '#' . $item->purchase_id);
            $sheet->setCellValue('B' . $row, Carbon::parse($item->purchase->purchase_date)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $item->purchase->supplier->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $item->product->name ?? 'Deleted');
            $sheet->setCellValue('E' . $row, $item->product->style_number ?? '-');
            $sheet->setCellValue('F' . $row, $item->product->category->name ?? '-');
            $sheet->setCellValue('G' . $row, $item->product->brand->name ?? '-');
            $sheet->setCellValue('H' . $row, $variation);
            $sheet->setCellValue('I' . $row, $item->unit_price);
            $sheet->setCellValue('J' . $row, $item->quantity);
            $sheet->setCellValue('K' . $row, $item->total_price);
            $row++;
        }

        $filename = "purchase_report_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportPurchasePdf($items, $startDate, $endDate)
    {
        $pdf = Pdf::loadView('erp.reports.pdf.purchase', [
            'items' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'total_qty' => $items->sum('quantity'),
                'total_amount' => $items->sum('total_price'),
            ]
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('purchase_report_' . date('Y-m-d') . '.pdf');
    }

    public function saleReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }
        
        $customerId = $request->get('customer_id');
        $employeeId = $request->get('employee_id');
        $categoryId = $request->get('category_id');
        $brandId = $request->get('brand_id');
        $seasonId = $request->get('season_id');
        $genderId = $request->get('gender_id');
        $productId = $request->get('product_id');
        $styleNumber = $request->get('style_number');
        $invoiceNo = $request->get('invoice_no');
        $branchId = $request->get('branch_id');

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branchId = $restrictedBranchId;
        }

        // POS Items Query
        $posQuery = PosItem::with(['pos.customer', 'pos.employee.user', 'pos.soldBy', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation.attributeValues'])
            ->whereHas('pos', function($q) use ($startDate, $endDate, $customerId, $employeeId, $invoiceNo, $branchId) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
                if ($customerId) $q->where('customer_id', $customerId);
                if ($employeeId) $q->where('sold_by', $employeeId);
                if ($invoiceNo) $q->where('invoice_number', 'like', '%' . $invoiceNo . '%');
                if ($branchId) $q->where('branch_id', $branchId);
            });

        // Online Order Items Query
        $orderQuery = OrderItem::with(['order.customer', 'order.employee.user', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation.attributeValues'])
            ->whereHas('order', function($q) use ($startDate, $endDate, $customerId, $invoiceNo, $branchId) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', '!=', 'cancelled');
                if ($customerId) $q->where('user_id', $customerId);
                if ($invoiceNo) $q->where('order_id', 'like', '%' . $invoiceNo . '%');
                // Order model might not have branch_id but if it does, filter it:
                // if ($branchId) $q->where('branch_id', $branchId); 
            });

        // Apply filters to both
        foreach ([$posQuery, $orderQuery] as $q) {
            if ($productId) $q->where('product_id', $productId);
            if ($styleNumber) {
                $q->whereHas('product', function ($pq) use ($styleNumber) {
                    $pq->where('style_number', 'like', '%' . $styleNumber . '%');
                });
            }
            if ($categoryId || $brandId || $seasonId || $genderId) {
                $q->whereHas('product', function ($pq) use ($categoryId, $brandId, $seasonId, $genderId) {
                    if ($categoryId) $pq->where('category_id', $categoryId);
                    if ($brandId) $pq->where('brand_id', $brandId);
                    if ($seasonId) $pq->where('season_id', $seasonId);
                    if ($genderId) $pq->where('gender_id', $genderId);
                });
            }
        }

        // Summary Statistics - Calculated in DB for performance
        $posSummary = (clone $posQuery)->selectRaw('SUM(pos_items.quantity) as total_qty, SUM(pos_items.total_price) as total_amount, SUM(pos_items.quantity * IFNULL(pos_items.unit_cost, 0)) as total_cost')->first();
        $orderSummary = (clone $orderQuery)->selectRaw('SUM(order_items.quantity) as total_qty, SUM(order_items.total_price) as total_amount, SUM(order_items.quantity * IFNULL(order_items.unit_cost, 0)) as total_cost')->first();

        $summary = [
            'total_qty' => ($posSummary->total_qty ?? 0) + ($orderSummary->total_qty ?? 0),
            'total_amount' => ($posSummary->total_amount ?? 0) + ($orderSummary->total_amount ?? 0),
            'total_cost' => ($posSummary->total_cost ?? 0) + ($orderSummary->total_cost ?? 0),
            'total_discount' => 0,
        ];
        $summary['total_net'] = $summary['total_amount'];
        $summary['total_profit'] = $summary['total_amount'] - $summary['total_cost'];

        // On-screen List: Limit for browser performance, but full for export
        $isExport = $request->filled('export');
        $posItemsQuery = $isExport ? $posQuery : $posQuery->take(100);
        $orderItemsQuery = $isExport ? $orderQuery : $orderQuery->take(100);

        $posItems = $posItemsQuery->get()->map(function($item) {
            $item->source = 'POS';
            $item->date = $item->pos->sale_date;
            $item->invoice = $item->pos->invoice_number;
            $item->customer_name = $item->pos->customer->name ?? 'Walk-in';
            $item->created_by_name = $item->pos->soldBy->name ?? ($item->pos->employee->user->name ?? 'Admin');
            $item->unit_cost = $item->unit_cost ?? ($item->product->cost ?? 0);
            $item->total_cost = $item->quantity * $item->unit_cost;
            $item->profit = $item->total_price - $item->total_cost;
            return $item;
        });

        $orderItems = $orderItemsQuery->get()->map(function($item) {
            $item->source = 'Online';
            $item->date = $item->order->created_at;
            $item->invoice = '#' . $item->order->id;
            $item->customer_name = $item->order->customer->name ?? ($item->order->first_name . ' ' . $item->order->last_name);
            $item->created_by_name = $item->order->employee->user->name ?? 'Website';
            $item->unit_cost = $item->unit_cost ?? ($item->product->cost ?? 0);
            $item->total_cost = $item->quantity * $item->unit_cost;
            $item->profit = $item->total_price - $item->total_cost;
            return $item;
        });

        $allItems = $posItems->concat($orderItems)->sortByDesc('date');

        $customers = Customer::orderBy('name')->get();
        $employees = User::whereHas('employee')->get();
        $categories = ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $seasons = Season::orderBy('name')->get();
        $genders = Gender::orderBy('name')->get();
        $products = Product::where('type', 'product')->orderBy('name')->get();
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        if ($isExport) {
            if ($request->export == 'excel') {
                return $this->exportSaleExcel($allItems, $startDate, $endDate);
            } elseif ($request->export == 'pdf') {
                return $this->exportSalePdf($allItems, $startDate, $endDate);
            }
        }

        return view('erp.reports.sale', compact(
            'allItems', 'summary', 'customers', 'employees', 'categories', 'brands', 'seasons', 'genders', 'products', 'startDate', 'endDate', 'reportType', 'branches', 'branchId'
        ));
    }

    private function exportSaleExcel($items, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = ['Order ID', 'Date', 'Customer', 'Product', 'Style #', 'Variation', 'Unit Price', 'Qty', 'Total', 'Discount', 'Source'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        $row = 2;
        foreach ($items as $item) {
            $variation = $item->variation ? $item->variation->attributeValues->pluck('value')->implode(', ') : 'Standard';
            
            $sheet->setCellValue('A' . $row, $item->invoice);
            $sheet->setCellValue('B' . $row, Carbon::parse($item->date)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $item->customer_name);
            $sheet->setCellValue('D' . $row, $item->product->name ?? 'Deleted');
            $sheet->setCellValue('E' . $row, $item->product->style_number ?? '-');
            $sheet->setCellValue('F' . $row, $variation);
            $sheet->setCellValue('G' . $row, $item->unit_price);
            $sheet->setCellValue('H' . $row, $item->quantity);
            $sheet->setCellValue('I' . $row, $item->total_price);
            $sheet->setCellValue('J' . $row, $item->discount);
            $sheet->setCellValue('K' . $row, $item->source);
            $row++;
        }

        $filename = "sale_report_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportSalePdf($items, $startDate, $endDate)
    {
        $pdf = Pdf::loadView('erp.reports.pdf.sale', [
            'items' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'total_qty' => $items->sum('quantity'),
                'total_amount' => $items->sum('total_price'),
                'total_discount' => $items->sum('discount'),
            ]
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('sale_report_' . date('Y-m-d') . '.pdf');
    }
    public function profitLossReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view financial reports')) {
            abort(403, 'Unauthorized action.');
        }
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        // --- INCOME SIDE (Revenue & Inflows) ---
        
        // 1. Operating Revenue (Net Sales)
        // We rely on the Journal Entries (General Ledger) for the most accurate revenue figure.
        // This automatically excludes VAT (Liability) and includes Net Sales from POS and Online orders.
        $revenueQuery = \App\Models\JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_entries.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->where('chart_of_account_types.name', 'like', 'Revenue%')
            ->whereBetween('journals.entry_date', [$startDate, $endDate]);

        if ($branchId) $revenueQuery->where('journals.branch_id', $branchId);

        $creditVoucherDetails = $revenueQuery->select('chart_of_accounts.name', \DB::raw('SUM(journal_entries.credit - journal_entries.debit) as amount'))
            ->groupBy('chart_of_accounts.name')
            ->having('amount', '!=', 0)
            ->get();

        $operatingRevenue = $creditVoucherDetails->sum('amount');

        // 2. Stock Valuation (Informational Asset Value)
        $stockQuery = Product::where('type', 'product');
        if ($branchId) {
            $stockQuery->withSum(['branchStocks' => function($q) use ($branchId) { $q->where('branch_id', $branchId); }], 'quantity');
        } else {
            $stockQuery->withSum('variationStocks', 'quantity');
        }
        $stockAmount = $stockQuery->get()->sum(function($p) {
            return ($p->branch_stocks_sum_quantity ?? $p->variation_stocks_sum_quantity ?? 0) * $p->cost;
        });

        // 3. Money Receipts (Manual Non-Journalized Inflows)
        // Note: If manual receipts create journals to revenue accounts, they are already in $operatingRevenue.
        // We only sum payments that are NOT linked to a journal to avoid double counting.
        $moneyReceipt = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('payment_for', 'manual_receipt')
            ->whereNotExists(function($q) {
                $q->select(\DB::raw(1))
                  ->from('journals')
                  ->whereRaw('journals.reference = CONCAT("MR-", payments.id)');
            });
        if ($branchId) {
            $moneyReceipt->where(function($q) use ($branchId) {
                $q->whereHas('pos', function($pq) use ($branchId) { $pq->where('branch_id', $branchId); })
                  ->orWhereHas('invoice.pos', function($ipq) use ($branchId) { $ipq->where('branch_id', $branchId); });
            });
        }
        $moneyReceiptAmount = $moneyReceipt->sum('amount');

        // 4. Purchase Returns (Asset Gain/Liability Reduction)
        // Only count if not already in journals
        $purchaseReturnAmount = PurchaseReturn::whereBetween('return_date', [$startDate, $endDate])
            ->join('purchase_return_items', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id');
        if ($branchId) {
            $purchaseReturnAmount->whereHas('purchase', function($q) use ($branchId) {
                $q->where('ship_location_type', 'branch')->where('location_id', $branchId);
            });
        }
        $purchaseReturnAmount = $purchaseReturnAmount->sum('purchase_return_items.total_price');

        // 5. Exchange Adjustments
        // The value of items received in trade. This represents an inflow of assets.
        $posQuery = Pos::whereBetween('sale_date', [$startDate, $endDate]);
        if ($branchId) $posQuery->where('branch_id', $branchId);
        $exchangeAmount = $posQuery->sum('exchange_amount');

        // 6. Sender Transfer Amount (Value of Stock Received from other branches)
        $senderTransferQuery = \App\Models\StockTransfer::whereBetween('delivered_at', [$startDate, $endDate])->where('status', 'delivered');
        if ($branchId) $senderTransferQuery->where('to_id', $branchId);
        $senderTransferAmount = $senderTransferQuery->sum('total_price');

        // TOTAL INCOME
        $totalIncome = $operatingRevenue + $moneyReceiptAmount + $purchaseReturnAmount + $exchangeAmount + $senderTransferAmount;


        // --- EXPENSE SIDE ---

        // 1. Cost of Goods Sold
        $posCostQuery = PosItem::whereHas('pos', function($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
                if ($branchId) $q->where('branch_id', $branchId);
            });
        $posCost = $posCostQuery->sum(DB::raw('quantity * IFNULL(unit_cost, 0)'));
            
        $onlineCost = OrderItem::whereHas('order', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'cancelled');
            })->sum(DB::raw('quantity * IFNULL(unit_cost, 0)'));
            
        $cogsAmount = $posCost + $onlineCost;

        // 2. Debit Voucher (Net Expenses from Journals)
        $debitVoucherQuery = \App\Models\JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_entries.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->where('chart_of_account_types.name', 'like', 'Expense%')
            ->whereBetween('journals.entry_date', [$startDate, $endDate]);

        if ($branchId) $debitVoucherQuery->where('journals.branch_id', $branchId);
        
        // Detailed breakdown including Salary, Supplier Payments, etc.
        $debitVoucherDetails = $debitVoucherQuery
            ->select('chart_of_accounts.name', \DB::raw('SUM(journal_entries.debit) - SUM(journal_entries.credit) as amount'))
            ->groupBy('chart_of_accounts.name')
            ->having('amount', '>', 0)
            ->get();
            
        $debitVoucher = $debitVoucherDetails->sum('amount');

        // 3. Employee Payment - We no longer sum this directly from salary_payments 
        // because they now create journal entries which are included in $debitVoucher above.
        // We set it to 0 or remove it to avoid double counting.
        $employeePayment = 0; 
        
        // For old data that doesn't have journal entries, we could potentially sum them 
        // if they don't have a linked journal... but better to keep it clean.
        // Let's keep it as 0 to ensure the P&L relies solely on the GL (Journal Entries).

        // 4. Supplier Pay - Similarly, supplier payments should be in journal entries
        $supplierPay = 0; 

        // 5. Sales Returns
        // Note: Sales returns are now handled as a reduction in 'Operating Revenue' 
        // via the Journal Entry debiting the Sales Return account. 
        // We set this to 0 here to avoid double counting in the Expense section.
        $salesReturnAmount = 0;

        // 7. Receiver Transfer Amount (Stock Value out from transfers)
        $receiverTransferQuery = \App\Models\StockTransfer::whereBetween('delivered_at', [$startDate, $endDate])->where('status', 'delivered');
        if ($branchId) $receiverTransferQuery->where('from_id', $branchId);
        $receiverTransferAmount = $receiverTransferQuery->sum('total_price');

        $totalExpense = $cogsAmount + $debitVoucher + $salesReturnAmount + $receiverTransferAmount;
        
        $netProfit = $totalIncome - $totalExpense;
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        $viewData = compact(
            'startDate', 'endDate', 'branches', 'branchId', 'reportType',
            'operatingRevenue', 'creditVoucherDetails', 'stockAmount', 'moneyReceiptAmount', 'purchaseReturnAmount', 'exchangeAmount', 'senderTransferAmount', 'totalIncome',
            'cogsAmount', 'debitVoucher', 'debitVoucherDetails', 'employeePayment', 'supplierPay', 'salesReturnAmount', 'receiverTransferAmount', 'totalExpense',
            'netProfit'
        );

        if ($request->filled('export')) {
            if ($request->export == 'excel') {
                return $this->exportProfitLossExcel($viewData);
            } elseif ($request->export == 'pdf') {
                return $this->exportProfitLossPdf($viewData);
            }
        }

        return view('erp.reports.profit-loss', $viewData);
    }

    public function cashBookReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view financial reports')) {
            abort(403, 'Unauthorized action.');
        }
        return $this->financialBookReport($request, FinancialAccount::TYPE_CASH, 'Cash Book');
    }

    public function bankBookReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view financial reports')) {
            abort(403, 'Unauthorized action.');
        }
        return $this->financialBookReport($request, FinancialAccount::TYPE_BANK, 'Bank Book');
    }

    public function mobileBookReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view financial reports')) {
            abort(403, 'Unauthorized action.');
        }
        return $this->financialBookReport($request, FinancialAccount::TYPE_MOBILE, 'Mobile Book');
    }

    private function financialBookReport(Request $request, $type, $title)
    {
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        $query = FinancialAccount::where('type', $type);
        
        $accounts = $query->get()->map(function($account) use ($startDate, $endDate, $branchId) {
            // Current total balance for consolidated view reference
            $currentTotalBalance = $account->balance;

            if ($branchId) {
                // BRANCH SPECIFIC LOGIC
                // 1. Calculate balance before the period (Opening)
                $openingMovement = JournalEntry::where('financial_account_id', $account->id)
                    ->whereHas('journal', function($q) use ($startDate, $branchId) {
                        $q->where('entry_date', '<', $startDate->toDateString());
                        $q->where('branch_id', $branchId);
                    })
                    ->selectRaw('SUM(debit) as d, SUM(credit) as c')
                    ->first();
                
                $opening = ($openingMovement->d ?? 0) - ($openingMovement->c ?? 0);

                // 2. Calculate balance during the period (Movements)
                $periodMovement = JournalEntry::where('financial_account_id', $account->id)
                    ->whereHas('journal', function($q) use ($startDate, $endDate, $branchId) {
                        $q->whereBetween('entry_date', [$startDate->toDateString(), $endDate->toDateString()]);
                        $q->where('branch_id', $branchId);
                    })
                    ->selectRaw('SUM(debit) as d, SUM(credit) as c')
                    ->first();

                $account->opening = $opening;
                $account->debit = $periodMovement->d ?? 0;
                $account->credit = $periodMovement->c ?? 0;
                $account->closing = $opening + $account->debit - $account->credit;
            } else {
                // CONSOLIDATED LOGIC (Working backwards from current total balance)
                $futureMovement = JournalEntry::where('financial_account_id', $account->id)
                    ->whereHas('journal', function($q) use ($startDate) {
                        $q->where('entry_date', '>=', $startDate->toDateString());
                    })
                    ->selectRaw('SUM(debit) as d, SUM(credit) as c')
                    ->first();

                $opening = $currentTotalBalance - ($futureMovement->d ?? 0) + ($futureMovement->c ?? 0);

                $periodMovement = JournalEntry::where('financial_account_id', $account->id)
                    ->whereHas('journal', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('entry_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    })
                    ->selectRaw('SUM(debit) as d, SUM(credit) as c')
                    ->first();

                $account->opening = $opening;
                $account->debit = $periodMovement->d ?? 0;
                $account->credit = $periodMovement->c ?? 0;
                $account->closing = $opening + $account->debit - $account->credit;
            }

            return $account;
        });

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        if ($request->filled('export')) {
            $data = compact('accounts', 'startDate', 'endDate', 'title', 'branchId', 'branches');
            if ($request->export == 'excel') {
                return $this->exportFinancialBookExcel($data);
            } elseif ($request->export == 'pdf') {
                return $this->exportFinancialBookPdf($data);
            }
        }

        return view('erp.reports.financial-book', compact(
            'accounts', 'startDate', 'endDate', 'reportType', 'title', 'branches', 'branchId'
        ));
    }

    private function exportFinancialBookExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', $data['title']);
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('d M Y') . ' to ' . $data['endDate']->format('d M Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['Account Name', 'Branch', 'Opening', 'Debit', 'Credit', 'Closing'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $col++;
        }

        $row = 5;
        foreach ($data['accounts'] as $account) {
            $sheet->setCellValue('A' . $row, $account->account_holder_name ?? ($account->provider_name . ' - ' . $account->account_number));
            $sheet->setCellValue('B' . $row, $account->branch_name ?? '-');
            $sheet->setCellValue('C' . $row, $account->opening);
            $sheet->setCellValue('D' . $row, $account->debit);
            $sheet->setCellValue('E' . $row, $account->credit);
            $sheet->setCellValue('F' . $row, $account->closing);
            $row++;
        }

        $filename = str_replace(' ', '_', $data['title']) . "_" . date('Ymd') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportFinancialBookPdf($data)
    {
        $pdf = Pdf::loadView('erp.reports.pdf.financial-book', $data)->setPaper('a4', 'portrait');
        return $pdf->download(str_replace(' ', '_', $data['title']) . "_" . date('Y-m-d') . ".pdf");
    }

    private function exportProfitLossExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Profit & Loss Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('d M Y') . ' to ' . $data['endDate']->format('d M Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // INCOME SECTION
        $row = 4;
        $sheet->setCellValue('A'.$row, 'INCOME'); $sheet->setCellValue('B'.$row, 'AMOUNT');
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A'.$row, 'Sales Revenue'); $sheet->setCellValue('B'.$row, $data['salesAmount']); $row++;
        
        // Loop credit vouchers
        if($data['creditVoucherDetails']->isNotEmpty()){
            foreach($data['creditVoucherDetails'] as $detail){
                $sheet->setCellValue('A'.$row, $detail->name); 
                $sheet->setCellValue('B'.$row, $detail->amount); 
                $row++;
            }
        } else {
             $sheet->setCellValue('A'.$row, 'Credit Vouchers'); $sheet->setCellValue('B'.$row, $data['creditVoucher']); $row++;
        }

        $sheet->setCellValue('A'.$row, 'Money Receipts'); $sheet->setCellValue('B'.$row, $data['moneyReceipt']); $row++;
        $sheet->setCellValue('A'.$row, 'Purchase Returns'); $sheet->setCellValue('B'.$row, $data['purchaseReturnAmount']); $row++;
        $sheet->setCellValue('A'.$row, 'Exchange Adjustments'); $sheet->setCellValue('B'.$row, $data['exchangeAmount']); $row++;
        $sheet->setCellValue('A'.$row, 'Transfers In'); $sheet->setCellValue('B'.$row, $data['senderTransferAmount']); $row++;
        
        $sheet->setCellValue('A'.$row, 'TOTAL INCOME'); $sheet->setCellValue('B'.$row, $data['totalIncome']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $row += 2; // Spacer

        // EXPENSE SECTION
        $sheet->setCellValue('A'.$row, 'EXPENSES'); $sheet->setCellValue('B'.$row, 'AMOUNT');
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $row++;
        
        $sheet->setCellValue('A'.$row, 'Cost of Goods Sold'); $sheet->setCellValue('B'.$row, $data['cogsAmount']); $row++;
        

        // Loop debit vouchers
        if($data['debitVoucherDetails']->isNotEmpty()){
             foreach($data['debitVoucherDetails'] as $detail){
                $sheet->setCellValue('A'.$row, $detail->name); 
                $sheet->setCellValue('B'.$row, $detail->amount); 
                $row++;
            }
        } else {
             $sheet->setCellValue('A'.$row, 'Debit Vouchers'); $sheet->setCellValue('B'.$row, $data['debitVoucher']); $row++;
        }

        $sheet->setCellValue('A'.$row, 'Employee Salaries'); $sheet->setCellValue('B'.$row, $data['employeePayment']); $row++;
        $sheet->setCellValue('A'.$row, 'Supplier Payments'); $sheet->setCellValue('B'.$row, $data['supplierPay']); $row++;
        $sheet->setCellValue('A'.$row, 'Sales Returns'); $sheet->setCellValue('B'.$row, $data['salesReturnAmount']); $row++;
        $sheet->setCellValue('A'.$row, 'Transfers Out'); $sheet->setCellValue('B'.$row, $data['receiverTransferAmount']); $row++;

        $sheet->setCellValue('A'.$row, 'TOTAL EXPENSE'); $sheet->setCellValue('B'.$row, $data['totalExpense']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $row += 2;

        $sheet->setCellValue('A'.$row, 'NET PROFIT / LOSS'); $sheet->setCellValue('B'.$row, $data['netProfit']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true)->setSize(12);

        $row += 2;
        $sheet->setCellValue('A'.$row, '* Current Stock Value (Asset Info)'); $sheet->setCellValue('B'.$row, $data['stockAmount']);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ProfitLoss_Report_'.date('Ymd').'.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportProfitLossPdf($data)
    {
        $pdf = Pdf::loadView('erp.reports.pdf.profit-loss', $data)->setPaper('a4', 'portrait');
        return $pdf->download('ProfitLoss_Report_' . date('Y-m-d') . '.pdf');
    }

    public function customerReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }

        $reportType = $request->get('report_type', 'yearly');
        $now = Carbon::now();
        
        if ($reportType == 'daily') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfDay();
            $endDate = $startDate->copy()->endOfDay();
        } elseif ($reportType == 'monthly') {
            $month = $request->get('month', $now->month);
            $year = $request->get('year', $now->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', $now->year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else { // Custom
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        // Initial Customer Query with Filters
        $customerQuery = Customer::query();
        if ($request->filled('name')) {
            $customerQuery->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('phone')) {
            $customerQuery->where('mobile', 'like', '%' . $request->phone . '%');
        }

        // Handle Export
        $isExport = $request->get('export') == 'excel';
        
        if ($isExport) {
            $customers = $customerQuery->get();
        } else {
            $customers = $customerQuery->paginate(50)->appends($request->all());
        }

        $customerIds = $customers->pluck('id')->toArray();

        // 1. Sales & Exchange (from Pos table)
        $salesQuery = Pos::selectRaw('customer_id, 
            SUM(CASE WHEN sale_date < ? THEN total_amount ELSE 0 END) as opening_sales,
            SUM(CASE WHEN sale_date < ? THEN discount ELSE 0 END) as opening_discount,
            SUM(CASE WHEN sale_date < ? THEN exchange_amount ELSE 0 END) as opening_exchange,
            SUM(CASE WHEN sale_date BETWEEN ? AND ? THEN total_amount ELSE 0 END) as period_sales,
            SUM(CASE WHEN sale_date BETWEEN ? AND ? THEN discount ELSE 0 END) as period_discount,
            SUM(CASE WHEN sale_date BETWEEN ? AND ? THEN exchange_amount ELSE 0 END) as period_exchange
        ', [$startDate, $startDate, $startDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate])
        ->whereIn('customer_id', $customerIds)
        ->groupBy('customer_id');
        
        if ($branchId) $salesQuery->where('branch_id', $branchId);
        $salesStats = $salesQuery->get()->keyBy('customer_id');

        // 2. Payments (Mixed POS and Manual)
        $paymentQuery = Payment::query();
        if ($branchId) {
             $paymentQuery->where(function($q) use ($branchId) {
                $q->whereHas('pos', fn($pq) => $pq->where('branch_id', $branchId))
                  ->orWhereHas('invoice.pos', fn($ipq) => $ipq->where('branch_id', $branchId))
                  ->orWhere('payment_for', 'manual_receipt'); 
             });
        }
        
        $paymentStats = $paymentQuery->selectRaw('customer_id,
            SUM(CASE WHEN payment_date < ? THEN amount ELSE 0 END) as opening_paid,
            SUM(CASE WHEN payment_date BETWEEN ? AND ? AND payment_for = "manual_receipt" THEN amount ELSE 0 END) as period_manual,
            SUM(CASE WHEN payment_date BETWEEN ? AND ? AND (payment_for IS NULL OR payment_for != "manual_receipt") THEN amount ELSE 0 END) as period_paid
        ', [$startDate, $startDate, $endDate, $startDate, $endDate])
        ->whereIn('customer_id', $customerIds)
        ->whereNotNull('customer_id')
        ->groupBy('customer_id')
        ->get()
        ->keyBy('customer_id');

        // 3. Returns (SaleReturn)
        $returnQuery = SaleReturn::query()
            ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id');
        
        if ($branchId) $returnQuery->where('return_to_id', $branchId);

        $returnStats = $returnQuery->selectRaw('customer_id,
            SUM(CASE WHEN return_date < ? THEN sale_return_items.total_price ELSE 0 END) as opening_return,
            SUM(CASE WHEN return_date BETWEEN ? AND ? THEN sale_return_items.total_price ELSE 0 END) as period_return
        ', [$startDate, $startDate, $endDate])
        ->whereIn('customer_id', $customerIds)
        ->groupBy('customer_id')
        ->get()
        ->keyBy('customer_id');

        // 4. Initial Balances (from Balance table)
        $initialBalances = Balance::where('source_type', 'customer')
            ->whereIn('source_id', $customerIds)
            ->selectRaw('source_id as customer_id, 
                SUM(CASE WHEN created_at < ? THEN balance ELSE 0 END) as opening_bal
            ', [$startDate])
            ->groupBy('source_id')
            ->get()
            ->keyBy('customer_id');

        // Merge Data
        $customerCollection = $isExport ? $customers : $customers->getCollection();
        
        $customerCollection->transform(function($customer) use ($salesStats, $paymentStats, $returnStats, $initialBalances, $branchId) {
            $sid = $customer->id;
            
            $s = $salesStats[$sid] ?? null;
            $p = $paymentStats[$sid] ?? null;
            $r = $returnStats[$sid] ?? null;
            $b = $initialBalances[$sid] ?? null;

            $op_sales = $s->opening_sales ?? 0;
            $op_paid = $p->opening_paid ?? 0; 
            $op_return = $r->opening_return ?? 0;
            $op_exchange = $s->opening_exchange ?? 0;
            $op_init = $b->opening_bal ?? 0;

            $opening = $op_init + $op_sales - ($op_paid + $op_return + $op_exchange);

            $period_sales = $s->period_sales ?? 0;
            $period_manual = $p->period_manual ?? 0;
            $period_paid = $p->period_paid ?? 0; 
            $period_return = $r->period_return ?? 0;
            $period_exchange = $s->period_exchange ?? 0;
            $period_discount = $s->period_discount ?? 0;

            $due = $opening + $period_sales - ($period_paid + $period_manual + $period_return + $period_exchange);
            
            $customer->outlet = $branchId ? (\App\Models\Branch::find($branchId)->name ?? 'Main') : 'All';
            $customer->opening = $opening;
            $customer->sales = $period_sales;
            $customer->paid = $period_paid;
            $customer->payment = $period_manual;
            $customer->discount = $period_discount;
            $customer->return = $period_return;
            $customer->exchange = $period_exchange;
            $customer->due = $due;

            // Mark for removal if all values are zero
            $customer->is_empty = ($opening == 0 && $period_sales == 0 && $period_paid == 0 && $period_manual == 0 && $period_return == 0 && $period_exchange == 0 && $due == 0);

            return $customer;
        });

        if ($isExport) {
            return $this->exportCustomerReportExcelFile($customers, $startDate, $endDate);
        }

        // Filter out empty customers if desired (to avoid 2027 showing data for everyone)
        if ($request->filled('hide_empty') || $reportType != 'custom') {
             // Optional: You could filter here, but pagination might get weird. 
             // However, for "2027", we definitely want to see only those with standing balances or new activity.
        }

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        if ($request->ajax()) {
            return view('erp.reports.partials.customer-report-table', compact('customers'))->render();
        }

        return view('erp.reports.customer-report', compact('customers', 'startDate', 'endDate', 'reportType', 'branches', 'branchId'));
    }

    public function customerLedger(Request $request, $id = null)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }

        $customerId = $id ?: $request->get('customer_id');
        $customers = Customer::orderBy('name')->get();
        $reportType = $request->get('report_type', 'all');
        $startDate = null;
        $endDate = null;
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        if (!$customerId) {
            $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();
            return view('erp.reports.customer-ledger', compact('customers', 'branches', 'branchId', 'reportType', 'startDate', 'endDate'));
        }

        $customer = Customer::findOrFail($customerId);
        $now = Carbon::now();

        if ($reportType == 'daily') {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } elseif ($reportType == 'monthly') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }

        // Opening Balance calculation (Consolidated)
        $openingBalance = 0;
        if ($startDate) {
            // 1. Initial Balance (No branch filtering usually, but we check if it exists before startDate)
            $op_init = Balance::where('source_type', 'customer')
                ->where('source_id', $customerId)
                ->where('created_at', '<', $startDate)
                ->sum('balance');

            // 2. Previous Sales
            $salesQ = Pos::where('customer_id', $customerId)->where('sale_date', '<', $startDate->toDateString());
            if ($branchId) $salesQ->where('branch_id', $branchId);
            $op_sales = $salesQ->sum('total_amount') - $salesQ->sum('exchange_amount');

            // 3. Previous Payments
            $payQ = Payment::where('customer_id', $customerId)->where('payment_date', '<', $startDate->toDateString());
            if ($branchId) {
                $payQ->where(function($q) use ($branchId) {
                    $q->whereHas('pos', fn($pq) => $pq->where('branch_id', $branchId))
                      ->orWhereHas('invoice.pos', fn($ipq) => $ipq->where('branch_id', $branchId))
                      ->orWhere('payment_for', 'manual_receipt');
                });
            }
            $op_paid = $payQ->sum('amount');

            // 4. Previous Returns
            $retQ = SaleReturn::where('customer_id', $customerId)->where('return_date', '<', $startDate->toDateString());
            if ($branchId) $retQ->where('return_to_id', $branchId)->where('return_to_type', 'branch');
            $op_return = $retQ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                               ->sum('sale_return_items.total_price');

            $openingBalance = $op_init + $op_sales - ($op_paid + $op_return);
        }

        // Transactions Fetching
        // 1. POS Sales (Debit)
        $posQuery = Pos::where('customer_id', $customerId);
        if ($branchId) $posQuery->where('branch_id', $branchId);
        if ($startDate) $posQuery->where('sale_date', '>=', $startDate->toDateString());
        if ($endDate) $posQuery->where('sale_date', '<=', $endDate->toDateString());
        $posSales = $posQuery->get()->map(fn($p) => [
            'date' => $p->sale_date,
            'type' => 'POS Sale',
            'reference' => $p->invoice_number,
            'debit' => $p->total_amount - $p->exchange_amount,
            'credit' => 0,
            'note' => $p->exchange_amount > 0 ? "Sale with Exchange (Net Debit)" : "POS Transaction"
        ]);

        // 2. Payments (Credit)
        $payQuery = Payment::where('customer_id', $customerId);
        if ($branchId) {
            $payQuery->where(function($q) use ($branchId) {
                $q->whereHas('pos', fn($pq) => $pq->where('branch_id', $branchId))
                  ->orWhereHas('invoice.pos', fn($ipq) => $ipq->where('branch_id', $branchId))
                  ->orWhere('payment_for', 'manual_receipt');
            });
        }
        if ($startDate) $payQuery->where('payment_date', '>=', $startDate->toDateString());
        if ($endDate) $payQuery->where('payment_date', '<=', $endDate->toDateString());
        $payments = $payQuery->get()->map(fn($p) => [
            'date' => $p->payment_date,
            'type' => 'Payment (' . str_replace('_', ' ', $p->payment_for) . ')',
            'reference' => $p->payment_reference ?: ($p->transaction_id ?: 'PAY-'.$p->id),
            'debit' => 0,
            'credit' => $p->amount,
            'note' => $p->note
        ]);

        // 3. Returns (Credit)
        $retQuery = SaleReturn::where('customer_id', $customerId);
        if ($branchId) {
            $retQuery->where('return_to_id', $branchId)->where('return_to_type', 'branch');
        }
        if ($startDate) $retQuery->where('return_date', '>=', $startDate->toDateString());
        if ($endDate) $retQuery->where('return_date', '<=', $endDate->toDateString());
        $returns = $retQuery->with('items')->get()->map(fn($r) => [
            'date' => $r->return_date,
            'type' => 'Sale Return',
            'reference' => 'RET-'.$r->id,
            'debit' => 0,
            'credit' => $r->items->sum('total_price'),
            'note' => $r->reason
        ]);

        $transactions = $posSales->concat($payments)->concat($returns)->sortBy('date');
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        if ($request->get('export') == 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.reports.pdf.customer-ledger', compact(
                'customer', 'transactions', 'openingBalance', 'startDate', 'endDate', 'branches', 'branchId'
            ))->setPaper('a4', 'portrait');
            
            return $pdf->download("Customer_Ledger_{$customer->name}.pdf");
        }

        if ($request->get('export') == 'excel') {
            $filename = "Customer_Ledger_{$customer->name}_" . date('Ymd_His') . ".xlsx";
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header
            $sheet->setCellValue('A1', "Statement of Customer Ledger: " . $customer->name);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            
            $sheet->setCellValue('A2', "Period: " . ($startDate ? $startDate->format('d M, Y') : 'Life-to-date') . " - " . ($endDate ? $endDate->format('d M, Y') : date('d M, Y')));
            
            // Table Headers
            $headers = ['Date', 'Transaction Detail', 'Reference', 'Debit', 'Credit', 'Balance'];
            foreach ($headers as $index => $header) {
                $sheet->setCellValue(chr(65 + $index) . '4', $header);
                $sheet->getStyle(chr(65 + $index) . '4')->getFont()->setBold(true);
                $sheet->getStyle(chr(65 + $index) . '4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCACACA');
            }
            
            $row = 5;
            $runningBalance = $openingBalance ?? 0;
            
            // Opening Balance
            $sheet->setCellValue('A' . $row, '-');
            $sheet->setCellValue('B' . $row, 'PREVIOUS OPENING BALANCE');
            $sheet->setCellValue('C' . $row, '-');
            $sheet->setCellValue('D' . $row, '-');
            $sheet->setCellValue('E' . $row, '-');
            $sheet->setCellValue('F' . $row, number_format($runningBalance, 2) . ($runningBalance > 0 ? ' DR' : ' CR'));
            $sheet->getStyle('B' . $row)->getFont()->setItalic(true);
            $row++;
            
            foreach ($transactions as $txn) {
                $runningBalance += ($txn['debit'] - $txn['credit']);
                $sheet->setCellValue('A' . $row, \Carbon\Carbon::parse($txn['date'])->format('d M, Y'));
                $sheet->setCellValue('B' . $row, $txn['type'] . ($txn['note'] ? " ({$txn['note']})" : ""));
                $sheet->setCellValue('C' . $row, $txn['reference']);
                $sheet->setCellValue('D' . $row, $txn['debit']);
                $sheet->setCellValue('E' . $row, $txn['credit']);
                $sheet->setCellValue('F' . $row, number_format(abs($runningBalance), 2) . ($runningBalance > 0 ? ' DR' : ' CR'));
                $row++;
            }
            
            // Summary Footer
            $sheet->setCellValue('C' . $row, 'TOTAL');
            $sheet->setCellValue('D' . $row, $transactions->sum('debit'));
            $sheet->setCellValue('E' . $row, $transactions->sum('credit'));
            $sheet->setCellValue('F' . $row, number_format(abs($runningBalance), 2) . ($runningBalance > 0 ? ' FINAL DUE' : ' FINAL ADVANCE'));
            $sheet->getStyle('C'.$row.':F'.$row)->getFont()->setBold(true);
            
            foreach (range('A', 'F') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filePath = storage_path('app/public/' . $filename);
            $writer->save($filePath);
            
            return response()->download($filePath, $filename)->deleteFileAfterSend();
        }

        return view('erp.reports.customer-ledger', compact('customer', 'customers', 'transactions', 'openingBalance', 'startDate', 'endDate', 'reportType', 'branches', 'branchId'));
    }

    public function supplierReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }

        $reportType = $request->get('report_type', 'daily');
        $now = Carbon::now();

        if ($reportType == 'monthly') {
            $month = $request->get('month', $now->month);
            $year = $request->get('year', $now->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', $now->year);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } elseif ($reportType == 'custom') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        $suppliers = Supplier::orderBy('name')->get()->map(function($s) use ($startDate, $endDate, $branchId) {
            $sid = $s->id;

            // 1. Purchase Bills
            $billQuery = PurchaseBill::where('supplier_id', $sid);
            if ($branchId) {
                $billQuery->whereHas('purchase', fn($q) => $q->where('location_id', $branchId)->where('ship_location_type', 'branch'));
            }
            
            $op_purchase = (clone $billQuery)->where('bill_date', '<', $startDate->toDateString())->sum('total_amount');
            $period_purchase = (clone $billQuery)->whereBetween('bill_date', [$startDate->toDateString(), $endDate->toDateString()])->sum('total_amount');

            // 2. Payments (Granular)
            $payQuery = SupplierPayment::where('supplier_id', $sid);
            if ($branchId) {
                $payQuery->whereHas('bill.purchase', fn($q) => $q->where('location_id', $branchId)->where('ship_location_type', 'branch'));
            }

            $op_paid = (clone $payQuery)->where('payment_date', '<', $startDate->toDateString())->sum('amount');
            
            // Period Paid (Regular bill payments)
            $period_paid = (clone $payQuery)->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereNotNull('purchase_bill_id')
                ->sum('amount');
            
            // Period Payment (Advance or manual)
            $period_manual = (clone $payQuery)->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->whereNull('purchase_bill_id')
                ->sum('amount');

            // 3. Returns
            $returnQuery = PurchaseReturn::where('supplier_id', $sid);
            if ($branchId) {
                $returnQuery->whereHas('items', fn($q) => $q->where('return_from_type', 'branch')->where('return_from_id', $branchId));
            }

            $op_return = (clone $returnQuery)->where('return_date', '<', $startDate->toDateString())->get()->sum('total_amount');
            $period_return = (clone $returnQuery)->whereBetween('return_date', [$startDate->toDateString(), $endDate->toDateString()])->get()->sum('total_amount');

            $opening = $op_purchase - ($op_paid + $op_return);
            $due = $opening + $period_purchase - ($period_paid + $period_manual + $period_return);

            return (object)[
                'id' => $sid,
                'name' => $s->name,
                'mobile' => $s->phone,
                'supplier_id' => $s->supplier_id ?? 'SUP-'.$sid,
                'opening' => $opening,
                'purchase' => $period_purchase,
                'paid' => $period_paid,
                'payment' => $period_manual,
                'return' => $period_return,
                'due' => $due,
                'outlet' => $branchId ? (\App\Models\Branch::find($branchId)->name ?? 'Branch') : 'All Outlets',
                'is_empty' => ($opening == 0 && $period_purchase == 0 && $period_paid == 0 && $period_manual == 0 && $period_return == 0 && $due == 0)
            ];
        });

        $totals = [
            'opening' => $suppliers->sum('opening'),
            'purchase' => $suppliers->sum('purchase'),
            'paid' => $suppliers->sum('paid'),
            'payment' => $suppliers->sum('payment'),
            'return' => $suppliers->sum('return'),
            'due' => $suppliers->sum('due')
        ];

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        if ($request->get('export') == 'excel') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', 'Supplier Summary Report');
            $sheet->mergeCells('A1:K1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->setCellValue('A2', "Period: " . $startDate->format('d M, Y') . " - " . $endDate->format('d M, Y'));
            $sheet->setCellValue('A3', "Outlet: " . ($branchId ? (\App\Models\Branch::find($branchId)->name ?? 'All') : 'All Outlets'));

            $headers = ['#SN', 'Supplier Name', 'Supplier ID', 'Mobile', 'Outlet', 'Opening', 'Total', 'Paid', 'Payment', 'Return', 'Due'];
            foreach ($headers as $i => $h) {
                $col = chr(65 + $i);
                $sheet->setCellValue($col . '5', $h);
                $sheet->getStyle($col . '5')->getFont()->setBold(true);
                $sheet->getStyle($col . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1B4D3E');
                $sheet->getStyle($col . '5')->getFont()->getColor()->setARGB('FFFFFFFF');
            }

            $row = 6;
            $sl = 1;
            foreach ($suppliers as $s) {
                if ($s->is_empty) continue;
                $sheet->setCellValue('A' . $row, $sl++);
                $sheet->setCellValue('B' . $row, $s->name);
                $sheet->setCellValue('C' . $row, $s->supplier_id);
                $sheet->setCellValue('D' . $row, $s->mobile);
                $sheet->setCellValue('E' . $row, $s->outlet);
                $sheet->setCellValue('F' . $row, $s->opening);
                $sheet->setCellValue('G' . $row, $s->purchase);
                $sheet->setCellValue('H' . $row, $s->paid);
                $sheet->setCellValue('I' . $row, $s->payment);
                $sheet->setCellValue('J' . $row, $s->return);
                $sheet->setCellValue('K' . $row, $s->due);
                $sheet->getStyle('F' . $row . ':K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $row++;
            }

            // Total Row
            $sheet->setCellValue('A' . $row, 'GRAND TOTAL');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('F' . $row, $totals['opening']);
            $sheet->setCellValue('G' . $row, $totals['purchase']);
            $sheet->setCellValue('H' . $row, $totals['paid']);
            $sheet->setCellValue('I' . $row, $totals['payment']);
            $sheet->setCellValue('J' . $row, $totals['return']);
            $sheet->setCellValue('K' . $row, $totals['due']);
            $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);
            $sheet->getStyle('F' . $row . ':K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $row . ':K' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');

            foreach (range('A', 'K') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

            $filename = "Supplier_Summary_" . date('Ymd_His') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        if ($request->ajax()) {
            return view('erp.reports.partials.supplier-report-table', compact('suppliers', 'totals'))->render();
        }

        return view('erp.reports.supplier-report', compact('suppliers', 'branches', 'branchId', 'reportType', 'startDate', 'endDate', 'totals'));
    }

    public function supplierLedger(Request $request, $id = null)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }
        $id = $id ?: $request->get('supplier_id');
        $suppliers = Supplier::orderBy('name')->get();
        if (!$id) {
            $startDate = null; $endDate = null; $reportType = 'all'; $branchId = null;
            $branches = \App\Models\Branch::all();
            return view('erp.reports.supplier-ledger', compact('suppliers', 'branches', 'branchId', 'startDate', 'endDate', 'reportType'));
        }

        $supplier = Supplier::findOrFail($id);
        $reportType = $request->get('report_type', 'all');
        $now = Carbon::now();

        if ($reportType == 'daily') {
            $startDate = $now->copy()->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } elseif ($reportType == 'monthly') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        // Helper for Stats
        $getStats = function($from = null, $to = null) use ($id, $branchId) {
            $purchaseQuery = PurchaseBill::where('supplier_id', $id);
            if ($branchId) {
                $purchaseQuery->whereHas('purchase', function($q) use ($branchId) {
                    $q->where('location_id', $branchId)->where('ship_location_type', 'branch');
                });
            }
            if ($from) $purchaseQuery->where('bill_date', '>=', $from->toDateString());
            if ($to) $purchaseQuery->where('bill_date', '<=', $to->toDateString());
            $purchases = $purchaseQuery->get()->map(fn($p) => [
                'date' => $p->bill_date,
                'type' => 'Purchase Bill',
                'reference' => $p->bill_number,
                'debit' => 0,
                'credit' => $p->total_amount,
                'note' => $p->description
            ]);

            $payQuery = SupplierPayment::where('supplier_id', $id);
            if ($branchId) {
                $payQuery->whereHas('bill.purchase', function($q) use ($branchId) {
                    $q->where('location_id', $branchId)->where('ship_location_type', 'branch');
                });
            }
            if ($from) $payQuery->where('payment_date', '>=', $from->toDateString());
            if ($to) $payQuery->where('payment_date', '<=', $to->toDateString());
            $payments = $payQuery->get()->map(fn($p) => [
                'date' => $p->payment_date,
                'type' => 'Payment',
                'reference' => $p->reference ?: 'PAY-'.$p->id,
                'debit' => $p->amount,
                'credit' => 0,
                'note' => $p->note
            ]);

            $retQuery = PurchaseReturn::where('supplier_id', $id);
            if ($branchId) {
                $retQuery->whereHas('items', function($q) use ($branchId) {
                    $q->where('return_from_type', 'branch')->where('return_from_id', $branchId);
                });
            }
            if ($from) $retQuery->where('return_date', '>=', $from->toDateString());
            if ($to) $retQuery->where('return_date', '<=', $to->toDateString());
            $returns = $retQuery->with('items')->get()->map(fn($r) => [
                'date' => $r->return_date,
                'type' => 'Purchase Return',
                'reference' => 'RET-'.$r->id,
                'debit' => $r->total_amount,
                'credit' => 0,
                'note' => $r->notes
            ]);

            return $purchases->concat($payments)->concat($returns);
        };

        $openingBalance = 0;
        if ($startDate) {
            $openingStats = $getStats(null, $startDate->copy()->subDay());
            $openingBalance = $openingStats->sum('credit') - $openingStats->sum('debit');
        }

        $transactions = $getStats($startDate, $endDate)->sortBy('date');
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        if ($request->get('export') == 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.reports.pdf.supplier-ledger', compact(
                'supplier', 'transactions', 'openingBalance', 'startDate', 'endDate', 'branches', 'branchId'
            ))->setPaper('a4', 'portrait');
            return $pdf->download("Supplier_Ledger_{$supplier->name}.pdf");
        }

        if ($request->get('export') == 'excel') {
            $filename = "Supplier_Ledger_{$supplier->name}_" . date('Ymd_His') . ".xlsx";
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', "Supplier Ledger Account: " . $supplier->name);
            $sheet->setCellValue('A2', "Period: " . ($startDate ? $startDate->format('d M, Y') : 'Life-to-date') . " - " . ($endDate ? $endDate->format('d M, Y') : date('d M, Y')));
            $headers = ['Date', 'Transaction Detail', 'Reference', 'Debit (Payment)', 'Credit (Purchase)', 'Balance'];
            foreach ($headers as $index => $header) {
                $cell = chr(65 + $index) . '4';
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }
            $row = 5; $runningBalance = $openingBalance;
            $sheet->setCellValue('B'.$row, 'OPENING BALANCE');
            $sheet->setCellValue('F'.$row, number_format(abs($runningBalance), 2) . ($runningBalance > 0 ? ' CR' : ' DR'));
            foreach ($transactions as $txn) {
                $row++;
                $runningBalance += ($txn['credit'] - $txn['debit']);
                $sheet->setCellValue('A'.$row, Carbon::parse($txn['date'])->format('d M, Y'));
                $sheet->setCellValue('B'.$row, $txn['type']);
                $sheet->setCellValue('C'.$row, $txn['reference']);
                $sheet->setCellValue('D'.$row, $txn['debit']);
                $sheet->setCellValue('E'.$row, $txn['credit']);
                $sheet->setCellValue('F'.$row, number_format(abs($runningBalance), 2) . ($runningBalance > 0 ? ' CR' : ' DR'));
            }
            foreach (range('A', 'F') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $path = storage_path('app/public/'.$filename);
            $writer->save($path);
            return response()->download($path, $filename)->deleteFileAfterSend();
        }

        return view('erp.reports.supplier-ledger', compact('supplier', 'suppliers', 'transactions', 'openingBalance', 'startDate', 'endDate', 'reportType', 'branches', 'branchId'));
    }


    public function stockReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');
        $warehouseId = $request->get('warehouse_id');

        $query = Product::with(['category', 'brand', 'branchStocks.branch', 'warehouseStocks.warehouse', 'variationStocks'])
            ->where('type', 'product');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get()->map(function($p) use ($branchId, $warehouseId) {
            if ($p->has_variations) {
                if ($branchId) {
                    $p->total_stock = $p->variationStocks->where('branch_id', $branchId)->sum('quantity');
                } elseif ($warehouseId) {
                    $p->total_stock = $p->variationStocks->where('warehouse_id', $warehouseId)->sum('quantity');
                } else {
                    $p->total_stock = $p->variationStocks->sum('quantity');
                }
            } else {
                if ($branchId) {
                    $p->total_stock = $p->branchStocks->where('branch_id', $branchId)->sum('quantity');
                } elseif ($warehouseId) {
                    $p->total_stock = $p->warehouseStocks->where('warehouse_id', $warehouseId)->sum('quantity');
                } else {
                    $p->total_stock = $p->branchStocks->sum('quantity') + $p->warehouseStocks->sum('quantity');
                }
            }
            
            $p->stock_value = $p->total_stock * $p->cost;
            $p->potential_revenue = $p->total_stock * $p->price;
            return $p;
        });

        $categories = ProductServiceCategory::whereNull('parent_id')->get();
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();

        return view('erp.reports.stock-report', compact('products', 'categories', 'branches', 'branchId', 'warehouses', 'warehouseId'));
    }

    public function executiveReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view executive reports')) {
            abort(403, 'Unauthorized action.');
        }
        $reportType = $request->get('report_type', 'daily');
        $branchId = $request->get('branch_id');
        $restrictedBranchId = $this->getRestrictedBranchId();
        
        if ($restrictedBranchId) {
            $branchId = $restrictedBranchId;
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        if ($reportType == 'monthly') {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }

        // --- 1. SALES SUMMARY ---
        // POS Sales
        $posQuery = Pos::whereBetween('sale_date', [$startDate, $endDate]);
        if ($branchId) $posQuery->where('branch_id', $branchId);
        $posSales = $posQuery->selectRaw('COUNT(*) as count, SUM(sub_total) as subtotal, SUM(discount) as total_discount, SUM(total_amount) as net_sales')->first();

        // Online Sales
        $onlineQuery = \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'cancelled');
        $onlineSales = $onlineQuery->selectRaw('COUNT(*) as count, SUM(subtotal) as subtotal, SUM(coupon_discount) as total_discount, SUM(total) as net_sales')->first();

        // Branch Isolation for Online Sales
        if ($branchId) {
            $selectedBranch = \App\Models\Branch::find($branchId);
            if (!$selectedBranch || !$selectedBranch->show_online) {
                $onlineSales = (object)['count' => 0, 'subtotal' => 0, 'total_discount' => 0, 'net_sales' => 0];
            }
        }

        // Extra Revenue Sources (Secondary Income from GL)
        $creditVoucher = \App\Models\JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_entries.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->where('chart_of_account_types.name', 'like', 'Revenue%')
            ->whereBetween('journals.entry_date', [$startDate, $endDate]);

        if ($branchId) $creditVoucher->where('journals.branch_id', $branchId);
        $creditVoucher = $creditVoucher->sum('journal_entries.credit') - $creditVoucher->sum('journal_entries.debit');

        // New Robust Expense Query (Matches P&L)
        $debitVoucherQuery = \App\Models\JournalEntry::join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_entries.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->join('chart_of_account_types', 'chart_of_accounts.type_id', '=', 'chart_of_account_types.id')
            ->where('chart_of_account_types.name', 'like', 'Expense%')
            ->whereBetween('journals.entry_date', [$startDate, $endDate]);

        if ($branchId) $debitVoucherQuery->where('journals.branch_id', $branchId);

        $operatingExpenses = $debitVoucherQuery->select('chart_of_accounts.name', \DB::raw('SUM(journal_entries.debit - journal_entries.credit) as total'))
            ->groupBy('chart_of_accounts.name')
            ->having('total', '>', 0)
            ->get();

        // 2. Money Receipt
        $mrQuery = \App\Models\Payment::whereBetween('payment_date', [$startDate, $endDate])->where('payment_for', 'manual_receipt');
        if ($branchId) {
            $mrQuery->where(function($q) use ($branchId) {
                $q->whereHas('pos', function($pq) use ($branchId) { $pq->where('branch_id', $branchId); })
                  ->orWhereHas('invoice.pos', function($ipq) use ($branchId) { $ipq->where('branch_id', $branchId); });
            });
        }
        $moneyReceipt = $mrQuery->sum('amount');

        // 3. Purchase Returns (Income/Recovery)
        $purchaseReturnQuery = \App\Models\PurchaseReturn::whereBetween('return_date', [$startDate, $endDate])
            ->join('purchase_return_items', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id');
        if ($branchId) {
            $purchaseReturnQuery->whereHas('purchase', function($q) use ($branchId) {
                $q->where('ship_location_type', 'branch')->where('location_id', $branchId);
            });
        }
        $purchaseReturnAmount = $purchaseReturnQuery->sum('purchase_return_items.total_price');

        // 4. Exchange Amount (Income)
        // Re-using posQuery from sales which already has date/branch filters
        $exchangeAmount = $posQuery->sum('exchange_amount');

        // 5. Sender Transfer Amount (Money in from transfers)
        $senderTransferQuery = \App\Models\StockTransfer::whereBetween('delivered_at', [$startDate, $endDate])->where('status', 'delivered');
        if ($branchId) $senderTransferQuery->where('to_id', $branchId);
        $senderTransferAmount = $senderTransferQuery->sum('paid_amount');


        // --- 2. COGS ---
        // POS Cost
        $posCostQuery = PosItem::whereHas('pos', function($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
                if ($branchId) $q->where('branch_id', $branchId);
            });
        $posCost = $posCostQuery->sum(DB::raw('quantity * IFNULL(unit_cost, 0)'));

        // Online Cost
        $onlineCost = 0;
        if (!$branchId || (isset($selectedBranch) && $selectedBranch->show_online)) {
            $onlineCostQuery = OrderItem::whereHas('order', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'cancelled');
                });
            $onlineCost = $onlineCostQuery->sum(DB::raw('quantity * IFNULL(unit_cost, 0)'));
        }

        $totalCogs = $posCost + $onlineCost;

        // --- 3. EXPENSES (Consolidated from GL + Returns/Transfers) ---
        // 4. Sales Returns (Expense/Contra-Revenue)
        $salesReturnQuery = \App\Models\SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id');
        if ($branchId) {
            $salesReturnQuery->where('return_to_type', 'branch')->where('return_to_id', $branchId);
        }
        $salesReturnAmount = $salesReturnQuery->sum('sale_return_items.total_price');
        
        if ($salesReturnAmount > 0) {
            $operatingExpenses->push((object)['name' => 'Sales Returns', 'total' => $salesReturnAmount]);
        }

        // 5. Receiver Transfer Amount (Money Out)
        $receiverTransferQuery = \App\Models\StockTransfer::whereBetween('delivered_at', [$startDate, $endDate])->where('status', 'delivered');
        if ($branchId) $receiverTransferQuery->where('from_id', $branchId);
        $receiverTransferAmount = $receiverTransferQuery->sum('paid_amount');

        if ($receiverTransferAmount > 0) {
            $operatingExpenses->push((object)['name' => 'Transfers Out', 'total' => $receiverTransferAmount]);
        }

        $totalExpenses = $operatingExpenses->sum('total');

        // --- 4. STOCK VALUATION (Snapshot) ---
        $stockQuery = Product::where('type', 'product');
        if ($branchId) {
            $stockQuery->withSum(['branchStocks' => function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }], 'quantity');
        } else {
            $stockQuery->withSum('variationStocks', 'quantity');
        }
        
        $stockProducts = $stockQuery->get();
        $totalCostVal = $stockProducts->sum(function($p) {
            return ($p->branch_stocks_sum_quantity ?? $p->variation_stocks_sum_quantity ?? 0) * $p->cost;
        });
        $totalMrpVal = $stockProducts->sum(function($p) {
            return ($p->branch_stocks_sum_quantity ?? $p->variation_stocks_sum_quantity ?? 0) * $p->price;
        });
        $totalWholesaleVal = $stockProducts->sum(function($p) {
            return ($p->branch_stocks_sum_quantity ?? $p->variation_stocks_sum_quantity ?? 0) * ($p->wholesale_price > 0 ? $p->wholesale_price : $p->cost);
        });

        $stockValue = (object)[
            'total_cost' => $totalCostVal,
            'total_mrp' => $totalMrpVal,
            'total_wholesale' => $totalWholesaleVal
        ];

        // --- FINAL CALCULATION ---
        $otherIncome = $creditVoucher + $moneyReceipt + $purchaseReturnAmount + $exchangeAmount + $senderTransferAmount;
        $grossRevenue = ($posSales->net_sales ?? 0) + ($onlineSales->net_sales ?? 0) + $otherIncome;
        
        // Cogs is already calculated
        $grossProfit = $grossRevenue - $totalCogs;
        
        // Expenses
        $totalExpenses = $operatingExpenses->sum('total'); // Now includes Returns + TransfersOut

        $netProfit = $grossProfit - $totalExpenses;

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType,
            'branchId' => $branchId,
            'branches' => $branches,
            'posSales' => $posSales,
            'onlineSales' => $onlineSales,
            'creditVoucher' => $creditVoucher,
            'moneyReceipt' => $moneyReceipt,
            'purchaseReturnAmount' => $purchaseReturnAmount,
            'exchangeAmount' => $exchangeAmount,
            'senderTransferAmount' => $senderTransferAmount,
            'otherIncome' => $otherIncome,
            'posCost' => $posCost,
            'onlineCost' => $onlineCost,
            'totalCogs' => $totalCogs,
            'operatingExpenses' => $operatingExpenses,
            'totalExpenses' => $totalExpenses,
            'stockValue' => $stockValue,
            'grossRevenue' => $grossRevenue,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'month' => $month,
            'year' => $year
        ];

        if ($request->filled('export')) {
            if ($request->export == 'excel') {
                return $this->exportExecutiveExcel($data);
            } elseif ($request->export == 'pdf') {
                return $this->exportExecutivePdf($data);
            }
        }

        if ($request->ajax()) {
            return view('erp.reports.executive-report-partial', $data);
        }

        return view('erp.reports.executive-report', $data);
    }

    private function exportExecutiveExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Executive Business Performance Report');
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('d M Y') . ' to ' . $data['endDate']->format('d M Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        // Revenue Section
        $sheet->setCellValue('A4', 'SECTION 1: REVENUE');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->setCellValue('A5', 'Retail (POS) Sales'); $sheet->setCellValue('B5', $data['posSales']->net_sales ?? 0);
        $sheet->setCellValue('A6', 'Online Sales'); $sheet->setCellValue('B6', $data['onlineSales']->net_sales ?? 0);
        $sheet->setCellValue('A7', 'Other Income (Vouchers, Receipts, Returns, etc)'); $sheet->setCellValue('B7', $data['otherIncome']);
        $sheet->setCellValue('A8', 'Total Gross Revenue'); $sheet->setCellValue('B8', $data['grossRevenue']);
        $sheet->getStyle('A8:B8')->getFont()->setBold(true);
        
        // COGS Section
        $sheet->setCellValue('A10', 'SECTION 2: COST OF GOODS SOLD (COGS)');
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->setCellValue('A11', 'Product Cost (Inventory Value Gone)'); $sheet->setCellValue('B11', $data['totalCogs']);
        $sheet->setCellValue('A12', 'GROSS PROFIT'); $sheet->setCellValue('B12', $data['grossProfit']);
        $sheet->getStyle('A12:B12')->getFont()->setBold(true);

        // Expenses Section
        $sheet->setCellValue('A14', 'SECTION 3: OPERATING EXPENSES (Bills & Salaries)');
        $sheet->getStyle('A14')->getFont()->setBold(true);
        $row = 15;
        foreach($data['operatingExpenses'] as $exp) {
            $sheet->setCellValue('A'.$row, $exp->name);
            $sheet->setCellValue('B'.$row, $exp->total);
            $row++;
        }
        $sheet->setCellValue('A'.$row, 'Total Expenses'); $sheet->setCellValue('B'.$row, $data['totalExpenses']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true);
        $row += 2;

        // Final Line
        $sheet->setCellValue('A'.$row, 'THE BOTTOM LINE (NET PROFIT)');
        $sheet->setCellValue('B'.$row, $data['netProfit']);
        $sheet->getStyle('A'.$row.':B'.$row)->getFont()->setBold(true)->setSize(14);
        
        // Stock Section
        $row += 2;
        $sheet->setCellValue('A'.$row, 'SECTION 4: CURRENT ASSET VALUE (STOCK)');
        $sheet->getStyle('A'.$row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A'.$row, 'Stock Value at Purchase Price'); $sheet->setCellValue('B'.$row, $data['stockValue']->total_cost); $row++;
        $sheet->setCellValue('A'.$row, 'Stock Value at Wholesale Price'); $sheet->setCellValue('B'.$row, $data['stockValue']->total_wholesale); $row++;
        $sheet->setCellValue('A'.$row, 'Stock Value at MRP (Retail)'); $sheet->setCellValue('B'.$row, $data['stockValue']->total_mrp);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Executive_Report_'.date('Ymd').'.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportExecutivePdf($data)
    {
        $pdf = Pdf::loadView('erp.reports.pdf.executive', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Executive_Report_' . date('Y-m-d') . '.pdf');
    }

    public function expenseReport(Request $request)
    {
        $reportType = $request->get('report_type', 'daily'); // Just for UI state
        
        // Date Logic: Always respect the input dates if provided, otherwise default to today.
        // The frontend toggles will handle setting these inputs.
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
        } else {
            $startDate = Carbon::now()->startOfDay();
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } else {
            $endDate = Carbon::now()->endOfDay();
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');
        $expenseCategoryId = $request->get('expense_category_id');

        // 1. Find all account IDs that are categorized as "Expense"
    $expenseTypeIds = \App\Models\ChartOfAccountType::where('name', 'like', 'Expense%')->pluck('id')->toArray();
    $expenseAccountIds = \App\Models\ChartOfAccount::whereIn('type_id', $expenseTypeIds)->pluck('id')->toArray();

    // 2. Main Query: Start from JournalEntry as the primary source
    $journalQuery = \App\Models\JournalEntry::query()
        ->select([
            'journals.entry_date as date',
            'journals.voucher_no as ref_no',
            'chart_of_accounts.name as category',
            'journals.branch_id',
            'journals.description as note',
            'journal_entries.debit as amount',
            'journal_entries.memo as entry_memo'
        ])
        ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
        ->join('chart_of_accounts', 'journal_entries.chart_of_account_id', '=', 'chart_of_accounts.id')
        ->whereIn('journal_entries.chart_of_account_id', $expenseAccountIds)
        ->whereBetween('journals.entry_date', [$startDate, $endDate])
        ->where('journal_entries.debit', '>', 0);

    // Filters
    if ($branchId) $journalQuery->where('journals.branch_id', $branchId);
    if ($expenseCategoryId) $journalQuery->where('chart_of_accounts.id', $expenseCategoryId);

    $expenses = $journalQuery->orderBy('journals.entry_date', 'desc')->get()->map(function ($item) {
        return [
            'date' => $item->date,
            'ref_no' => $item->ref_no,
            'category' => $item->category,
            'branch' => $item->branch_id ? (\App\Models\Branch::find($item->branch_id)->name ?? 'Main') : 'Head Office',
            'note' => $item->note ?: $item->entry_memo,
            'amount' => $item->amount
        ];
    });

    $expenseCategories = \App\Models\ChartOfAccount::whereIn('id', $expenseAccountIds)->get();
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        // Handle Export
        if ($request->filled('export')) {
            $data = [
                'expenses' => $expenses,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalAmount' => $expenses->sum('amount'),
                'branchName' => $branchId ? (\App\Models\Branch::find($branchId)->name ?? 'All') : 'All Branches'
            ];

            if ($request->export == 'excel') {
                return $this->exportExpenseExcel($data);
            } elseif ($request->export == 'pdf') {
                return $this->exportExpensePdf($data);
            }
        }

        // Return JSON for AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('erp.reports.partials.expense-rows', compact('expenses'))->render(),
                'total_amount' => number_format($expenses->sum('amount'), 2),
                'date_range' => $startDate->format('Y-m-d') . ' - ' . $endDate->format('Y-m-d')
            ]);
        }

        return view('erp.reports.expense-report', compact('expenses', 'startDate', 'endDate', 'reportType', 'branches', 'branchId', 'expenseCategories'));
    }

    private function exportExpenseExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Expense Report - Sisal Fashion');
        $sheet->setCellValue('A2', 'Period: ' . $data['startDate']->format('d M Y') . ' to ' . $data['endDate']->format('d M Y'));
        $sheet->setCellValue('A3', 'Branch: ' . $data['branchName']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['Date', 'Ref No', 'Category', 'Branch', 'Note', 'Amount'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $sheet->getStyle($col . '5')->getFont()->setBold(true);
            $col++;
        }

        $row = 6;
        foreach ($data['expenses'] as $exp) {
            $sheet->setCellValue('A' . $row, $exp['date']);
            $sheet->setCellValue('B' . $row, $exp['ref_no']);
            $sheet->setCellValue('C' . $row, $exp['category']);
            $sheet->setCellValue('D' . $row, $exp['branch']);
            $sheet->setCellValue('E' . $row, $exp['note']);
            $sheet->setCellValue('F' . $row, $exp['amount']);
            $row++;
        }

        $sheet->setCellValue('E' . $row, 'TOTAL:');
        $sheet->setCellValue('F' . $row, $data['totalAmount']);
        $sheet->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);

        $filename = "Expense_Report_" . date('Ymd') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function performanceReport(Request $request, \App\Services\PerformanceReportService $service)
    {
        if (!auth()->user()->hasPermissionTo('view reports')) {
            abort(403, 'Unauthorized action.');
        }

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $branchId = $request->get('branch_id');
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) $branchId = $restrictedBranchId;

        if ($request->ajax() || $request->filled('export')) {
            $finalData = $service->getPerformanceData(
                $startDate, 
                $endDate, 
                $branchId, 
                $request->get('product_id'), 
                $request->get('category_id')
            );

            if ($request->filled('export')) {
                if ($request->export == 'excel') return $this->exportPerformanceExcel($finalData, $startDate, $endDate);
                if ($request->export == 'pdf') return $this->exportPerformancePdf($finalData, $startDate, $endDate);
            }

            return response()->json([
                'data' => $finalData,
                'summary' => [
                    'total_net_sale' => $finalData->sum('net_sale_amount'),
                    'total_net_cost' => $finalData->sum('net_purchase_cost'),
                    'total_profit' => $finalData->sum('gross_profit'),
                ]
            ]);
        }

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();
        $categories = ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();

        return view('erp.reports.performance', compact('startDate', 'endDate', 'branches', 'branchId', 'categories'));
    }

    private function exportPerformanceExcel($data, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sales vs Purchase Performance Report');
        $sheet->setCellValue('A2', 'Period: ' . $startDate->format('d/m/Y') . ' to ' . $endDate->format('d/m/Y'));
        
        $headers = ['Product', 'Style #', 'Variation', 'Sold Qty', 'Ret Qty', 'Net Qty', 'Net Sale', 'Net Cost', 'Profit', 'Margin %'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $col++;
        }

        $row = 5;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->product_name);
            $sheet->setCellValue('B' . $row, $item->style_number);
            $sheet->setCellValue('C' . $row, $item->variation_name);
            $sheet->setCellValue('D' . $row, $item->sold_qty);
            $sheet->setCellValue('E' . $row, $item->returned_qty);
            $sheet->setCellValue('F' . $row, $item->net_qty);
            $sheet->setCellValue('G' . $row, $item->net_sale_amount);
            $sheet->setCellValue('H' . $row, $item->net_purchase_cost);
            $sheet->setCellValue('I' . $row, $item->gross_profit);
            $sheet->setCellValue('J' . $row, number_format($item->profit_margin, 2) . '%');
            $row++;
        }

        $filename = "Performance_Report_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function exportPerformancePdf($data, $startDate, $endDate)
    {
        $summary = [
            'total_sale' => $data->sum('net_sale_amount'),
            'total_cost' => $data->sum('net_purchase_cost'),
            'total_profit' => $data->sum('gross_profit'),
        ];
        $pdf = Pdf::loadView('erp.reports.pdf.performance', compact('data', 'startDate', 'endDate', 'summary'))->setPaper('a4', 'landscape');
        return $pdf->download('Performance_Report_' . date('Y-m-d') . '.pdf');
    }

    private function exportExpensePdf($data)
    {
        $pdf = Pdf::loadView('erp.reports.pdf.expense', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Expense_Report_' . date('Y-m-d') . '.pdf');
    }

    private function exportCustomerReportExcelFile($customers, $startDate, $endDate)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Customer Summary Report');
        $sheet->setCellValue('A2', 'Period: ' . $startDate->format('d M, Y') . ' - ' . $endDate->format('d M, Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $headers = ['SL', 'Customer Name', 'Phone', 'Outlet', 'Opening', 'Sales', 'Paid', 'Payment', 'Discount', 'Return', 'Exchange', 'Closing Due'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $sheet->getStyle($col . '4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCACACA');
            $col++;
        }
        
        $row = 5;
        $sl = 1;
        $tOpening = 0; $tSales = 0; $tPaid = 0; $tPayment = 0; $tDiscount = 0; $tReturn = 0; $tExchange = 0; $tDue = 0;

        foreach ($customers as $c) {
            if ($c->is_empty) continue;
            
            $sheet->setCellValue('A' . $row, $sl++);
            $sheet->setCellValue('B' . $row, $c->name);
            $sheet->setCellValue('C' . $row, $c->mobile ?? 'N/A');
            $sheet->setCellValue('D' . $row, $c->outlet);
            $sheet->setCellValue('E' . $row, $c->opening);
            $sheet->setCellValue('F' . $row, $c->sales);
            $sheet->setCellValue('G' . $row, $c->paid);
            $sheet->setCellValue('H' . $row, $c->payment);
            $sheet->setCellValue('I' . $row, $c->discount);
            $sheet->setCellValue('J' . $row, $c->return);
            $sheet->setCellValue('K' . $row, $c->exchange);
            $sheet->setCellValue('L' . $row, $c->due);
            
            $tOpening += $c->opening; $tSales += $c->sales; $tPaid += $c->paid; 
            $tPayment += $c->payment; $tDiscount += $c->discount;
            $tReturn += $c->return; $tExchange += $c->exchange; $tDue += $c->due;

            // Format numbers
            $sheet->getStyle('E'.$row.':L'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        // Add Grand Total Row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        $sheet->setCellValue('E' . $row, $tOpening);
        $sheet->setCellValue('F' . $row, $tSales);
        $sheet->setCellValue('G' . $row, $tPaid);
        $sheet->setCellValue('H' . $row, $tPayment);
        $sheet->setCellValue('I' . $row, $tDiscount);
        $sheet->setCellValue('J' . $row, $tReturn);
        $sheet->setCellValue('K' . $row, $tExchange);
        $sheet->setCellValue('L' . $row, $tDue);

        $sheet->getStyle('A' . $row . ':L' . $row)->getFont()->setBold(true);
        $sheet->getStyle('E' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $row . ':L' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
        
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'Customer_Summary_Report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
