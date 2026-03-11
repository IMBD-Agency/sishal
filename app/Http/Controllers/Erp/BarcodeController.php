<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use App\Services\BarcodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;

class BarcodeController extends Controller
{
    /**
     * Dedicated page for barcode generation
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('view products')) {
            abort(403, 'Unauthorized action.');
        }
        return view('erp.barcodes.index');
    }

    /**
     * Search product by style number for barcode generation
     */
    public function searchByStyle(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('view products')) {
            abort(403, 'Unauthorized action.');
        }
        $styleNo = $request->query('style_no');
        
        if (!$styleNo) {
            return response()->json(['success' => false, 'message' => 'Style number is required']);
        }

        $product = Product::where('sku', $styleNo)
            ->orWhere('style_number', $styleNo)
            ->with(['variations'])
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'style_number' => $product->style_number,
                'price' => $product->price,
                'has_variations' => $product->has_variations,
                'variations' => $product->variations->map(function($v) use ($product) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'display_name' => $v->display_name ?? $v->name,
                        'sku' => $v->sku ?? (($product->style_number ?? $product->sku) . '-' . $v->id),
                        'price' => $v->price ?? $product->price,
                    ];
                })
            ]
        ]);
    }

    /**
     * Generate barcode for a single product
     */
    public function generateProductBarcode($productId)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $product = Product::findOrFail($productId);
        
        // Generate linear barcode SVG (Prioritize style_number as requested)
        $identifier = $product->style_number ?? $product->sku;
        $barcode = $this->generateLinearBarcode($identifier);
        
        return response()->json([
            'success' => true,
            'barcode' => $barcode,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $identifier,
                'price' => $product->price,
                'discount' => $product->discount,
                'available_stock' => $product->manage_stock ? ($product->warehouseStock()->sum('quantity') - $product->warehouseStock()->sum('reserved_quantity')) : 0,
            ]
        ]);
    }

    /**
     * Generate barcode for a product variation
     */
    public function generateVariationBarcode($productId, $variationId)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $product = Product::findOrFail($productId);
        $variation = ProductVariation::where('product_id', $productId)
            ->where('id', $variationId)
            ->firstOrFail();
        
        // Generate barcode using variation SKU or (style_number/product SKU) + variation ID
        $baseIdentifier = $product->style_number ?? $product->sku;
        $sku = $variation->sku ?? ($baseIdentifier . '-' . $variation->id);
        
        $barcode = $this->generateLinearBarcode($sku);
        
        return response()->json([
            'success' => true,
            'barcode' => $barcode,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $baseIdentifier,
                'price' => $product->price,
            ],
            'variation' => [
                'id' => $variation->id,
                'name' => $variation->name,
                'display_name' => $variation->display_name,
                'sku' => $sku,
                'price' => $variation->price ?? $product->price,
                'available_stock' => $variation->available_stock,
            ]
        ]);
    }

    /**
     * Generate bulk barcodes
     */
    public function generateBulkBarcodes(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $barcodes = [];
        
        foreach ($request->product_ids as $productId) {
            $product = Product::find($productId);
            if ($product) {
                $identifier = $product->style_number ?? $product->sku;
                $barcode = $this->generateLinearBarcode($identifier);
                
                $barcodes[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $identifier,
                    'price' => $product->price,
                    'barcode' => $barcode,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'barcodes' => $barcodes
        ]);
    }

    /**
     * Return printable barcode label view
     */
    public function printBarcodeLabel($productId, $variationId = null)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $product = Product::findOrFail($productId);
        $variation = null;
        $sku = $product->style_number ?? $product->sku;
        $price = $product->price;
        $name = $product->name;
        $color = null;
        $size = null;

        if ($variationId) {
            $variation = ProductVariation::where('product_id', $productId)
                ->where('id', $variationId)
                ->firstOrFail();
            
            $baseIdentifier = $product->style_number ?? $product->sku;
            $sku = $variation->sku ?? ($baseIdentifier . '-' . $variation->id);
            $price = $variation->price ?? $product->price;
            $name = $product->name . ' - ' . ($variation->display_name ?? $variation->name);
            
            // Try to extract color and size for the professional label
            if ($variation->combinations) {
                foreach ($variation->combinations as $combo) {
                    $attr = $combo->attributeValue->attribute;
                    $val = $combo->attributeValue->value;
                    if (str_contains(strtolower($attr->name), 'color')) $color = $val;
                    if (str_contains(strtolower($attr->name), 'size')) $size = $val;
                }
            }
        }

        // Generate barcode with optimized dimensions for professional look
        // We'll use a slightly taller height and viewBox for scaling
        $barcodeSvg = $this->generateLinearBarcode($sku, 2.0, 60);
        
        // Convert SVG to base64 data URI for better browser rendering
        $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);

        // Get quantity from request (default 1)
        $quantity = request('quantity', 1);

        return view('erp.barcodes.label', compact('product', 'variation', 'barcodeBase64', 'sku', 'price', 'name', 'quantity', 'color', 'size'));
    }

    /**
     * Download barcode labels as PDF
     */
    public function downloadBarcodePDF($productId, $variationId = null)
    {
        if (!auth()->user()->hasPermissionTo('manage products')) {
            abort(403, 'Unauthorized action.');
        }
        $product = Product::findOrFail($productId);
        $variation = null;
        $sku = $product->style_number ?? $product->sku;
        $price = $product->price;
        $name = $product->name;
        $color = null;
        $size = null;

        if ($variationId) {
            $variation = ProductVariation::where('product_id', $productId)
                ->where('id', $variationId)
                ->firstOrFail();
            
            $baseIdentifier = $product->style_number ?? $product->sku;
            $sku = $variation->sku ?? ($baseIdentifier . '-' . $variation->id);
            $price = $variation->price ?? $product->price;
            $name = $product->name . ' - ' . ($variation->display_name ?? $variation->name);

            // Extract color and size for PDF label too
            if ($variation->combinations) {
                foreach ($variation->combinations as $combo) {
                    $attr = $combo->attributeValue->attribute;
                    $val = $combo->attributeValue->value;
                    if (str_contains(strtolower($attr->name), 'color')) $color = $val;
                    if (str_contains(strtolower($attr->name), 'size')) $size = $val;
                }
            }
        }

        // Generate barcode with optimized size for small PDF labels
        $barcodeSvg = $this->generateLinearBarcode($sku, 2.0, 60);
        
        // Convert SVG to base64 data URI for better PDF compatibility
        $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);

        // Get quantity from request (default 1)
        $quantity = request('quantity', 1);

        // Generate PDF with the exact sticker size (38mm x 25mm)
        $pdf = Pdf::loadView('erp.barcodes.pdf-label', compact('barcodeBase64', 'sku', 'name', 'quantity', 'price', 'color', 'size'));
        $pdf->setPaper([0, 0, 107.711, 70.866], 'portrait');
        
        return $pdf->download('barcode-' . $sku . '.pdf');
    }

    /**
     * Generate a Code 128 barcode SVG
     */
    private function generateLinearBarcode($text, $barWidth = 2.2, $height = 48)
    {
        // Use slightly wider bars for better scanning
        return BarcodeGenerator::generateCode128SVG($text, $barWidth, $height);
    }
}
