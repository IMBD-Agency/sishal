<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'cost',
        'discount',
        'image',
        'sort_order',
        'is_default',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    /**
     * Get the product that owns the variation.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variation combinations.
     */
    public function combinations(): HasMany
    {
        return $this->hasMany(ProductVariationCombination::class, 'variation_id');
    }

    /**
     * Get the variation stocks.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductVariationStock::class, 'variation_id');
    }

    /**
     * Get the variation galleries.
     */
    public function galleries(): HasMany
    {
        return $this->hasMany(ProductVariationGallery::class, 'variation_id');
    }

    /**
     * Get the attributes for this variation.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            VariationAttribute::class,
            'product_variation_combinations',
            'variation_id',
            'attribute_id'
        )->withPivot('attribute_value_id');
    }

    /**
     * Get the attribute values for this variation.
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            VariationAttributeValue::class,
            'product_variation_combinations',
            'variation_id',
            'attribute_value_id'
        )->withPivot('attribute_id');
    }

    /**
     * Get the effective price considering discounts.
     */
    public function getEffectivePriceAttribute(): float
    {
        // Base price is variation price if set, otherwise product price
        $basePrice = ($this->price && $this->price > 0) ? (float) $this->price : (float) $this->product->price;
        
        // Discount price is variation discount if set, otherwise product discount
        $discountPrice = ($this->discount && $this->discount > 0) ? (float) $this->discount : (float) ($this->product->discount ?? 0);
        
        // If discount price is valid and less than base price, it's the effective price
        if ($discountPrice > 0 && $discountPrice < $basePrice) {
            return $discountPrice;
        }
        
        return $basePrice;
    }

    /**
     * Get the final price (considering discount).
     * Simple and flexible: Use variation price if it's set (allows for variation-specific pricing)
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->effective_price;
    }

    /**
     * Get the total stock across all locations.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity') ?? 0;
    }

    /**
     * Get the available stock (total - reserved).
     * For ecommerce: Only shows warehouse stock, not branch stock.
     */
    public function getAvailableStockAttribute(): int
    {
        // Use query builder to ensure we get fresh data
        // Only count warehouse stock for ecommerce display (exclude branch stock)
        $totalStock = $this->stocks()->whereNotNull('warehouse_id')->whereNull('branch_id')->sum('quantity') ?? 0;
        $reservedStock = $this->stocks()->whereNotNull('warehouse_id')->whereNull('branch_id')->sum('reserved_quantity') ?? 0;
        return $totalStock - $reservedStock;
    }

    /**
     * Check if variation is in stock.
     */
    public function isInStock(): bool
    {
        return $this->available_stock > 0;
    }

    /**
     * Get variation display name with attributes.
     */
    public function getDisplayNameAttribute(): string
    {
        $attributes = $this->attributeValues->pluck('value')->toArray();
        return $this->product->name . ' - ' . implode(', ', $attributes);
    }

    /**
     * Scope for active variations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for default variations.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
