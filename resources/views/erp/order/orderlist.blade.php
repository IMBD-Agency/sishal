@extends('erp.master')

@section('title', 'Order Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order List</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Order List</h2>
                    <p class="text-muted mb-0">Manage order information, contacts, and transactions efficiently.</p>
                </div>
                <div class="col-md-4 text-end">
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">

            <div class="mb-3">
                <form method="GET" action="" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search (ID, Name, Phone, Email)</label>
                        <input type="text" name="search" class="form-control" placeholder="Order ID or Customer's Name, Phone, Email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="shipping">Shipping</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Bill Status</label>
                        <select name="bill_status" class="form-select">
                            <option value="">All</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('order.list') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </form>
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
                                    <th class="border-0">Order ID</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Phone</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th>Bill Status</th>
                                    <th class="border-0">Subtotal</th>
                                    <th class="border-0">Discount</th>
                                    <th class="border-0">Total</th>
                                    @can('delete orders')
                                    <th class="border-0 text-center">Actions</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td><a href="{{ route('order.show',$order->id) }}" class="btn btn-outline-primary">{{ $order->order_number ?? '-' }}</a></td>
                                        <td>{{@$order->name}}</td>
                                        <td>{{@$order->phone}}</td>
                                        <td class="text-center">
                                            <span class="badge 
                                                @if($order->status == 'pending') bg-warning text-dark
                                                @elseif($order->status == 'approved' || $order->status == 'paid') bg-success
                                                @elseif($order->status == 'unpaid' || $order->status == 'rejected') bg-danger
                                                @else bg-secondary
                                                @endif
                                                update-status-btn"
                                                style="cursor:pointer;"
                                                data-id="{{ $order->id }}"
                                                data-status="{{ $order->status }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal"
                                            >
                                                {{ ucfirst($order->status ?? '-') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($order->invoice && $order->invoice->status == 'unpaid') bg-danger
                                                @elseif($order->invoice && $order->invoice->status == 'paid') bg-success
                                                @elseif($order->invoice && $order->invoice->status == 'pending') bg-warning text-dark
                                                @else bg-secondary
                                                @endif
                                            ">
                                                {{ ucfirst($order->invoice->status ?? '-') }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $order->subtotal }}৳
                                        </td>
                                        <td>
                                            {{ $order->discount }}৳
                                        </td>
                                        <td>
                                            {{ $order->total }}৳
                                        </td>
                                        @can('delete orders')
                                        <td class="text-center">
                                            @php
                                                $canDelete = in_array($order->status, ['pending', 'cancelled']) && 
                                                           !in_array($order->status, ['shipping', 'shipped', 'delivered', 'received']) &&
                                                           (!$order->payments || $order->payments->count() == 0 || $order->status === 'cancelled');
                                            @endphp
                                            @if($canDelete)
                                                <button class="btn btn-sm btn-outline-danger delete-order-btn" 
                                                        data-order-id="{{ $order->id }}" 
                                                        data-order-number="{{ $order->order_number }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteOrderModal"
                                                        title="Delete Order">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <span class="text-muted" title="Cannot delete this order">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            @endif
                                        </td>
                                        @endcan
                                    </tr>
                                @empty   
                                <tr>
                                    <td colspan="12" class="text-center">No order Found</td></tr> 
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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