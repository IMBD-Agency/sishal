@extends('erp.master')

@section('title', 'Order Return List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
            .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
            .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
            .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
            .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
            .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
            .bg-purple-soft { background-color: rgba(111, 66, 193, 0.1); }
            .text-purple { color: #6f42c1 !important; }
            
            .transition-all { transition: all 0.2s ease-in-out; }
            #returnTable tbody tr:hover { 
                background-color: #f8faff !important;
                box-shadow: inset 0 0 0 9999px #f8faff;
            }
            .x-small { font-size: 0.7rem; }
            .avatar-sm { width: 32px; height: 32px; font-size: 0.85rem; }
            .form-select, .form-control { border-color: #f1f3f5; border-radius: 8px; }
            .form-select:focus, .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
            #returnTable thead th { letter-spacing: 0.05em; border-bottom: 2px solid #f8f9fa; }
            .filter-card { transition: all 0.3s ease; }
            
            /* Quick Filter Styles */
            .quick-filter-btn {
                background-color: #fff;
                color: #6c757d;
                border: 1px solid #e9ecef;
                padding: 0.4rem 1.25rem;
                font-size: 0.85rem;
                font-weight: 500;
                transition: all 0.2s;
            }
            .quick-filter-btn:hover {
                background-color: #f8f9fa;
                color: #0d6efd;
            }
            .btn-check:checked + .quick-filter-btn {
                background-color: #0d6efd !important;
                color: #fff !important;
                border-color: #0d6efd !important;
                box-shadow: 0 4px 6px rgba(13, 110, 253, 0.15);
            }
            .btn-group .btn-check:first-child + .quick-filter-btn {
                border-top-left-radius: 8px;
                border-bottom-left-radius: 8px;
            }
            .btn-group .btn-check:last-child + .quick-filter-btn {
                border-top-right-radius: 8px;
                border-bottom-right-radius: 8px;
            }
        </style>

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order Returns</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Order Returns</h2>
                    <p class="text-muted mb-0">Manage and track E-commerce order returns.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('orderReturn.create') }}" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>New Return
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Filter Section -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 filter-card">
                <div class="card-body p-4">
                    <form method="GET" action="" id="filterForm">
                        <div class="row g-3">
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Quick Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" value="{{ $filters['search'] ?? '' }}" placeholder="Search ID, Customer, Order #">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Return Date</label>
                                <input type="date" name="return_date" class="form-control" value="{{ $filters['return_date'] ?? '' }}">
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter"></i></button>
                                <a href="{{ route('orderReturn.list') }}" class="btn btn-light border flex-grow-1"><i class="fas fa-sync-alt"></i></a>
                            </div>

                            <div class="col-12 d-flex align-items-center gap-3 mt-2">
                                <label class="fw-bold small text-uppercase text-muted mb-0">Quick Filter:</label>
                                <div class="btn-group shadow-sm" role="group">
                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_all" value="" {{ !request('quick_filter') ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_all">All</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_today" value="today" {{ request('quick_filter') == 'today' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_today">Today</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_monthly" value="monthly" {{ request('quick_filter') == 'monthly' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_monthly">Monthly</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">Order Return Logs</h5>
                            <p class="text-muted x-small mb-0">History of e-commerce product returns.</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="btn-group shadow-sm">
                                <button type="button" class="btn btn-sm btn-light border px-3" id="printReport">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                                <button type="button" class="btn btn-sm btn-light border px-3 text-danger" id="exportPdf">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-sm btn-light border px-3 text-success" id="exportExcel">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                            </div>
                            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold">
                                {{ $returns->total() }} Records
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="returnTable">
                            <thead class="bg-light text-muted small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4 border-0 py-3">Reference</th>
                                    <th class="border-0 py-3">Customer</th>
                                    <th class="border-0 py-3">Order Source</th>
                                    <th class="border-0 py-3">Location</th>
                                    <th class="border-0 py-3">Date</th>
                                    <th class="border-0 py-3 text-center">Status</th>
                                    <th class="border-0 py-3">Refund</th>
                                    <th class="pe-4 border-0 py-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                    <tr class="transition-all">
                                        <td class="ps-4 fw-bold">#OR-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $return->customer->name ?? 'Walk-in' }}</div>
                                            @if($return->customer && $return->customer->phone)
                                                <small class="text-muted"><i class="fas fa-phone-alt me-1 x-small"></i> {{ $return->customer->phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($return->order)
                                                <span class="badge bg-primary-soft text-primary border-primary-soft rounded-pill px-2 py-1">
                                                    <i class="fas fa-shopping-bag me-1"></i> {{ $return->order->order_number ?? 'OrderID-' . $return->order->id }}
                                                </span>
                                            @else
                                                <span class="text-muted italic small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="fw-medium">
                                                @if($return->return_to_type == 'branch') <i class="fas fa-store text-muted me-1"></i> {{ $return->destination_name }}
                                                @elseif($return->return_to_type == 'warehouse') <i class="fas fa-warehouse text-muted me-1"></i> {{ $return->destination_name }}
                                                @elseif($return->return_to_type == 'employee') <i class="fas fa-user-tie text-muted me-1"></i> {{ $return->destination_name }}
                                                @else N/A
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ \Carbon\Carbon::parse($return->return_date)->format('d M, Y') }}</div>
                                            <small class="text-muted opacity-75">{{ \Carbon\Carbon::parse($return->created_at)->format('h:i A') }}</small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'bg-warning-soft text-warning border-warning',
                                                    'approved' => 'bg-success-soft text-success border-success',
                                                    'rejected' => 'bg-danger-soft text-danger border-danger',
                                                    'processed' => 'bg-info-soft text-info border-info',
                                                ];
                                                $currentClass = $statusClasses[$return->status] ?? 'bg-secondary-soft text-secondary border-secondary';
                                            @endphp
                                            <span class="badge border rounded-pill px-3 py-2 fw-medium status-badge {{ $currentClass }}"
                                                  data-id="{{ $return->id }}" 
                                                  data-status="{{ $return->status }}"
                                                  style="cursor:pointer;">
                                                <i class="fas fa-circle me-1 x-small"></i> {{ ucfirst($return->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-soft text-dark border-0 rounded-3">
                                                {{ ucfirst($return->refund_type) }}
                                            </span>
                                            @if(\Illuminate\Support\Str::contains($return->notes, 'Exchanged for Order'))
                                                <span class="badge bg-purple-soft text-purple border-0 rounded-3 ms-1" title="Includes Exchange">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border px-2 py-1 rounded-3" type="button" data-bs-toggle="dropdown">
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
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $returns->firstItem() ?? 0 }}-{{ $returns->lastItem() ?? 0 }} of {{ $returns->total() }} returns</small>
                        {{ $returns->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form id="statusForm">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="fw-bold">Update Return Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 pb-4">
                        <input type="hidden" name="id" id="modalId">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current State</label>
                            <input type="text" class="form-control bg-light border-0" id="currentStatus" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New State</label>
                            <select class="form-select border-2" name="status" id="newStatus" required>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="processed">Processed (Restores Stock)</option>
                            </select>
                            <div class="form-text text-danger mt-2" id="stockWarning" style="display:none;">
                                <i class="fas fa-exclamation-triangle me-1"></i> Marking as Processed will increment inventory levels!
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control" name="notes" id="statusNotes" rows="3" placeholder="Explain status change..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Confirm Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function applyQuickFilter(value) {
        document.querySelector('input[name="return_date"]').value = '';
        document.getElementById('filterForm').submit();
    }

    $(document).ready(function() {
        let activeBadge;

        $('.status-badge').on('click', function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            activeBadge = $(this);
            
            $('#modalId').val(id);
            $('#currentStatus').val(status.charAt(0).toUpperCase() + status.slice(1));
            $('#newStatus').val(status);
            $('#statusNotes').val('');
            $('#stockWarning').toggle(status !== 'processed');
            $('#statusModal').modal('show');
        });

        $('#newStatus').on('change', function() {
            $('#stockWarning').toggle($(this).val() === 'processed');
        });

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#modalId').val();
            const $btn = $(this).find('button[type="submit"]');
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

            $.ajax({
                url: `/erp/order-return/${id}/update-status`,
                method: 'POST',
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                success: (res) => {
                    $('#statusModal').modal('hide');
                    location.reload();
                },
                error: (err) => alert(err.responseJSON?.message || 'Error occurred'),
                complete: () => $btn.prop('disabled', false).text('Confirm Change')
            });
        });

        // Dummy export functionality (needs backend support)
        $('#exportExcel').click(() => alert('Export Excel for Order Returns coming soon!'));
        $('#exportPdf').click(() => alert('Export PDF for Order Returns coming soon!'));
        $('#printReport').click(() => alert('Print for Order Returns coming soon!'));
    });
    </script>
    @endpush
@endsection
