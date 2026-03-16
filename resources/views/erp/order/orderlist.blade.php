@extends('erp.master')

@section('title', 'Order Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .bg-purple-soft { background-color: rgba(111, 66, 193, 0.1); }
            .text-purple { color: #6f42c1 !important; }
        </style>

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
            <!-- Premium Filter Card -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="filterForm" action="{{ route('order.list') }}" method="GET" autocomplete="off">
                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly</label>
                            </div>
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Keywords</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Order ID, Customer..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm select2-simple">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>Shipping</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <!-- Field Blocks (Daily) -->
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-2 report-field monthly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Month</label>
                                <select name="month" class="form-select form-select-sm select2-simple">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-2 report-field monthly-group yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                <select name="year" class="form-select form-select-sm select2-simple">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill text-white fw-bold shadow-sm">
                                        <i class="fas fa-filter me-1"></i>APPLY
                                    </button>
                                    <a href="{{ route('order.list') }}" class="btn btn-light border btn-sm flex-fill fw-bold shadow-sm">
                                        <i class="fas fa-undo me-1"></i>RESET
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stock order Listing Table -->
            <div class="card border-0 shadow-sm" id="report-content-area">
                @include('erp.order.orderlist_partial')
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
    function toggleReportFields() {
        var reportType = $('.report-type-radio:checked').val();
        $('.report-field').addClass('d-none');
        
        if (reportType === 'daily') {
            $('.daily-group').removeClass('d-none');
        } else if (reportType === 'monthly') {
            $('.monthly-group').removeClass('d-none');
        } else if (reportType === 'yearly') {
            $('.yearly-group').removeClass('d-none');
        }
    }

    toggleReportFields();
    $('.report-type-radio').change(function() {
        const type = $(this).val();
        if (type === 'daily') {
            const today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
            $('#end_date').val(today);
        }
        toggleReportFields();
    });

    function refreshOrder() {
        const form = $('#filterForm');
        const container = $('#report-content-area');
        
        container.css('opacity', '0.5');
        
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            success: function(response) {
                container.html(response);
                container.css('opacity', '1');
                
                // Update the Excel/PDF links
                const queryParams = form.serialize();
                $('.btn-group a').each(function() {
                    const baseUrl = $(this).attr('href').split('?')[0];
                    const exportType = $(this).text().toLowerCase().includes('pdf') ? 'pdf' : 'excel';
                    $(this).attr('href', baseUrl + '?' + queryParams);
                });
            },
            error: function() {
                container.css('opacity', '1');
                showAlert('error', 'Failed to load order data.');
            }
        });
    }

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        refreshOrder();
    });

    // Handle pagination links
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const container = $('#report-content-area');
        container.css('opacity', '0.5');
        
        $.ajax({
            url: url,
            success: function(response) {
                container.html(response);
                container.css('opacity', '1');
            }
        });
    });

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