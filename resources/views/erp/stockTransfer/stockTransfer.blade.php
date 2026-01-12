@extends('erp.master')

@section('title', 'Stock Transfer')

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
        
        .transition-all { transition: all 0.2s ease-in-out; }
        #transferTable tbody tr:hover { 
            background-color: #f8faff !important;
        }
        
        .form-select, .form-control { border-color: #f1f3f5; }
        .form-select:focus, .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            border: 1px solid transparent;
            cursor: pointer;
        }
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
    </style>

    <!-- Header Section -->
    <div class="container-fluid px-4 py-3 bg-white border-bottom">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Stock Transfer</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0">Stock Transfer</h2>
                <p class="text-muted mb-0">Manage and track inventory movements across all locations.</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#stockTransferModal">
                    <i class="fas fa-plus-circle me-2"></i>Make Transfer
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i><strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filters Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="" id="filterForm" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Search Product/Variation</label>
                        <div class="input-group border rounded-3 overflow-hidden">
                            <span class="input-group-text bg-white border-0"><i class="fas fa-search text-primary"></i></span>
                            <input type="text" name="search" class="form-control border-0 px-2" placeholder="Product name, variation, ID..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-semibold text-muted small text-uppercase">From Location</label>
                        <select class="form-select border rounded-3" name="from_branch_id">
                            <option value="">All Locations</option>
                            <optgroup label="Branches">
                                @foreach ($branches as $branch)
                                    <option value="branch_{{ $branch->id }}" {{ ($filters['from_branch_id'] ?? '') == ('branch_' . $branch->id) || ($filters['from_branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Warehouses">
                                @foreach ($warehouses as $warehouse)
                                    <option value="warehouse_{{ $warehouse->id }}" {{ ($filters['from_branch_id'] ?? '') == ('warehouse_' . $warehouse->id) || ($filters['from_warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-semibold text-muted small text-uppercase">To Location</label>
                        <select class="form-select border rounded-3" name="to_branch_id">
                            <option value="">All Locations</option>
                            <optgroup label="Branches">
                                @foreach ($branches as $branch)
                                    <option value="branch_{{ $branch->id }}" {{ ($filters['to_branch_id'] ?? '') == ('branch_' . $branch->id) || ($filters['to_branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Warehouses">
                                @foreach ($warehouses as $warehouse)
                                    <option value="warehouse_{{ $warehouse->id }}" {{ ($filters['to_branch_id'] ?? '') == ('warehouse_' . $warehouse->id) || ($filters['to_warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Status</label>
                        <select class="form-select border rounded-3" name="status">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-12">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Transfer Date Range</label>
                        <div class="input-group border rounded-3 overflow-hidden">
                            <input type="date" name="date_from" class="form-control border-0 border-end" value="{{ $filters['date_from'] ?? '' }}" title="Start Date">
                            <span class="input-group-text bg-light border-0 px-2 text-muted small">to</span>
                            <input type="date" name="date_to" class="form-control border-0" value="{{ $filters['date_to'] ?? '' }}" title="End Date">
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center mt-2">
                        <div class="d-flex align-items-center gap-3">
                            <label class="fw-semibold text-muted small text-uppercase mb-0">Quick Filter:</label>
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="quick_filter" id="filter_all" value="" {{ !($filters['quick_filter'] ?? '') ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                <label class="btn quick-filter-btn" for="filter_all">All</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="filter_today" value="today" {{ ($filters['quick_filter'] ?? '') == 'today' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                <label class="btn quick-filter-btn" for="filter_today">Today</label>

                                <input type="radio" class="btn-check" name="quick_filter" id="filter_monthly" value="monthly" {{ ($filters['quick_filter'] ?? '') == 'monthly' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                <label class="btn quick-filter-btn" for="filter_monthly">Monthly</label>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('stocktransfer.list') }}" class="btn btn-light border px-4 rounded-3 text-muted">
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

        <!-- Stock Transfer Listing Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">Transfer History</h5>
                        <p class="text-muted small mb-0">Overview of all stock movements and their current status.</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="btn-group shadow-sm">
                            <a href="{{ route('stocktransfer.export.pdf', array_merge(request()->all(), ['action' => 'print'])) }}" target="_blank" class="btn btn-sm btn-light border px-3 fw-medium">
                                <i class="fas fa-print me-1"></i>Print
                            </a>
                            <a href="{{ route('stocktransfer.export.pdf', request()->all()) }}" class="btn btn-sm btn-light border px-3 fw-medium text-danger">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </a>
                            <a href="{{ route('stocktransfer.export.excel', request()->all()) }}" class="btn btn-sm btn-light border px-3 fw-medium text-success">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </a>
                        </div>
                        @if($transfers->total() > 0)
                            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                Total: {{ $transfers->total() }} Records
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="transferTable">
                        <thead class="bg-light text-muted small text-uppercase fw-bold">
                            <tr>
                                <th class="ps-4 border-0 py-3">Product Information</th>
                                <th class="border-0 py-3">Source & Destination</th>
                                <th class="border-0 py-3 text-center">Quantity</th>
                                <th class="border-0 py-3 text-center">Status</th>
                                <th class="border-0 py-3">Audit Log</th>
                                <th class="pe-4 border-0 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transfers as $transfer)
                                <tr class="transition-all">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 rounded-3 bg-light text-primary d-flex align-items-center justify-content-center">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $transfer->product->name ?? '-' }}</div>
                                                @if($transfer->variation_id && $transfer->variation)
                                                    <small class="text-primary fw-medium">{{ $transfer->variation->name ?? 'Variation #' . $transfer->variation_id }}</small>
                                                @else
                                                    <small class="text-muted small">Standard Product</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small mb-1">
                                            <span class="text-muted me-2">From:</span>
                                            <span class="fw-medium text-dark">
                                                @if($transfer->from_type === 'branch')
                                                    <i class="fas fa-store me-1 x-small text-muted"></i> {{ $transfer->fromBranch->name ?? '-' }}
                                                @else
                                                    <i class="fas fa-warehouse me-1 x-small text-muted"></i> {{ $transfer->fromWarehouse->name ?? '-' }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="small">
                                            <span class="text-muted me-3">To:</span>
                                            <span class="fw-medium text-dark">
                                                @if($transfer->to_type === 'branch')
                                                    <i class="fas fa-store me-1 x-small text-muted"></i> {{ $transfer->toBranch->name ?? '-' }}
                                                @else
                                                    <i class="fas fa-warehouse me-1 x-small text-muted"></i> {{ $transfer->toWarehouse->name ?? '-' }}
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark fs-6">{{ $transfer->quantity }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = [
                                                'pending' => 'bg-warning-soft text-warning border-warning',
                                                'approved' => 'bg-info-soft text-info border-info',
                                                'shipped' => 'bg-primary-soft text-primary border-primary',
                                                'delivered' => 'bg-success-soft text-success border-success',
                                                'rejected' => 'bg-danger-soft text-danger border-danger',
                                            ][$transfer->status] ?? 'bg-secondary-soft text-secondary border-secondary';
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}" data-transfer-id="{{ $transfer->id }}" data-current-status="{{ $transfer->status }}">
                                            <i class="fas fa-circle me-1 x-small" style="font-size: 0.5rem;"></i> {{ ucfirst($transfer->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-muted mb-1">
                                            <i class="far fa-user me-1 x-small"></i> {{@$transfer->requestedPerson->first_name}} {{ @$transfer->requestedPerson->last_name }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1 x-small"></i> {{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d M, H:i') : '-' }}
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('stocktransfer.show',$transfer->id) }}" class="btn btn-sm btn-light border-0 text-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(in_array($transfer->status, ['pending', 'rejected']))
                                                <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border-0 text-danger" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fas fa-exchange-alt fa-3x text-light mb-3"></i>
                                            <h6 class="text-muted">No stock transfers found</h6>
                                            <a href="{{ route('stocktransfer.list') }}" class="btn btn-sm btn-outline-primary mt-2">Clear filters</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing <span class="fw-bold text-dark">{{ $transfers->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $transfers->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $transfers->total() }}</span> transfers
                    </span>
                    {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>

        <!-- Stock Transfer Modal -->
        <div class="modal fade" id="stockTransferModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content border-0 shadow" method="POST" action="{{ route('stocktransfer.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Create Stock Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- FROM -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-3">Transfer From</label>
                                    <div class="mb-3">
                                        <select class="form-select from-type-select mb-2" name="from_type" required>
                                            <option value="branch">Branch</option>
                                            <option value="warehouse">Warehouse</option>
                                        </select>
                                    </div>
                                    <div class="from-branch-group">
                                        <select name="from_branch_id" class="form-select">
                                            <option value="">Select Branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="from-warehouse-group" style="display:none;">
                                        <select name="from_warehouse_id" class="form-select">
                                            <option value="">Select Warehouse</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TO -->
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <label class="form-label fw-bold text-muted small text-uppercase mb-3">Transfer To</label>
                                    <div class="mb-3">
                                        <select class="form-select to-type-select mb-2" name="to_type" required>
                                            <option value="branch">Branch</option>
                                            <option value="warehouse">Warehouse</option>
                                        </select>
                                    </div>
                                    <div class="to-branch-group">
                                        <select name="to_branch_id" class="form-select">
                                            <option value="">Select Branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="to-warehouse-group" style="display:none;">
                                        <select name="to_warehouse_id" class="form-select">
                                            <option value="">Select Warehouse</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{$warehouse->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Product -->
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold">Select Product</label>
                                <select class="form-select" name="product_id" id="productSelect" required style="width: 100%">
                                    <option value="">Search by name or code...</option>
                                </select>
                            </div>

                            <div class="col-md-8" id="variationWrapper" style="display: none;">
                                <label class="form-label fw-bold">Select Variation</label>
                                <select class="form-select" name="variation_id" id="variationSelect" style="width: 100%">
                                    <option value="">Select Variation...</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" class="form-control" name="quantity" min="0.01" step="0.01" required placeholder="0.00">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Internal Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Reason for transfer, special instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">Process Transfer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div class="modal fade" id="statusUpdateModal" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content border-0 shadow" id="statusUpdateForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Update Transfer Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="transfer_id" id="modalTransferId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Status</label>
                            <select class="form-select rounded-3" name="status" id="modalStatusSelect">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <p class="text-muted small">Changing status may affect inventory levels at both source and destination.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <script>
            function applyQuickFilter(value) {
                document.getElementsByName('date_from')[0].value = '';
                document.getElementsByName('date_to')[0].value = '';
                document.getElementById('filterForm').submit();
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Function to clean up stuck modal backdrop (only called after modal is closed)
                function cleanupModalBackdrop() {
                    // Wait a moment to ensure modal animation is complete
                    setTimeout(function() {
                        // Check if any modal is currently showing
                        var hasVisibleModal = document.querySelector('.modal.show');
                        
                        // Only clean up if no modal is visible
                        if (!hasVisibleModal) {
                            // Remove any stuck backdrops
                            var backdrops = document.querySelectorAll('.modal-backdrop');
                            backdrops.forEach(function(backdrop) {
                                backdrop.remove();
                            });
                            
                            // Clean up body classes
                            if (document.body.classList.contains('modal-open')) {
                                document.body.classList.remove('modal-open');
                            }
                            if (document.body.style.paddingRight) {
                                document.body.style.paddingRight = '';
                            }
                        }
                    }, 150);
                }

                // Initialize modal instance once
                var stockTransferModalElement = document.getElementById('stockTransferModal');
                var stockTransferModal = null;
                if (stockTransferModalElement) {
                    stockTransferModal = new bootstrap.Modal(stockTransferModalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                }

                document.querySelectorAll('[data-bs-target="#stockTransferModal"]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (stockTransferModal) {
                            stockTransferModal.show();
                        }
                    });
                });

                $('#productSelect').select2({
                    placeholder: 'Search or select a product',
                    allowClear: true,
                    ajax: {
                        url: '/erp/products/search',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    return { id: item.id, text: item.name };
                                })
                            };
                        },
                        cache: true
                    },
                    width: 'resolve',
                    dropdownParent: $('#stockTransferModal'),
                });

                // Initialize variation select2
                $('#variationSelect').select2({
                    placeholder: 'Select Variation...',
                    allowClear: true,
                    width: 'resolve',
                    dropdownParent: $('#stockTransferModal'),
                });

                // Load variations when product is selected
                $('#productSelect').on('change', function() {
                    const productId = $(this).val();
                    loadProductVariations(productId);
                });

                // Function to load product variations
                function loadProductVariations(productId) {
                    if (!productId) {
                        $('#variationWrapper').hide();
                        $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                        return;
                    }
                    
                    $.ajax({
                        url: '/erp/products/' + productId + '/variations-list',
                        type: 'GET',
                        dataType: 'json',
                        success: function(variations) {
                            // Clear existing options
                            $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                            
                            // Add variation options
                            if (variations && variations.length > 0) {
                                variations.forEach(function(variation) {
                                    var option = new Option(variation.display_name || variation.name, variation.id, false, false);
                                    $('#variationSelect').append(option);
                                });
                                $('#variationWrapper').show();
                            } else {
                                $('#variationWrapper').hide();
                            }
                            
                            $('#variationSelect').trigger('change');
                        },
                        error: function(xhr) {
                            console.error('Error loading variations:', xhr);
                            $('#variationWrapper').hide();
                            $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                        }
                    });
                }

                // Show/hide branch/warehouse selects for FROM
                $('.from-type-select').on('change', function() {
                    if ($(this).val() === 'branch') {
                        $('.from-branch-group').show();
                        $('.from-warehouse-group').hide();
                    } else if ($(this).val() === 'warehouse') {
                        $('.from-branch-group').hide();
                        $('.from-warehouse-group').show();
                    } else {
                        $('.from-branch-group').hide();
                        $('.from-warehouse-group').hide();
                    }
                }).trigger('change');
                // Show/hide branch/warehouse selects for TO
                $('.to-type-select').on('change', function() {
                    if ($(this).val() === 'branch') {
                        $('.to-branch-group').show();
                        $('.to-warehouse-group').hide();
                    } else if ($(this).val() === 'warehouse') {
                        $('.to-branch-group').hide();
                        $('.to-warehouse-group').show();
                    } else {
                        $('.to-branch-group').hide();
                        $('.to-warehouse-group').hide();
                    }
                }).trigger('change');

                // Reset form when modal is closed
                $('#stockTransferModal').on('hidden.bs.modal', function() {
                    // Reset form fields
                    $('#productSelect').val(null).trigger('change');
                    $('#variationSelect').val(null).trigger('change');
                    $('#variationWrapper').hide();
                    var form = $('#stockTransferModal').find('form')[0];
                    if (form) {
                        form.reset();
                    }
                    
                    // Clean up any stuck backdrops
                    cleanupModalBackdrop();
                });

                $(document).on('click', '.status-badge', function() {
                    var transferId = $(this).data('transfer-id');
                    var currentStatus = $(this).data('current-status');
                    $('#modalTransferId').val(transferId);
                    $('#modalStatusSelect').val(currentStatus);

                    // Enable all options first
                    $('#modalStatusSelect option').prop('disabled', false);

                    // Disable options based on current status
                    if (currentStatus === 'delivered') {
                        $('#modalStatusSelect option').prop('disabled', true);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', false);
                    } else if (currentStatus === 'approved') {
                        $('#modalStatusSelect option[value="pending"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="approved"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="shipped"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="rejected"]').prop('disabled', false);
                    } else if (currentStatus === 'shipped') {
                        $('#modalStatusSelect option[value="pending"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="approved"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="shipped"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="rejected"]').prop('disabled', false);
                    } else if (currentStatus === 'pending') {
                        $('#modalStatusSelect option[value="pending"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="approved"]').prop('disabled', false);
                        $('#modalStatusSelect option[value="shipped"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="delivered"]').prop('disabled', true);
                        $('#modalStatusSelect option[value="rejected"]').prop('disabled', false);
                    }

                    var actionUrl = "{{ route('stocktransfer.status', ['id' => 'TRANSFER_ID']) }}".replace('TRANSFER_ID', transferId);
                    $('#statusUpdateForm').attr('action', actionUrl);
                    var statusModalElement = document.getElementById('statusUpdateModal');
                    if (statusModalElement) {
                        var statusModal = new bootstrap.Modal(statusModalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                        statusModal.show();
                        
                        // Clean up backdrop when status modal is closed
                        $(statusModalElement).on('hidden.bs.modal', function() {
                            cleanupModalBackdrop();
                        });
                    }
                });
            });
        </script>
    </div>
@endsection