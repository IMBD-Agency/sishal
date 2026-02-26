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
}
