<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'country',
        'state',
        'country_code',
        'latitude',
        'longitude',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get shipping methods for this city
     */
    public function shippingMethods()
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_method_cities')
            ->withPivot('cost_override')
            ->withTimestamps();
    }

    /**
     * Scope for active cities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for searching cities
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('country', 'like', "%{$term}%")
              ->orWhere('state', 'like', "%{$term}%");
        });
    }

    /**
     * Get display name with country
     */
    public function getDisplayNameAttribute()
    {
        $parts = [$this->name];
        if ($this->state) {
            $parts[] = $this->state;
        }
        if ($this->country) {
            $parts[] = $this->country;
        }
        return implode(', ', $parts);
    }
}
