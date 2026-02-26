<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view products')) {
            abort(403, 'Unauthorized action.');
        }
        $brands = Brand::latest()->get();
        return view('erp.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Brand::create($validated);
        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $brand->update($validated);
        return redirect()->back()->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $brand->delete();
        return redirect()->back()->with('success', 'Brand deleted successfully.');
    }
}
