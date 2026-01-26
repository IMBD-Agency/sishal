<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductVariationStock;
use App\Models\WarehouseProductStock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function stocklist(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view product stock list')) {
            abort(403, 'Unauthorized action.');
        }

        $branches = Branch::all();
        $warehouses = Warehouse::all();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get();
        $brands = \App\Models\Brand::where('status', 'active')->orderBy('name')->get();
        $seasons = \App\Models\Season::where('status', 'active')->orderBy('name')->get();
        $genders = \App\Models\Gender::all();

        $query = Product::select('id', 'name', 'sku', 'style_number', 'price', 'category_id', 'brand_id', 'season_id', 'gender_id', 'image', 'has_variations')
            ->with([
                'category:id,name', 
                'brand:id,name',
                'season:id,name',
                'gender:id,name'
            ]);

        // Optimized Database Aggregation for Big Data
        $query->withSum(['branchStock as simple_branch_stock' => function($q) use ($request) {
            if ($request->filled('branch_id')) { $q->where('branch_id', $request->branch_id); }
        }], 'quantity');

        $query->withSum(['warehouseStock as simple_warehouse_stock' => function($q) use ($request) {
            if ($request->filled('warehouse_id')) { $q->where('warehouse_id', $request->warehouse_id); }
        }], 'quantity');

        $query->withSum(['variationStocks as var_stock' => function($q) use ($request) {
            if ($request->filled('branch_id')) { $q->where('branch_id', $request->branch_id); }
            if ($request->filled('warehouse_id')) { $q->where('warehouse_id', $request->warehouse_id); }
        }], 'quantity');

        // Filter by product name or SKU/Style Number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%")
                  ->orWhere('style_number', 'like', "%$search%");
            });
        }

        // Optimized Location Filters
        if ($request->filled('branch_id')) {
            $branchId = $request->branch_id;
            $query->where(function($q) use ($branchId) {
                $q->whereHas('branchStock', function($sq) use ($branchId) { $sq->where('branch_id', $branchId); })
                  ->orWhereHas('variations.stocks', function($sq) use ($branchId) { $sq->where('branch_id', $branchId); });
            });
        }

        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->warehouse_id;
            $query->where(function($q) use ($warehouseId) {
                $q->whereHas('warehouseStock', function($sq) use ($warehouseId) { $sq->where('warehouse_id', $warehouseId); })
                  ->orWhereHas('variations.stocks', function($sq) use ($warehouseId) { $sq->where('warehouse_id', $warehouseId); });
            });
        }

        // Product Attribute Filters
        if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
        if ($request->filled('brand_id')) { $query->where('brand_id', $request->brand_id); }
        if ($request->filled('season_id')) { $query->where('season_id', $request->season_id); }
        if ($request->filled('gender_id')) { $query->where('gender_id', $request->gender_id); }

        // Low Stock Filter
        if ($request->boolean('low_stock')) {
            // Check if total aggregated stock is low
            $query->where(function($q) {
                $q->whereHas('branchStock', function($sq) { $sq->havingRaw('SUM(quantity) <= 5'); })
                  ->orWhereHas('variationStocks', function($sq) { $sq->havingRaw('SUM(quantity) <= 5'); });
            });
        }

        $productStocks = $query->latest()->paginate(20)->appends($request->except('page'));

        // Load breakdown relations ONLY for the the 20 items on the current page
        $productStocks->load([
            'branchStock.branch:id,name', 
            'warehouseStock.warehouse:id,name', 
            'variations.stocks.branch:id,name', 
            'variations.stocks.warehouse:id,name'
        ]);
        
        return view('erp.productStock.productStockList', compact(
            'productStocks', 'branches', 'warehouses', 'categories', 'brands', 'seasons', 'genders'
        ));
    }

    public function exportStockExcel(Request $request)
    {
        $products = $this->getStockQuery($request)->get();
        // Simplified Excel export using similar logic to adjustments
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Name', 'Style Number', 'Category', 'Brand', 'Total Stock'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
        }
        
        $row = 2;
        foreach ($products as $product) {
            $total = 0;
            if ($product->has_variations) {
                foreach($product->variations as $v) { $total += $v->stocks->sum('quantity'); }
            } else {
                $total = $product->branchStock->sum('quantity') + $product->warehouseStock->sum('quantity');
            }
            
            $sheet->setCellValue('A' . $row, $product->name);
            $sheet->setCellValue('B' . $row, $product->style_number ?? $product->sku);
            $sheet->setCellValue('C' . $row, $product->category->name ?? '-');
            $sheet->setCellValue('D' . $row, $product->brand->name ?? '-');
            $sheet->setCellValue('E' . $row, $total);
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'inventory_report_' . date('Y-m-d') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportStockPdf(Request $request)
    {
        $products = $this->getStockQuery($request)->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.productStock.stock-report-pdf', compact('products'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('inventory_report_' . date('Y-m-d') . '.pdf');
    }

    private function getStockQuery(Request $request)
    {
        $query = Product::with(['branchStock', 'warehouseStock', 'category', 'brand', 'variations.stocks']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('style_number', 'like', "%$search%");
            });
        }

        if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
        if ($request->filled('branch_id')) { $query->whereHas('branchStock', function($q) use ($request) { $q->where('branch_id', $request->branch_id); }); }
        
        if ($request->boolean('low_stock')) {
            $query->where(function($q) {
                $q->whereHas('branchStock', function($sq) { $sq->havingRaw('SUM(quantity) <= 5'); });
            });
        }

        return $query;
    }

    public function addStockToBranches(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branches' => 'required|array',
            'branches.*' => 'exists:branches,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:1',
        ]);

        $productId = $request->product_id;
        $branches = $request->branches;
        $quantities = $request->quantities;

        \Log::alert($request->all());

        foreach ($branches as $i => $branchId) {
            $quantity = $quantities[$i];
            $stock = \App\Models\BranchProductStock::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->first();
            if ($stock) {
                $newQuantity = $stock->quantity + $quantity;
                $stock->update([
                    'quantity' => $newQuantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            } else {
                \App\Models\BranchProductStock::create([
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock added to branches successfully.']);
    }

    public function addStockToWarehouses(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouses' => 'required|array',
            'warehouses.*' => 'exists:warehouses,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:1',
        ]);

        $productId = $request->product_id;
        $warehouses = $request->warehouses;
        $quantities = $request->quantities;

        foreach ($warehouses as $i => $warehouseId) {
            $quantity = $quantities[$i];
            $stock = \App\Models\WarehouseProductStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();
            if ($stock) {
                $newQuantity = $stock->quantity + $quantity;
                $stock->update([
                    'quantity' => $newQuantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            } else {
                \App\Models\WarehouseProductStock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock added to warehouses successfully.']);
    }

    public function adjustStock(Request $request)
    {
        $request->validate([
            'location_type' => 'required|in:branch,warehouse',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:stock_in,stock_out',
            'quantity' => 'required|numeric|min:1',
        ]);

        // Validate location ID based on location type
        if ($request->location_type == 'branch') {
            $request->validate(['branch_id' => 'required|exists:branches,id']);
        } else {
            $request->validate(['warehouse_id' => 'required|exists:warehouses,id']);
        }

        // If a variation_id is provided, adjust variation stock; otherwise fall back to product-level stock
        $isVariation = $request->filled('variation_id');

        if($request->location_type == 'branch')
        {
            if ($isVariation) {
                $stock = ProductVariationStock::where('variation_id', $request->variation_id)
                    ->where('branch_id', $request->branch_id)
                    ->whereNull('warehouse_id')
                    ->first();

                if ($stock) {
                    if($request->type == 'stock_in') {
                        $stock->quantity += $request->quantity;
                    } else {
                        if($stock->quantity >= $request->quantity){
                            $stock->quantity -= $request->quantity;
                        } else {
                            return response()->json(['success' => false, 'message' => 'Insufficient variation stock'], 400);
                        }
                    }
                    $stock->updated_by = auth()->id() ?? 1;
                    $stock->last_updated_at = now();
                    $stock->save();
                } else {
                    if($request->type == 'stock_in') {
                        ProductVariationStock::create([
                            'variation_id' => $request->variation_id,
                            'branch_id' => $request->branch_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No variation stock to decrement for this branch.'], 400);
                    }
                }
            } else {
                $branchStock = BranchProductStock::where('branch_id', $request->branch_id)->where('product_id', $request->product_id)->first();
                if ($branchStock) {
                    if($request->type == 'stock_in')
                    {
                        $branchStock->quantity += $request->quantity;
                    }else{
                        if($branchStock->quantity > 0){
                            $branchStock->quantity -= $request->quantity;
                        }else{
                            return response()->json(['success' => false, 'message' => 'Stock is already empty'], 400);
                        }
                    }
                    $branchStock->save();
                } else {
                    if($request->type == 'stock_in') {
                        BranchProductStock::create([
                            'branch_id' => $request->branch_id,
                            'product_id' => $request->product_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No stock found for this branch and product. Cannot stock out.'], 400);
                    }
                }
            }
        } else {
            if ($isVariation) {
                $stock = ProductVariationStock::where('variation_id', $request->variation_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->whereNull('branch_id')
                    ->first();

                if ($stock) {
                    if($request->type == 'stock_in') {
                        $stock->quantity += $request->quantity;
                    } else {
                        if($stock->quantity >= $request->quantity){
                            $stock->quantity -= $request->quantity;
                        } else {
                            return response()->json(['success' => false, 'message' => 'Insufficient variation stock'], 400);
                        }
                    }
                    $stock->updated_by = auth()->id() ?? 1;
                    $stock->last_updated_at = now();
                    $stock->save();
                } else {
                    if($request->type == 'stock_in') {
                        ProductVariationStock::create([
                            'variation_id' => $request->variation_id,
                            'warehouse_id' => $request->warehouse_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No variation stock to decrement for this warehouse.'], 400);
                    }
                }
            } else {
                $warehouseStock = WarehouseProductStock::where('warehouse_id', $request->warehouse_id)->where('product_id', $request->product_id)->first();
                if ($warehouseStock) {
                    if($request->type == 'stock_in')
                    {
                        $warehouseStock->quantity += $request->quantity;
                    } else{
                        if($warehouseStock->quantity > 0)
                        {
                            $warehouseStock->quantity -= $request->quantity;
                        }else{
                            return response()->json(['success' => false, 'message' => 'Stock is already empty'], 400);
                        }
                    }
                    $warehouseStock->save();
                } else {
                    if($request->type == 'stock_in') {
                        WarehouseProductStock::create([
                            'warehouse_id' => $request->warehouse_id,
                            'product_id' => $request->product_id,
                            'quantity' => $request->quantity,
                            'updated_by' => auth()->id() ?? 1,
                            'last_updated_at' => now(),
                        ]);
                    } else {
                        return response()->json(['success' => false, 'message' => 'No stock found for this warehouse and product. Cannot stock out.'], 400);
                    }
                }
            }
        }
        
        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }

    public function getCurrentStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_type' => 'required|in:branch,warehouse',
        ]);

        // Validate location ID based on location type
        if ($request->location_type == 'branch') {
            $request->validate(['branch_id' => 'required|exists:branches,id']);
        } else {
            $request->validate(['warehouse_id' => 'required|exists:warehouses,id']);
        }

        $productId = $request->product_id;
        $variationId = $request->variation_id ?? null;
        $locationType = $request->location_type;
        $quantity = 0;

        if ($variationId) {
            // Get variation stock
            if ($locationType == 'branch') {
                $stock = ProductVariationStock::where('variation_id', $variationId)
                    ->where('branch_id', $request->branch_id)
                    ->whereNull('warehouse_id')
                    ->first();
            } else {
                $stock = ProductVariationStock::where('variation_id', $variationId)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->whereNull('branch_id')
                    ->first();
            }
            $quantity = $stock ? $stock->quantity : 0;
        } else {
            // Get product-level stock
            if ($locationType == 'branch') {
                $stock = BranchProductStock::where('product_id', $productId)
                    ->where('branch_id', $request->branch_id)
                    ->first();
            } else {
                $stock = WarehouseProductStock::where('product_id', $productId)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();
            }
            $quantity = $stock ? $stock->quantity : 0;
        }

        return response()->json([
            'success' => true,
            'quantity' => $quantity
        ]);
    }
    public function adjustmentCreate()
    {
        if (!auth()->user()->hasPermissionTo('view product stock list')) {
            abort(403, 'Unauthorized action.');
        }

        $branches = Branch::all();
        $warehouses = Warehouse::all();
        
        return view('erp.productStock.createAdjustment', compact('branches', 'warehouses'));
    }

    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric',
        ]);

        \DB::beginTransaction();
        try {
            $branchId = $request->branch_id;
            $userId = auth()->id() ?? 1;

            // Create adjustment header
            $adjustment = \App\Models\StockAdjustment::create([
                'adjustment_number' => 'ADJ-' . time() . rand(10, 99),
                'date' => now(),
                'branch_id' => $branchId,
                'notes' => $request->note,
                'created_by' => $userId,
            ]);

            foreach ($request->items as $itemData) {
                $productId = $itemData['product_id'];
                $variationId = $itemData['variation_id'] ?? null;
                $newQty = $itemData['quantity'];
                
                $oldQty = 0;
                
                if ($variationId) {
                    $stock = ProductVariationStock::where('variation_id', $variationId)
                        ->where('branch_id', $branchId)
                        ->whereNull('warehouse_id')
                        ->first();
                        
                    if ($stock) {
                        $oldQty = $stock->quantity;
                        $stock->quantity = $newQty;
                        $stock->updated_by = $userId;
                        $stock->last_updated_at = now();
                        $stock->save();
                    } else {
                        ProductVariationStock::create([
                            'variation_id' => $variationId,
                            'branch_id' => $branchId,
                            'quantity' => $newQty,
                            'updated_by' => $userId,
                            'last_updated_at' => now(),
                        ]);
                    }
                } else {
                    $stock = BranchProductStock::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->first();
                        
                    if ($stock) {
                        $oldQty = $stock->quantity;
                        $stock->quantity = $newQty;
                        $stock->updated_by = $userId;
                        $stock->last_updated_at = now();
                        $stock->save();
                    } else {
                        BranchProductStock::create([
                            'product_id' => $productId,
                            'branch_id' => $branchId,
                            'quantity' => $newQty,
                            'updated_by' => $userId,
                            'last_updated_at' => now(),
                        ]);
                    }
                }

                // Record item
                $adjustment->items()->create([
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $newQty,
                ]);
            }

            \DB::commit();
            return redirect()->route('stock.adjustment.list')->with('success', 'Stock adjusted successfully and record saved!');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Stock Adjustment Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    public function adjustmentList(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view product stock list')) {
            abort(403, 'Unauthorized action.');
        }

        $query = \App\Models\StockAdjustmentItem::with(['adjustment.branch', 'adjustment.creator', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation']);

        // Reports Filter logic (applied to parent adjustment)
        $reportType = $request->get('report_type', 'daily');
        if ($reportType == 'monthly') {
            $month = $request->get('month', date('n'));
            $year = $request->get('year', date('Y'));
            $query->whereHas('adjustment', function($q) use ($month, $year) {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            });
        } elseif ($reportType == 'yearly') {
            $year = $request->get('year', date('Y'));
            $query->whereHas('adjustment', function($q) use ($year) {
                $q->whereYear('date', $year);
            });
        } else {
            if ($request->filled('start_date')) {
                $query->whereHas('adjustment', function($q) use ($request) {
                    $q->where('date', '>=', $request->start_date);
                });
            }
            if ($request->filled('end_date')) {
                $query->whereHas('adjustment', function($q) use ($request) {
                    $q->where('date', '<=', $request->end_date);
                });
            }
        }

        // Parent Adjustment Filters
        if ($request->filled('adjustment_number')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('adjustment_number', 'like', '%' . $request->adjustment_number . '%');
            });
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        // Item/Product Filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('style_number')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('style_number', 'like', '%' . $request->style_number . '%');
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

        $adjustments = $query->latest()->paginate(20)->appends($request->except('page'));

        if ($request->ajax()) {
            return view('erp.productStock.components.adjustmentTable', compact('adjustments'))->render();
        }

        $branches = Branch::all();
        $products = Product::orderBy('name')->get();
        $categories = \App\Models\ProductServiceCategory::whereNull('parent_id')->get();
        $brands = \App\Models\Brand::all();
        $seasons = \App\Models\Season::all();
        $genders = \App\Models\Gender::all();

        return view('erp.productStock.adjustmentList', compact(
            'adjustments', 'branches', 'products', 'categories', 'brands', 'seasons', 'genders', 'reportType'
        ));
    }

    public function exportAdjustmentExcel(Request $request)
    {
        $items = $this->getAdjustmentQuery($request)->get();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Serial No', 'Invoice', 'Date', 'Category', 'Brand', 'Season', 'Gender', 'Product Name', 'Style Number', 'Old Qty', 'New Qty', 'Diff', 'Adjusted By'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
        }
        
        $row = 2;
        foreach ($items as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->adjustment->adjustment_number);
            $sheet->setCellValue('C' . $row, $item->adjustment->date);
            $sheet->setCellValue('D' . $row, $item->product->category->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->product->brand->name ?? '-');
            $sheet->setCellValue('F' . $row, $item->product->season->name ?? '-');
            $sheet->setCellValue('G' . $row, $item->product->gender->name ?? '-');
            $sheet->setCellValue('H' . $row, $item->product->name);
            $sheet->setCellValue('I' . $row, $item->product->style_number);
            $sheet->setCellValue('J' . $row, $item->old_quantity);
            $sheet->setCellValue('K' . $row, $item->new_quantity);
            $sheet->setCellValue('L' . $row, $item->new_quantity - $item->old_quantity);
            $sheet->setCellValue('M' . $row, $item->adjustment->creator->name ?? 'Admin');
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'stock_adjustments_' . date('Y-m-d') . '.xlsx';
        $path = storage_path('app/public/' . $filename);
        $writer->save($path);
        
        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportAdjustmentPdf(Request $request)
    {
        $adjustments = $this->getAdjustmentQuery($request)->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('erp.productStock.adjustment-report-pdf', compact('adjustments'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('stock_adjustments_' . date('Y-m-d') . '.pdf');
    }

    private function getAdjustmentQuery(Request $request)
    {
        $query = \App\Models\StockAdjustmentItem::with(['adjustment.branch', 'adjustment.creator', 'product.category', 'product.brand', 'product.season', 'product.gender', 'variation']);

        if ($request->filled('report_type')) {
            $reportType = $request->report_type;
            if ($reportType == 'monthly') {
                $month = $request->get('month', date('n'));
                $year = $request->get('year', date('Y'));
                $query->whereHas('adjustment', function($q) use ($month, $year) {
                    $q->whereMonth('date', $month)->whereYear('date', $year);
                });
            } elseif ($reportType == 'yearly') {
                $year = $request->get('year', date('Y'));
                $query->whereHas('adjustment', function($q) use ($year) {
                    $q->whereYear('date', $year);
                });
            } else {
                if ($request->filled('start_date')) {
                    $query->whereHas('adjustment', function($q) use ($request) {
                        $q->where('date', '>=', $request->start_date);
                    });
                }
                if ($request->filled('end_date')) {
                    $query->whereHas('adjustment', function($q) use ($request) {
                        $q->where('date', '<=', $request->end_date);
                    });
                }
            }
        }

        if ($request->filled('adjustment_number')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('adjustment_number', 'like', '%' . $request->adjustment_number . '%');
            });
        }
        if ($request->filled('branch_id')) {
            $query->whereHas('adjustment', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('style_number')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('style_number', 'like', '%' . $request->style_number . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        return $query->latest();
    }
}
