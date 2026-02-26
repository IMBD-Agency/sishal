<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view products')) {
            abort(403, 'Unauthorized action.');
        }
        $units = Unit::latest()->get();
        return view('erp.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
        ]);

        Unit::create($validated);
        return redirect()->back()->with('success', 'Unit created successfully.');
    }

    public function update(Request $request, Unit $unit)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
        ]);

        $unit->update($validated);
        return redirect()->back()->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $unit->delete();
        return redirect()->back()->with('success', 'Unit deleted successfully.');
    }
}
