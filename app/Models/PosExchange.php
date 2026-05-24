<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosExchange extends Model
{
    protected $guarded = ['id'];

    public function originalPos()
    {
        return $this->belongsTo(Pos::class, 'original_pos_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function items()
    {
        return $this->hasMany(PosExchangeItem::class);
    }

    public function returnedItems()
    {
        return $this->hasMany(PosExchangeItem::class)->where('type', 'returned');
    }

    public function newItems()
    {
        return $this->hasMany(PosExchangeItem::class)->where('type', 'new');
    }
}
