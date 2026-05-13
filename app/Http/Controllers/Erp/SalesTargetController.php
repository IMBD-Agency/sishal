<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\SalesTarget;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SalesTarget::with(['employee.user', 'branch', 'creator']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        // Apply filters
        if ($request->filled('period_month') && $request->period_month != 'Select One') {
            $query->where('period_month', $request->period_month);
        }
        if ($request->filled('period_year') && $request->period_year != 'Select One') {
            $query->where('period_year', $request->period_year);
        }
        if ($request->filled('employee_id') && $request->employee_id != 'all') {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }

        $targets = $query->orderBy('period_year', 'desc')
                         ->orderBy('period_month', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        // Recalculate achievement for the current page targets to ensure fresh data
        $bonusService = new \App\Services\BonusCalculationService();
        foreach ($targets as $target) {
            $bonusService->calculateAchievementForEmployee(
                $target->employee_id,
                $target->period_month,
                $target->period_year
            );
        }

        // Get employees for filter (based on user's access)
        $employeesQuery = Employee::where('status', 'active')->with('user');
        if ($restrictedBranchId) {
            $employeesQuery->where('branch_id', $restrictedBranchId);
        }
        $employees = $employeesQuery->get();

        // Get branches for filter (only for Super Admin)
        $branches = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $branches = Branch::orderBy('name')->get();
        }

        return view('erp.sales-targets.index', compact('targets', 'employees', 'branches'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
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

        return view('erp.sales-targets.create', compact('employees', 'branches'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'target_amount' => 'required|numeric|min:0',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'period_month' => 'required_if:period_type,monthly',
            'period_year' => 'required|integer|min:2020|max:2030',
        ]);

        $employee = Employee::find($request->employee_id);

        SalesTarget::create([
            'employee_id' => $request->employee_id,
            'branch_id' => $employee->branch_id,
            'target_amount' => $request->target_amount,
            'achieved_amount' => 0,
            'bonus_percentage' => $request->bonus_percentage,
            'period_type' => $request->period_type,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'status' => 'active',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('sales-targets.index')->with('success', 'Sales target created successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $target = SalesTarget::with(['employee.user', 'branch', 'creator', 'salaryPayments'])
                             ->findOrFail($id);

        return view('erp.sales-targets.show', compact('target'));
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $target = SalesTarget::findOrFail($id);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $employees = Employee::with('user')->where('branch_id', $restrictedBranchId)->get();
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $employees = Employee::with('user')->get();
            $branches = Branch::all();
        }

        return view('erp.sales-targets.edit', compact('target', 'employees', 'branches'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'target_amount' => 'required|numeric|min:0',
            'bonus_percentage' => 'required|numeric|min:0|max:100',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'period_month' => 'required_if:period_type,monthly',
            'period_year' => 'required|integer|min:2020|max:2030',
            'status' => 'required|in:active,inactive,achieved,expired',
        ]);

        $target = SalesTarget::findOrFail($id);
        $target->update([
            'target_amount' => $request->target_amount,
            'bonus_percentage' => $request->bonus_percentage,
            'period_type' => $request->period_type,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('sales-targets.index')->with('success', 'Sales target updated successfully.');
    }

    public function updateAchievement(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'achieved_amount' => 'required|numeric|min:0',
        ]);

        $target = SalesTarget::findOrFail($id);
        $target->update([
            'achieved_amount' => $request->achieved_amount,
            'status' => $request->achieved_amount >= $target->target_amount ? 'achieved' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Achievement updated successfully.',
            'achievement_percentage' => $target->achievement_percentage,
            'is_achieved' => $target->is_achieved,
            'calculated_bonus' => $target->calculated_bonus,
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $target = SalesTarget::findOrFail($id);
        $target->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sales target deleted successfully.'
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SalesTarget::with(['employee.user', 'branch']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('period_month') && $request->period_month != 'Select One') {
            $query->where('period_month', $request->period_month);
        }
        if ($request->filled('period_year') && $request->period_year != 'Select One') {
            $query->where('period_year', $request->period_year);
        }
        if ($request->filled('employee_id') && $request->employee_id != 'all') {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $targets = $query->orderBy('period_year', 'desc')
                         ->orderBy('period_month', 'desc')
                         ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        // Headers
        $headers = ['ID', 'Employee', 'Branch', 'Period Month', 'Period Year', 'Target Amount', 'Achieved Amount', 'Achievement %', 'Bonus %', 'Status', 'Created Date'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($targets as $target) {
            $sheet->setCellValue('A' . $row, $target->id);
            $sheet->setCellValue('B' . $row, $target->employee->user->first_name . ' ' . $target->employee->user->last_name);
            $sheet->setCellValue('C' . $row, $target->branch ? $target->branch->name : 'N/A');
            $sheet->setCellValue('D' . $row, $target->period_month);
            $sheet->setCellValue('E' . $row, $target->period_year);
            $sheet->setCellValue('F' . $row, $target->target_amount);
            $sheet->setCellValue('G' . $row, $target->achieved_amount);
            $sheet->setCellValue('H' . $row, number_format($target->achievement_percentage, 2));
            $sheet->setCellValue('I' . $row, $target->bonus_percentage);
            $sheet->setCellValue('J' . $row, $target->status);
            $sheet->setCellValue('K' . $row, date('d M Y', strtotime($target->created_at)));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'sales_targets_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SalesTarget::with(['employee.user', 'branch']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('period_month') && $request->period_month != 'Select One') {
            $query->where('period_month', $request->period_month);
        }
        if ($request->filled('period_year') && $request->period_year != 'Select One') {
            $query->where('period_year', $request->period_year);
        }
        if ($request->filled('employee_id') && $request->employee_id != 'all') {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $targets = $query->orderBy('period_year', 'desc')
                         ->orderBy('period_month', 'desc')
                         ->get();

        $pdf = PDF::loadView('erp.sales-targets.pdf', compact('targets'));
        return $pdf->download('sales_targets_' . date('Y-m-d_His') . '.pdf');
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
