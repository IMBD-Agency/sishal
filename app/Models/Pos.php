<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pos extends Model
{
    protected $fillable = [
        'sale_number',
        'challan_number',
        'customer_id',
        'employee_id',
        'sold_by',
        'branch_id',
        'sale_date',
        'sale_type',
        'sub_total',
        'discount',
        'delivery',
        'total_amount',
        'estimated_delivery_date',
        'estimated_delivery_time',
        'status',
        'invoice_id',
        'notes',
        'account_type',
        'account_number',
        'remarks',
        'courier_id',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }
    public function soldBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'sold_by');
    }
    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id');
    }
    public function items()
    {
        return $this->hasMany(\App\Models\PosItem::class, 'pos_sale_id');
    }
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'pos_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class,'branch_id');
    }
}
