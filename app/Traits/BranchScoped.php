<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

trait BranchScoped
{
    /**
     * Get the current user's branch ID
     */
    protected function getUserBranchId()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }

        // Super Admin can see all branches
        if ($user->hasRole('Super Admin')) {
            return null;
        }

        // Get employee's branch (if null, means global access)
        return $user->employee ? $user->employee->branch_id : null;
    }

    /**
     * Apply branch scope to a query
     * 
     * @param Builder $query
     * @param string $branchColumn The column name for branch_id (default: 'branch_id')
     * @return Builder
     */
    protected function scopeToBranch($query, $branchColumn = 'branch_id')
    {
        $branchId = $this->getUserBranchId();

        // If no branch restrictionReturn all
        if ($branchId === null) {
            return $query;
        }

        // Filter by user's branch
        return $query->where($branchColumn, $branchId);
    }

    /**
     * Check if current user can access a specific branch
     */
    protected function canAccessBranch($branchId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->canAccessBranch($branchId);
    }

    /**
     * Check if current user is branch-restricted
     */
    protected function isBranchRestricted()
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->isBranchRestricted();
    }

    /**
     * Get branches accessible to current user
     * Returns all branches for admins/global users, or single branch for restricted employees
     */
    protected function getAccessibleBranches()
    {
        $user = Auth::user();
        
        if (!$user) {
            return collect();
        }

        // Super Admin or Global Employees (no branch_id) can access all branches
        if ($user->hasRole('Super Admin') || ($user->employee && is_null($user->employee->branch_id))) {
            return \App\Models\Branch::all();
        }

        // Restricted Employees can only access their branch
        if ($user->employee && $user->employee->branch_id) {
            return \App\Models\Branch::where('id', $user->employee->branch_id)->get();
        }

        // Fallback (e.g. non-employee users) - assume restricted or no access
        return collect();
    }
}
