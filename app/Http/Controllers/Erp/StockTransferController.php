<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\ProductVariationStock;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\FinancialAccount;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->applyFilters($request);

        // Group by invoice_number to show as "Invoices" instead of individual items
        // For records without invoice_number (old ones), we'll group them by ID so they appear individually
        // Since we can't easily mix grouped and non-grouped in one query efficiently without raw SQL,
        // we will fetch the latest item for each invoice or the item itself if no invoice.
        
        // Use a subquery strategy to get the "Representative" transfer for each invoice
        // BUT strict mode makes this hard.
        // EASIER: Just list them. If user wants "Like invoice", they usually mean Create One Invoice.
        // Viewing them in list: we can visually group them in blade if they are consecutive?
        // NO, pagination breaks that.
        
        // Let's use a distinct approach on invoice_number where it exists
        $transfers = StockTransfer::select('invoice_number', \DB::raw('MAX(id) as id'), \DB::raw('COUNT(*) as item_count'), \DB::raw('SUM(total_price) as total_amount'), \DB::raw('MAX(requested_at) as requested_at'), \DB::raw('MAX(status) as status'))
            ->whereNotNull('invoice_number')
            ->groupBy('invoice_number')
            ->orderBy('requested_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);
        
        // If we want to support old records without invoice number, we'd need a UNION.
        // For now, let's assume we proceed with new "Invoice" style transfers.
        // Or better: update old records to have an invoice number? Too risky.
        
        // Let's try to include both: records with invoice_number (grouped) AND records without (individual).
        // This is complex for pagination.
        // Simple fallback: Show ALL, but in the LIST VIEW (Blade), simple records show as is.
        // BUT the user request is "Invoice in one".
        
        // LET'S STICK TO THE ORIGINAL QUERY but we will modify the BLADE to visualy group?
        // No, user specifically asked "Invoice in one". This implies a list of "Dispatch Notes".
        
        // Adjusted Strategy: 
        // We will default to showing a list of "Transfers" (Grouped).
        // If needed we can drill down.
        
        // Since I just added the column, all previous are NULL.
        // I will display records with invoice_number as a SINGLE ROW.
        // Records without invoice_number (old) will display as SINGLE ROW each.
        
        // We can mimic this by generating a fake invoice ID for nulls in the select?
        // "COALESCE(invoice_number, CONCAT('INDIV-', id)) as group_id"
        
        $restrictedBranchId = $this->getRestrictedBranchId();

        $transfers = StockTransfer::select(
                \DB::raw('COALESCE(invoice_number, CONCAT("INDIV-", id)) as invoice_group'),
                \DB::raw('MAX(id) as id'), // To link to details
                \DB::raw('COUNT(*) as item_count'),
                \DB::raw('SUM(total_price) as grouped_total_price'),
                'from_type', 'from_id', 'to_type', 'to_id', 'requested_by', 'status', 'requested_at' // Assuming these are same for the group
            )
            ->with(['fromBranch', 'fromWarehouse', 'toBranch', 'toWarehouse', 'requestedPerson'])
            ->where(function($q) use ($restrictedBranchId) {
                if ($restrictedBranchId) {
                    $q->where(function($q2) use ($restrictedBranchId) {
                        $q2->where('from_type', 'branch')->where('from_id', $restrictedBranchId);
                    })->orWhere(function($q2) use ($restrictedBranchId) {
                        $q2->where('to_type', 'branch')->where('to_id', $restrictedBranchId);
                    });
                }
            })
            ->groupBy('invoice_group', 'from_type', 'from_id', 'to_type', 'to_id', 'requested_by', 'status', 'requested_at')
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
            $warehouses = collect();
        } else {
            $branches = Branch::all();
            $warehouses = Warehouse::all();
        }
        $statuses = ['pending', 'approved', 'rejected', 'shipped', 'delivered'];
        
        // Get filter options
        $categories = \App\Models\ProductServiceCategory::all();
        $brands = \App\Models\Brand::all();
        $seasons = \App\Models\Season::all();
        $genders = \App\Models\Gender::all();
        $styleNumbers = \App\Models\Product::whereNotNull('style_number')
            ->distinct()
            ->pluck('style_number');
        
        $filters = $request->only(['search', 'from_branch_id', 'from_warehouse_id', 'to_branch_id', 'to_warehouse_id', 'status', 'date_from', 'date_to', 'variation_id', 'quick_filter']);
        
        return view('erp.stockTransfer.stockTransfer', compact('transfers', 'branches', 'warehouses', 'statuses', 'filters', 'categories', 'brands', 'seasons', 'genders', 'styleNumbers'));
    }

    public function create()
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $branches = Branch::where('id', $restrictedBranchId)->get();
            $warehouses = collect();
        } else {
            $branches = Branch::all();
            $warehouses = Warehouse::all();
        }
        $financialAccounts = \App\Models\FinancialAccount::orderBy('provider_name')->get();
        return view('erp.stockTransfer.create', compact('branches', 'warehouses', 'financialAccounts'));
    }

    private function applyFilters(Request $request)
    {
        $query = StockTransfer::with([
            'product.category', 
            'product.brand', 
            'product.season', 
            'product.gender',
            'variation.combinations.attribute', 
            'variation.combinations.attributeValue',
            'fromBranch', 
            'fromWarehouse', 
            'toBranch', 
            'toWarehouse', 
            'requestedPerson', 
            'approvedPerson'
        ]);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where(function($q) use ($restrictedBranchId) {
                $q->where(function($q2) use ($restrictedBranchId) {
                    $q2->where('from_type', 'branch')->where('from_id', $restrictedBranchId);
                })->orWhere(function($q2) use ($restrictedBranchId) {
                    $q2->where('to_type', 'branch')->where('to_id', $restrictedBranchId);
                });
            });
        }

        if ($request->filled('from_branch_id')) {
            $fromValue = $request->from_branch_id;
            if (str_starts_with($fromValue, 'branch_')) {
                $branchId = str_replace('branch_', '', $fromValue);
                $query->where('from_type', 'branch')->where('from_id', $branchId);
            } elseif (str_starts_with($fromValue, 'warehouse_')) {
                $warehouseId = str_replace('warehouse_', '', $fromValue);
                $query->where('from_type', 'warehouse')->where('from_id', $warehouseId);
            } else {
                $query->where('from_type', 'branch')->where('from_id', $fromValue);
            }
        }
        if ($request->filled('from_warehouse_id')) {
            $query->where('from_type', 'warehouse')->where('from_id', $request->from_warehouse_id);
        }
        
        if ($request->filled('to_branch_id')) {
            $toValue = $request->to_branch_id;
            if (str_starts_with($toValue, 'branch_')) {
                $branchId = str_replace('branch_', '', $toValue);
                $query->where('to_type', 'branch')->where('to_id', $branchId);
            } elseif (str_starts_with($toValue, 'warehouse_')) {
                $warehouseId = str_replace('warehouse_', '', $toValue);
                $query->where('to_type', 'warehouse')->where('to_id', $warehouseId);
            } else {
                $query->where('to_type', 'branch')->where('to_id', $toValue);
            }
        }
        if ($request->filled('to_warehouse_id')) {
            $query->where('to_type', 'warehouse')->where('to_id', $request->to_warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%$search%");
                })->orWhereHas('variation', function($vq) use ($search) {
                    $vq->where('name', 'like', "%$search%");
                })->orWhere('id', 'like', "%$search%");
            });
        }

        if ($request->filled('variation_id')) {
            $query->where('variation_id', $request->variation_id);
        }
        
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        
        if ($request->filled('style_number')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('style_number', $request->style_number);
            });
        }
        
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        if ($request->filled('brand_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }
        
        if ($request->filled('season_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('season_id', $request->season_id);
            });
        }
        
        if ($request->filled('gender_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('gender_id', $request->gender_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }
        
        if ($request->filled('month')) {
            $query->whereMonth('requested_at', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('requested_at', $request->year);
        }

        if ($request->filled('quick_filter')) {
            if ($request->quick_filter == 'today') {
                $query->whereDate('requested_at', now()->toDateString());
            } elseif ($request->quick_filter == 'monthly') {
                $query->whereMonth('requested_at', now()->month)
                      ->whereYear('requested_at', now()->year);
            }
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        $query = StockTransfer::select(
                \DB::raw('COALESCE(invoice_number, CONCAT("INDIV-", id)) as invoice_group'),
                \DB::raw('MAX(id) as id'),
                \DB::raw('COUNT(*) as item_count'),
                \DB::raw('SUM(total_price) as grouped_total_amount'),
                \DB::raw('SUM(quantity) as total_qty'),
                'from_type', 'from_id', 'to_type', 'to_id', 'requested_by', 'status', 'requested_at',
                'sender_account_id', 'receiver_account_id', 'invoice_number'
            )
            ->with(['fromBranch', 'fromWarehouse', 'toBranch', 'toWarehouse', 'requestedPerson', 'senderAccount', 'receiverAccount'])
            ->where(function($q) use ($restrictedBranchId) {
                if ($restrictedBranchId) {
                    $q->where(function($q2) use ($restrictedBranchId) {
                        $q2->where('from_type', 'branch')->where('from_id', $restrictedBranchId);
                    })->orWhere(function($q2) use ($restrictedBranchId) {
                        $q2->where('to_type', 'branch')->where('to_id', $restrictedBranchId);
                    });
                }
            })
            ->groupBy('invoice_group', 'from_type', 'from_id', 'to_type', 'to_id', 'requested_by', 'status', 'requested_at', 'sender_account_id', 'receiver_account_id', 'invoice_number')
            ->orderBy('requested_at', 'desc');

        $transfers = $query->get();
        $headers = ['Invoice No', 'Date', 'Source', 'Destination', 'Items', 'Total Qty', 'Total Amount', 'Sender Account', 'Receiver Account', 'Status', 'Requested By'];
        
        $exportData = [];
        foreach ($transfers as $transfer) {
            $exportData[] = [
                $transfer->invoice_number ?? 'N/A',
                $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d-m-Y') : '-',
                $transfer->from_type == 'branch' ? ($transfer->fromBranch->name ?? '-') : ($transfer->fromWarehouse->name ?? '-'),
                $transfer->to_type == 'branch' ? ($transfer->toBranch->name ?? '-') : ($transfer->toWarehouse->name ?? '-'),
                $transfer->item_count,
                number_format($transfer->total_qty, 0),
                number_format($transfer->grouped_total_amount, 2),
                $transfer->senderAccount->provider_name ?? '-',
                $transfer->receiverAccount->provider_name ?? '-',
                ucfirst($transfer->status),
                $transfer->requestedPerson->name ?? '-'
            ];
        }

        $filename = 'stock_transfer_summary_' . date('Y-m-d_H-i-s') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Stock Transfer Summary Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '3', $header);
            $sheet->getStyle(chr(65 + $index) . '3')->getFont()->setBold(true);
        }
        
        $dataRow = 4;
        foreach ($exportData as $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . $dataRow, $value);
            }
            $dataRow++;
        }
        
        foreach (range('A', chr(65 + count($headers) - 1)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);
        
        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    public function exportPdf(Request $request)
    {
        $restrictedBranchId = $this->getRestrictedBranchId();
        $query = StockTransfer::select(
                \DB::raw('COALESCE(invoice_number, CONCAT("INDIV-", id)) as invoice_group'),
                \DB::raw('MAX(id) as id'),
                \DB::raw('COUNT(*) as item_count'),
                \DB::raw('SUM(total_price) as grouped_total_amount'),
                \DB::raw('SUM(quantity) as total_qty'),
                'from_type', 'from_id', 'to_type', 'to_id', 'requested_by', 'status', 'requested_at', 'invoice_number'
            )
            ->with(['fromBranch', 'fromWarehouse', 'toBranch', 'toWarehouse', 'requestedPerson'])
            ->where(function($q) use ($restrictedBranchId) {
                if ($restrictedBranchId) {
                    $q->where(function($q2) use ($restrictedBranchId) {
                        $q2->where('from_type', 'branch')->where('from_id', $restrictedBranchId);
                    })->orWhere(function($q2) use ($restrictedBranchId) {
                        $q2->where('to_type', 'branch')->where('to_id', $restrictedBranchId);
                    });
                }
            })
            ->groupBy('invoice_group', 'from_type', 'from_id', 'to_type', 'to_id', 'requested_by', 'status', 'requested_at', 'invoice_number')
            ->orderBy('requested_at', 'desc');

        $transfers = $query->get();
        $filename = 'stock_transfer_summary_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.stockTransfer.report-pdf', [
            'transfers' => $transfers,
            'filters' => $request->all()
        ]);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->download($filename);
    }

    public function show($id)
    {
        $transfer = StockTransfer::with(['product.category', 'variation'])->findOrFail($id);
        
        if ($transfer->invoice_number) {
            $transfers = StockTransfer::with(['product.category', 'variation'])
                ->where('invoice_number', $transfer->invoice_number)
                ->get();
        } else {
            $transfers = collect([$transfer]);
        }
        
        return view('erp.stockTransfer.show', compact('transfer', 'transfers'));
    }

    public function store(Request $request)
    {
        // Validate basic transfer information
    $request->validate([
        'transfer_date' => 'required|date',
        'from_outlet' => 'required|string',
        'to_outlet' => 'required|string',
        'items' => 'required|array|min:1',
    ]);

    // Parse to_outlet
    $toOutlet = $request->to_outlet;
    if (str_starts_with($toOutlet, 'branch_')) {
        $toType = 'branch';
        $toId = str_replace('branch_', '', $toOutlet);
    } elseif (str_starts_with($toOutlet, 'warehouse_')) {
        $toType = 'warehouse';
        $toId = str_replace('warehouse_', '', $toOutlet);
    } else {
        return redirect()->back()->with('error', 'Invalid receiver outlet selected.');
    }

    // Parse from_outlet
    $fromOutlet = $request->from_outlet;
    if (str_starts_with($fromOutlet, 'branch_')) {
        $fromType = 'branch';
        $fromId = str_replace('branch_', '', $fromOutlet);
    } elseif (str_starts_with($fromOutlet, 'warehouse_')) {
        $fromType = 'warehouse';
        $fromId = str_replace('warehouse_', '', $fromOutlet);
    } else {
        return redirect()->back()->with('error', 'Invalid sender outlet selected.');
    }    

        // Process each item and validate stock
        $transfersCreated = 0;
        $errors = [];

        // Generate sequential invoice number for this batch
        $today = date('Ymd');
        $lastInvoice = StockTransfer::where('invoice_number', 'like', "TRF-{$today}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice && preg_match('/TRF-\d{8}-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        $invoiceNumber = 'TRF-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        foreach ($request->items as $key => $item) {
            // Skip if no quantity
            if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                continue;
            }

            $productId = $item['product_id'];
            $variationId = $item['variation_id'] ?? null;
            $quantity = floatval($item['quantity']);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $totalPrice = $quantity * $unitPrice;
            
            // Pro-rate the global paid amount across items based on total value
            // Calculate total dispatch value first for pro-rating
            $totalDispatchValue = 0;
            foreach ($request->items as $i) {
                $totalDispatchValue += floatval($i['quantity'] ?? 0) * floatval($i['unit_price'] ?? 0);
            }
            
            $globalPaid = floatval($request->paid_amount ?? 0);
            $itemPaid = $totalDispatchValue > 0 ? ($totalPrice / $totalDispatchValue) * $globalPaid : 0;
            $itemDue = $totalPrice - $itemPaid;

            // Validate stock availability based on Source Location
        if ($variationId) {
            $query = ProductVariationStock::where('variation_id', $variationId);
            if ($fromType === 'branch') {
                $query->where('branch_id', $fromId);
            } else {
                $query->where('warehouse_id', $fromId);
            }
            $totalStock = $query->sum('quantity');
        } else {
            if ($fromType === 'branch') {
                $totalStock = BranchProductStock::where('product_id', $productId)->where('branch_id', $fromId)->sum('quantity');
            } else {
                $totalStock = WarehouseProductStock::where('product_id', $productId)->where('warehouse_id', $fromId)->sum('quantity');
            }
        }    

            if ($quantity > $totalStock) {
                $errors[] = "Product/Variation ID {$productId}/{$variationId}: Requested {$quantity}, but only {$totalStock} available.";
                continue;
            }

            // Create transfer record
        try {
            StockTransfer::create([
                'from_type' => $fromType,
                'from_id' => $fromId,
                'to_type' => $toType,
                'to_id' => $toId,
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'paid_amount' => $itemPaid,
                    'due_amount' => $itemDue,
                    'sender_account_id' => $request->sender_account_id,
                    'receiver_account_id' => $request->receiver_account_id,
                    'sender_account_type' => $request->sender_account_type,
                    'sender_account_number' => $request->sender_account_number,
                    'receiver_account_type' => $request->receiver_account_type,
                    'receiver_account_number' => $request->receiver_account_number,
                    'type' => 'transfer',
                    'status' => 'pending',
                    'requested_by' => auth()->id(),
                    'requested_at' => $request->transfer_date,
                    'notes' => $request->note ?? null,
                    'invoice_number' => $invoiceNumber,
                ]);
                $transfersCreated++;
            } catch (\Exception $e) {
                $errors[] = "Error creating transfer for product {$productId}: " . $e->getMessage();
            }
        }

        if ($transfersCreated > 0) {
            $message = "Successfully created {$transfersCreated} transfer(s).";
            if (count($errors) > 0) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            return redirect()->route('stocktransfer.list')->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'No transfers created. ' . implode(', ', $errors));
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $primaryTransfer = StockTransfer::findOrFail($id);
        
        // Identify the batch: if invoice_number exists, get all; otherwise just this one
        if ($primaryTransfer->invoice_number) {
            $transfers = StockTransfer::where('invoice_number', $primaryTransfer->invoice_number)->get();
        } else {
            $transfers = collect([$primaryTransfer]);
        }

        // Phase 1: Pre-validation (Crucial for atomic invoice approval)
        if ($request->status == 'approved') {
            foreach ($transfers as $transfer) {
                if ($transfer->status == 'approved') continue; // Already approved

                if ($transfer->variation_id) {
                     // Check Variation Stock
                     if($transfer->from_type == 'branch'){
                         $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                             ->where('branch_id', $transfer->from_id)
                             ->whereNull('warehouse_id')
                             ->first();
                         $availableQty = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
                         if (!$vStock || $availableQty < $transfer->quantity) {
                             return redirect()->back()->with('error', "Insufficient stock for product '{$transfer->product->name}' (Var: {$transfer->variation->name}) at source branch. Available: {$availableQty}, Requested: {$transfer->quantity}");
                         }
                     } else {
                         // Warehouse Variation
                         $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                             ->where('warehouse_id', $transfer->from_id)
                             ->whereNull('branch_id')
                             ->first();
                         $availableQty = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
                         if (!$vStock || $availableQty < $transfer->quantity) {
                             return redirect()->back()->with('error', "Insufficient stock for product '{$transfer->product->name}' (Var: {$transfer->variation->name}) at source warehouse. Available: {$availableQty}, Requested: {$transfer->quantity}");
                         }
                     }
                } else {
                    // Check Regular Stock
                    if($transfer->from_type == 'branch'){
                         $branchStock = BranchProductStock::where('product_id', $transfer->product_id)->where('branch_id', $transfer->from_id)->first();
                         if (!$branchStock || $branchStock->quantity < $transfer->quantity) {
                             return redirect()->back()->with('error', "Insufficient stock for product '{$transfer->product->name}' at source branch.");
                         }
                    } else {
                         $warehouseStock = WarehouseProductStock::where('product_id', $transfer->product_id)->where('warehouse_id', $transfer->from_id)->first();
                         if (!$warehouseStock || $warehouseStock->quantity < $transfer->quantity) {
                             return redirect()->back()->with('error', "Insufficient stock for product '{$transfer->product->name}' at source warehouse.");
                         }
                    }
                }
            }
        }

        // Phase 2: Apply Status Updates and Stock Changes
        DB::beginTransaction();
        try {
            foreach ($transfers as $transfer) {
                // Skip if status matches (idempotency, though some transitions might be valid re-entries, simplified here)
                if ($transfer->status == $request->status) continue;

                if($request->status == 'approved')
                {
                    $transfer->status = $request->status;
                    $transfer->approved_by = auth()->id();
                    $transfer->approved_at = now();
                    $transfer->save(); // Save status first

                    // Deduct Stock
                    if ($transfer->variation_id) {
                        if($transfer->from_type == 'branch'){
                            $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                                ->where('branch_id', $transfer->from_id)
                                ->whereNull('warehouse_id')
                                ->first();
                            if ($vStock) {
                                $vStock->quantity -= $transfer->quantity;
                                if ($vStock->quantity < 0) $vStock->quantity = 0;
                                $vStock->save();
                            }

                            // Mirror deduction to branch product stock
                            $branchStock = BranchProductStock::where('branch_id', $transfer->from_id)
                                ->where('product_id', $transfer->product_id)
                                ->first();
                            if ($branchStock) {
                                $branchStock->quantity -= $transfer->quantity;
                                if ($branchStock->quantity < 0) $branchStock->quantity = 0;
                                $branchStock->save();
                            }
                        } else {
                            $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                                ->where('warehouse_id', $transfer->from_id)
                                ->whereNull('branch_id')
                                ->first();
                            if ($vStock) {
                                $vStock->quantity -= $transfer->quantity;
                                if ($vStock->quantity < 0) $vStock->quantity = 0;
                                $vStock->save();
                            }

                            // Mirror deduction to warehouse product stock
                            $warehouseStock = WarehouseProductStock::where('warehouse_id', $transfer->from_id)
                                ->where('product_id', $transfer->product_id)
                                ->first();
                            if ($warehouseStock) {
                                $warehouseStock->quantity -= $transfer->quantity;
                                if ($warehouseStock->quantity < 0) $warehouseStock->quantity = 0;
                                $warehouseStock->save();
                            }
                        }
                    } else {
                        if($transfer->from_type == 'branch'){
                            $branchStock = BranchProductStock::where('product_id', $transfer->product_id)->where('branch_id', $transfer->from_id)->first();
                            if ($branchStock) {
                                $branchStock->quantity -= $transfer->quantity;
                                if ($branchStock->quantity < 0) $branchStock->quantity = 0;
                                $branchStock->save();
                            }
                        }else{
                            $warehouseStock = WarehouseProductStock::where('product_id', $transfer->product_id)->where('warehouse_id', $transfer->from_id)->first();
                            if ($warehouseStock) {
                                $warehouseStock->quantity -= $transfer->quantity;
                                if ($warehouseStock->quantity < 0) $warehouseStock->quantity = 0;
                                $warehouseStock->save();
                            }
                        }
                    }

                }elseif($request->status == 'shipped' && $transfer->status == 'approved'){
                    $transfer->status = $request->status;
                    $transfer->shipped_by = auth()->id();
                    $transfer->shipped_at = now();
                    $transfer->save();
                }elseif($request->status == 'delivered' && in_array($transfer->status, ['shipped', 'approved'])){
                    $transfer->status = $request->status;
                    $transfer->delivered_by = auth()->id();
                    $transfer->delivered_at = now();
                    $transfer->save();

                    // Add Stock to Destination
                    if ($transfer->variation_id) {
                        if ($transfer->to_type == 'branch') {
                            $vStock = ProductVariationStock::firstOrNew([
                                'variation_id' => $transfer->variation_id,
                                'branch_id' => $transfer->to_id,
                                'warehouse_id' => null
                            ]);
                            $vStock->quantity = ($vStock->quantity ?? 0) + $transfer->quantity;
                            $vStock->updated_by = auth()->id();
                            $vStock->last_updated_at = now();
                            $vStock->save();

                            // Mirror addition to branch product stock
                            $branchStock = BranchProductStock::firstOrNew([
                                'branch_id'  => $transfer->to_id,
                                'product_id' => $transfer->product_id,
                            ]);
                            $branchStock->quantity = ($branchStock->quantity ?? 0) + $transfer->quantity;
                            $branchStock->updated_by = auth()->id();
                            $branchStock->last_updated_at = now();
                            $branchStock->save();
                        } else {
                            $vStock = ProductVariationStock::firstOrNew([
                                'variation_id' => $transfer->variation_id,
                                'warehouse_id' => $transfer->to_id,
                                'branch_id' => null
                            ]);
                            $vStock->quantity = ($vStock->quantity ?? 0) + $transfer->quantity;
                            $vStock->updated_by = auth()->id();
                            $vStock->last_updated_at = now();
                            $vStock->save();

                            // Mirror addition to warehouse product stock
                            $warehouseStock = WarehouseProductStock::firstOrNew([
                                'warehouse_id' => $transfer->to_id,
                                'product_id'   => $transfer->product_id,
                            ]);
                            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $transfer->quantity;
                            $warehouseStock->updated_by = auth()->id();
                            $warehouseStock->last_updated_at = now();
                            $warehouseStock->save();
                        }
                    } else {
                        if ($transfer->to_type == 'branch') {
                            $branchStock = BranchProductStock::firstOrNew([
                                'product_id' => $transfer->product_id,
                                'branch_id' => $transfer->to_id
                            ]);
                            $branchStock->quantity = ($branchStock->quantity ?? 0) + $transfer->quantity;
                            $branchStock->updated_by = auth()->id();
                            $branchStock->last_updated_at = now();
                            $branchStock->save();
                        } else {
                            $warehouseStock = WarehouseProductStock::firstOrNew([
                                'product_id' => $transfer->product_id,
                                'warehouse_id' => $transfer->to_id
                            ]);
                            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $transfer->quantity;
                            $warehouseStock->updated_by = auth()->id();
                            $warehouseStock->last_updated_at = now();
                            $warehouseStock->save();
                        }
                    }
                }elseif($request->status == 'rejected' && $transfer->status != 'delivered'){
                    $oldStatus = $transfer->status;
                    $transfer->status = $request->status;
                    $transfer->approved_by = null; 
                    $transfer->approved_at = null;
                    $transfer->shipped_by = null; 
                    $transfer->shipped_at = null;
                    $transfer->delivered_by = null; 
                    $transfer->delivered_at = null;
                    $transfer->save();
        
                    // Restore stock IF it was previously deducted (i.e. if it was approved/shipped)
                    if (in_array($oldStatus, ['approved', 'shipped'])) {
                        if ($transfer->variation_id) {
                            if($transfer->from_type == 'branch'){
                                $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                                    ->where('branch_id', $transfer->from_id)
                                    ->whereNull('warehouse_id')
                                    ->first();
                                if ($vStock) {
                                    $vStock->quantity += $transfer->quantity;
                                    $vStock->save();
                                }

                                // Mirror restoration to branch product stock
                                $branchStock = BranchProductStock::where('branch_id', $transfer->from_id)
                                    ->where('product_id', $transfer->product_id)
                                    ->first();
                                if ($branchStock) {
                                    $branchStock->quantity += $transfer->quantity;
                                    $branchStock->save();
                                }
                            } else {
                                $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                                    ->where('warehouse_id', $transfer->from_id)
                                    ->whereNull('branch_id')
                                    ->first();
                                if ($vStock) {
                                    $vStock->quantity += $transfer->quantity;
                                    $vStock->save();
                                }

                                // Mirror restoration to warehouse product stock
                                $warehouseStock = WarehouseProductStock::where('warehouse_id', $transfer->from_id)
                                    ->where('product_id', $transfer->product_id)
                                    ->first();
                                if ($warehouseStock) {
                                    $warehouseStock->quantity += $transfer->quantity;
                                    $warehouseStock->save();
                                }
                            }
                        } else {
                            if($transfer->from_type == 'branch'){
                                $branchStock = BranchProductStock::where('product_id', $transfer->product_id)->where('branch_id', $transfer->from_id)->first();
                                if ($branchStock) {
                                    $branchStock->quantity += $transfer->quantity;
                                    $branchStock->save();
                                }
                            } else {
                                $warehouseStock = WarehouseProductStock::where('product_id', $transfer->product_id)->where('warehouse_id', $transfer->from_id)->first();
                                if ($warehouseStock) {
                                    $warehouseStock->quantity += $transfer->quantity;
                                    $warehouseStock->save();
                                }
                            }
                        }
                    }
                }
            }

            // =====================================================
            // ACCOUNTING LOGIC: Move money from Receiver to Sender
            // =====================================================
            if ($request->status == 'delivered') {
                $totalBatchPaid = $transfers->sum('paid_amount');
                if ($totalBatchPaid > 0) {
                    $firstTransfer = $transfers->first();
                    $senderAccId = $firstTransfer->sender_account_id;
                    $receiverAccId = $firstTransfer->receiver_account_id;

                    if ($senderAccId && $receiverAccId) {
                        $senderAcc = FinancialAccount::find($senderAccId);
                        $receiverAcc = FinancialAccount::find($receiverAccId);

                        if ($senderAcc && $receiverAcc) {
                            // 1. Update Account Balances
                            $senderAcc->balance += $totalBatchPaid;
                            $senderAcc->save();

                            $receiverAcc->balance -= $totalBatchPaid;
                            $receiverAcc->save();

                            // 2. Create Journal Record
                            $voucherNo = 'STP-' . str_pad($primaryTransfer->id, 6, '0', STR_PAD_LEFT);
                            // Avoid duplicate voucher if re-processing (though guarded by status change)
                            if (!Journal::where('voucher_no', $voucherNo)->exists()) {
                                $journal = Journal::create([
                                    'voucher_no'     => $voucherNo,
                                    'entry_date'     => now(),
                                    'type'           => 'Transfer',
                                    'description'    => 'Payment for Stock Transfer #' . ($primaryTransfer->invoice_number ?? $primaryTransfer->id),
                                    'branch_id'      => $primaryTransfer->from_type == 'branch' ? $primaryTransfer->from_id : null,
                                    'voucher_amount' => $totalBatchPaid,
                                    'paid_amount'    => $totalBatchPaid,
                                    'reference'      => $primaryTransfer->invoice_number,
                                    'created_by'     => auth()->id(),
                                    'updated_by'     => auth()->id(),
                                ]);

                                // 3. DEBIT: Sender Account (Asset Increases)
                                JournalEntry::create([
                                    'journal_id'           => $journal->id,
                                    'chart_of_account_id'  => $senderAcc->account_id,
                                    'financial_account_id' => $senderAcc->id,
                                    'debit'                => $totalBatchPaid,
                                    'credit'               => 0,
                                    'memo'                 => 'Received payment for stock transfer',
                                    'created_by'           => auth()->id(),
                                    'updated_by'           => auth()->id(),
                                ]);

                                // 4. CREDIT: Receiver Account (Asset Decreases)
                                JournalEntry::create([
                                    'journal_id'           => $journal->id,
                                    'chart_of_account_id'  => $receiverAcc->account_id,
                                    'financial_account_id' => $receiverAcc->id,
                                    'debit'                => 0,
                                    'credit'               => $totalBatchPaid,
                                    'memo'                 => 'Paid for stock transfer receipt',
                                    'created_by'           => auth()->id(),
                                    'updated_by'           => auth()->id(),
                                ]);
                            }
                        }
                    }
                }
            }
            // =====================================================

            DB::commit();
            return redirect()->back()->with('success', 'Status updated for Invoice ' . ($primaryTransfer->invoice_number ?? $primaryTransfer->id));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $transfer = StockTransfer::findOrFail($id);

        // Only allow deletion if transfer is pending or rejected
        // Cannot delete approved, shipped, or delivered transfers as they affect stock
        if (!in_array($transfer->status, ['pending', 'rejected'])) {
            return redirect()->back()->with('error', 'Cannot delete transfer with status: ' . ucfirst($transfer->status) . '. Only pending or rejected transfers can be deleted.');
        }

        $transfer->delete();

        return redirect()->route('stocktransfer.list')->with('success', 'Stock transfer deleted successfully.');
    }
}
