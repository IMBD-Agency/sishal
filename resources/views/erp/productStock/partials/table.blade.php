@php
    $items = [];
    $sl = $productStocks->firstItem();

    foreach ($productStocks as $product) {
        $productLocations = [];
        
        // Collect all locations associated with this product
        foreach($product->branchStock as $bs) { $productLocations['branch_' . $bs->branch_id] = ['type' => 'branch', 'id' => $bs->branch_id, 'name' => $bs->branch->name ?? 'Unknown']; }
        foreach($product->warehouseStock as $ws) { $productLocations['warehouse_' . $ws->warehouse_id] = ['type' => 'warehouse', 'id' => $ws->warehouse_id, 'name' => $ws->warehouse->name ?? 'Unknown']; }
        foreach($product->variationStocks as $vs) {
            $key = $vs->branch_id ? 'branch_' . $vs->branch_id : 'warehouse_' . $vs->warehouse_id;
            if(!isset($productLocations[$key])) {
                $productLocations[$key] = [
                    'type' => $vs->branch_id ? 'branch' : 'warehouse',
                    'id' => $vs->branch_id ?: $vs->warehouse_id,
                    'name' => ($vs->branch->name ?? $vs->warehouse->name) ?? 'Unknown'
                ];
            }
        }

        // Apply filters for location
        if ($selectedBranchId || $selectedWarehouseId) {
            $productLocations = array_filter($productLocations, function($loc) use ($selectedBranchId, $selectedWarehouseId) {
                if ($selectedBranchId && $loc['type'] == 'branch' && $loc['id'] == $selectedBranchId) return true;
                if ($selectedWarehouseId && $loc['type'] == 'warehouse' && $loc['id'] == $selectedWarehouseId) return true;
                return false;
            });
        }

        foreach ($productLocations as $locKey => $location) {
            if ($product->has_variations) {
                foreach ($product->variations as $variation) {
                    // Filter variation if specific variation selected
                    if (request('variation_value_id')) {
                        $matches = false;
                        foreach($variation->attributeValues as $av) { if($av->id == request('variation_value_id')) $matches = true; }
                        if(!$matches) continue;
                    }

                    $items[] = [
                        'product' => $product,
                        'variation' => $variation,
                        'location' => $location,
                    ];
                }
            } else {
                $items[] = [
                    'product' => $product,
                    'variation' => null,
                    'location' => $location,
                ];
            }
        }
    }
@endphp

