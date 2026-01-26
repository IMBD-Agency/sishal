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
        <div class="premium-card mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Inventory Filters</h6>
                    <!-- Report Period Toggles -->
                    <div class="d-flex gap-3">
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="dailyReport">Custom Range</label>
                        </div>
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthlyReport">Monthly</label>
                        </div>
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearlyReport">Yearly</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('productstock.list') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <!-- Dynamic Date Fields (Toggle visibility via JS) -->
                        <div class="col-md-2 date-range-field">
                            <label class="form-label small fw-bold text-muted text-uppercase">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2 date-range-field">
                             <label class="form-label small fw-bold text-muted text-uppercase">To Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2 month-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase">Select Month</label>
                            <select name="month" class="form-select">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 year-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase">Select Year</label>
                            <select name="year" class="form-select">
                                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Search Product</label>
                            <div class="search-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" placeholder="Name, SKU..." name="search" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Branch Location</label>
                            <select class="form-select select2-simple" name="branch_id">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">Warehouse</label>
                            <select class="form-select select2-simple" name="warehouse_id">
                                <option value="">All Warehouses</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-4 pt-3 border-top">
                            <div class="accordion" id="advancedFilters">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 px-0 bg-transparent shadow-none small fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#moreFilters">
                                            <i class="fas fa-sliders-h me-2"></i>Advanced Options (Category, Brand, Alerts)
                                        </button>
                                    </h2>
                                    <div id="moreFilters" class="accordion-collapse collapse" data-bs-parent="#advancedFilters">
                                        <div class="accordion-body px-0 py-3">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                                                    <select class="form-select select2-simple" name="category_id">
                                                        <option value="">All Categories</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                                                    <select class="form-select select2-simple" name="brand_id">
                                                        <option value="">All Brands</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Season</label>
                                                    <select class="form-select select2-simple" name="season_id">
                                                        <option value="">All Seasons</option>
                                                        @foreach ($seasons as $season)
                                                            <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch pt-4">
                                                        <input class="form-check-input" type="checkbox" name="low_stock" id="lowStockSwitch" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold small text-danger" for="lowStockSwitch">
                                                            <i class="fas fa-exclamation-circle me-1"></i>Low Stock Only
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('productstock.list') }}" class="btn btn-light border fw-bold px-4">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                                <button type="submit" class="btn btn-create-premium px-5">
                                    <i class="fas fa-filter me-2"></i>Filter Results
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="premium-card shadow-sm">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                     <a href="{{ route('productstock.export.excel', request()->all()) }}" class="btn btn-outline-dark btn-sm shadow-sm">
                        <i class="fas fa-file-excel"></i>
                    </a>
                    <a href="{{ route('productstock.export.pdf', request()->all()) }}" class="btn btn-outline-dark btn-sm shadow-sm">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                    <button class="btn btn-outline-dark btn-sm shadow-sm" onclick="window.print()">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table mb-0" id="stockTable">
                        <thead>
                             <tr>
                                <th class="ps-4">Item Details</th>
                                <th>Style / SKU</th>
                                <th>Category</th>
                                <th>Classification</th>
                                <th class="text-center">Total Stock</th>
                                <th class="text-center">Distribution</th>
                                <th class="text-end pe-4">Stock Breakdown</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productStocks as $stock)
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
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="thumbnail-box me-3" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 6px; overflow: hidden;">
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
                                    <td>
                                        <div class="small">{{ $stock->brand->name ?? '-' }}</div>
                                        <div class="small text-muted">{{ $stock->season->name ?? '' }}</div>
                                    </td>
                                    <td class="text-center">
                                         <span class="badge {{ $totalStock > 5 ? 'bg-success' : 'bg-danger' }} fs-6">
                                            {{ $totalStock }}
                                        </span>
                                        @if($hasNegativeStock)
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="Negative Stock Detected"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $locCount = count($branchStockData) + count($warehouseStockData); @endphp
                                        <span class="badge bg-light text-dark border pointer" onclick="$(this).closest('tr').find('.view-breakdown').click()">
                                            {{ $locCount }} Locations
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-action view-breakdown" 
                                                data-branch-stock='@json($branchStockData)'
                                                data-warehouse-stock='@json($warehouseStockData)'>
                                            <i class="fas fa-list-ul"></i>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script>
$(document).ready(function() {
    $('.select2-simple').select2({ width: '100%', dropdownParent: $('body') });

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
});
</script>
@endpush
@endsection