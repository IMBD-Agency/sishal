<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                @if($type === 'product')
                    <th class="ps-4">Product</th>
                    <th>Category</th>
                @else
                    <th class="ps-4">Category</th>
                    <th class="text-end">Products</th>
                @endif
                <th class="text-end">Quantity Sold</th>
                <th class="text-end">Revenue</th>
                <th class="text-end">Cost</th>
                <th class="text-end pe-4">Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
            <tr>
                <td class="ps-4">
                    @if($type === 'product')
                        <div class="d-flex align-items-center">
                            @if($item['product']->image)
                                <img src="{{ asset($item['product']->image) }}" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            @endif
                            <span class="fw-bold">{{ $item['product']->name }}</span>
                        </div>
                    @else
                        <span class="fw-bold">{{ $item['category_name'] }}</span>
                    @endif
                </td>
                @if($type === 'product')
                    <td>
                        <span class="badge bg-light text-dark border">{{ $item['product']->category->name ?? 'Uncategorized' }}</span>
                    </td>
                @else
                    <td class="text-end fw-semibold text-muted small">{{ number_format($item['product_count']) }}</td>
                @endif
                <td class="text-end fw-semibold">{{ number_format($item['quantity_sold']) }}</td>
                <td class="text-end text-primary fw-semibold">{{ number_format($item['revenue'], 2) }} TK</td>
                <td class="text-end text-muted">{{ number_format($item['cost'], 2) }} TK</td>
                <td class="text-end pe-4">
                    <span class="{{ $item['profit'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ $item['profit'] >= 0 ? '+' : '' }}{{ number_format($item['profit'], 2) }} TK
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">No data found for this period.</td>
            </tr>
            @endforelse
        </tbody>
        @if($data->count() > 0)
        <tfoot class="bg-light fw-bold">
            <tr>
                @if($type === 'product')
                    <td colspan="2" class="ps-4">TOTAL</td>
                @else
                    <td class="ps-4">TOTAL</td>
                    <td class="text-end">{{ number_format($data->sum('product_count')) }}</td>
                @endif
                <td class="text-end">{{ number_format($data->sum('quantity_sold')) }}</td>
                <td class="text-end">{{ number_format($data->sum('revenue'), 2) }} TK</td>
                <td class="text-end text-muted">{{ number_format($data->sum('cost'), 2) }} TK</td>
                <td class="text-end pe-4 text-success">{{ number_format($data->sum('profit'), 2) }} TK</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
