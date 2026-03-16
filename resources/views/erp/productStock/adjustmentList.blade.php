@extends('erp.master')

@section('title', 'Product Adjustment List')

@section('body')
@include('erp.components.sidebar')
<div class="main-content bg-light min-vh-100" id="mainContent">
    @include('erp.components.header')



    <!-- Top Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0 text-dark">Product Adjustment List</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('stock.adjustment.create') }}" class="btn btn-primary fw-bold shadow-sm">
                    <i class="fas fa-plus me-2"></i>New Adjustment
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-3 shadow-sm">
            <div class="card-body p-3">
                <form action="{{ route('stock.adjustment.list') }}" method="GET" id="filterForm" autocomplete="off">
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm filter-input" value="{{ request('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm filter-input" value="{{ request('end_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Adjustment ID</label>
                            <input type="text" name="adjustment_number" class="form-control form-control-sm filter-input" placeholder="INV-XXXX" value="{{ request('adjustment_number') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Product</label>
                            <select name="product_id" class="form-select form-select-sm select2-simple filter-select" data-placeholder="All Product">
                                <option value="">All Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style Number</label>
                            <input type="text" name="style_number" class="form-control form-control-sm filter-input" placeholder="Style SKU" value="{{ request('style_number') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                            <select name="category_id" class="form-select form-select-sm filter-select" data-placeholder="All Category">
                                <option value="">All Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end mt-1">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                            <select name="brand_id" class="form-select form-select-sm filter-select" data-placeholder="All Brand">
                                <option value="">All Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                                <select name="season_id" class="form-select form-select-sm filter-select select2-simple" data-placeholder="All Season">
                                <option value="">All Season</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                            <select name="gender_id" class="form-select form-select-sm filter-select" data-placeholder="All Gender">
                                <option value="">All Gender</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2" style="margin-top: 25px;">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill text-white fw-bold shadow-sm filter-btn">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                                <a href="{{ route('stock.adjustment.list') }}" class="btn btn-light border btn-sm flex-fill fw-bold shadow-sm">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </div>
                <div class="card-footer bg-light border-top p-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3" id="btn-excel-export">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" id="btn-pdf-export">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="small text-muted fw-bold">Audit Sync: <span class="text-success">{{ now()->format('H:i') }}</span></span>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>

        <!-- Table Search Wrapper -->
        <div class="d-flex justify-content-end align-items-center mb-3">
            
            <div class="d-flex align-items-center gap-2">
                <label class="small fw-bold text-muted mb-0">Quick Search:</label>
                <input type="text" id="customSearch" class="form-control form-control-sm" placeholder="Filter current results..." style="width: 220px;">
            </div>
        </div>

        <!-- Table Container -->
        <div class="premium-card shadow-sm table-card-relative">
            <div id="loading-overlay" style="z-index: 10;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div id="table-container" class="card-body p-0">
                @include('erp.productStock.components.adjustmentTable')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-simple').select2({ width: '100%' });

        function fetchAdjustments(url = null) {
            $('#loading-overlay').css('display', 'flex');
            let fetchUrl = url || $('#filterForm').attr('action');
            let data = $('#filterForm').serialize();

            $.ajax({
                url: fetchUrl,
                type: 'GET',
                data: data,
                success: function(response) {
                    $('#table-container').html(response);
                    $('#loading-overlay').hide();
                },
                error: function() {
                    $('#loading-overlay').hide();
                    alert('Error loading data');
                }
            });
        }

        $('.filter-radio').on('change', function() { fetchAdjustments(); });
        $('.filter-select').on('change', function() { fetchAdjustments(); });
        $('.filter-input').on('change', function() { fetchAdjustments(); });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchAdjustments();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            fetchAdjustments($(this).attr('href'));
        });

        $('#btn-excel-export').on('click', function() {
            let data = $('#filterForm').serialize();
            window.location.href = "{{ route('stock.adjustment.excel') }}?" + data;
        });

        $('#btn-pdf-export').on('click', function() {
            let data = $('#filterForm').serialize();
            window.location.href = "{{ route('stock.adjustment.pdf') }}?" + data;
        });

        $('#btn-csv-export').on('click', function() {
            let data = $('#filterForm').serialize();
            window.location.href = "{{ route('stock.adjustment.excel') }}?" + data; // Reusing excel logic for simple demo
        });

        $('#customSearch').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $("#table-container tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@endpush
@endsection
