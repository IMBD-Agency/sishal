<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pos;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Branch;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $request->get('range', 'week');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();

        $stats = $this->getStatistics($startDate, $endDate, $dateRange);
        $salesOverview = $this->getSalesOverview($startDate, $endDate, $dateRange);
        $orderStatus = $this->getOrderStatus($startDate, $endDate);
        $currentInvoices = $this->getCurrentInvoices();
        $topSellingItems = $this->getTopSellingItems($startDate, $endDate);
        $lowStockItems = $this->getLowStockItems();
        $locationPerformance = $this->getLocationPerformance($startDate, $endDate);
        $profitMetrics = $this->getProfitMetrics($startDate, $endDate, $dateRange);
        $channelBreakdown = $this->getChannelBreakdown($startDate, $endDate);

        return view('erp.dashboard', [
            'range' => $dateRange,
            'stats' => $stats,
            'salesOverview' => $salesOverview,
            'orderStatus' => $orderStatus,
            'currentInvoices' => $currentInvoices,
            'topSellingItems' => $topSellingItems,
            'lowStockItems' => $lowStockItems,
            'locationPerformance' => $locationPerformance,
            'profitMetrics' => $profitMetrics,
            'channelBreakdown' => $channelBreakdown
        ]);
    }

    public function getDashboardData(Request $request)
    {
        $dateRange = $request->get('range', 'week');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();

        // Get statistics
        $stats = $this->getStatistics($startDate, $endDate, $dateRange);
        
        // Get sales overview data
        $salesOverview = $this->getSalesOverview($startDate, $endDate, $dateRange);
        
        // Get order status distribution
        $orderStatus = $this->getOrderStatus($startDate, $endDate);
        
        // Get top selling items
        $topSellingItems = $this->getTopSellingItems($startDate, $endDate);
        
        // Get location performance
        $locationPerformance = $this->getLocationPerformance($startDate, $endDate);
        
        // Get current invoices
        $currentInvoices = $this->getCurrentInvoices();
        
        // Get order vs sale comparison
        $comparison = $this->getOrderVsSaleComparison($dateRange);

        return response()->json([
            'stats' => $stats,
            'salesOverview' => $salesOverview,
            'orderStatus' => $orderStatus,
            'topSellingItems' => $topSellingItems,
            'locationPerformance' => $locationPerformance,
            'currentInvoices' => $currentInvoices,
            'comparison' => $comparison
        ]);
    }

    private function getStartDate($range)
    {
        switch ($range) {
            case 'day':
                return Carbon::today();
            case 'week':
                return Carbon::now()->startOfWeek();
            case 'month':
                return Carbon::now()->startOfMonth();
            case 'year':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->startOfWeek();
        }
    }

    private function getStatistics($startDate, $endDate, $range)
    {
        // Get COD percentage from settings
        $generalSetting = \App\Models\GeneralSetting::first();
        $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;

        // Optimized Aggregates for Current Period
        $currentPosData = DB::table('pos')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount - COALESCE(delivery, 0)) as total_sales')
            ->first();

        // Online orders need slightly more logic due to COD percentages
        $currentOrderQuery = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        $currentOrderOrders = $currentOrderQuery->count();
        
        // Sum revenue with COD logic directly in SQL if possible, or lean fetch
        $currentOrderSales = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("SUM(
                (total - COALESCE(delivery, 0)) - 
                CASE 
                    WHEN payment_method = 'cash' THEN ROUND(total * $codPercentage, 2)
                    ELSE 0 
                END
            ) as total_sales")
            ->value('total_sales') ?? 0;

        $previousStartDate = $this->getPreviousPeriodStart($startDate, $range);
        $previousEndDate = $startDate->copy()->subDay();

        // Optimized Aggregates for Previous Period
        $previousPosData = DB::table('pos')
            ->whereBetween('sale_date', [$previousStartDate, $previousEndDate])
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount - COALESCE(delivery, 0)) as total_sales')
            ->first();

        $previousOrderSales = DB::table('orders')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->selectRaw("SUM(
                (total - COALESCE(delivery, 0)) - 
                CASE 
                    WHEN payment_method = 'cash' THEN ROUND(total * $codPercentage, 2)
                    ELSE 0 
                END
            ) as total_sales")
            ->value('total_sales') ?? 0;

        $previousOrderOrders = DB::table('orders')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();

        // Combine Totals
        $currentSales = ($currentPosData->total_sales ?? 0) + $currentOrderSales;
        $currentOrders = ($currentPosData->total_orders ?? 0) + $currentOrderOrders;
        $currentAvgOrder = $currentOrders > 0 ? $currentSales / $currentOrders : 0;

        $previousSales = ($previousPosData->total_sales ?? 0) + $previousOrderSales;
        $previousOrders = ($previousPosData->total_orders ?? 0) + $previousOrderOrders;
        $previousAvgOrder = $previousOrders > 0 ? $previousSales / $previousOrders : 0;

        // Calculate percentages
        $salesPercentage = $previousSales > 0 ? (($currentSales - $previousSales) / $previousSales) * 100 : 0;
        $ordersPercentage = $previousOrders > 0 ? (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;
        $avgOrderPercentage = $previousAvgOrder > 0 ? (($currentAvgOrder - $previousAvgOrder) / $previousAvgOrder) * 100 : 0;

        $satisfactionData = $this->getCustomerSatisfaction($startDate, $endDate);

        return [
            'totalSales' => [
                'value' => number_format($currentSales, 2),
                'percentage' => round($salesPercentage, 1),
                'trend' => $salesPercentage >= 0 ? 'up' : 'down'
            ],
            'totalOrders' => [
                'value' => (int)$currentOrders,
                'percentage' => round($ordersPercentage, 1),
                'trend' => $ordersPercentage >= 0 ? 'up' : 'down'
            ],
            'averageOrder' => [
                'value' => number_format($currentAvgOrder, 2),
                'percentage' => round($avgOrderPercentage, 1),
                'trend' => $avgOrderPercentage >= 0 ? 'up' : 'down'
            ],
            'customerSatisfaction' => [
                'value' => $satisfactionData['rating'],
                'percentage' => $satisfactionData['percentage'],
                'trend' => 'up'
            ]
        ];
    }

    private function getPreviousPeriodStart($startDate, $range)
    {
        switch ($range) {
            case 'day':
                return $startDate->copy()->subDay();
            case 'week':
                return $startDate->copy()->subWeek();
            case 'month':
                return $startDate->copy()->subMonth();
            case 'year':
                return $startDate->copy()->subYear();
            default:
                return $startDate->copy()->subWeek();
        }
    }

    private function getSalesOverview($startDate, $endDate, $range)
    {
        // Get POS data
        $posQuery = Pos::query();
        
        // Get Online Order data
        $orderQuery = Order::query();
        
        switch ($range) {
            case 'day':
                $posQuery->selectRaw('HOUR(sale_date) as period, SUM(total_amount - COALESCE(delivery, 0)) as total')
                      ->whereDate('sale_date', $startDate)
                      ->groupBy('period')
                      ->orderBy('period');
                $orderQuery->selectRaw('HOUR(created_at) as period, SUM(total - COALESCE(delivery, 0)) as total')
                      ->whereDate('created_at', $startDate)
                      ->groupBy('period')
                      ->orderBy('period');
                // 0..23 hours
                $labels = range(0, 23);
                break;
            case 'week':
                $posQuery->selectRaw("DATE_FORMAT(sale_date, '%a') as period, DAYOFWEEK(sale_date) as sort_key, SUM(total_amount - COALESCE(delivery, 0)) as total")
                      ->whereBetween('sale_date', [$startDate, $endDate])
                      ->groupBy('sort_key', 'period')
                      ->orderBy('sort_key');
                $orderQuery->selectRaw("DATE_FORMAT(created_at, '%a') as period, DAYOFWEEK(created_at) as sort_key, SUM(total - COALESCE(delivery, 0)) as total")
                      ->whereBetween('created_at', [$startDate, $endDate])
                      ->groupBy('sort_key', 'period')
                      ->orderBy('sort_key');
                $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                break;
            case 'month':
                $posQuery->selectRaw('DATE(sale_date) as period, SUM(total_amount - COALESCE(delivery, 0)) as total')
                      ->whereBetween('sale_date', [$startDate, $endDate])
                      ->groupBy('period')
                      ->orderBy('period');
                $orderQuery->selectRaw('DATE(created_at) as period, SUM(total - COALESCE(delivery, 0)) as total')
                      ->whereBetween('created_at', [$startDate, $endDate])
                      ->groupBy('period')
                      ->orderBy('period');
                // Generate a label for each day in range
                $labels = [];
                $cursor = $startDate->copy();
                while ($cursor->lte($endDate)) {
                    $labels[] = $cursor->toDateString();
                    $cursor->addDay();
                }
                break;
            case 'year':
                $posQuery->selectRaw("DATE_FORMAT(sale_date, '%b') as period, MONTH(sale_date) as sort_key, SUM(total_amount - COALESCE(delivery, 0)) as total")
                      ->whereBetween('sale_date', [$startDate, $endDate])
                      ->groupBy('sort_key', 'period')
                      ->orderBy('sort_key');
                $orderQuery->selectRaw("DATE_FORMAT(created_at, '%b') as period, MONTH(created_at) as sort_key, SUM(total - COALESCE(delivery, 0)) as total")
                      ->whereBetween('created_at', [$startDate, $endDate])
                      ->groupBy('sort_key', 'period')
                      ->orderBy('sort_key');
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                break;
        }

        $posData = $posQuery->get();
        $orderData = $orderQuery->get();
        $salesData = [];
        
        foreach ($labels as $label) {
            $posPeriodData = $posData->firstWhere('period', $label);
            $orderPeriodData = $orderData->firstWhere('period', $label);
            
            $posTotal = $posPeriodData ? (float)$posPeriodData->total : 0.0;
            $orderTotal = $orderPeriodData ? (float)$orderPeriodData->total : 0.0;
            
            $salesData[] = $posTotal + $orderTotal;
        }

        $totalSales = array_sum($salesData);
        $average = count($salesData) > 0 ? $totalSales / count($salesData) : 0;
        $peakDay = 'N/A';
        if (!empty($salesData)) {
            $maxVal = max($salesData);
            $peakIndex = array_search($maxVal, $salesData, true);
            if ($peakIndex !== false && isset($labels[$peakIndex])) {
                $peakDay = $labels[$peakIndex];
            }
        }

        return [
            'labels' => $labels,
            'data' => $salesData,
            'totalSales' => number_format($totalSales, 2),
            'average' => number_format($average, 2),
            'peakDay' => $peakDay
        ];
    }

    private function getOrderStatus($startDate, $endDate)
    {
        // Get POS status data
        $posStatuses = Pos::whereBetween('sale_date', [$startDate, $endDate])
                               ->selectRaw('status, COUNT(*) as count')
                               ->groupBy('status')
                               ->get();

        // Get Online Order status data
        $orderQuery = Order::query();
        $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
        $orderStatuses = $orderQuery->selectRaw('status, COUNT(*) as count')
                                   ->groupBy('status')
                                   ->get();

        // Combine status counts
        $pending = ($posStatuses->where('status', 'pending')->first()->count ?? 0) + 
                   ($orderStatuses->where('status', 'pending')->first()->count ?? 0);
        $delivered = ($posStatuses->where('status', 'delivered')->first()->count ?? 0) + 
                     ($orderStatuses->where('status', 'delivered')->first()->count ?? 0);
        $shipping = ($posStatuses->where('status', 'shipping')->first()->count ?? 0) + 
                    ($orderStatuses->where('status', 'shipping')->first()->count ?? 0);
        $cancelled = ($posStatuses->where('status', 'cancelled')->first()->count ?? 0) + 
                     ($orderStatuses->where('status', 'cancelled')->first()->count ?? 0);

        // Add online order specific statuses
        $approved = $orderStatuses->where('status', 'approved')->first()->count ?? 0;
        $processing = $orderStatuses->where('status', 'processing')->first()->count ?? 0;

        return [
            'pending' => $pending,
            'delivered' => $delivered,
            'shipping' => $shipping,
            'cancelled' => $cancelled,
            'approved' => $approved,
            'processing' => $processing,
            'total' => $pending + $delivered + $shipping + $cancelled + $approved + $processing
        ];
    }

    private function getTopSellingItems($startDate, $endDate)
    {
        try {
            // Get POS sales aggregates per product
            $posSales = DB::table('pos_items')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('product_id', DB::raw('SUM(quantity) as pos_qty, SUM(total_price) as pos_rev'))
                ->groupBy('product_id');

            // Get Online sales aggregates per product
            $orderSales = DB::table('order_items')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('product_id', DB::raw('SUM(quantity) as order_qty, SUM(total_price) as order_rev'))
                ->groupBy('product_id');

            // Combine using Products table as base
            $topItems = DB::table('products')
                ->leftJoinSub($posSales, 'pos_summary', 'products.id', '=', 'pos_summary.product_id')
                ->leftJoinSub($orderSales, 'order_summary', 'products.id', '=', 'order_summary.product_id')
                ->leftJoinSub(DB::table('product_service_categories'), 'cats', 'products.category_id', '=', 'cats.id')
                ->selectRaw('products.name, 
                    cats.name as category_name,
                    (COALESCE(pos_summary.pos_qty, 0) + COALESCE(order_summary.order_qty, 0)) as total_sold,
                    (COALESCE(pos_summary.pos_rev, 0) + COALESCE(order_summary.order_rev, 0)) as total_revenue')
                ->where('products.type', 'product')
                ->where('products.status', 'active')
                ->where(function($q) {
                    $q->whereNotNull('pos_summary.product_id')->orWhereNotNull('order_summary.product_id');
                })
                ->orderByDesc('total_sold')
                ->take(5)
                ->get();

            $totalSoldAll = $topItems->sum('total_sold');
            $colors = ['primary', 'success', 'warning', 'info', 'danger'];
            $icons = ['fas fa-box', 'fas fa-shopping-cart', 'fas fa-star', 'fas fa-trophy', 'fas fa-fire'];

            return $topItems->map(function ($item, $index) use ($totalSoldAll, $colors, $icons) {
                return [
                    'name' => $item->name,
                    'category' => $item->category_name ?? 'Uncategorized',
                    'sales' => (float)$item->total_sold,
                    'revenue' => number_format($item->total_revenue, 2),
                    'percentage' => $totalSoldAll > 0 ? round(($item->total_sold / $totalSoldAll) * 100, 1) : 0,
                    'icon' => $icons[$index % 5],
                    'color' => $colors[$index % 5]
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            \Log::error('Error getting top selling items: ' . $e->getMessage());
            return [];
        }
    }

    private function getLocationPerformance($startDate, $endDate)
    {
        $query = Pos::query();

        $locations = $query->join('branches', 'pos.branch_id', '=', 'branches.id')
                          ->selectRaw('branches.name, SUM(pos.total_amount - COALESCE(pos.delivery, 0)) as total_sales')
                          ->whereBetween('pos.sale_date', [$startDate, $endDate])
                          ->groupBy('branches.id', 'branches.name')
                          ->orderBy('total_sales', 'desc')
                          ->get();

        return [
            'labels' => $locations->pluck('name')->toArray(),
            'data' => $locations->pluck('total_sales')->toArray()
        ];
    }

    private function getCurrentInvoices()
    {
        $query = Invoice::query();

        return $query->with(['pos.customer'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($invoice) {
                        return [
                            'id' => $invoice->invoice_number,
                            'customer' => $invoice->pos->customer->name ?? 'Walk-in Customer',
                            'amount' => number_format($invoice->total_amount, 2),
                            'status' => $invoice->status
                        ];
                    });
    }

    private function getOrderVsSaleComparison($range)
    {
        $startDate = $this->getStartDate($range);
        $endDate = Carbon::now();

        // This would typically compare orders vs actual sales
        // For now, returning mock data
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $orders = [65, 78, 82, 75, 89, 95];
        $sales = [58, 72, 76, 68, 82, 88];

        return [
            'labels' => $labels,
            'orders' => $orders,
            'sales' => $sales
        ];
    }

    private function getCustomerSatisfaction($startDate, $endDate)
    {
        // Get reviews from the current period
        $currentReviews = Review::where('is_approved', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Get reviews from the previous period for comparison
        $previousStartDate = $startDate->copy()->subWeek();
        $previousEndDate = $startDate->copy()->subDay();
        $previousReviews = Review::where('is_approved', true)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->get();

        // Calculate current period satisfaction
        $currentRating = 0;
        $currentCount = $currentReviews->count();
        if ($currentCount > 0) {
            $currentRating = $currentReviews->avg('rating');
        }

        // Calculate previous period satisfaction
        $previousRating = 0;
        $previousCount = $previousReviews->count();
        if ($previousCount > 0) {
            $previousRating = $previousReviews->avg('rating');
        }

        // If no reviews in current period, use overall average
        if ($currentCount == 0) {
            $overallReviews = Review::where('is_approved', true)->get();
            $currentRating = $overallReviews->count() > 0 ? $overallReviews->avg('rating') : 0;
            $currentCount = $overallReviews->count();
        }

        // Calculate percentage change
        $percentage = 0;
        if ($previousRating > 0) {
            $percentage = (($currentRating - $previousRating) / $previousRating) * 100;
        } elseif ($currentRating > 0) {
            $percentage = 100; // New data, consider it as improvement
        }

        return [
            'rating' => round($currentRating, 1),
            'percentage' => round($percentage, 1),
            'count' => $currentCount,
            'trend' => $percentage >= 0 ? 'up' : 'down'
        ];
    }

    private function getLowStockItems()
    {
        // Optimized: Calculate total stock across all sources in SQL
        // Filters directly in the database to only return the top 5 critical items
        return \App\Models\Product::where('manage_stock', true)
            ->where('status', 'active')
            ->with('category')
            ->withSum('variationStocks as total_stock', 'quantity')
            ->having('total_stock', '<', 10)
            ->orderBy('total_stock', 'asc')
            ->take(5)
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'Uncategorized',
                    'stock' => (int)($product->total_stock ?? 0),
                    'sku' => $product->sku
                ];
            });
    }

    private function getProfitMetrics($startDate, $endDate, $range)
    {
        // Get COD percentage from settings
        $generalSetting = \App\Models\GeneralSetting::first();
        $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;

        $periods = [
            'current' => [$startDate, $endDate],
            'previous' => [$this->getPreviousPeriodStart($startDate, $range), $startDate->copy()->subDay()]
        ];

        $metrics = [];
        foreach ($periods as $key => $period) {
            // POS Revenue & Cost
            $pos = DB::table('pos_items')
                ->join('products', 'pos_items.product_id', '=', 'products.id')
                ->whereBetween('pos_items.created_at', [$period[0], $period[1]])
                ->selectRaw('SUM(pos_items.total_price) as revenue, SUM(pos_items.quantity * products.cost) as cost')
                ->first();

            // Online Revenue & Cost (excluding delivery)
            $orders = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('order_items.created_at', [$period[0], $period[1]])
                ->selectRaw('SUM(order_items.total_price) as revenue, SUM(order_items.quantity * products.cost) as cost')
                ->first();

            // COD Discount Aggregate
            $codDiscount = DB::table('orders')
                ->whereBetween('created_at', [$period[0], $period[1]])
                ->where('payment_method', 'cash')
                ->selectRaw("SUM(ROUND(total * $codPercentage, 2)) as discount")
                ->value('discount') ?? 0;

            $rev = ($pos->revenue ?? 0) + ($orders->revenue ?? 0) - $codDiscount;
            $cost = ($pos->cost ?? 0) + ($orders->cost ?? 0);
            $metrics[$key] = ['revenue' => $rev, 'cost' => $cost, 'profit' => $rev - $cost];
        }

        $currentProfit = $metrics['current']['profit'];
        $previousProfit = $metrics['previous']['profit'];
        $currentMargin = $metrics['current']['revenue'] > 0 ? ($currentProfit / $metrics['current']['revenue']) * 100 : 0;
        
        $profitPercentage = $previousProfit > 0 ? (($currentProfit - $previousProfit) / $previousProfit) * 100 : 0;

        return [
            'profit' => number_format($currentProfit, 2),
            'margin' => round($currentMargin, 1),
            'percentage' => round($profitPercentage, 1),
            'trend' => $profitPercentage >= 0 ? 'up' : 'down'
        ];
    }

    private function getChannelBreakdown($startDate, $endDate)
    {
        // Get COD percentage
        $generalSetting = \App\Models\GeneralSetting::first();
        $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;

        // POS Sales
        $posData = DB::table('pos')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount - COALESCE(delivery, 0)) as total_sales')
            ->first();
        
        $posRevenue = $posData->total_sales ?? 0;
        $posOrders = $posData->total_orders ?? 0;

        // Online Sales - Using database aggregate for revenue with COD logic
        $onlineData = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("COUNT(*) as total_orders, 
                SUM(
                    (total - COALESCE(delivery, 0)) - 
                    CASE 
                        WHEN payment_method = 'cash' THEN ROUND(total * $codPercentage, 2)
                        ELSE 0 
                    END
                ) as total_sales")
            ->first();

        $onlineRevenue = $onlineData->total_sales ?? 0;
        $onlineOrdersCount = $onlineData->total_orders ?? 0;

        // Pending orders (need attention)
        $pendingOrders = DB::table('orders')
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        $totalRevenue = $posRevenue + $onlineRevenue;

        return [
            'pos' => [
                'revenue' => number_format($posRevenue, 2),
                'orders' => $posOrders,
                'percentage' => $totalRevenue > 0 ? round(($posRevenue / $totalRevenue) * 100, 1) : 0
            ],
            'online' => [
                'revenue' => number_format($onlineRevenue, 2),
                'orders' => $onlineOrdersCount,
                'percentage' => $totalRevenue > 0 ? round(($onlineRevenue / $totalRevenue) * 100, 1) : 0
            ],
            'pending' => $pendingOrders
        ];
    }
}
