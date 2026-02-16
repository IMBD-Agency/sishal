<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\OrderItem;
use App\Models\InvoiceItem;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationStock;
use App\Models\BranchProductStock;
use App\Models\WarehouseProductStock;
use App\Models\InvoiceTemplate;
use App\Models\Brand;
use App\Models\ProductServiceCategory;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderExchangeController extends Controller
{
    public function index(Request $request)
    {
        $exchanges = $this->getFilteredQuery($request)
            ->with([
                'customer', 
                'order', 
                'items.product.brand', 
                'items.product.category', 
                'items.product.season', 
                'items.variation'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Map linked exchange orders for financials
        $exchanges->getCollection()->transform(function($exchange) {
            $exchange->exchangeOrder = Order::where('notes', 'like', "%Return #{$exchange->id}%")
                ->with('invoice')
                ->first();
            return $exchange;
        });

        $customers = Customer::orderBy('name')->get();
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        $brands = Brand::where('status', 'active')->orWhere('status', 1)->orderBy('name')->get();
        $categories = ProductServiceCategory::where('status', 'active')->orWhere('status', 1)->orderBy('name')->get();
        $seasons = Season::where('status', 'active')->orWhere('status', 1)->orderBy('name')->get();

        return view('erp.orderExchange.index', compact('exchanges', 'customers', 'branches', 'warehouses', 'brands', 'categories', 'seasons'));
    }

    private function getFilteredQuery(Request $request)
    {
        $query = OrderReturn::query();

        // Core logic: Must be an exchange
        $query->where(function($q) {
            $q->where('notes', 'like', '%Exchanged%')
              ->orWhere('reason', 'like', '%Exchange%')
              ->orWhereHas('order', function($qo) {
                  $qo->where('payment_method', 'exchange_adjustment');
              });
        });

        // Search (Reference, Name, Phone)
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('customer', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%")
                         ->orWhere('phone', 'like', "%$search%");
                  })
                  ->orWhereHas('order', function($qp) use ($search) {
                      $qp->where('order_number', 'like', "%$search%");
                  });
            });
        }

        // Customer Filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }

        // Location Filter
        if ($request->filled('location_type') && $request->filled('location_id')) {
            $query->where('return_to_type', $request->location_type)
                  ->where('return_to_id', $request->location_id);
        }

        // Product Attributes Filters
        if ($request->filled('brand_id')) {
            $query->whereHas('items.product', function($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('items.product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('season_id')) {
            $query->whereHas('items.product', function($q) use ($request) {
                $q->where('season_id', $request->season_id);
            });
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        $exchanges = $this->getFilteredQuery($request)
            ->with([
                'customer', 
                'order', 
                'items.product.brand', 
                'items.product.category', 
                'items.product.season', 
                'items.variation'
            ])
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Date', 'Exch. Ref', 'Customer', 'Returned Product', 'Brand', 
            'Category', 'Season', 'Size/Var', 'Qty', 'Unit Price', 
            'Line Credit', 'Exch. Order #', 'New Order Subtotal', 'Exch. Discount', 
            'Paid Amount', 'Due Balance'
        ];

        $col = 'A';
        foreach($headers as $h) {
            $sheet->setCellValue($col.'1', $h);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $col++;
        }

        $rowNum = 2;
        foreach($exchanges as $ex) {
            // Find linked exchange order for financials
            $exOrder = Order::where('notes', 'like', "%Return #{$ex->id}%")
                ->with('invoice')
                ->first();
            
            foreach($ex->items as $item) {
                $sheet->setCellValue('A'.$rowNum, $ex->return_date);
                $sheet->setCellValue('B'.$rowNum, 'EXC-' . str_pad($ex->id, 5, '0', STR_PAD_LEFT));
                $sheet->setCellValue('C'.$rowNum, $ex->customer->name ?? 'N/A');
                $sheet->setCellValue('D'.$rowNum, $item->product->name ?? 'N/A');
                $sheet->setCellValue('E'.$rowNum, $item->product->brand->name ?? '-');
                $sheet->setCellValue('F'.$rowNum, $item->product->category->name ?? '-');
                $sheet->setCellValue('G'.$rowNum, $item->product->season->name ?? '-');
                $sheet->setCellValue('H'.$rowNum, $item->variation->name ?? 'Std');
                $sheet->setCellValue('I'.$rowNum, $item->returned_qty);
                $sheet->setCellValue('J'.$rowNum, $item->unit_price);
                $sheet->setCellValue('K'.$rowNum, $item->total_price);
                
                $sheet->setCellValue('L'.$rowNum, $exOrder->order_number ?? 'N/A');
                $sheet->setCellValue('M'.$rowNum, $exOrder->total ?? 0);
                $sheet->setCellValue('N'.$rowNum, $exOrder->discount ?? 0);
                $sheet->setCellValue('O'.$rowNum, $exOrder->invoice->paid_amount ?? 0);
                $sheet->setCellValue('P'.$rowNum, $exOrder->invoice->due_amount ?? 0);
                
                $rowNum++;
            }
        }

        foreach(range('A','P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'order_exchanges_report_' . date('Ymd_His') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function create(Request $request)
    {
        $customers = Customer::all();
        $orders = Order::orderBy('created_at', 'desc')->take(100)->get();
        $products = Product::all();
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        
        $preSelectedOrder = null;
        if ($request->has('order_id')) {
            $preSelectedOrder = Order::with(['customer', 'items.product', 'items.variation'])->find($request->order_id);
        }

        $generalSettings = \App\Models\GeneralSetting::first();
        
        return view('erp.orderExchange.create', compact(
            'customers', 'orders', 'products', 'branches', 'warehouses', 
            'preSelectedOrder', 'generalSettings'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
            'return_to_type' => 'required|in:branch,warehouse',
            'return_to_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.returned_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'new_items' => 'required|array|min:1',
            'new_items.*.product_id' => 'required|exists:products,id',
            'new_items.*.qty' => 'required|numeric|min:0.01',
            'new_items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create the Return Record
            $returnData = $request->only(['customer_id', 'order_id', 'return_date', 'return_to_type', 'return_to_id', 'reason', 'notes']);
            $returnData['status'] = 'processed'; // Exchanges are usually processed immediately in this UI
            $returnData['refund_type'] = 'credit'; // For exchange
            $returnData['processed_by'] = auth()->id();
            $returnData['processed_at'] = now();
            
            $orderReturn = OrderReturn::create($returnData);

            $totalReturnValue = 0;
            foreach ($request->items as $item) {
                $total = $item['returned_qty'] * $item['unit_price'];
                $totalReturnValue += $total;
                
                $returnItem = OrderReturnItem::create([
                    'order_return_id' => $orderReturn->id,
                    'order_item_id' => $item['order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'returned_qty' => $item['returned_qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $total,
                    'reason' => $item['reason'] ?? 'Exchange',
                ]);

                // Auto-Restock for exchange
                $this->addStockForReturnItem($orderReturn, $returnItem);
            }

            // 2. Create the New Exchange Order
            $newItems = $request->new_items;
            $subtotal = 0;
            foreach ($newItems as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }

            $customer = Customer::find($request->customer_id);
            $orderNumber = 'EXC-' . strtoupper(date('Ymd') . '-' . substr(uniqid(), -5));
            
            $newOrder = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $customer->user_id ?? 0,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'approved',
                'payment_method' => 'exchange_adjustment',
                'notes' => "Exchange Order for Return #" . $orderReturn->id,
                'created_by' => auth()->id(),
            ]);

            // Create Invoice
            $invTemplate = InvoiceTemplate::where('is_default', 1)->first();
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'order_id' => $newOrder->id,
                'template_id' => $invTemplate ? $invTemplate->id : null,
                'invoice_number' => 'INV-' . $orderNumber,
                'issue_date' => now(),
                'due_date' => now(),
                'total_amount' => $subtotal,
                'subtotal' => $subtotal,
                'paid_amount' => min($subtotal, $totalReturnValue),
                'due_amount' => max(0, $subtotal - $totalReturnValue),
                'status' => ($subtotal <= $totalReturnValue) ? 'paid' : 'partial',
                'created_by' => auth()->id(),
                'operated_by' => auth()->id(),
            ]);
            
            $newOrder->invoice_id = $invoice->id;
            $newOrder->save();

            // 3. Process New Items & Deduct Stock
            foreach ($newItems as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $newOrder->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['qty'] * $item['unit_price'],
                ]);
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['qty'] * $item['unit_price'],
                ]);

                $this->deductStock($orderItem, $request->return_to_type, $request->return_to_id);
            }

            // Link them
            $orderReturn->notes = ($orderReturn->notes ? $orderReturn->notes . "\n" : "") . "Exchanged for Order #" . $newOrder->order_number;
            $orderReturn->save();

            DB::commit();
            return redirect()->route('orderExchange.show', $orderReturn->id)->with('success', 'Exchange processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Exchange Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Exchange Failed: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $orderReturn = OrderReturn::with([
            'items.product.brand', 
            'items.product.category', 
            'items.product.season', 
            'items.variation', 
            'customer'
        ])->findOrFail($id);
        
        $exchangeOrder = Order::where('notes', 'like', "%Return #{$id}%")
            ->with([
                'items.product.brand', 
                'items.product.category', 
                'items.product.season', 
                'items.variation', 
                'invoice'
            ])->first();
            
        return view('erp.orderExchange.show', compact('orderReturn', 'exchangeOrder'));
    }

    private function addStockForReturnItem($orderReturn, $item)
    {
        $qty = $item->returned_qty;
        $productId = $item->product_id;
        $variationId = $item->variation_id;
        $toType = $orderReturn->return_to_type;
        $toId = $orderReturn->return_to_id;

        if ($toType == 'branch') {
            if ($variationId) {
                $stock = ProductVariationStock::where('variation_id', $variationId)->where('branch_id', $toId)->first();
                if ($stock) $stock->increment('quantity', $qty);
                else ProductVariationStock::create(['variation_id' => $variationId, 'branch_id' => $toId, 'quantity' => $qty]);
            } else {
                $stock = BranchProductStock::where('branch_id', $toId)->where('product_id', $productId)->first();
                if ($stock) $stock->increment('quantity', $qty);
                else BranchProductStock::create(['branch_id' => $toId, 'product_id' => $productId, 'quantity' => $qty]);
            }
        } else {
            if ($variationId) {
                $stock = ProductVariationStock::where('variation_id', $variationId)->where('warehouse_id', $toId)->first();
                if ($stock) $stock->increment('quantity', $qty);
                else ProductVariationStock::create(['variation_id' => $variationId, 'warehouse_id' => $toId, 'quantity' => $qty]);
            } else {
                $stock = WarehouseProductStock::where('warehouse_id', $toId)->where('product_id', $productId)->first();
                if ($stock) $stock->increment('quantity', $qty);
                else WarehouseProductStock::create(['warehouse_id' => $toId, 'product_id' => $productId, 'quantity' => $qty]);
            }
        }
    }

    private function deductStock($item, $type, $id)
    {
        $qty = $item->quantity;
        $productId = $item->product_id;
        $variationId = $item->variation_id;

        if ($variationId) {
            $stock = ProductVariationStock::where('variation_id', $variationId)->where($type . '_id', $id)->first();
            if (!$stock || $stock->quantity < $qty) throw new \Exception("Insufficient stock for item in " . ucfirst($type));
            $stock->decrement('quantity', $qty);
        } else {
            $stock = ($type == 'branch') 
                ? BranchProductStock::where('branch_id', $id)->where('product_id', $productId)->first()
                : WarehouseProductStock::where('warehouse_id', $id)->where('product_id', $productId)->first();
            if (!$stock || $stock->quantity < $qty) throw new \Exception("Insufficient stock for item in " . ucfirst($type));
            $stock->decrement('quantity', $qty);
        }
        
        $item->current_position_type = $type;
        $item->current_position_id = $id;
        $item->save();
    }
}
