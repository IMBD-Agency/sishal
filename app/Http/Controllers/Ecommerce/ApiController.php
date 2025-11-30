<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Wishlist;
use App\Services\SalesAnalyticsService;
use App\Http\Resources\ProductResource;

class ApiController extends Controller
{
    protected $salesAnalytics;

    public function __construct(SalesAnalyticsService $salesAnalytics)
    {
        $this->salesAnalytics = $salesAnalytics;
    }

    public function mostSoldProducts(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Set execution time limit to prevent timeouts
            set_time_limit(30);
            
            $userId = Auth::id();
            $limit = $request->get('limit', 20);
            $days = $request->get('days', 30);
            $useCache = $request->get('cache', true);
            
            // Get top selling products with proper sales-based sorting
            if ($useCache) {
                $products = $this->salesAnalytics->getTopSellingProductsCached($limit, $days, 5);
            } else {
                $products = $this->salesAnalytics->getTopSellingProducts($limit, $days);
            }
            
            // Check if products collection is empty or null
            if (!$products || $products->isEmpty()) {
                Log::warning('No top selling products found', [
                    'limit' => $limit,
                    'days' => $days,
                    'cache_used' => $useCache
                ]);
            }

            // Optimize wishlist check with single query
            $wishlistedIds = [];
            if ($userId) {
                $wishlistedIds = Wishlist::where('user_id', $userId)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->pluck('product_id')
                    ->toArray();
            }

            // Transform products with optimized data loading
            $products->transform(function ($product) use ($wishlistedIds) {
                $product->is_wishlisted = in_array($product->id, $wishlistedIds);
                $product->has_stock = $product->hasStock();
                $product->avg_rating = $product->averageRating();
                $product->total_reviews = $product->totalReviews();
                return $product;
            });

            $executionTime = microtime(true) - $startTime;
            
            // Log performance metrics
            Log::info('Top selling products loaded', [
                'execution_time' => round($executionTime, 3),
                'user_id' => $userId,
                'products_count' => $products->count(),
                'cache_used' => $useCache,
                'days_period' => $days
            ]);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'meta' => [
                    'execution_time' => round($executionTime, 3),
                    'total_products' => $products->count(),
                    'period_days' => $days,
                    'cached' => $useCache
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading top selling products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load top selling products',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function newArrivalsProducts(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Set execution time limit to prevent timeouts
            set_time_limit(30);
            
            $userId = Auth::id();
            $limit = $request->get('limit', 20);
            $useCache = $request->get('cache', true);
            
            $cacheKey = "new_arrivals_products_{$limit}";
            $fullResponseCacheKey = "new_arrivals_full_response_{$limit}";
            
            // Try to get cached full response first (without user-specific wishlist data)
            if ($useCache && !$userId) {
                $cachedResponse = Cache::get($fullResponseCacheKey);
                if ($cachedResponse) {
                    return response()->json($cachedResponse);
                }
            }
            
            // Get new arrivals with caching
            if ($useCache) {
                $products = Cache::remember($cacheKey, 300, function () use ($limit) {
                    return \App\Models\Product::with('category')
                        ->where('type', 'product')
                        ->where('status', 'active')
                        ->orderByDesc('created_at')
                        ->take($limit)
                        ->get();
                });
            } else {
                $products = \App\Models\Product::with('category')
                    ->where('type', 'product')
                    ->where('status', 'active')
                    ->orderByDesc('created_at')
                    ->take($limit)
                    ->get();
            }
            
            // Check if products collection is empty or null
            if (!$products || $products->isEmpty()) {
                Log::warning('No new arrival products found', [
                    'limit' => $limit,
                    'cache_used' => $useCache
                ]);
            }

            // Optimize wishlist check with single query
            $wishlistedIds = [];
            if ($userId) {
                $wishlistedIds = Wishlist::where('user_id', $userId)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->pluck('product_id')
                    ->toArray();
            }

            // Transform products with optimized data loading
            $products->transform(function ($product) use ($wishlistedIds) {
                $product->is_wishlisted = in_array($product->id, $wishlistedIds);
                $product->has_stock = $product->hasStock();
                $product->avg_rating = $product->averageRating();
                $product->total_reviews = $product->totalReviews();
                return $product;
            });

            $executionTime = microtime(true) - $startTime;
            
            // Prepare response data
            $responseData = [
                'success' => true,
                'data' => ProductResource::collection($products),
                'meta' => [
                    'execution_time' => round($executionTime, 3),
                    'total_products' => $products->count(),
                    'cached' => $useCache
                ]
            ];
            
            // Cache full response for non-logged-in users (5 minutes)
            if ($useCache && !$userId) {
                Cache::put($fullResponseCacheKey, $responseData, 300);
            }
            
            // Log performance metrics
            Log::info('New arrivals products loaded', [
                'execution_time' => round($executionTime, 3),
                'user_id' => $userId,
                'products_count' => $products->count(),
                'cache_used' => $useCache
            ]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Error loading new arrivals products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load new arrivals products',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function bestDealsProducts(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $userId = Auth::id();
            $limit = $request->get('limit', 20);
            $useCache = $request->get('cache', true);
            
            $cacheKey = "best_deals_products_{$limit}";
            
            // Get best deals products (products with discount > 0)
            if ($useCache) {
                $products = Cache::remember($cacheKey, 300, function () use ($limit) {
                    return \App\Models\Product::with('category')
                        ->where('type', 'product')
                        ->where('status', 'active')
                        ->where('discount', '>', 0)
                        ->orderByDesc('discount')
                        ->orderByDesc('created_at')
                        ->take($limit)
                        ->get();
                });
            } else {
                $products = \App\Models\Product::with('category')
                    ->where('type', 'product')
                    ->where('status', 'active')
                    ->where('discount', '>', 0)
                    ->orderByDesc('discount')
                    ->orderByDesc('created_at')
                    ->take($limit)
                    ->get();
            }

            // Optimize wishlist check with single query
            $wishlistedIds = [];
            if ($userId) {
                $wishlistedIds = Wishlist::where('user_id', $userId)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->pluck('product_id')
                    ->toArray();
            }

            // Transform products with optimized data loading
            $products->transform(function ($product) use ($wishlistedIds) {
                $product->is_wishlisted = in_array($product->id, $wishlistedIds);
                $product->has_stock = $product->hasStock();
                $product->avg_rating = $product->averageRating();
                $product->total_reviews = $product->totalReviews();
                return $product;
            });

            $executionTime = microtime(true) - $startTime;
            
            // Log performance metrics
            Log::info('Best deals products loaded', [
                'execution_time' => round($executionTime, 3),
                'user_id' => $userId,
                'products_count' => $products->count(),
                'cache_used' => $useCache
            ]);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'meta' => [
                    'execution_time' => round($executionTime, 3),
                    'total_products' => $products->count(),
                    'cached' => $useCache
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading best deals products', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load best deals products',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
