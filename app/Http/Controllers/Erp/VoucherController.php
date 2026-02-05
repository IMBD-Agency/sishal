<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        $now = Carbon::now();

        if ($reportType == 'monthly') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfMonth() : $now->copy()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfMonth() : $now->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfYear() : $now->copy()->startOfYear();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfYear() : $now->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : $now->copy()->startOfDay();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : $now->copy()->endOfDay();
        }

        $query = Journal::with(['branch', 'customer', 'supplier', 'expenseAccount', 'creator'])
            ->whereBetween('entry_date', [$startDate, $endDate]);

        if ($request->filled('customer_id') && $request->customer_id != 'all') {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('supplier_id') && $request->supplier_id != 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('voucher_type') && $request->voucher_type != 'all') {
            $query->where('type', $request->voucher_type);
        }
        if ($request->filled('account_id') && $request->account_id != 'all') {
            $query->where('expense_account_id', $request->account_id);
        }

        // Calculate Totals for all filtered results (not just current page)
        $totals = (clone $query)->selectRaw('SUM(voucher_amount) as total_voucher, SUM(paid_amount) as total_paid')->first();
        $vouchers = $query->latest()->paginate(50)->withQueryString();

        $customers = Customer::orderBy('name')->take(200)->get();
        $suppliers = Supplier::orderBy('name')->take(200)->get();
        $expenseAccounts = ChartOfAccount::whereHas('type', function($q) {
            $q->whereIn('name', ['Expense', 'Expenses', 'Income', 'Revenue']);
        })->take(200)->get();

        return view('erp.vouchers.index', compact('vouchers', 'startDate', 'endDate', 'customers', 'suppliers', 'expenseAccounts', 'reportType', 'totals'));
    }

    public function create()
    {
        $voucherNo = 'V-' . date('Ymd') . '-' . str_pad(Journal::count() + 1, 4, '0', STR_PAD_LEFT);
        
        // Fetch Expense/Revenue Accounts
    $expenseTypeIds = ChartOfAccountType::whereIn('name', ['Expense', 'Expenses', 'Income', 'Revenue'])->pluck('id');
    
    $expenseAccounts = ChartOfAccount::whereIn('type_id', $expenseTypeIds)
        ->orWhereHas('parent', function($q) use ($expenseTypeIds) {
            $q->whereIn('type_id', $expenseTypeIds);
        })->get();
    
    // Fetch Asset Accounts (Cash/Bank)
    $assetTypeIds = ChartOfAccountType::whereIn('name', ['Asset', 'Assets'])->pluck('id');
    
    $paymentAccounts = ChartOfAccount::whereIn('type_id', $assetTypeIds)
        ->orWhereHas('parent', function($q) use ($assetTypeIds) {
            $q->whereIn('type_id', $assetTypeIds);
        })->get();

    // Fallback: If still empty, try more aggressive names
    if($paymentAccounts->isEmpty()) {
        $paymentAccounts = ChartOfAccount::where('name', 'like', '%Cash%')
            ->orWhere('name', 'like', '%Bank%')
            ->orWhere('name', 'like', '%Wallet%')
            ->get();
    }

        $expenseTypeId = ChartOfAccountType::whereIn('name', ['Expense', 'Expenses'])->first()->id ?? 15;

        $branches = Branch::all();
        $customers = Customer::all();
        $suppliers = Supplier::all();
        
        // Pass account types for the modal
        $accountTypes = \App\Models\ChartOfAccountType::all();
        $parentAccounts = \App\Models\ChartOfAccountParent::all();
        $subTypes = \App\Models\ChartOfAccountSubType::all();
        
        // Map Type ID -> First Parent ID (for Simplified UX)
        $defaultParents = $parentAccounts->groupBy('type_id')->map(function($group) {
            return $group->first()->id;
        });

        return view('erp.vouchers.create', compact('voucherNo', 'expenseAccounts', 'paymentAccounts', 'branches', 'customers', 'suppliers', 'expenseTypeId', 'accountTypes', 'parentAccounts', 'subTypes', 'defaultParents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'voucher_no' => 'required|unique:journals,voucher_no',
            'expense_account_id' => 'required|exists:chart_of_accounts,id',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'particulars' => 'required|array',
            'amounts' => 'required|array',
            'amounts.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = array_sum($request->amounts);

            $journal = Journal::create([
                'voucher_no' => $request->voucher_no,
                'type' => $request->voucher_type ?? 'Payment',
                'entry_date' => $request->entry_date,
                'description' => $request->note,
                'branch_id' => $request->branch_id,
                'customer_id' => $request->customer_id,
                'supplier_id' => $request->supplier_id,
                'expense_account_id' => $request->expense_account_id,
                'voucher_amount' => $totalAmount,
                'paid_amount' => $totalAmount, // Assuming full payment for now as per UI
                'reference' => $request->reference,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Handle Entries based on Voucher Type
            if ($request->voucher_type == 'Receipt') {
                // RECEIPTS: Money coming IN
                // Debit: Cash/Bank (Asset)
                // Credit: Revenue (Income)

                // 1. Credit Entry (Revenue/Income) - Multiple Lines
                foreach ($request->particulars as $index => $part) {
                    if ($request->amounts[$index] > 0) {
                        JournalEntry::create([
                            'journal_id' => $journal->id,
                            'chart_of_account_id' => $request->expense_account_id, // Revenue Account
                            'debit' => 0,
                            'credit' => $request->amounts[$index],
                            'memo' => $part,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                // 2. Debit Entry (Cash/Bank) - Total Amount
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $request->account_id,
                    'debit' => $totalAmount,
                    'credit' => 0,
                    'memo' => 'Receipt from ' . ($journal->expenseAccount->name ?? 'Voucher'),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

            } else {
                // PAYMENTS (Default): Money going OUT
                // Debit: Expense
                // Credit: Cash/Bank (Asset)

                // 1. Debit Entry (Expense) - Multiple Lines
                foreach ($request->particulars as $index => $part) {
                    if ($request->amounts[$index] > 0) {
                        JournalEntry::create([
                            'journal_id' => $journal->id,
                            'chart_of_account_id' => $request->expense_account_id,
                            'debit' => $request->amounts[$index],
                            'credit' => 0,
                            'memo' => $part,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                // 2. Credit Entry (Cash/Bank) - Total Amount
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $request->account_id,
                    'debit' => 0,
                    'credit' => $totalAmount,
                    'memo' => 'Payment for ' . ($journal->expenseAccount->name ?? 'Voucher'),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            DB::commit();
            return redirect()->route('vouchers.index')->with('success', 'Voucher created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $journal = Journal::findOrFail($id);
            // Delete associated entries first (though database cascade should handle it)
            $journal->entries()->delete();
            $journal->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Voucher deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
