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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}"
                                    class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Stock Transfer</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div
                            class="avatar-sm bg-info text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Logistic Transfer History</h4>
                    </div>
                </div>
                <div
                    class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <!-- <a href="{{ route('stocktransfer.list') }}?view_mode=returns" class="btn btn-outline-warning fw-bold shadow-sm">
                                <i class="fas fa-undo-alt me-2"></i>View Returns
                            </a> -->
                    @can('create transfers')
                        <a href="{{ route('stocktransfer.create') }}" class="btn btn-create-premium">
                            <i class="fas fa-plus-circle me-2"></i>New Transfer
                        </a>
                    @endcan
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

            {{-- View Mode Tabs --}}
            @php $viewMode = request('view_mode', 'all'); @endphp
            <div class="d-flex gap-2 mb-4 view-mode-tabs">
                <button data-url="{{ route('stocktransfer.list') }}" data-view-mode="all"
                    class="btn btn-sm fw-bold px-4 ajax-tab {{ $viewMode === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    <i class="fas fa-list me-2"></i>All Records
                    <span
                        class="badge {{ $viewMode === 'all' ? 'bg-light text-dark' : 'bg-secondary text-white' }} ms-1 tab-count-all">{{ $transferCount + $returnCount }}</span>
                </button>
                <button data-url="{{ route('stocktransfer.list') }}?view_mode=transfers" data-view-mode="transfers"
                    class="btn btn-sm fw-bold px-4 ajax-tab {{ $viewMode === 'transfers' ? 'btn-primary' : 'btn-outline-primary' }}">
                    <i class="fas fa-truck me-2"></i>Transfers Only
                    <span
                        class="badge {{ $viewMode === 'transfers' ? 'bg-light text-dark' : 'bg-primary text-white' }} ms-1 tab-count-transfers">{{ $transferCount }}</span>
                </button>
                <button data-url="{{ route('stocktransfer.list') }}?view_mode=returns" data-view-mode="returns"
                    class="btn btn-sm fw-bold px-4 ajax-tab {{ $viewMode === 'returns' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                    <i class="fas fa-undo-alt me-2"></i>Returns Only
                    <span
                        class="badge {{ $viewMode === 'returns' ? 'bg-dark text-white' : 'bg-warning text-dark' }} ms-1 tab-count-returns">{{ $returnCount }}</span>
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i
                            class="fas fa-filter me-2 text-primary"></i>Transfer Filter</h6>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('stocktransfer.list') }}" id="filterForm">
                        <input type="hidden" name="quick_filter" id="quick_filter_hidden" value="">
                        <input type="hidden" name="view_mode" value="{{ request('view_mode', '') }}">

                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-4">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active"
                                    id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Manual
                                    Range</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active"
                                    id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active"
                                    id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <!-- Primary Row -->
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Date From</label>
                                <input type="date" name="date_from" class="form-control shadow-none"
                                    value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Date To</label>
                                <input type="date" name="date_to" class="form-control shadow-none"
                                    value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 month-field {{ $reportType != 'monthly' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select shadow-none">
                                    <option value="">All Months</option>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2 year-field {{ !in_array($reportType, ['monthly', 'yearly']) ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select shadow-none">
                                    <option value="">All Years</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Receiver
                                    Outlet</label>
                                <select name="to_branch_id" class="form-select shadow-none">
                                    <option value="">All Outlets</option>
                                    <optgroup label="Branches">
                                        @foreach ($branches as $branch)
                                            <option value="branch_{{ $branch->id }}" {{ request('to_branch_id') == 'branch_' . $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Warehouses">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}" {{ request('to_branch_id') == 'warehouse_' . $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                                <select name="status" class="form-select shadow-none">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Invoice No</label>
                                <input type="text" name="invoice_number" class="form-control shadow-none"
                                    placeholder="Search invoice..." value="{{ request('invoice_number') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Source
                                    Location</label>
                                <select name="from_branch_id" class="form-select shadow-none">
                                    <option value="">All Sources</option>
                                    <optgroup label="Warehouses">
                                        @foreach ($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}" {{ request('from_branch_id') == 'warehouse_' . $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Branches">
                                        @foreach ($branches as $branch)
                                            <option value="branch_{{ $branch->id }}" {{ request('from_branch_id') == 'branch_' . $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Product</label>
                                <select name="product_id" class="form-select shadow-none select2-filter">
                                    <option value="">All Products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style Number</label>
                                <select name="style_number" class="form-select shadow-none">
                                    <option value="">All Style Numbers</option>
                                    @foreach($styleNumbers as $styleNumber)
                                        <option value="{{ $styleNumber }}" {{ request('style_number') == $styleNumber ? 'selected' : '' }}>{{ $styleNumber }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select name="category_id" class="form-select shadow-none">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                                <select name="brand_id" class="form-select shadow-none">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Global Search</label>
                                <div class="search-wrapper-premium">
                                    <input type="text" name="search" id="globalSearchInput" class="form-control shadow-none"
                                        placeholder="Search Invoice, Style, Product..." value="{{ request('search') }}">
                                    <i class="fas fa-search search-icon-premium" style="right: 15px; left: auto;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light border-top p-3 mt-4 mx-n4 mb-n4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('stocktransfer.export.excel', request()->all()) }}"
                                        class="btn btn-outline-success btn-sm fw-bold px-3 shadow-sm no-loader"
                                        target="_blank">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a>
                                    <a href="{{ route('stocktransfer.export.pdf', request()->all()) }}"
                                        class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm no-loader"
                                        target="_blank">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" id="resetFilters"
                                        class="btn btn-light border px-4 fw-bold text-muted justify-content-center"
                                        style="height: 42px; display: flex; align-items: center;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                        <i class="fas fa-search me-2"></i>Filter Transfers
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small">
                    @if($viewMode === 'returns')
                        <i class="fas fa-undo-alt me-2 text-warning"></i>Transfer Return Records
                    @elseif($viewMode === 'transfers')
                        <i class="fas fa-truck me-2 text-primary"></i>Transfer Records Only
                    @else
                        <i class="fas fa-list me-2 text-primary"></i>All Transfer Data
                    @endif
                </h6>
                <div id="bulkActions" class="d-none animate__animated animate__fadeIn">
                    <button type="button" id="bulkDeleteBtn" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm">
                        <i class="fas fa-trash-alt me-2"></i>Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>

            <!-- Main Listing Table -->
            <div class="premium-card">
                <div class="card-body p-0" id="tableContainer">
                    @include('erp.stockTransfer.partials.table')
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="mt-4 text-end">
                <div class="d-inline-flex align-items-center gap-4 bg-white border premium-card px-4 py-3 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-muted text-uppercase small">Consolidated Total Qty:</span>
                        <span class="h5 fw-bold text-info mb-0"
                            id="summaryQty">{{ number_format($totalQuantity, 0) }}</span>
                    </div>
                    <div class="vr"></div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-muted text-uppercase small">Total Dispatch Value:</span>
                        <span class="h5 fw-bold text-success mb-0"
                            id="summaryValue">{{ number_format($totalValue, 2) }}৳</span>
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
                                <option value="delivered">Fulfilled (Delivered)</option>
                                <option value="rejected">Declined</option>
                            </select>
                        </div>
                        <div class="alert alert-info border-0 small mb-0">
                            <i class="fas fa-info-circle me-2"></i>Status transitions may trigger automatic inventory
                            adjustments across branches.
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-create-premium px-4">Update Workflow</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteTransferModal" tabindex="-1" aria-labelledby="deleteTransferModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold" id="deleteTransferModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete Transfer
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning border-0 rounded-2 mb-3" style="background:#fff8e1;"
                            id="reversalWarningAlert">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-undo text-warning mt-1"></i>
                                <div>
                                    <strong id="modal-warning-title">Stock will be reversed!</strong><br>
                                    <small class="text-muted" id="modal-warning-desc">Deleting this transfer will undo its
                                        stock changes and restore the source quantities.</small>
                                </div>
                            </div>
                        </div>
                        <div class="bg-light rounded-2 p-3 mb-1">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Invoice
                                        No / ID</div>
                                    <div class="fw-bold text-primary" id="modal-transfer-invoice">—</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Status
                                    </div>
                                    <div class="fw-bold" id="modal-transfer-status">—</div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Product &
                                        Qty</div>
                                    <div class="fw-bold text-dark" id="modal-transfer-product-qty">—</div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Source
                                    </div>
                                    <div class="fw-bold text-secondary" id="modal-transfer-source">—</div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">
                                        Destination</div>
                                    <div class="fw-bold text-secondary" id="modal-transfer-destination">—</div>
                                </div>
                                <div class="col-12 mt-2" id="modal-reversal-impact-section">
                                    <div class="small text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Expected
                                        Reversal Impact</div>
                                    <div class="fw-bold text-danger" id="modal-transfer-reversal">—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger px-4 fw-bold" id="confirmDeleteTransferBtn">
                            <i class="fas fa-trash-alt me-2"></i>Delete & Reverse Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .breadcrumb-premium {
                font-size: 0.8rem;
            }

            .search-wrapper-premium {
                position: relative;
            }

            .search-icon-premium {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                color: #9e9e9e;
                font-size: 0.8rem;
            }

            /* Select2 Premium Styling */
            .select2-container--default .select2-selection--single {
                height: 40px !important;
                border: 1px solid #dee2e6 !important;
                border-radius: 8px !important;
                display: flex !important;
                align-items: center !important;
                padding-left: 10px !important;
                background-color: #fff !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 40px !important;
                color: #333 !important;
                padding-left: 0 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 38px !important;
                right: 10px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__clear {
                position: absolute !important;
                right: 35px !important;
                top: 50% !important;
                transform: translateY(-50%) !important;
                margin-right: 0 !important;
                color: #ff4d4f !important;
                font-weight: bold !important;
            }

            .select2-dropdown {
                border: 1px solid #eee !important;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
                border-radius: 12px !important;
                z-index: 9999 !important;
            }

            .select2-search__field {
                border: 1px solid #eee !important;
                border-radius: 8px !important;
                padding: 10px !important;
                margin-bottom: 5px !important;
            }

            .select2-results__option--highlighted[aria-selected] {
                background-color: #f0f7ff !important;
                color: #007bff !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                // Initialize Select2
                $('.select2-filter').select2({
                    placeholder: 'Search...',
                    allowClear: true,
                    width: '100%'
                });
                function loadTransfers(url, formData = null) {
                    $('#tableContainer').css('opacity', '0.5');
                    $.ajax({
                        url: url,
                        data: formData,
                        success: function (res) {
                            $('#tableContainer').html(res.html).css('opacity', '1');

                            // Update counts
                            $('.tab-count-all').text(parseInt(res.transferCount) + parseInt(res.returnCount));
                            $('.tab-count-transfers').text(res.transferCount);
                            $('.tab-count-returns').text(res.returnCount);

                            // Update summary
                            $('#summaryQty').text(res.totalQuantity);
                            $('#summaryValue').text(res.totalValue);

                            // Update export links with current filters
                            updateExportLinks(formData);

                            // Sync window URL without reload
                            const newUrl = url + (url.includes('?') ? '&' : '?') + (formData ? formData : '');
                            window.history.pushState({}, '', newUrl);
                        }
                    });
                }

                function updateExportLinks(queryString) {
                    const excelUrl = "{{ route('stocktransfer.export.excel') }}" + (queryString ? '?' + queryString : '');
                    const pdfUrl = "{{ route('stocktransfer.export.pdf') }}" + (queryString ? '?' + queryString : '');
                    $('.btn-outline-success').attr('href', excelUrl);
                    $('.btn-outline-danger').attr('href', pdfUrl);
                }

                // AJAX Filtering
                $('#filterForm').on('submit', function (e) {
                    e.preventDefault();
                    const formData = $(this).serialize();
                    loadTransfers("{{ route('stocktransfer.list') }}", formData);
                });

                // AJAX Reset
                $('#resetFilters').on('click', function () {
                    $('#filterForm')[0].reset();
                    $('input[name="view_mode"]').val('');
                    $('.report-type-radio[value="daily"]').prop('checked', true);
                    
                    const today = new Date().toISOString().split('T')[0];
                    $('input[name="date_from"]').val(today);
                    $('input[name="date_to"]').val(today);
                    
                    $('.date-range-field').removeClass('d-none').show();
                    $('.month-field, .year-field').addClass('d-none').hide();
                    
                    const formData = $('#filterForm').serialize();
                    loadTransfers("{{ route('stocktransfer.list') }}", formData);
                });

                // View Mode Tabs AJAX
                $('.ajax-tab').on('click', function () {
                    const mode = $(this).data('view-mode');
                    $('input[name="view_mode"]').val(mode === 'all' ? '' : mode);

                    $('.ajax-tab').removeClass('btn-dark btn-primary btn-warning text-white text-dark').addClass('btn-outline-secondary btn-outline-primary btn-outline-warning');
                    if (mode === 'all') $(this).removeClass('btn-outline-secondary').addClass('btn-dark');
                    if (mode === 'transfers') $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
                    if (mode === 'returns') $(this).removeClass('btn-outline-warning').addClass('btn-warning text-dark');

                    $('#filterForm').submit();
                });

                // AJAX Pagination
                $(document).on('click', '.ajax-pagination a', function (e) {
                    e.preventDefault();
                    loadTransfers($(this).attr('href'), $('#filterForm').serialize());
                });

                // Bulk Actions Logic
                $(document).on('change', '#masterCheckbox', function () {
                    $('.row-checkbox').prop('checked', this.checked);
                    toggleBulkActions();
                });

                $(document).on('change', '.row-checkbox', function () {
                    if ($('.row-checkbox:checked').length == $('.row-checkbox').length) {
                        $('#masterCheckbox').prop('checked', true);
                    } else {
                        $('#masterCheckbox').prop('checked', false);
                    }
                    toggleBulkActions();
                });

                function toggleBulkActions() {
                    const checkedCount = $('.row-checkbox:checked').length;
                    if (checkedCount > 0) {
                        $('#bulkActions').removeClass('d-none').addClass('d-block');
                        $('#selectedCount').text(checkedCount);
                    } else {
                        $('#bulkActions').removeClass('d-block').addClass('d-none');
                    }
                }

                $('#bulkDeleteBtn').on('click', function () {
                    const selected = [];
                    $('.row-checkbox:checked').each(function () {
                        selected.push({
                            val: $(this).val(),
                            type: $(this).data('type')
                        });
                    });

                    if (selected.length === 0) return;

                    if (confirm(`Are you sure you want to delete ${selected.length} selected record(s)/batch(es)? This action is permanent.`)) {
                        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

                        $.ajax({
                            url: "{{ route('stocktransfer.bulk.delete') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                selected: selected
                            },
                            success: function (res) {
                                if (res.success) {
                                    showTransferToast('success', res.message);
                                    $('#filterForm').submit();
                                    $('#masterCheckbox').prop('checked', false);
                                } else {
                                    showTransferToast('danger', res.message);
                                    $('#bulkDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete Selected (<span id="selectedCount">0</span>)');
                                }
                            },
                            error: function (err) {
                                showTransferToast('danger', 'Internal server error while deleting.');
                                $('#bulkDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete Selected (<span id="selectedCount">0</span>)');
                            },
                            complete: function () {
                                $('#bulkDeleteBtn').prop('disabled', false);
                                toggleBulkActions();
                            }
                        });
                    }
                });

                // Report type toggles
                $('.report-type-radio').on('change', function () {
                    const val = $(this).val();
                    if (val === 'daily') {
                        $('.date-range-field').removeClass('d-none').show();
                        $('.month-field, .year-field').addClass('d-none').hide();
                    } else if (val === 'monthly') {
                        $('.month-field, .year-field').removeClass('d-none').show();
                        $('.date-range-field').addClass('d-none').hide();
                    } else if (val === 'yearly') {
                        $('.year-field').removeClass('d-none').show();
                        $('.date-range-field, .month-field').addClass('d-none').hide();
                    }
                });

                // Status Modal logic
                $(document).on('click', '.status-badge', function () {
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
                    }

                    var actionUrl = "{{ route('stocktransfer.status', ['id' => 'TRANSFER_ID']) }}".replace('TRANSFER_ID', transferId);
                    $('#statusUpdateForm').attr('action', actionUrl);
                    var statusModal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
                    statusModal.show();
                });

                // Delete Transfer Logic
                let pendingTransferId = null;

                $(document).on('click', '.btn-delete-transfer', function () {
                    const id = $(this).data('transfer-id');
                    const invoice = $(this).data('invoice');
                    const status = $(this).data('status');
                    const product = $(this).data('product');
                    const qty = $(this).data('qty');
                    const source = $(this).data('source');
                    const destination = $(this).data('destination');

                    pendingTransferId = id;

                    // Populate fields
                    $('#modal-transfer-invoice').text(invoice || 'N/A');
                    $('#modal-transfer-status').html(`<span class="badge bg-${getStatusBadgeClass(status)}">${status.toUpperCase()}</span>`);
                    $('#modal-transfer-product-qty').text(`${product} (Qty: ${qty})`);
                    $('#modal-transfer-source').text(source);
                    $('#modal-transfer-destination').text(destination);

                    // Set warning messaging based on status
                    if (status === 'delivered') {
                        $('#modal-warning-title').text('Full Stock Reversal Warning!');
                        $('#modal-warning-desc').text('Deleting this transfer will subtract stock from the destination and restore it to the source.');
                        $('#modal-transfer-reversal').html(`<span class="text-danger">- ${qty} from ${destination}</span><br><span class="text-success">+ ${qty} to ${source}</span>`);
                        $('#modal-reversal-impact-section').show();
                    } else if (status === 'approved') {
                        $('#modal-warning-title').text('Stock Restoration Warning!');
                        $('#modal-warning-desc').text('Stock was only deducted from the source location. Deleting this will restore the quantity back to the source.');
                        $('#modal-transfer-reversal').html(`<span class="text-success">+ ${qty} to ${source}</span>`);
                        $('#modal-reversal-impact-section').show();
                    } else {
                        $('#modal-warning-title').text('Delete Transfer Confirmation');
                        $('#modal-warning-desc').text('No stock was moved for pending or rejected transfers. Deleting is safe and will remove the record.');
                        $('#modal-transfer-reversal').text('No stock reversal needed.');
                        $('#modal-reversal-impact-section').hide();
                    }

                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteTransferModal'));
                    deleteModal.show();
                });

                function getStatusBadgeClass(status) {
                    switch (status) {
                        case 'approved': return 'success';
                        case 'rejected': return 'danger';
                        case 'delivered': return 'primary';
                        default: return 'warning';
                    }
                }

                $(document).on('click', '#confirmDeleteTransferBtn', function () {
                    if (!pendingTransferId) return;

                    const btn = $(this);
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

                    $.ajax({
                        url: '/erp/stock-transfer/' + pendingTransferId,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            const modalEl = document.getElementById('deleteTransferModal');
                            if (modalEl && bootstrap.Modal.getInstance(modalEl)) {
                                bootstrap.Modal.getInstance(modalEl).hide();
                            }
                            pendingTransferId = null;

                            // Show toast
                            showTransferToast('success', res.message || 'Transfer deleted successfully.');

                            // Refresh the table using the existing mechanism
                            $('#filterForm').submit();
                        },
                        error: function (xhr) {
                            const modalEl = document.getElementById('deleteTransferModal');
                            if (modalEl && bootstrap.Modal.getInstance(modalEl)) {
                                bootstrap.Modal.getInstance(modalEl).hide();
                            }
                            const msg = xhr.responseJSON?.message || 'Failed to delete transfer.';
                            showTransferToast('danger', msg);
                        },
                        complete: function () {
                            btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-2"></i>Delete & Reverse Stock');
                        }
                    });
                });

                function showTransferToast(type, message) {
                    const colors = { success: '#198754', danger: '#dc3545' };
                    const icons = { success: 'fa-check-circle', danger: 'fa-times-circle' };
                    const id = 'transfer-toast-' + Date.now();
                    $('body').append(`
                                    <div id="${id}" style="
                                        position:fixed;bottom:24px;right:24px;z-index:9999;
                                        background:${colors[type]};color:#fff;
                                        padding:14px 20px;border-radius:10px;
                                        font-weight:600;font-size:0.9rem;
                                        box-shadow:0 6px 20px rgba(0,0,0,0.18);
                                        display:flex;align-items:center;gap:10px;">
                                        <i class="fas ${icons[type]}"></i> ${message}
                                    </div>`);
                    setTimeout(() => $('#' + id).fadeOut(400, function () { $(this).remove(); }), 3500);
                }
            });
        </script>
    @endpush
@endsection