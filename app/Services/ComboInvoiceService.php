<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;

class ComboInvoiceService
{
    /**
     * Expand combo product into individual invoice items
     * 
     * @param Invoice $invoice
     * @param Product $product
     * @param int $quantity
     * @param array $itemData
     * @return void
     */
    public function addComboToInvoice(
        Invoice $invoice,
        Product $product,
        int $quantity = 1,
        array $itemData = []
    ): void {
        if (!$product->isCombo()) {
            // Not a combo, add as regular item
            $this->addRegularItem($invoice, $product, $quantity, $itemData);
            return;
        }

        // Get combo items
        $comboItems = $product->comboItems()->with(['product', 'variation'])->get();

        if ($comboItems->isEmpty()) {
            // No combo items defined, add combo as single item
            $this->addRegularItem($invoice, $product, $quantity, $itemData);
            return;
        }

        // Calculate total combo original price for ratio distribution
        $totalOriginalPrice = $product->combo_original_price;
        $comboPrice = $itemData['unit_price'] ?? $product->price;
        $discountRatio = $totalOriginalPrice > 0 ? $comboPrice / $totalOriginalPrice : 1;

        // Add individual combo items as invoice items
        foreach ($comboItems as $comboItem) {
            $originalUnitPrice = $comboItem->combo_price ?? $comboItem->product->price;
            $itemQuantity = $comboItem->quantity * $quantity;
            
            // Apply discount ratio to each item
            $discountedUnitPrice = $originalUnitPrice * $discountRatio;
            $totalPrice = $discountedUnitPrice * $itemQuantity;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $comboItem->product_id,
                'variation_id' => $comboItem->variation_id,
                'quantity' => $itemQuantity,
                'unit_price' => round($discountedUnitPrice, 2),
                'discount' => 0,
                'total_price' => round($totalPrice, 2),
            ]);
        }
    }

    /**
     * Add regular product as invoice item
     */
    private function addRegularItem(
        Invoice $invoice,
        Product $product,
        int $quantity,
        array $itemData
    ): void {
        $unitPrice = $itemData['unit_price'] ?? $product->price;
        $discount = $itemData['discount'] ?? 0;
        $totalPrice = ($unitPrice * $quantity) - $discount;

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'variation_id' => $itemData['variation_id'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'total_price' => $totalPrice,
        ]);
    }

    /**
     * Check if a product is a combo and return combo details for POS/Invoice UI
     */
    public function getComboDetails(Product $product): ?array
    {
        if (!$product->isCombo()) {
            return null;
        }

        $comboItems = $product->comboItems()->with(['product', 'variation'])->get();
        
        if ($comboItems->isEmpty()) {
            return null;
        }

        $items = [];
        $totalOriginalPrice = 0;

        foreach ($comboItems as $item) {
            $price = $item->combo_price ?? $item->product->price;
            $totalOriginalPrice += $price * $item->quantity;
            
            $items[] = [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'variation_id' => $item->variation_id,
                'variation_name' => $item->variation?->name,
                'quantity' => $item->quantity,
                'unit_price' => $price,
                'total_price' => $price * $item->quantity,
            ];
        }

        return [
            'is_combo' => true,
            'combo_price' => $product->price,
            'original_price' => $totalOriginalPrice,
            'savings' => $totalOriginalPrice - $product->price,
            'items' => $items,
        ];
    }
}
