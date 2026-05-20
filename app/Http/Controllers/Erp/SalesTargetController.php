<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\SalesTarget;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SalesTarget::with(['branch', 'creator']);
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

        // Recalculate achievement for the current page branch targets to ensure fresh data
        $bonusService = new \App\Services\BonusCalculationService();
        foreach ($targets as $target) {
            $bonusService->calculateAchievementForBranch(
                $target->branch_id,
                $target->period_month,
                $target->period_year
            );
        }

        // Fetch all active employees for potential display/referencing
        $employeesQuery = Employee::where('status', 'active')->with('user');
        if ($restrictedBranchId) {
            $employeesQuery->where('branch_id', $restrictedBranchId);
        }
        $employees = $employeesQuery->get();

        // Get branches (filtered if restricted)
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
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
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $branches = Branch::all();
        }

        return view('erp.sales-targets.create', compact('branches'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'target_quantity' => 'required|numeric|min:0',
            'incentive_amount' => 'required|numeric|min:0',
            'commission_per_extra_sale' => 'required|numeric|min:0',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'period_month' => 'required_if:period_type,monthly',
            'period_year' => 'required|integer|min:2020|max:2030',
        ]);

        // Prevent setting duplicate targets for the same branch and period
        $exists = SalesTarget::where('branch_id', $request->branch_id)
                             ->where('period_month', $request->period_month)
                             ->where('period_year', $request->period_year)
                             ->whereIn('status', ['active', 'achieved'])
                             ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['branch_id' => 'A sales target has already been set for this branch and period.'])
                ->withInput();
        }

        SalesTarget::create([
            'branch_id' => $request->branch_id,
            'target_quantity' => $request->target_quantity,
            'incentive_amount' => $request->incentive_amount,
            'commission_per_extra_sale' => $request->commission_per_extra_sale,
            'achieved_quantity' => 0,
            'achieved_incentive' => 0,
            'achieved_extra_commission' => 0,
            'total_achieved_bonus' => 0,
            'period_type' => $request->period_type,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'status' => 'active',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('sales-targets.index')->with('success', 'Branch sales target created successfully.');
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $target = SalesTarget::with(['branch', 'creator', 'salaryPayments'])
                             ->findOrFail($id);

        // Fetch active employees of this branch to show how the bonus is split percentage-wise
        $branchEmployees = Employee::where('branch_id', $target->branch_id)
                                   ->where('status', 'active')
                                   ->with('user')
                                   ->get();

        $totalBranchSalary = $branchEmployees->sum('salary');

        return view('erp.sales-targets.show', compact('target', 'branchEmployees', 'totalBranchSalary'));
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $target = SalesTarget::findOrFail($id);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
        } else {
            $branches = Branch::all();
        }

        return view('erp.sales-targets.edit', compact('target', 'branches'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'target_quantity' => 'required|numeric|min:0',
            'incentive_amount' => 'required|numeric|min:0',
            'commission_per_extra_sale' => 'required|numeric|min:0',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'period_month' => 'required_if:period_type,monthly',
            'period_year' => 'required|integer|min:2020|max:2030',
            'status' => 'required|in:active,inactive,achieved,expired',
        ]);

        $target = SalesTarget::findOrFail($id);

        // Prevent duplicates if branch, month, or year changes
        if ($target->branch_id != $request->branch_id || $target->period_month != $request->period_month || $target->period_year != $request->period_year) {
            $exists = SalesTarget::where('branch_id', $request->branch_id)
                                 ->where('period_month', $request->period_month)
                                 ->where('period_year', $request->period_year)
                                 ->where('id', '!=', $id)
                                 ->whereIn('status', ['active', 'achieved'])
                                 ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withErrors(['branch_id' => 'A sales target has already been set for this branch and period.'])
                    ->withInput();
            }
        }

        $target->update([
            'branch_id' => $request->branch_id,
            'target_quantity' => $request->target_quantity,
            'incentive_amount' => $request->incentive_amount,
            'commission_per_extra_sale' => $request->commission_per_extra_sale,
            'period_type' => $request->period_type,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Trigger recalculation immediately
        $bonusService = new \App\Services\BonusCalculationService();
        $bonusService->calculateAchievementForBranch($target->branch_id, $target->period_month, $target->period_year);

        return redirect()->route('sales-targets.index')->with('success', 'Branch sales target updated successfully.');
    }

    public function updateAchievement(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage sales targets')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'achieved_quantity' => 'required|numeric|min:0',
        ]);

        $target = SalesTarget::findOrFail($id);
        $bonusService = new \App\Services\BonusCalculationService();
        $result = $bonusService->updateSalesTargetAchievement($target->id, $request->achieved_quantity);

        return response()->json([
            'success' => true,
            'message' => 'Achievement updated successfully.',
            'achievement_percentage' => $result['achievement_percentage'],
            'is_achieved' => $target->fresh()->is_achieved,
            'calculated_bonus' => $result['bonus_amount'],
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

        $query = SalesTarget::with(['branch']);
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
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
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
        $headers = ['ID', 'Branch', 'Period Month', 'Period Year', 'Target Quantity', 'Achieved Quantity', 'Achievement %', 'Incentive (৳)', 'Commission / Extra Sale (৳)', 'Branch Bonus (৳)', 'Status', 'Created Date'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($targets as $target) {
            $sheet->setCellValue('A' . $row, $target->id);
            $sheet->setCellValue('B' . $row, $target->branch ? $target->branch->name : 'N/A');
            $sheet->setCellValue('C' . $row, $target->period_month);
            $sheet->setCellValue('D' . $row, $target->period_year);
            $sheet->setCellValue('E' . $row, $target->target_quantity);
            $sheet->setCellValue('F' . $row, $target->achieved_quantity);
            $sheet->setCellValue('G' . $row, number_format($target->achievement_percentage, 2));
            $sheet->setCellValue('H' . $row, $target->incentive_amount);
            $sheet->setCellValue('I' . $row, $target->commission_per_extra_sale);
            $sheet->setCellValue('J' . $row, $target->total_achieved_bonus);
            $sheet->setCellValue('K' . $row, $target->status);
            $sheet->setCellValue('L' . $row, date('d M Y', strtotime($target->created_at)));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'branch_sales_targets_' . date('Y-m-d_His') . '.xlsx';

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

        $query = SalesTarget::with(['branch']);
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
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }

        $targets = $query->orderBy('period_year', 'desc')
                          ->orderBy('period_month', 'desc')
                          ->get();

        $pdf = Pdf::loadView('erp.sales-targets.pdf', compact('targets'));
        return $pdf->download('branch_sales_targets_' . date('Y-m-d_His') . '.pdf');
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
