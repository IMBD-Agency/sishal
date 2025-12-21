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
     * Generate barcode for a single product
     */
    public function generateProductBarcode($productId)
    {
        $product = Product::findOrFail($productId);
        
        // Generate linear barcode SVG
        $barcode = $this->generateLinearBarcode($product->sku);
        
        return response()->json([
            'success' => true,
            'barcode' => $barcode,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'discount' => $product->discount,
            ]
        ]);
    }

    /**
     * Generate barcode for a product variation
     */
    public function generateVariationBarcode($productId, $variationId)
    {
        $product = Product::findOrFail($productId);
        $variation = ProductVariation::where('product_id', $productId)
            ->where('id', $variationId)
            ->firstOrFail();
        
        // Generate barcode using variation SKU or product SKU + variation ID
        $sku = $variation->sku ?? ($product->sku . '-' . $variation->id);
        
        $barcode = $this->generateLinearBarcode($sku);
        
        return response()->json([
            'success' => true,
            'barcode' => $barcode,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ],
            'variation' => [
                'id' => $variation->id,
                'name' => $variation->name,
                'display_name' => $variation->display_name,
                'sku' => $sku,
                'price' => $variation->price ?? $product->price,
            ]
        ]);
    }

    /**
     * Generate bulk barcodes
     */
    public function generateBulkBarcodes(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $barcodes = [];
        
        foreach ($request->product_ids as $productId) {
            $product = Product::find($productId);
            if ($product) {
                $barcode = $this->generateLinearBarcode($product->sku);
                
                $barcodes[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
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
        $product = Product::findOrFail($productId);
        $variation = null;
        $sku = $product->sku;
        $price = $product->price;
        $name = $product->name;

        if ($variationId) {
            $variation = ProductVariation::where('product_id', $productId)
                ->where('id', $variationId)
                ->firstOrFail();
            
            $sku = $variation->sku ?? ($product->sku . '-' . $variation->id);
            $price = $variation->price ?? $product->price;
            $name = $product->name . ' - ' . ($variation->display_name ?? $variation->name);
        }

        // Generate barcode with same quality as PDF for consistency
        $barcodeSvg = $this->generateLinearBarcode($sku, 3, 55);
        
        // Convert SVG to base64 data URI for better browser rendering
        $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);

        // Get quantity from request (default 1)
        $quantity = request('quantity', 1);

        return view('erp.barcodes.label', compact('product', 'variation', 'barcodeBase64', 'sku', 'price', 'name', 'quantity'));
    }

    /**
     * Download barcode labels as PDF
     */
    public function downloadBarcodePDF($productId, $variationId = null)
    {
        $product = Product::findOrFail($productId);
        $variation = null;
        $sku = $product->sku;
        $price = $product->price;
        $name = $product->name;

        if ($variationId) {
            $variation = ProductVariation::where('product_id', $productId)
                ->where('id', $variationId)
                ->firstOrFail();
            
            $sku = $variation->sku ?? ($product->sku . '-' . $variation->id);
            $price = $variation->price ?? $product->price;
            $name = $product->name . ' - ' . ($variation->display_name ?? $variation->name);
        }

        // Generate barcode with higher quality for PDF
        $barcodeSvg = $this->generateLinearBarcode($sku, 3, 55);
        
        // Convert SVG to base64 data URI for better PDF compatibility
        $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);

        // Get quantity from request (default 1)
        $quantity = request('quantity', 1);

        // Generate PDF with A4 size for better printing
        $pdf = Pdf::loadView('erp.barcodes.pdf-label', compact('barcodeBase64', 'sku', 'name', 'quantity'));
        $pdf->setPaper('a4', 'portrait');
        
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
