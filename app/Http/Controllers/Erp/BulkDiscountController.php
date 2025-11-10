<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\BulkDiscount;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BulkDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BulkDiscount::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $discounts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('erp.bulk-discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('status', 'active')->get();
        
        return view('erp.bulk-discounts.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'scope_type' => 'required|in:all,products',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'exists:products,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        // Validate value based on type
        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%'])->withInput();
        }

        // Handle date conversion
        $inputTimezone = env('APP_INPUT_TZ', config('app.timezone', 'UTC'));
        if (!empty($validated['start_date'])) {
            $validated['start_date'] = Carbon::parse($validated['start_date'], $inputTimezone)->utc();
        }
        if (!empty($validated['end_date'])) {
            $validated['end_date'] = Carbon::parse($validated['end_date'], $inputTimezone)->utc();
        }

        // Convert empty arrays to null
        if (empty($validated['applicable_products'])) {
            $validated['applicable_products'] = null;
        }

        // Handle checkbox - if not present in request, it's unchecked (false)
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active') == '1';

        BulkDiscount::create($validated);

        return redirect()->route('bulk-discounts.index')
                        ->with('success', 'Bulk discount created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(BulkDiscount $bulkDiscount)
    {
        return view('erp.bulk-discounts.show', compact('bulkDiscount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BulkDiscount $bulkDiscount)
    {
        $products = Product::where('status', 'active')->get();
        
        return view('erp.bulk-discounts.edit', compact('bulkDiscount', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BulkDiscount $bulkDiscount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'scope_type' => 'required|in:all,products',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'exists:products,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        // Validate value based on type
        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%'])->withInput();
        }

        // Handle date conversion
        $inputTimezone = env('APP_INPUT_TZ', config('app.timezone', 'UTC'));
        if (!empty($validated['start_date'])) {
            $validated['start_date'] = Carbon::parse($validated['start_date'], $inputTimezone)->utc();
        } else {
            $validated['start_date'] = null;
        }
        if (!empty($validated['end_date'])) {
            $validated['end_date'] = Carbon::parse($validated['end_date'], $inputTimezone)->utc();
        } else {
            $validated['end_date'] = null;
        }

        // Convert empty arrays to null
        if (empty($validated['applicable_products'])) {
            $validated['applicable_products'] = null;
        }

        // Handle checkbox - if not present in request, it's unchecked (false)
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active') == '1';

        $bulkDiscount->update($validated);

        return redirect()->route('bulk-discounts.index')
                        ->with('success', 'Bulk discount updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BulkDiscount $bulkDiscount)
    {
        $bulkDiscount->delete();

        return redirect()->route('bulk-discounts.index')
                        ->with('success', 'Bulk discount deleted successfully!');
    }

    /**
     * Toggle discount status
     */
    public function toggleStatus(BulkDiscount $bulkDiscount)
    {
        $bulkDiscount->is_active = !$bulkDiscount->is_active;
        $bulkDiscount->save();

        return response()->json([
            'success' => true,
            'is_active' => $bulkDiscount->is_active,
            'message' => 'Bulk discount status updated successfully!'
        ]);
    }
}
