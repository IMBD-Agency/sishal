<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalaryIncrementController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Employee::with(['user', 'branch'])
                         ->whereNotNull('last_increment_date');

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        // Apply filters
        if ($request->filled('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('month') && $request->month != 'Select Month') {
            $query->whereMonth('last_increment_date', date('m', strtotime($request->month)));
        }

        if ($request->filled('year') && $request->year != 'Select Year') {
            $query->whereYear('last_increment_date', $request->year);
        }

        $employees = $query->orderBy('last_increment_date', 'desc')->paginate(15)->withQueryString();

        // Get branches for filter (only for Super Admin)
        $branches = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $branches = Branch::orderBy('name')->get();
        }

        return view('erp.salary-increments.index', compact('employees', 'branches'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $employees = Employee::with('user')->where('branch_id', $restrictedBranchId)->get();
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $employees = Employee::with('user')->get();
            $branches = Branch::all();
        }

        return view('erp.salary-increments.create', compact('employees', 'branches'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'new_salary' => 'required|numeric|min:0',
            'increment_effective_date' => 'required|date|after_or_equal:today',
        ]);

        $employee = Employee::find($request->employee_id);
        $currentSalary = $employee->salary;
        $newSalary = $request->new_salary;
        $incrementAmount = $newSalary - $currentSalary;
        $incrementPercentage = ($incrementAmount / $currentSalary) * 100;

        $employee->applyIncrement(
            $newSalary,
            $incrementAmount,
            $incrementPercentage,
            $request->increment_effective_date
        );

        return redirect()->route('salary-increments.index')
                        ->with('success', 'Salary increment applied successfully. New salary will be effective from ' . $request->increment_effective_date);
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::with(['user', 'branch', 'salaryPayments'])
                            ->findOrFail($id);

        return view('erp.salary-increments.show', compact('employee'));
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::with(['user', 'branch'])->findOrFail($id);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $branches = Branch::all();
        }

        return view('erp.salary-increments.edit', compact('employee', 'branches'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'new_salary' => 'required|numeric|min:0',
            'increment_effective_date' => 'required|date|after_or_equal:today',
        ]);

        $employee = Employee::find($id);
        $currentSalary = $employee->salary;
        $newSalary = $request->new_salary;
        $incrementAmount = $newSalary - $currentSalary;
        $incrementPercentage = ($incrementAmount / $currentSalary) * 100;

        $employee->applyIncrement(
            $newSalary,
            $incrementAmount,
            $incrementPercentage,
            $request->increment_effective_date
        );

        return redirect()->route('salary-increments.index')
                        ->with('success', 'Salary increment updated successfully. New salary will be effective from ' . $request->increment_effective_date);
    }

    public function getEmployeeSalaryInfo($employeeId)
    {
        if (!auth()->user()->hasPermissionTo('view salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $employee = Employee::with('user')->find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json([
            'current_salary' => $employee->salary,
            'previous_salary' => $employee->previous_salary,
            'last_increment_date' => $employee->last_increment_date,
            'increment_effective_date' => $employee->increment_effective_date,
            'increment_amount' => $employee->increment_amount,
            'increment_percentage' => $employee->increment_percentage,
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Employee::with(['user', 'branch'])
                         ->whereNotNull('last_increment_date');

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('branch_id') && $request->branch_id != 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('month') && $request->month != 'Select Month') {
            $query->whereMonth('last_increment_date', date('m', strtotime($request->month)));
        }

        if ($request->filled('year') && $request->year != 'Select Year') {
            $query->whereYear('last_increment_date', $request->year);
        }

        $employees = $query->orderBy('last_increment_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        // Headers
        $headers = ['Employee', 'Branch', 'Previous Salary', 'Current Salary', 'Increment Amount', 'Increment %', 'Last Increment Date', 'Effective From'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($employees as $employee) {
            $sheet->setCellValue('A' . $row, $employee->user->first_name . ' ' . $employee->user->last_name);
            $sheet->setCellValue('B' . $row, $employee->branch ? $employee->branch->name : 'N/A');
            $sheet->setCellValue('C' . $row, $employee->previous_salary);
            $sheet->setCellValue('D' . $row, $employee->salary);
            $sheet->setCellValue('E' . $row, $employee->increment_amount);
            $sheet->setCellValue('F' . $row, number_format($employee->increment_percentage, 2));
            $sheet->setCellValue('G' . $row, date('d M Y', strtotime($employee->last_increment_date)));
            $sheet->setCellValue('H' . $row, $employee->increment_effective_date ? date('d M Y', strtotime($employee->increment_effective_date)) : 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'salary_increments_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary increments')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Employee::with(['user', 'branch'])
                         ->whereNotNull('last_increment_date');

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('branch_id') && $request->branch_id != 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('month') && $request->month != 'Select Month') {
            $query->whereMonth('last_increment_date', date('m', strtotime($request->month)));
        }

        if ($request->filled('year') && $request->year != 'Select Year') {
            $query->whereYear('last_increment_date', $request->year);
        }

        $employees = $query->orderBy('last_increment_date', 'desc')->get();

        $pdf = \PDF::loadView('erp.salary-increments.pdf', compact('employees'));
        return $pdf->download('salary_increments_' . date('Y-m-d_His') . '.pdf');
    }

    protected function getRestrictedBranchId()
    {
        $user = auth()->user();
        if ($user->hasRole('Super Admin')) {
            return null;
        }
        
        if ($user->branch_id) {
            return $user->branch_id;
        }
        
        return null;
    }
}
