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
}
