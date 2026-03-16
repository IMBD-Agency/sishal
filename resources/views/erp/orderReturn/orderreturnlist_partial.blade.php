<div class="card-body p-0" id="order-return-table-container">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="returnTable">
            <thead class="table-light sticky-top">
                <tr>
                    <th class="ps-4 border-0 py-3">Return Info</th>
                    <th class="border-0 py-3">Customer</th>
                    <th class="border-0 py-3">Order Source</th>
                    <th class="border-0 py-3 text-center">Status</th>
                    <th class="border-0 py-3">Refund Amount</th>
                    <th class="border-0 py-3">Refund Type</th>
                    <th class="border-0 py-3">Location</th>
                    <th class="pe-4 border-0 py-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                    @php
                        $refundAmount = $return->items->sum('total_price');
                    @endphp
                    <tr class="transition-all">
                        <td class="ps-4">
                            <div class="fw-bold text-primary">#OR-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($return->return_date)->format('d M, Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $return->customer->name ?? 'Walk-in' }}</div>
                            @if($return->customer && $return->customer->phone)
                                <div class="small text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 0.7rem;"></i>{{ $return->customer->phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if($return->order)
                                <div class="fw-medium">#{{ $return->order->order_number ?? 'Order-'.$return->order->id }}</div>
                                <div class="small text-muted">Ref Order</div>
                            @else
                                <span class="text-muted italic small">Direct Return</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-warning-subtle text-warning border-warning',
                                    'approved' => 'bg-info-subtle text-info border-info',
                                    'rejected' => 'bg-danger-subtle text-danger border-danger',
                                    'processed' => 'bg-success-subtle text-success border-success',
                                ];
                                $currentClass = $statusClasses[$return->status] ?? 'bg-secondary-subtle text-secondary border-secondary';
                            @endphp
                            <span class="badge {{ $currentClass }} border px-3 py-2 fw-medium status-badge"
                                  data-id="{{ $return->id }}" 
                                  data-status="{{ $return->status }}"
                                  style="cursor:pointer; font-size: 0.75rem;">
                                {{ ucfirst($return->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ number_format($refundAmount, 2) }}৳</div>
                            <div class="x-small text-muted">{{ $return->items->count() }} items</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.7rem;">
                                <i class="fas fa-wallet me-1 opacity-50"></i>{{ ucfirst($return->refund_type) }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-medium">
                                @if($return->return_to_type == 'branch') <i class="fas fa-store text-muted me-1"></i> {{ $return->destination_name }}
                                @elseif($return->return_to_type == 'warehouse') <i class="fas fa-warehouse text-muted me-1"></i> {{ $return->destination_name }}
                                @elseif($return->return_to_type == 'employee') <i class="fas fa-user-tie text-muted me-1"></i> {{ $return->destination_name }}
                                @else N/A
                                @endif
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm shadow-sm border" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                    <li><a class="dropdown-item py-2" href="{{ route('orderReturn.show', $return->id) }}"><i class="fas fa-eye me-2 text-primary"></i>Show Details</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('orderReturn.edit', $return->id) }}"><i class="fas fa-edit me-2 text-warning"></i>Edit Record</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('orderReturn.delete', $return->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger" onclick="return confirm('Delete this return?')">
                                                <i class="fas fa-trash-alt me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-box-open fs-1 d-block mb-3 opacity-25"></i>
                                No returns found matching your filters.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="card-footer bg-white border-0 py-3" id="order-return-pagination">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $returns->firstItem() ?? 0 }} to {{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} returns</small>
        {{ $returns->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
