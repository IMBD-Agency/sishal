<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'used_count',
        'user_limit',
        'start_date',
        'end_date',
        'is_active',
        'free_delivery',
        'description',
        'scope_type',
        'applicable_categories',
        'applicable_products',
        'excluded_categories',
        'excluded_products',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'free_delivery' => 'boolean',
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'user_limit' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'excluded_categories' => 'array',
        'excluded_products' => 'array',
    ];

    /**
     * Get coupon usages
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Get orders that used this coupon
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if coupon is valid
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

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon can be used by user
     */
    public function canBeUsedBy($userId = null, $sessionId = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($userId) {
            $userUsageCount = $this->usages()
                ->where('user_id', $userId)
                ->count();

            if ($userUsageCount >= $this->user_limit) {
                return false;
            }
        } elseif ($sessionId) {
            $sessionUsageCount = $this->usages()
                ->where('session_id', $sessionId)
                ->count();

            if ($sessionUsageCount >= $this->user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if coupon applies to product
     */
    public function appliesToProduct($productId, $categoryId = null): bool
    {
        // Check excluded products
        if ($this->excluded_products && in_array($productId, $this->excluded_products)) {
            return false;
        }

        // Check excluded categories
        if ($categoryId && $this->excluded_categories && in_array($categoryId, $this->excluded_categories)) {
            return false;
        }

        // If scope is 'all', it applies to all products
        if ($this->scope_type === 'all') {
            return true;
        }

        // Check applicable products
        if ($this->scope_type === 'products' && $this->applicable_products) {
            return in_array($productId, $this->applicable_products);
        }

        // Check applicable categories
        if ($this->scope_type === 'categories' && $categoryId && $this->applicable_categories) {
            return in_array($categoryId, $this->applicable_categories);
        }

        // For exclude types, if not excluded, it applies
        if (in_array($this->scope_type, ['exclude_categories', 'exclude_products'])) {
            return true;
        }

        return false;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($subtotal): float
    {
        // If value is 0 or null, return 0 (for free delivery only coupons)
        if (!$this->value || $this->value <= 0) {
            return 0;
        }
        
        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100;
            
            // Apply max discount limit if set
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
            
            return round($discount, 2);
        } else {
            // Fixed amount
            return min($this->value, $subtotal);
        }
    }

    /**
     * Check if minimum purchase requirement is met
     */
    public function meetsMinimumPurchase($subtotal): bool
    {
        if (!$this->min_purchase) {
            return true;
        }

        return $subtotal >= $this->min_purchase;
    }

    /**
     * Scope: Active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid coupons (active and within date range)
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
            })
            ->where(function($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            });
    }
}
