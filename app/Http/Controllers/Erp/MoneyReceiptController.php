<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MoneyReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'invoice', 'creator'])
            ->whereNotNull('customer_id') // Assuming Money Receipts are always linked to a customer
            ->latest('id');

        // Filters
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
                  ->orWhere('customer_id', 'like', "%{$search}%") 
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('branch_id')) {
            $branchId = $request->branch_id;
            $query->where(function($q) use ($branchId) {
                $q->whereHas('pos', function($pq) use ($branchId) {
                    $pq->where('branch_id', $branchId);
                })->orWhereHas('invoice.pos', function($ipq) use ($branchId) {
                    $ipq->where('branch_id', $branchId);
                });
            });
        }

        $receipts = $query->paginate(20)->appends($request->all());
        $totalAmount = $query->sum('amount');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('erp.money-receipt.table_rows', compact('receipts'))->render(),
                'totalAmount' => number_format($totalAmount, 2),
                'pagination' => (string) $receipts->links()
            ]);
        }

        $customers = Customer::orderBy('name')->get();
        $branches = \App\Models\Branch::all();
        return view('erp.money-receipt.index', compact('receipts', 'totalAmount', 'customers', 'branches'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        // Generate Receipt No: MR-YYYYMMDD-SEQU
        $receiptNo = $this->generateReceiptNumber();

        return view('erp.money-receipt.create', compact('customers', 'receiptNo'));
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

    public function store(Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date',
            // 'money_receipt_no' => 'nullable|string|max:50', // We will generate this backend side to be safe
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

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
            $payment->payment_method = $request->payment_method ?? 'Cash';
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

            DB::commit();
            return redirect()->route('money-receipt.index')->with('success', "Money Receipt created successfully. Receipt No: $receiptNo");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating receipt: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
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
}
