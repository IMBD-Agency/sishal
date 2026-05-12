<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\FinancialAccount;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view transfers')) {
            abort(403, 'Unauthorized action.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $user = auth()->user();

        $query = Transfer::with(['fromAccount', 'toAccount', 'creator']);

        // Apply branch restrictions - only show transfers involving user's branch accounts
        if ($restrictedBranchId) {
            $query->where(function($q) use ($restrictedBranchId) {
                $q->whereHas('fromAccount', function($fq) use ($restrictedBranchId) {
                    $fq->where('branch_id', $restrictedBranchId)
                       ->orWhereHas('warehouse', function($wq) use ($restrictedBranchId) {
                           $wq->whereHas('branches', function($bq) use ($restrictedBranchId) {
                               $bq->where('id', $restrictedBranchId);
                           });
                       });
                })->orWhereHas('toAccount', function($tq) use ($restrictedBranchId) {
                    $tq->where('branch_id', $restrictedBranchId)
                       ->orWhereHas('warehouse', function($wq) use ($restrictedBranchId) {
                           $wq->whereHas('branches', function($bq) use ($restrictedBranchId) {
                               $bq->where('id', $restrictedBranchId);
                           });
                       });
                });
            });
        } elseif ($user->warehouse_id) {
            $query->where(function($q) use ($user) {
                $q->whereHas('fromAccount', function($fq) use ($user) {
                    $fq->where('warehouse_id', $user->warehouse_id);
                })->orWhereHas('toAccount', function($tq) use ($user) {
                    $tq->where('warehouse_id', $user->warehouse_id);
                });
            });
        }

        // Date filters
        if ($request->filled('start_date')) {
            $query->whereDate('transfer_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transfer_date', '<=', $request->end_date);
        }

        // Account filters
        if ($request->filled('from_account_id')) {
            $query->where('from_financial_account_id', $request->from_account_id);
        }
        if ($request->filled('to_account_id')) {
            $query->where('to_financial_account_id', $request->to_account_id);
        }

        $transfers = $query->latest('transfer_date')->latest('id')->paginate(20)->appends($request->all());

        // Get accounts for filter dropdown - filtered by branch
        $accountsQuery = FinancialAccount::orderBy('type')->orderBy('provider_name');
        if ($restrictedBranchId) {
            $accountsQuery->where(function($q) use ($restrictedBranchId) {
                $q->where('branch_id', $restrictedBranchId)
                  ->orWhereHas('warehouse', function($wq) use ($restrictedBranchId) {
                      $wq->whereHas('branches', function($bq) use ($restrictedBranchId) {
                          $bq->where('id', $restrictedBranchId);
                      });
                  });
            });
        } elseif ($user->warehouse_id) {
            $accountsQuery->where('warehouse_id', $user->warehouse_id);
        }
        $accounts = $accountsQuery->get();
        
        // Calculate totals
        $totalTransfers = $transfers->sum('amount');

        return view('erp.transfers.index', compact('transfers', 'accounts', 'totalTransfers'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('create transfers')) {
            abort(403, 'Unauthorized action.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $user = auth()->user();

        // FROM ACCOUNTS: User's branch accounts only (source)
        $fromAccountsQuery = FinancialAccount::with(['branch', 'warehouse'])
            ->whereNotNull('branch_id') // Only branch accounts
            ->orderBy('type')
            ->orderBy('provider_name');

        if ($restrictedBranchId) {
            // Only show user's branch accounts
            $fromAccountsQuery->where('branch_id', $restrictedBranchId);
        } elseif (!$user->hasRole('SuperAdmin')) {
            // Non-super admin without branch restriction - show all branch accounts
            // or could restrict further based on requirements
        }

        $fromAccounts = $fromAccountsQuery->get();

        // TO ACCOUNTS: Main/Central accounts AND Warehouse accounts - destination for branch transfers
        // These include accounts where branch_id is NULL (admin-controlled central accounts)
        // AND accounts where warehouse_id is set (warehouse accounts)
        $toAccountsQuery = FinancialAccount::with(['branch', 'warehouse'])
            ->where(function($q) {
                $q->whereNull('branch_id') // Main/central accounts
                  ->orWhereNotNull('warehouse_id'); // OR warehouse accounts
            })
            ->orderBy('type')
            ->orderBy('provider_name');

        // Super admin sees all central and warehouse accounts
        // Branch users see all central and warehouse accounts too
        // because branches can send money to warehouse accounts

        $toAccounts = $toAccountsQuery->get();

        // Get branches and warehouses for location filter
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
            $branchWarehouseId = Branch::where('id', $restrictedBranchId)->value('warehouse_id');
            $warehouses = Warehouse::whereIn('id', array_filter([$branchWarehouseId]))->get();
        } else {
            $branches = Branch::orderBy('name')->get();
            $warehouses = Warehouse::orderBy('name')->get();
        }

        return view('erp.transfers.create', compact('fromAccounts', 'toAccounts', 'branches', 'warehouses'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('create transfers')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'from_financial_account_id' => 'required|exists:financial_accounts,id',
            'to_financial_account_id' => 'required|exists:financial_accounts,id|different:from_financial_account_id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'memo' => 'nullable|string|max:500',
        ]);

        $restrictedBranchId = $this->getRestrictedBranchId();
        $user = auth()->user();

        // Security check: Verify user has access to both accounts
        $fromAccount = FinancialAccount::find($request->from_financial_account_id);
        $toAccount = FinancialAccount::find($request->to_financial_account_id);

        // Verify accounts are of correct type
        // FROM must be a branch account (since branches send money to warehouse/center)
        if (!$fromAccount->branch_id) {
            abort(403, 'Source account must be a branch account.');
        }
        // TO must be a main/central account OR a warehouse account (not another branch account)
        if ($toAccount->branch_id && !$toAccount->warehouse_id) {
            abort(403, 'Destination account must be a main/central account or warehouse account (not another branch account).');
        }

        if ($restrictedBranchId) {
            // Branch user: Can only transfer FROM their own branch accounts
            // TO account can be any warehouse account (warehouse controls branches)
            if ($fromAccount->branch_id != $restrictedBranchId) {
                abort(403, 'You can only transfer from your own branch accounts.');
            }
        } elseif ($user->warehouse_id) {
            // Warehouse manager: Can only transfer between warehouse accounts
            if ($fromAccount->warehouse_id != $user->warehouse_id || $toAccount->warehouse_id != $user->warehouse_id) {
                abort(403, 'You can only transfer between accounts in your warehouse.');
            }
        }

        // Verify from account has sufficient balance
        if ($fromAccount->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in source account. Available: ' . number_format($fromAccount->balance, 2));
        }

        DB::beginTransaction();
        try {
            // Create transfer record
            $transfer = Transfer::create([
                'from_financial_account_id' => $request->from_financial_account_id,
                'to_financial_account_id' => $request->to_financial_account_id,
                'amount' => $request->amount,
                'transfer_date' => $request->transfer_date,
                'reference' => $request->reference,
                'memo' => $request->memo,
                'created_by' => Auth::id(),
            ]);

            // Update account balances
            $fromAccount->balance -= $request->amount;
            $fromAccount->save();

            $toAccount = FinancialAccount::find($request->to_financial_account_id);
            $toAccount->balance += $request->amount;
            $toAccount->save();

            // Create Journal Entry for accounting
            $this->createJournalEntry($transfer, $fromAccount, $toAccount);

            DB::commit();
            return redirect()->route('transfers.index')->with('success', 'Fund transfer completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    private function createJournalEntry($transfer, $fromAccount, $toAccount)
    {
        // Find chart of accounts
        $fromChartAccount = ChartOfAccount::find($fromAccount->account_id);
        $toChartAccount = ChartOfAccount::find($toAccount->account_id);

        if (!$fromChartAccount || !$toChartAccount) {
            return; // Skip journal if accounts not found
        }

        // Create Journal
        $voucherNo = 'TRF-' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT);
        while (Journal::where('voucher_no', $voucherNo)->exists()) {
            $voucherNo = 'TRF-' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT) . '-' . rand(10, 99);
        }

        $journal = Journal::create([
            'voucher_no' => $voucherNo,
            'entry_date' => $transfer->transfer_date,
            'branch_id' => $fromAccount->branch_id,
            'type' => 'Transfer',
            'description' => 'Fund Transfer: ' . $fromAccount->provider_name . ' to ' . $toAccount->provider_name,
            'voucher_amount' => $transfer->amount,
            'paid_amount' => $transfer->amount,
            'reference' => $transfer->reference,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // DEBIT: Destination Account (Money coming in)
        JournalEntry::create([
            'journal_id' => $journal->id,
            'chart_of_account_id' => $toChartAccount->id,
            'financial_account_id' => $toAccount->id,
            'debit' => $transfer->amount,
            'credit' => 0,
            'memo' => 'Received from: ' . $fromAccount->provider_name,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // CREDIT: Source Account (Money going out)
        JournalEntry::create([
            'journal_id' => $journal->id,
            'chart_of_account_id' => $fromChartAccount->id,
            'financial_account_id' => $fromAccount->id,
            'debit' => 0,
            'credit' => $transfer->amount,
            'memo' => 'Transferred to: ' . $toAccount->provider_name,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Update transfer with journal ID
        $transfer->journal_id = $journal->id;
        $transfer->save();
    }

    public function show(Transfer $transfer)
    {
        if (!auth()->user()->hasPermissionTo('view transfers')) {
            abort(403, 'Unauthorized action.');
        }

        return view('erp.transfers.show', compact('transfer'));
    }

    public function destroy(Transfer $transfer)
    {
        if (!auth()->user()->hasPermissionTo('delete transfers')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Reverse account balances
            $fromAccount = $transfer->fromAccount;
            $toAccount = $transfer->toAccount;

            $fromAccount->balance += $transfer->amount;
            $fromAccount->save();

            $toAccount->balance -= $transfer->amount;
            $toAccount->save();

            // Delete related journal entries and journal
            if ($transfer->journal) {
                $transfer->journal->entries()->delete();
                $transfer->journal->delete();
            }

            $transfer->delete();

            DB::commit();
            return redirect()->route('transfers.index')->with('success', 'Transfer deleted and amounts reversed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting transfer: ' . $e->getMessage());
        }
    }

    // API endpoint to get accounts by location (branch/warehouse)
    public function getAccountsByLocation(Request $request)
    {
        $query = FinancialAccount::query();
        $restrictedBranchId = $this->getRestrictedBranchId();
        $user = auth()->user();

        // Apply branch restrictions
        if ($restrictedBranchId) {
            $query->where(function($q) use ($restrictedBranchId) {
                $q->where('branch_id', $restrictedBranchId)
                  ->orWhereHas('warehouse', function($wq) use ($restrictedBranchId) {
                      $wq->whereHas('branches', function($bq) use ($restrictedBranchId) {
                          $bq->where('id', $restrictedBranchId);
                      });
                  });
            });
        } elseif ($user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        // Additional filters from request
        if ($request->filled('branch_id')) {
            // Only allow filtering by the user's branch if restricted
            if (!$restrictedBranchId || $request->branch_id == $restrictedBranchId) {
                $query->where('branch_id', $request->branch_id);
            }
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $accounts = $query->get()->map(function($account) {
            $location = '';
            if ($account->branch_id) {
                $location = $account->branch->name ?? 'Branch';
            } elseif ($account->warehouse_id) {
                $location = $account->warehouse->name ?? 'Warehouse';
            }

            return [
                'id' => $account->id,
                'name' => $account->provider_name . ' - ' . $account->account_number,
                'type' => $account->type,
                'balance' => $account->balance,
                'location' => $location,
            ];
        });

        return response()->json($accounts);
    }
}
