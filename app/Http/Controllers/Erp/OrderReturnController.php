<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderReturnController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view returns')) {
            abort(403, 'Unauthorized action.');
        }

        $query = OrderReturn::query();

        // Search by customer name, phone, email, or Order number
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($qc) use ($search) {
                    $qc->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                })
                ->orWhereHas('order', function($qp) use ($search) {
                    $qp->where('order_number', 'like', "%$search%");
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

        $returns = $query->with(['customer', 'order', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $statuses = ['pending', 'approved', 'rejected', 'processed'];
        $filters = $request->all();

        return view('erp.orderReturn.orderreturnlist', compact('returns', 'statuses', 'filters'));
    }

    public function create(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage returns')) {
            abort(403, 'Unauthorized action.');
        }
        $customers = Customer::all();
        $orders = Order::orderBy('created_at', 'desc')->take(100)->get();
        $invoices = Invoice::all();
        $products = \App\Models\Product::all();
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        
        $preSelectedOrder = null;
        if ($request->has('order_id')) {
            $preSelectedOrder = Order::with(['customer', 'items.product', 'items.variation'])->find($request->order_id);
        }

        $generalSettings = \App\Models\GeneralSetting::first();
        
        return view('erp.orderReturn.create', compact(
            'customers', 'orders', 'invoices', 'products', 'branches', 'warehouses', 
            'preSelectedOrder', 'generalSettings'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage returns')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'order_id' => 'nullable|exists:orders,id',
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
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
            'new_items' => 'nullable|array',
            'new_items.*.product_id' => 'required_with:new_items|exists:products,id',
            'new_items.*.qty' => 'required_with:new_items|numeric|min:1',
            'new_items.*.unit_price' => 'required_with:new_items|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Validate return quantities against order if order_id is provided
            if ($request->order_id) {
                $this->validateReturnQuantities($request->order_id, $request->items);
            }

            $data = $request->except(['items', 'status', 'new_items']);
            $data['status'] = 'pending';
            $orderReturn = OrderReturn::create($data);

            $totalReturnValue = 0;

            foreach ($request->items as $item) {
                $total = $item['returned_qty'] * $item['unit_price'];
                $totalReturnValue += $total;
                
                \App\Models\OrderReturnItem::create([
                    'order_return_id' => $orderReturn->id,
                    'order_item_id' => $item['order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'returned_qty' => $item['returned_qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $total,
                    'reason' => $item['reason'] ?? null,
                ]);
            }


            DB::commit();
            
            return redirect()->route('orderReturn.show', $orderReturn->id)->with('success', 'Order return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order Return Creation Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create return: ' . $e->getMessage())->withInput();
        }
    }



    public function show($id)
    {
        if (!auth()->user()->hasPermissionTo('view returns')) {
            abort(403, 'Unauthorized action.');
        }
        $orderReturn = OrderReturn::with(['items.product', 'items.variation', 'employee.user'])->findOrFail($id);
        
        // Find related exchange order if exists
        $exchangeOrder = Order::where('notes', 'like', "%Exchange Order for Return #{$id}%")
            ->with(['items.product', 'items.variation', 'invoice'])
            ->first();

        return view('erp.orderReturn.show', compact('orderReturn', 'exchangeOrder'));
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('manage returns')) {
            abort(403, 'Unauthorized action.');
        }
        $orderReturn = OrderReturn::with(['items', 'employee.user'])->findOrFail($id);
        $customers = Customer::all();
        $orders = Order::all();
        $invoices = Invoice::all();
        $products = \App\Models\Product::all();
        $branches = \App\Models\Branch::all();
        $warehouses = \App\Models\Warehouse::all();
        return view('erp.orderReturn.edit', compact('orderReturn', 'customers', 'orders', 'invoices', 'products', 'branches', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage returns')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'order_id' => 'nullable|exists:orders,id',
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
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        $orderReturn = OrderReturn::findOrFail($id);

        // Prevent editing if already processed
        if ($orderReturn->status === 'processed') {
            return redirect()->back()->withErrors(['error' => 'Cannot edit a processed return.']);
        }

        // Validate return quantities against order if order_id is provided
        if ($request->order_id) {
            $this->validateReturnQuantities($request->order_id, $request->items, $id);
        }

        $orderReturn->update($request->except(['items', 'status']));
        // Remove old items
        $orderReturn->items()->delete();
        // Add new items
        foreach ($request->items as $item) {
            \App\Models\OrderReturnItem::create([
                'order_return_id' => $orderReturn->id,
                'order_item_id' => $item['order_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'returned_qty' => $item['returned_qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['returned_qty'] * $item['unit_price'],
                'reason' => $item['reason'] ?? null,
            ]);
        }
        return redirect()->route('orderReturn.list')->with('success', 'Order return updated successfully.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('manage returns')) {
            abort(403, 'Unauthorized action.');
        }
        $orderReturn = OrderReturn::findOrFail($id);
        $orderReturn->delete();
        return redirect()->route('orderReturn.list')->with('success', 'Order return deleted successfully.');
    }

    public function updateReturnStatus(Request $request, $id)
    {
        if (!auth()->user()->hasPermissionTo('manage returns')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'status' => 'required|in:approved,rejected,processed',
            'notes' => 'nullable|string|max:500'
        ]);

        $orderReturn = OrderReturn::with(['items'])->findOrFail($id);

        // Prevent re-processing
        if ($orderReturn->status === 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Sale return is already processed and cannot be updated.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $orderReturn->status;
            $newStatus = $request->status;
            $updateData = ['status' => $newStatus];

            // Add notes if provided
            if ($request->filled('notes')) {
                $currentNotes = $orderReturn->notes ? $orderReturn->notes . "\n" : "";
                $updateData['notes'] = $currentNotes . "[" . now()->format('Y-m-d H:i:s') . "] Status changed to " . ucfirst($newStatus) . ": " . $request->notes;
            }

            $orderReturn->update($updateData);

            // If status is being processed, adjust stock (add returned qty)
            if ($newStatus === 'processed') {
                // Refresh items to ensure we have latest data including variation_id
                $orderReturn->refresh();
                $orderReturn->load('items');
                
                foreach ($orderReturn->items as $item) {
                    // Refresh item to ensure we have latest data
                    $item->refresh();
                    
                    // Log item details before processing
                    \Log::info('Processing return item', [
                        'item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'variation_id' => $item->variation_id,
                        'returned_qty' => $item->returned_qty,
                        'return_to_type' => $orderReturn->return_to_type,
                        'return_to_id' => $orderReturn->return_to_id
                    ]);
                    
                    // Verify variation_id is set if product has variations
                    if ($item->product_id) {
                        $product = \App\Models\Product::find($item->product_id);
                        if ($product && $product->has_variations && !$item->variation_id) {
                            \Log::warning('Product has variations but variation_id is missing in return item', [
                                'item_id' => $item->id,
                                'product_id' => $item->product_id
                            ]);
                        }
                    }
                    
                    $this->addStockForReturnItem($orderReturn, $item);
                }
                
                // Track who processed and when
                $orderReturn->processed_by = auth()->id();
                $orderReturn->processed_at = now();
                $orderReturn->save();
            }

            DB::commit();

            $statusMessage = match($newStatus) {
                'approved' => 'Order return has been approved successfully.',
                'rejected' => 'Order return has been rejected.',
                'processed' => 'Order return has been processed and stock has been updated.',
                default => 'Order return status has been updated.'
            };

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'data' => [
                    'id' => $orderReturn->id,
                    'status' => $orderReturn->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order return status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add returned quantity to the selected stock (branch, warehouse, or employee)
     */
    private function addStockForReturnItem($orderReturn, $item)
    {
        $qty = $item->returned_qty;
        $productId = $item->product_id;
        $variationId = $item->variation_id ?? null;
        $toType = $orderReturn->return_to_type;
        $toId = $orderReturn->return_to_id;

        // Log for debugging
        \Log::info('Adding stock for return item', [
            'product_id' => $productId,
            'variation_id' => $variationId,
            'quantity' => $qty,
            'return_to_type' => $toType,
            'return_to_id' => $toId
        ]);

        switch ($toType) {
            case 'branch':
                if ($variationId && $variationId > 0) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $toId)
                        ->whereNull('warehouse_id')
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                        \Log::info('Variation stock incremented in branch', [
                            'stock_id' => $stock->id,
                            'new_quantity' => $stock->quantity
                        ]);
                    } else {
                        $newStock = \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'branch_id' => $toId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id(),
                            'last_updated_at' => now()
                        ]);
                        \Log::info('New variation stock created in branch', [
                            'stock_id' => $newStock->id,
                            'quantity' => $newStock->quantity
                        ]);
                    }
                } else {
                    $stock = \App\Models\BranchProductStock::where('branch_id', $toId)
                        ->where('product_id', $productId)
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                        \Log::info('Product stock incremented in branch', [
                            'stock_id' => $stock->id,
                            'new_quantity' => $stock->quantity
                        ]);
                    } else {
                        $newStock = \App\Models\BranchProductStock::create([
                            'branch_id' => $toId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id(),
                            'last_updated_at' => now()
                        ]);
                        \Log::info('New product stock created in branch', [
                            'stock_id' => $newStock->id,
                            'quantity' => $newStock->quantity
                        ]);
                    }
                }
                break;
            case 'warehouse':
                if ($variationId && $variationId > 0) {
                    $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                        ->where('warehouse_id', $toId)
                        ->whereNull('branch_id')
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                        \Log::info('Variation stock incremented in warehouse', [
                            'stock_id' => $stock->id,
                            'new_quantity' => $stock->quantity
                        ]);
                    } else {
                        $newStock = \App\Models\ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'warehouse_id' => $toId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id(),
                            'last_updated_at' => now()
                        ]);
                        \Log::info('New variation stock created in warehouse', [
                            'stock_id' => $newStock->id,
                            'quantity' => $newStock->quantity
                        ]);
                    }
                } else {
                    $stock = \App\Models\WarehouseProductStock::where('warehouse_id', $toId)
                        ->where('product_id', $productId)
                        ->first();
                    if ($stock) {
                        $stock->increment('quantity', $qty);
                        \Log::info('Product stock incremented in warehouse', [
                            'stock_id' => $stock->id,
                            'new_quantity' => $stock->quantity
                        ]);
                    } else {
                        $newStock = \App\Models\WarehouseProductStock::create([
                            'warehouse_id' => $toId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'updated_by' => auth()->id(),
                            'last_updated_at' => now()
                        ]);
                        \Log::info('New product stock created in warehouse', [
                            'stock_id' => $newStock->id,
                            'quantity' => $newStock->quantity
                        ]);
                    }
                }
                break;
            case 'employee':
                // Note: Employee stock doesn't support variations currently
                $stock = \App\Models\EmployeeProductStock::where('employee_id', $toId)
                    ->where('product_id', $productId)
                    ->first();
                if ($stock) {
                    $stock->increment('quantity', $qty);
                    \Log::info('Product stock incremented for employee', [
                        'stock_id' => $stock->id,
                        'new_quantity' => $stock->quantity
                    ]);
                } else {
                    $newStock = \App\Models\EmployeeProductStock::create([
                        'employee_id' => $toId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'issued_by' => auth()->id()
                    ]);
                    \Log::info('New product stock created for employee', [
                        'stock_id' => $newStock->id,
                        'quantity' => $newStock->quantity
                    ]);
                }
                break;
            default:
                throw new \Exception("Invalid return_to_type: {$toType}");
        }
    }

    /**
     * Validate return quantities against original order
     */
    private function validateReturnQuantities($orderId, $items, $excludeReturnId = null)
    {
        $order = Order::with('items')->findOrFail($orderId);

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $variationId = $item['variation_id'] ?? null;
            $returnedQty = (float) $item['returned_qty'];

            // Find matching order item
            $orderItem = $order->items()->where('product_id', $productId)
                ->when($variationId, function($q) use ($variationId) {
                    $q->where('variation_id', $variationId);
                })
                ->first();

            if (!$orderItem) {
                throw new \Exception("Product not found in the original order.");
            }

            // Check if there are existing returns for this order
            $existingReturns = OrderReturn::where('order_id', $orderId)
                ->when($excludeReturnId, function($q) use ($excludeReturnId) {
                    $q->where('id', '!=', $excludeReturnId);
                })
                ->whereIn('status', ['pending', 'approved', 'processed'])
                ->get();

            $totalReturnedQty = 0;
            foreach ($existingReturns as $return) {
                $returnItems = $return->items()->where('product_id', $productId)
                    ->when($variationId, function($q) use ($variationId) {
                        $q->where('variation_id', $variationId);
                    })
                    ->sum('returned_qty');
                $totalReturnedQty += $returnItems;
            }

            $availableQty = (float) $orderItem->quantity - $totalReturnedQty;

            if ($returnedQty > $availableQty) {
                throw new \Exception("Cannot return quantity greater than available. Available: {$availableQty}, Requested: {$returnedQty}");
            }
        }
    }
}
