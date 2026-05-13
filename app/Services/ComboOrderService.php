<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class ComboOrderService
{
    /**
     * Add combo product to order with parent-child structure
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

        // Create parent row for combo
        $unitPrice = $product->price;
        $totalPrice = $unitPrice * $quantity;

        $parentItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variation_id' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'current_position_type' => $positionType,
            'current_position_id' => $positionId,
        ]);

        // Add child items for each product in combo with 0 price
        foreach ($comboItems as $comboItem) {
            $itemQuantity = $comboItem->quantity * $quantity;

            OrderItem::create([
                'parent_item_id' => $parentItem->id,
                'order_id' => $order->id,
                'product_id' => $comboItem->product_id,
                'variation_id' => $comboItem->variation_id,
                'quantity' => $itemQuantity,
                'unit_price' => 0,
                'total_price' => 0,
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
