<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\FinancialAccount;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class MoneyReceiptController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view money receipts')) {
            abort(403, 'Unauthorized action.');
        }
        $query = Payment::with(['customer', 'invoice', 'creator.employee', 'pos', 'account'])
            ->where(function($q) {
                $q->where('payment_for', 'manual_receipt')
                  ->orWhereNotNull('customer_id');
            });

        $query = $this->applyFilters($query, $request);

        $perPage = $request->input('per_page', 20);
        $receipts = $query->latest('id')->paginate($perPage)->appends($request->all());
        $totalAmount = $query->sum('amount');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('erp.money-receipt.table_rows', compact('receipts'))->render(),
                'totalAmount' => number_format($totalAmount, 2),
                'pagination' => (string) $receipts->links()
            ]);
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $customersQuery = Customer::query();
        if ($restrictedBranchId) {
            $customersQuery->where('branch_id', $restrictedBranchId);
        }
        $customers = $customersQuery->orderBy('name')->take(200)->get();
        $recentReceipts = Payment::whereNotNull('payment_reference')->latest()->take(50)->get();
        $recentInvoices = Invoice::latest()->take(50)->get();

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = \App\Models\Branch::where('id', $restrictedBranchId)->get();
        } else {
            $branches = \App\Models\Branch::all();
        }
        return view('erp.money-receipt.index', compact('receipts', 'totalAmount', 'customers', 'branches', 'recentReceipts', 'recentInvoices'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage money receipts')) {
            abort(403, 'Unauthorized action.');
        }
        $restrictedBranchId = $this->getRestrictedBranchId();
        $customersQuery = Customer::query();
        if ($restrictedBranchId) {
            $customersQuery->where('branch_id', $restrictedBranchId);
        }
        $customers = $customersQuery->orderBy('name')->take(200)->get();
        // Generate Receipt No: MR-YYYYMMDD-SEQU
        $receiptNo = $this->generateReceiptNumber();

        $user = auth()->user();
        $bankAccounts = FinancialAccount::all();
        if ($user && $user->employee && $user->employee->branch_id) {
            $bankAccounts = $bankAccounts->where('branch_id', $user->employee->branch_id);
        } else {
            $bankAccounts = $bankAccounts->whereNull('branch_id');
        }

        // Get recent invoices for invoice-based selection
        $recentInvoicesQuery = Invoice::with('customer')
            ->where('due_amount', '>', 0)
            ->orderBy('id', 'desc');

        // If a specific invoice is requested, make sure it's included even if not in the top 100
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::with('customer')->find($request->invoice_id);
        }

        $recentInvoices = $recentInvoicesQuery->take(100)->get();

        // If we have a selected invoice that's not in the recent list, prepend it
        if ($selectedInvoice && !$recentInvoices->contains('id', $selectedInvoice->id)) {
            $recentInvoices->prepend($selectedInvoice);
        }

        return view('erp.money-receipt.create', compact('customers', 'receiptNo', 'bankAccounts', 'recentInvoices', 'selectedInvoice'));
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view money receipts')) {
            abort(403, 'Unauthorized action.');
        }

        $receipt = Payment::with(['customer', 'invoice', 'creator.employee', 'pos.branch'])->findOrFail($id);
        
        // If they want PDF/Print
        if (request('action') === 'print') {
            $general_settings = \App\Models\GeneralSetting::first();
            $pdf = Pdf::loadView('erp.money-receipt.show', compact('receipt', 'general_settings'));
            // Use thermal paper size (approx 80mm width) or A4? 
            // Let's use A4 portrait for manual receipts to be safe, or just return view.
            return $pdf->stream('receipt-'.$receipt->payment_reference.'.pdf');
        }

        return view('erp.money-receipt.show', compact('receipt'));
    }

    public function getDueInvoices($customerId)
    {
        $invoices = Invoice::where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->where('due_amount', '>', 0)
            ->orderBy('id', 'desc')
            ->get(['id', 'invoice_number', 'due_amount', 'total_amount', 'paid_amount', 'issue_date']);

        return response()->json($invoices);
    }

    /**
     * Get single invoice details with customer info
     * Used for auto-populating money receipt when coming from sales list
     */
    public function getInvoiceDetails($invoiceId)
    {
        $invoice = Invoice::with('customer')->find($invoiceId);

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'due_amount' => $invoice->due_amount,
            'total_amount' => $invoice->total_amount,
            'paid_amount' => $invoice->paid_amount,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $invoice->customer ? $invoice->customer->name : 'Walk-in',
            'is_walk_in' => is_null($invoice->customer_id)
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage money receipts')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get payment type and validate accordingly
        $paymentType = $request->payment_type;
        
        if ($paymentType === 'customer') {
            $request->validate([
                'payment_date' => 'required|date',
                'customer_id' => 'required|exists:customers,id',
                'invoice_id' => 'nullable|exists:invoices,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'nullable|string',
                'note' => 'nullable|string',
            ]);
            $customerId = $request->customer_id;
        } else {
            $request->validate([
                'payment_date' => 'required|date',
                'invoice_search_id' => 'required|exists:invoices,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'nullable|string',
                'note' => 'nullable|string',
            ]);
            // Get customer from invoice
            $invoice = Invoice::find($request->invoice_search_id);
            if (!$invoice) {
                return redirect()->back()->with('error', 'Invoice not found');
            }
            $customerId = $invoice->customer_id;
            $request->merge(['customer_id' => $customerId, 'invoice_id' => $request->invoice_search_id]);
        }

        DB::beginTransaction();
        try {
            // Always generate a fresh number on save to prevent duplicates/race conditions
            $receiptNo = $this->generateReceiptNumber();

            // Create Payment
            $payment = new Payment();
            $payment->customer_id = $request->customer_id;
            $payment->invoice_id = $request->invoice_id; // Can be null (Advance/account payment)
            $payment->payment_date = $request->payment_date;
            $payment->amount = $request->amount;
            $payment->payment_method = $request->payment_method ?? 'cash';
            $payment->payment_reference = $receiptNo;
            $payment->note = $request->note;
            $payment->payment_for = 'manual_receipt';
            $payment->user_id = auth()->id();
            
            // Check if account info is provided in request (not in validation but might be in form)
            if ($request->filled('account_id')) {
                $payment->account_id = $request->account_id;
            }

            $payment->save();

            // Handle Customer Balance update (to match PosController pattern)
            if ($request->customer_id) {
                // We use first() because the existing system seems to track a single 'record' per customer in some places, 
                // but PosController shows it might be history. However, let's follow the 'update if exists' logic to stay consistent.
                $balance = \App\Models\Balance::where('source_type', 'customer')->where('source_id', $request->customer_id)->first();
                if ($balance) {
                    $balance->balance -= $request->amount;
                    $balance->save();
                } else {
                    \App\Models\Balance::create([
                        'source_type' => 'customer',
                        'source_id' => $request->customer_id,
                        'balance' => -$request->amount,
                        'description' => 'Manual Receipt - ' . $receiptNo,
                    ]);
                }
            }

            // Handle Invoice Update if invoice selected
            if ($request->invoice_id) {
                $invoice = Invoice::lockForUpdate()->find($request->invoice_id);
                if ($invoice) {
                    $invoice->paid_amount += $request->amount;
                    $invoice->due_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
                    
                    if ($invoice->paid_amount >= $invoice->total_amount) {
                         $invoice->status = 'paid';
                    } elseif ($invoice->paid_amount > 0) {
                         $invoice->status = 'partial';
                    }
                    $invoice->save();
                }
            }

            // =====================================================
            // AUTO JOURNAL ENTRY (Double-Entry Accounting)
            // =====================================================
            $financialAccount = null;
            if ($request->filled('account_id')) {
                $financialAccount = FinancialAccount::find($request->account_id);
            } else {
                // Fallback to first available account of specified type
                $financialAccount = FinancialAccount::where('type', strtolower($request->payment_method ?: 'cash'))->first();
            }

            if ($financialAccount && $financialAccount->account_id) {
                // Update the real balance to maintain the Cash/Bank Book
                $financialAccount->balance += $payment->amount;
                $financialAccount->save();

                $voucherNo = 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT);
                while (Journal::where('voucher_no', $voucherNo)->exists()) {
                    $voucherNo = 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) . '-' . rand(10, 99);
                }

                $journal = Journal::create([
                    'voucher_no'     => $voucherNo,
                    'entry_date'     => $payment->payment_date,
                    'type'           => 'Receipt',
                    'description'    => 'Manual Money Receipt #' . $receiptNo . ($payment->note ? ' - ' . $payment->note : ''),
                    'customer_id'    => $payment->customer_id,
                    'voucher_amount' => $payment->amount,
                    'paid_amount'    => $payment->amount,
                    'reference'      => $receiptNo,
                    'created_by'     => auth()->id(),
                    'updated_by'     => auth()->id(),
                ]);

                // DEBIT Cash/Bank (Asset increases)
                JournalEntry::create([
                    'journal_id'           => $journal->id,
                    'chart_of_account_id'  => $financialAccount->account_id,
                    'financial_account_id' => $financialAccount->id,
                    'debit'                => $payment->amount,
                    'credit'               => 0,
                    'memo'                 => 'Collection via ' . $financialAccount->provider_name,
                    'created_by'           => auth()->id(),
                    'updated_by'           => auth()->id(),
                ]);

                // CREDIT Accounts Receivable (Asset decreases)
                $arAccount = ChartOfAccount::where('name', 'like', '%Receivable%')->first();
                if (!$arAccount) {
                    $assetType = ChartOfAccountType::where('name', 'Asset')->first();
                    $arAccount = ChartOfAccount::create([
                        'name' => 'Accounts Receivable',
                        'type_id' => $assetType ? $assetType->id : 8,
                        'code' => '10002',
                        'status' => 'active'
                    ]);
                }

                JournalEntry::create([
                    'journal_id'           => $journal->id,
                    'chart_of_account_id'  => $arAccount->id,
                    'debit'                => 0,
                    'credit'               => $payment->amount,
                    'memo'                 => 'Manual Receipt from Customer',
                    'created_by'           => auth()->id(),
                    'updated_by'           => auth()->id(),
                ]);
            }
            // =====================================================

            DB::commit();
            return redirect()->route('money-receipt.index')->with('success', "Money Receipt created successfully. Receipt No: $receiptNo");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating receipt: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage money receipts')) {
            abort(403, 'Unauthorized action.');
        }

        $receipt = Payment::findOrFail($id);
        $restrictedBranchId = $this->getRestrictedBranchId();
        $customersQuery = Customer::query();
        if ($restrictedBranchId) {
            $customersQuery->where('branch_id', $restrictedBranchId);
        }
        $customers = $customersQuery->orderBy('name')->take(200)->get();
        $bankAccounts = FinancialAccount::all();
        
        // Load invoices for the selected customer
        $invoices = Invoice::where('customer_id', $receipt->customer_id)
            ->where(function($q) use ($receipt) {
                $q->where('status', '!=', 'paid')
                  ->orWhere('id', $receipt->invoice_id);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('erp.money-receipt.edit', compact('receipt', 'customers', 'bankAccounts', 'invoices'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage money receipts')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'payment_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);
            $oldAmount = $payment->amount;
            $oldCustomerId = $payment->customer_id;
            $oldInvoiceId = $payment->invoice_id;
            $oldAccountId = $payment->account_id;

            // 1. Revert Old Balance/Invoice/Account
            if ($oldCustomerId) {
                $balance = \App\Models\Balance::where('source_type', 'customer')->where('source_id', $oldCustomerId)->first();
                if ($balance) {
                    $balance->balance += $oldAmount;
                    $balance->save();
                }
            }

            if ($oldInvoiceId) {
                $oldInvoice = Invoice::find($oldInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->paid_amount -= $oldAmount;
                    $oldInvoice->due_amount += $oldAmount;
                    if ($oldInvoice->paid_amount <= 0) $oldInvoice->status = 'unpaid';
                    elseif ($oldInvoice->paid_amount < $oldInvoice->total_amount) $oldInvoice->status = 'partial';
                    $oldInvoice->save();
                }
            }

            if ($oldAccountId) {
                $oldAccount = FinancialAccount::find($oldAccountId);
                if ($oldAccount) {
                    $oldAccount->balance -= $oldAmount;
                    $oldAccount->save();
                }
            }

            // Delete old Journal/JournalEntries
            Journal::where('reference', $payment->payment_reference)->delete();

            // 2. Apply New Changes
            $payment->customer_id = $request->customer_id;
            $payment->invoice_id = $request->invoice_id;
            $payment->payment_date = $request->payment_date;
            $payment->amount = $request->amount;
            $payment->payment_method = $request->payment_method;
            $payment->note = $request->note;
            $payment->account_id = $request->account_id;
            $payment->save();

            // Update New Balance
            if ($request->customer_id) {
                $balance = \App\Models\Balance::where('source_type', 'customer')->where('source_id', $request->customer_id)->first();
                if ($balance) {
                    $balance->balance -= $request->amount;
                    $balance->save();
                } else {
                    \App\Models\Balance::create([
                        'source_type' => 'customer',
                        'source_id' => $request->customer_id,
                        'balance' => -$request->amount,
                        'description' => 'Manual Receipt (Updated) - ' . $payment->payment_reference,
                    ]);
                }
            }

            // Update New Invoice
            if ($request->invoice_id) {
                $invoice = Invoice::find($request->invoice_id);
                if ($invoice) {
                    $invoice->paid_amount += $request->amount;
                    $invoice->due_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
                    if ($invoice->paid_amount >= $invoice->total_amount) $invoice->status = 'paid';
                    else $invoice->status = 'partial';
                    $invoice->save();
                }
            }

            // Update New Account
            $financialAccount = FinancialAccount::find($request->account_id);
            if (!$financialAccount) {
                $financialAccount = FinancialAccount::where('type', $request->payment_method)->first();
            }

            if ($financialAccount) {
                $financialAccount->balance += $request->amount;
                $financialAccount->save();

                // Recreate Journal Entry
                $voucherNo = 'REC-UPD-' . $payment->id;
                $journal = Journal::create([
                    'voucher_no'     => $voucherNo,
                    'entry_date'     => $payment->payment_date,
                    'type'           => 'Receipt',
                    'description'    => 'Updated Receipt #' . $payment->payment_reference,
                    'customer_id'    => $payment->customer_id,
                    'voucher_amount' => $payment->amount,
                    'paid_amount'    => $payment->amount,
                    'reference'      => $payment->payment_reference,
                    'created_by'     => auth()->id(),
                    'updated_by'     => auth()->id(),
                ]);

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $financialAccount->account_id,
                    'financial_account_id' => $financialAccount->id,
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'memo' => 'Collection (Updated)',
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $arAccount = ChartOfAccount::where('name', 'like', '%Receivable%')->first();
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'chart_of_account_id' => $arAccount->id,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'memo' => 'Manual Receipt (Updated)',
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('money-receipt.index')->with('success', "Money Receipt updated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating receipt: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('manage money receipts')) {
            abort(403, 'Unauthorized action.');
        }
        DB::beginTransaction();
        try {
            $payment = Payment::where('id', $id)->firstOrFail();

            // Revert Customer Balance if customer_id exists
            if ($payment->customer_id) {
                $balance = \App\Models\Balance::where('source_type', 'customer')->where('source_id', $payment->customer_id)->first();
                if ($balance) {
                    $balance->balance += $payment->amount; // Add back the amount we previously deducted
                    $balance->save();
                }
            }

            // Revert Invoice calculation if exists
            if ($payment->invoice_id && $payment->invoice) {
                $invoice = Invoice::lockForUpdate()->find($payment->invoice_id);
                
                $invoice->paid_amount -= $payment->amount;
                $invoice->due_amount += $payment->amount; // Add back to due

                // Correct floating point potential issues
                if ($invoice->paid_amount < 0) $invoice->paid_amount = 0;
                
                // Re-evaluate status
                if ($invoice->paid_amount >= $invoice->total_amount) {
                    $invoice->status = 'paid';
                    $invoice->due_amount = 0;
                } elseif ($invoice->paid_amount > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'unpaid';
                }
                
                $invoice->save();
            }

            // Revert Financial Account balance if account_id exists
            if ($payment->account_id) {
                $account = FinancialAccount::find($payment->account_id);
                if ($account) {
                    $account->balance -= $payment->amount;
                    $account->save();
                }
            }

            // Delete Associated Journal and Journal Entries
            if ($payment->payment_reference) {
                $journal = Journal::where('reference', $payment->payment_reference)->first();
                if ($journal) {
                    $journal->entries()->delete();
                    $journal->delete();
                }
            }

            $payment->delete();

            DB::commit();
            return redirect()->route('money-receipt.index')->with('success', 'Money Receipt deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting receipt: ' . $e->getMessage());
        }
    }

    private function generateReceiptNumber()
    {
        $prefix = 'MR-';
        
        // Find the last payment reference that starts with MR-
        $lastPayment = Payment::where('payment_reference', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(payment_reference) DESC') // Ensure we get the longest string first ( e.g. MR-100 vs MR-10) - robust sorting
            ->orderBy('payment_reference', 'desc')
            ->first();

        if ($lastPayment) {
            // Extract the number part
            // MR-000001 -> 1
            $lastRef = $lastPayment->payment_reference;
            $numberPart = (int) str_replace($prefix, '', $lastRef);
            $newSeq = $numberPart + 1;
        } else {
            $newSeq = 1;
        }

        return $prefix . str_pad($newSeq, 6, '0', STR_PAD_LEFT);
    }

    private function applyFilters($query, Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where(function($q) use ($restrictedBranchId) {
                $q->whereHas('pos', function($pq) use ($restrictedBranchId) {
                    $pq->where('branch_id', $restrictedBranchId);
                })
                ->orWhereHas('invoice.pos', function($ipq) use ($restrictedBranchId) {
                    $ipq->where('branch_id', $restrictedBranchId);
                })
                ->orWhereHas('creator.employee', function($eq) use ($restrictedBranchId) {
                    $eq->where('branch_id', $restrictedBranchId);
                });
            });
        }

        if ($request->filled('report_type')) {
            $type = $request->report_type;
            if ($type === 'daily') {
                $query->whereDate('payment_date', Carbon::today());
            } elseif ($type === 'monthly') {
                $month = $request->input('month', date('m'));
                $year = $request->input('year', date('Y'));
                $query->whereMonth('payment_date', $month)->whereYear('payment_date', $year);
            } elseif ($type === 'yearly') {
                $year = $request->input('year', date('Y'));
                $query->whereYear('payment_date', $year);
            }
            // 'all' type does not apply any date restriction
        }

        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('receipt_no')) {
            $query->where('payment_reference', 'like', "%{$request->receipt_no}%");
        }

        if ($request->filled('invoice_no')) {
            $query->whereHas('invoice', function($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->invoice_no}%");
            });
        }

        if ($request->filled('branch_id')) {
            $branchId = $request->branch_id;
            $query->where(function($q) use ($branchId) {
                $q->whereHas('pos', function($pq) use ($branchId) {
                    $pq->where('branch_id', $branchId);
                })->orWhereHas('invoice.pos', function($ipq) use ($branchId) {
                    $ipq->where('branch_id', $branchId);
                })->orWhereHas('creator.employee', function($eq) use ($branchId) {
                    $eq->where('branch_id', $branchId);
                });
            });
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view money receipts')) {
            abort(403, 'Unauthorized action.');
        }
        $query = Payment::with(['customer', 'invoice', 'creator.employee', 'pos'])
            ->where(function($q) {
                $q->where('payment_for', 'manual_receipt')
                  ->orWhereNotNull('customer_id');
            });
        $query = $this->applyFilters($query, $request);
        $receipts = $query->latest('id')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Serial No', 'Receipt No', 'Receipt Date', 'Invoice Date', 'Customer', 
            'Outlet', 'Sales Invoice', 'Due Amount', 'Paid Amount', 'Account', 'Collector'
        ];
        
        $sheet->fromArray([$headers], NULL, 'A1');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        $rowNum = 2;
        foreach ($receipts as $index => $receipt) {
            $data = [
                $index + 1,
                $receipt->payment_reference,
                Carbon::parse($receipt->payment_date)->format('d/m/Y'),
                $receipt->invoice ? Carbon::parse($receipt->invoice->issue_date)->format('d/m/Y') : '-',
                $receipt->customer->name ?? 'Walk-in',
                $receipt->pos->branch->name ?? $receipt->invoice->pos->branch->name ?? '-',
                $receipt->invoice->invoice_number ?? '-',
                $receipt->invoice->due_amount ?? 0,
                $receipt->amount,
                $receipt->account ? $receipt->account->provider_name : (ucfirst($receipt->payment_method) ?: 'Cash'),
                $receipt->creator->name ?? '-'
            ];
            $sheet->fromArray([$data], NULL, 'A' . $rowNum);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'money_receipt_report_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view money receipts')) {
            abort(403, 'Unauthorized action.');
        }
        $query = Payment::with(['customer', 'invoice', 'creator.employee', 'pos', 'account'])
            ->where(function($q) {
                $q->where('payment_for', 'manual_receipt')
                  ->orWhereNotNull('customer_id');
            });
        $query = $this->applyFilters($query, $request);
        $receipts = $query->latest('id')->get();
        $totalAmount = $query->sum('amount');

        $pdf = Pdf::loadView('erp.money-receipt.export-pdf', compact('receipts', 'totalAmount'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'money_receipt_report_' . date('Ymd_His') . '.pdf';
        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }
        return $pdf->download($filename);
    }
}
