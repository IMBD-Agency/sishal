<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\ComboProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ComboProductController extends Controller
{
    /**
     * List all combo products
     */
    public function list(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view combos')) {
            abort(403, 'Unauthorized action.');
        }
        $query = Product::where('type', 'combo')
            ->with(['comboItems.product.branchStock', 'comboItems.variation.stocks', 'branch']);
            
        // Filtering
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $combos = $query->orderBy('id', 'desc')->paginate(20);
        $branches = Branch::where('status', 'active')->get();

        return view('erp.combo-products.list', compact('combos', 'branches'));
    }

    /**
     * Get current user branch ID (null for global/super admin)
     */
    private function getUserBranchId()
    {
        $user = Auth::user();
        if (!$user) return null;
        if ($user->hasRole('Super Admin')) return null;
        return $user->employee ? $user->employee->branch_id : null;
    }

    /**
     * Show combo creation form
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('manage combos')) {
            abort(403, 'Unauthorized action.');
        }
        $userBranchId = $this->getUserBranchId();
        
        $availableProducts = Product::where('type', 'product')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get all branches and warehouses for global users
        $branches = Branch::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();

        // Get stock for each product
        foreach ($availableProducts as $product) {
            if ($userBranchId) {
                // Branch user: show only their branch stock
                $product->total_stock = $product->branchStocks
                    ->where('branch_id', $userBranchId)
                    ->sum('quantity');
            } else {
                // Global user: show all branch and warehouse stocks
                $product->total_stock = $product->branchStocks->sum('quantity') + 
                    \App\Models\WarehouseProductStock::where('product_id', $product->id)->sum('quantity');
            }
            $product->branch_stocks = $product->branchStocks->pluck('quantity', 'branch_id');
        }

        return view('erp.combo-products.create', compact('availableProducts', 'branches', 'warehouses', 'userBranchId'));
    }

    /**
     * Store new combo product
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage combos')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Calculate total original price
        $totalOriginalPrice = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $totalOriginalPrice += $product->price * $item['quantity'];
        }

        // Calculate combo price based on discount or manual price
        $comboPrice = $request->price;
        if ($request->discount_percent !== null && $request->discount_percent > 0) {
            $comboPrice = $totalOriginalPrice * (1 - ($request->discount_percent / 100));
        } elseif (!$comboPrice) {
            $comboPrice = $totalOriginalPrice; // No discount, use original price
        }

        // Handle Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = \App\Services\ImageService::compressAndSave(
                file: $request->file('image'),
                directory: 'uploads/products',
                cropSquare: true
            );
        }

        // Create combo product
        $combo = Product::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'slug' => Str::slug($request->name),
            'type' => 'combo',
            'price' => $comboPrice,
            'short_desc' => $request->short_desc,
            'description' => $request->description,
            'image' => $imagePath,
            'branch_id' => $this->getUserBranchId(),
            'status' => 'active',
            'show_in_ecommerce' => true,
        ]);

        // Add items to combo
        foreach ($request->items as $item) {
            ComboProduct::create([
                'combo_product_id' => $combo->id,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'] ?? null,
                'quantity' => $item['quantity'],
                'combo_price' => $item['combo_price'] ?? null,
            ]);
        }

        return redirect()->route('erp.combo-products.index')
            ->with('success', 'Combo created successfully!');
    }

    /**
     * Show combo items for a product
     */
    public function index(Product $product)
    {
        if (!auth()->user()->hasPermissionTo('view combos')) {
            abort(403, 'Unauthorized action.');
        }
        if (!$product->isCombo()) {
            return redirect()->back()->with('error', 'This is not a combo product');
        }

        $userBranchId = $this->getUserBranchId();
        
        $comboItems = $product->comboItems()->with(['product', 'variation'])->get();
        $availableProducts = Product::where('type', 'product')
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->get();

        // Get all branches and warehouses for global users
        $branches = Branch::where('status', 'active')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();

        // Get stock for each product based on user branch
        foreach ($availableProducts as $productItem) {
            if ($userBranchId) {
                // Branch user: show only their branch stock
                $productItem->total_stock = $productItem->branchStocks
                    ->where('branch_id', $userBranchId)
                    ->sum('quantity');
            } else {
                // Global user: show all branch and warehouse stocks
                $productItem->total_stock = $productItem->branchStocks->sum('quantity') + 
                    \App\Models\WarehouseProductStock::where('product_id', $productItem->id)->sum('quantity');
            }
            $productItem->branch_stocks = $productItem->branchStocks->pluck('quantity', 'branch_id');
        }

        return view('erp.combo-products.index', compact('product', 'comboItems', 'availableProducts', 'branches', 'warehouses', 'userBranchId'));
    }

    /**
     * Add item to existing combo
     */
    public function addItem(Request $request, Product $product)
    {
        if (!auth()->user()->hasPermissionTo('manage combos')) {
            abort(403, 'Unauthorized action.');
        }
        if (!$product->isCombo()) {
            return redirect()->back()->with('error', 'This is not a combo product');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
            'combo_price' => 'nullable|numeric|min:0',
        ]);

        ComboProduct::create([
            'combo_product_id' => $product->id,
            'product_id' => $request->product_id,
            'variation_id' => $request->variation_id,
            'quantity' => $request->quantity,
            'combo_price' => $request->combo_price,
        ]);

        return redirect()->back()->with('success', 'Product added to combo successfully');
    }

    /**
     * Remove combo item
     */
    public function destroy(ComboProduct $comboProduct)
    {
        if (!auth()->user()->hasPermissionTo('manage combos')) {
            abort(403, 'Unauthorized action.');
        }
        $comboProduct->delete();
        return redirect()->back()->with('success', 'Item removed from combo');
    }

    /**
     * Delete entire combo product
     */
    public function destroyCombo(Product $product)
    {
        if (!auth()->user()->hasPermissionTo('manage combos')) {
            abort(403, 'Unauthorized action.');
        }
        if (!$product->isCombo()) {
            return redirect()->back()->with('error', 'This is not a combo product');
        }

        // Delete all combo items first
        $product->comboItems()->delete();
        
        // Delete the combo product
        $product->delete();

        return redirect()->route('erp.combo-products.index')
            ->with('success', 'Combo deleted successfully');
    }
}
