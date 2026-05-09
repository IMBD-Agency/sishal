<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\Employee;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\SalaryPayment;
use App\Services\BonusCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryPaymentController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary')) {
            abort(403, 'Unauthorized action.');
        }

        $query = SalaryPayment::with(['employee.user', 'branch', 'chartOfAccount']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

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
        if ($request->filled('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }

        $payments = $query->orderBy('id', 'desc')->paginate(20)->appends($request->except('page'));
        
        $employeesQuery = Employee::with('user');
        if ($restrictedBranchId) {
            $employeesQuery->where('branch_id', $restrictedBranchId);
        }
        $employees = $employeesQuery->get();

        $branches = [];
        if (auth()->user()->hasRole('Super Admin')) {
            $branches = Branch::orderBy('name')->get();
        }

        $assetTypeIds = ChartOfAccountType::where('name', 'Asset')->pluck('id');
        $accounts = ChartOfAccount::whereIn('type_id', $assetTypeIds)
            ->orWhereHas('parent', function($q) use ($assetTypeIds) {
                $q->whereIn('type_id', $assetTypeIds);
            })->get();

        return view('erp.salary.index', compact('payments', 'employees', 'branches', 'accounts'));
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

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage salary')) {
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
        
        // Fetch Asset Accounts (Cash/Bank)
        $assetTypeIds = ChartOfAccountType::where('name', 'Asset')->pluck('id');
        $accounts = ChartOfAccount::whereIn('type_id', $assetTypeIds)
            ->orWhereHas('parent', function($q) use ($assetTypeIds) {
                $q->whereIn('type_id', $assetTypeIds);
            })->get();

        return view('erp.salary.create', compact('employees', 'branches', 'accounts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage salary')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required',
            'year' => 'required',
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $employee = Employee::find($request->employee_id);

        $salaryPayment = SalaryPayment::create([
            'employee_id' => $request->employee_id,
            'branch_id' => $employee->branch_id,
            'month' => $request->month,
            'year' => $request->year,
            'total_salary' => $request->total_salary ?? $employee->salary,
            'paid_amount' => $request->paid_amount,
            'bonus_amount' => $request->bonus_amount ?? 0,
            'is_bonus_editable' => $request->has('is_bonus_editable') ? $request->is_bonus_editable : true,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'account_id' => $request->account_id,
            'account_no' => $request->account_no,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        // Calculate and apply bonus if target exists
        $bonusService = new BonusCalculationService();
        $bonusService->applyBonusToSalaryPayment($salaryPayment->id, $request->bonus_amount, $request->has('is_bonus_editable'));

        // Create Journal Entry for Salary Payment
        try {
            // Find the Salary expense account (should be in Chart of Accounts)
            $salaryAccount = ChartOfAccount::where('name', 'like', '%Salary%')
                ->whereHas('type', fn($q) => $q->where('name', 'like', 'Expense%'))
                ->first();

            if (!$salaryAccount) {
                \Log::warning('Salary account not found in Chart of Accounts. Journal entry not created for salary payment ID: ' . $salaryPayment->id);
            } else {
                // Create Journal Header
                $journal = Journal::create([
                    'voucher_no' => 'SAL-' . date('Ymd', strtotime($request->payment_date)) . '-' . str_pad($salaryPayment->id, 4, '0', STR_PAD_LEFT),
                    'type' => 'Payment',
                    'entry_date' => $request->payment_date,
                    'description' => 'Salary payment for ' . $employee->user->first_name . ' - ' . $request->month . '/' . $request->year,
                    'branch_id' => $employee->branch_id,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                // Debit Entry: Salary Expense
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $salaryAccount->id,
                    'debit' => $request->paid_amount,
                    'credit' => 0,
                    'memo' => 'Salary expense - ' . $employee->user->first_name,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                // Credit Entry: Cash/Bank Account
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $request->account_id, // The payment account (Cash/Bank)
                    'debit' => 0,
                    'credit' => $request->paid_amount,
                    'memo' => 'Payment from ' . ChartOfAccount::find($request->account_id)->name,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                \Log::info('Journal entry created for salary payment ID: ' . $salaryPayment->id . ', Journal ID: ' . $journal->id);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating journal entry for salary payment: ' . $e->getMessage());
            // Don't fail the salary payment creation, just log the error
        }

        return redirect()->route('salary.index')->with('success', 'Salary payment recorded successfully.');
    }

    public function getSalaryDetails(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary')) {
            abort(403, 'Unauthorized action.');
        }
        $employeeId = $request->employee_id;
        $month = $request->month;
        $year = $request->year;

        $employee = Employee::find($employeeId);
        if (!$employee) return response()->json(['error' => 'Not found'], 404);

        $previousPaid = SalaryPayment::where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('paid_amount');

        // Calculate bonus if target exists
        $bonusService = new BonusCalculationService();
        $achievementData = $bonusService->calculateAchievementForEmployee($employeeId, $month, $year);

        return response()->json([
            'salary' => $employee->salary,
            'previous_paid' => $previousPaid,
            'due' => $employee->salary - $previousPaid,
            'bonus_data' => $achievementData,
            'has_bonus' => $achievementData['bonus_amount'] > 0,
            'bonus_amount' => $achievementData['bonus_amount'],
        ]);
    }

    public function calculateBonus(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage salary')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required',
            'year' => 'required',
        ]);

        $bonusService = new BonusCalculationService();
        $achievementData = $bonusService->calculateAchievementForEmployee(
            $request->employee_id,
            $request->month,
            $request->year
        );

        return response()->json([
            'success' => true,
            'achievement_data' => $achievementData,
        ]);
    }

    public function updateBonus(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage salary')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'bonus_amount' => 'required|numeric|min:0',
        ]);

        $salaryPayment = SalaryPayment::findOrFail($id);
        if (!$salaryPayment->is_bonus_editable) {
            return response()->json([
                'success' => false,
                'message' => 'Bonus is not editable for this payment.'
            ], 403);
        }

        $salaryPayment->update([
            'bonus_amount' => $request->bonus_amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bonus updated successfully.',
            'new_bonus' => $request->bonus_amount,
            'new_total' => $salaryPayment->total_payment,
        ]);
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view salary')) {
            abort(403, 'Unauthorized action.');
        }
        $payment = SalaryPayment::with(['employee.user', 'branch', 'chartOfAccount', 'creator'])
            ->findOrFail($id);

        return view('erp.salary.show', compact('payment'));
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('manage salary')) {
            abort(403, 'Unauthorized action.');
        }
        $payment = SalaryPayment::findOrFail($id);

        // Delete related journal entries
        $voucherNo = 'SAL-' . date('Ymd', strtotime($payment->payment_date)) . '-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT);
        $journal = Journal::where('voucher_no', $voucherNo)->first();

        if ($journal) {
            // Delete journal entries
            JournalEntry::where('journal_id', $journal->id)->delete();
            // Delete journal header
            $journal->delete();
        }

        // Delete salary payment
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Salary payment deleted successfully.'
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary')) {
            abort(403, 'Unauthorized action.');
        }
        $query = SalaryPayment::with(['employee.user', 'branch', 'chartOfAccount']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

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

        $payments = $query->orderBy('id', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        // Headers
        $headers = ['ID', 'Employee', 'Branch', 'Month', 'Year', 'Total Salary', 'Paid Amount', 'Payment Date', 'Payment Method', 'Account', 'Note'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // Data
        $row = 2;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment->id);
            $sheet->setCellValue('B' . $row, $payment->employee->user->first_name . ' ' . $payment->employee->user->last_name);
            $sheet->setCellValue('C' . $row, $payment->branch->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $payment->month);
            $sheet->setCellValue('E' . $row, $payment->year);
            $sheet->setCellValue('F' . $row, number_format($payment->total_salary, 2));
            $sheet->setCellValue('G' . $row, number_format($payment->paid_amount, 2));
            $sheet->setCellValue('H' . $row, date('d M Y', strtotime($payment->payment_date)));
            $sheet->setCellValue('I' . $row, $payment->payment_method ?? 'N/A');
            $sheet->setCellValue('J' . $row, $payment->chartOfAccount->name ?? 'N/A');
            $sheet->setCellValue('K' . $row, $payment->note ?? '');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'salary_payments_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view salary')) {
            abort(403, 'Unauthorized action.');
        }
        $query = SalaryPayment::with(['employee.user', 'branch', 'chartOfAccount']);
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

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

        $payments = $query->orderBy('id', 'desc')->get();

        $pdf = \PDF::loadView('erp.salary.pdf', compact('payments'));
        return $pdf->download('salary_payments_' . date('Y-m-d_His') . '.pdf');
    }
}
