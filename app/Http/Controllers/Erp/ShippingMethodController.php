<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view shipping list')) {
            abort(403, 'Unauthorized action.');
        }
        $shippingMethods = ShippingMethod::ordered()->get();
        return view('erp.shipping-methods.index', compact('shippingMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities = \App\Models\City::active()->ordered()->get();
        return view('erp.shipping-methods.create', compact('cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'estimated_days_min' => 'nullable|integer|min:1',
            'estimated_days_max' => 'nullable|integer|min:1|gte:estimated_days_min',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'cities' => 'nullable|array',
            'cities.*' => 'exists:cities,id',
            'city_costs' => 'nullable|array',
            'city_costs.*' => 'nullable|numeric|min:0',
        ]);

        $shippingMethod = ShippingMethod::create($validated);

        // Sync cities with cost overrides
        if ($request->has('cities')) {
            $citiesData = [];
            foreach ($request->cities as $index => $cityId) {
                $costOverride = isset($request->city_costs[$cityId]) && $request->city_costs[$cityId] 
                    ? $request->city_costs[$cityId] 
                    : null;
                $citiesData[$cityId] = ['cost_override' => $costOverride];
            }
            $shippingMethod->cities()->sync($citiesData);
        } else {
            // If no cities selected, remove all city restrictions
            $shippingMethod->cities()->detach();
        }

        return redirect()->route('shipping-methods.index')->with('success', 'Shipping method created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingMethod $shippingMethod)
    {
        return view('erp.shipping-methods.show', compact('shippingMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShippingMethod $shippingMethod)
    {
        $cities = \App\Models\City::active()->ordered()->get();
        $shippingMethod->load('cities');
        return view('erp.shipping-methods.edit', compact('shippingMethod', 'cities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'estimated_days_min' => 'nullable|integer|min:1',
            'estimated_days_max' => 'nullable|integer|min:1|gte:estimated_days_min',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'cities' => 'nullable|array',
            'cities.*' => 'exists:cities,id',
            'city_costs' => 'nullable|array',
            'city_costs.*' => 'nullable|numeric|min:0',
        ]);

        $shippingMethod->update($validated);

        // Sync cities with cost overrides
        if ($request->has('cities')) {
            $citiesData = [];
            foreach ($request->cities as $index => $cityId) {
                $costOverride = isset($request->city_costs[$cityId]) && $request->city_costs[$cityId] 
                    ? $request->city_costs[$cityId] 
                    : null;
                $citiesData[$cityId] = ['cost_override' => $costOverride];
            }
            $shippingMethod->cities()->sync($citiesData);
        } else {
            // If no cities selected, remove all city restrictions
            $shippingMethod->cities()->detach();
        }

        return redirect()->route('shipping-methods.index')->with('success', 'Shipping method updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();
        return redirect()->route('shipping-methods.index')->with('success', 'Shipping method deleted successfully!');
    }
}
