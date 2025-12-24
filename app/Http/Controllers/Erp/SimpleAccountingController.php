<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pos;
use App\Models\PosItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\BranchProductStock;
use App\Models\WarehouseProductStock;
use App\Models\ProductVariationStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SimpleAccountingController extends Controller
{
    /**
     * Sales Summary Report
     */
    public function salesSummary(Request $request)
    {
        $dateRange = $request->get('range', 'week');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();

        // Get sales data
        $salesData = $this->getSalesData($startDate, $endDate);
        
        // Get cost data
        $costData = $this->getCostData($startDate, $endDate);
        
        // Calculate profit
        $profitData = $this->calculateProfitData($salesData, $costData);

        return view('erp.simple-accounting.sales-summary', compact(
            'salesData',
            'costData', 
            'profitData',
            'dateRange',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Sales Report (Combined Product and Category)
     */
    public function salesReport(Request $request)
    {
        $dateRange = $request->get('range', 'month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();

        $onlineProductProfits = $this->getProductProfits($startDate, $endDate, 'online');
        $onlineCategoryProfits = $this->getCategoryProfits($startDate, $endDate, 'online');
        
        $posProductProfits = $this->getProductProfits($startDate, $endDate, 'pos');
        $posCategoryProfits = $this->getCategoryProfits($startDate, $endDate, 'pos');

        return view('erp.simple-accounting.sales-report', compact(
            'onlineProductProfits',
            'onlineCategoryProfits',
            'posProductProfits',
            'posCategoryProfits',
            'dateRange',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Top Products Report
     */
    public function topProducts(Request $request)
    {
        $dateRange = $request->get('range', 'month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();
        $limit = $request->get('limit', 10);

        // Get top products by revenue
        $topByRevenue = $this->getTopProductsByRevenue($startDate, $endDate, $limit);
        
        // Get top products by profit
        $topByProfit = $this->getTopProductsByProfit($startDate, $endDate, $limit);
        
        // Get top products by quantity sold
        $topByQuantity = $this->getTopProductsByQuantity($startDate, $endDate, $limit);

        return view('erp.simple-accounting.top-products', compact(
            'topByRevenue',
            'topByProfit',
            'topByQuantity',
            'dateRange',
            'startDate',
            'endDate',
            'limit'
        ));
    }

    /**
     * Stock Value Report
     */
    public function stockValue(Request $request)
    {
        // Get stock value by product
        $productStockValues = $this->getProductStockValues();
        
        // Get stock value by category
        $categoryStockValues = $this->getCategoryStockValues();
        
        // Get total stock value
        $totalStockValue = $productStockValues->sum('total_value');

        return view('erp.simple-accounting.stock-value', compact(
            'productStockValues',
            'categoryStockValues',
            'totalStockValue'
        ));
    }


    /**
     * Get Sales Report Data for Modal
     */
    public function getSalesDataReport(Request $request)
    {
        $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfDay();
        $type = $request->get('type', 'product');
        $source = $request->get('source', 'all');

        if ($type === 'category') {
            $data = $this->getCategoryProfits($startDate, $endDate, $source);
            $transformed = $data->values()->map(function($item) {
                return [
                    'name' => $item['category_name'],
                    'product_count' => $item['product_count'],
                    'quantity_sold' => $item['quantity_sold'],
                    'revenue' => number_format($item['revenue'], 2),
                    'cost' => number_format($item['cost'], 2),
                    'profit' => number_format($item['profit'], 2),
                ];
            });
            $summary = [
                'total_revenue' => number_format($data->sum('revenue'), 2),
                'total_profit' => number_format($data->sum('profit'), 2),
                'total_items' => $data->sum('quantity_sold'),
            ];
        } else {
            $data = $this->getProductProfits($startDate, $endDate, $source);
            $transformed = $data->values()->map(function($item) {
                return [
                    'name' => $item['product']->name ?? 'Deleted Product',
                    'category' => $item['product']->category->name ?? 'Uncategorized',
                    'quantity_sold' => $item['quantity_sold'],
                    'revenue' => number_format($item['revenue'], 2),
                    'cost' => number_format($item['cost'], 2),
                    'profit' => number_format($item['profit'], 2),
                ];
            });
            $summary = [
                'total_revenue' => number_format($data->sum('revenue'), 2),
                'total_profit' => number_format($data->sum('profit'), 2),
                'total_items' => $data->sum('quantity_sold'),
            ];
        }

        return response()->json([
            'data' => $transformed,
            'summary' => $summary
        ]);
    }

    /**
     * Export Sales Report to Excel
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfDay();
        $type = $request->get('type', 'product');
        $source = $request->get('source', 'all');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        if ($type === 'category') {
            $data = $this->getCategoryProfits($startDate, $endDate, $source);
            $sheet->setCellValue('A1', 'Category Wise Sales Report');
            $sheet->setCellValue('A3', 'Category');
            $sheet->setCellValue('B3', 'Products');
            $sheet->setCellValue('C3', 'Qty Sold');
            $sheet->setCellValue('D3', 'Revenue');
            $sheet->setCellValue('E3', 'Cost');
            $sheet->setCellValue('F3', 'Profit');
            
            $row = 4;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item['category_name']);
                $sheet->setCellValue('B' . $row, $item['product_count']);
                $sheet->setCellValue('C' . $row, $item['quantity_sold']);
                $sheet->setCellValue('D' . $row, $item['revenue']);
                $sheet->setCellValue('E' . $row, $item['cost']);
                $sheet->setCellValue('F' . $row, $item['profit']);
                $row++;
            }
        } else {
            $data = $this->getProductProfits($startDate, $endDate, $source);
            $sheet->setCellValue('A1', 'Product Wise Sales Report');
            $sheet->setCellValue('A3', 'Product');
            $sheet->setCellValue('B3', 'Category');
            $sheet->setCellValue('C3', 'Qty Sold');
            $sheet->setCellValue('D3', 'Revenue');
            $sheet->setCellValue('E3', 'Cost');
            $sheet->setCellValue('F3', 'Profit');
            
            $row = 4;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item['product']->name ?? 'Deleted Product');
                $sheet->setCellValue('B' . $row, $item['product']->category->name ?? 'Uncategorized');
                $sheet->setCellValue('C' . $row, $item['quantity_sold']);
                $sheet->setCellValue('D' . $row, $item['revenue']);
                $sheet->setCellValue('E' . $row, $item['cost']);
                $sheet->setCellValue('F' . $row, $item['profit']);
                $row++;
            }
        }

        $filename = "sales_report_{$type}_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Sales Report to PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $endDate = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfDay();
        $type = $request->get('type', 'product');
        $source = $request->get('source', 'all');

        if ($type === 'category') {
            $data = $this->getCategoryProfits($startDate, $endDate, $source);
            $view = 'erp.simple-accounting.exports.category-sales-pdf';
        } else {
            $data = $this->getProductProfits($startDate, $endDate, $source);
            $view = 'erp.simple-accounting.exports.product-sales-pdf';
        }

        $pdf = Pdf::loadView($view, [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->download("sales_report_{$type}_" . date('Y-m-d') . ".pdf");
    }

    /**
     * Get sales data for date range
     */
    private function getSalesData($startDate, $endDate)
    {
        // Get orders data
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->get();

        // Get COD percentage from settings
        $generalSetting = \App\Models\GeneralSetting::first();
        $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;

        // Calculate revenue excluding delivery charges and applying COD discount for COD orders
        $orderRevenue = $orders->sum(function($order) use ($codPercentage) {
            $revenue = $order->total - ($order->delivery ?? 0);
            
            // Apply COD discount for COD orders (cash payment method)
            if ($order->payment_method === 'cash' && $codPercentage > 0) {
                $codDiscount = round($order->total * $codPercentage, 2);
                $revenue = $revenue - $codDiscount;
            }
            
            return $revenue;
        });
        $orderCount = $orders->count();

        // Get POS data
        $posSales = Pos::whereBetween('sale_date', [$startDate, $endDate])->get();
        
        // Calculate revenue excluding delivery charges (only product prices)
        $posRevenue = $posSales->sum(function($pos) {
            return $pos->total_amount - ($pos->delivery ?? 0);
        });
        $posCount = $posSales->count();

        return [
            'order_revenue' => $orderRevenue,
            'order_count' => $orderCount,
            'pos_revenue' => $posRevenue,
            'pos_count' => $posCount,
            'total_revenue' => $orderRevenue + $posRevenue,
            'total_sales_count' => $orderCount + $posCount
        ];
    }

    /**
     * Get cost data for date range
     */
    private function getCostData($startDate, $endDate)
    {
        // Get order items with costs
        $orderItems = OrderItem::whereHas('order', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', '!=', 'cancelled');
        })->with('product')->get();

        $orderCosts = 0;
        foreach ($orderItems as $item) {
            $cost = $item->product->cost ?? 0;
            $orderCosts += $cost * $item->quantity;
        }

        // Get POS items with costs
        $posItems = PosItem::whereHas('pos', function($query) use ($startDate, $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        })->with('product')->get();

        $posCosts = 0;
        foreach ($posItems as $item) {
            $cost = $item->product->cost ?? 0;
            $posCosts += $cost * $item->quantity;
        }

        return [
            'order_costs' => $orderCosts,
            'pos_costs' => $posCosts,
            'total_costs' => $orderCosts + $posCosts
        ];
    }

    /**
     * Calculate profit data
     */
    private function calculateProfitData($salesData, $costData)
    {
        $grossProfit = $salesData['total_revenue'] - $costData['total_costs'];
        $profitMargin = $salesData['total_revenue'] > 0 ? 
            ($grossProfit / $salesData['total_revenue']) * 100 : 0;

        return [
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'cost_percentage' => $salesData['total_revenue'] > 0 ? 
                ($costData['total_costs'] / $salesData['total_revenue']) * 100 : 0
        ];
    }

    /**
     * Get product profits
     */
    private function getProductProfits($startDate, $endDate, $source = 'all')
    {
        // Get COD percentage from settings
        $generalSetting = \App\Models\GeneralSetting::first();
        $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;

        $orders = collect();
        if ($source === 'all' || $source === 'online') {
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->with('items.product')
                ->get();
        }

        $posItems = collect();
        if ($source === 'all' || $source === 'pos') {
            $posItems = PosItem::whereHas('pos', function($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            })->with('product')->get();
        }

        $productProfits = collect();

        // Process order items with COD discount applied
        foreach ($orders as $order) {
            // Calculate COD discount for this order if it's COD
            $orderCodDiscount = 0;
            if ($order->payment_method === 'cash' && $codPercentage > 0) {
                $orderCodDiscount = round($order->total * $codPercentage, 2);
            }

            // Calculate total item revenue for this order (excluding delivery)
            $orderItemsTotal = $order->items->sum(function($item) {
                return $item->unit_price * $item->quantity;
            });

            // Process each item in the order
            foreach ($order->items as $item) {
                // Skip if product is null (deleted product)
                if (!$item->product) {
                    continue;
                }

                $productId = $item->product_id;
                $itemRevenue = $item->unit_price * $item->quantity;
                
                // Apply COD discount proportionally to this item
                $itemCodDiscount = 0;
                if ($orderCodDiscount > 0 && $orderItemsTotal > 0) {
                    // Distribute COD discount proportionally based on item's share of order
                    $itemCodDiscount = round(($itemRevenue / $orderItemsTotal) * $orderCodDiscount, 2);
                }
                
                $revenue = $itemRevenue - $itemCodDiscount;
                $cost = ($item->product->cost ?? 0) * $item->quantity;
                $profit = $revenue - $cost;

                if (!$productProfits->has($productId)) {
                    $productProfits->put($productId, [
                        'product' => $item->product,
                        'revenue' => 0,
                        'cost' => 0,
                        'profit' => 0,
                        'quantity_sold' => 0
                    ]);
                }

                $current = $productProfits->get($productId);
                $productProfits->put($productId, [
                    'product' => $current['product'],
                    'revenue' => $current['revenue'] + $revenue,
                    'cost' => $current['cost'] + $cost,
                    'profit' => $current['profit'] + $profit,
                    'quantity_sold' => $current['quantity_sold'] + $item->quantity
                ]);
            }
        }

        // Process POS items
        foreach ($posItems as $item) {
            // Skip if product is null (deleted product)
            if (!$item->product) {
                continue;
            }

            $productId = $item->product_id;
            $revenue = $item->unit_price * $item->quantity;
            $cost = ($item->product->cost ?? 0) * $item->quantity;
            $profit = $revenue - $cost;

            if (!$productProfits->has($productId)) {
                $productProfits->put($productId, [
                    'product' => $item->product,
                    'revenue' => 0,
                    'cost' => 0,
                    'profit' => 0,
                    'quantity_sold' => 0
                ]);
            }

            $current = $productProfits->get($productId);
            $productProfits->put($productId, [
                'product' => $current['product'],
                'revenue' => $current['revenue'] + $revenue,
                'cost' => $current['cost'] + $cost,
                'profit' => $current['profit'] + $profit,
                'quantity_sold' => $current['quantity_sold'] + $item->quantity
            ]);
        }

        return $productProfits->sortByDesc('profit');
    }

    /**
     * Get category profits
     */
    private function getCategoryProfits($startDate, $endDate, $source = 'all')
    {
        $productProfits = $this->getProductProfits($startDate, $endDate, $source);
        
        $categoryProfits = collect();
        
        foreach ($productProfits as $productId => $data) {
            // Skip if product is null (deleted product)
            if (!$data['product']) {
                continue;
            }

            $categoryId = $data['product']->category_id;
            $categoryName = $data['product']->category->name ?? 'Uncategorized';
            
            if (!$categoryProfits->has($categoryId)) {
                $categoryProfits->put($categoryId, [
                    'category_name' => $categoryName,
                    'revenue' => 0,
                    'cost' => 0,
                    'profit' => 0,
                    'product_count' => 0,
                    'quantity_sold' => 0
                ]);
            }

            $current = $categoryProfits->get($categoryId);
            $categoryProfits->put($categoryId, [
                'category_name' => $current['category_name'],
                'revenue' => $current['revenue'] + $data['revenue'],
                'cost' => $current['cost'] + $data['cost'],
                'profit' => $current['profit'] + $data['profit'],
                'product_count' => $current['product_count'] + 1,
                'quantity_sold' => $current['quantity_sold'] + $data['quantity_sold']
            ]);
        }

        return $categoryProfits->sortByDesc('profit');
    }

    /**
     * Get top products by revenue
     */
    private function getTopProductsByRevenue($startDate, $endDate, $limit)
    {
        $productProfits = $this->getProductProfits($startDate, $endDate);
        return $productProfits->sortByDesc('revenue')->take($limit);
    }

    /**
     * Get top products by profit
     */
    private function getTopProductsByProfit($startDate, $endDate, $limit)
    {
        $productProfits = $this->getProductProfits($startDate, $endDate);
        return $productProfits->sortByDesc('profit')->take($limit);
    }

    /**
     * Get top products by quantity sold
     */
    private function getTopProductsByQuantity($startDate, $endDate, $limit)
    {
        $productProfits = $this->getProductProfits($startDate, $endDate);
        return $productProfits->sortByDesc('quantity_sold')->take($limit);
    }

    /**
     * Get product stock values
     */
    private function getProductStockValues()
    {
        $products = Product::with(['category', 'branchStock', 'warehouseStock', 'variations.stocks'])->get();
        
        $stockValues = collect();
        
        foreach ($products as $product) {
            $totalStock = 0;
            $totalValue = 0;
            
            if ($product->has_variations) {
                foreach ($product->variations as $variation) {
                    $variationStock = $variation->stocks->sum('quantity');
                    $totalStock += $variationStock;
                    $totalValue += $variationStock * ($product->cost ?? 0);
                }
            } else {
                $branchStock = $product->branchStock->sum('quantity');
                $warehouseStock = $product->warehouseStock->sum('quantity');
                $totalStock = $branchStock + $warehouseStock;
                $totalValue = $totalStock * ($product->cost ?? 0);
            }
            
            if ($totalStock > 0) {
                $stockValues->push([
                    'product' => $product,
                    'total_stock' => $totalStock,
                    'unit_cost' => $product->cost ?? 0,
                    'total_value' => $totalValue
                ]);
            }
        }
        
        return $stockValues->sortByDesc('total_value');
    }

    /**
     * Get category stock values
     */
    private function getCategoryStockValues()
    {
        $productStockValues = $this->getProductStockValues();
        
        $categoryValues = collect();
        
        foreach ($productStockValues as $data) {
            $categoryId = $data['product']->category_id;
            $categoryName = $data['product']->category->name ?? 'Uncategorized';
            
            if (!$categoryValues->has($categoryId)) {
                $categoryValues->put($categoryId, [
                    'category_name' => $categoryName,
                    'total_stock' => 0,
                    'total_value' => 0,
                    'product_count' => 0
                ]);
            }

            $current = $categoryValues->get($categoryId);
            $categoryValues->put($categoryId, [
                'category_name' => $current['category_name'],
                'total_stock' => $current['total_stock'] + $data['total_stock'],
                'total_value' => $current['total_value'] + $data['total_value'],
                'product_count' => $current['product_count'] + 1
            ]);
        }

        return $categoryValues->sortByDesc('total_value');
    }

    /**
     * Get start date based on range
     */
    private function getStartDate($range)
    {
        switch ($range) {
            case 'today':
                return Carbon::today();
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'quarter':
                return Carbon::now()->subQuarter();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subMonth();
        }
    }
}
