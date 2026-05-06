<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceReportService
{
    /**
     * Get performance data for products and variations.
     * Calculated as: (Sales - Returns) with associated COGS.
     */
    public function getPerformanceData($startDate, $endDate, $branchId = null, $productId = null, $categoryId = null)
    {
        // 1. Get Sales (POS + Online) grouped by product and variation
        $posSales = DB::table('pos_items')
            ->join('pos', 'pos_items.pos_sale_id', '=', 'pos.id')
            ->when($categoryId, function($q) use ($categoryId) {
                return $q->join('products', 'pos_items.product_id', '=', 'products.id')
                         ->where('products.category_id', $categoryId);
            })
            ->whereBetween('pos.sale_date', [$startDate, $endDate])
            ->when($branchId, fn($q) => $q->where('pos.branch_id', $branchId))
            ->when($productId, fn($q) => $q->where('pos_items.product_id', $productId))
            ->select('pos_items.product_id', 'pos_items.variation_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as amount'))
            ->groupBy('pos_items.product_id', 'pos_items.variation_id')
            ->get();

        $orderSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($categoryId, function($q) use ($categoryId) {
                return $q->join('products', 'order_items.product_id', '=', 'products.id')
                         ->where('products.category_id', $categoryId);
            })
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->when($productId, fn($q) => $q->where('order_items.product_id', $productId))
            ->select('order_items.product_id', 'order_items.variation_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as amount'))
            ->groupBy('order_items.product_id', 'order_items.variation_id')
            ->get();

        // 2. Get Returns
        $returns = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
            ->when($categoryId, function($q) use ($categoryId) {
                return $q->join('products', 'sale_return_items.product_id', '=', 'products.id')
                         ->where('products.category_id', $categoryId);
            })
            ->whereBetween('sale_returns.return_date', [$startDate, $endDate])
            ->when($productId, fn($q) => $q->where('sale_return_items.product_id', $productId))
            ->select('sale_return_items.product_id', 'sale_return_items.variation_id', DB::raw('SUM(returned_qty) as qty'), DB::raw('SUM(total_price) as amount'))
            ->groupBy('sale_return_items.product_id', 'sale_return_items.variation_id')
            ->get()
            ->keyBy(fn($item) => $item->product_id . '-' . $item->variation_id);

        $merged = [];

        // Merge Sales
        foreach ($posSales->concat($orderSales) as $sale) {
            $key = $sale->product_id . '-' . $sale->variation_id;
            if (!isset($merged[$key])) {
                $merged[$key] = $this->emptyPerformanceRow($sale->product_id, $sale->variation_id);
            }
            $merged[$key]['sold_qty'] += $sale->qty;
            $merged[$key]['sold_amount'] += $sale->amount;
        }

        // Merge Returns
        foreach ($returns as $key => $ret) {
            if (!isset($merged[$key])) {
                $merged[$key] = $this->emptyPerformanceRow($ret->product_id, $ret->variation_id);
            }
            $merged[$key]['returned_qty'] += $ret->qty;
            $merged[$key]['returned_amount'] += $ret->amount;
        }

        // 3. Enrich with Product/Variation details and calculate COGS/Profit
        return collect($merged)->map(function($item) {
            $product = Product::find($item['product_id']);
            $variation = $item['variation_id'] ? ProductVariation::with('attributeValues')->find($item['variation_id']) : null;
            
            $item['product_name'] = $product->name ?? 'Deleted Product';
            $item['style_number'] = $product->style_number ?? '-';
            $item['variation_name'] = $variation ? $variation->attributeValues->pluck('value')->implode(', ') : 'Standard';
            
            $item['net_qty'] = $item['sold_qty'] - $item['returned_qty'];
            $item['net_sale_amount'] = $item['sold_amount'] - $item['returned_amount'];
            
            // Unit cost from variation or product
            $item['unit_cost'] = (float)($variation->cost ?? $product->cost ?? 0);
            $item['net_purchase_cost'] = $item['net_qty'] * $item['unit_cost'];
            $item['gross_profit'] = $item['net_sale_amount'] - $item['net_purchase_cost'];
            $item['profit_margin'] = $item['net_sale_amount'] > 0 ? ($item['gross_profit'] / $item['net_sale_amount']) * 100 : 0;
            
            return (object)$item;
        })->filter(fn($item) => $item->sold_qty > 0 || $item->returned_qty > 0)->values();
    }

    private function emptyPerformanceRow($productId, $variationId)
    {
        return [
            'product_id' => $productId,
            'variation_id' => $variationId,
            'sold_qty' => 0,
            'sold_amount' => 0,
            'returned_qty' => 0,
            'returned_amount' => 0,
        ];
    }
}
