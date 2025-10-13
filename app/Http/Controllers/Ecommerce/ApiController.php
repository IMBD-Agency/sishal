<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;

class ApiController extends Controller
{
    public function mostSoldProducts()
    {
        $userId = Auth::id();
        $products = \App\Models\Product::with('category')->where('type','product')
            ->take(20)
            ->get();

        // Attach is_wishlisted, rating, and stock data to each product
        $products->transform(function ($product) use ($userId) {
            $product->is_wishlisted = false;
            if ($userId) {
                $product->is_wishlisted = Wishlist::where('user_id', $userId)
                    ->where('product_id', $product->id)
                    ->exists();
            }
            
            // Add stock information
            $product->has_stock = $product->hasStock();
            // Add rating information
            if (method_exists($product, 'averageRating')) {
                $product->avg_rating = $product->averageRating();
            }
            if (method_exists($product, 'totalReviews')) {
                $product->total_reviews = $product->totalReviews();
            }
            
            return $product;
        });

        return response()->json($products);
    }

    public function newArrivalsProducts()
    {
        $userId = Auth::id();
        $products = \App\Models\Product::with('category')
            ->where('type','product')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $products->transform(function ($product) use ($userId) {
            $product->is_wishlisted = false;
            if ($userId) {
                $product->is_wishlisted = Wishlist::where('user_id', $userId)
                    ->where('product_id', $product->id)
                    ->exists();
            }
            $product->has_stock = $product->hasStock();
            if (method_exists($product, 'averageRating')) {
                $product->avg_rating = $product->averageRating();
            }
            if (method_exists($product, 'totalReviews')) {
                $product->total_reviews = $product->totalReviews();
            }
            return $product;
        });

        return response()->json($products);
    }
}
