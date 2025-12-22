<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function categoryList(Request $request)
    {
     
        if (!auth()->user()->hasPermissionTo('view category list')) {
            abort(403, 'Unauthorized action.');
        }
        $query = ProductServiceCategory::with(['parent', 'children'])->whereNull('parent_id');
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%")
                  ;
            });
        }
        $categories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $allCategories = ProductServiceCategory::orderBy('name')->get(['id','name']);
        return view('erp.productCategory.categoryList', compact('categories', 'allCategories'));
    }

    public function subcategoryList(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view subcategory list')) {
            abort(403, 'Unauthorized action.');
        }
        $query = ProductServiceCategory::with('parent')->whereNotNull('parent_id');
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%")
                  ;
            });
        }
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        $subcategories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $parentCategories = ProductServiceCategory::whereNull('parent_id')->orderBy('name')->get(['id','name','slug']);
        return view('erp.productCategory.subcategoryList', compact('subcategories', 'parentCategories'));
    }

    public function storeSubcategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                Rule::unique('product_service_categories', 'slug')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                })
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'status' => 'nullable|in:active,inactive',
            'parent_id' => 'required|exists:product_service_categories,id',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'status', 'parent_id']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
            $data['image'] = 'uploads/categories/' . $imageName;
        }

        ProductServiceCategory::create($data);

        return redirect()->back()->with('success', 'Subcategory created successfully!');
    }

    public function updateSubcategory(Request $request, $id)
    {
        $subcategory = ProductServiceCategory::findOrFail($id);
        // Handle AJAX status toggle
        if ($request->ajax() && $request->has('status')) {
            $subcategory->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                Rule::unique('product_service_categories', 'slug')
                    ->ignore($subcategory->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('parent_id', $request->parent_id);
                    })
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'status' => 'nullable|in:active,inactive',
            'parent_id' => 'required|exists:product_service_categories,id',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'status', 'parent_id']);

        if ((int)$data['parent_id'] === (int)$subcategory->id) {
            return redirect()->back()->withErrors(['parent_id' => 'A subcategory cannot be its own parent.'])->withInput();
        }

        if ($request->hasFile('image')) {
            if ($subcategory->image && file_exists(public_path($subcategory->image))) {
                @unlink(public_path($subcategory->image));
            }
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
            $data['image'] = 'uploads/categories/' . $imageName;
        }

        $subcategory->update($data);

        return redirect()->back()->with('success', 'Subcategory updated successfully!');
    }

    public function deleteSubcategory($id)
    {
        $subcategory = ProductServiceCategory::findOrFail($id);
        if ($subcategory->image && file_exists(public_path($subcategory->image))) {
            @unlink(public_path($subcategory->image));
        }
        $subcategory->delete();
        return redirect()->back()->with('success', 'Subcategory deleted successfully!');
    }

    public function storeCategory(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('create category')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:product_service_categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'status' => 'nullable|in:active,inactive',
            'parent_id' => 'nullable|exists:product_service_categories,id',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'status', 'parent_id']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
            $data['image'] = 'uploads/categories/' . $imageName;
        }

        ProductServiceCategory::create($data);
        
        // Clear category caches
        \App\Services\CacheService::clearCategoryCaches();

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    
    public function updateCategory(Request $request, $id)
    {
        $category = ProductServiceCategory::findOrFail($id);
        
        // Handle AJAX status toggle
        if ($request->ajax() && $request->has('status')) {
            $category->update(['status' => $request->status]);
            // If parent category is set to inactive, cascade to subcategories
            if ($request->status === 'inactive') {
                ProductServiceCategory::where('parent_id', $category->id)->update(['status' => 'inactive']);
            }
            return response()->json(['success' => true]);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [Rule::unique('product_service_categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->only(['name', 'slug', 'description', 'status']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && file_exists(public_path($category->image))) {
                @unlink(public_path($category->image));
            }
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);
            $data['image'] = 'uploads/categories/' . $imageName;
        }

        $category->update($data);

        // Cascade inactivation to subcategories when status explicitly provided
        if (array_key_exists('status', $data) && $data['status'] === 'inactive') {
            ProductServiceCategory::where('parent_id', $category->id)->update(['status' => 'inactive']);
        }
        
        // Clear category caches
        \App\Services\CacheService::clearCategoryCaches();

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function deleteCategory($id)
    {
        $category = ProductServiceCategory::findOrFail($id);
        // Delete image file if exists
        if ($category->image && file_exists(public_path($category->image))) {
            @unlink(public_path($category->image));
        }
        $category->delete();
        
        // Clear category caches
        \App\Services\CacheService::clearCategoryCaches();
        
        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function index(Request $request)
    {
        if (auth()->user()->hasPermissionTo('view products list')) {
            $query = Product::query();

            // Filter by category if provided
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
    
            // Search by product name or SKU if provided
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('sku', 'like', "%$search%")
                      ;
                });
            }
    
            $products = $query->with(['category', 'variations.stocks', 'branchStock', 'warehouseStock'])
                ->latest()
                ->paginate(12)
                ->withQueryString();
    
            return view('erp.products.productlist', compact('products'));
        }
        else{
            abort(403, 'Unauthorized action.');
        }
    }

    public function create()
    {
        $attributes = \App\Models\Attribute::where('status', 'active')->orderBy('name')->get();
        return view('erp.products.create', compact('attributes'));
    }
    
    public function store(Request $request)
    {
        // Debug: Log the request data
        \Log::info('Product Store Request Data:', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:products,slug',
            'sku' => 'required|string|unique:products,sku',
            'short_desc' => 'nullable|string',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'category_id' => 'required|exists:product_service_categories,id',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'cost' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'size_chart' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'gallery' => 'nullable',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'status' => 'nullable|in:active,inactive',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.*' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'nullable|exists:attributes,id',
            'attributes.*.value' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'slug', 'sku', 'short_desc', 'description', 'features', 'category_id', 'price', 'discount', 'cost', 'status', 'meta_title', 'meta_description']);
        $data['type'] = 'product'; // Always set type to product
        $data['has_variations'] = $request->boolean('has_variations');
        $data['manage_stock'] = $request->boolean('manage_stock');
        
        // Handle meta_keywords array - convert to JSON string for storage
        if ($request->has('meta_keywords') && is_array($request->meta_keywords)) {
            // Filter out empty keywords
            $keywords = array_filter($request->meta_keywords, function($keyword) {
                return !empty(trim($keyword));
            });
            $data['meta_keywords'] = json_encode(array_values($keywords));
        }

        // Handle main image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $data['image'] = 'uploads/products/' . $imageName;
        }

        // Handle size chart image upload
        if ($request->hasFile('size_chart')) {
            $sizeChart = $request->file('size_chart');
            $sizeChartName = time().'_'.uniqid().'_sizechart.'.$sizeChart->getClientOriginalExtension();
            $sizeChart->move(public_path('uploads/products'), $sizeChartName);
            $data['size_chart'] = 'uploads/products/' . $sizeChartName;
        }

        $product = Product::create($data);

        // Handle gallery images upload
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $galleryImage) {
                $galleryImageName = time().'_'.uniqid().'.'.$galleryImage->getClientOriginalExtension();
                $galleryImage->move(public_path('uploads/products/gallery'), $galleryImageName);
                $product->galleries()->create([
                    'image' => 'uploads/products/gallery/' . $galleryImageName
                ]);
            }
        }

        // Handle product attributes (specifications)
        if ($request->has('attributes')) {
            // Get attributes data properly
            $attributesData = $request->get('attributes');
            
            foreach ($attributesData as $attributeData) {
                // Check if both attribute_id and value are not empty
                if (!empty($attributeData['attribute_id']) && 
                    !empty($attributeData['value']) && 
                    trim($attributeData['value']) !== '') {
                    
                    $product->productAttributes()->attach($attributeData['attribute_id'], [
                        'value' => trim($attributeData['value'])
                    ]);
                }
            }
        }

        // Clear product cache after creating new product
        $this->clearProductCache($product->id);

        return redirect()->route('product.list')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with([
            'category', 
            'galleries',
            'branchStock.branch',
            'warehouseStock.warehouse',
            'saleItems.invoice',
            'variations.stocks.branch',
            'variations.stocks.warehouse'
        ])->findOrFail($id);
        
        // Calculate units sold from POS items
        $unitsSold = $product->saleItems->sum('quantity');
        
        // Calculate total stock - if product has variations, sum variation stocks; otherwise sum direct product stocks
        $totalStock = 0;
        $branchStocksData = [];
        $warehouseStocksData = [];
        
        if ($product->has_variations && $product->variations && $product->variations->isNotEmpty()) {
            // Product has variations - calculate from variation stocks
            foreach ($product->variations as $variation) {
                if ($variation->stocks && $variation->stocks->isNotEmpty()) {
                    foreach ($variation->stocks as $stock) {
                        $totalStock += $stock->quantity;
                        
                        if ($stock->branch_id && $stock->branch) {
                            $branchName = $stock->branch->name ?? 'Unknown Branch';
                            if (!isset($branchStocksData[$branchName])) {
                                $branchStocksData[$branchName] = [
                                    'branch_name' => $branchName,
                                    'quantity' => 0,
                                    'updated_at' => $stock->last_updated_at ?? $stock->updated_at
                                ];
                            }
                            $branchStocksData[$branchName]['quantity'] += $stock->quantity;
                        }
                        
                        if ($stock->warehouse_id && $stock->warehouse) {
                            $warehouseName = $stock->warehouse->name ?? 'Unknown Warehouse';
                            if (!isset($warehouseStocksData[$warehouseName])) {
                                $warehouseStocksData[$warehouseName] = [
                                    'warehouse_name' => $warehouseName,
                                    'quantity' => 0,
                                    'updated_at' => $stock->last_updated_at ?? $stock->updated_at
                                ];
                            }
                            $warehouseStocksData[$warehouseName]['quantity'] += $stock->quantity;
                        }
                    }
                }
            }
            
            $branchStocks = collect(array_values($branchStocksData));
            $warehouseStocks = collect(array_values($warehouseStocksData));
        } else {
            // Product doesn't have variations - use direct product stocks
            $branchStocks = $product->branchStock->map(function($stock) {
                return [
                    'branch_name' => $stock->branch->name ?? 'Unknown Branch',
                    'quantity' => $stock->quantity,
                    'updated_at' => $stock->last_updated_at ?? $stock->updated_at
                ];
            });
            
            $warehouseStocks = $product->warehouseStock->map(function($stock) {
                return [
                    'warehouse_name' => $stock->warehouse->name ?? 'Unknown Warehouse',
                    'quantity' => $stock->quantity,
                    'updated_at' => $stock->last_updated_at ?? $stock->updated_at
                ];
            });
            
            $totalStock = $branchStocks->sum('quantity') + $warehouseStocks->sum('quantity');
        }
        
        // Get recent activity (recent sales)
        $recentActivity = $product->saleItems()
            ->with(['invoice'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'sale',
                    'description' => 'Order fulfilled',
                    'details' => $item->quantity . ' units sold',
                    'time' => $item->created_at->diffForHumans(),
                    'date' => $item->created_at
                ];
            });
        
        return view('erp.products.show', compact(
            'product', 
            'unitsSold',
            'branchStocks', 
            'warehouseStocks', 
            'recentActivity',
            'totalStock'
        ));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::with('category.parent', 'galleries', 'productAttributes')->findOrFail($id);
        $attributes = \App\Models\Attribute::where('status', 'active')->orderBy('name')->get();
        return view('erp.products.edit', compact('product', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Debug: Log the request data
        \Log::info('Product Update Request Data:', $request->all());
        
        $product = Product::with('galleries')->findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'unique:products,slug,' . $product->id,
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'short_desc' => 'nullable|string',
            'description' => 'nullable|string',
            'features' => 'nullable|string',
            'category_id' => 'required|exists:product_service_categories,id',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'cost' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'size_chart' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'gallery' => 'nullable',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable|in:active,inactive',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.*' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'nullable|exists:attributes,id',
            'attributes.*.value' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'slug', 'sku', 'short_desc', 'description', 'features', 'category_id', 'price', 'discount', 'cost', 'status', 'meta_title', 'meta_description']);
        $data['type'] = 'product'; // Always set type to product
        
        // Handle meta_keywords array - convert to JSON string for storage
        if ($request->has('meta_keywords') && is_array($request->meta_keywords)) {
            // Filter out empty keywords
            $keywords = array_filter($request->meta_keywords, function($keyword) {
                return !empty(trim($keyword));
            });
            $data['meta_keywords'] = json_encode(array_values($keywords));
        }

        // Handle main image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            $image = $request->file('image');
            $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/products'), $imageName);
            $data['image'] = 'uploads/products/' . $imageName;
        }

        // Handle size chart image deletion
        if ($request->has('delete_size_chart') && $request->delete_size_chart == '1') {
            // Delete old size chart if exists
            if ($product->size_chart && file_exists(public_path($product->size_chart))) {
                @unlink(public_path($product->size_chart));
            }
            $data['size_chart'] = null;
        }

        // Handle size chart image upload
        if ($request->hasFile('size_chart')) {
            // Delete old size chart if exists
            if ($product->size_chart && file_exists(public_path($product->size_chart))) {
                @unlink(public_path($product->size_chart));
            }
            $sizeChart = $request->file('size_chart');
            $sizeChartName = time().'_'.uniqid().'_sizechart.'.$sizeChart->getClientOriginalExtension();
            $sizeChart->move(public_path('uploads/products'), $sizeChartName);
            $data['size_chart'] = 'uploads/products/' . $sizeChartName;
        }

        $product->update($data);

        // Handle gallery images upload
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $galleryImage) {
                $galleryImageName = time().'_'.uniqid().'.'.$galleryImage->getClientOriginalExtension();
                $galleryImage->move(public_path('uploads/products/gallery'), $galleryImageName);
                $product->galleries()->create([
                    'image' => 'uploads/products/gallery/' . $galleryImageName
                ]);
            }
        }

        // Handle product attributes (specifications) - sync to replace existing
        \Log::info('Attributes data received:', $request->get('attributes', []));
        
        // Always detach existing attributes first
        $product->productAttributes()->detach();
        \Log::info('Detached existing attributes for product:', ['product_id' => $product->id]);
        
        if ($request->has('attributes')) {
            // Get attributes data properly
            $attributesData = $request->get('attributes');
            \Log::info('Processing attributes data:', $attributesData);
            
            foreach ($attributesData as $index => $attributeData) {
                \Log::info("Processing attribute {$index}:", $attributeData);
                
                // Check if both attribute_id and value are not empty
                if (!empty($attributeData['attribute_id']) && 
                    !empty($attributeData['value']) && 
                    trim($attributeData['value']) !== '') {
                    
                    \Log::info('Adding attribute to product:', [
                        'product_id' => $product->id,
                        'attribute_id' => $attributeData['attribute_id'],
                        'value' => trim($attributeData['value'])
                    ]);
                    
                    try {
                        $result = $product->productAttributes()->attach($attributeData['attribute_id'], [
                            'value' => trim($attributeData['value'])
                        ]);
                        \Log::info('Attribute attached successfully:', ['result' => $result]);
                    } catch (\Exception $e) {
                        \Log::error('Error attaching attribute:', [
                            'error' => $e->getMessage(),
                            'attribute_data' => $attributeData
                        ]);
                    }
                } else {
                    \Log::info('Skipping empty attribute:', $attributeData);
                }
            }
        } else {
            \Log::info('No attributes provided or not an array/object');
        }
        
        // Check final state
        $finalAttributes = $product->productAttributes()->get();
        \Log::info('Final product attributes count:', ['count' => $finalAttributes->count()]);

        // Clear product cache after updating product
        $this->clearProductCache($product->id);

        return redirect()->route('product.list')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $productId = $product->id;
        $product->delete();
        
        // Clear product cache after deleting product
        $this->clearProductCache($productId);
        
        return redirect()->route('product.list')->with('success', 'Product deleted successfully!');
    }

    public function searchCategory(Request $request)
    {
        $q = $request->q;
        $query = ProductServiceCategory::with('parent');
        if ($q) {
            $query->where(function($qry) use ($q) {
                $qry->where('name', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                    ->orWhere('status', 'like', "%$q%");
            });
        }
        $categories = $query->orderBy('name')->limit(20)->get(['id', 'name', 'parent_id']);
        
        // Format categories with parent information for subcategories
        $formatted = $categories->map(function($category) {
            $displayName = $category->name;
            if ($category->parent_id && $category->parent) {
                $displayName = $category->parent->name . ' > ' . $category->name;
            }
            return [
                'id' => $category->id,
                'name' => $category->name,
                'display_name' => $displayName,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent ? $category->parent->name : null
            ];
        });
        
        return response()->json($formatted);
    }

    /**
     * Remove a gallery image from a product.
     */

     public function addGalleryImage(Request $request)
     {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);
        $product = Product::findOrFail($request->product_id);
        $image = $request->file('image');
        $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
        $image->move(public_path('uploads/products/gallery'), $imageName);
        $product->galleries()->create([
            'image' => 'uploads/products/gallery/' . $imageName
        ]);
        return response()->json(['success' => true, 'message' => 'Gallery image added successfully!']);
     }
    public function deleteGalleryImage($id)
    {
        $gallery = \App\Models\ProductGallery::findOrFail($id);
        // Delete image file if exists
        if ($gallery->image && file_exists(public_path($gallery->image))) {
            @unlink(public_path($gallery->image));
        }
        $gallery->delete();
        return redirect()->back()->with('success', 'Gallery image removed successfully!');
    }

    public function productSearch(Request $request)
    {
        $q = $request->q;
        $query = Product::query();
        if ($q) {
            $query->where('name', 'like', "%$q%")
                  ->orWhere('sku', 'like', "%$q%")
                  ;
        }
        $products = $query->orderBy('name')->limit(20)->get(['id', 'name', 'sku', 'has_variations']);
        return response()->json($products);
    }

    public function getProductVariations($productId)
    {
        $product = Product::with(['variations.combinations.attribute', 'variations.combinations.attributeValue'])
            ->findOrFail($productId);
        
        if (!$product->has_variations) {
            return response()->json([]);
        }
        
        $variations = $product->variations()->where('status', 'active')->get()->map(function($variation) use ($product) {
            $attributes = $variation->combinations->map(function($combination) {
                return $combination->attributeValue->value ?? '';
            })->filter()->implode(' - ');

            // Price logic: If variation has price, use it; otherwise use product price
            // Then apply discount if exists (variation discount or product discount)
            $basePrice = ($variation->price && $variation->price > 0) ? (float) $variation->price : (float) $product->price;
            
            // Check for discount (variation discount first, then product discount)
            $discount = ($variation->discount && $variation->discount > 0) ? (float) $variation->discount : ((float) ($product->discount ?? 0));
            $hasDiscount = $discount > 0 && $discount < $basePrice;
            $displayPrice = $hasDiscount ? $discount : $basePrice;
            
            return [
                'id' => $variation->id,
                'name' => $variation->name ?: ($product->name . ($attributes ? ' - ' . $attributes : '')),
                'display_name' => $variation->name ?: ($product->name . ($attributes ? ' - ' . $attributes : '')),
                'sku' => $variation->sku,
                'price' => $displayPrice, // Final price with discount applied
                'base_price' => $basePrice, // Base price before discount
                'discount' => $discount,
                'has_discount' => $hasDiscount
            ];
        });
        
        return response()->json($variations);
    }

    /**
     * Find product by SKU/barcode for POS scanning
     */
    public function findProductByBarcode(Request $request, $branchId)
    {
        $barcode = $request->input('barcode') ?? $request->input('sku');
        
        if (!$barcode) {
            return response()->json(['success' => false, 'message' => 'Barcode/SKU is required'], 400);
        }

        // First, try to find by product SKU
        $product = Product::with(['category', 'branchStock' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }, 'variations.stocks' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId)->whereNull('warehouse_id');
        }])
        ->where('sku', $barcode)
        ->where(function($q) use ($branchId) {
            $q->whereHas('branchStock', function($subQ) use ($branchId) {
                $subQ->where('branch_id', $branchId);
            })
            ->orWhereHas('variations.stocks', function($subQ) use ($branchId) {
                $subQ->where('branch_id', $branchId)->whereNull('warehouse_id');
            });
        })
        ->first();

        // If product found, return it with stock info
        if ($product) {
            $productData = $this->transformProductForPOS($product, $branchId);
            return response()->json(['success' => true, 'product' => $productData, 'type' => 'product']);
        }

        // If not found, try to find by variation SKU
        $variation = \App\Models\ProductVariation::with(['product.category', 'product.branchStock' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }, 'stocks' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId)->whereNull('warehouse_id');
        }])
        ->where('sku', $barcode)
        ->where('status', 'active')
        ->whereHas('stocks', function($q) use ($branchId) {
            $q->where('branch_id', $branchId)->whereNull('warehouse_id');
        })
        ->first();

        if ($variation && $variation->product) {
            $product = $variation->product;
            $productData = $this->transformProductForPOS($product, $branchId);
            
            // Add variation info
            $variationStock = 0;
            foreach ($variation->stocks as $stock) {
                if ($stock->branch_id == $branchId && !$stock->warehouse_id) {
                    $variationStock += $stock->available_quantity ?? ($stock->quantity - ($stock->reserved_quantity ?? 0));
                }
            }
            
            $productData['selected_variation'] = [
                'id' => $variation->id,
                'sku' => $variation->sku,
                'name' => $variation->name,
                'price' => ($variation->price && $variation->price > 0) ? $variation->price : null,
                'stock' => $variationStock,
            ];
            
            return response()->json(['success' => true, 'product' => $productData, 'type' => 'variation', 'variation_id' => $variation->id]);
        }

        return response()->json(['success' => false, 'message' => 'Product not found with this barcode/SKU'], 404);
    }

    /**
     * Transform product data for POS display
     */
    private function transformProductForPOS($product, $branchId)
    {
        if ($product->has_variations) {
            // Load variations if not already loaded
            if (!$product->relationLoaded('variations')) {
                $product->load(['variations.stocks' => function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->whereNull('warehouse_id');
                }]);
            }
            
            // Sum all variation stocks for this branch
            $totalVariationStock = 0;
            $lastUpdatedAt = null;
            foreach ($product->variations as $variation) {
                if ($variation->relationLoaded('stocks')) {
                    foreach ($variation->stocks as $vStock) {
                        if ($vStock->branch_id == $branchId && !$vStock->warehouse_id) {
                            $totalVariationStock += $vStock->quantity;
                            if (!$lastUpdatedAt || ($vStock->last_updated_at && $vStock->last_updated_at > $lastUpdatedAt)) {
                                $lastUpdatedAt = $vStock->last_updated_at;
                            }
                        }
                    }
                }
            }
            
            $branch = \App\Models\Branch::find($branchId);
            $branchName = $branch ? $branch->name : 'Unknown Branch';
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'type' => $product->type,
                'price' => $product->price,
                'cost' => $product->cost,
                'discount' => $product->discount,
                'status' => $product->status,
                'image' => $product->image,
                'description' => $product->description,
                'has_variations' => $product->has_variations,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'branch_stock' => [
                    'branch_id' => $branchId,
                    'branch_name' => $branchName,
                    'quantity' => $totalVariationStock,
                    'last_updated_at' => $lastUpdatedAt
                ],
                'total_stock' => $totalVariationStock
            ];
        } else {
            $branchStock = $product->branchStock->first();
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'type' => $product->type,
                'price' => $product->price,
                'cost' => $product->cost,
                'discount' => $product->discount,
                'status' => $product->status,
                'image' => $product->image,
                'description' => $product->description,
                'has_variations' => $product->has_variations,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'branch_stock' => $branchStock ? [
                    'branch_id' => $branchStock->branch_id,
                    'branch_name' => $branchStock->branch->name ?? 'Unknown Branch',
                    'quantity' => $branchStock->quantity,
                    'last_updated_at' => $branchStock->last_updated_at
                ] : [
                    'branch_id' => $branchId,
                    'branch_name' => 'Unknown Branch',
                    'quantity' => 0,
                    'last_updated_at' => null
                ],
                'total_stock' => $product->branchStock->sum('quantity')
            ];
        }
    }

    public function getPrice($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        // Return price with discount logic (same as POS)
        // If has discount, return discount price, otherwise return base price
        $hasDiscount = $product->discount && $product->discount > 0 && $product->discount < $product->price;
        $displayPrice = $hasDiscount ? $product->discount : $product->price;
        
        return response()->json([
            'price' => (float) $displayPrice,
            'base_price' => (float) $product->price,
            'discount' => (float) ($product->discount ?? 0),
            'has_discount' => $hasDiscount
        ]);
    }

    public function getSalePrice($id)
    {
        $product = \App\Models\Product::findOrFail($id);
        // For sales/returns, use the selling price (consider discount if applicable)
        // Use discount price if available, otherwise use regular price
        $price = ($product->discount && $product->discount > 0 && $product->discount < $product->price) 
            ? $product->discount 
            : $product->price;
        return response()->json(['price' => $price]);
    }


    public function searchProductWithFilters(Request $request, $branchId)
    {
        // For products with variations, we need to check variation stocks
        // For products without variations, we check branch stocks
        $query = Product::with(['category', 'branchStock' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }, 'variations.stocks' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId)->whereNull('warehouse_id');
        }])
        ->where(function($q) use ($branchId) {
            // Products with branch stock (non-variation products)
            $q->whereHas('branchStock', function($subQ) use ($branchId) {
                $subQ->where('branch_id', $branchId);
            })
            // OR products with variations that have stock in this branch
            ->orWhereHas('variations.stocks', function($subQ) use ($branchId) {
                $subQ->where('branch_id', $branchId)->whereNull('warehouse_id');
            });
        });

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('id', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Always paginate for testing (per page = 1)
        $products = $query->paginate(12); // 1 per page for testing

        // Get branch name once for all products in this request
        $branch = \App\Models\Branch::find($branchId);
        $branchName = $branch ? $branch->name : 'Unknown Branch';

        $products->getCollection()->transform(function($product) use ($branchId, $branchName) {
            // For products with variations, calculate total stock from variation stocks
            if ($product->has_variations) {
                // Variations and stocks are already eager loaded via 'with' on the query
                // No need to call $product->load() here as it causes N+1 issues
                
                // Sum all variation stocks for this branch
                $totalVariationStock = 0;
                $lastUpdatedAt = null;
                foreach ($product->variations as $variation) {
                    foreach ($variation->stocks as $vStock) {
                        if ($vStock->branch_id == $branchId && !$vStock->warehouse_id) {
                            $totalVariationStock += $vStock->quantity;
                            if (!$lastUpdatedAt || ($vStock->last_updated_at && $vStock->last_updated_at > $lastUpdatedAt)) {
                                $lastUpdatedAt = $vStock->last_updated_at;
                            }
                        }
                    }
                }
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'type' => $product->type,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'discount' => $product->discount,
                    'status' => $product->status,
                    'image' => $product->image,
                    'description' => $product->description,
                    'has_variations' => $product->has_variations,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ] : null,
                    'branch_stock' => [
                        'branch_id' => $branchId,
                        'branch_name' => $branchName,
                        'quantity' => $totalVariationStock,
                        'last_updated_at' => $lastUpdatedAt
                    ],
                    'total_stock' => $totalVariationStock
                ];
            } else {
                // For products without variations, use branch stock
                $branchStock = $product->branchStock->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'type' => $product->type,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'discount' => $product->discount,
                    'status' => $product->status,
                    'image' => $product->image,
                    'description' => $product->description,
                    'has_variations' => $product->has_variations,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ] : null,
                    'branch_stock' => [
                        'branch_id' => $branchStock->branch_id ?? $branchId,
                        'branch_name' => $branchStock->branch->name ?? $branchName,
                        'quantity' => $branchStock->quantity ?? 0,
                        'last_updated_at' => $branchStock->last_updated_at ?? null
                    ],
                    'total_stock' => $branchStock->quantity ?? 0
                ];
            }
        });

        // Return paginated response with meta
        return response()->json([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Clear product-related cache
     */
    private function clearProductCache($productId = null)
    {
        try {
            \App\Services\CacheService::clearProductCaches($productId);
        } catch (\Exception $e) {
            \Log::warning('Failed to clear product cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear cache by pattern - Use CacheService instead of flushing all cache
     */
    private function clearCachePattern($pattern)
    {
        try {
            // Use CacheService which handles database cache properly
            // This prevents clearing ALL cache which causes performance issues
            \App\Services\CacheService::clearProductCaches();
            \Log::info("Cleared product caches using CacheService for pattern: {$pattern}");
        } catch (\Exception $e) {
            // If CacheService fails, try to clear specific known cache keys
            \Log::warning("Could not clear cache pattern {$pattern}: " . $e->getMessage());
            // Don't use Cache::flush() - it clears ALL cache and causes performance issues
            // Instead, clear only known product-related cache keys
            try {
                Cache::forget('max_product_price');
                Cache::forget('home_page_data');
                Cache::forget('products_list_*');
                Cache::forget('product_details_*');
                Cache::forget('new_arrivals_products_*');
                Cache::forget('best_deals_products_*');
            } catch (\Exception $clearException) {
                \Log::error("Failed to clear specific cache keys: " . $clearException->getMessage());
            }
        }
    }

}
