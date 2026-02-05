<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Get the branch ID to restrict data to, if user is not a SuperAdmin.
     */
    protected function getRestrictedBranchId()
    {
        $user = auth()->user();
        if (!$user) return null;

        // SuperAdmins see everything
        if ($user->hasRole('SuperAdmin')) {
            return null;
        }

        // Return branch_id if employee record exists
        return $user->employee?->branch_id;
    }
}
