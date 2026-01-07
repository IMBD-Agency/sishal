<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $effectivePrice = $this->effective_price;
        $hasDiscount = $effectivePrice < $this->price;
        $discountValue = $hasDiscount ? (float) $effectivePrice : 0;

        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'discount' => $discountValue,
            'image' => $this->getImageUrl(),
            'short_desc' => $this->short_desc,
            'description' => $this->description,
            'status' => $this->status,
            'has_variations' => $this->has_variations,
            'manage_stock' => $this->manage_stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            
            // Computed fields
            'is_wishlisted' => $this->when(isset($this->is_wishlisted), $this->is_wishlisted),
            'has_stock' => $this->when(isset($this->has_stock), $this->has_stock),
            'avg_rating' => $this->when(isset($this->avg_rating), round($this->avg_rating, 1)),
            'total_reviews' => $this->when(isset($this->total_reviews), $this->total_reviews),
            'total_sold' => $this->when(isset($this->total_sold), $this->total_sold),
            'total_revenue' => $this->when(isset($this->total_revenue), round($this->total_revenue, 2)),
             
            // Price calculations
            'final_price' => (float) $effectivePrice,
            'discount_percentage' => $this->price > 0 && $hasDiscount ? round((($this->price - $effectivePrice) / $this->price) * 100, 1) : 0,
        ];
    }

    /**
     * Get the image URL with proper fallback
     */
    protected function getImageUrl(): string
    {
        if (!$this->image) {
            return asset('static/default-product.jpg');
        }

        // Check if image path is already a full URL
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }


     
        // Check if image starts with / or public/
        $imagePath = $this->image;
        if (strpos($imagePath, 'public/') === 0) {
            $imagePath = substr($imagePath, 7); // Remove 'public/' prefix
        }
        if (strpos($imagePath, '/') !== 0) {
            $imagePath = '/' . $imagePath;
        }

        // Use asset() helper for proper URL generation
        return asset($imagePath);
    }
}

