<div class="table-responsive">
    <table class="table premium-table compact reporting-table mb-0" id="procurementTable">
        <thead>
            <tr>
                <th class="ps-3">SL</th>
                <th>Inv #</th>
                <th>Date</th>
                <th class="text-center">Image</th>
                <th>Supplier</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Season</th>
                <th>Gender</th>
                <th style="min-width: 150px;">Product Name</th>
                <th>Style #</th>
                <th width="120">Color</th>
                <th width="100">Size</th>
                <th class="text-center">Pur. Qty</th>
                <th class="text-center bg-light">Inv. T. Qty</th>
                <th class="text-end">Pur. Value</th>
                <th class="text-end bg-light">Inv. T. Value</th>
                <th class="text-center text-danger">Ret. Qty</th>
                <th class="text-center text-danger bg-light">Inv. T. Ret. Qty</th>
                <th class="text-end text-danger">Ret. Value</th>
                <th class="text-end text-danger bg-light">Inv. T. Ret. Value</th>
                <th class="text-center text-success">Act. Qty</th>
                <th class="text-center text-success bg-light">Inv. T. Act. Qty</th>
                <th class="text-end text-success">Act. Value</th>
                <th class="text-end text-success bg-light">Inv. T. Act. Value</th>
                <th class="text-center fw-bold text-info">Live Stock</th>
                <th class="text-end">Bill Disc.</th>
                <th class="text-end">Paid A/C</th>
                <th class="text-end">Due A/C</th>
                <th class="text-center">Status</th>
                <th class="text-center pe-3">ACTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $purchase = $item->purchase;
                    $bill = $purchase->bill;
                    $product = $item->product;
                    $variation = $item->variation;
                    
                    $color = '-'; $size = '-';
                    if ($variation && $variation->attributeValues) {
                        foreach($variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) $color = $val->value;
                            elseif (str_contains($attrName, 'size')) $size = $val->value;
                        }
                    }

                    $retQty = $item->returnItems->sum('returned_qty');
                    $retAmt = $item->returnItems->sum(function($ri) { return $ri->returned_qty * $ri->unit_price; });
                    $actualQty = $item->quantity - $retQty;
                    $actualAmt = $item->total_price - $retAmt;

                    $showInvoiceTotals = ($index == 0 || $items[$index-1]->purchase_id != $item->purchase_id);
                    // For performance, we pre-calculate these or use the models
                    $invPurQty = $purchase->items->sum('quantity');
                    $invPurAmt = $purchase->items->sum('total_price');
                    $invRetQty = $purchase->items->sum(fn($i) => $i->returnItems->sum('returned_qty'));
                    $invRetAmt = $purchase->items->sum(fn($i) => $i->returnItems->sum(fn($ri) => $ri->returned_qty * $ri->unit_price));
                    
                    $invActQty = $invPurQty - $invRetQty;
                    $invActAmt = $invPurAmt - $invRetAmt;
                @endphp
                <tr>
                    <td class="ps-3 text-muted">{{ $items->firstItem() + $index }}</td>
                    <td class="fw-bold">
                        <a href="{{ route('purchase.show', $purchase->id) }}" class="text-decoration-none text-primary">
                            @if($bill && $bill->bill_number) {{ $bill->bill_number }} 
                            @else #PUR-{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }} @endif
                        </a>
                    </td>
                    <td>{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        <div class="thumbnail-box mx-auto" style="width: 35px; height: 35px;">
                            <img src="{{ $product && $product->image ? asset($product->image) : asset('static/default-product.jpg') }}" alt="">
                        </div>
                    </td>
                    <td class="fw-bold">{{ $purchase->supplier->name ?? '-' }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ $product->season->name ?? '-' }}</td>
                    <td>{{ $product->gender->name ?? '-' }}</td>
                    <td class="fw-bold text-dark">{{ $product->name ?? '-' }}</td>
                    <td><code class="text-primary bg-light px-2 py-1 rounded">{{ $product->sku ?? $product->style_number ?? '-' }}</code></td>
                    <td class="text-uppercase fw-bold">{{ $color }}</td>
                    <td class="fw-bold">{{ $size }}</td>
                    
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-center fw-bold bg-light">@if($showInvoiceTotals) {{ number_format($invPurQty, 2) }} @else - @endif</td>
                    <td class="text-end">{{ number_format($item->total_price, 2) }}৳</td>
                    <td class="text-end fw-bold bg-light">@if($showInvoiceTotals) {{ number_format($invPurAmt, 2) }}৳ @else - @endif</td>
                    
                    <td class="text-center text-danger">{{ number_format($retQty, 2) ?: '-' }}</td>
                    <td class="text-center text-danger fw-bold bg-light">@if($showInvoiceTotals) {{ number_format($invRetQty, 2) }} @else - @endif</td>
                    <td class="text-end text-danger">{{ $retQty ? number_format($retAmt, 2).'৳' : '-' }}</td>
                    <td class="text-end text-danger fw-bold bg-light">@if($showInvoiceTotals) {{ number_format($invRetAmt, 2) }}৳ @else - @endif</td>
                    
                    <td class="text-center text-success">{{ number_format($actualQty, 2) }}</td>
                    <td class="text-center text-success fw-bold bg-light">@if($showInvoiceTotals) {{ number_format($invActQty, 2) }} @else - @endif</td>
                    <td class="text-end text-success">{{ number_format($actualAmt, 2) }}৳</td>
                    <td class="text-end text-success fw-bold bg-light">@if($showInvoiceTotals) {{ number_format($invActAmt, 2) }}৳ @else - @endif</td>
                    
                    <td class="text-center fw-bold text-info">
                        {{ $item->current_stock ?? 0 }}
                    </td>
                    
                    <td class="text-end text-warning fw-bold">
                        @if($showInvoiceTotals) {{ number_format($bill->discount_amount ?? 0, 2) }}৳ @else - @endif
                    </td>
                    <td class="text-end text-primary fw-bold">
                        @if($showInvoiceTotals) {{ number_format($bill->paid_amount ?? 0, 2) }}৳ @else - @endif
                    </td>
                    <td class="text-end text-danger fw-bold">
                        @if($showInvoiceTotals) {{ number_format($bill->due_amount ?? 0, 2) }}৳ @else - @endif
                    </td>
                    <td class="text-center">
                        @php
                            $statusDot = [
                                'pending' => 'bg-warning',
                                'received' => 'bg-success',
                                'cancelled' => 'bg-danger',
                            ][$purchase->status] ?? 'bg-secondary';
                        @endphp
                        <span class="status-pill {{ str_replace('bg-', 'status-', $statusDot) }}">
                            <i class="fas fa-circle extra-small"></i>{{ ucfirst($purchase->status) }}
                        </span>
                    </td>
                    <td class="pe-3">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('purchase.show', $purchase->id) }}" class="action-circle bg-light border-0" title="View Audit Detail">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="31" class="text-center py-5 text-muted">No procurement records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot class="bg-light fw-bold">
            <tr class="text-muted small border-top">
                <td colspan="13" class="text-end">Page Subtotal</td>
                <td class="text-center">{{ number_format($items->sum('quantity'), 2) }}</td>
                <td class="bg-white"></td>
                <td class="text-end">{{ number_format($items->sum('total_price'), 2) }}৳</td>
                <td colspan="15"></td>
            </tr>
            <tr class="bg-soft-primary border-top-2">
                <td colspan="13" class="text-end text-uppercase py-3">Grand Total (All Records)</td>
                <td class="text-center py-3">{{ number_format($reportTotals['pur_qty'], 2) }}</td>
                <td class="bg-light"></td>
                <td class="text-end py-3">{{ number_format($reportTotals['pur_amt'], 2) }}৳</td>
                <td class="bg-light"></td>
                <td colspan="4" class="bg-light"></td>
                <td colspan="4" class="bg-light"></td>
                <td class="bg-light"></td>
                <td class="text-end py-3">{{ number_format($reportTotals['discount'], 2) }}৳</td>
                <td class="text-end text-primary py-3">{{ number_format($reportTotals['paid'], 2) }}৳</td>
                <td class="text-end text-danger py-3">{{ number_format($reportTotals['due'], 2) }}৳</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

@if($items->hasPages())
<div class="card-footer bg-white py-3 border-top">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted fw-bold text-uppercase">Registry Batch: {{ $items->firstItem() }} - {{ $items->lastItem() }} of {{ $items->total() }}</small>
        {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endif
