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
        $query = Purchase::with(['bill']);

        // Search by purchase id (supports partial match)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('id', 'like', "%$search%");
            });
        }
        // Filter by purchase date
        if ($request->filled('purchase_date')) {
            $query->whereDate('purchase_date', $request->purchase_date);
        }
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Quick Filter
        if ($request->filled('quick_filter')) {
            if ($request->quick_filter == 'today') {
                $query->whereDate('purchase_date', now()->toDateString());
            } elseif ($request->quick_filter == 'monthly') {
                $query->whereMonth('purchase_date', now()->month)
                      ->whereYear('purchase_date', now()->year);
            }
        }

        $purchases = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->all());
        $branches = Branch::all();
        return view('erp.purchases.purchaseList', [
            'purchases' => $purchases,
            'branches' => $branches,
            'filters' => $request->only(['search', 'purchase_date', 'status', 'quick_filter'])
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->applyFilters($request);
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
        $query = $this->applyFilters($request);
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

    private function applyFilters(Request $request)
    {
        $query = Purchase::with(['bill', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('id', 'like', "%$search%");
            });
        }
        if ($request->filled('purchase_date')) {
            $query->whereDate('purchase_date', $request->purchase_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('quick_filter')) {
            if ($request->quick_filter == 'today') {
                $query->whereDate('purchase_date', now()->toDateString());
            } elseif ($request->quick_filter == 'yesterday') {
                $query->whereDate('purchase_date', now()->subDay()->toDateString());
            } elseif ($request->quick_filter == 'last_7_days') {
                $query->whereBetween('purchase_date', [now()->subDays(7)->toDateString(), now()->toDateString()]);
            } elseif ($request->quick_filter == 'monthly') {
                $query->whereMonth('purchase_date', now()->month)
                      ->whereYear('purchase_date', now()->year);
            } elseif ($request->quick_filter == 'yearly') {
                $query->whereYear('purchase_date', now()->year);
            }
        }

        return $query;
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        $products = \App\Models\Product::all();
        return view('erp.purchases.create', compact('branches', 'warehouses', 'products'));
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
                PurchaseItem::create(attributes: [
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
                PurchaseBill::create([
                    'supplier_id'   => $request->supplier_id,
                    'purchase_id'   => $purchase->id,
                    'bill_date'     => now()->toDateString(),
                    'total_amount'  => $totalAmount,
                    'paid_amount'   => 0,
                    'due_amount'    => $totalAmount,
                    'status'        => 'unpaid',
                    'created_by'    => auth()->id(),
                    'description'   => 'Auto-generated bill from assign ID: ' . $purchase->id,
                ]);
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
        return view('erp.purchases.edit', compact('purchase', 'branches', 'warehouses'));
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
