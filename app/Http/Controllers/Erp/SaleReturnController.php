<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaleReturn;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Pos;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleReturn::query();

        // Search by customer name, phone, email, or POS sale_number
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($qc) use ($search) {
                    $qc->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                })
                ->orWhereHas('posSale', function($qp) use ($search) {
                    $qp->where('sale_number', 'like', "%$search%");
                });
            });
        }

        // Filter by Date Range
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate) {
            $query->whereDate('return_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('return_date', '<=', $endDate);
        }

        // Quick Filters
        if ($request->has('quick_filter')) {
            $filter = $request->input('quick_filter');
            if ($filter == 'today') {
                $query->whereDate('return_date', Carbon::today());
            } elseif ($filter == 'monthly') {
                $query->whereMonth('return_date', Carbon::now()->month)
                      ->whereYear('return_date', Carbon::now()->year);
            }
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by branch
        if ($branchId = $request->input('branch_id')) {
            $query->where('return_to_type', 'branch')->where('return_to_id', $branchId);
        }

        // Filter by warehouse
        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('return_to_type', 'warehouse')->where('return_to_id', $warehouseId);
        }

        $returns = $query->with(['customer', 'posSale', 'invoice.order', 'branch', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $statuses = ['pending', 'approved', 'rejected', 'processed'];
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        $filters = $request->all();

        return view('erp.saleReturn.salereturnlist', compact('returns', 'statuses', 'filters', 'branches', 'warehouses'));
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = SaleReturn::with(['customer', 'posSale', 'branch', 'warehouse', 'items']);
        $this->applyFilters($query, $request);
        $returns = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sale Returns');

        // Headers
        $headers = ['Return ID', 'Date', 'Customer', 'POS Sale', 'Location', 'Items Count', 'Total Amount', 'Status'];
        foreach ($headers as $key => $header) {
            $cell = chr(65 + $key) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        }

        // Data
        $row = 2;
        foreach ($returns as $return) {
            $location = 'N/A';
            if ($return->return_to_type == 'branch') $location = 'Branch: ' . ($return->branch->name ?? 'N/A');
            elseif ($return->return_to_type == 'warehouse') $location = 'Warehouse: ' . ($return->warehouse->name ?? 'N/A');
            elseif ($return->return_to_type == 'employee') $location = 'Employee: ' . ($return->employee->user->first_name ?? 'N/A');

            $sheet->setCellValue('A' . $row, '#SR-' . str_pad($return->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $return->return_date);
            $sheet->setCellValue('C' . $row, $return->customer->name ?? 'Walk-in');
            $sheet->setCellValue('D' . $row, $return->posSale->sale_number ?? 'N/A');
            $sheet->setCellValue('E' . $row, $location);
            $sheet->setCellValue('F' . $row, $return->items->count());
            $sheet->setCellValue('G' . $row, number_format($return->items->sum('total_price'), 2));
            $sheet->setCellValue('H' . $row, ucfirst($return->status));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'sale_returns_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    /**
     * Export to PDF or Stream (Print)
     */
    public function exportPdf(Request $request)
    {
        $query = SaleReturn::with(['customer', 'posSale', 'branch', 'warehouse', 'items']);
        $this->applyFilters($query, $request);
        $returns = $query->get();

        $pdf = Pdf::loadView('erp.saleReturn.report-pdf', [
            'returns' => $returns,
            'filters' => $request->all(),
            'date' => date('d M, Y')
        ]);

        $pdf->setPaper('A4', 'landscape');
        $filename = 'sale_returns_' . date('Ymd_His') . '.pdf';

        if ($request->input('action') === 'print') {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Helper to apply filters shared between index and exports
     */
    private function applyFilters($query, Request $request)
    {
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($qc) use ($search) {
                    $qc->where('name', 'like', "%$search%");
                })->orWhereHas('posSale', function($qp) use ($search) {
                    $qp->where('sale_number', 'like', "%$search%");
                });
            });
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate) $query->whereDate('return_date', '>=', $startDate);
        if ($endDate) $query->whereDate('return_date', '<=', $endDate);

        if ($status = $request->input('status')) $query->where('status', $status);
        if ($branchId = $request->input('branch_id')) $query->where('return_to_type', 'branch')->where('return_to_id', $branchId);
        if ($warehouseId = $request->input('warehouse_id')) $query->where('return_to_type', 'warehouse')->where('return_to_id', $warehouseId);
    }

    public function create(Request $request)
    {
        $customers = Customer::all();
        $posSales = Pos::all();
        $invoices = Invoice::all();
        $products = \App\Models\Product::all();
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        
        // Handle pre-selected POS sale from query parameter
        $selectedPosSale = null;
        if ($request->has('pos_sale_id')) {
            $selectedPosSale = Pos::with(['customer', 'items.product', 'items.variation', 'branch', 'invoice'])
                ->find($request->pos_sale_id);
        }
        
        return view('erp.saleReturn.create', compact('customers', 'posSales', 'invoices', 'products', 'branches', 'warehouses', 'selectedPosSale'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'pos_sale_id' => 'nullable|exists:pos,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'return_date' => 'required|date',
            'refund_type' => 'required|in:none,cash,bank,credit',
            'return_to_type' => 'required|in:branch,warehouse,employee',
            'return_to_id' => 'required|integer',
            'reason' => 'nullable|string',
            'processed_by' => 'nullable|exists:users,id',
            'processed_at' => 'nullable|date',
            'account_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);
        $data = $request->except(['items', 'status']);
        $data['status'] = 'pending';
        $saleReturn = SaleReturn::create($data);
        foreach ($request->items as $item) {
            \App\Models\SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'sale_item_id' => $item['sale_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'returned_qty' => $item['returned_qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['returned_qty'] * $item['unit_price'],
                'reason' => $item['reason'] ?? null,
            ]);
        }
        return redirect()->route('saleReturn.list')->with('success', 'Sale return created successfully.');
    }

    public function show($id)
    {
        $saleReturn = SaleReturn::with([
            'customer',
            'posSale',
            'items.product',
            'items.variation',
            'employee.user',
            'branch',
            'warehouse'
        ])->findOrFail($id);
        return view('erp.saleReturn.show', compact('saleReturn'));
    }

    public function edit($id)
    {
        $saleReturn = SaleReturn::with(['items', 'employee.user'])->findOrFail($id);
        $customers = Customer::all();
        $posSales = Pos::all();
        $invoices = Invoice::all();
        $products = \App\Models\Product::all();
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        return view('erp.saleReturn.edit', compact('saleReturn', 'customers', 'posSales', 'invoices', 'products', 'branches', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'pos_sale_id' => 'nullable|exists:pos,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'return_date' => 'required|date',
            'refund_type' => 'required|in:none,cash,bank,credit',
            'return_to_type' => 'required|in:branch,warehouse,employee',
            'return_to_id' => 'required|integer',
            'reason' => 'nullable|string',
            'processed_by' => 'nullable|exists:users,id',
            'processed_at' => 'nullable|date',
            'account_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);
        $saleReturn = SaleReturn::findOrFail($id);
        $saleReturn->update($request->except(['items', 'status']));
        // Remove old items
        $saleReturn->items()->delete();
        // Add new items
        foreach ($request->items as $item) {
            \App\Models\SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'sale_item_id' => $item['sale_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'returned_qty' => $item['returned_qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['returned_qty'] * $item['unit_price'],
                'reason' => $item['reason'] ?? null,
            ]);
        }
        return redirect()->route('saleReturn.list')->with('success', 'Sale return updated successfully.');
    }

    public function destroy($id)
    {
        $saleReturn = SaleReturn::findOrFail($id);
        $saleReturn->delete();
        return redirect()->route('saleReturn.list')->with('success', 'Sale return deleted successfully.');
    }

    /**
     * Change the status of a sale return. If processed, add returned quantity to the selected stock.
     */
    public function updateReturnStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,processed',
            'notes' => 'nullable|string|max:500'
        ]);

        $saleReturn = SaleReturn::with(['items'])->findOrFail($id);

        // Prevent re-processing
        if ($saleReturn->status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Sale return is already processed and cannot be updated.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $saleReturn->status;
            $newStatus = $request->status;
            $updateData = ['status' => $newStatus];

            // Add notes if provided
            if ($request->filled('notes')) {
                $currentNotes = $saleReturn->notes ? $saleReturn->notes . "\n" : "";
                $updateData['notes'] = $currentNotes . "[" . now()->format('Y-m-d H:i:s') . "] Status changed to " . ucfirst($newStatus) . ": " . $request->notes;
            }

            $saleReturn->update($updateData);

            // If status is being processed, adjust stock (add returned qty)
            if ($newStatus === 'processed') {
                foreach ($saleReturn->items as $item) {
                    $this->addStockForReturnItem($saleReturn, $item);
                }
            }

            DB::commit();

            $statusMessage = match($newStatus) {
                'approved' => 'Sale return has been approved successfully.',
                'rejected' => 'Sale return has been rejected.',
                'processed' => 'Sale return has been processed and stock has been updated.',
                default => 'Sale return status has been updated.'
            };

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'data' => [
                    'id' => $saleReturn->id,
                    'status' => $saleReturn->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sale return status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add returned quantity to the selected stock (branch, warehouse, or employee)
     */
    private function addStockForReturnItem($saleReturn, $item)
    {
        $qty = $item->returned_qty;
        $productId = $item->product_id;
        $variationId = $item->variation_id ?? null;
        $toType = $saleReturn->return_to_type;
        $toId = $saleReturn->return_to_id;

        switch ($toType) {
            case 'branch':
                if ($variationId) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $toId)
                        ->whereNull('warehouse_id')
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'branch_id' => $toId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                } else {
                    $stock = \App\Models\BranchProductStock::where('branch_id', $toId)
                        ->where('product_id', $productId)
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\BranchProductStock::create([
                            'branch_id' => $toId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                }
                break;
            case 'warehouse':
                if ($variationId) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('warehouse_id', $toId)
                        ->whereNull('branch_id')
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'warehouse_id' => $toId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                } else {
                    $stock = \App\Models\WarehouseProductStock::where('warehouse_id', $toId)
                        ->where('product_id', $productId)
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                    } else {
                        \App\Models\WarehouseProductStock::create([
                            'warehouse_id' => $toId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id()
                        ]);
                    }
                }
                break;
            case 'employee':
                $stock = \App\Models\EmployeeProductStock::where('employee_id', $toId)
                    ->where('product_id', $productId)
                    ->first();
                if ($stock) {
                    $stock->increment('quantity', $qty);
                } else {
                    \App\Models\EmployeeProductStock::create([
                        'employee_id' => $toId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'issued_by' => auth()->id()
                    ]);
                }
                break;
            default:
                throw new \Exception("Invalid return_to_type: {$toType}");
        }
    }
} 