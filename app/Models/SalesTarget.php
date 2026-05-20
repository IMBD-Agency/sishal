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
        'target_quantity',
        'incentive_amount',
        'commission_per_extra_sale',
        'achieved_quantity',
        'achieved_incentive',
        'achieved_extra_commission',
        'total_achieved_bonus',
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
        'target_quantity' => 'decimal:2',
        'incentive_amount' => 'decimal:2',
        'commission_per_extra_sale' => 'decimal:2',
        'achieved_quantity' => 'decimal:2',
        'achieved_incentive' => 'decimal:2',
        'achieved_extra_commission' => 'decimal:2',
        'total_achieved_bonus' => 'decimal:2',
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
        if ($this->target_quantity == 0) return 0;
        return ($this->achieved_quantity / $this->target_quantity) * 100;
    }

    public function getIsAchievedAttribute()
    {
        return $this->achieved_quantity >= $this->target_quantity;
    }

    public function getCalculatedBonusAttribute()
    {
        return $this->total_achieved_bonus;
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
