<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $fillable = [
        'pos_sale_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'total_price',
        'current_position_type',
        'current_position_id'
    ];

    // Relationships
    public function pos()
    {
        return $this->belongsTo(\App\Models\Pos::class, 'pos_sale_id');
    }
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'current_position_id');
    }

    public function variation()
    {
        return $this->belongsTo(\App\Models\ProductVariation::class, 'variation_id');
    }

    public function technician()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'current_position_id');
    }

    public function returnItems()
    {
        return $this->hasMany(\App\Models\SaleReturnItem::class, 'sale_item_id');
    }

    public function invoice()
    {
        return $this->hasOneThrough(
            \App\Models\Invoice::class,
            \App\Models\Pos::class,
            'id', // Foreign key on Pos table
            'id', // Foreign key on Invoice table
            'pos_sale_id', // Local key on PosItem table
            'invoice_id' // Local key on Pos table
        );
    }
}
