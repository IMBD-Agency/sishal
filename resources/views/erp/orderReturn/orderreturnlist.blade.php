@extends('erp.master')

@section('title', 'Order Return')

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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}"
                                    class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order Return List</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Order Return List</h2>
                    <p class="text-muted mb-0">Manage order return information, contacts, and transactions efficiently.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('orderReturn.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-adjust me-2"></i>Add Order Return
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Please check the following errors:
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="container-fluid px-4 py-4">

            <div class="mb-3">
                <form method="GET" action="" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search (Customer, Phone, Email, POS #)</label>
                        <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Customer, Phone, Email, POS #">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Return Date</label>
                        <input type="date" name="return_date" class="form-control" value="{{ $filters['return_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('orderReturn.list') }}" class="btn btn-outline-danger">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Stock purchase Listing Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Order Return List</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="border-0">ID</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Order</th>
                                    <th class="border-0">Return Date</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Refund Type</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                    <tr>
                                        <td>{{ $return->id }}</td>
                                        <td>{{ optional($return->customer)->name }}</td>
                                        <td>
                                            @if($return->order)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                    #SFO{{ $return->order->id }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $return->return_date }}</td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'bg-warning',
                                                    'approved' => 'bg-success',
                                                    'rejected' => 'bg-danger',
                                                    'processed' => 'bg-info'
                                                ];
                                                $badgeClass = $statusClasses[$return->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} status-badge" 
                                                  data-id="{{ $return->id }}" 
                                                  data-status="{{ $return->status }}"
                                                  style="cursor:pointer;">
                                                {{ ucfirst($return->status) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($return->refund_type) }}</td>
                                        <td>
                                            <a href="{{ route('orderReturn.show', $return->id) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($return->status !== 'processed')
                                                <a href="{{ route('orderReturn.edit', $return->id) }}" class="btn btn-warning btn-sm" title="Edit Return">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-warning btn-sm" disabled title="Cannot edit processed returns">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if($return->status === 'pending')
                                                <form action="{{ route('orderReturn.delete', $return->id) }}" method="POST"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Return"
                                                        onclick="return confirm('Are you sure you want to delete this return?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-danger btn-sm" disabled title="Cannot delete non-pending returns">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p class="mb-0">No order returns found for the given criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Showing {{ $returns->firstItem() }} to {{ $returns->lastItem() }} of {{ $returns->total() }} order returns
                        </span>
                        {{ $returns->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endsection

{{-- Status Change Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="statusForm">
        <div class="modal-header">
          <h5 class="modal-title" id="statusModalLabel">Change Order Return Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="order_return_id" id="modalOrderReturnId">
          <div class="mb-3">
            <label for="currentStatus" class="form-label">Current Status</label>
            <input type="text" class="form-control" id="currentStatus" readonly>
          </div>
          <div class="mb-3">
            <label for="newStatus" class="form-label">New Status</label>
            <select class="form-select" name="status" id="newStatus" required>
              <option value="">Select Status</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="processed">Processed</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="statusNotes" class="form-label">Notes (Optional)</label>
            <textarea class="form-control" name="notes" id="statusNotes" rows="3" placeholder="Add any notes about this status change..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let selectedRow;
    $('.status-badge').on('click', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        selectedRow = $(this);
        $('#modalOrderReturnId').val(id);
        $('#currentStatus').val(status.charAt(0).toUpperCase() + status.slice(1));
        $('#newStatus').val('');
        $('#statusNotes').val('');
        $('#statusModal').modal('show');
    });

    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#modalOrderReturnId').val();
        const status = $('#newStatus').val();
        const notes = $('#statusNotes').val();
        if (!status) return;
        $.ajax({
            url: '/erp/order-return/' + id + '/update-status',
            method: 'POST',
            data: {
                status: status,
                notes: notes,
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                $('#statusModal').modal('hide');
                if(res.success) {
                    // Update badge color and text
                    const statusClasses = {
                        'pending': 'bg-warning',
                        'approved': 'bg-success',
                        'rejected': 'bg-danger',
                        'processed': 'bg-info'
                    };
                    selectedRow.text(status.charAt(0).toUpperCase() + status.slice(1));
                    selectedRow.data('status', status);
                    selectedRow.removeClass('bg-warning bg-success bg-danger bg-info bg-secondary');
                    selectedRow.addClass(statusClasses[status] || 'bg-secondary');
                    
                    // Show success message
                    const alertDiv = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<strong>Success!</strong> ' + res.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>');
                    $('.container-fluid').first().prepend(alertDiv);
                    setTimeout(function() { alertDiv.fadeOut(); }, 5000);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to update status.');
            }
        });
    });
});
</script>
@endpush