<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'location',
        'contact_phone',
        'contact_email',
        'manager_id',
        'status'
    ];

    public function manager()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'manager_id');
    }

    public function branches()
    {
        return $this->hasMany(\App\Models\Branch::class);
    }

    public function warehouseProductStocks()
    {
        return $this->hasMany(\App\Models\WarehouseProductStock::class);
    }

    // Employees are accessed through the branch relationship
    // Use $warehouse->branch->employees to get employees
}
