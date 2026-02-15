@extends('erp.master')

@section('title', 'Order Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order Management</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Order List</h2>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('order.export.excel', request()->all()) }}" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('order.export.pdf', request()->all()) }}" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filters</h6>
                </div>
                <div class="card-body pt-0">
                    <form method="GET" action="{{ route('order.list') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">General Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control bg-light border-0 shadow-sm" placeholder="Order ID, Name, Phone..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Customer</label>
                            <select name="user_id" class="form-select bg-light border-0 shadow-sm select2" data-placeholder="Select Customer">
                                <option value=""></option>
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('user_id') == $customer->id ? 'selected' : '' }}>
                                        {{ trim($customer->first_name . ' ' . $customer->last_name) }} 
                                        {{ $customer->phone ? '('.$customer->phone.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Order Status</label>
                            <select name="status" class="form-select bg-light border-0 shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>Shipping</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Payment Status</label>
                            <select name="bill_status" class="form-select bg-light border-0 shadow-sm">
                                <option value="">All Statuses</option>
                                <option value="unpaid" {{ request('bill_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ request('bill_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ request('bill_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Date Range</label>
                            <div class="input-group border-0 shadow-sm rounded">
                                <span class="input-group-text bg-light border-0 small">From</span>
                                <input type="date" name="start_date" class="form-control bg-light border-0" value="{{ request('start_date') }}">
                                <span class="input-group-text bg-light border-0 small">To</span>
                                <input type="date" name="end_date" class="form-control bg-light border-0" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-6 text-end align-self-end">
                            <a href="{{ route('order.list') }}" class="btn btn-light px-4 border shadow-sm me-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm font-weight-bold">
                                <i class="fas fa-filter me-1"></i> APPLY FILTERS
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stock order Listing Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Order List</h5>
                    </div>
                </div>
                <div class="card-body p-0">
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
                                            <a href="{{ route('order.show', $order->id) }}" class="fw-bold text-decoration-none">
                                                {{ $order->order_number ?? '-' }}
                                            </a>
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
                                    <td colspan="8" class="text-center py-5 text-muted">No orders found matching your filters.</td>
                                </tr> 
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                        </span>
                        {{ $orders->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Order Confirmation Modal -->
    @can('delete orders')
    <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteOrderModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Order
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone!
                    </div>
                    <p>Are you sure you want to delete order <strong id="deleteOrderNumber"></strong>?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This will:
                        <ul class="mb-0 mt-2">
                            <li>Restore product stock to inventory</li>
                            <li>Delete all order items and related data</li>
                            <li>Remove associated invoices (if not paid)</li>
                            <li>Delete payment records</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteOrderBtn">
                        <i class="fas fa-trash me-1"></i>Yes, Delete Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="updateStatusModalLabel">
                        <i class="fas fa-edit me-2"></i>Update Order Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="orderId" name="order_id">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label">Select New Status</label>
                            <select class="form-select" id="newStatus" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="shipping">Shipping</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Changing status to "Shipping" will automatically deduct stock from inventory.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmUpdateStatusBtn">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update Status Modal
    let orderToUpdate = null;
    
    $('.update-status-btn').on('click', function() {
        orderToUpdate = {
            id: $(this).data('id'),
            status: $(this).data('status')
        };
        $('#orderId').val(orderToUpdate.id);
        $('#newStatus').val(orderToUpdate.status);
    });
    
    $('#confirmUpdateStatusBtn').on('click', function() {
        const orderId = $('#orderId').val();
        const newStatus = $('#newStatus').val();
        
        if (!orderId || !newStatus) {
            showAlert('error', 'Please select a status');
            return;
        }
        
        const $btn = $(this);
        const originalText = $btn.html();
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');
        
        $.ajax({
            url: '{{ route("order.updateStatus", ":id") }}'.replace(':id', orderId),
            type: 'POST',
            data: {
                status: newStatus,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Close modal
                    $('#updateStatusModal').modal('hide');
                    
                    // Reload the page to refresh the order list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', response.message || 'Failed to update status');
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while updating the status';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('error', message);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Cancel Order Direct
    $('.cancel-order-direct').on('click', function() {
        const orderId = $(this).data('id');
        const orderNumber = $(this).data('number');
        
        if (confirm(`Are you sure you want to cancel order ${orderNumber}? This will restore stock.`)) {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: '{{ route("order.updateStatus", ":id") }}'.replace(':id', orderId),
                type: 'POST',
                data: {
                    status: 'cancelled',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', response.message);
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'An error occurred while cancelling the order');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        }
    });

    // Reset modal when closed
    $('#updateStatusModal').on('hidden.bs.modal', function() {
        orderToUpdate = null;
        $('#orderId').val('');
        $('#newStatus').val('');
        $('#confirmUpdateStatusBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Update Status');
    });
    
    // Delete Order Modal
    let orderToDelete = null;
    
    $('.delete-order-btn').on('click', function() {
        orderToDelete = {
            id: $(this).data('order-id'),
            number: $(this).data('order-number')
        };
        $('#deleteOrderNumber').text(orderToDelete.number);
    });
    
    $('#confirmDeleteOrderBtn').on('click', function() {
        if (!orderToDelete) return;
        
        const $btn = $(this);
        const originalText = $btn.html();
        
        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Deleting...');
        
        $.ajax({
            url: '{{ route("erp.order.delete", ":id") }}'.replace(':id', orderToDelete.id),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Close modal
                    $('#deleteOrderModal').modal('hide');
                    
                    // Reload the page to refresh the order list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', response.message || 'Failed to delete order');
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while deleting the order';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('error', message);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Reset modal when closed
    $('#deleteOrderModal').on('hidden.bs.modal', function() {
        orderToDelete = null;
        $('#confirmDeleteOrderBtn').prop('disabled', false).html('<i class="fas fa-trash me-1"></i>Yes, Delete Order');
    });
    
    // Alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the main content
        $('.main-content').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush