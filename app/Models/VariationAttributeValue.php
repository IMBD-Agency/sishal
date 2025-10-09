<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VariationAttributeValue extends Model
{
    protected $fillable = [
        'attribute_id',
        'value',
        'color_code',
        'image',
        'sort_order',
        'status'
    ];

    /**
     * Get the attribute that owns the value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariationAttribute::class, 'attribute_id');
    }

    /**
     * Get the variations that use this attribute value.
     */
    public function variations(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariation::class,
            'product_variation_combinations',
            'attribute_value_id',
            'variation_id'
        )->withPivot('attribute_id');
    }

    /**
     * Scope for active values.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for color values.
     */
    public function scopeColor($query)
    {
        return $query->whereHas('attribute', function($q) {
            $q->where('is_color', true);
        });
    }

    /**
     * Get the name (alias for value).
     */
    public function getNameAttribute(): string
    {
        return $this->value;
    }

    /**
     * Get the display color (with fallback).
     */
    public function getDisplayColorAttribute(): string
    {
        if ($this->attribute->is_color && $this->color_code) {
            return $this->color_code;
        }
        
        return '#cccccc'; // Default gray color
    }
}
