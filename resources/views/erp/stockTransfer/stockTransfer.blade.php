@extends('erp.master')

@section('title', 'Stock Transfer History')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Stock Transfer</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-info text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Logistic Transfer History</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('stocktransfer.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus-circle me-2"></i>New Transfer
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- Advanced Filters -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Transfer Filter</h6>
                        <div class="d-flex gap-3">
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type_active" id="dailyReport" value="daily" checked>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="dailyReport">Manual Range</label>
                            </div>
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type_active" id="monthlyReport" value="monthly">
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type_active" id="yearlyReport" value="yearly">
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearlyReport">Yearly</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('stocktransfer.list') }}" id="filterForm">
                        <input type="hidden" name="quick_filter" id="quick_filter_hidden" value="">
                        
                        <div class="row g-3">
                            <!-- Primary Row -->
                            <div class="col-md-2 date-range-field">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Date From</label>
                                <input type="date" name="date_from" class="form-control shadow-none" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2 date-range-field">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Date To</label>
                                <input type="date" name="date_to" class="form-control shadow-none" value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-2 month-field d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select shadow-none">
                                    <option value="">All Months</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2 year-field d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select shadow-none">
                                    <option value="">All Years</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Receiver Outlet</label>
                                <select name="to_branch_id" class="form-select shadow-none">
                                    <option value="">All Outlets</option>
                                    @foreach ($branches as $branch)
                                        <option value="branch_{{ $branch->id }}" {{ request('to_branch_id') == 'branch_'.$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                                <select name="status" class="form-select shadow-none">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Invoice</label>
                                <select name="invoice_id" class="form-select shadow-none">
                                    <option value="">All Invoices</option>
                                    @foreach($transfers as $transfer)
                                        <option value="{{ $transfer->id }}" {{ request('invoice_id') == $transfer->id ? 'selected' : '' }}>#{{ $transfer->id }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Product</label>
                                <select name="product_id" class="form-select shadow-none">
                                    <option value="">All Products</option>
                                    @foreach($transfers->pluck('product')->unique('id') as $product)
                                        @if($product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <!-- Expanded Metrics -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style Number</label>
                                <select name="style_number" class="form-select shadow-none text-muted">
                                    <option value="">All Style Numbers</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select name="category_id" class="form-select shadow-none text-muted">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                                <select name="brand_id" class="form-select shadow-none text-muted">
                                    <option value="">All Brands</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                                <select name="season_id" class="form-select shadow-none text-muted">
                                    <option value="">All Seasons</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                                <select name="gender_id" class="form-select shadow-none text-muted">
                                    <option value="">All Genders</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-2">
                            <button type="submit" class="btn btn-create-premium px-4">
                                <i class="fas fa-search me-2"></i>Filter Transfers
                            </button>
                            <a href="{{ route('stocktransfer.list') }}" class="btn btn-light border fw-bold px-4">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Section Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <a href="{{ route('stocktransfer.export.excel', request()->all()) }}" class="btn btn-outline-dark btn-sm fw-bold shadow-sm">
                        <i class="fas fa-file-excel me-1 text-success"></i> Excel
                    </a>
                    <a href="{{ route('stocktransfer.export.pdf', request()->all()) }}" class="btn btn-outline-dark btn-sm fw-bold shadow-sm">
                        <i class="fas fa-file-pdf me-1 text-danger"></i> PDF
                    </a>
                </div>
                <div class="position-relative search-wrap-premium">
                    <i class="fas fa-search position-absolute top-50 start-3 translate-middle-y text-muted opacity-50"></i>
                    <input type="text" id="tableSearch" class="form-control form-control-sm ps-5 border-2 rounded-pill shadow-none" placeholder="Search rows...">
                </div>
            </div>

            <!-- Main Listing Table -->
            <div class="premium-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table table-hover align-middle mb-0 compact" id="transferTable">
                            <thead>
                                    <tr>
                                        <th class="ps-3">SL</th>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Source</th>
                                        <th>Destination</th>
                                        <th>Personnel</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Season</th>
                                        <th>Gender</th>
                                        <th>Product Name</th>
                                        <th>Style No</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Due</th>
                                        <th class="text-center pe-3">ACTION</th>
                                    </tr>
</thead>
                            <tbody>
                                @forelse ($transfers as $index => $transfer)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $transfers->firstItem() + $index }}</td>
                                        <td class="fw-bold text-dark">ST-{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $transfer->fromBranch->name ?? 'Main Store' }}</td>
                                        <td>{{ $transfer->toBranch->name ?? 'Main Store' }}</td>
                                        <td>{{ @$transfer->requestedPerson->name ?? '-' }}</td>
                                        <td>{{ $transfer->product->category->name ?? '-' }}</td>
                                        <td>{{ $transfer->product->brand->name ?? '-' }}</td>
                                        <td>{{ $transfer->product->season->name ?? '-' }}</td>
                                        <td>{{ $transfer->product->gender->name ?? '-' }}</td>
                                        <td class="fw-bold">{{ $transfer->product->name ?? '-' }}</td>
                                        <td class="text-pink fw-bold">{{ $transfer->product->style_number ?? '-' }}</td>
                                        <td>
                                            @if($transfer->variation && $transfer->variation->combinations)
                                                @php
                                                    $color = null;
                                                    foreach($transfer->variation->combinations as $combo) {
                                                        $attrName = strtolower($combo->attribute->name ?? '');
                                                        if(in_array($attrName, ['color', 'colour', 'colors'])) {
                                                            $color = $combo->attributeValue->value ?? null;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                {{ $color ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($transfer->variation && $transfer->variation->combinations)
                                                @php
                                                    $size = null;
                                                    foreach($transfer->variation->combinations as $combo) {
                                                        $attrName = strtolower($combo->attribute->name ?? '');
                                                        if(in_array($attrName, ['size', 'sizes'])) {
                                                            $size = $combo->attributeValue->value ?? null;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                {{ $size ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ number_format($transfer->quantity, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($transfer->total_price, 2) }}</td>
                                        <td class="text-end text-success fw-bold">{{ number_format($transfer->paid_amount, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($transfer->due_amount, 2) }}</td>
                                        <td class="pe-3">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('stocktransfer.show',$transfer->id) }}" class="action-circle" title="View Detail">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>
                                                <button type="button" class="action-circle status-badge" 
                                                        data-transfer-id="{{ $transfer->id }}" 
                                                        data-current-status="{{ $transfer->status }}" 
                                                        title="Change Status">
                                                    <i class="fas fa-cog text-secondary"></i>
                                                </button>
                                                @if(in_array($transfer->status, ['pending', 'rejected']))
                                                    <form action="{{ route('stocktransfer.delete', $transfer->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="action-circle" onclick="return confirm('Are you sure you want to delete this transfer?')" title="Delete">
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
                        {{ $transfers->links('vendor.pagination.bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="mt-4 text-end">
                <div class="d-inline-flex align-items-center gap-4 bg-white border premium-card px-4 py-3 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-muted text-uppercase small">Consolidated Total Qty:</span>
                        <span class="h5 fw-bold text-info mb-0">{{ number_format($transfers->sum('quantity'), 0) }}</span>
                    </div>
                    <div class="vr"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-muted text-uppercase small">Total Dispatch Value:</span>
                        <span class="h5 fw-bold text-success mb-0">{{ number_format($transfers->sum('total_price'), 2) }}৳</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div class="modal fade" id="statusUpdateModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content border-0 shadow-lg" id="statusUpdateForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-light border-0 p-4">
                        <h5 class="fw-bold mb-0">Workflow Status Update</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="transfer_id" id="modalTransferId">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">New Global Status</label>
                            <select class="form-select shadow-none" name="status" id="modalStatusSelect">
                                <option value="pending">Pending Review</option>
                                <option value="approved">Approved</option>
                                <option value="shipped">In Transit (Shipped)</option>
                                <option value="delivered">Fulfilled (Delivered)</option>
                                <option value="rejected">Declined</option>
                            </select>
                        </div>
                        <div class="alert alert-info border-0 small mb-0">
                            <i class="fas fa-info-circle me-2"></i>Status transitions may trigger automatic inventory adjustments across branches.
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-create-premium px-4">Update Workflow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('css')
    <style>
        .breadcrumb-premium { font-size: 0.8rem; }
        .search-wrap-premium { width: 220px; }
        .premium-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #edeff2;
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Report type toggles
            $('.report-type-radio').on('change', function() {
                const val = $(this).val();
                if(val === 'daily') {
                    $('.date-range-field').removeClass('d-none').show();
                    $('.month-field, .year-field').addClass('d-none').hide();
                } else if(val === 'monthly') {
                    $('.month-field, .year-field').removeClass('d-none').show();
                    $('.date-range-field').addClass('d-none').hide();
                } else if(val === 'yearly') {
                    $('.year-field').removeClass('d-none').show();
                    $('.date-range-field, .month-field').addClass('d-none').hide();
                }
            });

            // Table search
            $('#tableSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#transferTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Status Modal logic
            $(document).on('click', '.status-badge', function() {
                var transferId = $(this).data('transfer-id');
                var currentStatus = $(this).data('current-status');
                $('#modalTransferId').val(transferId);
                $('#modalStatusSelect').val(currentStatus);

                // Enable/Disable transitions
                $('#modalStatusSelect option').prop('disabled', false);
                if (currentStatus === 'delivered') {
                    $('#modalStatusSelect option').prop('disabled', true);
                    $('#modalStatusSelect option[value="delivered"]').prop('disabled', false);
                } else if (currentStatus === 'approved') {
                    $('#modalStatusSelect option[value="pending"]').prop('disabled', true);
                } else if (currentStatus === 'shipped') {
                    $('#modalStatusSelect option[value="pending"], #modalStatusSelect option[value="approved"]').prop('disabled', true);
                }

                var actionUrl = "{{ route('stocktransfer.status', ['id' => 'TRANSFER_ID']) }}".replace('TRANSFER_ID', transferId);
                $('#statusUpdateForm').attr('action', actionUrl);
                var statusModal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
                statusModal.show();
            });
        });
    </script>
@endpush
@endsection