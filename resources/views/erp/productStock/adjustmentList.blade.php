@extends('erp.master')

@section('title', 'Product Adjustment List')

@section('body')
@include('erp.components.sidebar')
<div class="main-content bg-light min-vh-100" id="mainContent">
    @include('erp.components.header')

    <style>
        .report-filter-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.75rem;
        }
        .table-header-custom {
            background-color: #3d6b52 !important;
            color: white !important;
        }
        .badge-adjustment {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
        }
        .form-label-small {
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #4b5563;
        }
        .btn-export {
            background-color: #6b7280;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
        .btn-export:hover {
            background-color: #4b5563;
            color: white;
        }
        #loading-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
        .table-card-relative { position: relative; }
        .select2-container--default .select2-selection--single {
            height: 31px; border-radius: .25rem; border: 1px solid #dee2e6;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height:31px; font-size: 0.875rem;
        }
    </style>

    <div class="container-fluid px-4 py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Product Adjustment List</h4>
            <a href="{{ route('stock.adjustment.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Adjustment
            </a>
        </div>

        <!-- Filters -->
        <div class="card report-filter-card mb-4">
            <div class="card-body p-4">
                <form action="{{ route('stock.adjustment.list') }}" method="GET" id="filterForm">
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="dailyReport">Daily Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="monthlyReport">Monthly Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="yearlyReport">Yearly Reports</label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label-small">Start Date *</label>
                            <input type="date" name="start_date" class="form-control form-control-sm filter-input" value="{{ request('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-small">End Date *</label>
                            <input type="date" name="end_date" class="form-control form-control-sm filter-input" value="{{ request('end_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-small">Invoice *</label>
                            <input type="text" name="adjustment_number" class="form-control form-control-sm filter-input" placeholder="All Invoice" value="{{ request('adjustment_number') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-small">Product *</label>
                            <select name="product_id" class="form-select form-select-sm select2-simple filter-select">
                                <option value="">All Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-small">Style Number *</label>
                            <input type="text" name="style_number" class="form-control form-control-sm filter-input" placeholder="All Style" value="{{ request('style_number') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label-small">Category *</label>
                            <select name="category_id" class="form-select form-select-sm filter-select">
                                <option value="">All Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label-small">Brand *</label>
                            <select name="brand_id" class="form-select form-select-sm filter-select">
                                <option value="">All Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-small">Season *</label>
                            <select name="season_id" class="form-select form-select-sm filter-select">
                                <option value="">All Season</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-small">Gender *</label>
                            <select name="gender_id" class="form-select form-select-sm filter-select">
                                <option value="">All Gender</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-sm w-100 text-white fw-bold">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="mb-3 d-flex justify-content-between align-items-end">
            <div class="btn-group shadow-sm">
                <button type="button" class="btn btn-export btn-sm border-end" id="btn-csv-export">CSV</button>
                <button type="button" class="btn btn-export btn-sm border-end" id="btn-excel-export">Excel</button>
                <button type="button" class="btn btn-export btn-sm border-end" id="btn-pdf-export">PDF</button>
                <button type="button" class="btn btn-export btn-sm" onclick="window.print()">Print</button>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <label class="small fw-bold text-muted mb-0">Search:</label>
                <input type="text" id="customSearch" class="form-control form-control-sm" placeholder="Search in results..." style="width: 200px;">
            </div>
        </div>

        <!-- Table Container -->
        <div class="card report-filter-card table-card-relative">
            <div id="loading-overlay">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div id="table-container">
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
