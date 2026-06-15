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
                    <th>Branch / Warehouse</th>
                    <th class="text-center">Old Qty</th>
                    <th class="text-center">New Qty</th>
                    <th class="text-center">Diff</th>
                    <th>Created By</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $index => $item)
                    <tr id="adj-row-{{ $item->id }}">
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
                        <td class="text-center">
                            @if($item->adjustment)
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete-adjustment"
                                    data-adj-id="{{ $item->adjustment->id }}"
                                    data-adj-number="{{ $item->adjustment->adjustment_number }}"
                                    data-product="{{ $item->product->name ?? 'this product' }}"
                                    data-diff="{{ $diff }}"
                                    title="Delete & Reverse Stock">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">No adjustments found matching your filters.</td>
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

{{-- Delete Confirmation Modal (kept here so it renders on both initial load and AJAX refresh) --}}
<div class="modal fade" id="deleteAdjustmentModal" tabindex="-1" aria-labelledby="deleteAdjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="deleteAdjustmentModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete Adjustment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 rounded-2 mb-3" style="background:#fff8e1;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-undo text-warning mt-1"></i>
                        <div>
                            <strong>Stock will be reversed!</strong><br>
                            <small class="text-muted">Deleting this adjustment will undo its stock change and restore the previous quantity.</small>
                        </div>
                    </div>
                </div>
                <div class="bg-light rounded-2 p-3 mb-1">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Adjustment No</div>
                            <div class="fw-bold text-primary" id="modal-adj-number">—</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Product</div>
                            <div class="fw-bold text-dark" id="modal-adj-product">—</div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Qty Change</div>
                            <div class="fw-bold" id="modal-adj-diff">—</div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">After Reversal</div>
                            <div class="fw-bold text-warning" id="modal-adj-reversal">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light px-4 pb-4 pt-2">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger px-4 fw-bold" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i>Delete & Reverse Stock
                </button>
            </div>
        </div>
    </div>
</div>