<!-- Table Registry -->
<div class="premium-card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive" style="max-width: 100%; overflow-x: auto; background: #fff; border-radius: 12px; border: 1px solid #eef0f2;">
            <table class="table table-hover align-middle mb-0" style="min-width: 1800px; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background: #111827 !important;">
                        <th class="ps-4 py-4 text-center" style="width: 60px; border-top-left-radius: 12px; background: #111827 !important; color: #ffffff !important; border: none;">No.</th>
                        <th class="py-4" style="min-width: 250px; background: #111827 !important; color: #ffffff !important; border: none;">Product Details</th>
                        <th class="py-4 text-center" style="width: 140px; background: #111827 !important; color: #ffffff !important; border: none;">Style #</th>
                        <th class="py-4 text-center" style="width: 100px; background: #111827 !important; color: #ffffff !important; border: none;">Color</th>
                        <th class="py-4 text-center" style="width: 80px; background: #111827 !important; color: #ffffff !important; border: none;">Size</th>
                        <th class="py-4 text-center" style="min-width: 150px; background: #111827 !important; color: #ffffff !important; border: none;"> Outlet</th>
                        <th class="py-4 text-center fw-bold" style="width: 100px; background: #111827 !important; color: #ffffff !important; border: none;" title="Stock before selected period">Opening</th>
                        <th class="py-4 text-center fw-bold" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Purchase Quantity">P-Qnt</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Purchase Return">PR-Qnt</th>
                        <th class="py-4 text-center text-info" style="width: 90px; background: #111827 !important; color: #00e5ff !important; border: none;" title="Actual Purchase (P-Qnt - PR-Qnt)">Net-P</th>
                        <th class="py-4 text-center fw-bold" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Sale Quantity">S-Qnt</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Sale Return">SR-Qnt</th>
                        <th class="py-4 text-center text-info" style="width: 90px; background: #111827 !important; color: #00e5ff !important; border: none;" title="Actual Sale (S-Qnt - SR-Qnt)">Net-S</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Adjustment">Adjust</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Exchange To">Exc-To</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Exchange From">Exc-Fr</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Transfer From">Tr-Fr</th>
                        <th class="py-4 text-center" style="width: 90px; background: #111827 !important; color: #ffffff !important; border: none;" title="Transfer To">Tr-To</th>
                        <th class="py-4 text-center fw-bold" style="width: 110px; background: #111827 !important; color: #ffffff !important; border: none;" title="Total Current Stock">STOCK</th>
                        <th class="py-4 text-end" style="width: 140px; background: #111827 !important; color: #ffffff !important; border: none;">Cost Value</th>
                        <th class="py-4 text-end pe-4" style="width: 140px; border-top-right-radius: 12px; background: #111827 !important; color: #ffffff !important; border: none;">Sale Value</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_opening = 0;
                        $total_p = 0;
                        $total_pr = 0;
                        $total_net_p = 0;
                        $total_s = 0;
                        $total_sr = 0;
                        $total_net_s = 0;
                        $total_adj = 0;
                        $total_exc_to = 0;
                        $total_exc_fr = 0;
                        $total_tr_fr = 0;
                        $total_tr_to = 0;
                        $total_stock = 0;
                        $total_cost_val = 0;
                        $total_sale_val = 0;
                        $total_rev = 0;
                    @endphp
                    @forelse ($items as $item)
                        @php
                            $prod = $item['product'];
                            $var = $item['variation'];
                            $loc = $item['location'];
                            
                            $variationId = $var ? $var->id : null;
                            $locationId = $loc['id'];
                            $locationType = $loc['type'];

                            // Helper for matching movements to this specific row (Prod + Var + Loc)
                            $matchMove = function($movement) use ($prod, $variationId) {
                                if ($movement->product_id != $prod->id) return false;
                                if ($prod->has_variations && $movement->variation_id != $variationId) return false;
                                return true; 
                            };

                            // Movements logic
                            $p_qnt = $prod->purchaseItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->purchase && $m->purchase->location_id == $locationId && $m->purchase->ship_location_type == $locationType;
                            })->sum('quantity');

                            $pr_qnt = $prod->purchaseReturnItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->return_from_id == $locationId && $m->return_from_type == $locationType;
                            })->sum('returned_qty');

                            $s_qnt = 0;
                            $s_qnt += $prod->saleItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->pos && $m->pos->branch_id == $locationId && $locationType == 'branch' && $m->pos->sale_type != 'exchange';
                            })->sum('quantity');
                            $s_qnt += $prod->invoiceItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $locationType == 'warehouse'; 
                            })->sum('quantity');
                            $s_qnt += $prod->orderItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->order && $m->order->branch_id == $locationId && $locationType == 'branch';
                            })->sum('quantity');

                            $sr_qnt = 0;
                            $sr_qnt += $prod->saleReturnItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->saleReturn && $m->saleReturn->return_to_id == $locationId && $m->saleReturn->return_to_type == $locationType && $m->saleReturn->refund_type != 'exchange';
                            })->sum('returned_qty');
                            $sr_qnt += $prod->orderReturnItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->orderReturn && $m->orderReturn->return_to_id == $locationId && $m->orderReturn->return_to_type == $locationType;
                            })->sum('returned_qty');

                            $adjust = $prod->stockAdjustmentItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                if (!$matchMove($m) || !$m->adjustment) return false;
                                if ($locationType == 'branch' && $m->adjustment->branch_id == $locationId) return true;
                                if ($locationType == 'warehouse' && $m->adjustment->warehouse_id == $locationId) return true;
                                return false;
                            })->sum(function($m) { return $m->new_quantity - $m->old_quantity; });

                            $exc_to = $prod->saleItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->pos && $m->pos->branch_id == $locationId && $locationType == 'branch' && $m->pos->sale_type == 'exchange';
                            })->sum('quantity');

                            $exc_from = $prod->saleReturnItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->saleReturn && $m->saleReturn->return_to_id == $locationId && $m->saleReturn->return_to_type == $locationType && $m->saleReturn->refund_type == 'exchange';
                            })->sum('returned_qty');

                            $tr_from = $prod->stockTransfers->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->from_id == $locationId && $m->from_type == $locationType && $m->status == 'delivered';
                            })->sum('quantity');

                            $tr_to = $prod->stockTransfers->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->to_id == $locationId && $m->to_type == $locationType && $m->status == 'delivered';
                            })->sum('quantity');

                            $stock_qty = 0;
                            if ($prod->has_variations) {
                                $stock_qty = $var->stocks->where($locationType == 'branch' ? 'branch_id' : 'warehouse_id', $locationId)->sum('quantity');
                            } else {
                                $stock_qty = ($locationType == 'branch' ? $prod->branchStock->where('branch_id', $locationId)->sum('quantity') : $prod->warehouseStock->where('warehouse_id', $locationId)->sum('quantity'));
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

                            $cost = $var ? ($var->cost ?: $prod->cost) : $prod->cost;
                            $price = $var ? ($var->price ?: $prod->price) : $prod->price;
                            
                            $row_cost_val = $stock_qty * $cost;
                            $row_sale_val = $stock_qty * $price;
                            $row_rev = 0;
                            // Add sales revenue (Standard sales + Exchanges)
                            $row_rev += $prod->saleItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->pos && $m->pos->branch_id == $locationId && $locationType == 'branch';
                            })->sum('total_price');

                            // Add Warehouse sales (Invoices)
                            $row_rev += $prod->invoiceItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $locationType == 'warehouse'; 
                            })->sum('total_price');

                            // Add Order sales
                            $row_rev += $prod->orderItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->order && $m->order->branch_id == $locationId && $locationType == 'branch';
                            })->sum('total_price');

                            // Subtract Returns revenue
                            $row_rev -= $prod->saleReturnItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->saleReturn && $m->saleReturn->return_to_id == $locationId && $m->saleReturn->return_to_type == $locationType;
                            })->sum('total_price');

                            $row_rev -= $prod->orderReturnItems->filter(function($m) use ($matchMove, $locationId, $locationType) {
                                return $matchMove($m) && $m->orderReturn && $m->orderReturn->return_to_id == $locationId && $m->orderReturn->return_to_type == $locationType;
                            })->sum('total_price');

                            // Accumulate Totals
                            $total_opening += $opening_stock;
                            $total_p += $p_qnt;
                            $total_pr += $pr_qnt;
                            $total_net_p += ($p_qnt - $pr_qnt);
                            $total_s += $s_qnt;
                            $total_sr += $sr_qnt;
                            $total_net_s += ($s_qnt - $sr_qnt);
                            $total_adj += $adjust;
                            $total_exc_to += $exc_to;
                            $total_exc_fr += $exc_from;
                            $total_tr_fr += $tr_from;
                            $total_tr_to += $tr_to;
                            $total_stock += $stock_qty;
                            $total_cost_val += $row_cost_val;
                            $total_sale_val += $row_sale_val;
                            $total_rev += $row_rev;
                        @endphp
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td class="text-center text-muted small">{{ $sl++ }}</td>
                            <td class="fw-bold text-dark py-3">
                                {{ $prod->name }}
                                @if($var) <br><small class="text-muted fw-normal">#{{ $var->sku }}</small> @endif
                            </td>
                            <td class="text-center"><code>{{ $prod->style_number ?: '-' }}</code></td>
                            <td class="text-center"><span class="badge bg-light text-dark border fw-normal">{{ $color }}</span></td>
                            <td class="text-center"><span class="badge bg-light text-dark border fw-normal">{{ $size }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-dark bg-opacity-10 text-dark border border-opacity-25 px-2">
                                    {{ $loc['name'] }}
                                </span>
                            </td>
                            <td class="text-center bg-light text-dark fw-bold" style="font-size: 14px;">{{ $opening_stock }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $p_qnt ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $pr_qnt ?: '' }}</td>
                            <td class="text-center fw-bold text-info" style="font-size: 14px; background: rgba(0,229,255,0.02)">{{ ($p_qnt - $pr_qnt) ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $s_qnt ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $sr_qnt ?: '' }}</td>
                            <td class="text-center fw-bold text-info" style="font-size: 14px; background: rgba(0,229,255,0.02)">{{ ($s_qnt - $sr_qnt) ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $adjust ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $exc_to ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $exc_from ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $tr_from ?: '' }}</td>
                            <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $tr_to ?: '' }}</td>
                            <td class="text-center fw-bold fs-6 text-dark">
                                {{ $stock_qty }}
                            </td>
                            <td class="text-end fw-bold text-dark">{{ number_format($row_cost_val, 2) }}</td>
                            <td class="text-end pe-4 fw-bold text-dark">{{ number_format($row_sale_val, 2) }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="21" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No matching stock data found for the selected criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($items) > 0)
                <tfoot style="background: #f8f9fa; border-top: 2px solid #111827;">
                    <tr>
                        <td colspan="6" class="text-end fw-bold py-3">GRAND TOTAL (PAGE)</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_opening }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_p }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_pr }}</td>
                        <td class="text-center fw-bold text-info" style="font-size: 14px; background: rgba(0,229,255,0.05)">{{ $total_net_p }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_s }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_sr }}</td>
                        <td class="text-center fw-bold text-info" style="font-size: 14px; background: rgba(0,229,255,0.05)">{{ $total_net_s }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_adj }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_exc_to }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_exc_fr }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_tr_fr }}</td>
                        <td class="text-center fw-bold text-dark" style="font-size: 14px;">{{ $total_tr_to }}</td>
                        <td class="text-center fw-bold text-dark fs-6 bg-primary bg-opacity-10">{{ $total_stock }}</td>
                        <td class="text-end fw-bold text-dark">{{ number_format($total_cost_val, 2) }}</td>
                        <td class="text-end pe-4 fw-bold text-dark">{{ number_format($total_sale_val, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="card-footer bg-white border-top-0 py-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="text-muted small mb-0">Displaying {{ $productStocks->firstItem() }} to {{ $productStocks->lastItem() }} of {{ $productStocks->total() }} items</p>
            {{ $productStocks->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>

<style>
    .premium-table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
    }
    .premium-table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .premium-table td {
        padding: 0.75rem 0.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .reporting-table code {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
    }
</style>
