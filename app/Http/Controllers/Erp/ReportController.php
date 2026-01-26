<?php


namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
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

        $query = PurchaseItem::with(['purchase.supplier', 'purchase.bill', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation.attributeValues'])
            ->whereHas('purchase', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
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
        $challans = Purchase::latest()->take(100)->get();

        return view('erp.reports.purchase', compact(
            'items', 'summary', 'suppliers', 'categories', 'brands', 'seasons', 'genders', 'products', 'challans', 'startDate', 'endDate', 'reportType'
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
}
