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

class ReportController extends Controller
{
    public function index()
    {
        return view('erp.reports.index');
    }

    public function purchaseReport(Request $request)
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

        // POS Items Query
        $posQuery = PosItem::with(['pos.customer', 'pos.employee', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation.attributeValues'])
            ->whereHas('pos', function($q) use ($startDate, $endDate, $customerId, $employeeId, $invoiceNo) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
                if ($customerId) $q->where('customer_id', $customerId);
                if ($employeeId) $q->where('created_by', $employeeId);
                if ($invoiceNo) $q->where('invoice_number', 'like', '%' . $invoiceNo . '%');
            });

        // Online Order Items Query
        $orderQuery = OrderItem::with(['order.customer', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation.attributeValues'])
            ->whereHas('order', function($q) use ($startDate, $endDate, $customerId, $invoiceNo) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', '!=', 'cancelled');
                if ($customerId) $q->where('user_id', $customerId);
                if ($invoiceNo) $q->where('order_id', 'like', '%' . $invoiceNo . '%');
                // Online orders don't have 'employee' in the same way, but 'created_by' could be used if tracked
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
        $posSummary = (clone $posQuery)->selectRaw('SUM(quantity) as total_qty, SUM(total_price) as total_amount, SUM(0) as total_discount')->first();
        $orderSummary = (clone $orderQuery)->selectRaw('SUM(quantity) as total_qty, SUM(total_price) as total_amount, SUM(0) as total_discount')->first();

        $summary = [
            'total_qty' => ($posSummary->total_qty ?? 0) + ($orderSummary->total_qty ?? 0),
            'total_amount' => ($posSummary->total_amount ?? 0) + ($orderSummary->total_amount ?? 0),
            'total_discount' => ($posSummary->total_discount ?? 0) + ($orderSummary->total_discount ?? 0),
        ];
        $summary['total_net'] = $summary['total_amount'] - $summary['total_discount'];

        // On-screen List: Limit to top 200 for browser performance
        // (Full data still available via Export)
        $posItems = $posQuery->take(100)->get()->map(function($item) {
            $item->source = 'POS';
            $item->date = $item->pos->sale_date;
            $item->invoice = $item->pos->invoice_number;
            $item->customer_name = $item->pos->customer->name ?? 'Walk-in';
            return $item;
        });

        $orderItems = $orderQuery->take(100)->get()->map(function($item) {
            $item->source = 'Online';
            $item->date = $item->order->created_at;
            $item->invoice = '#' . $item->order->id;
            $item->customer_name = $item->order->customer->first_name ?? ($item->order->first_name . ' ' . $item->order->last_name);
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

        if ($request->filled('export')) {
            if ($request->export == 'excel') {
                return $this->exportSaleExcel($allItems, $startDate, $endDate);
            } elseif ($request->export == 'pdf') {
                return $this->exportSalePdf($allItems, $startDate, $endDate);
            }
        }

        return view('erp.reports.sale', compact(
            'allItems', 'summary', 'customers', 'employees', 'categories', 'brands', 'seasons', 'genders', 'products', 'startDate', 'endDate', 'reportType'
        ));
    }

    private function exportSaleExcel($items, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = ['Order ID', 'Date', 'Customer', 'Product', 'Style #', 'Variation', 'Price', 'Qty', 'Discount', 'Total', 'Source'];
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
            $sheet->setCellValue('I' . $row, $item->discount);
            $sheet->setCellValue('J' . $row, $item->total_price);
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
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // --- INCOME SIDE ---
        
        // 1. Sales Amount (POS Total)
        $salesAmount = Pos::whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');

        // 2. Credit Voucher (Actual data from Journals)
        $creditVoucher = DB::table('journals')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('journals.type', 'Receipt')
            ->join('journal_entries', 'journals.id', '=', 'journal_entries.journal_id')
            ->sum('journal_entries.credit');

        // 3. Stock Amount (Current Stock Valuation by Cost)
        // Note: This is a snapshot of CURRENT stock, not historical.
        $stockAmount = DB::table('products')
            ->selectRaw('products.cost * (
                IFNULL((SELECT SUM(quantity) FROM branch_product_stocks WHERE product_id = products.id), 0) + 
                IFNULL((SELECT SUM(quantity) FROM warehouse_product_stocks WHERE product_id = products.id), 0)
            ) as stock_val')
            ->get()
            ->sum('stock_val');

        // 4. Money Receipt (Placeholder)
        $moneyReceipt = 0;

        // 5. Purchase Returns
        $purchaseReturnAmount = PurchaseReturn::whereBetween('return_date', [$startDate, $endDate])
            ->join('purchase_return_items', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->sum('purchase_return_items.total_price');

        // 6. Exchange Amount
        $exchangeAmount = Pos::whereBetween('sale_date', [$startDate, $endDate])->sum('exchange_amount');

        // 7. Sender Transfer Amount (Placeholder)
        $senderTransferAmount = 0;

        $totalIncome = $salesAmount + $creditVoucher + $stockAmount + $moneyReceipt + $purchaseReturnAmount + $exchangeAmount + $senderTransferAmount;


        // --- EXPENSE SIDE ---

        // 1. Purchase Amount
        $purchaseAmount = PurchaseBill::whereBetween('bill_date', [$startDate, $endDate])->sum('total_amount');

        // 2. Debit Voucher (Actual data from Journals)
        $debitVoucher = DB::table('journals')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('journals.type', 'Payment')
            ->join('journal_entries', 'journals.id', '=', 'journal_entries.journal_id')
            ->sum('journal_entries.debit');

        // 3. Employee Payment (Placeholder)
        $employeePayment = 0;

        // 4. Supplier Pay
        $supplierPay = SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');

        // 5. Sales Returns
        $salesReturnAmount = SaleReturn::whereBetween('return_date', [$startDate, $endDate])
            ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->sum('sale_return_items.total_price');

        // 7. Receiver Transfer Amount (Item 6 is empty in design)
        $receiverTransferAmount = 0;

        $totalExpense = $purchaseAmount + $debitVoucher + $employeePayment + $supplierPay + $salesReturnAmount + $receiverTransferAmount;
        
        $netProfit = $totalIncome - $totalExpense;

        return view('erp.reports.profit-loss', compact(
            'startDate', 'endDate',
            'salesAmount', 'creditVoucher', 'stockAmount', 'moneyReceipt', 'purchaseReturnAmount', 'exchangeAmount', 'senderTransferAmount', 'totalIncome',
            'purchaseAmount', 'debitVoucher', 'employeePayment', 'supplierPay', 'salesReturnAmount', 'receiverTransferAmount', 'totalExpense',
            'netProfit'
        ));
    }

    public function customerReport(Request $request)
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
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        $customers = Customer::all()->map(function($customer) use ($startDate, $endDate, $branchId) {
            // Helper to get totals before or within period
            $getStats = function($from = null, $to = null) use ($customer, $branchId) {
                // Sales
                $salesQuery = Pos::where('customer_id', $customer->id);
                if ($from) $salesQuery->where('sale_date', '>=', $from->toDateString());
                if ($to) $salesQuery->where('sale_date', '<=', $to->toDateString());
                if ($branchId) $salesQuery->where('branch_id', $branchId);
                
                $sales = $salesQuery->sum('total_amount');
                $discount = $salesQuery->sum('discount');
                $exchange = $salesQuery->sum('exchange_amount');

                // Payments (Direct POS)
                $paidPosQuery = Payment::where('payment_for', 'pos')
                    ->where(function($q) use ($customer) {
                        $q->where('customer_id', $customer->id)
                          ->orWhereHas('pos', fn($pq) => $pq->where('customer_id', $customer->id));
                    });
                if ($from) $paidPosQuery->where('payment_date', '>=', $from->toDateString());
                if ($to) $paidPosQuery->where('payment_date', '<=', $to->toDateString());
                if ($branchId) {
                    $paidPosQuery->whereHas('pos', fn($q) => $q->where('branch_id', $branchId));
                }
                $paid = $paidPosQuery->sum('amount');

                // Payments (Manual)
                $manualQuery = Payment::where('payment_for', 'manual_receipt')
                    ->where('customer_id', $customer->id);
                if ($from) $manualQuery->where('payment_date', '>=', $from->toDateString());
                if ($to) $manualQuery->where('payment_date', '<=', $to->toDateString());
                if ($branchId) {
                    $manualQuery->where(function($q) use ($branchId) {
                        $q->whereHas('pos', fn($pq) => $pq->where('branch_id', $branchId))
                          ->orWhereHas('invoice.pos', fn($ipq) => $ipq->where('branch_id', $branchId));
                    });
                }
                $manual = $manualQuery->sum('amount');

                // Returns
                $returnQuery = SaleReturn::where('customer_id', $customer->id);
                if ($from) $returnQuery->where('return_date', '>=', $from->toDateString());
                if ($to) $returnQuery->where('return_date', '<=', $to->toDateString());
                if ($branchId) $returnQuery->where('return_to_id', $branchId)->where('return_to_type', 'branch');
                $return = $returnQuery->with('items')->get()->sum(fn($r) => $r->items->sum('total_price'));

                return (object)[
                    'sales' => $sales,
                    'paid' => $paid,
                    'manual' => $manual,
                    'discount' => $discount,
                    'return' => $return,
                    'exchange' => $exchange
                ];
            };

            // Calculate Opening (Up to day before startDate)
            $openingStats = $getStats(null, $startDate->copy()->subDay());
            $opening = $openingStats->sales - ($openingStats->paid + $openingStats->manual + $openingStats->return + $openingStats->exchange);

            // Calculate Period Stats
            $period = $getStats($startDate, $endDate);

            // Calculate Closing Due
            // Due = Opening + Sales - Paid - Manual - Return - Exchange
            $due = $opening + $period->sales - ($period->paid + $period->manual + $period->return + $period->exchange);

            return (object)[
                'id' => $customer->id,
                'name' => $customer->name,
                'outlet' => $branchId ? (\App\Models\Branch::find($branchId)->name ?? 'Main') : 'All',
                'opening' => $opening,
                'sales' => $period->sales,
                'paid' => $period->paid,
                'payment' => $period->manual,
                'discount' => $period->discount,
                'return' => $period->return,
                'exchange' => $period->exchange,
                'due' => $due
            ];
        });

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        return view('erp.reports.customer-report', compact('customers', 'startDate', 'endDate', 'reportType', 'branches'));
    }

    public function customerLedger(Request $request, $id = null)
    {
        $customerId = $id ?: $request->get('customer_id');
        $customers = Customer::orderBy('name')->get();
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        if (!$customerId) {
            $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();
            return view('erp.reports.customer-ledger', compact('customers', 'branches', 'branchId'));
        }

        $customer = Customer::findOrFail($customerId);
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
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        }

        // Opening Balance calculation
        $openingBalance = 0;
        if ($startDate) {
            $openingBalance = Balance::where('source_type', 'customer')
                ->where('source_id', $customerId)
                ->where('created_at', '<', $startDate)
                ->sum('balance');
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
            'debit' => $p->total_amount,
            'credit' => 0,
            'note' => 'POS Transaction'
        ]);

        // 2. Payments (Credit)
        $payQuery = Payment::where('customer_id', $customerId);
        if ($branchId) {
            $payQuery->where(function($q) use ($branchId) {
                $q->whereHas('pos', fn($pq) => $pq->where('branch_id', $branchId))
                  ->orWhereHas('invoice.pos', fn($ipq) => $ipq->where('branch_id', $branchId));
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

        return view('erp.reports.customer-ledger', compact('customer', 'customers', 'transactions', 'openingBalance', 'startDate', 'endDate', 'reportType', 'branches', 'branchId'));
    }

    public function supplierReport(Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');

        $suppliers = Supplier::all()->map(function($s) use ($branchId) {
            $purchaseQuery = PurchaseBill::where('supplier_id', $s->id);
            $paymentQuery = SupplierPayment::where('supplier_id', $s->id);
            
            if ($branchId) {
                $purchaseQuery->whereHas('purchase', function($q) use ($branchId) {
                    $q->where('location_id', $branchId)->where('ship_location_type', 'branch');
                });
            }

            $s->total_purchase = $purchaseQuery->sum('total_amount');
            $s->total_paid = $paymentQuery->sum('amount');
            $s->due_amount = $s->total_purchase - $s->total_paid;
            return $s;
        });

        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();
        return view('erp.reports.supplier-report', compact('suppliers', 'branches', 'branchId'));
    }

    public function supplierLedger(Request $request, $id)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');
        
        $supplier = Supplier::findOrFail($id);
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $purchaseQuery = PurchaseBill::where('supplier_id', $id)
            ->whereBetween('bill_date', [$startDate, $endDate]);

        if ($branchId) {
            $purchaseQuery->whereHas('purchase', function($q) use ($branchId) {
                $q->where('location_id', $branchId)->where('ship_location_type', 'branch');
            });
        }

        $purchases = $purchaseQuery->get()->map(function($p) {
            return [
                'date' => $p->bill_date,
                'type' => 'Purchase Bill',
                'reference' => $p->bill_number ?? ('#' . $p->purchase_id),
                'debit' => $p->total_amount,
                'credit' => 0,
                'note' => $p->description ?? ''
            ];
        });

        $payQuery = SupplierPayment::where('supplier_id', $id)
            ->whereBetween('payment_date', [$startDate, $endDate]);
            
        // Assuming supplier payments should also follow branch if they are tied to a purchase.
        // If not tied, they stay global for now or we add logic if they have branch_id.

        $payments = $payQuery->get()->map(function($p) {
            return [
                'date' => $p->payment_date,
                'type' => 'Payment',
                'reference' => $p->payment_reference ?? 'PAY-'.$p->id,
                'debit' => 0,
                'credit' => $p->amount,
                'note' => $p->note
            ];
        });

        $transactions = $purchases->concat($payments)->sortBy('date');
        $branches = $restrictedBranchId ? \App\Models\Branch::where('id', $restrictedBranchId)->get() : \App\Models\Branch::all();

        return view('erp.reports.supplier-ledger', compact('supplier', 'transactions', 'startDate', 'endDate', 'branches', 'branchId'));
    }

    public function stockReport(Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branchId = $restrictedBranchId ?: $request->get('branch_id');
        $warehouseId = $request->get('warehouse_id');

        $query = Product::with(['category', 'brand', 'branchStocks.branch', 'warehouseStocks.warehouse', 'variationStocks'])
            ->where('type', 'product');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get()->map(function($p) use ($branchId, $warehouseId) {
            // Updated logic to support specific warehouse or branch filtering
            if ($branchId) {
                // Specific Branch
                $p->total_stock = $p->variationStocks->where('branch_id', $branchId)->sum('quantity');
            } elseif ($warehouseId) {
                // Specific Warehouse
                $p->total_stock = $p->variationStocks->where('warehouse_id', $warehouseId)->sum('quantity');
            } else {
                // All Locations (Branch + Warehouse)
                $p->total_stock = $p->variationStocks->sum('quantity');
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
}
