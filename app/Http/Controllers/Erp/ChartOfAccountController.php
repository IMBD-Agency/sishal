<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountParent;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        // Only show singular account types (hide duplicates like Assets, Liabilities, etc.)
        $singularTypes = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'];
        $accountTypes = ChartOfAccountType::with('subTypes')
            ->whereIn('name', $singularTypes)
            ->get()
            ->unique('name')  // Remove duplicate names after fetching
            ->values();  // Reset array keys
            
        $accountParents = ChartOfAccountParent::with(['type', 'subType', 'accounts'])->get();
        $chartOfAccounts = ChartOfAccount::with(['parent', 'type', 'subType', 'createdBy'])->get();

        return view('erp.doubleEntry.chartofaccount', compact('accountTypes', 'accountParents', 'chartOfAccounts'));
    }

    public function storeType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        ChartOfAccountType::create($request->all());
        return back()->with('success', 'Account Type created successfully.');
    }

    public function getSubTypes($id)
    {
        $subTypes = ChartOfAccountSubType::where('type_id', $id)->get();
        return response()->json($subTypes);
    }

    public function getNextCode($typeId)
    {
        $type = AccountType::find($typeId);
        $lastAccount = ChartOfAccount::where('type_id', $typeId)->orderBy('code', 'desc')->first();
        
        if ($lastAccount && is_numeric($lastAccount->code)) {
            $nextCode = (int)$lastAccount->code + 1;
        } else {
            // Default starting points based on common accounting standards
            $name = strtolower($type->name ?? '');
            if (str_contains($name, 'asset')) $nextCode = 1001;
            elseif (str_contains($name, 'liabitity')) $nextCode = 2001;
            elseif (str_contains($name, 'equity')) $nextCode = 3001;
            elseif (str_contains($name, 'income') || str_contains($name, 'revenue')) $nextCode = 4001;
            elseif (str_contains($name, 'expense')) $nextCode = 5001;
            else $nextCode = rand(1000, 9999);
        }

        return response()->json(['success' => true, 'code' => $nextCode]);
    }

    public function storeParent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:chart_of_account_types,id',
            'sub_type_id' => 'required|exists:chart_of_account_sub_types,id',
            'code' => 'required|string|unique:chart_of_account_parents,code',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        ChartOfAccountParent::create($data);
        return back()->with('success', 'Parent Account created successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'required|exists:chart_of_account_parents,id',
            'type_id' => 'required|exists:chart_of_account_types,id',
            'sub_type_id' => 'required|exists:chart_of_account_sub_types,id',
            'code' => 'required|string|unique:chart_of_accounts,code',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $account = ChartOfAccount::create($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true, 
                'data' => $account,
                'message' => 'Account created successfully'
            ]);
        }

        return back()->with('success', 'Chart of Account created successfully.');
    }

    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:chart_of_accounts,code,' . $id,
        ]);

        $account->update($request->all());
        return back()->with('success', 'Account updated successfully.');
    }

    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        
        // DEVELOPMENT MODE: Force delete logic
        // 1. Identify all Journals (Transactions) that involve this account
        $relatedJournalIds = \App\Models\JournalEntry::where('chart_of_account_id', $id)
            ->pluck('journal_id')
            ->unique();
            
        if ($relatedJournalIds->count() > 0) {
            // 2. Delete ALL entries belonging to these transactions (to prevent unbalanced books)
            \App\Models\JournalEntry::whereIn('journal_id', $relatedJournalIds)->delete();
            
            // 3. Delete the Journal headers
            \App\Models\Journal::whereIn('id', $relatedJournalIds)->delete();
        }

        $account->delete();
        return response()->json(['success' => true, 'message' => 'Account and ' . $relatedJournalIds->count() . ' related transactions deleted successfully.']);
    }

    public function updateParent(Request $request, $id)
    {
        $parent = ChartOfAccountParent::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:chart_of_account_parents,code,' . $id,
        ]);

        $parent->update($request->all());
        return back()->with('success', 'Parent Account updated successfully.');
    }

    public function destroyParent($id)
    {
        $parent = ChartOfAccountParent::findOrFail($id);
        if ($parent->accounts()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete parent account with existing sub-accounts.']);
        }
        $parent->delete();
        return response()->json(['success' => true, 'message' => 'Parent Account deleted successfully.']);
    }
}
