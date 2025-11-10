<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\ProductVariationStock;
use App\Models\Product;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::with(['manager', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $users = \App\Models\User::where('is_admin', 1)->get();
        $branches = \App\Models\Branch::all();
        
        return view('erp.warehouses.index', compact('warehouses', 'users', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = \App\Models\User::where('is_admin', 1)->get();
        $branches = \App\Models\Branch::all(); // Optional for linking to branch
        return view('erp.warehouses.create', compact('users', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id' // Make optional for ecommerce warehouses
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()->route('warehouses.show', $warehouse->id)
            ->with('success', 'Warehouse created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $warehouse = Warehouse::with([
            'manager',
            'branch.employees.user.roles',
            'warehouseProductStocks.product.category'
        ])->findOrFail($id);

        // Get product-level stocks (for products without variations)
        $productStocks = $warehouse->warehouseProductStocks()
            ->with(['product.category'])
            ->get();

        // Get variation-level stocks (for products with variations)
        $variationStocks = ProductVariationStock::where('warehouse_id', $id)
            ->whereNull('branch_id')
            ->with(['variation.product' => function($query) {
                $query->with('category');
            }])
            ->get();

        // Aggregate variation stocks by product
        $variationStockByProduct = [];
        foreach ($variationStocks as $vStock) {
            if ($vStock->variation && $vStock->variation->product) {
                $productId = $vStock->variation->product->id;
                if (!isset($variationStockByProduct[$productId])) {
                    $variationStockByProduct[$productId] = [
                        'product' => $vStock->variation->product,
                        'quantity' => 0,
                        'stock_type' => 'variation'
                    ];
                }
                $variationStockByProduct[$productId]['quantity'] += $vStock->quantity;
            }
        }

        // Combine product-level and variation-level stocks
        $allProducts = collect();
        
        // Add product-level stocks
        foreach ($productStocks as $stock) {
            // Only include if product doesn't have variations (to avoid duplicates)
            if (!$stock->product || !$stock->product->has_variations) {
                $allProducts->push([
                    'product' => $stock->product,
                    'quantity' => $stock->quantity,
                    'stock_type' => 'product',
                    'created_at' => $stock->created_at
                ]);
            }
        }

        // Add variation-level stocks (aggregated by product)
        foreach ($variationStockByProduct as $productId => $data) {
            $allProducts->push([
                'product' => $data['product'],
                'quantity' => $data['quantity'],
                'stock_type' => 'variation',
                'created_at' => $data['product']->created_at ?? now()
            ]);
        }

        // Sort by created_at and get recent 10
        $recent_products = $allProducts->sortByDesc('created_at')->take(10)->values();

        // Dynamic counts - count unique products
        $products_count = $allProducts->count();
        $employees_count = $warehouse->branch ? $warehouse->branch->employees->count() : 0;

        // Get employees with their roles through branch
        $employees = collect();
        if ($warehouse->branch) {
            $employees = $warehouse->branch->employees()
                ->with(['user.roles'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('erp.warehouses.show', compact(
            'warehouse',
            'products_count',
            'employees_count',
            'recent_products',
            'employees'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id' // Make optional for ecommerce warehouses
        ]);

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->name = $validated['name'];
        $warehouse->location = $validated['location'];
        $warehouse->manager_id = $validated['manager_id'] ?? null;
        $warehouse->branch_id = $validated['branch_id'] ?? null; // Allow null for ecommerce warehouses

        $warehouse->save();

        return redirect()->back()->with('success', 'Warehouse updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully!');
    }

    public function storeWarehousePerBranch(Request $request, $branchId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id'
        ]);

        $warehouse = new Warehouse();
        $warehouse->name = $validated['name'];
        $warehouse->location = $validated['location'];
        $warehouse->manager_id = $validated['manager_id'] ?? null;
        $warehouse->branch_id = $branchId;

        $warehouse->save();

        return redirect()->back()->with('success', 'Warehouse created successfully!');
    }
}
