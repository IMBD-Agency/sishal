<div class="table-responsive">
    <table class="table premium-table reporting-table compact table-hover align-middle mb-0" id="transferTable">
        <thead>
            <tr>
                <th class="ps-3" style="width: 40px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="masterCheckbox">
                    </div>
                </th>
                <th>SL</th>
                <th>Invoice No</th>
                <th>Date</th>
                <th>Source</th>
                <th>Destination</th>
                <th>Requested By</th>
                <th class="text-center">Total Items</th>
                <th class="text-end">Total Amount</th>
                <th class="text-center">Type</th>
                <th class="text-center">Status</th>
                <th class="text-center pe-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transfers as $index => $transfer)
                @php
                    $isReturn = str_starts_with($transfer->invoice_number ?? '', 'RET-');
                @endphp
                <tr class="{{ $isReturn ? 'table-warning' : '' }}">
                    <td class="ps-3">
                        <div class="form-check">
                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $transfer->invoice_number ?? $transfer->id }}" data-type="{{ $transfer->invoice_number ? 'invoice' : 'single' }}">
                        </div>
                    </td>
                    <td class="text-muted">{{ $transfers->firstItem() + $index }}</td>
                    <td class="fw-bold text-dark">
                        @if($transfer->invoice_number)
                            {{ $transfer->invoice_number }}
                            @if($isReturn)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"><i class="fas fa-undo-alt me-1"></i>RETURN</span>
                            @endif
                        @else
                            <span class="text-muted small">N/A (ID: {{ $transfer->id }})</span>
                        @endif
                    </td>
                    <td>{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $transfer->fromBranch->name ?? ($transfer->fromWarehouse->name ?? 'Unknown') }}</td>
                    <td>{{ $transfer->toBranch->name ?? ($transfer->toWarehouse->name ?? 'Unknown') }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-xs bg-light rounded-circle text-primary me-2 d-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:10px;">
                                <i class="fas fa-user"></i>
                            </div>
                            {{ $transfer->requestedPerson->name ?? 'System' }}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ number_format($transfer->grouped_quantity, 0) }} Qty</span>
                    </td>
                    <td class="text-end fw-bold">{{ number_format($transfer->grouped_total_price, 2) }}৳</td>
                    <td class="text-center">
                        @if($isReturn)
                            <span class="badge bg-warning text-dark"><i class="fas fa-undo-alt me-1"></i>Return</span>
                        @else
                            <span class="badge bg-light text-dark border"><i class="fas fa-truck me-1"></i>Transfer</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($transfer->status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'delivered' => 'primary',
                                default => 'warning'
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($transfer->status) }}</span>
                    </td>
                    <td class="pe-3 text-center">
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('stocktransfer.show', $transfer->id) }}" class="action-circle" title="View Details">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                            @if($transfer->status === 'delivered' && !$isReturn)
                                <a href="{{ route('stocktransfer.return', $transfer->id) }}" class="action-circle" title="Return Items"
                                   onclick="return confirm('Initiate a return of these items?')">
                                    <i class="fas fa-undo-alt text-warning"></i>
                                </a>
                            @endif

                            @if((auth()->user()->hasPermissionTo('delete transfers') || auth()->user()->hasPermissionTo('manage transfers')) && in_array($transfer->status, ['pending', 'rejected']))
                                <form action="{{ route('stocktransfer.destroy', $transfer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transfer batch? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-circle border-0 bg-transparent" title="Delete Transfer">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="20" class="text-center py-5">
                        <div class="text-muted opacity-50">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="fw-bold">No transfer records found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($transfers->hasPages())
<div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
    <small class="text-muted fw-500">Showing {{ $transfers->firstItem() }} to {{ $transfers->lastItem() }}</small>
    <div class="ajax-pagination">
        {{ $transfers->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endif
