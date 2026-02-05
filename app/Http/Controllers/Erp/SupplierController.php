<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();
        
        // Search by multiple fields
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%")
                  ->orWhere('phone', 'LIKE', "%$search%")
                  ->orWhere('company_name', 'LIKE', "%$search%")
                  ->orWhere('tax_number', 'LIKE', "%$search%");
            });
        }

        // Dropdown Filters
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        $suppliers = $query->latest()->paginate(20)->appends($request->all());
        
        // Get unique cities and countries for filters
        $cities = Supplier::whereNotNull('city')->pluck('city')->unique()->sort();
        $countries = Supplier::whereNotNull('country')->pluck('country')->unique()->sort();

        return view('erp.suppliers.index', compact('suppliers', 'cities', 'countries'));
    }

    public function create()
    {
        return view('erp.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(string $id)
    {
        $supplier = Supplier::with('purchases.items')->findOrFail($id);
        return view('erp.suppliers.show', compact('supplier'));
    }

    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('erp.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    public function ledger(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $entries = $supplier->ledgerEntries()->paginate(50);
        return view('erp.suppliers.ledger', compact('supplier', 'entries'));
    }
}
