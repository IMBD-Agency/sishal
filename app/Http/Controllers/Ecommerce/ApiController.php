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
            set_time_limit(60);
            ini_set('max_execution_time', 60);
            
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
                // Return empty array instead of error
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'execution_time' => round(microtime(true) - $startTime, 3),
                        'total_products' => 0,
                        'period_days' => $days,
                        'cached' => $useCache
                    ]
                ]);
            }

            // Optimize wishlist check with single query (user-specific, not cached)
            $wishlistedIds = [];
            if ($userId) {
                $wishlistedIds = Wishlist::where('user_id', $userId)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->pluck('product_id')
                    ->toArray();
            }

            // Add wishlist status (ratings/reviews/stock already pre-calculated in cache)
            // If products are from cache, they already have these values
            // If not cached, we need to calculate them
            $products->transform(function ($product) use ($wishlistedIds) {
                $product->is_wishlisted = in_array($product->id, $wishlistedIds);
                
                // Only calculate if not already set (from cache)
                if (!isset($product->avg_rating)) {
                    $product->avg_rating = $product->averageRating();
                }
                if (!isset($product->total_reviews)) {
                    $product->total_reviews = $product->totalReviews();
                }
                if (!isset($product->has_stock)) {
                    $product->has_stock = $product->hasStock();
                }
                
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
                'trace' => substr($e->getTraceAsString(), 0, 500), // Limit trace length
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return empty data instead of error to prevent frontend issues
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'execution_time' => round(microtime(true) - $startTime, 3),
                    'total_products' => 0,
                    'error' => config('app.debug') ? $e->getMessage() : 'Service temporarily unavailable'
                ]
            ], 200); // Return 200 with empty data instead of 500
        }
    }

    public function newArrivalsProducts(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Set execution time limit to prevent timeouts
            set_time_limit(60);
            ini_set('max_execution_time', 60);
            
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
            
            // Get new arrivals with caching (eager load relationships to avoid N+1 queries)
            if ($useCache) {
                $products = Cache::remember($cacheKey, 300, function () use ($limit) {
                    return \App\Models\Product::with([
                        'category',
                        'reviews' => function($q) {
                            $q->where('is_approved', true);
                        },
                        'branchStock',
                        'warehouseStock',
                        'variations.stocks'
                    ])
                        ->where('type', 'product')
                        ->where('status', 'active')
                        ->orderByDesc('created_at')
                        ->take($limit)
                        ->get()
                        ->map(function ($product) {
                            // Pre-calculate ratings, reviews, and stock status
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
                            
                            return $product;
                        });
                });
            } else {
                $products = \App\Models\Product::with([
                    'category',
                    'reviews' => function($q) {
                        $q->where('is_approved', true);
                    },
                    'branchStock',
                    'warehouseStock',
                    'variations.stocks'
                ])
                    ->where('type', 'product')
                    ->where('status', 'active')
                    ->orderByDesc('created_at')
                    ->take($limit)
                    ->get()
                    ->map(function ($product) {
                        // Pre-calculate ratings, reviews, and stock status
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
                        
                        return $product;
                    });
            }
            
            // Check if products collection is empty or null
            if (!$products || $products->isEmpty()) {
                Log::warning('No new arrival products found', [
                    'limit' => $limit,
                    'cache_used' => $useCache
                ]);
                // Return empty array instead of error
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'execution_time' => round(microtime(true) - $startTime, 3),
                        'total_products' => 0,
                        'cached' => $useCache
                    ]
                ]);
            }

            // Optimize wishlist check with single query (user-specific, not cached)
            $wishlistedIds = [];
            if ($userId) {
                $wishlistedIds = Wishlist::where('user_id', $userId)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->pluck('product_id')
                    ->toArray();
            }

            // Add wishlist status (only user-specific data, already has ratings/reviews/stock)
            $products->transform(function ($product) use ($wishlistedIds) {
                $product->is_wishlisted = in_array($product->id, $wishlistedIds);
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
                'trace' => substr($e->getTraceAsString(), 0, 500), // Limit trace length
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return empty data instead of error to prevent frontend issues
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'execution_time' => round(microtime(true) - $startTime, 3),
                    'total_products' => 0,
                    'error' => config('app.debug') ? $e->getMessage() : 'Service temporarily unavailable'
                ]
            ], 200); // Return 200 with empty data instead of 500
        }
    }

    public function bestDealsProducts(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Set execution time limit to prevent timeouts
            set_time_limit(60);
            ini_set('max_execution_time', 60);
            
            $userId = Auth::id();
            $limit = $request->get('limit', 20);
            $useCache = $request->get('cache', true);
            
            $cacheKey = "best_deals_products_{$limit}";
            
            // Get best deals products (products with discount > 0) - eager load relationships
            if ($useCache) {
                $products = Cache::remember($cacheKey, 300, function () use ($limit) {
                    return \App\Models\Product::with([
                        'category',
                        'reviews' => function($q) {
                            $q->where('is_approved', true);
                        },
                        'branchStock',
                        'warehouseStock',
                        'variations.stocks'
                    ])
                        ->where('type', 'product')
                        ->where('status', 'active')
                        ->where('discount', '>', 0)
                        ->orderByDesc('discount')
                        ->orderByDesc('created_at')
                        ->take($limit)
                        ->get()
                        ->map(function ($product) {
                            // Pre-calculate ratings, reviews, and stock status
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
                            
                            return $product;
                        });
                });
            } else {
                $products = \App\Models\Product::with([
                    'category',
                    'reviews' => function($q) {
                        $q->where('is_approved', true);
                    },
                    'branchStock',
                    'warehouseStock',
                    'variations.stocks'
                ])
                    ->where('type', 'product')
                    ->where('status', 'active')
                    ->where('discount', '>', 0)
                    ->orderByDesc('discount')
                    ->orderByDesc('created_at')
                    ->take($limit)
                    ->get()
                    ->map(function ($product) {
                        // Pre-calculate ratings, reviews, and stock status
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
                        
                        return $product;
                    });
            }

            // Check if products collection is empty or null
            if (!$products || $products->isEmpty()) {
                Log::warning('No best deals products found', [
                    'limit' => $limit,
                    'cache_used' => $useCache
                ]);
                // Return empty array instead of error
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => [
                        'execution_time' => round(microtime(true) - $startTime, 3),
                        'total_products' => 0,
                        'cached' => $useCache
                    ]
                ]);
            }

            // Optimize wishlist check with single query (user-specific, not cached)
            $wishlistedIds = [];
            if ($userId) {
                try {
                    $wishlistedIds = Wishlist::where('user_id', $userId)
                        ->whereIn('product_id', $products->pluck('id'))
                        ->pluck('product_id')
                        ->toArray();
                } catch (\Exception $e) {
                    Log::warning('Error loading wishlist', ['error' => $e->getMessage()]);
                    $wishlistedIds = [];
                }
            }

            // Add wishlist status (ratings/reviews/stock already pre-calculated in cache)
            // If products are from cache, they already have these values
            // If not cached, we need to calculate them
            $products->transform(function ($product) use ($wishlistedIds) {
                $product->is_wishlisted = in_array($product->id, $wishlistedIds);
                
                // Only calculate if not already set (from cache)
                if (!isset($product->avg_rating)) {
                    try {
                        $product->avg_rating = $product->averageRating();
                    } catch (\Exception $e) {
                        $product->avg_rating = 0;
                    }
                }
                if (!isset($product->total_reviews)) {
                    try {
                        $product->total_reviews = $product->totalReviews();
                    } catch (\Exception $e) {
                        $product->total_reviews = 0;
                    }
                }
                if (!isset($product->has_stock)) {
                    try {
                        $product->has_stock = $product->hasStock();
                    } catch (\Exception $e) {
                        $product->has_stock = false;
                    }
                }
                
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
                'trace' => substr($e->getTraceAsString(), 0, 500), // Limit trace length
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return empty data instead of error to prevent frontend issues
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'execution_time' => round(microtime(true) - $startTime, 3),
                    'total_products' => 0,
                    'error' => config('app.debug') ? $e->getMessage() : 'Service temporarily unavailable'
                ]
            ], 200); // Return 200 with empty data instead of 500
        }
    }
}
