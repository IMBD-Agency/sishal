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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'discount' => (float) $this->discount,
            'image' => $this->image ? asset($this->image) : asset('static/default-product.jpg'),
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
            'final_price' => $this->discount > 0 ? $this->discount : $this->price,
            'discount_percentage' => $this->price > 0 ? round((($this->price - $this->discount) / $this->price) * 100, 1) : 0,
        ];
    }
}
