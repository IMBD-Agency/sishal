<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear all product-related caches
     */
    public static function clearProductCaches($productId = null)
    {
        // Clear max product price cache (used in multiple places)
        Cache::forget('max_product_price');
        
        // Clear product list caches (pattern matching)
        $patterns = [
            'products_list_*',
            'product_details_*',
            'new_arrivals_products_*',
            'new_arrivals_full_response_*',
            'best_deals_products_*',
            'best_deal_products_home',
            'best_deals_page_*', // Best Deals page cache
            'top_selling_products_*',
            'featured_services',
        ];
        
        foreach ($patterns as $pattern) {
            // Note: Cache::forget doesn't support wildcards, so we'll clear specific keys
            // For wildcard support, you'd need Redis with tags or manual key tracking
        }
        
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
            // Clear all related products caches (when any product changes globally)
            // Note: In production with Redis, use cache tags for better invalidation
            // For now, we'll clear them when products are updated globally
        }
        
        // Clear common API caches (clear all limit variations)
        for ($limit = 10; $limit <= 50; $limit += 10) {
            Cache::forget("new_arrivals_products_{$limit}");
            Cache::forget("new_arrivals_full_response_{$limit}");
            Cache::forget("best_deals_products_{$limit}");
            Cache::forget("top_selling_products_{$limit}_30");
        }
        Cache::forget('best_deal_products_home');
        Cache::forget('featured_services');
        
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
        // Note: In production, you might want to use cache tags (Redis) for this
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

