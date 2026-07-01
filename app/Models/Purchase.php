<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'ship_location_type',
        'location_id',
        'purchase_date',
        'status',
        'created_by',
        'notes',
        'bill_id',
        'is_billed',
    ];

    protected static function booted()
    {
        static::deleting(function ($purchase) {
            if ($purchase->bill) {
                $purchase->bill->delete();
            }
            $purchase->items()->delete();
        });
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class,'purchase_id')->orderBy('sort_order');
    }

    public function bill()
    {
        return $this->hasOne(PurchaseBill::class, 'purchase_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
