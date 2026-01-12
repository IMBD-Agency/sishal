@extends('erp.master')

@section('title', 'Purchase Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
            .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
            .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
            .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
            .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
            
            .transition-all { transition: all 0.2s ease-in-out; }
            
            #purchaseTable tbody tr:hover { 
                background-color: #f8faff !important;
                box-shadow: inset 0 0 0 9999px #f8faff;
            }
            
            .x-small { font-size: 0.7rem; }
            .avatar-sm { width: 32px; height: 32px; font-size: 0.85rem; }
            
            .form-select, .form-control { border-color: #f1f3f5; }
            .form-select:focus, .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05); }
            
            #purchaseTable thead th { letter-spacing: 0.05em; border-bottom: 2px solid #f8f9fa; }
            #purchaseTable tbody tr { border-bottom: 1px solid #f8f9fa; }

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
        </style>

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Purchase</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Purchase List</h2>
                    <p class="text-muted mb-0">Manage inventory purchases from suppliers or warehouses.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('purchase.create') }}" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>Purchase
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="" class="row g-3" id="filterForm">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Search Purchase ID</label>
                            <div class="input-group border rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" name="search" class="form-control border-0 px-2" placeholder="ID..." value="{{ $filters['search'] ?? '' }}">
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Status</label>
                            <select name="status" class="form-select border rounded-3">
                                <option value="">All Status</option>
                                <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="received" {{ ($filters['status'] ?? '') == 'received' ? 'selected' : '' }}>Received</option>
                                <option value="cancelled" {{ ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-12">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Purchase Date</label>
                            <div class="input-group border rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="far fa-calendar-alt text-primary"></i></span>
                                <input type="date" name="purchase_date" class="form-control border-0" value="{{ $filters['purchase_date'] ?? '' }}">
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center gap-3">
                                <label class="fw-semibold text-muted small text-uppercase mb-0">Quick Filter:</label>
                                <div class="btn-group shadow-sm" role="group">
                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_all" value="" {{ !($filters['quick_filter'] ?? '') ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_all">All</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_today" value="today" {{ ($filters['quick_filter'] ?? '') == 'today' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_today">Today</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_yesterday" value="yesterday" {{ ($filters['quick_filter'] ?? '') == 'yesterday' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_yesterday">Yesterday</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_last7" value="last_7_days" {{ ($filters['quick_filter'] ?? '') == 'last_7_days' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_last7">7 Days</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_monthly" value="monthly" {{ ($filters['quick_filter'] ?? '') == 'monthly' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_monthly">Monthly</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_yearly" value="yearly" {{ ($filters['quick_filter'] ?? '') == 'yearly' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_yearly">This Year</label>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('purchase.list') }}" class="btn btn-light border px-4 rounded-3 text-muted">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                                <button type="submit" class="btn btn-primary px-4 rounded-3 shadow-sm">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Purchase History</h5>
                            <p class="text-muted small mb-0">List of all inventory purchases.</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('purchase.export.pdf', array_merge(request()->all(), ['action' => 'print'])) }}" target="_blank" class="btn btn-sm btn-light border px-3 fw-medium">
                                    <i class="fas fa-print me-1"></i>Print
                                </a>
                                <a href="{{ route('purchase.export.pdf', request()->all()) }}" class="btn btn-sm btn-light border px-3 fw-medium text-danger">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </a>
                                <a href="{{ route('purchase.export.excel', request()->all()) }}" class="btn btn-sm btn-light border px-3 fw-medium text-success">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </a>
                            </div>
                            @if($purchases->total() > 0)
                                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                    Total: {{ $purchases->total() }} Records
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="purchaseTable">
                            <thead class="bg-light text-muted small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4 border-0 py-3">Purchase ID</th>
                                    <th class="border-0 py-3">Location Information</th>
                                    <th class="border-0 py-3">Purchase Date</th>
                                    <th class="border-0 py-3 text-center">Status</th>
                                    <th class="border-0 py-3 text-end">Total Amount</th>
                                    <th class="pe-4 border-0 py-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchases as $purchase)
                                    <tr class="transition-all">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 rounded-3 bg-light text-primary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-truck-loading"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('purchase.show', $purchase->id) }}" class="text-decoration-none fw-bold text-dark d-block">
                                                        #{{ $purchase->id }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $locationName = 'N/A';
                                                $icon = 'fa-store';
                                                if ($purchase->ship_location_type === 'branch') {
                                                    $branch = \App\Models\Branch::find($purchase->location_id);
                                                    $locationName = $branch ? $branch->name : 'Unknown Branch';
                                                } elseif ($purchase->ship_location_type === 'warehouse') {
                                                    $warehouse = \App\Models\Warehouse::find($purchase->location_id);
                                                    $locationName = $warehouse ? $warehouse->name : 'Unknown Warehouse';
                                                    $icon = 'fa-warehouse';
                                                }
                                            @endphp
                                            <div class="fw-bold text-dark">
                                                <i class="fas {{ $icon }} me-1 text-muted small"></i> {{ $locationName }}
                                            </div>
                                            <small class="text-muted text-uppercase x-small">{{ $purchase->ship_location_type }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">
                                                <i class="far fa-calendar-alt me-1 text-muted small"></i> 
                                                {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') : '-' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = [
                                                    'pending' => 'bg-warning-soft text-warning border-warning',
                                                    'received' => 'bg-success-soft text-success border-success',
                                                    'cancelled' => 'bg-danger-soft text-danger border-danger',
                                                ][$purchase->status] ?? 'bg-secondary-soft text-secondary border-secondary';
                                            @endphp
                                            <span class="badge border {{ $statusClass }} px-3 py-2 rounded-pill update-status-btn"
                                                style="cursor:pointer;"
                                                data-id="{{ $purchase->id }}"
                                                data-status="{{ $purchase->status }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal"
                                            >
                                                {{ ucfirst($purchase->status ?? '-') }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            ৳{{ number_format($purchase->items->sum('total_price'), 2) }}
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('purchase.show', $purchase->id) }}" class="btn btn-sm btn-light border-0" title="View Details">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-sm btn-light border-0" title="Edit">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </a>
                                                <form action="{{ route('purchase.delete', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border-0" title="Delete">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty   
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                                            No Assignment Records Found
                                        </td>
                                    </tr> 
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($purchases->hasPages())
                <div class="card-footer bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} records
                        </span>
                        {{ $purchases->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="updateStatusForm">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light border-0 py-3">
                        <h5 class="modal-title fw-bold" id="updateStatusModalLabel">
                            <i class="fas fa-sync-alt me-2 text-primary"></i>Update Purchase Status
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="purchase_id" id="modalPurchaseId">
                        <div class="mb-4 text-center">
                            <i class="fas fa-info-circle text-info mb-2 fs-2 d-block"></i>
                            <p class="text-muted mb-0">Note: Changing status to <strong class="text-success">Received</strong> will automatically update the inventory stock at the destination location.</p>
                        </div>
                        <div class="mb-3">
                            <label for="modalStatus" class="form-label fw-semibold">New Status</label>
                            <select name="status" id="modalStatus" class="form-select border-2 py-2" required>
                                <option value="pending">Pending</option>
                                <option value="received">Received</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function applyQuickFilter(value) {
            const form = document.getElementById('filterForm');
            const quickFilterInput = form.querySelector('input[name="quick_filter"]:checked');
            
            // Clear date inputs when using quick filters
            if (value) {
                form.querySelector('input[name="purchase_date"]').value = '';
            }
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var updateStatusForm = document.getElementById('updateStatusForm');
            var modalStatus = document.getElementById('modalStatus');
            document.querySelectorAll('.update-status-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var status = this.getAttribute('data-status');
                    updateStatusForm.action = '{{ url('/erp/purchases') }}/' + id + '/update-status';
                    modalStatus.value = status;
                    
                    // If already received, prevent changes as stock is already updated
                    if (status === 'received') {
                        modalStatus.setAttribute('disabled', 'disabled');
                        updateStatusForm.querySelector('button[type="submit"]').style.display = 'none';
                    } else {
                        modalStatus.removeAttribute('disabled');
                        updateStatusForm.querySelector('button[type="submit"]').style.display = 'block';
                    }
                });
            });
        });
    </script>
@endsection