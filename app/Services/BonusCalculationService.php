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
        $employee = Employee::find($employeeId);
        if (!$employee || !$employee->branch_id) {
            return [
                'has_target' => false,
                'target_amount' => 0,
                'achieved_amount' => 0,
                'achievement_percentage' => 0,
                'bonus_amount' => 0,
            ];
        }

        $target = $this->calculateAchievementForBranch($employee->branch_id, $month, $year);
        if (!$target) {
            return [
                'has_target' => false,
                'target_amount' => 0,
                'achieved_amount' => 0,
                'achievement_percentage' => 0,
                'bonus_amount' => 0,
            ];
        }

        // Get all active employees in this branch to distribute bonus percentage-wise based on salary
        $branchEmployees = Employee::where('branch_id', $employee->branch_id)
                                   ->where('status', 'active')
                                   ->get();

        $totalBranchSalary = $branchEmployees->sum('salary');
        $employeeSalary = $employee->salary;

        $employeeSharePercentage = 0;
        $employeeBonus = 0;

        if ($totalBranchSalary > 0 && $target->total_achieved_bonus > 0) {
            $employeeSharePercentage = ($employeeSalary / $totalBranchSalary) * 100;
            $employeeBonus = ($target->total_achieved_bonus * $employeeSalary) / $totalBranchSalary;
        }

        return [
            'has_target' => true,
            'target' => $target,
            'target_amount' => $target->target_quantity, // mapped to target_quantity for display compatibility
            'achieved_amount' => $target->achieved_quantity, // mapped to achieved_quantity
            'achievement_percentage' => $target->achievement_percentage,
            'bonus_amount' => $employeeBonus,
            'bonus_data' => [
                'has_target' => true,
                'target_quantity' => $target->target_quantity,
                'achieved_quantity' => $target->achieved_quantity,
                'incentive_amount' => $target->incentive_amount,
                'commission_per_extra_sale' => $target->commission_per_extra_sale,
                'total_branch_bonus' => $target->total_achieved_bonus,
                'employee_salary' => $employeeSalary,
                'total_branch_salary' => $totalBranchSalary,
                'share_percentage' => $employeeSharePercentage,
            ]
        ];
    }

    public function calculateAchievementForBranch($branchId, $month, $year)
    {
        $target = SalesTarget::where('branch_id', $branchId)
                             ->where('period_month', $month)
                             ->where('period_year', $year)
                             ->whereIn('status', ['active', 'achieved'])
                             ->first();

        if (!$target) {
            return null;
        }

        $achievedQty = $this->calculateBranchSales($branchId, $month, $year);

        $achievedIncentive = 0;
        $achievedExtraCommission = 0;
        $totalAchievedBonus = 0;
        $status = 'active';

        if ($achievedQty >= $target->target_quantity) {
            $achievedIncentive = $target->incentive_amount;
            $extraQty = $achievedQty - $target->target_quantity;
            $achievedExtraCommission = $extraQty * $target->commission_per_extra_sale;
            $totalAchievedBonus = $achievedIncentive + $achievedExtraCommission;
            $status = 'achieved';
        }

        $target->update([
            'achieved_quantity' => $achievedQty,
            'achieved_incentive' => $achievedIncentive,
            'achieved_extra_commission' => $achievedExtraCommission,
            'total_achieved_bonus' => $totalAchievedBonus,
            'status' => $status,
        ]);

        return $target;
    }

    public function calculateBranchSales($branchId, $month, $year)
    {
        $monthNum = date('m', strtotime($month));

        // Sum the quantities of items sold in the branch for the specified period
        $branchSalesQty = DB::table('pos_items')
            ->join('pos', 'pos_items.pos_sale_id', '=', 'pos.id')
            ->where('pos.branch_id', $branchId)
            ->whereMonth('pos.sale_date', '=', $monthNum)
            ->whereYear('pos.sale_date', '=', $year)
            ->where('pos.status', '!=', 'cancelled')
            ->sum('pos_items.quantity');

        return $branchSalesQty;
    }

    public function calculateEmployeeSales($employeeId, $month, $year)
    {
        // Kept for backwards compatibility but redirected to employee's branch sales quantity
        $employee = Employee::find($employeeId);
        if (!$employee || !$employee->branch_id) {
            return 0;
        }
        return $this->calculateBranchSales($employee->branch_id, $month, $year);
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
                             ->whereIn('status', ['active', 'achieved'])
                             ->get();

        $results = [];
        foreach ($targets as $target) {
            // Update branch achievement first
            $this->calculateAchievementForBranch($target->branch_id, $month, $year);

            // Fetch active employees of that branch
            $branchEmployees = Employee::where('branch_id', $target->branch_id)
                                       ->where('status', 'active')
                                       ->get();

            $totalBranchSalary = $branchEmployees->sum('salary');

            foreach ($branchEmployees as $employee) {
                $employeeSharePercentage = 0;
                $employeeBonus = 0;

                if ($totalBranchSalary > 0 && $target->total_achieved_bonus > 0) {
                    $employeeSharePercentage = ($employee->salary / $totalBranchSalary) * 100;
                    $employeeBonus = ($target->total_achieved_bonus * $employee->salary) / $totalBranchSalary;
                }

                $results[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->user->first_name . ' ' . $employee->user->last_name,
                    'branch_id' => $target->branch_id,
                    'branch_name' => $target->branch ? $target->branch->name : 'N/A',
                    'target_quantity' => $target->target_quantity,
                    'achieved_quantity' => $target->achieved_quantity,
                    'total_branch_bonus' => $target->total_achieved_bonus,
                    'employee_salary' => $employee->salary,
                    'share_percentage' => $employeeSharePercentage,
                    'bonus_amount' => $employeeBonus,
                    'target_achieved' => $target->is_achieved,
                ];
            }
        }

        return $results;
    }

    public function updateSalesTargetAchievement($targetId, $achievedQuantity)
    {
        $target = SalesTarget::find($targetId);
        if (!$target) {
            return false;
        }

        $achievedIncentive = 0;
        $achievedExtraCommission = 0;
        $totalAchievedBonus = 0;
        $status = 'active';

        if ($achievedQuantity >= $target->target_quantity) {
            $achievedIncentive = $target->incentive_amount;
            $extraQty = $achievedQuantity - $target->target_quantity;
            $achievedExtraCommission = $extraQty * $target->commission_per_extra_sale;
            $totalAchievedBonus = $achievedIncentive + $achievedExtraCommission;
            $status = 'achieved';
        }

        $target->update([
            'achieved_quantity' => $achievedQuantity,
            'achieved_incentive' => $achievedIncentive,
            'achieved_extra_commission' => $achievedExtraCommission,
            'total_achieved_bonus' => $totalAchievedBonus,
            'status' => $status,
        ]);

        return [
            'achievement_percentage' => ($target->target_quantity > 0) ? ($achievedQuantity / $target->target_quantity) * 100 : 0,
            'status' => $status,
            'bonus_amount' => $totalAchievedBonus,
        ];
    }

    public function getBonusSummary($month, $year)
    {
        $targets = SalesTarget::where('period_month', $month)
                             ->where('period_year', $year)
                             ->with(['branch'])
                             ->get();

        $summary = [
            'total_targets' => $targets->count(),
            'achieved_targets' => $targets->where('status', 'achieved')->count(),
            'total_target_quantity' => $targets->sum('target_quantity'),
            'total_achieved_quantity' => $targets->sum('achieved_quantity'),
            'total_bonus_amount' => $targets->sum('total_achieved_bonus'),
            'achievement_rate' => 0,
        ];

        $summary['achievement_rate'] = $summary['total_targets'] > 0 
            ? ($summary['achieved_targets'] / $summary['total_targets']) * 100 
            : 0;

        return $summary;
    }
}
