<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'invoice_id',
        'return_date',
        'status',
        'refund_type',
        'reason',
        'processed_by',
        'processed_at',
        'account_id',
        'return_to_type',
        'return_to_id',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }
    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrderReturnItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'return_to_id');
    }

    // Accessor to get the destination name
    public function getDestinationNameAttribute()
    {
        switch ($this->return_to_type) {
            case 'branch':
                $branch = \App\Models\Branch::find($this->return_to_id);
                return $branch ? $branch->name : 'N/A';
            case 'warehouse':
                $warehouse = \App\Models\Warehouse::find($this->return_to_id);
                return $warehouse ? $warehouse->name : 'N/A';
            case 'employee':
                $employee = \App\Models\Employee::with('user')->find($this->return_to_id);
                if ($employee && $employee->user) {
                    return $employee->user->first_name . ' ' . $employee->user->last_name;
                }
                return 'N/A';
            default:
                return 'N/A';
        }
    }
}
