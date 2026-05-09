<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalesTarget;
use App\Models\SalaryPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BonusCalculationService
{
    public function calculateAchievementForEmployee($employeeId, $month, $year)
    {
        $target = SalesTarget::where('employee_id', $employeeId)
                           ->where('period_month', $month)
                           ->where('period_year', $year)
                           ->where('status', 'active')
                           ->first();

        if (!$target) {
            return [
                'has_target' => false,
                'target_amount' => 0,
                'achieved_amount' => 0,
                'achievement_percentage' => 0,
                'bonus_amount' => 0,
            ];
        }

        $achievedAmount = $this->calculateEmployeeSales($employeeId, $month, $year);
        $achievementPercentage = ($target->target_amount > 0) ? ($achievedAmount / $target->target_amount) * 100 : 0;
        $bonusAmount = ($achievementPercentage >= 100) ? ($achievedAmount * $target->bonus_percentage) / 100 : 0;

        $target->update([
            'achieved_amount' => $achievedAmount,
            'status' => ($achievementPercentage >= 100) ? 'achieved' : 'active'
        ]);

        return [
            'has_target' => true,
            'target' => $target,
            'target_amount' => $target->target_amount,
            'achieved_amount' => $achievedAmount,
            'achievement_percentage' => $achievementPercentage,
            'bonus_amount' => $bonusAmount,
        ];
    }

    public function calculateEmployeeSales($employeeId, $month, $year)
    {
        $totalSales = 0;

        // Get sales from POS system
        $posSales = DB::table('pos')
            ->where('created_by', $employeeId)
            ->whereMonth('created_at', '=', date('m', strtotime($month)))
            ->whereYear('created_at', '=', $year)
            ->sum('total_amount');

        $totalSales += $posSales;

        // Get sales from manual sales
        $manualSales = DB::table('manual_sales')
            ->where('created_by', $employeeId)
            ->whereMonth('created_at', '=', date('m', strtotime($month)))
            ->whereYear('created_at', '=', $year)
            ->sum('total_amount');

        $totalSales += $manualSales;

        return $totalSales;
    }

    public function applyBonusToSalaryPayment($salaryPaymentId, $bonusAmount = null, $isEditable = true)
    {
        $salaryPayment = SalaryPayment::find($salaryPaymentId);
        if (!$salaryPayment) {
            return false;
        }

        $employeeId = $salaryPayment->employee_id;
        $month = $salaryPayment->month;
        $year = $salaryPayment->year;

        $achievementData = $this->calculateAchievementForEmployee($employeeId, $month, $year);

        if ($achievementData['has_target'] && $achievementData['bonus_amount'] > 0) {
            $finalBonusAmount = $bonusAmount ?? $achievementData['bonus_amount'];
            
            $salaryPayment->update([
                'bonus_amount' => $finalBonusAmount,
                'is_bonus_editable' => $isEditable,
                'sales_target_id' => $achievementData['target']->id,
            ]);

            Log::info("Bonus applied to salary payment {$salaryPaymentId}: {$finalBonusAmount}");
            
            return true;
        }

        return false;
    }

    public function bulkCalculateBonuses($month, $year)
    {
        $targets = SalesTarget::where('period_month', $month)
                             ->where('period_year', $year)
                             ->where('status', 'active')
                             ->get();

        $results = [];
        foreach ($targets as $target) {
            $achievementData = $this->calculateAchievementForEmployee(
                $target->employee_id, 
                $month, 
                $year
            );

            $results[] = [
                'employee_id' => $target->employee_id,
                'employee_name' => $target->employee->user->first_name . ' ' . $target->employee->user->last_name,
                'target_amount' => $achievementData['target_amount'],
                'achieved_amount' => $achievementData['achieved_amount'],
                'achievement_percentage' => $achievementData['achievement_percentage'],
                'bonus_amount' => $achievementData['bonus_amount'],
                'target_achieved' => $achievementData['achievement_percentage'] >= 100,
            ];
        }

        return $results;
    }

    public function updateSalesTargetAchievement($targetId, $achievedAmount)
    {
        $target = SalesTarget::find($targetId);
        if (!$target) {
            return false;
        }

        $achievementPercentage = ($target->target_amount > 0) ? ($achievedAmount / $target->target_amount) * 100 : 0;
        $status = ($achievementPercentage >= 100) ? 'achieved' : 'active';

        $target->update([
            'achieved_amount' => $achievedAmount,
            'status' => $status,
        ]);

        return [
            'achievement_percentage' => $achievementPercentage,
            'status' => $status,
            'bonus_amount' => ($achievementPercentage >= 100) ? ($achievedAmount * $target->bonus_percentage) / 100 : 0,
        ];
    }

    public function getBonusSummary($month, $year)
    {
        $targets = SalesTarget::where('period_month', $month)
                             ->where('period_year', $year)
                             ->with(['employee.user'])
                             ->get();

        $summary = [
            'total_targets' => $targets->count(),
            'achieved_targets' => $targets->where('status', 'achieved')->count(),
            'total_target_amount' => $targets->sum('target_amount'),
            'total_achieved_amount' => $targets->sum('achieved_amount'),
            'total_bonus_amount' => 0,
            'achievement_rate' => 0,
        ];

        foreach ($targets as $target) {
            if ($target->status === 'achieved') {
                $summary['total_bonus_amount'] += ($target->achieved_amount * $target->bonus_percentage) / 100;
            }
        }

        $summary['achievement_rate'] = $summary['total_targets'] > 0 
            ? ($summary['achieved_targets'] / $summary['total_targets']) * 100 
            : 0;

        return $summary;
    }
}
