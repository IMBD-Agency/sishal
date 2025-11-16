<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cost',
        'estimated_days_min',
        'estimated_days_max',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        'estimated_days_min' => 'integer',
        'estimated_days_max' => 'integer',
        'sort_order' => 'integer',
    ];

    // Scope for active shipping methods
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordering by sort_order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Get formatted delivery time
    public function getDeliveryTimeAttribute()
    {
        if ($this->estimated_days_min && $this->estimated_days_max) {
            if ($this->estimated_days_min == $this->estimated_days_max) {
                return "{$this->estimated_days_min} business day" . ($this->estimated_days_min > 1 ? 's' : '');
            } else {
                return "{$this->estimated_days_min}-{$this->estimated_days_max} business days";
            }
        }
        return null;
    }

    /**
     * Get cities for this shipping method
     */
    public function cities()
    {
        return $this->belongsToMany(City::class, 'shipping_method_cities')
            ->withPivot('cost_override')
            ->withTimestamps();
    }

    /**
     * Get shipping cost for a specific city
     * Returns override cost if set, otherwise checks state for inside/outside Dhaka pricing
     */
    public function getCostForCity($cityId)
    {
        if (!$cityId) {
            return $this->cost;
        }
        
        // Check for city-specific override first
        $pivot = $this->cities()->where('cities.id', $cityId)->first();
        if ($pivot && $pivot->pivot->cost_override !== null) {
            return $pivot->pivot->cost_override;
        }
        
        // Get city to check state
        $city = \App\Models\City::find($cityId);
        if ($city) {
            // If state is "Dhaka" (inside Dhaka), use default cost
            // All other states (Outside Dhaka, Chattogram, Rangpur, Rajshahi, Khulna, Barishal, Sylhet, Mymensingh, etc.) use outside price
            if ($city->state === 'Dhaka') {
                // Inside Dhaka uses default cost
                return $this->cost;
            } else {
                // All other divisions/states get outside Dhaka price (120tk)
                return 120.00; // Outside Dhaka and all other divisions cost
            }
        }
        
        // Default fallback
        return $this->cost;
    }

    /**
     * Scope for shipping methods available in a city
     */
    public function scopeForCity($query, $cityId)
    {
        return $query->whereHas('cities', function($q) use ($cityId) {
            $q->where('cities.id', $cityId);
        })->orWhereDoesntHave('cities'); // Include methods without city restrictions
    }
}
