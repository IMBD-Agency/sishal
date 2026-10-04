<?php
require 'C:/Users/IMBD-WEB-TOHA/Desktop/composer1/vendor/autoload.php';
$app = require_once 'C:/Users/IMBD-WEB-TOHA/Desktop/composer1/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$startDate = Carbon::today()->startOfDay();
$endDate = Carbon::today()->endOfDay();

$sales = \App\Models\Pos::with(['items.product', 'payments', 'invoice'])
    ->where(function($q) use ($startDate, $endDate) {
        $q->whereBetween('sale_date', [$startDate, $endDate])
          ->orWhereHas('payments', function($pq) use ($startDate, $endDate) {
              $pq->whereBetween('payment_date', [$startDate, $endDate]);
          });
    })
    ->get();

foreach ($sales as $sale) {
    // 1. Product revenue and cost
    $grossItemsRev = $sale->items->whereNull('parent_item_id')->sum('total_price');
    $grossItemsCost = $sale->items->whereNull('parent_item_id')->sum(function($item) {
        $cost = $item->unit_cost > 0 ? $item->unit_cost : ($item->product->cost ?? 0);
        return $item->quantity * $cost;
    });

    $exchItemsRev = (float) DB::table('pos_exchange_items')
        ->join('pos_exchanges', 'pos_exchange_items.pos_exchange_id', '=', 'pos_exchanges.id')
        ->where('pos_exchanges.original_pos_id', $sale->id)
        ->where('pos_exchanges.status', 'completed')
        ->where('pos_exchange_items.type', 'new')
        ->sum('pos_exchange_items.total_price');

    $exchItemsCost = (float) DB::table('pos_exchange_items')
        ->join('pos_exchanges', 'pos_exchange_items.pos_exchange_id', '=', 'pos_exchanges.id')
        ->join('products', 'pos_exchange_items.product_id', '=', 'products.id')
        ->where('pos_exchanges.original_pos_id', $sale->id)
        ->where('pos_exchanges.status', 'completed')
        ->where('pos_exchange_items.type', 'new')
        ->sum(DB::raw('pos_exchange_items.quantity * COALESCE(products.cost, 0)'));

    $retItemsRev = (float) DB::table('sale_returns')
        ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
        ->where('sale_returns.pos_sale_id', $sale->id)
        ->where('sale_returns.status', '!=', 'rejected')
        ->sum('sale_return_items.total_price');

    $retItemsCost = (float) DB::table('sale_returns')
        ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
        ->join('products', 'sale_return_items.product_id', '=', 'products.id')
        ->where('sale_returns.pos_sale_id', $sale->id)
        ->where('sale_returns.status', '!=', 'rejected')
        ->sum(DB::raw('sale_return_items.returned_qty * COALESCE(products.cost, 0)'));

    $netProductRevenue = max(0, ($grossItemsRev + $exchItemsRev) - $retItemsRev);
    $netProductCost = max(0, ($grossItemsCost + $exchItemsCost) - $retItemsCost);

    $margin = $netProductRevenue > 0 ? ($netProductRevenue - $netProductCost) / $netProductRevenue : 0;

    // 2. Payments in period
    $payments = (float) DB::table('payments')
        ->where(function($q) use ($sale) {
            $q->where('pos_id', $sale->id);
            if ($sale->invoice_id) $q->orWhere('invoice_id', $sale->invoice_id);
        })
        ->whereBetween('payment_date', [$startDate, $endDate])
        ->sum('amount');

    // Return refunds in period
    $retRefunds = (float) DB::table('sale_returns')
        ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
        ->where('sale_returns.pos_sale_id', $sale->id)
        ->whereBetween('sale_returns.return_date', [$startDate, $endDate])
        ->whereIn('sale_returns.status', ['completed', 'approved', 'processed'])
        ->whereIn('sale_returns.refund_type', ['cash', 'bank', 'mobile'])
        ->selectRaw('COALESCE(SUM(sale_return_items.total_price * (1 + (CASE WHEN ' . ($sale->vat_rate ?? 0) . ' > 0 THEN (' . ($sale->vat_rate ?? 0) . '/100) ELSE 0 END))), 0) as refund')
        ->value('refund');

    // Exchange refunds in period
    $exchRefunds = (float) DB::table('pos_exchanges')
        ->where('original_pos_id', $sale->id)
        ->where('status', 'completed')
        ->whereBetween('exchange_date', [$startDate, $endDate])
        ->sum('refund_amount');

    $netCash = $payments - $retRefunds - $exchRefunds;

    // Non-product amounts
    $regularRetVat = (float) DB::table('sale_returns')
        ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
        ->where('sale_returns.pos_sale_id', $sale->id)
        ->where('sale_returns.refund_type', '!=', 'exchange')
        ->whereIn('sale_returns.status', ['completed', 'approved', 'processed'])
        ->selectRaw('COALESCE(SUM(sale_return_items.total_price * (CASE WHEN ' . ($sale->vat_rate ?? 0) . ' > 0 THEN (' . ($sale->vat_rate ?? 0) . '/100) ELSE 0 END)), 0) as ret_vat')
        ->value('ret_vat') ?? 0;

    $vat = max(0, (float)$sale->vat_amount - $regularRetVat);
    $delivery = (float)$sale->delivery;
    $nonProduct = $vat + $delivery;

    $productCollection = max(0, min($netProductRevenue, $netCash - $nonProduct));
    $cashProfit = $productCollection * $margin;
    $realizedCogs = $productCollection - $cashProfit;

    echo "Sale: {$sale->sale_number}\n";
    echo "  Net Product Revenue: $netProductRevenue\n";
    echo "  Net Product Cost: $netProductCost\n";
    echo "  Margin: " . round($margin * 100, 2) . "%\n";
    echo "  Net Cash: $netCash\n";
    echo "  Non-product (VAT $vat + Del $delivery): $nonProduct\n";
    echo "  Product Collection: $productCollection\n";
    echo "  Cash Profit: $cashProfit\n";
    echo "  Realized COGS: $realizedCogs\n";
}
