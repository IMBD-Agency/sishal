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
        $purchases = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->all());
        return view('erp.purchases.purchaseList', [
            'purchases' => $purchases,
            'filters' => $request->only(['search', 'purchase_date', 'status'])
        ]);
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
            'items.*.variation_id' => 'nullable|integer',
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
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'total_price'  => $item['quantity'] * $item['unit_price'],
                    'description'     => $item['description'],
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
                    'description'   => 'Auto-generated bill from purchase ID: ' . $purchase->id,
                ]);
            }
    
            DB::commit();
    
            return redirect()->route('purchase.list');
    
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
            $text = "#{$purchase->id} - Purchase ({$purchase->purchase_date})";
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
