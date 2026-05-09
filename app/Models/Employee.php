<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'phone',
        'address',
        'designation',
        'salary',
        'previous_salary',
        'increment_amount',
        'increment_percentage',
        'increment_effective_date',
        'last_increment_date',
        'hire_date',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function balance()
    {
        return $this->hasOne(\App\Models\Balance::class, 'source_id');
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function activeSalesTargets()
    {
        return $this->salesTargets()->active();
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function applyIncrement($newSalary, $incrementAmount = null, $incrementPercentage = null, $effectiveDate = null)
    {
        $this->previous_salary = $this->salary;
        $this->salary = $newSalary;
        $this->increment_amount = $incrementAmount ?? ($newSalary - $this->previous_salary);
        $this->increment_percentage = $incrementPercentage ?? (($this->increment_amount / $this->previous_salary) * 100);
        $this->last_increment_date = now();
        $this->increment_effective_date = $effectiveDate ?? now()->addMonth()->startOfMonth();
        $this->save();

        return $this;
    }
}
