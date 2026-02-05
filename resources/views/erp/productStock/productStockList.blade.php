@extends('erp.master')

@section('title', 'Inventory Dashboard')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Stock Management</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Live Inventory</h4>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">
                        Live Tracking
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                <a href="{{ route('stock.adjustment.list') }}" class="btn btn-light border shadow-sm fw-bold">
                    <i class="fas fa-history me-2"></i>History
                </a>
                <a href="{{ route('stock.adjustment.create') }}" class="btn btn-create-premium text-nowrap">
                    <i class="fas fa-sliders-h me-2"></i>New Adjustment
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-3 shadow-sm">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('productstock.list') }}" id="filterForm" autocomplete="off">
                    <!-- Report Period Toggles -->
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-2 date-range-field">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2 date-range-field">
                             <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2 month-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 year-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                            <select class="form-select form-select-sm select2-simple" name="branch_id" data-placeholder="All Branches">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Warehouse</label>
                            <select class="form-select form-select-sm select2-simple" name="warehouse_id" data-placeholder="All Warehouses">
                                <option value="">All Warehouses</option>
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                            <select class="form-select form-select-sm select2-simple" name="category_id" data-placeholder="All Categories">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                            <select class="form-select form-select-sm select2-simple" name="brand_id" data-placeholder="All Brands">
                                <option value="">All Brands</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                            <select class="form-select form-select-sm select2-simple" name="season_id" data-placeholder="All Seasons">
                                <option value="">All Seasons</option>
                                @foreach ($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mt-2">
                            <div class="form-check form-switch pt-1">
                                <input class="form-check-input" type="checkbox" name="low_stock" id="lowStockSwitch" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-danger" for="lowStockSwitch">Low Stock Only</label>
                            </div>
                        </div>

                        <div class="col-md-2 mt-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill text-white fw-bold shadow-sm filter-btn">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                                <a href="{{ route('productstock.list') }}" class="btn btn-light border btn-sm flex-fill fw-bold shadow-sm filter-btn">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Total Stock Items</h6>
                                <h2 class="fw-bold mb-0">{{ number_format($totalStockQty) }}</h2>
                            </div>
                            <div class="avatar-md bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-boxes fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Total Stock Value</h6>
                                <h2 class="fw-bold mb-0">৳ {{ number_format($totalStockValue, 2) }}</h2>
                            </div>
                            <div class="avatar-md bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-coins fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-info text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Active Products</h6>
                                <h2 class="fw-bold mb-0">{{ $productStocks->total() }}</h2>
                            </div>
                            <div class="avatar-md bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-tag fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 pt-2 border-top">
            <div class="btn-group shadow-none border rounded overflow-hidden">
                 <a href="{{ route('productstock.export.excel', request()->all()) }}" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end">EXCEL</a>
                <a href="{{ route('productstock.export.pdf', request()->all()) }}" class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted border-end">PDF</a>
                <button class="btn btn-white bg-white border-0 py-2 px-3 fw-bold small text-muted" onclick="window.print()">PRINT</button>
            </div>
            <div class="search-wrapper-premium">
                <input type="text" id="tableSearch" class="form-control rounded-pill search-input-premium" placeholder="Live search items...">
                <i class="fas fa-search search-icon-premium"></i>
            </div>
        </div>
        <div class="premium-card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table reporting-table mb-0" id="stockTable">
                        <thead>
                             <tr>
                                <th class="ps-3">SL</th>
                                <th>Item Details</th>
                                <th>Style / SKU</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Season</th>
                                <th>Gender</th>
                                <th class="text-end">Pur. Price</th>
                                <th class="text-end">MRP</th>
                                <th class="text-center">Total Stock</th>
                                <th class="text-end">Stock Value</th>
                                <th class="text-center">Locations</th>
                                <th class="text-center pe-3">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productStocks as $index => $stock)
                                @php
                                    $totalStock = ($stock->simple_branch_stock ?? 0) + 
                                                 ($stock->simple_warehouse_stock ?? 0) + 
                                                 ($stock->var_stock ?? 0);

                                    $branchStockData = [];
                                    $warehouseStockData = [];
                                    $hasNegativeStock = false;

                                    if ($stock->has_variations) {
                                        foreach ($stock->variations as $var) {
                                            foreach ($var->stocks as $s) {
                                                if ($s->branch_id) {
                                                    $name = $s->branch->name ?? 'Unknown';
                                                    $branchStockData[$name] = ($branchStockData[$name] ?? 0) + $s->quantity;
                                                    if ($s->quantity < 0) $hasNegativeStock = true;
                                                } else {
                                                    $name = $s->warehouse->name ?? 'Unknown';
                                                    $warehouseStockData[$name] = ($warehouseStockData[$name] ?? 0) + $s->quantity;
                                                    if ($s->quantity < 0) $hasNegativeStock = true;
                                                }
                                            }
                                        }
                                    } else {
                                        foreach ($stock->branchStock as $s) {
                                            $name = $s->branch->name ?? 'Unknown';
                                            $branchStockData[$name] = ($branchStockData[$name] ?? 0) + $s->quantity;
                                            if ($s->quantity < 0) $hasNegativeStock = true;
                                        }
                                        foreach ($stock->warehouseStock as $s) {
                                            $name = $s->warehouse->name ?? 'Unknown';
                                            $warehouseStockData[$name] = ($warehouseStockData[$name] ?? 0) + $s->quantity;
                                            if ($s->quantity < 0) $hasNegativeStock = true;
                                        }
                                    }
                                @endphp
                                <tr class="{{ $totalStock <= 5 ? 'bg-danger bg-opacity-10' : '' }}">
                                    <td class="ps-3 text-muted">{{ $productStocks->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="thumbnail-box me-3" style="width: 38px; height: 38px;">
                                                 @if($stock->image)
                                                    <img src="{{ asset($stock->image) }}" alt="img" style="width: 100%; height: 100%; object-fit: cover;">
                                                 @else
                                                    <i class="fas fa-image text-muted opacity-50 fa-lg"></i>
                                                 @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $stock->name }}</div>
                                                <div class="small text-muted">{{ number_format($stock->price, 2) }}৳</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-primary bg-light px-2 py-1 rounded small">{{ $stock->sku }}</code>
                                    </td>
                                    <td>
                                        <span class="category-tag">{{ $stock->category->name ?? '-' }}</span>
                                    </td>
                                    <td>{{ $stock->brand->name ?? '-' }}</td>
                                    <td class="text-uppercase small">{{ $stock->season->name ?? 'ALL' }}</td>
                                    <td class="text-uppercase small">{{ $stock->gender->name ?? 'ALL' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($stock->cost, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($stock->price, 2) }}</td>
                                    <td class="text-center">
                                         <span class="badge {{ $totalStock > 5 ? 'bg-success' : 'bg-danger' }} fs-6">
                                            {{ $totalStock }}
                                        </span>
                                        @if($hasNegativeStock)
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="Negative Stock Detected"></i>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($totalStock * $stock->cost, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @php $locCount = count($branchStockData) + count($warehouseStockData); @endphp
                                        <span class="badge bg-light text-dark border pointer" onclick="$(this).closest('tr').find('.view-breakdown').click()">
                                            {{ $locCount }} Locations
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <button class="btn btn-action view-breakdown" 
                                                data-branch-stock='@json($branchStockData)'
                                                data-warehouse-stock='@json($warehouseStockData)'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

             <!-- Pagination -->
            <div class="card-footer bg-white border-top-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="text-muted small mb-0">Displaying {{ $productStocks->firstItem() }} to {{ $productStocks->lastItem() }} of {{ $productStocks->total() }} items</p>
                    {{ $productStocks->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Breakdown Modal -->
    <div class="modal fade" id="stockBreakdownModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom p-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-warehouse me-2 text-primary"></i>Stock Distribution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-uppercase text-muted">
                                <tr>
                                    <th class="ps-4 py-3">Location Type</th>
                                    <th class="py-3">Name</th>
                                    <th class="text-end pe-4 py-3">Available Qty</th>
                                </tr>
                            </thead>
                            <tbody id="stockBreakdownTableBody">
                                <!-- JS Populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light p-3">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle Report Type Toggle
    function toggleReportFields() {
        const reportType = $('.report-type-radio:checked').val();
        
        if (reportType === 'daily') {
            $('.date-range-field').show();
            $('.month-field').hide();
            $('.year-field').hide();
        } else if (reportType === 'monthly') {
            $('.date-range-field').hide();
            $('.month-field').show();
            $('.year-field').show();
        } else if (reportType === 'yearly') {
            $('.date-range-field').hide();
            $('.month-field').hide();
            $('.year-field').show();
        }
    }
    toggleReportFields();
    $('.report-type-radio').on('change', toggleReportFields);

    $('.view-breakdown').on('click', function() {
        var branchData = $(this).data('branch-stock') || [];
        var warehouseData = $(this).data('warehouse-stock') || [];

        // Convert object to array if needed (handles PHP's json_encode behavior for associative arrays)
        var branchStock = (Array.isArray(branchData)) ? branchData : Object.entries(branchData).map(([k,v]) => ({name: k, qty: v}));
        var warehouseStock = (Array.isArray(warehouseData)) ? warehouseData : Object.entries(warehouseData).map(([k,v]) => ({name: k, qty: v}));

        // Use entries if simple key-value pairs were passed directly
        if(branchData && !Array.isArray(branchData) && typeof branchData === 'object' && branchStock.length === 0){
             branchStock = Object.keys(branchData).map(key => ({name: key, qty: branchData[key]}));
        }
         if(warehouseData && !Array.isArray(warehouseData) && typeof warehouseData === 'object' && warehouseStock.length === 0){
             warehouseStock = Object.keys(warehouseData).map(key => ({name: key, qty: warehouseData[key]}));
        }

        var tbody = $('#stockBreakdownTableBody');
        tbody.empty();
        
        let hasData = false;

        branchStock.forEach(function(item) {
            let name = item.name || item.branch_name; // Fallback
            let qty = item.qty || item.quantity;
            if(name) {
                hasData = true;
                tbody.append(`
                    <tr>
                        <td class="ps-4"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Branch</span></td>
                        <td class="fw-bold text-dark">${name}</td>
                        <td class="text-end pe-4"><span class="fw-bold ${qty < 5 ? 'text-danger' : 'text-success'}">${qty}</span></td>
                    </tr>
                `);
            }
        });

        warehouseStock.forEach(function(item) {
             let name = item.name || item.warehouse_name;
             let qty = item.qty || item.quantity;
             if(name) {
                hasData = true;
                tbody.append(`
                    <tr>
                        <td class="ps-4"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Warehouse</span></td>
                        <td class="fw-bold text-dark">${name}</td>
                        <td class="text-end pe-4"><span class="fw-bold ${qty < 5 ? 'text-danger' : 'text-success'}">${qty}</span></td>
                    </tr>
                `);
            }
        });

        if (!hasData) {
            tbody.append('<tr><td colspan="3" class="text-center py-5 text-muted"><i class="fas fa-box-open fa-2x mb-3 opacity-25"></i><p class="mb-0">No stock allocated to specific locations.</p></td></tr>');
        }

        $('#stockBreakdownModal').modal('show');
    });

    // Debounced Live Search
    let searchTimeout;
    $('#tableSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const value = $(this).val().toLowerCase();
        searchTimeout = setTimeout(function() {
            $("#stockTable tbody tr").filter(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1);
            });
        }, 300);
    });
});
</script>
@endpush
@endsection