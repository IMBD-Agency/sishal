<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Warehouse;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequisitionController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view requisitions')) {
            abort(403, 'Unauthorized action.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $query = Requisition::with(['branch', 'warehouse', 'creator']);

        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('requisition_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('requisition_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('requisition_number', 'LIKE', "%$search%");
        }

        $requisitions = $query->latest()->paginate(20)->appends($request->all());

        if ($request->ajax()) {
            return view('erp.requisition.partials.table', compact('requisitions'));
        }

        $branches = Branch::all();
        return view('erp.requisition.index', compact('requisitions', 'restrictedBranchId', 'branches'));
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view requisitions')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Requisition::with(['branch', 'warehouse', 'creator']);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->whereDate('requisition_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('requisition_date', '<=', $request->end_date);
        if ($request->filled('search')) $query->where('requisition_number', 'LIKE', "%{$request->search}%");

        $requisitions = $query->latest()->get();

        $headers = ['Req #', 'Branch', 'Warehouse', 'Date', 'Status', 'Requested By'];
        $data[] = $headers;

        foreach ($requisitions as $req) {
            $data[] = [
                $req->requisition_number,
                $req->branch->name,
                $req->warehouse->name,
                $req->requisition_date,
                strtoupper(str_replace('_', ' ', $req->status)),
                $req->creator->name
            ];
        }

        $filename = 'requisitions_' . date('Y-m-d_His') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path('app/public/' . $filename);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view requisitions')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Requisition::with(['branch', 'warehouse', 'creator']);

        $restrictedBranchId = $this->getRestrictedBranchId();
        if ($restrictedBranchId) {
            $query->where('branch_id', $restrictedBranchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->whereDate('requisition_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('requisition_date', '<=', $request->end_date);
        if ($request->filled('search')) $query->where('requisition_number', 'LIKE', "%{$request->search}%");

        $requisitions = $query->latest()->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.requisition.report-pdf', compact('requisitions'));
        return $pdf->download('requisitions_' . date('Y-m-d_His') . '.pdf');
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $restrictedBranchId = $this->getRestrictedBranchId();
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        $products = Product::where('status', 'active')->get();

        return view('erp.requisition.create', compact('branches', 'warehouses', 'products', 'restrictedBranchId'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'requisition_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $restrictedBranchId = $this->getRestrictedBranchId();
            $branchId = $restrictedBranchId ?: $request->branch_id;

            if (!$branchId) {
                return back()->with('error', 'Branch is required.');
            }

            // Generate requisition number
            $today = date('Ymd');
            $lastRequisition = Requisition::where('requisition_number', 'like', "REQ-{$today}-%")
                ->orderBy('requisition_number', 'desc')
                ->first();
            
            if ($lastRequisition && preg_match('/REQ-\d{8}-(\d+)/', $lastRequisition->requisition_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }
            $requisitionNumber = 'REQ-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $requisition = Requisition::create([
                'requisition_number' => $requisitionNumber,
                'branch_id' => $branchId,
                'warehouse_id' => $request->warehouse_id,
                'requisition_date' => $request->requisition_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('requisition.index')->with('success', 'Requisition created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $requisition = Requisition::with([
            'items.product.category',
            'items.variation.combinations.attribute',
            'items.variation.combinations.attributeValue',
            'branch',
            'warehouse',
            'creator',
        ])->findOrFail($id);
        return view('erp.requisition.show', compact('requisition'));
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $requisition = Requisition::with([
            'items.product',
            'items.variation.combinations.attribute',
            'items.variation.combinations.attributeValue',
        ])->findOrFail($id);

        if ($requisition->status !== 'pending') {
            return redirect()->route('requisition.show', $id)
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $restrictedBranchId = $this->getRestrictedBranchId();
        $branches   = Branch::all();
        $warehouses = Warehouse::all();

        return view('erp.requisition.edit', compact('requisition', 'branches', 'warehouses', 'restrictedBranchId'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $requisition = Requisition::findOrFail($id);

        if ($requisition->status !== 'pending') {
            return redirect()->route('requisition.show', $id)
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $request->validate([
            'warehouse_id'      => 'required|exists:warehouses,id',
            'requisition_date'  => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $requisition->update([
                'warehouse_id'     => $request->warehouse_id,
                'requisition_date' => $request->requisition_date,
                'notes'            => $request->notes,
            ]);

            // Replace all items fresh
            $requisition->items()->delete();

            foreach ($request->items as $item) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'product_id'     => $item['product_id'],
                    'variation_id'   => $item['variation_id'] ?: null,
                    'quantity'       => (int) $item['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('requisition.show', $id)
                ->with('success', 'Requisition updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('manage requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $requisition = Requisition::findOrFail($id);
        
        if ($requisition->status !== 'pending') {
            return back()->with('error', 'Only pending requisitions can be deleted.');
        }

        $requisition->delete();
        return redirect()->route('requisition.index')->with('success', 'Requisition deleted successfully.');
    }

    /**
     * Handle fulfillment of requisition items (Stock Transfer only).
     * Purchases are done manually via the Purchases module.
     */
    public function fulfill(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('process requisitions')) {
            abort(403, 'Unauthorized action.');
        }
        $requisition = Requisition::with('items.product', 'items.variation')->findOrFail($id);

        $request->validate([
            'items'        => 'required|array',
            'items.*.type' => 'required|in:transfer,skip',
            'items.*.qty'  => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transferItems = [];

            foreach ($request->items as $itemId => $data) {
                if ($data['type'] === 'skip' || (int)$data['qty'] <= 0) continue;

                $reqItem = RequisitionItem::findOrFail($itemId);

                $pending      = $reqItem->quantity - $reqItem->fulfilled_quantity;
                $qtyToFulfill = min((int)$data['qty'], $pending);

                if ($qtyToFulfill <= 0) continue;

                if ($data['type'] === 'transfer') {
                    $transferItems[] = ['req_item' => $reqItem, 'qty' => $qtyToFulfill];
                }
            }

            if (!empty($transferItems)) {
                $today       = date('Ymd');
                $lastInvoice = StockTransfer::where('invoice_number', 'like', "TRF-{$today}-%")
                    ->orderBy('invoice_number', 'desc')->first();

                $nextNumber    = ($lastInvoice && preg_match('/TRF-\d{8}-(\d+)/', $lastInvoice->invoice_number, $m))
                    ? intval($m[1]) + 1 : 1;
                $invoiceNumber = 'TRF-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                foreach ($transferItems as $itemData) {
                    $reqItem   = $itemData['req_item'];
                    $qty       = $itemData['qty'];
                    $unitPrice = $reqItem->variation
                        ? ($reqItem->variation->cost ?: $reqItem->product->cost)
                        : $reqItem->product->cost;

                    StockTransfer::create([
                        'from_type'            => 'warehouse',
                        'from_id'              => $requisition->warehouse_id,
                        'to_type'              => 'branch',
                        'to_id'                => $requisition->branch_id,
                        'product_id'           => $reqItem->product_id,
                        'variation_id'         => $reqItem->variation_id,
                        'quantity'             => $qty,
                        'unit_price'           => $unitPrice,
                        'total_price'          => $qty * $unitPrice,
                        'due_amount'           => $qty * $unitPrice,
                        'status'               => 'pending',
                        'requested_at'         => now(),
                        'requested_by'         => auth()->id(),
                        'invoice_number'       => $invoiceNumber,
                        'requisition_item_id'  => $reqItem->id,
                    ]);

                    $reqItem->increment('fulfilled_quantity', $qty);
                }
            }

            // Update requisition status
            $requisition->refresh();
            $allFulfilled = $anyFulfilled = false;
            foreach ($requisition->items as $item) {
                if ($item->fulfilled_quantity >= $item->quantity) {
                    $anyFulfilled = true;
                } else {
                    if ($item->fulfilled_quantity > 0) $anyFulfilled = true;
                }
            }
            $allFulfilled = $requisition->items->every(fn($i) => $i->fulfilled_quantity >= $i->quantity);
            $anyFulfilled = $requisition->items->some(fn($i)  => $i->fulfilled_quantity > 0);

            if ($allFulfilled) {
                $requisition->update(['status' => 'fulfilled']);
            } elseif ($anyFulfilled) {
                $requisition->update(['status' => 'partially_fulfilled']);
            }

            DB::commit();
            return redirect()->route('requisition.show', $id)->with('success', 'Stock transfer initiated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Fulfillment failed: ' . $e->getMessage());
        }
    }
}
