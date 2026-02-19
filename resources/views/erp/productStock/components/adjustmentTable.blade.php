<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-erp-success text-white">
            <tr>
                <th class="ps-3">Serial No</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Image</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Season</th>
                <th>Gender</th>
                <th>Product Name</th>
                <th>Style Number</th>
                <th class="text-center">Quantity</th>
                <th>Adjusted By</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @php $currentInvoice = null; @endphp
            @forelse($adjustments as $index => $item)
                <tr>
                    <td class="ps-3 text-muted small">{{ $adjustments->firstItem() + $index }}</td>
                    <td class="fw-bold small">{{ $item->adjustment->adjustment_number }}</td>
                    <td class="small">{{ \Carbon\Carbon::parse($item->adjustment->date)->format('m/d/Y') }}</td>
                    <td>
                        @if($item->product && $item->product->image)
                            <img src="{{ asset($item->product->image) }}" class="rounded shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-image text-muted" style="font-size: 10px;"></i>
                            </div>
                        @endif
                    </td>
                    <td class="small text-muted">{{ optional($item->product)->category->name ?? '-' }}</td>
                    <td class="small text-muted">{{ optional($item->product)->brand->name ?? '-' }}</td>
                    <td class="small text-muted">{{ optional($item->product)->season->name ?? '-' }}</td>
                    <td class="small text-muted">{{ optional($item->product)->gender->name ?? '-' }}</td>
                    <td class="small fw-bold">{{ $item->product->name ?? 'Deleted Product' }}</td>
                    <td class="small font-monospace text-info">{{ optional($item->product)->style_number ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border fw-bold" style="font-size: 0.85rem;">{{ $item->new_quantity }}</span>
                        @php $diff = $item->new_quantity - $item->old_quantity; @endphp
                        <div class="small {{ $diff >= 0 ? 'text-success' : 'text-danger' }} fw-bold" style="font-size: 0.7rem;">
                            {{ $diff >= 0 ? '+' : '' }}{{ $diff }}
                        </div>
                    </td>
                    <td class="small">{{ $item->adjustment->creator->name ?? 'Admin' }}</td>
                    <td class="text-center">
                        {{-- Action buttons if needed, keeping it clean for now --}}
                        <button type="button" class="btn btn-sm btn-light border" title="Quick View" onclick="alert('Adjustment Note: {{ $item->adjustment->notes ?? 'No notes' }}')">
                            <i class="fas fa-sticky-note text-warning"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                            <p class="mb-0">No adjustments found matching your filters</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <div>
        <span class="text-muted small">Showing {{ $adjustments->firstItem() ?? 0 }} to {{ $adjustments->lastItem() ?? 0 }} of {{ $adjustments->total() }} entries</span>
    </div>
    <div class="pagination-ajax">
        {{ $adjustments->links() }}
    </div>
</div>
