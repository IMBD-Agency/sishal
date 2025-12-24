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
        try {
            $startDate = now()->subDays($days);
            
            // Use a more efficient query with subqueries to avoid complex joins
            $products = Product::select([
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
            ->selectRaw('
                COALESCE((
                    SELECT SUM(quantity) 
                    FROM pos_items 
                    WHERE pos_items.product_id = products.id 
                    AND pos_items.created_at >= ?
                ), 0) + COALESCE((
                    SELECT SUM(quantity) 
                    FROM order_items 
                    WHERE order_items.product_id = products.id 
                    AND order_items.created_at >= ?
                ), 0) as total_sold,
                COALESCE((
                    SELECT SUM(total_price) 
                    FROM pos_items 
                    WHERE pos_items.product_id = products.id 
                    AND pos_items.created_at >= ?
                ), 0) + COALESCE((
                    SELECT SUM(total_price) 
                    FROM order_items 
                    WHERE order_items.product_id = products.id 
                    AND order_items.created_at >= ?
                ), 0) as total_revenue
            ', [$startDate, $startDate, $startDate, $startDate])
            ->where('products.type', 'product')
            ->where('products.status', 'active')
            ->where('products.show_in_ecommerce', true)
            ->orderByDesc('total_sold')
            ->orderByDesc('total_revenue')
            ->orderByDesc('products.created_at')
            ->take($limit)
            ->get();
            
            // Load relationships efficiently
            if ($products->isNotEmpty()) {
                $products->load([
                    'category',
                    'reviews' => function($q) {
                        $q->where('is_approved', true)->select(['id', 'product_id', 'rating']);
                    },
                    'branchStock' => function($q) {
                        $q->select(['id', 'product_id', 'quantity']);
                    },
                    'warehouseStock' => function($q) {
                        $q->select(['id', 'product_id', 'quantity']);
                    },
                    'variations' => function($q) {
                        $q->where('status', 'active')
                          ->with(['stocks' => function($sq) {
                              $sq->select(['id', 'variation_id', 'quantity']);
                          }]);
                    }
                ]);
            }
            
            return $products->map(function ($product) {
                // Pre-calculate ratings, reviews, and stock status
                try {
                    $product->avg_rating = $product->reviews->avg('rating') ?? 0;
                    $product->total_reviews = $product->reviews->count();
                    
                    // Pre-calculate stock status
                    if ($product->has_variations) {
                        $product->has_stock = $product->variations->where('status', 'active')
                            ->flatMap->stocks
                            ->sum('quantity') > 0;
                    } else {
                        $branchStock = $product->branchStock->sum('quantity') ?? 0;
                        $warehouseStock = $product->warehouseStock->sum('quantity') ?? 0;
                        $product->has_stock = ($branchStock + $warehouseStock) > 0;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error calculating product stats', [
                        'product_id' => $product->id,
                        'error' => $e->getMessage()
                    ]);
                    $product->avg_rating = 0;
                    $product->total_reviews = 0;
                    $product->has_stock = false;
                }
                
                return $product;
            });
        } catch (\Exception $e) {
            \Log::error('Error in getTopSellingProducts', [
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);
            // Return empty collection instead of throwing
            return collect([]);
        }
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
