<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\Employee;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view employee list')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SalaryPayment::with(['employee.user', 'branch', 'chartOfAccount']);

        if ($request->filled('month') && $request->month != 'Select One') {
            $query->where('month', $request->month);
        }
        if ($request->filled('year') && $request->year != 'Select One') {
            $query->where('year', $request->year);
        }
        if ($request->filled('employee_id') && $request->employee_id != 'all') {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('account_id') && $request->account_id != 'all') {
            $query->where('account_id', $request->account_id);
        }

        $payments = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));
        
        $employees = Employee::with('user')->get();
        $branches = Branch::all();
        
        // Fetch Asset Accounts (Cash/Bank) for the filter
        $assetTypeIds = ChartOfAccountType::whereIn('name', ['Asset', 'Assets'])->pluck('id');
        $accounts = ChartOfAccount::whereIn('type_id', $assetTypeIds)
            ->orWhereHas('parent', function($q) use ($assetTypeIds) {
                $q->whereIn('type_id', $assetTypeIds);
            })->get();

        return view('erp.salary.index', compact('payments', 'employees', 'branches', 'accounts'));
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        $branches = Branch::all();
        
        // Fetch Asset Accounts (Cash/Bank)
        $assetTypeIds = ChartOfAccountType::whereIn('name', ['Asset', 'Assets'])->pluck('id');
        $accounts = ChartOfAccount::whereIn('type_id', $assetTypeIds)
            ->orWhereHas('parent', function($q) use ($assetTypeIds) {
                $q->whereIn('type_id', $assetTypeIds);
            })->get();

        return view('erp.salary.create', compact('employees', 'branches', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required',
            'year' => 'required',
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $employee = Employee::find($request->employee_id);

        SalaryPayment::create([
            'employee_id' => $request->employee_id,
            'branch_id' => $employee->branch_id,
            'month' => $request->month,
            'year' => $request->year,
            'total_salary' => $request->total_salary ?? $employee->salary,
            'paid_amount' => $request->paid_amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'account_id' => $request->account_id,
            'account_no' => $request->account_no,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('salary.index')->with('success', 'Salary payment recorded successfully.');
    }

    public function getSalaryDetails(Request $request)
    {
        $employeeId = $request->employee_id;
        $month = $request->month;
        $year = $request->year;

        $employee = Employee::find($employeeId);
        if (!$employee) return response()->json(['error' => 'Not found'], 404);

        $previousPaid = SalaryPayment::where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('paid_amount');

        return response()->json([
            'salary' => $employee->salary,
            'previous_paid' => $previousPaid,
            'due' => $employee->salary - $previousPaid
        ]);
    }
}
