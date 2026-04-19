<div class="card-body p-0" id="order-table-container">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="orderTable">
            <thead class="table-light sticky-top">
                <tr>
                    <th class="border-0">Order Info</th>
                    <th class="border-0">Customer</th>
                    <th class="border-0 text-center">Status</th>
                    <th class="border-0">Payment</th>
                    <th class="border-0">Subtotal</th>
                    <th class="border-0">Discount</th>
                    <th class="border-0">Delivery</th>
                    <th class="border-0">Total</th>
                    <th class="border-0 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('order.show', $order->id) }}" class="fw-bold text-decoration-none">
                                    {{ $order->order_number ?? '-' }}
                                </a>
                                @if($order->payment_method == 'exchange_adjustment' || \Illuminate\Support\Str::contains($order->notes, 'Exchange Order'))
                                     <span class="badge bg-purple-soft text-purple border-0 rounded-pill px-2 py-0" style="font-size: 0.7rem;">
                                        Exchange
                                    </span>
                                @endif
                            </div>
                            <div class="small text-muted">{{ $order->created_at->format('d M, Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ @$order->name }}</div>
                            <div class="small text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 0.7rem;"></i>{{ @$order->phone }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = [
                                    'pending' => 'bg-warning-subtle text-warning border-warning',
                                    'approved' => 'bg-info-subtle text-info border-info',
                                    'shipping' => 'bg-primary-subtle text-primary border-primary',
                                    'delivered' => 'bg-success-subtle text-success border-success',
                                    'received' => 'bg-success-subtle text-success border-success',
                                    'cancelled' => 'bg-danger-subtle text-danger border-danger',
                                ][$order->status] ?? 'bg-secondary-subtle text-secondary border-secondary';
                            @endphp
                            <span class="badge {{ $statusClass }} border px-3 py-2 update-status-btn"
                                style="cursor:pointer; font-size: 0.75rem;"
                                data-id="{{ $order->id }}"
                                data-status="{{ $order->status }}"
                                data-bs-toggle="modal"
                                data-bs-target="#updateStatusModal"
                            >
                                {{ ucfirst($order->status ?? '-') }}
                            </span>
                        </td>
                        <td>
                            @php
                                $billStatus = $order->invoice->status ?? 'unpaid';
                                $billClass = [
                                    'paid' => 'bg-success-subtle text-success border-success',
                                    'partial' => 'bg-warning-subtle text-warning border-warning',
                                    'unpaid' => 'bg-danger-subtle text-danger border-danger',
                                ][$billStatus] ?? 'bg-secondary-subtle text-secondary border-secondary';
                            @endphp
                            <span class="badge {{ $billClass }} border px-2 py-1" style="font-size: 0.7rem;">
                                <i class="fas fa-file-invoice-dollar me-1"></i>{{ ucfirst($billStatus) }}
                            </span>
                        </td>
                        <td class="fw-medium">{{ number_format($order->subtotal, 2) }}৳</td>
                        <td class="text-danger">-{{ number_format($order->discount, 2) }}৳</td>
                        <td class="text-muted">+{{ number_format($order->delivery, 2) }}৳</td>
                        <td class="fw-bold text-primary">{{ number_format($order->total, 2) }}৳</td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm shadow-sm border" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('order.show', $order->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                    @if($order->status != 'cancelled')
                                        <li><a class="dropdown-item text-danger" href="{{ route('orderReturn.create', ['order_id' => $order->id]) }}"><i class="fas fa-undo me-2"></i>Process Return</a></li>
                                        <li><a class="dropdown-item text-success" href="{{ route('orderExchange.create', ['order_id' => $order->id]) }}"><i class="fas fa-sync me-2"></i>Process Exchange</a></li>
                                    @endif
                                    @php
                                        $canCancel = !in_array($order->status, ['cancelled', 'delivered', 'received']);
                                        $canDelete = auth()->user()->can('delete orders') && 
                                                   (in_array($order->status, ['pending', 'cancelled']) && 
                                                    !in_array($order->status, ['shipping', 'shipped', 'delivered', 'received']) &&
                                                    (!$order->payments || $order->payments->count() == 0 || $order->status === 'cancelled'));
                                    @endphp
                                    @if($canCancel)
                                        <li><a class="dropdown-item cancel-order-direct" href="javascript:void(0)" data-id="{{ $order->id }}" data-number="{{ $order->order_number }}"><i class="fas fa-ban me-2 text-warning"></i>Cancel Order</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    @can('delete orders')
                                        @if($canDelete)
                                            <li><a class="dropdown-item text-danger delete-order-btn" href="javascript:void(0)" data-order-id="{{ $order->id }}" data-order-number="{{ $order->order_number }}" data-bs-toggle="modal" data-bs-target="#deleteOrderModal"><i class="fas fa-trash me-2"></i>Delete Order</a></li>
                                        @else
                                            <li><a class="dropdown-item text-muted disabled" href="javascript:void(0)"><i class="fas fa-lock me-2"></i>Locked (Paid/In-Process)</a></li>
                                        @endif
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty   
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">No orders found matching your filters.</td>
                </tr> 
                @endforelse
            </tbody>
            <tfoot class="table-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="4" class="ps-4 py-3 text-end text-uppercase small" style="letter-spacing: 0.05em;">Current Page Totals:</td>
                    <td class="py-3 text-dark">{{ number_format($orders->sum('subtotal'), 2) }}৳</td>
                    <td class="py-3 text-danger">-{{ number_format($orders->sum('discount'), 2) }}৳</td>
                    <td class="py-3 text-muted">+{{ number_format($orders->sum('delivery'), 2) }}৳</td>
                    <td class="py-3 text-primary fs-6">{{ number_format($orders->sum('total'), 2) }}৳</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="card-footer bg-white border-0" id="order-pagination">
    <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </span>
        {{ $orders->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
