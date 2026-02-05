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
}
