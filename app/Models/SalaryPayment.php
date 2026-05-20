<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'month',
        'year',
        'total_salary',
        'paid_amount',
        'bonus_amount',
        'festival_bonus_amount',
        'is_bonus_editable',
        'sales_target_id',
        'payment_date',
        'payment_method',
        'account_id',
        'account_no',
        'note',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'festival_bonus_amount' => 'decimal:2',
        'is_bonus_editable' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesTarget()
    {
        return $this->belongsTo(SalesTarget::class);
    }

    public function getTotalPaymentAttribute()
    {
        return $this->paid_amount + $this->bonus_amount + $this->festival_bonus_amount;
    }

    public function getNetSalaryAttribute()
    {
        return $this->total_salary + $this->bonus_amount + $this->festival_bonus_amount;
    }
}
