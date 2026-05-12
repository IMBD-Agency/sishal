<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ComboProduct extends Model
{
    protected $fillable = [
        'combo_product_id',
        'product_id',
        'variation_id',
        'quantity',
        'combo_price',
    ];

    protected static function boot()
    {
        parent::boot();

        // Validate before creating
        static::creating(function ($comboProduct) {
            // Check if combo product exists and is type=combo
            $combo = Product::find($comboProduct->combo_product_id);
            if (!$combo || $combo->type !== 'combo') {
                throw new \InvalidArgumentException('Invalid combo product or product is not a combo type');
            }

            // Check if product exists
            if (!Product::find($comboProduct->product_id)) {
                throw new \InvalidArgumentException('Product does not exist');
            }

            // Check if variation exists (if provided)
            if ($comboProduct->variation_id && !ProductVariation::find($comboProduct->variation_id)) {
                throw new \InvalidArgumentException('Variation does not exist');
            }

            // Prevent adding combo to itself
            if ($comboProduct->combo_product_id === $comboProduct->product_id) {
                throw new \InvalidArgumentException('Cannot add a combo to itself');
            }
        });

        // Cascade delete when parent combo is deleted (simulating onDelete('cascade'))
        static::deleted(function ($comboProduct) {
            // Cleanup logic if needed
        });
    }

    public function combo()
    {
        return $this->belongsTo(Product::class, 'combo_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }
}
