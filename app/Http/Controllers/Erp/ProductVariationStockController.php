<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationStock;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductVariationStockController extends Controller
{
    /**
     * Display stock management for a product variation.
     */
    public function index($productId, $variationId)
    {
        $product = Product::findOrFail($productId);
        $variation = ProductVariation::with(['stocks.branch', 'stocks.warehouse'])->findOrFail($variationId);
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        
        return view('erp.products.variations.stock', compact('product', 'variation', 'branches', 'warehouses'));
    }

    /**
     * Add stock to branches for a variation.
     */
    public function addStockToBranches(Request $request, $productId, $variationId)
    {
        $request->validate([
            'branches' => 'required|array',
            'branches.*' => 'exists:branches,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:1',
        ]);

        $variation = ProductVariation::findOrFail($variationId);
        $branches = $request->branches;
        $quantities = $request->quantities;

        foreach ($branches as $i => $branchId) {
            $quantity = $quantities[$i];
            $stock = ProductVariationStock::where('variation_id', $variationId)
                ->where('branch_id', $branchId)
                ->whereNull('warehouse_id')
                ->first();
                
            if ($stock) {
                $newQuantity = $stock->quantity + $quantity;
                $stock->update([
                    'quantity' => $newQuantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            } else {
                ProductVariationStock::create([
                    'variation_id' => $variationId,
                    'branch_id' => $branchId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock added to branches successfully.']);
    }

    /**
     * Add stock to warehouses for a variation.
     */
    public function addStockToWarehouses(Request $request, $productId, $variationId)
    {
        $request->validate([
            'warehouses' => 'required|array',
            'warehouses.*' => 'exists:warehouses,id',
            'quantities' => 'required|array',
            'quantities.*' => 'numeric|min:1',
        ]);

        $variation = ProductVariation::findOrFail($variationId);
        $warehouses = $request->warehouses;
        $quantities = $request->quantities;

        foreach ($warehouses as $i => $warehouseId) {
            $quantity = $quantities[$i];
            $stock = ProductVariationStock::where('variation_id', $variationId)
                ->where('warehouse_id', $warehouseId)
                ->whereNull('branch_id')
                ->first();
                
            if ($stock) {
                $newQuantity = $stock->quantity + $quantity;
                $stock->update([
                    'quantity' => $newQuantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            } else {
                ProductVariationStock::create([
                    'variation_id' => $variationId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $quantity,
                    'updated_by' => auth()->id() ?? 1,
                    'last_updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock added to warehouses successfully.']);
    }

    /**
     * Adjust stock for a variation.
     */
    public function adjustStock(Request $request, $productId, $variationId)
    {
        $request->validate([
            'location_type' => 'required|in:branch,warehouse',
            'branch_id' => 'required_if:location_type,branch|exists:branches,id',
            'warehouse_id' => 'required_if:location_type,warehouse|exists:warehouses,id',
            'type' => 'required|in:stock_in,stock_out',
            'quantity' => 'required|numeric|min:1',
        ]);

        $variation = ProductVariation::findOrFail($variationId);

        if ($request->location_type == 'branch') {
            $stock = ProductVariationStock::where('variation_id', $variationId)
                ->where('branch_id', $request->branch_id)
                ->whereNull('warehouse_id')
                ->first();
                
            if ($stock) {
                if ($request->type == 'stock_in') {
                    $stock->quantity += $request->quantity;
                } else {
                    if ($stock->quantity >= $request->quantity) {
                        $stock->quantity -= $request->quantity;
                    } else {
                        return response()->json(['success' => false, 'message' => 'Insufficient stock'], 400);
                    }
                }
                $stock->updated_by = auth()->id() ?? 1;
                $stock->last_updated_at = now();
                $stock->save();
            } else {
                if ($request->type == 'stock_in') {
                    ProductVariationStock::create([
                        'variation_id' => $variationId,
                        'branch_id' => $request->branch_id,
                        'quantity' => $request->quantity,
                        'updated_by' => auth()->id() ?? 1,
                        'last_updated_at' => now(),
                    ]);
                } else {
                    return response()->json(['success' => false, 'message' => 'No stock found for this branch and variation. Cannot stock out.'], 400);
                }
            }
        } else {
            $stock = ProductVariationStock::where('variation_id', $variationId)
                ->where('warehouse_id', $request->warehouse_id)
                ->whereNull('branch_id')
                ->first();
                
            if ($stock) {
                if ($request->type == 'stock_in') {
                    $stock->quantity += $request->quantity;
                } else {
                    if ($stock->quantity >= $request->quantity) {
                        $stock->quantity -= $request->quantity;
                    } else {
                        return response()->json(['success' => false, 'message' => 'Insufficient stock'], 400);
                    }
                }
                $stock->updated_by = auth()->id() ?? 1;
                $stock->last_updated_at = now();
                $stock->save();
            } else {
                if ($request->type == 'stock_in') {
                    ProductVariationStock::create([
                        'variation_id' => $variationId,
                        'warehouse_id' => $request->warehouse_id,
                        'quantity' => $request->quantity,
                        'updated_by' => auth()->id() ?? 1,
                        'last_updated_at' => now(),
                    ]);
                } else {
                    return response()->json(['success' => false, 'message' => 'No stock found for this warehouse and variation. Cannot stock out.'], 400);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Stock adjusted successfully.']);
    }

    /**
     * Get stock levels for a variation.
     */
    public function getStockLevels($productId, $variationId)
    {
        $variation = ProductVariation::with(['stocks.branch', 'stocks.warehouse'])->findOrFail($variationId);
        
        $stockData = [
            'total_stock' => $variation->total_stock,
            'available_stock' => $variation->available_stock,
            'branch_stocks' => $variation->stocks->where('branch_id', '!=', null)->map(function($stock) {
                return [
                    'id' => $stock->id,
                    'branch_name' => $stock->branch->name ?? 'Unknown Branch',
                    'quantity' => $stock->quantity,
                    'reserved_quantity' => $stock->reserved_quantity,
                    'available_quantity' => $stock->available_quantity,
                    'last_updated_at' => $stock->last_updated_at,
                ];
            }),
            'warehouse_stocks' => $variation->stocks->where('warehouse_id', '!=', null)->map(function($stock) {
                return [
                    'id' => $stock->id,
                    'warehouse_name' => $stock->warehouse->name ?? 'Unknown Warehouse',
                    'quantity' => $stock->quantity,
                    'reserved_quantity' => $stock->reserved_quantity,
                    'available_quantity' => $stock->available_quantity,
                    'last_updated_at' => $stock->last_updated_at,
                ];
            }),
        ];
        
        return response()->json($stockData);
    }
}
