<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Journal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoubleEntryReportController extends Controller
{
    public function ledgerIndex(Request $request)
    {
        $chartAccounts = ChartOfAccount::orderBy('name')->get();
        
        $query = JournalEntry::with(['journal', 'chartOfAccount']);

        // Date Filtering
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        
        $query->whereHas('journal', function($q) use ($startDate, $endDate) {
            $q->whereBetween('entry_date', [$startDate, $endDate]);
        });

        // Account Filtering
        if ($request->filled('account_id')) {
            $query->where('chart_of_account_id', $request->account_id);
        }

        // Account Type Filtering
        if ($request->filled('account_type')) {
            $query->whereHas('chartOfAccount.type', function($q) use ($request) {
                $q->where('name', $request->account_type);
            });
        }

        // Search Support
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('journal', function($jq) use ($search) {
                    $jq->where('voucher_no', 'LIKE', "%{$search}%")
                       ->orWhere('description', 'LIKE', "%{$search}%");
                })->orWhereHas('chartOfAccount', function($cq) use ($search) {
                    $cq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('code', 'LIKE', "%{$search}%");
                });
            });
        }

        // Export Logic (Basic CSV for Excel)
        if ($request->export == 'excel') {
            $fileName = 'Ledger_Summary_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $entries = $query->get();
            $callback = function() use ($entries) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date', 'Voucher No', 'Account Name', 'Code', 'Description', 'Debit', 'Credit', 'Balance']);

                foreach ($entries as $entry) {
                    fputcsv($file, [
                        $entry->journal->entry_date->format('Y-m-d'),
                        $entry->journal->voucher_no,
                        $entry->chartOfAccount->name,
                        $entry->chartOfAccount->code,
                        $entry->journal->description,
                        $entry->debit,
                        $entry->credit,
                        $entry->debit - $entry->credit
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Clone for summary BEFORE applying latest() order to avoid MySQL aggregate error
        $summaryQuery = (clone $query);
        $ledgerEntries = $query->latest()->paginate(50)->withQueryString();
        
        $summary = $summaryQuery->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit, COUNT(*) as total_entries')->first();
        $totalDebits = $summary->total_debit ?? 0;
        $totalCredits = $summary->total_credit ?? 0;
        $totalEntries = $summary->total_entries ?? 0;

        return view('erp.doubleEntry.ledgersummery', compact(
            'chartAccounts', 'ledgerEntries', 'totalDebits', 'totalCredits', 
            'totalEntries', 'startDate', 'endDate'
        ));
    }

    public function ledgerAccount(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // Calculate Opening Balance
        $openingDebit = JournalEntry::where('chart_of_account_id', $id)
            ->whereHas('journal', function($q) use ($startDate) {
                $q->where('entry_date', '<', $startDate);
            })->sum('debit');
            
        $openingCredit = JournalEntry::where('chart_of_account_id', $id)
            ->whereHas('journal', function($q) use ($startDate) {
                $q->where('entry_date', '<', $startDate);
            })->sum('credit');

        $openingBalance = $openingDebit - $openingCredit;

        // Transactions - Ordered in DB
        $entries = JournalEntry::with('journal')
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->select('journal_entries.*')
            ->where('chart_of_account_id', $id)
            ->whereBetween('journals.entry_date', [$startDate, $endDate])
            ->orderBy('journals.entry_date', 'asc')
            ->get();

        $totalDebits = $entries->sum('debit');
        $totalCredits = $entries->sum('credit');

        // Calculate running balance
        $currentBalance = $openingBalance;
        foreach ($entries as $entry) {
            $currentBalance += ($entry->debit - $entry->credit);
            $entry->running_balance = $currentBalance;
        }

        return view('erp.doubleEntry.accountLedger', compact(
            'account', 'entries', 'openingBalance', 'startDate', 'endDate', 
            'totalDebits', 'totalCredits'
        ));
    }

    public function trialBalance(Request $request)
    {
        $endDate = $request->filled('end_date') ? $request->end_date : date('Y-m-d');

        $accounts = ChartOfAccount::whereHas('entries', function($q) use ($endDate) {
            $q->whereHas('journal', function($jq) use ($endDate) {
                $jq->where('entry_date', '<=', $endDate);
            });
        })
        ->withSum(['entries as total_debit' => function($q) use ($endDate) {
            $q->whereHas('journal', function($jq) use ($endDate) {
                $jq->where('entry_date', '<=', $endDate);
            });
        }], 'debit')
        ->withSum(['entries as total_credit' => function($q) use ($endDate) {
            $q->whereHas('journal', function($jq) use ($endDate) {
                $jq->where('entry_date', '<=', $endDate);
            });
        }], 'credit')
        ->with(['type', 'parent.type'])
        ->get()
        ->map(function($account) {
            $debit = $account->total_debit ?? 0;
            $credit = $account->total_credit ?? 0;
            $typeName = $account->type ? $account->type->name : ($account->parent && $account->parent->type ? $account->parent->type->name : 'Uncategorized');

            return [
                'code' => $account->code,
                'name' => $account->name,
                'type_name' => $typeName,
                'total_debit' => $debit,
                'total_credit' => $credit,
                'net_balance' => $debit - $credit
            ];
        })
        ->values();

        $totalDebit = $accounts->sum('total_debit');
        $totalCredit = $accounts->sum('total_credit');

        // Pass simple array structure to view
        return view('erp.doubleEntry.trialbalance', compact('accounts', 'endDate', 'totalDebit', 'totalCredit'));
    }

    public function balanceSheet(Request $request)
    {
        $endDate = $request->filled('end_date') ? $request->end_date : date('Y-m-d');
        
        $assetTypes = \App\Models\ChartOfAccountType::where('name', 'Asset')->pluck('id');
        $liabilityTypes = \App\Models\ChartOfAccountType::where('name', 'Liability')->pluck('id');
        $equityTypes = \App\Models\ChartOfAccountType::where('name', 'Equity')->pluck('id');

        $getAccounts = function($typeIds, $category) use ($endDate) {
            if ($typeIds->isEmpty()) return collect();
            
            return ChartOfAccount::where(function($q) use ($typeIds) {
                    $q->whereIn('type_id', $typeIds)
                      ->orWhereHas('parent', function($pq) use ($typeIds) {
                          $pq->whereIn('type_id', $typeIds);
                      });
                })
                ->withSum(['entries as total_debit' => function($q) use ($endDate) {
                    $q->whereHas('journal', function($jq) use ($endDate) {
                        $jq->where('entry_date', '<=', $endDate);
                    });
                }], 'debit')
                ->withSum(['entries as total_credit' => function($q) use ($endDate) {
                    $q->whereHas('journal', function($jq) use ($endDate) {
                        $jq->where('entry_date', '<=', $endDate);
                    });
                }], 'credit')
                ->get()->map(function($acc) use ($category) {
                    $debit = $acc->total_debit ?? 0;
                    $credit = $acc->total_credit ?? 0;
                    // Assets: Debit - Credit
                    // Liabilities & Equity: Credit - Debit
                    $balance = ($category === 'Assets') ? ($debit - $credit) : ($credit - $debit);
                    
                    return [
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'balance' => $balance,
                        'formatted_balance' => number_format(abs($balance), 2)
                    ];
                })->filter(fn($acc) => $acc['balance'] != 0)->values();
        };

        $assets = $getAccounts($assetTypes, 'Assets');
        $liabilities = $getAccounts($liabilityTypes, 'Liabilities');
        $equities = $getAccounts($equityTypes, 'Equity');
        
        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equities->sum('balance');

        return view('erp.doubleEntry.balancesheet', compact('assets', 'liabilities', 'equities', 'endDate', 'totalAssets', 'totalLiabilities', 'totalEquity'));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : date('Y-m-01');
        $endDate = $request->filled('end_date') ? $request->end_date : date('Y-m-d');
        
        // Fetch ALL relevant types to handle duplicates (e.g., "Revenue" AND "Income")
        $revenueTypes = \App\Models\ChartOfAccountType::where('name', 'Revenue')->pluck('id');
        $expenseTypes = \App\Models\ChartOfAccountType::where('name', 'Expense')->pluck('id');

        $getAccounts = function($typeIds, $category) use ($startDate, $endDate) {
            if ($typeIds->isEmpty()) return collect();
            
            $isRevenue = in_array($category, ['Revenue', 'Income']);

            return ChartOfAccount::where(function($q) use ($typeIds) {
                    $q->whereIn('type_id', $typeIds)
                      ->orWhereHas('parent', function($pq) use ($typeIds) {
                          $pq->whereIn('type_id', $typeIds);
                      });
                })
                ->withSum(['entries as total_debit' => function($q) use ($startDate, $endDate) {
                    $q->whereHas('journal', function($jq) use ($startDate, $endDate) {
                        $jq->whereBetween('entry_date', [$startDate, $endDate]);
                    });
                }], 'debit')
                ->withSum(['entries as total_credit' => function($q) use ($startDate, $endDate) {
                    $q->whereHas('journal', function($jq) use ($startDate, $endDate) {
                        $jq->whereBetween('entry_date', [$startDate, $endDate]);
                    });
                }], 'credit')
                ->get()
                ->map(function($acc) use ($isRevenue) {
                    $debit = $acc->total_debit ?? 0;
                    $credit = $acc->total_credit ?? 0;
                    
                    $balance = $isRevenue ? ($credit - $debit) : ($debit - $credit);
                    
                    return [
                        'code' => $acc->code,
                        'name' => $acc->name, 
                        'balance' => $balance,
                        'formatted_balance' => number_format(abs($balance), 2)
                    ];
                })
                ->filter(fn($acc) => $acc['balance'] != 0)
                ->values();
        };

        $revenues = $getAccounts($revenueTypes, 'Revenue');
        $expenses = $getAccounts($expenseTypes, 'Expenses');
        
        $totalRevenue = $revenues->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netProfit = $totalRevenue - $totalExpenses;
        // Avoid division by zero
        $profitPercentage = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        $profitLossData = [
            'revenue' => $revenues,
            'expenses' => $expenses,
            'totals' => [
                'revenue' => $totalRevenue,
                'revenue_formatted' => number_format($totalRevenue, 2),
                'expenses' => $totalExpenses,
                'expenses_formatted' => number_format($totalExpenses, 2),
                'net_profit' => $netProfit,
                'net_profit_formatted' => number_format($netProfit, 2),
                'profit_percentage' => number_format($profitPercentage, 1)
            ]
        ];

        // For debugging - get account types with their accounts
        $accountTypes = \App\Models\ChartOfAccountType::with('accounts')->get();

        return view('erp.doubleEntry.profitloss', compact('profitLossData', 'startDate', 'endDate', 'accountTypes'));
    }
}
