<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Clear cache entries matching a pattern
     * Works with both Redis and database cache drivers
     */
    private static function clearCacheByPattern($pattern)
    {
        $store = Cache::getStore();
        
        // Check if we're using Redis cache driver
        if (method_exists($store, 'getRedis')) {
            try {
                $redis = $store->getRedis();
                $prefix = config('cache.prefix', '');
                $searchPattern = $prefix . $pattern;
                
                // Use SCAN instead of KEYS for better performance in production
                $keys = [];
                $cursor = 0;
                do {
                    $result = $redis->scan($cursor, ['match' => $searchPattern, 'count' => 100]);
                    $cursor = $result[0];
                    $keys = array_merge($keys, $result[1]);
                } while ($cursor != 0);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                    \Log::info("Cleared " . count($keys) . " cache entries matching: {$pattern}");
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to clear Redis cache pattern {$pattern}: " . $e->getMessage());
            }
        } 
        // For database cache driver
        elseif (config('cache.default') === 'database') {
            try {
                $cacheTable = config('cache.stores.database.table', 'cache');
                $prefix = config('cache.prefix', '');
                
                // Convert pattern to SQL LIKE pattern
                $likePattern = str_replace('*', '%', $prefix . $pattern);
                
                // Delete matching cache entries
                $deleted = DB::table($cacheTable)
                    ->where('key', 'like', $likePattern)
                    ->delete();
                
                if ($deleted > 0) {
                    \Log::info("Cleared {$deleted} cache entries matching: {$pattern}");
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to clear database cache pattern {$pattern}: " . $e->getMessage());
            }
        }
        // For file cache driver - we can't easily pattern match, so log a warning
        else {
            \Log::warning("Pattern-based cache clearing not fully supported for " . config('cache.default') . " driver. Pattern: {$pattern}");
        }
    }
    
    /**
     * Clear all product-related caches
     */
    public static function clearProductCaches($productId = null)
    {
        // Clear max product price cache (used in multiple places)
        Cache::forget('max_product_price');
        
        // Clear product list caches using pattern matching
        $patterns = [
            'products_list_*',
            'product_details_*',
            'new_arrivals_products_*',
            'new_arrivals_full_response_*',
            'best_deals_products_*',
            'best_deals_page_*', // Best Deals page cache
            'top_selling_products_*',
        ];
        
        foreach ($patterns as $pattern) {
            self::clearCacheByPattern($pattern);
        }
        
        // Clear specific named caches
        Cache::forget('best_deal_products_home');
        Cache::forget('featured_services');
        
        // Clear specific product cache
        if ($productId) {
            $product = \App\Models\Product::find($productId);
            if ($product) {
                Cache::forget('product_details_' . $product->slug);
                Cache::forget("related_products_{$product->id}_{$product->category_id}");
                
                // Also clear related products caches for products in the same category
                // since they might include this product in their related list
                $sameCategoryProductIds = \App\Models\Product::where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->pluck('id');
                
                foreach ($sameCategoryProductIds as $prodId) {
                    Cache::forget("related_products_{$prodId}_{$product->category_id}");
                }
            }
        } else {
            // Clear all related products caches when any product changes globally
            self::clearCacheByPattern('related_products_*');
        }
        
        // Clear common API caches (clear all limit variations)
        for ($limit = 10; $limit <= 50; $limit += 10) {
            Cache::forget("new_arrivals_products_{$limit}");
            Cache::forget("new_arrivals_full_response_{$limit}");
            Cache::forget("best_deals_products_{$limit}");
            Cache::forget("top_selling_products_{$limit}_30");
        }
        
        // Clear home page
        Cache::forget('home_page_data');
    }
    
    /**
     * Clear category-related caches
     */
    public static function clearCategoryCaches()
    {
        Cache::forget('active_categories_with_children');
        Cache::forget('featured_categories');
        Cache::forget('all_active_categories');
        
        // Also clear home page as it uses categories
        Cache::forget('home_page_data');
        
        // Clear product list caches that depend on categories
        // Products list may be filtered by category, so clear those too
        self::clearCacheByPattern('products_list_*');
    }
    
    /**
     * Clear banner caches
     */
    public static function clearBannerCaches()
    {
        Cache::forget('banners_hero');
        Cache::forget('banners_vlogs_bottom');
        Cache::forget('home_page_data');
    }
    
    /**
     * Clear vlog caches
     */
    public static function clearVlogCaches()
    {
        Cache::forget('active_vlogs_latest_4');
        Cache::forget('home_page_data');
    }
    
    /**
     * Clear all home page related caches
     */
    public static function clearHomePageCaches()
    {
        Cache::forget('home_page_data');
        Cache::forget('best_deal_products_home');
        Cache::forget('featured_services');
        Cache::forget('active_vlogs_latest_4');
        Cache::forget('banners_hero');
        Cache::forget('banners_vlogs_bottom');
        Cache::forget('active_categories_with_children');
        Cache::forget('featured_categories');
    }
    
    /**
     * Clear all caches (use with caution)
     */
    public static function clearAllCaches()
    {
        Cache::flush();
    }
}

