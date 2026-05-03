<div class="table-responsive">
    <table class="table premium-table compact reporting-table table-bordered mb-0" id="salesTable">
        <thead>
            <tr>
                <th class="text-center" style="min-width: 40px;">#</th>
                <th style="min-width: 100px;">Invoice</th>
                <th style="min-width: 90px;">Date</th>
                <th style="min-width: 120px;">Customer</th>
                <th style="min-width: 100px;">Created By</th>
                <th class="text-center">Img</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Season</th>
                <th>Gender</th>
                <th style="min-width: 150px;">Product Name</th>
                <th>Style #</th>
                <th>Color</th>
                <th>Size</th>
                <th class="text-end">Unit Price</th>
                <th class="text-center bg-soft-primary">Sales Qty</th>
                <th class="text-end bg-soft-primary">Sales Amt</th>
                <th class="text-end">Discount</th>
                <th class="text-center bg-soft-danger">Ret Qty</th>
                <th class="text-end bg-soft-danger">Ret Amt</th>
                <th class="text-center bg-soft-success">Act Qty</th>
                <th class="text-end bg-soft-success">Act Amt</th>
                <th class="text-end">Delivery</th>
                <th class="text-end">Exchange</th>
                <th class="text-end fw-bold">Grand Total</th>
                <th class="text-end text-success fw-bold">Paid</th>
                <th class="text-end text-danger fw-bold">Due</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $sale = $item->pos;
                    $invoice = $sale->invoice;
                    $product = $item->product;
                    $variation = $item->variation;
                    $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);
                    
                    $color = '-'; $size = '-';
                    if ($variation && $variation->attributeValues) {
                        foreach($variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) $color = $val->value;
                            elseif (str_contains($attrName, 'size')) $size = $val->value;
                        }
                    }

                    $retQty = $item->returnItems->sum('returned_qty');
                    $retAmt = $item->returnItems->sum('total_price');
                    $actualQty = $item->quantity - $retQty;
                    $actualAmt = $item->total_price - $retAmt;
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $items->firstItem() + $index }}</td>
                    <td>
                        @if($isFirst)
                            <a href="{{ route('pos.show', $sale->id) }}" class="text-decoration-none fw-bold text-primary hover-opacity-75">
                                {{ $sale->sale_number ?? '-' }}
                            </a>
                        @endif
                    </td>
                    <td>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $sale->soldBy ? trim($sale->soldBy->first_name . ' ' . $sale->soldBy->last_name) : '-' }}</td>
                    <td class="text-center">
                        <div class="thumbnail-box" style="width: 30px; height: 30px; margin: 0 auto;">
                             @if($product && $product->image)
                                <img src="{{ asset($product->image) }}" alt="">
                             @else
                                <i class="fas fa-cube text-muted opacity-50 small"></i>
                             @endif
                        </div>
                    </td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ $product->season->name ?? '-' }}</td>
                    <td>{{ $product->gender->name ?? '-' }}</td>
                    <td class="fw-bold text-dark">{{ $product->name ?? '-' }}</td>
                    <td>{{ $product->style_number ?? $product->sku ?? '-' }}</td>
                    <td>{{ $color }}</td>
                    <td>{{ $size }}</td>
                    
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center bg-light">{{ $item->quantity }}</td>
                    <td class="text-end bg-light">{{ number_format($item->total_price, 2) }}</td>
                    <td class="text-end text-danger">
                        @if($isFirst) {{ number_format($sale->discount, 2) }} @endif
                    </td>
                    
                    <td class="text-center text-danger">{{ $retQty ?: '-' }}</td>
                    <td class="text-end text-danger">{{ $retQty ? number_format($retAmt, 2) : '-' }}</td>
                    
                    <td class="text-center text-success fw-bold">{{ $actualQty }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($actualAmt, 2) }}</td>
                    
                    <td class="text-end">
                        @if($isFirst) {{ number_format($sale->delivery, 2) }} @endif
                    </td>
                    <td class="text-end">
                        @if($isFirst) {{ number_format($sale->exchange_amount ?? 0, 2) }} @endif
                    </td>
                    
                    <td class="text-end fw-bold">
                         @if($isFirst) {{ number_format($sale->total_amount, 2) }} @endif
                    </td>
                    <td class="text-end text-success fw-bold">
                         @if($isFirst) {{ number_format($invoice->paid_amount ?? 0, 2) }} @endif
                    </td>
                    <td class="text-end text-danger fw-bold">
                         @if($isFirst) 
                            @if(($invoice->due_amount ?? 0) > 0)
                                {{ number_format($invoice->due_amount, 2) }}
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size: 0.65rem;">Paid</span>
                            @endif
                         @endif
                    </td>
                    <td class="text-center">
                        @if($isFirst)
                            <a href="{{ route('pos.show', $sale->id) }}" class="btn btn-action btn-sm" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="27" class="text-center py-5 text-muted">No sales records found.</td></tr>
            @endforelse
        </tbody>
        <tfoot class="bg-light fw-bold">
            <tr class="text-muted small border-top">
                <td colspan="15" class="text-end">Page Subtotal</td>
                <td class="text-center">{{ $items->sum('quantity') }}</td>
                <td class="text-end">{{ number_format($items->sum('total_price'), 2) }}</td>
                <td class="text-end text-danger">{{ number_format($sale->discount ?? 0, 2) }}</td>
                <td colspan="11"></td>
            </tr>
            <tr class="bg-soft-primary border-top-2">
                <td colspan="15" class="text-end text-uppercase py-3">Grand Total (All Records)</td>
                <td class="text-center py-3">{{ $reportTotals['sell_qty'] }}</td>
                <td class="text-end py-3">{{ number_format($reportTotals['sell_amt'], 2) }}</td>
                <td class="text-end py-3 text-danger">{{ number_format($reportTotals['discount'], 2) }}</td>
                <td colspan="4" class="bg-light"></td>
                <td class="text-end py-3">{{ number_format($reportTotals['delivery'], 2) }}</td>
                <td class="text-end py-3">{{ number_format($reportTotals['exchange'], 2) }}</td>
                <td class="text-end text-dark py-3">{{ number_format($reportTotals['final_total'], 2) }}</td>
                <td class="text-end text-success py-3">{{ number_format($reportTotals['paid'], 2) }}</td>
                <td class="text-end text-danger py-3">{{ number_format($reportTotals['due'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

@if($items->hasPages())
<div class="card-footer bg-white py-3">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $items->firstItem() }} - {{ $items->lastItem() }} of {{ $items->total() }}</small>
        {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endif
