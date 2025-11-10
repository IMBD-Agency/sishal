<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class BulkDiscount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'value',
        'percentage', // Keep for backward compatibility
        'scope_type',
        'applicable_products',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'type' => 'string',
        'value' => 'decimal:2',
        'percentage' => 'decimal:2', // Keep for backward compatibility
        'applicable_products' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Check if discount is valid (active and within date range)
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if discount applies to product
     */
    public function appliesToProduct($productId): bool
    {
        if ($this->scope_type === 'all') {
            return true;
        }

        if ($this->scope_type === 'products' && $this->applicable_products) {
            return in_array($productId, $this->applicable_products);
        }

        return false;
    }

    /**
     * Calculate discounted price
     */
    public function calculateDiscountedPrice($originalPrice): float
    {
        // Get the discount value (use value field if available, otherwise fallback to percentage for backward compatibility)
        $discountValue = $this->value ?? $this->percentage ?? 0;
        $discountType = $this->type ?? 'percentage';
        
        if ($discountType === 'percentage') {
            // For backward compatibility, if type is not set but percentage exists, use percentage
            if (!$this->type && $this->percentage) {
                $discountAmount = ($originalPrice * $this->percentage) / 100;
            } else {
                $discountAmount = ($originalPrice * $discountValue) / 100;
            }
        } else {
            // Fixed amount discount
            $discountAmount = min($discountValue, $originalPrice);
        }
        
        return round(max(0, $originalPrice - $discountAmount), 2);
    }

    /**
     * Scope: Active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid discounts (active and within date range)
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }
}
