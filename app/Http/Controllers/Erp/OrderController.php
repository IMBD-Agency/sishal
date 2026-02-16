<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\InvoiceAddress;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\BranchProductStock;
use App\Models\WarehouseProductStock;
use App\Models\EmployeeProductStock;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view order list')) {
            abort(403, 'Unauthorized action.');
        }

        $orders = $this->getFilteredQuery($request)
            ->with(['invoice', 'customer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $customers = \App\Models\User::where('is_admin', 0)
            ->orderBy('first_name')
            ->get();

        return view('erp.order.orderlist', compact('orders', 'customers'));
    }

    public function exportExcel(Request $request)
    {
        $orders = $this->getFilteredQuery($request)->with(['invoice', 'customer'])->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Order Date', 'Order #', 'Customer', 'Phone', 'Status', 'Bill Status', 'Subtotal', 'Discount', 'Delivery', 'Total'];
        foreach($headers as $k => $h) {
            $sheet->setCellValue(chr(65+$k).'1', $h);
        }

        $row = 2;
        foreach($orders as $o) {
            $sheet->setCellValue('A'.$row, $o->created_at->format('d M, Y'));
            $sheet->setCellValue('B'.$row, $o->order_number);
            $sheet->setCellValue('C'.$row, $o->name);
            $sheet->setCellValue('D'.$row, $o->phone);
            $sheet->setCellValue('E'.$row, ucfirst($o->status));
            $sheet->setCellValue('F'.$row, ucfirst($o->invoice->status ?? 'N/A'));
            $sheet->setCellValue('G'.$row, $o->subtotal);
            $sheet->setCellValue('H'.$row, $o->discount);
            $sheet->setCellValue('I'.$row, $o->delivery);
            $sheet->setCellValue('J'.$row, $o->total);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'order_list_' . date('Ymd_His') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $orders = $this->getFilteredQuery($request)->with(['invoice', 'customer'])->get();
        
        $filename = 'order_report_' . date('Y-m-d_H-i-s') . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.order.order-report-pdf', [
            'orders' => $orders,
            'filters' => $request->all()
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($filename);
    }

    private function getFilteredQuery(Request $request)
    {
        $query = Order::query();

        // Search by order number, name, phone, email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        // Filter by customer (user_id)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Date Range (Created At)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by estimated delivery date
        if ($request->filled('estimated_delivery_date')) {
            $query->whereDate('estimated_delivery_date', $request->estimated_delivery_date);
        }

        // Filter by bill status (invoice status)
        if ($request->filled('bill_status')) {
            $query->whereHas('invoice', function($q) use ($request) {
                $q->where('status', $request->bill_status);
            });
        }

        return $query;
    }

    public function show($id)
    {
        // Load order with variation relationship to ensure variation_id is available
        $order = Order::with(['invoice.payments', 'items.product', 'items.variation', 'employee.user', 'customer'])->find($id);
        
        // If AJAX request, return JSON
        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'id' => $order->id,
                'customer_id' => $order->user_id ?? $order->created_by,
                'customer_name' => $order->name ?? ($order->customer->name ?? 'N/A'),
                'order_number' => $order->order_number,
                'items' => $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'N/A',
                        'variation_id' => $item->variation_id, // CRITICAL: This must be included
                        'variation_name' => $item->variation ? $item->variation->name : null,
                        'variation_sku' => $item->variation ? $item->variation->sku : null,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'current_position_type' => $item->current_position_type,
                        'current_position_id' => $item->current_position_id,
                    ];
                })
            ]);
        }
        
        $bankAccounts = collect(); // Empty collection since FinancialAccount model was removed
        return view('erp.order.orderdetails', compact('order', 'bankAccounts'));
    }

    public function setEstimatedDelivery(Request $request, $id)
    {
        $validated = $request->validate([
            'estimated_delivery_date' => 'required|date',
            'estimated_delivery_time' => 'required',
        ]);

        $order = Order::findOrFail($id);
        $order->estimated_delivery_date = $validated['estimated_delivery_date'];
        $order->estimated_delivery_time = $validated['estimated_delivery_time'];
        $order->save();

        return response()->json(['success' => true, 'message' => 'Estimated delivery date and time updated.']);
    }

    // This function can be used for both add and edit
    public function updateEstimatedDelivery(Request $request, $id)
    {
        $validated = $request->validate([
            'estimated_delivery_date' => 'required|date',
            'estimated_delivery_time' => 'required',
        ]);

        $order = Order::findOrFail($id);
        $order->estimated_delivery_date = $validated['estimated_delivery_date'];
        $order->estimated_delivery_time = $validated['estimated_delivery_time'];
        $order->save();

        return response()->json(['success' => true, 'message' => 'Estimated delivery date and time updated.']);
    }

    public function updateStatus(Request $request, $id)
    {
        // Load order with items, products, and variations to ensure variation_id is available
        $order = Order::with(['items.product', 'items.product.variations', 'items.variation'])->findOrFail($id);

        // Handle order cancellation - restore stock
        if ($request->status === 'cancelled') {
            // Only allow cancellation if order is not already cancelled or delivered
            if (in_array($order->status, ['cancelled', 'delivered'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel an order that is already ' . $order->status . '.'
                ]);
            }

            $previousStatus = $order->status;
            
            DB::beginTransaction();
            try {
                // Restore stock for each order item
                // Note: Stock should be restored even if order was shipped (current_position cleared)
                // because stock was deducted when order was placed or shipped
                foreach ($order->items as $item) {
                    $this->restoreStockForOrderItem($item);
                }

                $order->status = 'cancelled';
                $order->save();

                DB::commit();

                Log::info('Order cancelled and stock restored', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'previous_status' => $previousStatus,
                    'items_count' => $order->items->count()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully. Stock has been restored.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order cancellation failed in updateStatus', [
                    'order_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order: ' . $e->getMessage()
                ], 500);
            }
        }

        if ($request->status === 'shipping') {
            // For e-commerce orders, we need to manage stock but don't require technician
            $isServiceOrder = $order->employee_id && $order->items->where('current_position_type')->count() > 0;
            
            if ($isServiceOrder) {
                // Service order: requires technician and complex stock management
                if (!$order->employee_id) {
                    return response()->json(['success' => false, 'message' => 'Assign a technician before shipping.']);
                }
                
                foreach ($order->items as $item) {
                    if (!$item->current_position_type || !$item->current_position_id) {
                        return response()->json(['success' => false, 'message' => 'All items must have a stock source before shipping.']);
                    }
                }

                foreach ($order->items as $item) {
                    $productId = $item->product_id;
                    $qty = $item->quantity;
                    $fromType = $item->current_position_type;
                    $fromId = $item->current_position_id;
                    $employeeId = $order->employee_id;

                    // Find or create employee stock
                    $employeeStock = EmployeeProductStock::firstOrCreate(
                        ['employee_id' => $employeeId, 'product_id' => $productId],
                        ['quantity' => 0, 'issued_by' => auth()->id() ?? 1]
                    );

                    // Get source stock
                    if ($fromType === 'branch') {
                        $fromStock = BranchProductStock::where('branch_id', $fromId)
                            ->where('product_id', $productId)
                            ->lockForUpdate()
                            ->first();
                    } elseif ($fromType === 'warehouse') {
                        $fromStock = WarehouseProductStock::where('warehouse_id', $fromId)
                            ->where('product_id', $productId)
                            ->lockForUpdate()
                            ->first();
                    } elseif ($fromType === 'employee') {
                        $fromStock = EmployeeProductStock::where('employee_id', $fromId)
                            ->where('product_id', $productId)
                            ->lockForUpdate()
                            ->first();
                    } else {
                        return response()->json(['success' => false, 'message' => 'Invalid stock source for item.']);
                    }

                    if (!$fromStock || $fromStock->quantity < $qty) {
                        $productName = $item->product ? $item->product->name : 'Product ID: ' . $item->product_id;
                        $availableQty = $fromStock ? $fromStock->quantity : 0;
                        return response()->json([
                            'success' => false, 
                            'message' => "Insufficient stock for '{$productName}'. Required: {$qty}, Available: {$availableQty}. Please add stock before shipping."
                        ]);
                    }

                    // Transfer stock
                    $fromStock->quantity -= $qty;
                    $fromStock->save();

                    $employeeStock->quantity += $qty;
                    $employeeStock->save();

                    // Update item's current_position_type/id to employee
                    $item->current_position_type = 'employee';
                    $item->current_position_id = $employeeId;
                    $item->save();
                }
            } else {
                // E-commerce order: stock deduction from branches (retail outlets)
                foreach ($order->items as $item) {
                    $productId = $item->product_id;
                    $qty = $item->quantity;
                    $variationId = $item->variation_id;

                    // Log order item details for debugging
                    \Log::info('Processing ecommerce order item for shipping', [
                        'order_item_id' => $item->id,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'quantity' => $qty,
                        'has_current_position' => !!(($item->current_position_type && $item->current_position_id))
                    ]);

                    // Check if item has stock source defined
                    if ($item->current_position_type && $item->current_position_id) {
                        // Use defined stock source (typically a branch)
                        $fromType = $item->current_position_type;
                        $fromId = $item->current_position_id;
                        $product = $item->product;

                        $fromStock = null;
                        
                        // For products with variations, use variation-level stock
                        if ($variationId && $variationId > 0) {
                            if ($fromType === 'branch') {
                                $fromStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                                    ->where('branch_id', $fromId)
                                    ->whereNull('warehouse_id')
                                    ->lockForUpdate()
                                    ->first();
                            } else {
                                $fromStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                                    ->where('warehouse_id', $fromId)
                                    ->whereNull('branch_id')
                                    ->lockForUpdate()
                                    ->first();
                            }
                            
                            if (!$fromStock && $product && $product->has_variations) {
                                $variation = \App\Models\ProductVariation::find($variationId);
                                $variationName = $variation ? ($variation->name ?? $variation->sku) : 'Variation ID: ' . $variationId;
                                return response()->json([
                                    'success' => false,
                                    'message' => "No stock available for variation '{$variationName}' at selected location. Please add stock."
                                ], 400);
                            }
                        }
                        
                        // Product-level stock if no variation_id
                        if (!$fromStock && (!$variationId || !$product || !$product->has_variations)) {
                            if ($fromType === 'branch') {
                                $fromStock = BranchProductStock::where('branch_id', $fromId)
                                    ->where('product_id', $productId)
                                    ->lockForUpdate()
                                    ->first();
                            } else {
                                $fromStock = \App\Models\WarehouseProductStock::where('warehouse_id', $fromId)
                                    ->where('product_id', $productId)
                                    ->lockForUpdate()
                                    ->first();
                            }
                        }

                        if (!$fromStock || $fromStock->quantity < $qty) {
                            $itemName = ($product ? $product->name : 'Product ID: ' . $productId);
                            $availableQty = $fromStock ? $fromStock->quantity : 0;
                            return response()->json([
                                'success' => false, 
                                'message' => "Insufficient stock for '{$itemName}'. Required: {$qty}, Available: {$availableQty}."
                            ]);
                        }

                        // Deduct stock
                        $fromStock->quantity -= $qty;
                        $fromStock->save();

                        $item->current_position_type = null;
                        $item->current_position_id = null;
                        $item->save();
                    } else {
                        // Priority search across branches and warehouses
                        $deducted = false;
                        $product = $item->product;
                        $variation = $item->variation;
                        
                        if (!$product) {
                            $product = \App\Models\Product::with('variations')->find($productId);
                        }
                        
                        if (!$variationId && $product && $product->has_variations) {
                            return response()->json([
                                'success' => false,
                                'message' => "Variation not specified for product '{$product->name}'. Please edit the order first."
                            ], 400);
                        }

                        // 1) Try finding stock in ANY ONLINE-ENABLED branch
                        if ($variationId && $variationId > 0) {
                            $anyBranchStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                                ->whereHas('branch', function($q){ $q->where('show_online', true); })
                                ->whereNull('warehouse_id')
                                ->where('quantity', '>=', $qty)
                                ->lockForUpdate()
                                ->orderByDesc('quantity')
                                ->first();

                            if ($anyBranchStock) {
                                $anyBranchStock->quantity -= $qty;
                                $anyBranchStock->save();
                                $deducted = true;
                                \Log::info('Stock deducted from online-enabled branch (auto)', ['branch_id' => $anyBranchStock->branch_id]);
                            }
                        }

                        // 2) Try finding stock in ANY Warehouse (Legacy fallback)
                        if (!$deducted && $variationId && $variationId > 0) {
                            $anyWarehouseStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                                ->whereNotNull('warehouse_id')
                                ->whereNull('branch_id')
                                ->where('quantity', '>=', $qty)
                                ->lockForUpdate()
                                ->orderByDesc('quantity')
                                ->first();

                            if ($anyWarehouseStock) {
                                $anyWarehouseStock->quantity -= $qty;
                                $anyWarehouseStock->save();
                                $deducted = true;
                                \Log::info('Stock deducted from warehouse legacy fallback (auto)', ['warehouse_id' => $anyWarehouseStock->warehouse_id]);
                            }
                        }

                        // 3) Try product-level stock (for non-variable products)
                        if (!$deducted && (!$product || !$product->has_variations)) {
                            // First online branches
                            $anyBranchStock = BranchProductStock::where('product_id', $productId)
                                ->whereHas('branch', function($q){ $q->where('show_online', true); })
                                ->where('quantity', '>=', $qty)
                                ->lockForUpdate()
                                ->orderByDesc('quantity')
                                ->first();

                            if ($anyBranchStock) {
                                $anyBranchStock->quantity -= $qty;
                                $anyBranchStock->save();
                                $deducted = true;
                                \Log::info('Product stock deducted from online-enabled branch (auto)', ['branch_id' => $anyBranchStock->branch_id]);
                            }

                            // Then warehouses
                            if (!$deducted) {
                                $anyWarehouseStock = \App\Models\WarehouseProductStock::where('product_id', $productId)
                                    ->where('quantity', '>=', $qty)
                                    ->lockForUpdate()
                                    ->orderByDesc('quantity')
                                    ->first();

                                if ($anyWarehouseStock) {
                                    $anyWarehouseStock->quantity -= $qty;
                                    $anyWarehouseStock->save();
                                    $deducted = true;
                                    \Log::info('Product stock deducted from warehouse fallback (auto)', ['warehouse_id' => $anyWarehouseStock->warehouse_id]);
                                }
                            }
                        }

                        if (!$deducted) {
                            $itemName = ($product ? $product->name : 'Product ID: ' . $productId);
                            return response()->json([
                                'success' => false,
                                'message' => "Insufficient stock for '{$itemName}'. No inventory available in enabled Branches or legacy Warehouses."
                            ]);
                        }

                        // Mark item as shipped
                        $item->current_position_type = null;
                        $item->current_position_id = null;
                        $item->save();
                    }
                }
            }
        }

        // Handle reactivation from cancelled status - deduct stock again
        if ($order->status === 'cancelled' && in_array($request->status, ['pending', 'approved', 'shipping'])) {
            DB::beginTransaction();
            try {
                // Check if items have stock source assigned, if not, we need to deduct stock
                foreach ($order->items as $item) {
                    // If current_position is not set, it means stock was restored when cancelled
                    // We need to deduct stock again and assign a warehouse
                    if (!$item->current_position_type || !$item->current_position_id) {
                        $source = $this->findOrAssignFulfillmentSource($item);
                        if ($source) {
                            $this->deductStockForOrderItem($item, $source['id'], $source['type']);
                        } else {
                            throw new \Exception("Unable to find available stock for order item ID {$item->id}");
                        }
                    }
                }

                $order->status = $request->status;
                $order->save();

                DB::commit();

                Log::info('Order reactivated from cancelled, stock deducted', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'new_status' => $request->status,
                    'items_count' => $order->items->count()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully. Stock has been deducted.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Order reactivation failed in updateStatus', [
                    'order_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 500);
            }
        }

        // Update the order status
        $order->status = $request->status;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function updateTechnician($id, $employee_id)
    {
        $order = Order::findOrFail($id);
        $order->employee_id = $employee_id;

        $order->save();
        return response()->json(['success' => true, 'message' => 'Technician Assigned']);
    }

    public function deleteTechnician($id)
    {
        $order = Order::findOrFail($id);
        $order->employee_id = null;

        $order->save();
        return response()->json(['success' => true, 'message' => 'Technician Assigned']);
    }

    public function updateNote(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->notes = $request->notes;

        $order->save();
        return response()->json(['success' => true, 'message' => 'Notes updated.']);
    }

    public function addPayment($orderId, Request $request)
    {
        $order = Order::with('invoice')->find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        $invoice = $order->invoice;
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'account_id' => 'nullable|integer',
            'note' => 'nullable|string',
        ]);
        // Create payment
        $payment = new Payment();
        $payment->payment_for = 'order';
        $payment->pos_id = $order->id;
        $payment->invoice_id = $invoice->id;
        $payment->payment_date = now()->toDateString();
        $payment->amount = $request->amount;
        $payment->account_id = $request->account_id;
        $payment->payment_method = $request->payment_method;
        $payment->note = $request->note;
        $payment->save();
        // Update invoice
        $invoice->paid_amount += $request->amount;
        $invoice->due_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
        if ($invoice->paid_amount >= $invoice->total_amount) {
            $invoice->status = 'paid';
            $invoice->due_amount = 0;
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'unpaid';
        }
        $invoice->save();

        // If invoice is fully paid, mark ecommerce order as approved
        if ($invoice->status === 'paid' && $order && $order->status !== 'approved') {
            $order->status = 'approved';
            $order->save();
        }

        // Get customer ID from order or invoice
        $customerId = $order->customer_id ?? $invoice->customer_id ?? null;
        
        if($request->payment_method == 'cash' && $customerId)
        {
            // For COD orders, calculate COD discount and adjust payment amount
            $codDiscount = 0;
            if ($order->payment_method === 'cash') {
                $generalSetting = \App\Models\GeneralSetting::first();
                $codPercentage = $generalSetting ? ($generalSetting->cod_percentage / 100) : 0.00;
                if ($codPercentage > 0) {
                    // Calculate COD discount on invoice total
                    $codDiscount = round($invoice->total_amount * $codPercentage, 2);
                }
            }
            
            // For COD payments: balance was created with (invoice_total - cod_discount)
            // When payment is received, customer pays full invoice_total, but we only expected (invoice_total - cod_discount)
            // So we should subtract the net amount we expected to receive (payment - COD discount)
            $netPaymentAmount = $request->amount - $codDiscount;
            
            $balance = Balance::where('source_type', 'customer')->where('source_id', $customerId)->first();
            if($balance)
            {
                // Subtract the net amount (after COD discount) from balance
                $balanceBefore = $balance->balance;
                $balance->balance -= $netPaymentAmount;
                $balance->save();
                
                \Log::info('COD Payment Processed', [
                    'order_number' => $order->order_number,
                    'customer_id' => $customerId,
                    'payment_amount' => $request->amount,
                    'cod_discount' => $codDiscount,
                    'net_payment_amount' => $netPaymentAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balance->balance,
                ]);
            }
            else
            {
                // If balance doesn't exist, create it with remaining due amount (after COD discount if applicable)
                $remainingDue = $invoice->due_amount;
                if ($codDiscount > 0) {
                    $remainingDue = $remainingDue - $codDiscount;
                }
                Balance::create([
                    'source_type' => 'customer',
                    'source_id' => $customerId,
                    'balance' => max(0, $remainingDue),
                    'description' => 'Order Sale',
                    'reference' => $order->order_number,
                ]);
            }
        }

        if($request->received_by)
        {
            $balance = Balance::where('source_type', 'employee')->where('source_id', $request->received_by)->first();
            if($balance)
            {
                $balance->balance += $request->amount;
                $balance->save();   
            }
            else
            {
                Balance::create([
                    'source_type' => 'employee',
                    'source_id' => $request->received_by,
                    'balance' => $request->amount,
                    'description' => 'Order Sale',
                    'reference' => $order->order_number,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Payment added successfully.']);
    }

    public function addAddress(Request $request, $id)
    {
        $existingInvoiceAddress = InvoiceAddress::where('invoice_id',$id)->first();

        if($existingInvoiceAddress){
            $existingInvoiceAddress->billing_address_1 = $request->billing_address_1;
            $existingInvoiceAddress->billing_address_2 = $request->billing_address_2;
            $existingInvoiceAddress->billing_city = $request->billing_city;
            $existingInvoiceAddress->billing_state = $request->billing_state;
            $existingInvoiceAddress->billing_country = $request->billing_country;
            $existingInvoiceAddress->billing_zip_code = $request->billing_zip_code;

            $existingInvoiceAddress->shipping_address_1 = $request->shipping_address_1;
            $existingInvoiceAddress->shipping_address_2 = $request->shipping_address_2;
            $existingInvoiceAddress->shipping_city = $request->shipping_city;
            $existingInvoiceAddress->shipping_state = $request->shipping_state;
            $existingInvoiceAddress->shipping_country = $request->shipping_country;
            $existingInvoiceAddress->shipping_zip_code = $request->shipping_zip_code;

            $existingInvoiceAddress->save();
        }else{
            $invoiceAddress = new InvoiceAddress();
            $invoiceAddress->invoice_id = $id;
            $invoiceAddress->billing_address_1 = $request->billing_address_1;
            $invoiceAddress->billing_address_2 = $request->billing_address_2;
            $invoiceAddress->billing_city = $request->billing_city;
            $invoiceAddress->billing_state = $request->billing_state;
            $invoiceAddress->billing_country = $request->billing_country;
            $invoiceAddress->billing_zip_code = $request->billing_zip_code;

            $invoiceAddress->shipping_address_1 = $request->shipping_address_1;
            $invoiceAddress->shipping_address_2 = $request->shipping_address_2;
            $invoiceAddress->shipping_city = $request->shipping_city;
            $invoiceAddress->shipping_state = $request->shipping_state;
            $invoiceAddress->shipping_country = $request->shipping_country;
            $invoiceAddress->shipping_zip_code = $request->shipping_zip_code;

            $invoiceAddress->save();
        }
    }

    public function getProductStocks($productId, Request $request)
    {
        // Check if this is for an ecommerce order (no employee_id means ecommerce order)
        $isEcommerceOrder = false;
        $orderItemId = $request->get('order_item_id');
        
        if ($orderItemId) {
            $orderItem = OrderItem::with('order')->find($orderItemId);
            if ($orderItem && $orderItem->order) {
                // Ecommerce orders typically don't have employee_id assigned
                // Service orders have employee_id and use branches
                $isEcommerceOrder = !$orderItem->order->employee_id;
            }
        }

        $allStocks = collect();

        // For ecommerce orders, prioritize branch stocks
        if ($isEcommerceOrder) {
            // Branch stocks (Retail Outlets)
            $branchStocks = BranchProductStock::with('branch')
                ->where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->get()
                ->map(function($stock) {
                    return [
                        'type' => 'branch',
                        'location' => $stock->branch->name ?? 'Unknown Branch',
                        'quantity' => $stock->quantity,
                        'branch_id' => $stock->branch_id,
                    ];
                });
            $allStocks = $branchStocks;
        } else {
            // For service orders, show all stock types
            // Branch stocks
            $branchStocks = BranchProductStock::with('branch')
                ->where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->get()
                ->map(function($stock) {
                    return [
                        'type' => 'branch',
                        'location' => $stock->branch->name ?? 'Unknown Branch',
                        'quantity' => $stock->quantity,
                        'branch_id' => $stock->branch_id,
                    ];
                });

            // Warehouse stocks
            $warehouseStocks = WarehouseProductStock::with('warehouse')
                ->where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->get()
                ->map(function($stock) {
                    return [
                        'type' => 'warehouse',
                        'location' => $stock->warehouse->name ?? 'Unknown Warehouse',
                        'quantity' => $stock->quantity,
                        'warehouse_id' => $stock->warehouse_id,
                    ];
                });

            // Employee stocks
            $employeeStocks = EmployeeProductStock::with(['employee.user'])
                ->where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->get()
                ->map(function($stock) {
                    return [
                        'type' => 'employee',
                        'location' => $stock->employee->user->first_name . ' ' . $stock->employee->user->last_name,
                        'quantity' => $stock->quantity,
                        'employee_id' => $stock->employee_id,
                    ];
                });

            // Merge all stocks
            $allStocks = $branchStocks->concat($warehouseStocks)->concat($employeeStocks);
        }

        return response()->json([
            'success' => true,
            'stocks' => $allStocks->values(),
        ]);
    }

    public function addStockToOrderItem(Request $request, $id)
    {
        $orderItem = OrderItem::with('order')->find($id);
        
        if (!$orderItem) {
            return response()->json(['success' => false, 'message' => 'Order item not found.'], 404);
        }

        // For ecommerce orders, default to branch as stock source
        $isEcommerceOrder = !$orderItem->order->employee_id;
        /*
        if ($isEcommerceOrder && $request->current_position_type !== 'warehouse') {
            return response()->json([
                'success' => false,
                'message' => 'Ecommerce orders can only use warehouse stock. Please select a warehouse.'
            ], 400);
        }
        */

        $orderItem->current_position_type = $request->current_position_type;
        $orderItem->current_position_id = $request->current_position_id;
        $orderItem->save();
        return response()->json(['success' => true, 'message' => 'Stock added successfully.']);
    }

    public function transferStockToEmployee(Request $request, $orderItemId)
    {
        $orderItem = OrderItem::findOrFail($orderItemId);
        $order = $orderItem->order;
        $productId = $orderItem->product_id;
        $quantity = $orderItem->quantity;

        // 1. Check if order has an employee
        if (!$order->employee_id) {
            return response()->json(['success' => false, 'message' => 'No technician assigned to this order.']);
        }

        $employeeId = $order->employee_id;

        // 2. Find or create employee stock for this product
        $employeeStock = EmployeeProductStock::firstOrCreate(
            ['employee_id' => $employeeId, 'product_id' => $productId],
            [
                'quantity' => 0,
                'issued_by' => optional(auth()->user())->id ?? 1
            ]
        );

        // 3. Transfer from current position
        $fromType = $orderItem->current_position_type;
        $fromId = $orderItem->current_position_id;

        DB::beginTransaction();
        try {
            if ($fromType === 'branch') {
                $fromStock = BranchProductStock::where('branch_id', $fromId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();
            } elseif ($fromType === 'warehouse') {
                $fromStock = WarehouseProductStock::where('warehouse_id', $fromId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();
            } elseif ($fromType === 'employee') {
                $fromStock = EmployeeProductStock::where('employee_id', $fromId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid source for stock transfer.']);
            }

            if (!$fromStock || $fromStock->quantity < $quantity) {
                return response()->json(['success' => false, 'message' => 'Insufficient stock to transfer.']);
            }

            // Deduct from source
            $fromStock->quantity -= $quantity;
            $fromStock->save();

            // Add to employee stock
            $employeeStock->quantity += $quantity;
            $employeeStock->save();

            // 4. Update order item
            $orderItem->current_position_type = 'employee';
            $orderItem->current_position_id = $employeeId;
            $orderItem->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stock transferred to employee successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function orderSearch(Request $request)
    {
        $q = $request->input('q');
        $query = Order::with(['customer', 'invoice']);
        
        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('order_number', 'like', "%$q%")
                    ->orWhereHas('invoice', function($qi) use ($q) {
                        $qi->where('invoice_number', 'like', "%$q%");
                    })
                    ->orWhereHas('customer', function($q2) use ($q) {
                        $q2->where('name', 'like', "%$q%")
                            ->orWhere('phone', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                    });
            });
        }
        
        $sales = $query->orderBy('created_at', 'desc')->limit(20)->get();
        
        $results = $sales->map(function($sale) {
            $customer = $sale->customer;
            $invoice = $sale->invoice;
            
            // Format: INV-001 (ORD-001) - Customer Name (Phone)
            $text = "";
            if ($invoice) {
                $text .= $invoice->invoice_number . " ";
            }
            $text .= "(" . $sale->order_number . ")";
            
            if ($customer) {
                $text .= " - " . $customer->name;
                if ($customer->phone) $text .= " (" . $customer->phone . ")";
            }
            
            return [
                'id' => $sale->id,
                'text' => $text
            ];
        });
        
        return response()->json($results);
    }

    /**
     * Delete an order with proper validation and safeguards
     */
    public function destroy($id)
    {
        // Check if user has permission to delete orders
        if (!auth()->user()->can('delete orders')) {
            return response()->json([
                'success' => false, 
                'message' => 'You do not have permission to delete orders.'
            ], 403);
        }

        $order = Order::with(['items', 'invoice', 'payments'])->findOrFail($id);

        // Check if order can be deleted based on status
        $deletableStatuses = ['pending', 'cancelled'];
        if (!in_array($order->status, $deletableStatuses)) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete order with status: ' . ucfirst($order->status) . '. Only pending or cancelled orders can be deleted.'
            ], 400);
        }

        // Check if order has been shipped or delivered
        if (in_array($order->status, ['shipping', 'shipped', 'delivered', 'received'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete order that has been shipped or delivered.'
            ], 400);
        }

        // Check if order has payments (except for cancelled orders)
        if ($order->status !== 'cancelled' && $order->payments && $order->payments->count() > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete order with existing payments. Please process a refund first.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Log the deletion for audit purposes
            Log::info('Order deletion initiated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'deleted_by' => auth()->id(),
                'deleted_at' => now(),
                'order_status' => $order->status,
                'customer_name' => $order->name,
                'customer_phone' => $order->phone
            ]);

            // Restore stock for each order item (only if not already cancelled)
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $this->restoreStockForOrderItem($item);
                }
            }

            // Delete related records
            $order->items()->delete();
            
            // Delete invoice if exists and not paid
            if ($order->invoice && $order->invoice->status !== 'paid') {
                $order->invoice->items()->delete();
                if ($order->invoice->invoiceAddress) {
                    $order->invoice->invoiceAddress()->delete();
                }
                $order->invoice->delete();
            }

            // Delete payments
            $order->payments()->delete();

            // Delete the order
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Order ' . $order->order_number . ' has been deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order deletion failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Failed to delete order. Please try again.'
            ], 500);
        }
    }

    /**
     * Restore stock for an order item
     */
    private function restoreStockForOrderItem($item)
    {
        $productId = $item->product_id;
        $variationId = $item->variation_id;
        $quantity = $item->quantity;
        $fromType = $item->current_position_type;
        $fromId = $item->current_position_id;
        $userId = auth()->id() ?? 1;

        // If no stock source, try to find existing stock or use default branch
        if (!$fromType || !$fromId) {
            // Try to find existing stock for this product/variation
            if ($variationId) {
                $existingStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                    ->whereNotNull('branch_id')
                    ->whereNull('warehouse_id')
                    ->first();
                if ($existingStock) {
                    $fromType = 'branch';
                    $fromId = $existingStock->branch_id;
                } else {
                    // Fallback to first branch
                    $fromType = 'branch';
                    $fromId = \App\Models\Branch::first()->id ?? 1;
                }
            } else {
                $existingStock = BranchProductStock::where('product_id', $productId)
                    ->first();
                if ($existingStock) {
                    $fromType = 'branch';
                    $fromId = $existingStock->branch_id;
                } else {
                    // Fallback to first branch
                    $fromType = 'branch';
                    $fromId = \App\Models\Branch::first()->id ?? 1;
                }
            }
        }

        // For products with variations, restore to variation-level stock
        if ($variationId) {
            if ($fromType === 'warehouse') {
                $variationStock = \App\Models\ProductVariationStock::firstOrCreate(
                    [
                        'variation_id' => $variationId,
                        'warehouse_id' => $fromId,
                        'branch_id' => null
                    ],
                    [
                        'quantity' => 0,
                        'updated_by' => $userId,
                        'last_updated_at' => now()
                    ]
                );
                $variationStock->quantity += $quantity;
                $variationStock->updated_by = $userId;
                $variationStock->last_updated_at = now();
                $variationStock->save();

                Log::info('Stock restored to variation warehouse', [
                    'variation_id' => $variationId,
                    'warehouse_id' => $fromId,
                    'quantity_restored' => $quantity,
                    'new_quantity' => $variationStock->quantity,
                    'order_item_id' => $item->id
                ]);
            } elseif ($fromType === 'branch') {
                $variationStock = \App\Models\ProductVariationStock::firstOrCreate(
                    [
                        'variation_id' => $variationId,
                        'branch_id' => $fromId,
                        'warehouse_id' => null
                    ],
                    [
                        'quantity' => 0,
                        'updated_by' => $userId,
                        'last_updated_at' => now()
                    ]
                );
                $variationStock->quantity += $quantity;
                $variationStock->updated_by = $userId;
                $variationStock->last_updated_at = now();
                $variationStock->save();

                Log::info('Stock restored to variation branch', [
                    'variation_id' => $variationId,
                    'branch_id' => $fromId,
                    'quantity_restored' => $quantity,
                    'new_quantity' => $variationStock->quantity,
                    'order_item_id' => $item->id
                ]);
            }
        } else {
            // For products without variations, restore to product-level stock
            if ($fromType === 'branch') {
                $stock = BranchProductStock::firstOrCreate(
                    ['branch_id' => $fromId, 'product_id' => $productId],
                    ['quantity' => 0, 'updated_by' => $userId]
                );
            } elseif ($fromType === 'warehouse') {
                $stock = WarehouseProductStock::firstOrCreate(
                    ['warehouse_id' => $fromId, 'product_id' => $productId],
                    ['quantity' => 0, 'updated_by' => $userId]
                );
            } elseif ($fromType === 'employee') {
                $stock = EmployeeProductStock::firstOrCreate(
                    ['employee_id' => $fromId, 'product_id' => $productId],
                    ['quantity' => 0, 'issued_by' => $userId, 'updated_by' => $userId]
                );
            } else {
                return; // Unknown stock type
            }

            $stock->quantity += $quantity;
            $stock->updated_by = $userId;
            if (isset($stock->last_updated_at)) {
                $stock->last_updated_at = now();
            }
            $stock->save();

            Log::info('Stock restored to product location', [
                'product_id' => $productId,
                'location_type' => $fromType,
                'location_id' => $fromId,
                'quantity_restored' => $quantity,
                'new_quantity' => $stock->quantity,
                'order_item_id' => $item->id
            ]);
        }
    }

    /**
     * Find or assign a fulfillment source for an ecommerce order item
     */
    private function findOrAssignFulfillmentSource($item)
    {
        $productId = $item->product_id;
        $variationId = $item->variation_id;
        $quantity = $item->quantity;

        // 1. Check variation-level stock
        if ($variationId) {
            // First: Online-enabled branches
            $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                ->whereHas('branch', function($q){ $q->where('show_online', true); })
                ->whereNull('warehouse_id')
                ->where('quantity', '>=', $quantity)
                ->orderByDesc('quantity')
                ->first();

            if ($stock) {
                return ['type' => 'branch', 'id' => $stock->branch_id];
            }

            // Second: Legacy warehouses
            $stock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                ->whereNotNull('warehouse_id')
                ->whereNull('branch_id')
                ->where('quantity', '>=', $quantity)
                ->orderByDesc('quantity')
                ->first();

            if ($stock) {
                return ['type' => 'warehouse', 'id' => $stock->warehouse_id];
            }
        }

        // 2. Check product-level stock (non-variable)
        // Online branches
        $productStock = BranchProductStock::where('product_id', $productId)
            ->whereHas('branch', function($q){ $q->where('show_online', true); })
            ->where('quantity', '>=', $quantity)
            ->orderByDesc('quantity')
            ->first();

        if ($productStock) {
            return ['type' => 'branch', 'id' => $productStock->branch_id];
        }

        // Legacy warehouses
        $productStock = \App\Models\WarehouseProductStock::where('product_id', $productId)
            ->where('quantity', '>=', $quantity)
            ->orderByDesc('quantity')
            ->first();

        if ($productStock) {
            return ['type' => 'warehouse', 'id' => $productStock->warehouse_id];
        }

        return null;
    }

    /**
     * Deduct stock for an ecommerce order item
     */
    private function deductStockForOrderItem($item, $sourceId, $sourceType = 'branch')
    {
        $productId = $item->product_id;
        $variationId = $item->variation_id;
        $quantity = $item->quantity;
        $userId = auth()->id() ?? 1;

        if ($variationId) {
            $variationStock = \App\Models\ProductVariationStock::where('variation_id', $variationId)
                ->where($sourceType === 'branch' ? 'branch_id' : 'warehouse_id', $sourceId)
                ->where($sourceType === 'branch' ? 'warehouse_id' : 'branch_id', null)
                ->lockForUpdate()
                ->first();

            if ($variationStock) {
                if ($variationStock->quantity >= $quantity) {
                    $variationStock->quantity -= $quantity;
                    $variationStock->updated_by = $userId;
                    $variationStock->last_updated_at = now();
                    $variationStock->save();

                    $item->current_position_type = $sourceType;
                    $item->current_position_id = $sourceId;
                    $item->save();
                } else {
                    throw new \Exception("Insufficient stock at the selected location.");
                }
            }
        } else {
            if ($sourceType === 'branch') {
                $stock = BranchProductStock::where('product_id', $productId)->where('branch_id', $sourceId)->lockForUpdate()->first();
            } else {
                $stock = \App\Models\WarehouseProductStock::where('product_id', $productId)->where('warehouse_id', $sourceId)->lockForUpdate()->first();
            }

            if ($stock && $stock->quantity >= $quantity) {
                $stock->quantity -= $quantity;
                $stock->save();

                $item->current_position_type = $sourceType;
                $item->current_position_id = $sourceId;
                $item->save();
            }
        }
    }
}
