<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariationAttribute extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_required',
        'is_color',
        'sort_order',
        'status'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_color' => 'boolean',
    ];

    /**
     * Get the attribute values.
     */
    public function values(): HasMany
    {
        return $this->hasMany(VariationAttributeValue::class, 'attribute_id');
    }

    /**
     * Get active attribute values.
     */
    public function activeValues(): HasMany
    {
        return $this->values()->where('status', 'active')->orderBy('sort_order');
    }

    /**
     * Scope for active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for color attributes.
     */
    public function scopeColor($query)
    {
        return $query->where('is_color', true);
    }

    /**
     * Scope for required attributes.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}
