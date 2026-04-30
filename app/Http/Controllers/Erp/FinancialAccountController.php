<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use App\Models\ChartOfAccount;
use App\Models\Branch;
use Illuminate\Http\Request;

class FinancialAccountController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();

        $query = FinancialAccount::with(['chartOfAccount', 'branch'])->orderBy('type');
        
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
            $branches = Branch::where('id', $restrictedBranchId)->orderBy('name')->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $accounts = $query->get();
        $chartAccounts = ChartOfAccount::where(function($query) {
            $query->whereHas('type', function($q) {
                $q->whereIn('name', ['Asset', 'Current Asset', 'Cash', 'Bank']);
            })->orWhere('name', 'like', '%Cash%')
              ->orWhere('name', 'like', '%Bank%')
              ->orWhere('name', 'like', '%Wallet%');
        })
        ->where('name', 'not like', '%Receivable%')
        ->orderBy('name')->get();
        
        // Fallback if the above filter returns nothing
        if ($chartAccounts->isEmpty()) {
            $chartAccounts = ChartOfAccount::orderBy('name')->get();
        }

        $accountTypes = FinancialAccount::getTypes();

        return view('erp.financialAccount.list', compact('accounts', 'chartAccounts', 'accountTypes', 'branches', 'restrictedBranchId'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'type'                 => 'required|in:cash,bank,mobile',
            'provider_name'        => 'required|string|max:255',
            'account_number'       => 'required|string|max:255',
            'account_holder_name'  => 'nullable|string|max:255',
            'currency'             => 'required|string|max:10',
            'account_id'           => 'nullable|exists:chart_of_accounts,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'branch_name'          => 'nullable|string|max:255',
            'swift_code'           => 'nullable|string|max:50',
            'mobile_number'        => 'nullable|string|max:20',
        ]);

        $data = $request->only([
            'branch_id', 'account_id', 'type', 'provider_name', 'account_number',
            'account_holder_name', 'currency', 'branch_name', 'swift_code', 'mobile_number'
        ]);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $data['branch_id'] = $restrictedBranchId;
        }

        FinancialAccount::create($data);

        return redirect()->route('financial-accounts.index')
            ->with('success', 'Financial account created successfully.');
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $account = FinancialAccount::findOrFail($id);

        $request->validate([
            'type'                 => 'required|in:cash,bank,mobile',
            'provider_name'        => 'required|string|max:255',
            'account_number'       => 'required|string|max:255',
            'account_holder_name'  => 'nullable|string|max:255',
            'currency'             => 'required|string|max:10',
            'account_id'           => 'nullable|exists:chart_of_accounts,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'branch_name'          => 'nullable|string|max:255',
            'swift_code'           => 'nullable|string|max:50',
            'mobile_number'        => 'nullable|string|max:20',
        ]);

        $data = $request->only([
            'branch_id', 'account_id', 'type', 'provider_name', 'account_number',
            'account_holder_name', 'currency', 'branch_name', 'swift_code', 'mobile_number'
        ]);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $data['branch_id'] = $restrictedBranchId;
        }

        $account->update($data);

        return redirect()->route('financial-accounts.index')
            ->with('success', 'Financial account updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('manage accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $account = FinancialAccount::findOrFail($id);
        $account->delete();

        return response()->json(['success' => true, 'message' => 'Account deleted successfully.']);
    }

    /**
     * API: return all accounts as JSON (used by dynamic dropdowns in forms)
     */
    public function getAll(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view accounts')) {
            abort(403, 'Unauthorized action.');
        }
        $type = $request->get('type');
        $query = FinancialAccount::orderBy('provider_name');
        
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        }

        if ($type) {
            $query->where('type', $type);
        }
        return response()->json($query->get());
    }
}
