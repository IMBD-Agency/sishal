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

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->applyFilters($request);
        $transfers = $query->orderBy('requested_at','desc')->paginate(15)->appends($request->except('page'));
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        $statuses = ['pending', 'approved', 'rejected', 'shipped', 'delivered'];
        $filters = $request->only(['search', 'from_branch_id', 'from_warehouse_id', 'to_branch_id', 'to_warehouse_id', 'status', 'date_from', 'date_to', 'variation_id', 'quick_filter']);
        return view('erp.stockTransfer.stockTransfer', compact('transfers', 'branches', 'warehouses', 'statuses', 'filters'));
    }

    private function applyFilters(Request $request)
    {
        $query = StockTransfer::with(['product.category', 'variation', 'fromBranch', 'fromWarehouse', 'toBranch', 'toWarehouse', 'requestedPerson', 'approvedPerson']);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
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
        $query = $this->applyFilters($request);
        $transfers = $query->orderBy('requested_at', 'desc')->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : ['id', 'date', 'product', 'source', 'destination', 'quantity', 'status', 'by'];

        $exportData = [];
        $headers = [];
        $columnMap = [
            'id' => 'ID',
            'date' => 'Date',
            'product' => 'Product',
            'source' => 'Source',
            'destination' => 'Destination',
            'quantity' => 'Quantity',
            'status' => 'Status',
            'by' => 'Requested By'
        ];

        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }
        $exportData[] = $headers;

        foreach ($transfers as $transfer) {
            $row = [];
            foreach ($selectedColumns as $column) {
                switch ($column) {
                    case 'id': $row[] = $transfer->id; break;
                    case 'date': $row[] = $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d-m-Y') : '-'; break;
                    case 'product': 
                        $prod = $transfer->product->name ?? '-';
                        if ($transfer->variation) $prod .= ' (' . $transfer->variation->name . ')';
                        $row[] = $prod;
                        break;
                    case 'source': 
                        if ($transfer->from_type == 'branch') $row[] = 'Branch: ' . ($transfer->fromBranch->name ?? '-');
                        else $row[] = 'Warehouse: ' . ($transfer->fromWarehouse->name ?? '-');
                        break;
                    case 'destination':
                        if ($transfer->to_type == 'branch') $row[] = 'Branch: ' . ($transfer->toBranch->name ?? '-');
                        else $row[] = 'Warehouse: ' . ($transfer->toWarehouse->name ?? '-');
                        break;
                    case 'quantity': $row[] = $transfer->quantity; break;
                    case 'status': $row[] = ucfirst($transfer->status); break;
                    case 'by': $row[] = $transfer->requestedPerson->name ?? '-'; break;
                }
            }
            $exportData[] = $row;
        }

        $filename = 'stock_transfers_' . date('Y-m-d_H-i-s') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Stock Transfer Report');
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
        $transfers = $query->orderBy('requested_at', 'desc')->get();
        $selectedColumns = $request->filled('columns') ? explode(',', $request->columns) : ['id', 'date', 'product', 'source', 'destination', 'quantity', 'status', 'by'];

        $columnMap = [
            'id' => 'ID',
            'date' => 'Date',
            'product' => 'Product',
            'source' => 'Source',
            'destination' => 'Destination',
            'quantity' => 'Quantity',
            'status' => 'Status',
            'by' => 'Requested By'
        ];

        $headers = [];
        foreach ($selectedColumns as $column) {
            if (isset($columnMap[$column])) {
                $headers[] = $columnMap[$column];
            }
        }

        $filename = 'stock_transfers_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.stockTransfer.report-pdf', [
            'transfers' => $transfers,
            'headers' => $headers,
            'selectedColumns' => $selectedColumns,
            'filters' => $request->all()
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }
        
        return $pdf->download($filename);
    }

    public function show($id)
    {
        $transfer = StockTransfer::with(['product.category', 'variation'])->findOrFail($id);
        return view('erp.stockTransfer.show', compact('transfer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_type' => 'required|in:branch,warehouse',
            'to_type' => 'required|in:branch,warehouse',
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|numeric|min:0.01',
            'type' => 'nullable|in:request,transfer',
            'status' => 'nullable|in:pending,approved,rejected,shipped,delivered',
            'notes' => 'nullable|string',
        ]);
        // Set from_id based on from_type
        if ($request->from_type === 'branch') {
            $validated['from_id'] = $request->from_branch_id;
        } elseif ($request->from_type === 'warehouse') {
            $validated['from_id'] = $request->from_warehouse_id;
        } else {
            $validated['from_id'] = null;
        }
        // Set to_id based on to_type
        if ($request->to_type === 'branch') {
            $validated['to_id'] = $request->to_branch_id;
        } elseif ($request->to_type === 'warehouse') {
            $validated['to_id'] = $request->to_warehouse_id;
        } else {
            $validated['to_id'] = null;
        }
        $validated['requested_by'] = auth()->id();
        $validated['requested_at'] = now();
        if (!isset($validated['type'])) $validated['type'] = 'transfer';
        if (!isset($validated['status'])) $validated['status'] = 'pending';
        $transfer = StockTransfer::create($validated);
        return redirect()->back()->with('success', 'Stock transfer created successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $transfer = StockTransfer::find($id);

        if($request->status == 'approved')
        {
            $transfer->status = $request->status;
            $transfer->approved_by = auth()->id();
            $transfer->approved_at = now();

            // Handle variation stock or regular product stock
            if ($transfer->variation_id) {
                // Use ProductVariationStock for variations
                if($transfer->from_type == 'branch'){
                    $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                        ->where('branch_id', $transfer->from_id)
                        ->whereNull('warehouse_id')
                        ->first();
                    $availableQty = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
                    if (!$vStock || $availableQty < $transfer->quantity) {
                        return redirect()->back()->with('error', 'Insufficient stock. Available: ' . $availableQty . ', Requested: ' . $transfer->quantity);
                    }
                    $vStock->quantity -= $transfer->quantity;
                    if ($vStock->quantity < 0) $vStock->quantity = 0;
                    $vStock->save();
                } else {
                    $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                        ->where('warehouse_id', $transfer->from_id)
                        ->whereNull('branch_id')
                        ->first();
                    $availableQty = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
                    if (!$vStock || $availableQty < $transfer->quantity) {
                        return redirect()->back()->with('error', 'Insufficient stock. Available: ' . $availableQty . ', Requested: ' . $transfer->quantity);
                    }
                    $vStock->quantity -= $transfer->quantity;
                    if ($vStock->quantity < 0) $vStock->quantity = 0;
                    $vStock->save();
                }
            } else {
                // Use regular BranchProductStock/WarehouseProductStock for products without variations
                if($transfer->from_type == 'branch'){
                    $branchStock = BranchProductStock::where('product_id', $transfer->product_id)->where('branch_id', $transfer->from_id)->first();
                    if ($branchStock && $branchStock->quantity >= $transfer->quantity) {
                        $branchStock->quantity -= $transfer->quantity;
                        $branchStock->save();
                    } else {
                        return redirect()->back()->with('error', 'Insufficient stock');
                    }
                }else{
                    $warehouseStock = WarehouseProductStock::where('product_id', $transfer->product_id)->where('warehouse_id', $transfer->from_id)->first();
                    if ($warehouseStock && $warehouseStock->quantity >= $transfer->quantity) {
                        $warehouseStock->quantity -= $transfer->quantity;
                        $warehouseStock->save();
                    } else {
                        return redirect()->back()->with('error', 'Insufficient stock');
                    }
                }
            }

        }elseif($request->status == 'shipped' && $transfer->status == 'approved'){
            $transfer->status = $request->status;
            $transfer->shipped_by = auth()->id();
            $transfer->shipped_at = now();
        }elseif($request->status == 'delivered' && $transfer->status == 'shipped'){
            $transfer->status = $request->status;
            $transfer->delivered_by = auth()->id();
            $transfer->delivered_at = now();

            // Handle variation stock or regular product stock
            if ($transfer->variation_id) {
                // Use ProductVariationStock for variations
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
                }
            } else {
                // Use regular BranchProductStock/WarehouseProductStock for products without variations
                if ($transfer->to_type == 'branch') {
                    $branchStock = BranchProductStock::firstOrNew([
                        'product_id' => $transfer->product_id,
                        'branch_id' => $transfer->to_id
                    ]);
                    $branchStock->quantity = ($branchStock->quantity ?? 0) + $transfer->quantity;
                    $branchStock->save();
                } else {
                    $warehouseStock = WarehouseProductStock::firstOrNew([
                        'product_id' => $transfer->product_id,
                        'warehouse_id' => $transfer->to_id,
                        'updated_by' => auth()->id()
                    ]);
                    $warehouseStock->quantity = ($warehouseStock->quantity ?? 0) + $transfer->quantity;
                    $warehouseStock->save();
                }
            }
        }elseif($request->status == 'rejected' && $transfer->status != 'delivered'){
            $transfer->status = $request->status;
            $transfer->approved_by = null;
            $transfer->approved_at = null;
            $transfer->shipped_by = null;
            $transfer->shipped_at = null;
            $transfer->delivered_by = null;
            $transfer->delivered_at = null;

            // Restore stock back to source location
            if ($transfer->variation_id) {
                // Use ProductVariationStock for variations
                if($transfer->from_type == 'branch'){
                    $vStock = ProductVariationStock::where('variation_id', $transfer->variation_id)
                        ->where('branch_id', $transfer->from_id)
                        ->whereNull('warehouse_id')
                        ->first();
                    if ($vStock) {
                        $vStock->quantity += $transfer->quantity;
                        $vStock->save();
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
                }
            } else {
                // Use regular BranchProductStock/WarehouseProductStock for products without variations
                if($transfer->from_type == 'branch'){
                    $branchStock = BranchProductStock::where('product_id', $transfer->product_id)->where('branch_id', $transfer->from_id)->first();
                    if ($branchStock) {
                        $branchStock->quantity += $transfer->quantity;
                        $branchStock->save();
                    }
                }else{
                    $warehouseStock = WarehouseProductStock::where('product_id', $transfer->product_id)->where('warehouse_id', $transfer->from_id)->first();
                    if ($warehouseStock) {
                        $warehouseStock->quantity += $transfer->quantity;
                        $warehouseStock->save();
                    }
                }
            }
        }else{
            $transfer->status = $request->status;
        }

        $transfer->save();

        return redirect()->back()->with('success', 'Transfer status updated successfully.');
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
