<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BranchIsolation
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that employees can only access data from their assigned branch.
     * Admins and users without employee records bypass this restriction.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Skip isolation for Super Admin ONLY
        // Note: We do NOT skip found is_admin because all employees are admins in this system
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Check if user has an employee record
        $employee = $user->employee;

        if (!$employee) {
            // User is not an employee, allow access
            return $next($request);
        }

        // If employee has a branch assigned, enforce isolation
        // If branch_id is NULL, they have Global Access (return next)
        if ($employee->branch_id) {
            // Store the employee's branch_id in the request for easy access
            $request->merge(['user_branch_id' => $employee->branch_id]);
            $request->merge(['user_employee_id' => $employee->id]);
        }

        return $next($request);
    }
}
