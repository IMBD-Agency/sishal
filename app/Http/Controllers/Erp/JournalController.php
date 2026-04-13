<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $query = Journal::with(['entries.chartOfAccount', 'creator']);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('entries', function($q2) use ($search) {
                      $q2->where('memo', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $journals = $query->latest()->get();
        $chartAccounts = ChartOfAccount::with('parent')->orderBy('name')->get();
        $financialAccounts = FinancialAccount::all();

        return view('erp.doubleEntry.journalaccount', compact('journals', 'chartAccounts', 'financialAccounts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'voucher_no' => 'required|string|unique:journals,voucher_no',
            'entry_date' => 'required|date',
            'type' => 'required|in:Journal,Payment,Receipt,Contra,Adjustment',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:chart_of_accounts,id',
            'entries.*.financial_account_id' => 'nullable|exists:financial_accounts,id',
            'entries.*.debit' => 'nullable|numeric|min:0',
            'entries.*.credit' => 'nullable|numeric|min:0',
        ]);

        // Validate debits == credits
        $totalDebit = collect($request->entries)->sum('debit');
        $totalCredit = collect($request->entries)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with('error', 'Debit (' . $totalDebit . ') and Credit (' . $totalCredit . ') totals must be equal.')->withInput();
        }

        try {
            DB::beginTransaction();

            $restrictedBranchId = $this->getRestrictedBranchId();

            $journal = Journal::create([
                'voucher_no' => $request->voucher_no,
                'entry_date' => $request->entry_date,
                'type' => $request->type,
                'description' => $request->description,
                'branch_id' => $restrictedBranchId,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($request->entries as $entry) {
                if (($entry['debit'] ?? 0) > 0 || ($entry['credit'] ?? 0) > 0) {
                    JournalEntry::create([
                        'journal_id' => $journal->id,
                        'chart_of_account_id' => $entry['account_id'],
                        'financial_account_id' => $entry['financial_account_id'] ?? null,
                        'debit' => $entry['debit'] ?? 0,
                        'credit' => $entry['credit'] ?? 0,
                        'memo' => $entry['memo'] ?? $request->description,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Journal Entry created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $journal = Journal::with(['entries.chartOfAccount', 'entries.financialAccount', 'creator'])->findOrFail($id);
        $chartAccounts = ChartOfAccount::orderBy('name')->get();
        $financialAccounts = FinancialAccount::all();
        
        return view('erp.doubleEntry.journaldetails', compact('journal', 'chartAccounts', 'financialAccounts'));
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $journal = Journal::findOrFail($id);
        $journal->delete(); // entries will be deleted by cascade

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Journal deleted successfully.']);
        }

        return redirect()->route('journal.list')->with('success', 'Journal deleted successfully.');
    }

    public function storeEntry(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'financial_account_id' => 'nullable|exists:financial_accounts,id',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'memo' => 'nullable|string',
        ]);

        JournalEntry::create([
            'journal_id' => $id,
            'chart_of_account_id' => $request->chart_of_account_id,
            'financial_account_id' => $request->financial_account_id,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'memo' => $request->memo,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Line entry added successfully.');
    }

    public function showEntry($id)
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $entry = JournalEntry::findOrFail($id);
        return response()->json(['entry' => $entry]);
    }

    public function updateEntry(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'financial_account_id' => 'nullable|exists:financial_accounts,id',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'memo' => 'nullable|string',
        ]);

        $entry = JournalEntry::findOrFail($id);
        $entry->update([
            'chart_of_account_id' => $request->chart_of_account_id,
            'financial_account_id' => $request->financial_account_id,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'memo' => $request->memo,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Line entry updated successfully.');
    }

    public function destroyEntry($id)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $entry = JournalEntry::findOrFail($id);
        $entry->delete();
        return response()->json(['success' => true]);
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }
        
        $query = Journal::with(['entries.chartOfAccount', 'creator']);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('entries', function($q2) use ($search) {
                      $q2->where('memo', 'like', "%{$search}%");
                  });
            });
        }

        $journals = $query->latest()->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Journal Report');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $headers = ['Voucher No', 'Date', 'Type', 'Description', 'Total Debit (Tk)', 'Total Credit (Tk)', 'Created By'];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $col++;
        }
        
        $row = 4;
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($journals as $journal) {
            $sheet->setCellValue('A' . $row, $journal->voucher_no);
            $sheet->setCellValue('B' . $row, $journal->entry_date->format('d M, Y'));
            $sheet->setCellValue('C' . $row, $journal->type);
            $sheet->setCellValue('D' . $row, $journal->description);
            $sheet->setCellValue('E' . $row, $journal->total_debit);
            $sheet->setCellValue('F' . $row, $journal->total_credit);
            $sheet->setCellValue('G' . $row, $journal->creator ? $journal->creator->first_name . ' ' . $journal->creator->last_name : 'System');
            $totalDebit += $journal->total_debit;
            $totalCredit += $journal->total_credit;
            $row++;
        }
        
        if (count($journals) > 0) {
            $sheet->setCellValue('D' . $row, 'Grand Total:');
            $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('f8f9fa');
            $sheet->getStyle('D' . $row)->getFont()->setBold(true);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue('E' . $row, $totalDebit);
            $sheet->getStyle('E' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('F' . $row, $totalCredit);
            $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        }
        
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'journals_report_' . date('Ymd_His') . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);
        $writer->save($filepath);
        
        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Journal::with(['entries.chartOfAccount', 'creator']);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('entry_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_date', '<=', $request->end_date);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('entries', function($q2) use ($search) {
                      $q2->where('memo', 'like', "%{$search}%");
                  });
            });
        }

        $journals = $query->latest()->get();
        $html = '<h1 style="text-align: center;">Journal Report</h1>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr style="background-color: #f2f2f2;"><th>Voucher No</th><th>Date</th><th>Type</th><th>Description</th><th>Total Debit</th><th>Total Credit</th></tr></thead>';
        $html .= '<tbody>';
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($journals as $journal) {
            $html .= '<tr>';
            $html .= '<td>' . $journal->voucher_no . '</td>';
            $html .= '<td>' . $journal->entry_date->format('d M, Y') . '</td>';
            $html .= '<td>' . ($journal->type ?? 'General') . '</td>';
            $html .= '<td>' . $journal->description . '</td>';
            $html .= '<td>' . number_format($journal->total_debit, 2) . '</td>';
            $html .= '<td>' . number_format($journal->total_credit, 2) . '</td>';
            $html .= '</tr>';
            $totalDebit += $journal->total_debit;
            $totalCredit += $journal->total_credit;
        }
        $html .= '</tbody>';
        if (count($journals) > 0) {
            $html .= '<tfoot>';
            $html .= '<tr style="font-weight: bold; background-color: #f8f9fa;">';
            $html .= '<td colspan="4" style="text-align: right;">Grand Total:</td>';
            $html .= '<td>' . number_format($totalDebit, 2) . '</td>';
            $html .= '<td>' . number_format($totalCredit, 2) . '</td>';
            $html .= '</tr>';
            $html .= '</tfoot>';
        }
        $html .= '</table>';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->download('journals_report_' . date('Ymd_His') . '.pdf');
    }
}
