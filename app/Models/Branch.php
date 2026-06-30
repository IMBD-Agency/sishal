<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'location',
        'contact_info',
        'status',
        'manager_id',
        'show_online',
        'warehouse_id',
        'is_warehouse',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', 'active');
        });
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }

    public function employees()
    {
        return $this->hasMany(\App\Models\Employee::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class);
    }

    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function branchProductStocks()
    {
        return $this->hasMany(\App\Models\BranchProductStock::class);
    }

    public function pos()
    {
        return $this->hasMany(\App\Models\Pos::class);
    }
}
