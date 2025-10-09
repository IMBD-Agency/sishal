<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\VariationAttribute;
use App\Models\VariationAttributeValue;
use App\Models\ProductVariationCombination;
use App\Models\ProductVariationStock;
use App\Models\ProductVariationGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariationController extends Controller
{
    /**
     * Build a consistent variation name from selected attribute value IDs.
     */
    private function buildVariationName(array $attributeValueIds): string
    {
        $ids = collect($attributeValueIds)
            ->filter(fn($v) => !is_null($v) && $v !== '')
            ->map(fn($v) => (int) $v)
            ->values();

        if ($ids->isEmpty()) {
            return '';
        }

        $values = VariationAttributeValue::with('attribute')
            ->whereIn('id', $ids)
            ->get()
            // Order by attribute sort_order to keep stable naming like "Color - Size"
            ->sortBy(fn($val) => optional($val->attribute)->sort_order ?? 0)
            ->pluck('value')
            ->values()
            ->all();

        return implode(' - ', $values);
    }

    /**
     * Generate cartesian product of attribute value id sets.
     * Input: [ attributeId => [valueId, ...], ... ]
     * Output: array of combinations where each combination is [ attributeId => valueId, ... ]
     */
    private function generateCombinations(array $attributeIdToValueIds): array
    {
        // Normalize: filter empty and cast to ints
        $normalized = [];
        foreach ($attributeIdToValueIds as $attributeId => $valueIds) {
            $vals = collect((array) $valueIds)
                ->filter(fn($v) => !is_null($v) && $v !== '')
                ->map(fn($v) => (int) $v)
                ->values()
                ->all();
            if (count($vals) > 0) {
                $normalized[(int) $attributeId] = $vals;
            }
        }

        if (empty($normalized)) {
            return [];
        }

        // Build cartesian product
        $result = [[]];
        foreach ($normalized as $attrId => $vals) {
            $append = [];
            foreach ($result as $product) {
                foreach ($vals as $val) {
                    $new = $product;
                    $new[$attrId] = $val;
                    $append[] = $new;
                }
            }
            $result = $append;
        }

        return $result;
    }
    /**
     * Display a listing of variations for a product.
     */
    public function index($productId)
    {
        $product = Product::with(['variations.combinations.attribute', 'variations.combinations.attributeValue', 'variations.stocks'])
            ->findOrFail($productId);
        
        return view('erp.products.variations.index', compact('product'));
    }

    /**
     * Show the form for creating a new variation.
     */
    public function create($productId)
    {
        $product = Product::findOrFail($productId);
        $attributes = VariationAttribute::active()
            ->with(['values' => function($q){ $q->orderBy('sort_order'); }])
            ->get();
        
        return view('erp.products.variations.create', compact('product', 'attributes'));
    }

    /**
     * Store a newly created variation.
     */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        
        // Debug: Log the request data
        \Log::info('Variation Store Request Data:', $request->all());
        
        $request->validate([
            'sku' => 'required|string',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_default' => 'boolean',
            'status' => 'required|in:active,inactive',
            'attributes' => 'required|array',
            'attribute_values' => 'required|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Check for potential SKU conflicts before processing
        $attributesInput = $request->input('attributes', []);
        $valuesInput = $request->input('attribute_values', []);
        $isAssociative = array_keys($valuesInput) !== range(0, count($valuesInput) - 1);
        $hasArrays = false;
        if ($isAssociative) {
            foreach ($valuesInput as $v) {
                if (is_array($v) && count($v) > 1) { $hasArrays = true; break; }
            }
        }

        if ($isAssociative && $hasArrays) {
            // Check for SKU conflicts in bulk mode
            $combinations = $this->generateCombinations($valuesInput);
            $baseSku = $request->sku;
            $conflictingSkus = [];
            
            foreach ($combinations as $combo) {
                $skuSuffix = collect($combo)->map(function($valId){
                    $val = VariationAttributeValue::with('attribute')->find($valId);
                    return $val ? Str::upper(Str::slug($val->value, '')) : (string) $valId;
                })->implode('');
                
                $fullSku = $baseSku . '-' . $skuSuffix;
                
                // Check if SKU already exists
                if (ProductVariation::where('sku', $fullSku)->exists()) {
                    $conflictingSkus[] = $fullSku;
                }
            }
            
            if (!empty($conflictingSkus)) {
                return back()->withInput()->withErrors([
                    'sku' => 'The following SKUs already exist: ' . implode(', ', $conflictingSkus) . '. Please use a different base SKU.'
                ]);
            }
        } else {
            // Check for SKU conflict in single mode
            if (ProductVariation::where('sku', $request->sku)->exists()) {
                return back()->withInput()->withErrors([
                    'sku' => 'This SKU already exists. Please use a different SKU.'
                ]);
            }
        }

        DB::beginTransaction();
        
        try {
            // Detect bulk create: attribute_values is associative and contains arrays for any attribute
            $attributesInput = $request->input('attributes', []);
            $valuesInput = $request->input('attribute_values', []);
            $isAssociative = array_keys($valuesInput) !== range(0, count($valuesInput) - 1);
            $hasArrays = false;
            if ($isAssociative) {
                foreach ($valuesInput as $v) {
                    if (is_array($v) && count($v) > 1) { $hasArrays = true; break; }
                }
            }

            // Upload main image once (reused for bulk)
            $uploadedImagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products/variations'), $imageName);
                $uploadedImagePath = 'uploads/products/variations/' . $imageName;
            }

            if ($isAssociative && $hasArrays) {
                // BULK: generate all combinations
                $combinations = $this->generateCombinations($valuesInput);
                $createdVariations = [];
                
                foreach ($combinations as $combo) {
                    // Generate unique SKU with counter if needed
                    $baseSkuSuffix = collect($combo)->map(function($valId){
                        $val = VariationAttributeValue::with('attribute')->find($valId);
                        return $val ? Str::upper(Str::slug($val->value, '')) : (string) $valId;
                    })->implode('');
                    
                    $baseSku = $request->sku . '-' . $baseSkuSuffix;
                    $finalSku = $baseSku;
                    $counter = 1;
                    
                    // Ensure SKU uniqueness
                    while (ProductVariation::where('sku', $finalSku)->exists()) {
                        $finalSku = $baseSku . '-' . $counter;
                        $counter++;
                    }
                    
                    // Generate variation name
                    $generatedName = $this->buildVariationName(array_values($combo));
                    
                    // Create variation per combo
                    $variationData = [
                        'product_id' => $productId,
                        'sku' => $finalSku,
                        'name' => $generatedName ?: $request->name,
                        'price' => $request->price,
                        'cost' => $request->cost,
                        'discount' => $request->discount,
                        'is_default' => false, // default set can be updated later individually
                        'status' => $request->status,
                    ];
                    if ($uploadedImagePath) { $variationData['image'] = $uploadedImagePath; }

                    $variation = ProductVariation::create($variationData);
                    $createdVariations[] = $variation;
                    
                    foreach ($combo as $attributeId => $attributeValueId) {
                        ProductVariationCombination::create([
                            'variation_id' => $variation->id,
                            'attribute_id' => (int) $attributeId,
                            'attribute_value_id' => (int) $attributeValueId,
                        ]);
                    }

                    // Optional: duplicate gallery images to each variation
                    if ($request->hasFile('gallery')) {
                        foreach ($request->file('gallery') as $index => $galleryImage) {
                            $galleryName = time() . '_' . $variation->id . '_g' . $index . '_' . $galleryImage->getClientOriginalName();
                            $galleryImage->move(public_path('uploads/products/variations/gallery'), $galleryName);
                            ProductVariationGallery::create([
                                'variation_id' => $variation->id,
                                'image' => 'uploads/products/variations/gallery/' . $galleryName,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            } else {
                // SINGLE: behaves as before
                $variationData = [
                    'product_id' => $productId,
                    'sku' => $request->sku,
                    'name' => $request->name,
                    'price' => $request->price,
                    'cost' => $request->cost,
                    'discount' => $request->discount,
                    'is_default' => $request->boolean('is_default'),
                    'status' => $request->status,
                ];
                if ($uploadedImagePath) { $variationData['image'] = $uploadedImagePath; }

                $variation = ProductVariation::create($variationData);

                $isAssoc = $isAssociative;
                if ($isAssoc) {
                    foreach ($valuesInput as $attributeId => $attributeValueId) {
                        if (!$attributeId || !$attributeValueId) { continue; }
                        ProductVariationCombination::create([
                            'variation_id' => $variation->id,
                            'attribute_id' => (int) $attributeId,
                            'attribute_value_id' => (int) $attributeValueId,
                        ]);
                    }
                } else {
                    foreach ($attributesInput as $index => $attributeId) {
                        $attributeValueId = $valuesInput[$index] ?? null;
                        if (!$attributeId || !$attributeValueId) { continue; }
                        ProductVariationCombination::create([
                            'variation_id' => $variation->id,
                            'attribute_id' => (int) $attributeId,
                            'attribute_value_id' => (int) $attributeValueId,
                        ]);
                    }
                }

                $valueIds = $isAssoc ? array_values($valuesInput) : array_values($valuesInput);
                $generatedName = $this->buildVariationName($valueIds);
                if ($generatedName !== '') {
                    $variation->update(['name' => $generatedName]);
                }

                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $index => $galleryImage) {
                        $galleryName = time() . '_gallery_' . $index . '_' . $galleryImage->getClientOriginalName();
                        $galleryImage->move(public_path('uploads/products/variations/gallery'), $galleryName);
                        ProductVariationGallery::create([
                            'variation_id' => $variation->id,
                            'image' => 'uploads/products/variations/gallery/' . $galleryName,
                            'sort_order' => $index,
                        ]);
                    }
                }

                if ($variation->is_default) {
                    ProductVariation::where('product_id', $productId)
                        ->where('id', '!=', $variation->id)
                        ->update(['is_default' => false]);
                }
            }

            // Update product to have variations
            $product->update(['has_variations' => true]);

            DB::commit();
            
            $message = 'Product variation created successfully.';
            if (isset($createdVariations) && count($createdVariations) > 1) {
                $message = count($createdVariations) . ' product variations created successfully.';
            }
            
            return redirect()->route('erp.products.variations.index', $productId)
                ->with('success', $message);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Variation creation error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating variation: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified variation.
     */
    public function show($productId, $variationId)
    {
        $product = Product::findOrFail($productId);
        $variation = ProductVariation::with([
            'combinations.attribute', 
            'combinations.attributeValue', 
            'stocks.branch', 
            'stocks.warehouse',
            'galleries'
        ])->findOrFail($variationId);
        
        return view('erp.products.variations.show', compact('product', 'variation'));
    }

    /**
     * Show the form for editing the specified variation.
     */
    public function edit($productId, $variationId)
    {
        $product = Product::findOrFail($productId);
        $variation = ProductVariation::with(['combinations.attribute', 'combinations.attributeValue', 'galleries'])
            ->findOrFail($variationId);
        $attributes = VariationAttribute::active()
            ->with(['values' => function($q){ $q->orderBy('sort_order'); }])
            ->get();
        
        return view('erp.products.variations.edit', compact('product', 'variation', 'attributes'));
    }

    /**
     * Update the specified variation.
     */
    public function update(Request $request, $productId, $variationId)
    {
        $product = Product::findOrFail($productId);
        $variation = ProductVariation::findOrFail($variationId);
        
        $request->validate([
            'sku' => 'required|string|unique:product_variations,sku,' . $variationId,
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'is_default' => 'boolean',
            'status' => 'required|in:active,inactive',
            'attributes' => 'required|array',
            'attribute_values' => 'required|array',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        DB::beginTransaction();
        
        try {
            // Update the variation
            $variationData = [
                'sku' => $request->sku,
                // Temporarily set; will be overwritten below from attribute values
                'name' => $request->name,
                'price' => $request->price,
                'cost' => $request->cost,
                'discount' => $request->discount,
                'is_default' => $request->boolean('is_default'),
                'status' => $request->status,
            ];

            // Handle main image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($variation->image) {
                    @unlink(public_path($variation->image));
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products/variations'), $imageName);
                $variationData['image'] = 'uploads/products/variations/' . $imageName;
            }

            $variation->update($variationData);

            // Update attribute combinations (supports both indexed and keyed formats)
            $variation->combinations()->delete();
            $attributesInput = $request->input('attributes', []);
            $valuesInput = $request->input('attribute_values', []);
            $isAssoc = array_keys($valuesInput) !== range(0, count($valuesInput) - 1);
            if ($isAssoc) {
                foreach ($valuesInput as $attributeId => $attributeValueId) {
                    if (!$attributeId || !$attributeValueId) { continue; }
                    ProductVariationCombination::create([
                        'variation_id' => $variation->id,
                        'attribute_id' => (int) $attributeId,
                        'attribute_value_id' => (int) $attributeValueId,
                    ]);
                }
            } else {
                foreach ($attributesInput as $index => $attributeId) {
                    $attributeValueId = $valuesInput[$index] ?? null;
                    if (!$attributeId || !$attributeValueId) { continue; }
                    ProductVariationCombination::create([
                        'variation_id' => $variation->id,
                        'attribute_id' => (int) $attributeId,
                        'attribute_value_id' => (int) $attributeValueId,
                    ]);
                }
            }

            // Overwrite variation name with auto-generated name from the saved combinations
            $valueIds = $isAssoc ? array_values($valuesInput) : array_values($valuesInput);
            $generatedName = $this->buildVariationName($valueIds);
            if ($generatedName !== '') {
                $variation->update(['name' => $generatedName]);
            }

            // Handle gallery images
            if ($request->hasFile('gallery')) {
                // Delete old gallery images
                foreach ($variation->galleries as $gallery) {
                    @unlink(public_path($gallery->image));
                }
                $variation->galleries()->delete();
                
                foreach ($request->file('gallery') as $index => $galleryImage) {
                    $galleryName = time() . '_gallery_' . $index . '_' . $galleryImage->getClientOriginalName();
                    $galleryImage->move(public_path('uploads/products/variations/gallery'), $galleryName);
                    
                    ProductVariationGallery::create([
                        'variation_id' => $variation->id,
                        'image' => 'uploads/products/variations/gallery/' . $galleryName,
                        'sort_order' => $index,
                    ]);
                }
            }

            // If this is set as default, unset other defaults
            if ($variation->is_default) {
                ProductVariation::where('product_id', $productId)
                    ->where('id', '!=', $variation->id)
                    ->update(['is_default' => false]);
            }

            DB::commit();
            
            return redirect()->route('erp.products.variations.index', $productId)
                ->with('success', 'Product variation updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error updating variation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified variation.
     */
    public function destroy($productId, $variationId)
    {
        $variation = ProductVariation::findOrFail($variationId);
        
        DB::beginTransaction();
        
        try {
            // Delete images
            if ($variation->image) {
                @unlink(public_path($variation->image));
            }
            
            foreach ($variation->galleries as $gallery) {
                @unlink(public_path($gallery->image));
            }
            
            // Delete the variation (cascade will handle related records)
            $variation->delete();
            
            // Check if product still has variations
            $remainingVariations = ProductVariation::where('product_id', $productId)->count();
            if ($remainingVariations == 0) {
                Product::where('id', $productId)->update(['has_variations' => false]);
            }
            
            DB::commit();
            
            return redirect()->route('erp.products.variations.index', $productId)
                ->with('success', 'Product variation deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting variation: ' . $e->getMessage());
        }
    }

    /**
     * Get attribute values for AJAX requests.
     */
    public function getAttributeValues($attributeId)
    {
        $values = VariationAttributeValue::where('attribute_id', $attributeId)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();
            
        return response()->json($values);
    }

    /**
     * Toggle variation status.
     */
    public function toggleStatus($productId, $variationId)
    {
        $variation = ProductVariation::findOrFail($variationId);
        $variation->update([
            'status' => $variation->status === 'active' ? 'inactive' : 'active'
        ]);
        
        return response()->json([
            'success' => true,
            'status' => $variation->status
        ]);
    }
}
