<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $query = Coupon::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by code or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('erp.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $categories = ProductServiceCategory::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        
        return view('erp.coupons.create', compact('categories', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:percentage,fixed',
            'value' => 'nullable|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'user_limit' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'free_delivery' => 'nullable|boolean',
            'description' => 'nullable|string',
            'scope_type' => 'required|in:all,categories,products,exclude_categories,exclude_products',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'exists:product_service_categories,id',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'exists:products,id',
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'exists:product_service_categories,id',
            'excluded_products' => 'nullable|array',
            'excluded_products.*' => 'exists:products,id',
        ]);

        // Handle checkbox - if not present in request, it's unchecked (false)
        $freeDelivery = $request->has('free_delivery') && $request->input('free_delivery') == '1';
        $hasDiscount = !empty($validated['value']) && $validated['value'] > 0;

        // Validate that at least one benefit is provided (discount or free delivery)
        if (!$freeDelivery && !$hasDiscount) {
            return back()->withErrors(['value' => 'Either a discount value or free delivery must be enabled.'])->withInput();
        }

        // If discount is provided, type and value are required
        if ($hasDiscount) {
            if (empty($validated['type'])) {
                return back()->withErrors(['type' => 'Discount type is required when providing a discount.'])->withInput();
            }
            // Validate max_discount for percentage type
            if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
                return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%'])->withInput();
            }
        } else {
            // If no discount, set type and value to defaults
            $validated['type'] = 'percentage';
            $validated['value'] = 0;
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
        if (empty($validated['applicable_categories'])) {
            $validated['applicable_categories'] = null;
        }
        if (empty($validated['applicable_products'])) {
            $validated['applicable_products'] = null;
        }
        if (empty($validated['excluded_categories'])) {
            $validated['excluded_categories'] = null;
        }
        if (empty($validated['excluded_products'])) {
            $validated['excluded_products'] = null;
        }

        // Handle checkbox - if not present in request, it's unchecked (false)
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active') == '1';
        $validated['free_delivery'] = $freeDelivery;
        $validated['used_count'] = 0;

        Coupon::create($validated);

        return redirect()->route('coupons.index')
                        ->with('success', 'Coupon created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        if (!auth()->user()->hasPermissionTo('view coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $coupon->load(['usages.user', 'usages.order', 'orders']);
        return view('erp.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coupon $coupon)
    {
        if (!auth()->user()->hasPermissionTo('manage coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $categories = ProductServiceCategory::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        
        return view('erp.coupons.edit', compact('coupon', 'categories', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        if (!auth()->user()->hasPermissionTo('manage coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:percentage,fixed',
            'value' => 'nullable|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'user_limit' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'free_delivery' => 'nullable|boolean',
            'description' => 'nullable|string',
            'scope_type' => 'required|in:all,categories,products,exclude_categories,exclude_products',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'exists:product_service_categories,id',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'exists:products,id',
            'excluded_categories' => 'nullable|array',
            'excluded_categories.*' => 'exists:product_service_categories,id',
            'excluded_products' => 'nullable|array',
            'excluded_products.*' => 'exists:products,id',
        ]);

        // Handle checkbox - if not present in request, it's unchecked (false)
        $freeDelivery = $request->has('free_delivery') && $request->input('free_delivery') == '1';
        $hasDiscount = !empty($validated['value']) && $validated['value'] > 0;

        // Validate that at least one benefit is provided (discount or free delivery)
        if (!$freeDelivery && !$hasDiscount) {
            return back()->withErrors(['value' => 'Either a discount value or free delivery must be enabled.'])->withInput();
        }

        // If discount is provided, type and value are required
        if ($hasDiscount) {
            if (empty($validated['type'])) {
                return back()->withErrors(['type' => 'Discount type is required when providing a discount.'])->withInput();
            }
            // Validate max_discount for percentage type
            if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
                return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%'])->withInput();
            }
        } else {
            // If no discount, set type and value to defaults
            $validated['type'] = 'percentage';
            $validated['value'] = 0;
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
        if (empty($validated['applicable_categories'])) {
            $validated['applicable_categories'] = null;
        }
        if (empty($validated['applicable_products'])) {
            $validated['applicable_products'] = null;
        }
        if (empty($validated['excluded_categories'])) {
            $validated['excluded_categories'] = null;
        }
        if (empty($validated['excluded_products'])) {
            $validated['excluded_products'] = null;
        }

        // Handle checkbox - if not present in request, it's unchecked (false)
        $validated['is_active'] = $request->has('is_active') && $request->input('is_active') == '1';
        $validated['free_delivery'] = $freeDelivery;

        $coupon->update($validated);

        return redirect()->route('coupons.index')
                        ->with('success', 'Coupon updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        if (!auth()->user()->hasPermissionTo('manage coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $coupon->delete();

        return redirect()->route('coupons.index')
                        ->with('success', 'Coupon deleted successfully!');
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus(Coupon $coupon)
    {
        if (!auth()->user()->hasPermissionTo('manage coupons')) {
            abort(403, 'Unauthorized action.');
        }
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return response()->json([
            'success' => true,
            'is_active' => $coupon->is_active,
            'message' => 'Coupon status updated successfully!'
        ]);
    }
}
