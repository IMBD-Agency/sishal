<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PosItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SalesAnalyticsService
{
    /**
     * Get top selling products based on actual sales data
     *
     * @param int $limit
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopSellingProducts($limit = 20, $days = 30)
    {
        $startDate = now()->subDays($days);
        
        return Product::select([
            'products.id',
            'products.name', 
            'products.slug',
            'products.sku',
            'products.price',
            'products.discount',
            'products.image',
            'products.short_desc',
            'products.description',
            'products.status',
            'products.has_variations',
            'products.manage_stock',
            'products.created_at',
            'products.updated_at',
            'products.category_id',
            'products.type',
            'products.cost',
            'products.meta_title',
            'products.meta_description',
            'products.meta_keywords'
        ])
        ->leftJoin('pos_items', function($join) use ($startDate) {
            $join->on('products.id', '=', 'pos_items.product_id')
                 ->where('pos_items.created_at', '>=', $startDate);
        })
        ->leftJoin('order_items', function($join) use ($startDate) {
            $join->on('products.id', '=', 'order_items.product_id')
                 ->where('order_items.created_at', '>=', $startDate);
        })
        ->selectRaw('
            COALESCE(SUM(pos_items.quantity), 0) + COALESCE(SUM(order_items.quantity), 0) as total_sold,
            COALESCE(SUM(pos_items.total_price), 0) + COALESCE(SUM(order_items.total_price), 0) as total_revenue
        ')
        ->where('products.type', 'product')
        ->where('products.status', 'active')
        ->groupBy([
            'products.id',
            'products.name', 
            'products.slug',
            'products.sku',
            'products.price',
            'products.discount',
            'products.image',
            'products.short_desc',
            'products.description',
            'products.status',
            'products.has_variations',
            'products.manage_stock',
            'products.created_at',
            'products.updated_at',
            'products.category_id',
            'products.type',
            'products.cost',
            'products.meta_title',
            'products.meta_description',
            'products.meta_keywords'
        ])
        ->orderByDesc('total_sold')
        ->orderByDesc('total_revenue')
        ->orderByDesc('products.created_at') // Tertiary sort for consistency
        ->take($limit)
        ->get();
    }

    /**
     * Get top selling products with caching
     *
     * @param int $limit
     * @param int $days
     * @param int $cacheMinutes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopSellingProductsCached($limit = 20, $days = 30, $cacheMinutes = 5)
    {
        $cacheKey = "top_selling_products_{$limit}_{$days}";
        
        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($limit, $days) {
            return $this->getTopSellingProducts($limit, $days);
        });
    }

    /**
     * Get product sales statistics
     *
     * @param int $productId
     * @param int $days
     * @return array
     */
    public function getProductSalesStats($productId, $days = 30)
    {
        $startDate = now()->subDays($days);
        
        $posSales = PosItem::where('product_id', $productId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
            ->first();
            
        $orderSales = OrderItem::where('product_id', $productId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
            ->first();
        
        return [
            'total_quantity_sold' => ($posSales->total_quantity ?? 0) + ($orderSales->total_quantity ?? 0),
            'total_revenue' => ($posSales->total_revenue ?? 0) + ($orderSales->total_revenue ?? 0),
            'period_days' => $days
        ];
    }

    /**
     * Clear top selling products cache
     */
    public function clearTopSellingCache()
    {
        $patterns = [
            'top_selling_products_*',
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
