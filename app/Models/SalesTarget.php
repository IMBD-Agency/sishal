<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'target_amount',
        'achieved_amount',
        'bonus_percentage',
        'period_type',
        'period_month',
        'period_year',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function getAchievementPercentageAttribute()
    {
        if ($this->target_amount == 0) return 0;
        return ($this->achieved_amount / $this->target_amount) * 100;
    }

    public function getIsAchievedAttribute()
    {
        return $this->achieved_amount >= $this->target_amount;
    }

    public function getCalculatedBonusAttribute()
    {
        if (!$this->is_achieved) return 0;
        return ($this->achieved_amount * $this->bonus_percentage) / 100;
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, $month, $year)
    {
        return $query->where('period_month', $month)->where('period_year', $year);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAchieved($query)
    {
        return $query->where('status', 'achieved');
    }
}
