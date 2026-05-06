<!DOCTYPE html>
<html>
<head>
    <title>Detailed Inventory Movement Report</title>
    <style>
        @page { size: A4 landscape; margin: 20px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8px; color: #333; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 4px 2px; text-align: center; }
        th { background-color: #2d5a4c; color: white; text-transform: uppercase; font-size: 7px; }
        .header { text-align: center; margin-bottom: 10px; }
        .text-left { text-align: left; padding-left: 5px; }
        .text-right { text-align: right; padding-right: 5px; }
        .fw-bold { font-weight: bold; }
        .bg-light { background-color: #f9f9f9; }
        .footer { position: fixed; bottom: -10px; width: 100%; text-align: center; font-size: 7px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin-bottom: 5px; color: #2d5a4c;">Stock Movement Details</h2>
        <p style="margin-top: 0; color: #666; font-size: 9px;">
            Inventory Report | Generated: {{ date('d M, Y h:i A') }}
        </p>
    </div>

    @php
        $sl = 1;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 2%;">No.</th>
                <th style="width: 11%;">Product Name</th>
                <th style="width: 7%;">Style</th>
                <th style="width: 4%;">Color</th>
                <th style="width: 3%;">Size</th>
                <th style="width: 7%;">Outlet</th>
                <th style="width: 4%;">Open</th>
                <th style="width: 3%;">P-Qnt</th>
                <th style="width: 3%;">PR-Q</th>
                <th style="width: 3%; color: #00e5ff;">Net-P</th>
                <th style="width: 3%;">S-Qnt</th>
                <th style="width: 3%;">SR-Q</th>
                <th style="width: 3%; color: #00e5ff;">Net-S</th>
                <th style="width: 4%;">Adj</th>
                <th style="width: 4%;">E-To</th>
                <th style="width: 4%;">E-Fr</th>
                <th style="width: 4%;">T-Fr</th>
                <th style="width: 4%;">T-To</th>
                <th style="width: 5%;">STOCK</th>
                <th style="width: 8%;">Cost Val</th>
                <th style="width: 8%;">Rev</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                @php
                    $productLocations = [];
                    foreach($product->branchStock as $bs) { $productLocations['branch_' . $bs->branch_id] = ['type' => 'branch', 'id' => $bs->branch_id, 'name' => $bs->branch->name ?? 'Unknown']; }
                    foreach($product->warehouseStock as $ws) { $productLocations['warehouse_' . $ws->warehouse_id] = ['type' => 'warehouse', 'id' => $ws->warehouse_id, 'name' => $ws->warehouse->name ?? 'Unknown']; }
                    foreach($product->variationStocks as $vs) {
                        $lkey = $vs->branch_id ? 'branch_' . $vs->branch_id : 'warehouse_' . $vs->warehouse_id;
                        if(!isset($productLocations[$lkey])) {
                            $productLocations[$lkey] = ['type' => $vs->branch_id ? 'branch' : 'warehouse', 'id' => $vs->branch_id ?: $vs->warehouse_id, 'name' => ($vs->branch->name ?? $vs->warehouse->name) ?? 'Unknown'];
                        }
                    }
                @endphp

                @foreach ($productLocations as $location)
                    @php
                        $lid = $location['id']; $ltype = $location['type'];
                        $vars = $product->has_variations ? $product->variations : [null];
                    @endphp

                    @foreach ($vars as $var)
                        @php
                            $vid = $var ? $var->id : null;
                            $matchMove = function($m) use ($product, $vid) { 
                                if ($m->product_id != $product->id) return false;
                                if ($product->has_variations && $m->variation_id != $vid) return false;
                                return true; 
                            };

                            $p_qnt = $product->purchaseItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->purchase && $m->purchase->location_id == $lid && $m->purchase->ship_location_type == $ltype;
                            })->sum('quantity');

                            $pr_qnt = $product->purchaseReturnItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->return_from_id == $lid && $m->return_from_type == $ltype;
                            })->sum('returned_qty');

                            $s_qnt = 0;
                            $s_qnt += $product->saleItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->pos && $m->pos->branch_id == $lid && $ltype == 'branch' && $m->pos->sale_type != 'exchange';
                            })->sum('quantity');
                            $s_qnt += $product->invoiceItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $ltype == 'warehouse'; 
                            })->sum('quantity');
                            $s_qnt += $product->orderItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->order && $m->order->branch_id == $lid && $ltype == 'branch';
                            })->sum('quantity');

                            $sr_qnt = 0;
                            $sr_qnt += $product->saleReturnItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->saleReturn && $m->saleReturn->return_to_id == $lid && $m->saleReturn->return_to_type == $ltype && $m->saleReturn->refund_type != 'exchange';
                            })->sum('returned_qty');
                            $sr_qnt += $product->orderReturnItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->orderReturn && $m->orderReturn->return_to_id == $lid && $m->orderReturn->return_to_type == $ltype;
                            })->sum('returned_qty');

                            $adjust = $product->stockAdjustmentItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                if (!$matchMove($m) || !$m->adjustment) return false;
                                if ($ltype == 'branch' && $m->adjustment->branch_id == $lid) return true;
                                if ($ltype == 'warehouse' && $m->adjustment->warehouse_id == $lid) return true;
                                return false;
                            })->sum(function($m) { return $m->new_quantity - $m->old_quantity; });

                            $exc_to = $product->saleItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->pos && $m->pos->branch_id == $lid && $ltype == 'branch' && $m->pos->sale_type == 'exchange';
                            })->sum('quantity');

                            $exc_from = $product->saleReturnItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->saleReturn && $m->saleReturn->return_to_id == $lid && $m->saleReturn->return_to_type == $ltype && $m->saleReturn->refund_type == 'exchange';
                            })->sum('returned_qty');

                            $tr_from = $product->stockTransfers->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->from_id == $lid && $m->from_type == $ltype && $m->status == 'delivered';
                            })->sum('quantity');

                            $tr_to = $product->stockTransfers->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->to_id == $lid && $m->to_type == $ltype && $m->status == 'delivered';
                            })->sum('quantity');

                            $stock_qty = 0;
                            if ($product->has_variations) {
                                $stock_qty = $var->stocks->where($ltype == 'branch' ? 'branch_id' : 'warehouse_id', $lid)->sum('quantity');
                            } else {
                                $stock_qty = ($ltype == 'branch' ? $product->branchStock->where('branch_id', $lid)->sum('quantity') : $product->warehouseStock->where('warehouse_id', $lid)->sum('quantity'));
                            }

                            $inflows = $p_qnt + $sr_qnt + ($adjust > 0 ? $adjust : 0) + $tr_to + $exc_from;
                            $outflows = $s_qnt + $pr_qnt + ($adjust < 0 ? abs($adjust) : 0) + $tr_from + $exc_to;
                            $opening_stock = $stock_qty - ($inflows - $outflows);

                            $color = '-'; $size = '-';
                            if ($var) {
                                foreach($var->attributeValues as $av) {
                                    $attr = strtolower($av->attribute->name ?? '');
                                    if(str_contains($attr, 'color')) $color = $av->value;
                                    elseif(str_contains($attr, 'size')) $size = $av->value;
                                }
                            }
                            $cost = $var ? ($var->cost ?: $product->cost) : $product->cost;
                            $price = $var ? ($var->price ?: $product->price) : $product->price;
                        @endphp
                        @php
                            $actual_rev = 0;
                            // Add sales revenue (Standard sales + Exchanges)
                            $actual_rev += $product->saleItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->pos && $m->pos->branch_id == $lid && $ltype == 'branch';
                            })->sum('total_price');

                            // Add Warehouse sales (Invoices)
                            $actual_rev += $product->invoiceItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $ltype == 'warehouse'; 
                            })->sum('total_price');

                            // Add Order sales
                            $actual_rev += $product->orderItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->order && $m->order->branch_id == $lid && $ltype == 'branch';
                            })->sum('total_price');

                            // Subtract Returns revenue
                            $actual_rev -= $product->saleReturnItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->saleReturn && $m->saleReturn->return_to_id == $lid && $m->saleReturn->return_to_type == $ltype;
                            })->sum('total_price');

                            $actual_rev -= $product->orderReturnItems->filter(function($m) use ($matchMove, $lid, $ltype) {
                                return $matchMove($m) && $m->orderReturn && $m->orderReturn->return_to_id == $lid && $m->orderReturn->return_to_type == $ltype;
                            })->sum('total_price');
                        @endphp
                        <tr>
                            <td>{{ $sl++ }}</td>
                            <td class="text-left fw-bold">{{ $product->name }}</td>
                            <td>{{ $product->style_number ?: ($product->sku ?: '-') }}</td>
                            <td>{{ $color }}</td>
                            <td>{{ $size }}</td>
                            <td>{{ $location['name'] }}</td>
                            <td>{{ $opening_stock }}</td>
                            <td>{{ $p_qnt ?: '' }}</td>
                            <td>{{ $pr_qnt ?: '' }}</td>
                            <td style="background: rgba(0,229,255,0.05); font-weight: bold;">{{ ($p_qnt - $pr_qnt) ?: '' }}</td>
                            <td>{{ $s_qnt ?: '' }}</td>
                            <td>{{ $sr_qnt ?: '' }}</td>
                            <td style="background: rgba(0,229,255,0.05); font-weight: bold;">{{ ($s_qnt - $sr_qnt) ?: '' }}</td>
                            <td>{{ $adjust ?: '' }}</td>
                            <td>{{ $exc_to ?: '' }}</td>
                            <td>{{ $exc_from ?: '' }}</td>
                            <td>{{ $tr_from ?: '' }}</td>
                            <td>{{ $tr_to ?: '' }}</td>
                            <td class="fw-bold bg-light">{{ $stock_qty }}</td>
                            <td class="text-right">{{ number_format($stock_qty * $cost, 2) }}</td>
                            <td class="text-right">{{ number_format($actual_rev, 2) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Automated Inventory Movement Report - {{ date('Y-m-d') }}
    </div>
</body>
</html>
