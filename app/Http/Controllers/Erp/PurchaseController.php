<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\PurchaseBill;
use App\Models\PurchaseItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = \App\Models\PurchaseItem::with([
            'purchase.bill',
            'purchase.supplier',
            'product.category',
            'product.brand',
            'product.season',
            'product.gender',
            'variation.attributeValues.attribute',
            'returnItems'
        ]);

        $query = $this->applyFilters($query, $request, $startDate, $endDate);

        // Calculate Totals before pagination
        $totalQty = $query->sum('quantity');
        $totalAmount = $query->sum('total_price');

        $items = $query->latest()->paginate(20)->appends($request->all());
        
        // Dropdown Data
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $seasons = \App\Models\Season::orderBy('name')->get();
        $genders = \App\Models\Gender::orderBy('name')->get();
        $products = \App\Models\Product::where('type', 'product')->orderBy('name')->get();
        $branches = Branch::all();

        return view('erp.purchases.purchaseList', compact(
            'items', 'suppliers', 'categories', 'brands', 'seasons', 'genders', 'products', 
            'branches', 'reportType', 'startDate', 'endDate', 'totalQty', 'totalAmount'
        ));
    }

    public function exportExcel(Request $request)
    {
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = $this->applyFilters($request, $startDate, $endDate);
        $purchases = $query->orderBy('created_at', 'desc')->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : ['id', 'date', 'location', 'status', 'total'];

        $exportData = [];
        $headers = [];
        $columnMap = [
            'id' => 'Assign ID',
            'date' => 'Date',
            'location' => 'Location',
            'status' => 'Status',
            'total' => 'Total Amount'
        ];

        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }
        $exportData[] = $headers;

        foreach ($purchases as $purchase) {
            $row = [];
            foreach ($selectedColumns as $column) {
                switch ($column) {
                    case 'id': $row[] = '#' . $purchase->id; break;
                    case 'date': $row[] = $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') : '-'; break;
                    case 'location': 
                        $loc = '';
                        if ($purchase->ship_location_type == 'branch') {
                            $branch = Branch::find($purchase->location_id);
                            $loc = 'Branch: ' . ($branch->name ?? '-');
                        } else {
                            $warehouse = Warehouse::find($purchase->location_id);
                            $loc = 'Warehouse: ' . ($warehouse->name ?? '-');
                        }
                        $row[] = $loc;
                        break;
                    case 'status': $row[] = ucfirst($purchase->status); break;
                    case 'total': $row[] = number_format($purchase->items->sum('total_price'), 2); break;
                }
            }
            $exportData[] = $row;
        }

        $filename = 'purchase_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Purchase Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '3', $header);
            $sheet->getStyle(chr(65 + $index) . '3')->getFont()->setBold(true);
        }
        
        $dataRow = 4;
        foreach ($exportData as $rowIndex => $row) {
            if ($rowIndex === 0) continue;
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
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();
        } else {
            $startDate = $request->filled('start_date') ? \Carbon\Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->filled('end_date') ? \Carbon\Carbon::parse($request->end_date)->endOfDay() : null;
        }

        $query = $this->applyFilters($request, $startDate, $endDate);
        $purchases = $query->orderBy('created_at', 'desc')->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : ['id', 'date', 'location', 'status', 'total'];

        $columnMap = [
            'id' => 'Assign ID',
            'date' => 'Date',
            'location' => 'Location',
            'status' => 'Status',
            'total' => 'Total Amount'
        ];

        $headers = [];
        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }

        $filename = 'purchase_report_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.purchases.report-pdf', [
            'purchases' => $purchases,
            'headers' => $headers,
            'selectedColumns' => $selectedColumns,
            'filters' => $request->all()
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }
        
        return $pdf->download($filename);
    }

    private function applyFilters($query, Request $request, $startDate = null, $endDate = null)
    {
        // Date Filtering
        if ($startDate && $endDate) {
            $query->whereHas('purchase', function($q) use ($startDate, $endDate) {
                $q->whereBetween('purchase_date', [$startDate, $endDate]);
            });
        } elseif ($startDate) {
            $query->whereHas('purchase', function($q) use ($startDate) {
                $q->whereDate('purchase_date', '>=', $startDate);
            });
        } elseif ($endDate) {
            $query->whereHas('purchase', function($q) use ($endDate) {
                $q->whereDate('purchase_date', '<=', $endDate);
            });
        }

        // Search by purchase id / invoice
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('purchase', function($q) use ($search) {
                $q->where('id', 'LIKE', "%$search%")
                  ->orWhereHas('bill', function($bq) use ($search) {
                      $bq->where('bill_number', 'LIKE', "%$search%");
                  });
            });
        }

        // Filters from dropdowns
        if ($request->filled('supplier_id')) {
            $query->whereHas('purchase', function($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('purchase', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        
        // Filter by Product/Style/Category/Brand/Season/Gender
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);

        if ($request->filled('style_number') || $request->filled('category_id') || 
            $request->filled('brand_id') || $request->filled('season_id') || $request->filled('gender_id')) {
            
            $query->whereHas('product', function($q) use ($request) {
                if ($request->filled('style_number')) $q->where('style_number', 'like', '%' . $request->style_number . '%');
                if ($request->filled('category_id')) $q->where('category_id', $request->category_id);
                if ($request->filled('brand_id')) $q->where('brand_id', $request->brand_id);
                if ($request->filled('season_id')) $q->where('season_id', $request->season_id);
                if ($request->filled('gender_id')) $q->where('gender_id', $request->gender_id);
            });
        }

        return $query;
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        $products = \App\Models\Product::all();
        $suppliers = \App\Models\Supplier::all();
        // Use DB table directly safely if model is missing
        $bankAccounts = \DB::table('financial_accounts')->get();
        return view('erp.purchases.create', compact('branches', 'warehouses', 'products', 'suppliers', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|integer',
            'ship_location_type' => 'required|in:branch,warehouse',
            'location_id' => 'required|integer',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable', // allow string/int
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'account_id' => 'nullable|integer',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }
    
            // Create Purchase (supplier is optional)
            $purchase = Purchase::create([
                'supplier_id'         => $request->supplier_id ?? null,
                'ship_location_type'  => $request->ship_location_type,
                'location_id'         => $request->location_id,
                'purchase_date'       => $request->purchase_date,
                'status'              => 'pending',
                'created_by'          => auth()->id(),
                'notes'               => $request->notes,
            ]);
    
            // Add Purchase Items
            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $item['product_id'],
                    'variation_id' => !empty($item['variation_id']) ? $item['variation_id'] : null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['quantity'] * $item['unit_price'],
                    'description'     => $item['description'] ?? null,
                ]);
            }
    
            // Create Bill (only if supplier is provided)
            if ($request->supplier_id) {
                $paid_amount = $request->input('paid_amount', 0);
                $due_amount = max(0, $totalAmount - $paid_amount);
                $status = 'unpaid';
                if ($paid_amount >= $totalAmount) $status = 'paid';
                elseif ($paid_amount > 0) $status = 'partial';

                $bill = PurchaseBill::create([
                    'supplier_id'   => $request->supplier_id,
                    'purchase_id'   => $purchase->id,
                    'bill_date'     => now()->toDateString(),
                    'total_amount'  => $totalAmount,
                    'paid_amount'   => $paid_amount,
                    'due_amount'    => $due_amount,
                    'status'        => $status,
                    'created_by'    => auth()->id(),
                    'description'   => 'Auto-generated bill from purchase ID: ' . $purchase->id,
                ]);

                // Record Bill in Ledger (CREDIT the supplier)
                \App\Models\SupplierLedger::recordTransaction(
                    $request->supplier_id,
                    'credit',
                    $totalAmount,
                    'Purchase Bill: ' . ($bill->bill_number ?: 'P-'.$purchase->id),
                    $request->purchase_date,
                    $bill
                );

                // If payment made, record Payment and update Ledger
                if ($paid_amount > 0) {
                    $payment = \App\Models\SupplierPayment::create([
                        'supplier_id' => $request->supplier_id,
                        'purchase_bill_id' => $bill->id,
                        'payment_date' => $request->purchase_date,
                        'amount' => $paid_amount,
                        'payment_method' => $request->payment_method ?? 'cash',
                        'reference' => 'Initial payment at purchase',
                        'note' => $request->notes,
                        'created_by' => auth()->id(),
                    ]);

                    // Record Payment in Ledger (DEBIT the supplier)
                    \App\Models\SupplierLedger::recordTransaction(
                        $request->supplier_id,
                        'debit',
                        $paid_amount,
                        'Payment for Purchase Bill: ' . ($bill->bill_number ?: 'P-'.$purchase->id),
                        $request->purchase_date,
                        $payment
                    );
                }
            }
    
            DB::commit();
    
            return redirect()->route('purchase.list')->with('success', 'Purchase created successfully.');
    
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['bill', 'supplier', 'items.product', 'items.variation'])->findOrFail($id);

        // Safely resolve location name; the related branch/warehouse might not exist anymore
        if ($purchase->ship_location_type === 'branch') {
            $branch = Branch::find($purchase->location_id);
            $purchase->location_name = $branch?->name ?? 'Unknown Branch';
        } elseif ($purchase->ship_location_type === 'warehouse') {
            $warehouse = Warehouse::find($purchase->location_id);
            $purchase->location_name = $warehouse?->name ?? 'Unknown Warehouse';
        } else {
            $purchase->location_name = 'Unknown Location';
        }

        return view('erp.purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        $suppliers = \App\Models\Supplier::all();
        return view('erp.purchases.edit', compact('purchase', 'branches', 'warehouses', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'nullable|integer',
            'ship_location_type' => 'required|in:branch,warehouse',
            'location_id' => 'required|integer',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($id);
            $previousStatus = $purchase->status;

            $purchase->update([
                'supplier_id'         => $request->supplier_id ?? null,
                'ship_location_type'  => $request->ship_location_type,
                'location_id'         => $request->location_id,
                'purchase_date'       => $request->purchase_date,
                'status'              => $request->status,
                'notes'               => $request->notes,
            ]);

            // Only add stock the first time we move into "received" status
            if ($request->status === 'received' && $previousStatus !== 'received') {
                foreach ($purchase->items as $item) {
                    if ($item->variation_id) {
                        // Update detailed variation stock
                        if ($purchase->ship_location_type === 'branch') {
                            $stock = \App\Models\ProductVariationStock::firstOrNew([
                                'variation_id' => $item->variation_id,
                                'branch_id' => $purchase->location_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();

                            // Also mirror into branch product stock so POS can see this product
                            $branchStock = \App\Models\BranchProductStock::firstOrNew([
                                'branch_id'  => $purchase->location_id,
                                'product_id' => $item->product_id,
                            ]);
                            $branchStock->quantity = ($branchStock->quantity ?? 0) + $item->quantity;
                            $branchStock->updated_by = auth()->id() ?? 1;
                            $branchStock->last_updated_at = now();
                            $branchStock->save();
                        } elseif ($purchase->ship_location_type === 'warehouse') {
                            $stock = \App\Models\ProductVariationStock::firstOrNew([
                                'variation_id' => $item->variation_id,
                                'warehouse_id' => $purchase->location_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();

                            // Mirror into warehouse product stock so non-variation flows can see it
                            $warehouseStock = \App\Models\WarehouseProductStock::firstOrNew([
                                'warehouse_id' => $purchase->location_id,
                                'product_id'   => $item->product_id,
                            ]);
                            $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $item->quantity;
                            $warehouseStock->updated_by = auth()->id() ?? 1;
                            $warehouseStock->last_updated_at = now();
                            $warehouseStock->save();
                        }
                    } else {
                        // Simple (non-variation) products: existing behavior
                        if ($purchase->ship_location_type === 'branch') {
                            $stock = \App\Models\BranchProductStock::firstOrNew([
                                'branch_id' => $purchase->location_id,
                                'product_id' => $item->product_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();
                        } elseif ($purchase->ship_location_type === 'warehouse') {
                            $stock = \App\Models\WarehouseProductStock::firstOrNew([
                                'warehouse_id' => $purchase->location_id,
                                'product_id' => $item->product_id,
                            ]);
                            $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                            $stock->updated_by = auth()->id() ?? 1;
                            $stock->last_updated_at = now();
                            $stock->save();
                        }
                    }
                }
            }

            // Remove old items
            $purchase->items()->delete();
            // Add new items
            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'   => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['quantity'] * $item['unit_price'],
                    'description'  => $item['description'] ?? null,
                ]);
            }
            // Optionally update bill if needed (not shown here)
            DB::commit();
            return redirect()->route('purchase.list')->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $purchase = Purchase::with('items')->findOrFail($id);
        $previousStatus = $purchase->status;
        $purchase->status = $request->status;
        $purchase->save();

        // Only add stock the first time we move into "received" status
        if ($request->status === 'received' && $previousStatus !== 'received') {
            foreach ($purchase->items as $item) {
                if ($item->variation_id) {
                    if ($purchase->ship_location_type === 'branch') {
                        $stock = \App\Models\ProductVariationStock::firstOrNew([
                            'variation_id' => $item->variation_id,
                            'branch_id' => $purchase->location_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();

                        // Mirror into branch product stock so POS can see this product
                        $branchStock = \App\Models\BranchProductStock::firstOrNew([
                            'branch_id'  => $purchase->location_id,
                            'product_id' => $item->product_id,
                        ]);
                        $branchStock->quantity = ($branchStock->quantity ?? 0) + $item->quantity;
                        $branchStock->updated_by = auth()->id() ?? 1;
                        $branchStock->last_updated_at = now();
                        $branchStock->save();
                    } elseif ($purchase->ship_location_type === 'warehouse') {
                        $stock = \App\Models\ProductVariationStock::firstOrNew([
                            'variation_id' => $item->variation_id,
                            'warehouse_id' => $purchase->location_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();

                        // Mirror into warehouse product stock so other flows can see it
                        $warehouseStock = \App\Models\WarehouseProductStock::firstOrNew([
                            'warehouse_id' => $purchase->location_id,
                            'product_id'   => $item->product_id,
                        ]);
                        $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $item->quantity;
                        $warehouseStock->updated_by = auth()->id() ?? 1;
                        $warehouseStock->last_updated_at = now();
                        $warehouseStock->save();
                    }
                } else {
                    if ($purchase->ship_location_type === 'branch') {
                        $stock = \App\Models\BranchProductStock::firstOrNew([
                            'branch_id' => $purchase->location_id,
                            'product_id' => $item->product_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();
                    } elseif ($purchase->ship_location_type === 'warehouse') {
                        $stock = \App\Models\WarehouseProductStock::firstOrNew([
                            'warehouse_id' => $purchase->location_id,
                            'product_id' => $item->product_id,
                        ]);
                        $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                        $stock->updated_by = auth()->id() ?? 1;
                        $stock->last_updated_at = now();
                        $stock->save();
                    }
                }
            }
        }
        return redirect()->back()->with('success', 'Purchase status updated successfully.');
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::with(['items', 'bill'])->findOrFail($id);
            // Delete related items
            $purchase->items()->delete();
            // Delete related bill
            if ($purchase->bill) {
                $purchase->bill->delete();
            }
            // Delete the purchase itself
            $purchase->delete();
            DB::commit();
            return redirect()->route('purchase.list')->with('success', 'Purchase and related data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
        }
    }

    public function searchPurchase(Request $request)
    {
        $search = $request->q;
        $query = Purchase::query();
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('id', 'like', "%$search%");
            });
        }
        $purchases = $query->limit(20)->get()->filter();
        $results = $purchases->filter(function($purchase) {
            return $purchase !== null;
        })->map(function($purchase) {
            $text = "#{$purchase->id} - Assign ({$purchase->purchase_date})";
            return [
                'id' => $purchase->id,
                'text' => $text
            ];
        });
        return response()->json(['results' => $results]);
    }

    public function getItemByPurchase($id)
    {
        $purchaseItems = \App\Models\PurchaseItem::with('product')
            ->where('purchase_id', $id)
            ->get();

        $results = $purchaseItems->map(function($item) {
            return [
                'id' => $item->id,
                'text' => "#{$item->id} - {$item->product->name} (Qty: {$item->quantity})",
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'description' => $item->description,
            ];
        });

        return response()->json(['results' => $results]);
    }

}
