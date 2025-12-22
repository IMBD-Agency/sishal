<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'variation_id',
        'returned_qty',
        'unit_price',
        'total_price',
        'reason'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(\App\Models\ProductVariation::class, 'variation_id');
    }
}
