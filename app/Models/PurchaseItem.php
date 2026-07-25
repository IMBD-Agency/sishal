<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'discount',
        'total_price',
        'description',
        'requisition_item_id',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_item_id');
    }

    public function getDiscountAttribute($value)
    {
        return (float) ($value ?? 0);
    }

    public function getNetTotalPriceAttribute()
    {
        return (float) ($this->total_price - $this->discount);
    }

    public function getNetUnitPriceAttribute()
    {
        return $this->quantity > 0 ? (float) ($this->net_total_price / $this->quantity) : 0;
    }
}
