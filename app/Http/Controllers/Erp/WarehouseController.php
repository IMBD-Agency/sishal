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
        $warehouses = Warehouse::with(['manager.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $employees = \App\Models\Employee::with('user')->get();
        
        return view('erp.warehouses.index', compact('warehouses', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = \App\Models\Employee::with('user')->get();
        return view('erp.warehouses.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:active,inactive'
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $warehouse = Warehouse::with([
            'manager.user',
            'branches' => function($query) {
                $query->withCount(['employees', 'branchProductStocks']);
            }
        ])->findOrFail($id);

        $simpleStocks = \App\Models\WarehouseProductStock::with('product')
                        ->where('warehouse_id', $id)
                        ->where('quantity', '>', 0)
                        ->paginate(10, ['*'], 'simple_page');

        $variationStocks = \App\Models\ProductVariationStock::with(['variation.product', 'variation.combinations.attribute', 'variation.combinations.attributeValue'])
                        ->where('warehouse_id', $id)
                        ->where('quantity', '>', 0)
                        ->paginate(10, ['*'], 'variation_page');

        return view('erp.warehouses.show', compact('warehouse', 'simpleStocks', 'variationStocks'));
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
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:active,inactive'
        ]);

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($validated);

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
