<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    public function addToCartByCard($productId)
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();

        try {
            // Validate product exists
            $product = \App\Models\Product::find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // CRITICAL: Products with variations cannot be added from product cards
            // User must go to product details page to select a variation first
            if ($product->has_variations) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product has variations (Color/Size). Please go to the product page to select your preferred options before adding to cart.',
                    'error_type' => 'variation_required',
                    'product_id' => $productId,
                    'product_slug' => $product->slug ?? null
                ], 422);
            }

            // Find existing cart item for this user or guest session and product
            $existingCart = Cart::where('product_id', $productId)
                ->when($userId, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }, function ($q) use ($sessionId) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                })
                ->whereNull('variation_id') // Only match items without variations
                ->first();

            if ($existingCart) {
                $existingCart->qty += 1;
                $existingCart->save();
            } else {
                $cartData = [
                    'product_id' => $productId,
                    'qty' => 1,
                ];
                if ($userId) {
                    $cartData['user_id'] = $userId;
                } else {
                    $cartData['session_id'] = $sessionId;
                }
                $existingCart = Cart::create($cartData);
            }

            // Load product with category for GTM tracking
            $product = $existingCart->product;
            $productData = null;
            if ($product) {
                $product->load('category');
                $productData = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? '',
                    'price' => $product->discount ?? $product->price,
                ];
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully!',
                'cart' => $existingCart,
                'product' => $productData,
                'qty' => $existingCart->qty
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding product to cart', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart'
            ], 500);
        }
    }

    public function addToCartByPage($productId, Request $request)
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();

        // Validate product ID
        if (!$productId || $productId === 'null' || $productId === '') {
            Log::error('Invalid product ID received', ['product_id' => $productId]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID'
            ], 400);
        }

        // Debug logging
        Log::info('Cart add request', [
            'product_id' => $productId,
            'qty' => $request->input('qty'),
            'variation_id' => $request->input('variation_id'),
            'attribute_value_ids' => $request->input('attribute_value_ids'),
            'user_id' => $userId,
            'session_id' => $sessionId,
            'auth_check' => auth()->check(),
            'all_request_data' => $request->all()
        ]);
        

        $qty = (int) $request->input('qty', 1);
        $variationId = $request->input('variation_id');
        $attributeValueIds = $request->input('attribute_value_ids', []);
        
        if ($qty < 1)
            $qty = 1;

        // Always load product with variations to validate variation requirements
        $product = \App\Models\Product::with('variations.combinations')->findOrFail($productId);

        // Resolve variation either by id or by attribute value ids
        $variation = null;
        if ($variationId) {
            $variation = \App\Models\ProductVariation::find($variationId);
        } elseif (is_array($attributeValueIds) && count($attributeValueIds) > 0) {
            $variation = $product->getVariationByAttributeValueIds($attributeValueIds);
            if ($variation) {
                $variationId = $variation->id;
            }
        }

        // Validate resolved variation
        if ($variation) {
            if ($variation->product_id != $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variation selected'
                ], 400);
            }
            if (!$variation->isInStock()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected variation is out of stock'
                ], 400);
            }
        } else {
            // If the product has variations, a valid variation must be selected
            if ($product->has_variations) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a valid variation'
                ], 400);
            }
        }

        // Find existing cart item for this user/session, product, and variation
        // CRITICAL: For products with variations, we need to handle old cart items without variation_id
        $existingCart = null;
        
        if ($variationId) {
            // First, try to find cart item with the exact variation_id
            $existingCart = Cart::where('product_id', $productId)
                ->where('variation_id', $variationId)
                ->when($userId, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }, function ($q) use ($sessionId) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                })
                ->first();
            
            // If not found, look for old cart items without variation_id (created before variation support)
            // This handles migration of old cart items
            if (!$existingCart) {
                $existingCart = Cart::where('product_id', $productId)
                    ->whereNull('variation_id')
                    ->when($userId, function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }, function ($q) use ($sessionId) {
                        $q->whereNull('user_id')->where('session_id', $sessionId);
                    })
                    ->first();
                
                if ($existingCart) {
                    Log::info('Found old cart item without variation_id - will update it', [
                        'cart_id' => $existingCart->id,
                        'product_id' => $productId,
                        'new_variation_id' => $variationId
                    ]);
                }
            }
        } else {
            // No variation_id - only match items without variation_id
            $existingCart = Cart::where('product_id', $productId)
                ->whereNull('variation_id')
                ->when($userId, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }, function ($q) use ($sessionId) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                })
                ->first();
        }

        if ($existingCart) {
            // Update quantity
            $existingCart->qty += $qty;
            
            // CRITICAL: If variation_id is provided, ALWAYS update it (even if cart already has one)
            // This ensures the cart always has the correct variation_id
            if ($variationId) {
                $oldVariationId = $existingCart->variation_id;
                $existingCart->variation_id = $variationId;
                
                if ($oldVariationId != $variationId) {
                    Log::info('Updating existing cart item with variation_id', [
                        'cart_id' => $existingCart->id,
                        'old_variation_id' => $oldVariationId,
                        'new_variation_id' => $variationId,
                        'product_id' => $productId
                    ]);
                }
            }
            
            $existingCart->save();
            
            // CRITICAL: Refresh from database to verify variation_id was saved
            $existingCart->refresh();
            
            Log::info('Updated existing cart item', [
                'cart_id' => $existingCart->id,
                'variation_id' => $existingCart->variation_id,
                'variation_id_verified' => $existingCart->variation_id === $variationId,
                'quantity' => $existingCart->qty
            ]);
        } else {
            $cartData = [
                'product_id' => $productId,
                'qty' => $qty,
            ];
            
            if ($variationId) {
                $cartData['variation_id'] = $variationId;
            }
            if ($userId) {
                $cartData['user_id'] = $userId;
            } else {
                $cartData['session_id'] = $sessionId;
            }
            
            Log::info('Creating new cart item', [
                'cart_data' => $cartData,
                'user_id' => $userId,
                'variation_id_received' => $variationId,
                'variation_id_saved' => $cartData['variation_id'] ?? null
            ]);
            
            $existingCart = Cart::create($cartData);
            
            // Verify variation_id was saved
            $existingCart->refresh();
            Log::info('Cart item created - verification', [
                'cart_id' => $existingCart->id,
                'variation_id_in_db' => $existingCart->variation_id,
                'expected_variation_id' => $variationId
            ]);
            
            // CRITICAL: If we created a new cart item with variation_id, 
            // delete any old cart items for the same product without variation_id
            // This prevents duplicate cart items
            if ($variationId && $product->has_variations) {
                $oldCartItems = Cart::where('product_id', $productId)
                    ->whereNull('variation_id')
                    ->where('id', '!=', $existingCart->id)
                    ->when($userId, function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }, function ($q) use ($sessionId) {
                        $q->whereNull('user_id')->where('session_id', $sessionId);
                    })
                    ->get();
                
                if ($oldCartItems->count() > 0) {
                    Log::info('Cleaning up old cart items without variation_id', [
                        'product_id' => $productId,
                        'old_items_count' => $oldCartItems->count(),
                        'new_cart_id' => $existingCart->id
                    ]);
                    $oldCartItems->each->delete();
                }
            }
        }

        // CRITICAL: Final verification - refresh from database one more time
        $existingCart->refresh();
        
        // Verify variation_id is set for products with variations
        if ($product->has_variations && !$existingCart->variation_id) {
            Log::error('Cart item created/updated without variation_id for product with variations - FINAL CHECK FAILED', [
                'cart_id' => $existingCart->id,
                'product_id' => $productId,
                'product_name' => $product->name,
                'variation_id_received' => $variationId,
                'variation_id_in_db' => $existingCart->variation_id,
                'cart_data' => $existingCart->toArray()
            ]);
            
            // Try to fix it one more time
            if ($variationId) {
                $existingCart->variation_id = $variationId;
                $existingCart->save();
                $existingCart->refresh();
                
                if ($existingCart->variation_id) {
                    Log::info('Fixed variation_id on retry', [
                        'cart_id' => $existingCart->id,
                        'variation_id' => $existingCart->variation_id
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to add product: Variation selection is required. Please select a variation (Color/Size) and try again.',
                        'error_type' => 'variation_required'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add product: Variation selection is required. Please select a variation (Color/Size) and try again.',
                    'error_type' => 'variation_required'
                ], 422);
            }
        }
        
        // Final verification before returning
        $existingCart->refresh();
        
        Log::info('Cart add - FINAL RESULT', [
            'cart_id' => $existingCart->id,
            'product_id' => $existingCart->product_id,
            'variation_id_in_db' => $existingCart->variation_id,
            'user_id_in_db' => $existingCart->user_id,
            'session_id_in_db' => $existingCart->session_id,
            'expected_variation_id' => $variationId,
            'expected_user_id' => $userId,
            'expected_session_id' => $sessionId
        ]);
        
        // Load product with category for GTM tracking
        $product = $existingCart->product;
        $productData = null;
        if ($product) {
            $product->load('category');
            $finalPrice = $variation && $variation->price ? $variation->price : ($product->discount ?? $product->price);
            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? '',
                'price' => $finalPrice,
            ];
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart' => $existingCart->load('variation'),
            'product' => $productData,
            'qty' => $existingCart->qty,
            'variation_id' => $existingCart->variation_id, // Include in response for debugging
            'cart_id' => $existingCart->id,
            'debug' => [
                'variation_id_saved' => $existingCart->variation_id,
                'user_id_saved' => $existingCart->user_id,
                'session_id_saved' => $existingCart->session_id
            ]
        ]);
    }

    public function getCartQtySum()
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();
        $sum = Cart::when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->sum('qty');
        return response()->json(['qty_sum' => $sum]);
    }

    public function getCartList()
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();
        $cartQuery = Cart::with('product')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            });
        
        $cartItems = $cartQuery->get();
        
        // CRITICAL: Clean up invalid cart items (products with variations but no variation_id)
        // BUT only for THIS user's/session's cart items to avoid deleting other users' carts
        $invalidCartItems = [];
        foreach ($cartItems as $item) {
            // Only check items that belong to this user/session
            $belongsToUser = ($userId && $item->user_id == $userId) || (!$userId && !$item->user_id && $item->session_id == $sessionId);
            
            if ($belongsToUser && $item->product && $item->product->has_variations && !$item->variation_id) {
                $invalidCartItems[] = $item->id;
                Log::warning('Found invalid cart item in cart list - will remove', [
                    'cart_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'user_id' => $userId,
                    'session_id' => $sessionId
                ]);
            }
        }
        
        if (count($invalidCartItems) > 0) {
            // Only delete items that belong to this user/session
            Cart::whereIn('id', $invalidCartItems)
                ->when($userId, function($q) use ($userId) {
                    $q->where('user_id', $userId);
                }, function($q) use ($sessionId) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                })
                ->delete();
            
            // Remove from collection
            $cartItems = $cartItems->reject(function($item) use ($invalidCartItems) {
                return in_array($item->id, $invalidCartItems);
            });
            Log::info('Removed invalid cart items from cart list', [
                'removed_ids' => $invalidCartItems,
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
        }
        
        // Clean up old cart items (older than 24 hours) - but only for this user/session
        $this->cleanupOldCartItems($userId, $sessionId);
        
        // Debug logging
        Log::info('Cart list request', [
            'user_id' => $userId,
            'items_found' => $cartItems->count(),
            'items' => $cartItems->map(function($item) {
                return [
                    'cart_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : 'Unknown',
                    'user_id' => $item->user_id
                ];
            })->toArray()
        ]);

        $cartList = [];
        $cartTotal = 0;
        $itemsToRemove = []; // Track items to remove
        
        foreach ($cartItems as $item) {
            $product = $item->product;
            if (!$product) {
                // Product no longer exists, mark for removal
                $itemsToRemove[] = $item->id;
                Log::warning('Cart item references non-existent product', [
                    'cart_id' => $item->id,
                    'product_id' => $item->product_id,
                    'user_id' => $item->user_id
                ]);
                continue;
            }
            // Use variation price if available, otherwise use product price
            $price = $product->price;
            if ($item->variation_id) {
                $variation = \App\Models\ProductVariation::find($item->variation_id);
                if ($variation && $variation->price) {
                    $price = $variation->price;
                }
            }
            
            // Apply discount if available
            if ($product->discount && $product->discount > 0) {
                $price = $product->discount;
            }
            
            $total = $price * $item->qty;
            $cartList[] = [
                'cart_id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'qty' => $item->qty,
                'price' => $price,
                'total' => $total,
            ];
            $cartTotal += $total;
        }
        
        // Remove orphaned cart items
        if (!empty($itemsToRemove)) {
            Cart::whereIn('id', $itemsToRemove)->delete();
            Log::info('Removed orphaned cart items', ['removed_ids' => $itemsToRemove]);
        }

        return response()->json([
            'cart' => $cartList,
            'cart_total' => $cartTotal
        ]);
    }
    
    private function cleanupOldCartItems($userId = null, $sessionId = null)
    {
        // Remove cart items older than 24 hours - but only for this user/session
        $cutoffTime = now()->subHours(24);
        $query = Cart::where('created_at', '<', $cutoffTime);
        
        // Only clean up items for this specific user/session
        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->whereNull('user_id')->where('session_id', $sessionId);
        }
        
        $oldItems = $query->get();
        
        if ($oldItems->count() > 0) {
            Log::info('Cleaning up old cart items', [
                'count' => $oldItems->count(),
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
            $query->delete();
        }
    }

    public function increaseQuantity($cartId)
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();
        
        $cartItem = Cart::where('id', $cartId)
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->first();
        
        if ($cartItem) {
            $cartItem->qty += 1;
            $cartItem->save();
            return response()->json(['success' => true, 'qty' => $cartItem->qty]);
        }
        return response()->json(['success' => false, 'message' => 'Cart item not found']);
    }

    public function decreaseQuantity($cartId)
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();
        
        $cartItem = Cart::where('id', $cartId)
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->first();
        
        if ($cartItem && $cartItem->qty > 1) {
            $cartItem->qty -= 1;
            $cartItem->save();
            return response()->json(['success' => true, 'qty' => $cartItem->qty]);
        }
        return response()->json(['success' => false, 'message' => 'Cannot decrease quantity']);
    }

    public function deleteCartItem($cartId)
    {
        $userId = Auth::check() ? Auth::user()->id : null;
        $sessionId = session()->getId();
        
        $deleted = Cart::where('id', $cartId)
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($sessionId) {
                $q->whereNull('user_id')->where('session_id', $sessionId);
            })
            ->delete();
        
        return response()->json(['success' => $deleted > 0]);
    }

    public function buyNow($productId, Request $request)
    {
        try {
            $userId = Auth::check() ? Auth::user()->id : null;
            $sessionId = session()->getId();
            
            // Get variation_id from request
            $variationId = $request->input('variation_id');
            $qty = (int) $request->input('qty', 1);
            if ($qty < 1) $qty = 1;
            
            // Load product to check if it has variations
            $product = \App\Models\Product::findOrFail($productId);
            
            // CRITICAL: For products with variations, variation_id is REQUIRED
            if ($product->has_variations && !$variationId) {
                Log::error('Buy Now - missing variation_id for product with variations', [
                    'product_id' => $productId,
                    'product_name' => $product->name
                ]);
                
                return redirect()->back()->with('error', "Please select a variation (Color/Size) before clicking Buy Now.");
            }
            
            // Validate variation_id belongs to product
            if ($variationId) {
                $variation = \App\Models\ProductVariation::where('id', $variationId)
                    ->where('product_id', $productId)
                    ->first();
                
                if (!$variation) {
                    return redirect()->back()->with('error', "Invalid variation selected. Please select a valid variation.");
                }
            }
            
            // Find existing cart item with same product and variation
            $cartQuery = Cart::where('product_id', $productId)
                ->when($userId, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }, function ($q) use ($sessionId) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                });
            
            if ($variationId) {
                $cartQuery->where('variation_id', $variationId);
            } else {
                $cartQuery->whereNull('variation_id');
            }
            
            $cartItem = $cartQuery->first();
            
            if ($cartItem) {
                // Update existing cart item - set quantity to requested qty (not increment)
                $cartItem->qty = $qty;
                
                // Update variation_id if provided and cart doesn't have it
                if ($variationId && !$cartItem->variation_id) {
                    $cartItem->variation_id = $variationId;
                }
                
                $cartItem->save();
            } else {
                // Create new cart item
                $cartData = [
                    'product_id' => $productId,
                    'qty' => $qty,
                ];
                
                if ($variationId) {
                    $cartData['variation_id'] = $variationId;
                }
                
                if ($userId) {
                    $cartData['user_id'] = $userId;
                } else {
                    $cartData['session_id'] = $sessionId;
                }
                
                $cartItem = Cart::create($cartData);
            }
            
            // Verify cart item was created/updated correctly
            $cartItem->refresh();
            if ($product->has_variations && !$cartItem->variation_id) {
                Log::error('Buy Now - cart item missing variation_id after creation', [
                    'cart_id' => $cartItem->id,
                    'product_id' => $productId,
                    'variation_id_received' => $variationId
                ]);
                return redirect()->back()->with('error', "Failed to add product to cart. Please try again.");
            }
            
            Log::info('Buy Now - cart item created/updated', [
                'cart_id' => $cartItem->id,
                'product_id' => $productId,
                'variation_id' => $cartItem->variation_id,
                'quantity' => $cartItem->qty
            ]);

            return redirect('/checkout');
        } catch (\Exception $e) {
            Log::error('Error buying now: ' . $e->getMessage(), [
                'product_id' => $productId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to process Buy Now. Please try again.');
        }
    }
}