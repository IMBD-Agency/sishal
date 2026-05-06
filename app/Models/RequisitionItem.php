<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id',
        'product_id',
        'variation_id',
        'quantity',
        'fulfilled_quantity',
        'status',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class);
    }
}
