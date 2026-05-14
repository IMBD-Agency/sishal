<div class="premium-card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0" id="adjustmentTable">
            <thead>
                <tr>
                    <th>#SN</th>
                    <th>Adjustment No</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Style #</th>
                    <th>Branch</th>
                    <th class="text-center">Old Qty</th>
                    <th class="text-center">New Qty</th>
                    <th class="text-center">Diff</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $index => $item)
                    <tr>
                        <td>{{ $adjustments->firstItem() + $index }}</td>
                        <td>
                            <span class="fw-bold text-primary">{{ $item->adjustment->adjustment_number ?? 'N/A' }}</span>
                        </td>
                        <td class="text-nowrap">{{ $item->adjustment?->date ? $item->adjustment->date->format('d-M-Y') : 'N/A' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="fw-bold text-dark">{{ $item->product->name ?? 'Deleted Product' }}</div>
                                    <div class="small text-muted">{{ $item->product->category->name ?? 'Uncategorized' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $item->product?->style_number ?: ($item->product?->sku ?? '-') }}</div>
                            @if($item->variation)
                                <div class="small text-muted" style="font-size: 0.7rem;">
                                    {{ optional($item->variation->attributeValues)->pluck('value')->implode(', ') ?? '' }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->adjustment?->branch)
                                <span class="badge bg-light text-dark border"><i class="fas fa-store me-1"></i>{{ $item->adjustment->branch->name }}</span>
                            @elseif($item->adjustment?->warehouse)
                                <span class="badge bg-light text-primary border"><i class="fas fa-warehouse me-1"></i>{{ $item->adjustment->warehouse->name }}</span>
                            @else
                                <span class="badge bg-light text-muted border">System</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ $item->old_quantity }}</td>
                        <td class="text-center fw-bold text-success">{{ $item->new_quantity }}</td>
                        <td class="text-center">
                            @php $diff = $item->new_quantity - $item->old_quantity; @endphp
                            @if($diff > 0)
                                <span class="text-success fw-bold">+{{ $diff }}</span>
                            @elseif($diff < 0)
                                <span class="text-danger fw-bold">{{ $diff }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $item->adjustment->creator->name ?? 'Admin' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">No adjustments found matching your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($adjustments->hasPages())
        <div class="card-footer bg-white border-top p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Showing {{ $adjustments->firstItem() }} to {{ $adjustments->lastItem() }} of {{ $adjustments->total() }} entries
                </div>
                <div class="pagination-premium">
                    {{ $adjustments->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
