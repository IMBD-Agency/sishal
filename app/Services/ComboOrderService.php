<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class ComboOrderService
{
    /**
     * Expand combo product into individual order items
     * 
     * @param Order $order
     * @param Product $product
     * @param int $quantity
     * @param string|null $positionType
     * @param int|null $positionId
     * @return void
     */
    public function addComboToOrder(
        Order $order,
        Product $product,
        int $quantity = 1,
        ?string $positionType = null,
        ?int $positionId = null
    ): void {
        if (!$product->isCombo()) {
            // Not a combo, add as regular item
            $this->addRegularItem($order, $product, $quantity, $positionType, $positionId);
            return;
        }

        // Get combo items
        $comboItems = $product->comboItems()->with(['product', 'variation'])->get();

        if ($comboItems->isEmpty()) {
            // No combo items defined, add combo as single item
            $this->addRegularItem($order, $product, $quantity, $positionType, $positionId);
            return;
        }

        // Add individual combo items as order items
        foreach ($comboItems as $comboItem) {
            $unitPrice = $comboItem->combo_price ?? $comboItem->product->price;
            $itemQuantity = $comboItem->quantity * $quantity;
            $totalPrice = $unitPrice * $itemQuantity;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $comboItem->product_id,
                'variation_id' => $comboItem->variation_id,
                'quantity' => $itemQuantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'current_position_type' => $positionType,
                'current_position_id' => $positionId,
            ]);

            // Deduct stock for each item
            $this->deductStock($comboItem->product_id, $comboItem->variation_id, $itemQuantity, $positionType, $positionId);
        }
    }

    /**
     * Add regular product as order item
     */
    private function addRegularItem(
        Order $order,
        Product $product,
        int $quantity,
        ?string $positionType,
        ?int $positionId
    ): void {
        $unitPrice = $product->price;
        $totalPrice = $unitPrice * $quantity;

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variation_id' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'current_position_type' => $positionType,
            'current_position_id' => $positionId,
        ]);

        $this->deductStock($product->id, null, $quantity, $positionType, $positionId);
    }

    /**
     * Deduct stock from warehouse/branch
     */
    private function deductStock(
        int $productId,
        ?int $variationId,
        int $quantity,
        ?string $positionType,
        ?int $positionId
    ): void {
        // Implement your stock deduction logic here
        // based on your existing stock management system
    }
}
