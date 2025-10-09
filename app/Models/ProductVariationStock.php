<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariationStock extends Model
{
    protected $fillable = [
        'variation_id',
        'branch_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'min_stock_level',
        'max_stock_level',
        'updated_by',
        'last_updated_at'
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the variation that owns the stock.
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    /**
     * Get the branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the user who updated the stock.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get available quantity (total - reserved).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Check if stock is low.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }

    /**
     * Check if stock is available.
     */
    public function isAvailable(): bool
    {
        return $this->available_quantity > 0;
    }
}
