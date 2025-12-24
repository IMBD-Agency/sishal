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
    
    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes($memoryLimit)
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) $memoryLimit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    public function mostSoldProducts(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            // Set execution time limit to prevent timeouts (increased for big data)
            // Use try-catch to prevent fatal errors if server doesn't allow changing limits
            try {
                @set_time_limit(120);
                @ini_set('max_execution_time', 120);
            } catch (\Exception $e) {
                // Server may not allow changing execution time - use default
                Log::debug('Could not set execution time limit', ['error' => $e->getMessage()]);
            }
            
            // Set memory limit for large datasets - wrapped to prevent fatal errors
            try {
                $currentMemoryLimit = ini_get('memory_limit');
                if ($currentMemoryLimit !== '-1') {
                    $memoryBytes = $this->convertToBytes($currentMemoryLimit);
                    if ($memoryBytes < 256 * 1024 * 1024) { // Less than 256MB
                        @ini_set('memory_limit', '256M');
                    }
                }
            } catch (\Exception $e) {
                // Server may not allow changing memory limit - use default
                Log::debug('Could not set memory limit', ['error' => $e->getMessage()]);
            }
            
            // Set database query timeout (30 seconds) - wrapped in try-catch to prevent connection errors
            try {
                DB::statement("SET SESSION wait_timeout = 30");
                DB::statement("SET SESSION interactive_timeout = 30");
            } catch (\Exception $e) {
                // Ignore if database doesn't allow setting session variables
                // This is not critical - database default timeout will be used
                Log::debug('Could not set database session timeout', ['error' => $e->getMessage()]);
            }
            
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
            // Set execution time limit to prevent timeouts (increased for big data)
            // Use try-catch to prevent fatal errors if server doesn't allow changing limits
            try {
                @set_time_limit(120);
                @ini_set('max_execution_time', 120);
            } catch (\Exception $e) {
                // Server may not allow changing execution time - use default
                Log::debug('Could not set execution time limit', ['error' => $e->getMessage()]);
            }
            
            // Set memory limit for large datasets - wrapped to prevent fatal errors
            try {
                $currentMemoryLimit = ini_get('memory_limit');
                if ($currentMemoryLimit !== '-1') {
                    $memoryBytes = $this->convertToBytes($currentMemoryLimit);
                    if ($memoryBytes < 256 * 1024 * 1024) { // Less than 256MB
                        @ini_set('memory_limit', '256M');
                    }
                }
            } catch (\Exception $e) {
                // Server may not allow changing memory limit - use default
                Log::debug('Could not set memory limit', ['error' => $e->getMessage()]);
            }
            
            // Set database query timeout (30 seconds) - wrapped in try-catch to prevent connection errors
            try {
                DB::statement("SET SESSION wait_timeout = 30");
                DB::statement("SET SESSION interactive_timeout = 30");
            } catch (\Exception $e) {
                // Ignore if database doesn't allow setting session variables
                // This is not critical - database default timeout will be used
                Log::debug('Could not set database session timeout', ['error' => $e->getMessage()]);
            }
            
            $userId = Auth::id();
            $limit = min($request->get('limit', 20), 50); // Cap at 50 to prevent memory issues
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
            // Optimized query with select specific columns to reduce memory usage
            if ($useCache) {
                $products = Cache::remember($cacheKey, 300, function () use ($limit) {
                    try {
                        return \App\Models\Product::select([
                            'products.id', 'products.name', 'products.slug', 'products.price', 
                            'products.discount', 'products.image', 'products.category_id',
                            'products.has_variations', 'products.status', 'products.created_at'
                        ])
                        ->with([
                            'category:id,name,slug',
                            'reviews' => function($q) {
                                $q->select('id', 'product_id', 'rating', 'is_approved')
                                  ->where('is_approved', true);
                            },
                            'branchStock:id,product_id,quantity',
                            'warehouseStock:id,product_id,quantity',
                            'variations' => function($q) {
                                $q->select('id', 'product_id', 'status')
                                  ->where('status', 'active')
                                  ->with(['stocks:id,variation_id,quantity']);
                            }
                        ])
                        ->where('type', 'product')
                        ->where('status', 'active')
                        ->where('show_in_ecommerce', true)
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
                    } catch (\Exception $e) {
                        Log::error('Error in new arrivals cache closure', [
                            'error' => $e->getMessage(),
                            'limit' => $limit
                        ]);
                        throw $e;
                    }
                });
            } else {
                $products = \App\Models\Product::select([
                    'products.id', 'products.name', 'products.slug', 'products.price', 
                    'products.discount', 'products.image', 'products.category_id',
                    'products.has_variations', 'products.status', 'products.created_at'
                ])
                ->with([
                    'category:id,name,slug',
                    'reviews' => function($q) {
                        $q->select('id', 'product_id', 'rating', 'is_approved')
                          ->where('is_approved', true);
                    },
                    'branchStock:id,product_id,quantity',
                    'warehouseStock:id,product_id,quantity',
                    'variations' => function($q) {
                        $q->select('id', 'product_id', 'status')
                          ->where('status', 'active')
                          ->with(['stocks:id,variation_id,quantity']);
                    }
                ])
                ->where('type', 'product')
                ->where('status', 'active')
                ->where('show_in_ecommerce', true)
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

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error loading new arrivals products', [
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'user_id' => Auth::id(),
                'limit' => $limit ?? 20
            ]);

            // Return empty data instead of error to prevent frontend issues
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'execution_time' => round(microtime(true) - $startTime, 3),
                    'total_products' => 0,
                    'error' => config('app.debug') ? 'Database query timeout or error' : 'Service temporarily unavailable'
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error loading new arrivals products', [
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 500), // Limit trace length
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
                'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
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
            // Set execution time limit to prevent timeouts (increased for big data)
            // Use try-catch to prevent fatal errors if server doesn't allow changing limits
            try {
                @set_time_limit(120);
                @ini_set('max_execution_time', 120);
            } catch (\Exception $e) {
                // Server may not allow changing execution time - use default
                Log::debug('Could not set execution time limit', ['error' => $e->getMessage()]);
            }
            
            // Set memory limit for large datasets - wrapped to prevent fatal errors
            try {
                $currentMemoryLimit = ini_get('memory_limit');
                if ($currentMemoryLimit !== '-1') {
                    $memoryBytes = $this->convertToBytes($currentMemoryLimit);
                    if ($memoryBytes < 256 * 1024 * 1024) { // Less than 256MB
                        @ini_set('memory_limit', '256M');
                    }
                }
            } catch (\Exception $e) {
                // Server may not allow changing memory limit - use default
                Log::debug('Could not set memory limit', ['error' => $e->getMessage()]);
            }
            
            // Set database query timeout (30 seconds) - wrapped in try-catch to prevent connection errors
            try {
                DB::statement("SET SESSION wait_timeout = 30");
                DB::statement("SET SESSION interactive_timeout = 30");
            } catch (\Exception $e) {
                // Ignore if database doesn't allow setting session variables
                // This is not critical - database default timeout will be used
                Log::debug('Could not set database session timeout', ['error' => $e->getMessage()]);
            }
            
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
                        ->where('show_in_ecommerce', true)
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
                    ->where('show_in_ecommerce', true)
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
