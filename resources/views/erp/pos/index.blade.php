@extends('erp.master')

@section('title', 'Sales History')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')
    
    <style>
        /* Premium Sticky Header & Horizontal Scroll Fix for Sales Report */
        
        /* 1. Maintain card containment to fix layout breakage */
        .premium-card {
            overflow: hidden !important;
            border: 1px solid #edf2f7;
        }

        /* 2. Create an internal scrolling area for the table */
        .table-responsive {
            max-height: 80vh; /* Large height to feel like page scroll */
            overflow: auto !important;
            position: relative;
            background: #fff;
        }

        /* 3. Stick headers to the top of the scrollable box */
        #salesTable {
            border-collapse: separate; /* Required for sticky header compatibility */
            border-spacing: 0;
            width: 100%;
        }

        #salesTable thead th {
            position: sticky;
            top: 0; /* Sticks to the top of .table-responsive */
            background-color: #2d5a4c !important; 
            color: #fff !important;
            z-index: 1000 !important;
            border-bottom: 2px solid #3d6a5c !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Subtle depth shadow */
            padding-top: 12px !important;
            padding-bottom: 12px !important;
        }

        /* 4. Fix for cell backgrounds to ensure they don't overlap shadows */
        #salesTable tbody td {
            background-color: #fff;
        }

        /* Custom Slim Scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        /* Maximize vertical view by making the header static on this page */
        .glass-header {
            position: relative !important;
            top: 0 !important;
            box-shadow: none !important;
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
            margin-bottom: 1rem !important;
        }
    </style>

    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Sales History</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Comprehensive Sales Report</h4>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                 <!-- <button class="btn btn-light fw-bold shadow-sm" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Report
                </button> -->
                <a href="{{ route('pos.manual.create') }}" class="btn btn-create-premium text-nowrap">
                    <i class="fas fa-file-invoice me-2"></i>New Sale
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Filter Search</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('pos.list') }}" method="GET" id="filterForm">
                    <div class="mb-4">
                        <div class="d-flex gap-4">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type', 'yearly') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Date Filters -->
                        <div class="col-md-2 daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('report_type') == 'daily' ? request('start_date') : '' }}">
                        </div>
                        <div class="col-md-2 daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('report_type') == 'daily' ? request('end_date') : '' }}">
                        </div>

                        <div class="col-md-2 monthly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                            <select name="month" class="form-select select2-simple">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 monthly-group yearly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                            <select name="year" class="form-select select2-simple">
                                @foreach(range(date('Y'), date('Y') - 10) as $y)
                                    <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                            <select name="branch_id" class="form-select select2-simple">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Customer</label>
                            <select name="customer_id" class="form-select select2-simple" data-placeholder="Select Customer">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                            <select name="category_id" class="form-select select2-simple">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                            <select name="brand_id" class="form-select select2-simple">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                            <select name="season_id" class="form-select select2-simple">
                                <option value="">All Seasons</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                            <select name="gender_id" class="form-select select2-simple">
                                <option value="">All Genders</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style #</label>
                            <input type="text" name="style_number" class="form-control" placeholder="Style Number" value="{{ request('style_number') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Quick Search</label>
                            <input type="text" class="form-control border-primary" placeholder="Sale #, Product Name, SKU..." name="search" value="{{ request('search') }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light border-top p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3" onclick="exportData('excel')">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" onclick="exportData('pdf')">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="resetFilters" class="btn btn-light border px-4 fw-bold text-muted" style="height: 42px; display: flex; align-items: center;">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                        <button type="submit" form="filterForm" class="btn btn-create-premium px-5" style="height: 42px;">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="premium-card shadow-sm">
             <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list me-2 text-primary"></i>Sales Registry</h6>
                </div>
                <div class="search-wrapper-premium">
                    <input type="text" id="salesSearch" class="form-control rounded-pill search-input-premium" placeholder="Quick find in this registry...">
                    <i class="fas fa-search search-icon-premium"></i>
                </div>
            </div>
            <div class="card-body p-0" id="table-container">
                @include('erp.pos.partials.table')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-simple').select2({
            width: '100%',
            dropdownParent: $('body')
        });

        const reportRadios = document.querySelectorAll('input[name="report_type"]');
        function toggleDateGroups() {
            const type = document.querySelector('input[name="report_type"]:checked').value;
             $('.daily-group, .monthly-group, .yearly-group').hide();
            
            if (type === 'daily') {
                $('.daily-group').show();
            } else if (type === 'monthly') {
                $('.monthly-group').show();
            } else if (type === 'yearly') {
                $('.yearly-group').show();
            }
        }
        reportRadios.forEach(radio => radio.addEventListener('change', toggleDateGroups));
        toggleDateGroups();

        // AJAX Filtering Logic
        function fetchSalesData(url = null) {
            const form = $('#filterForm');
            const targetUrl = url || form.attr('action');
            const data = url ? null : form.serialize();

            $('#table-container').css('opacity', '0.5');

            $.ajax({
                url: targetUrl,
                data: data,
                success: function(response) {
                    $('#table-container').html(response);
                    $('#table-container').css('opacity', '1');
                    
                    // Re-initialize any needed JS for the new table
                    initializeTableScripts();
                },
                error: function() {
                    $('#table-container').css('opacity', '1');
                    alert('Error loading data. Please try again.');
                }
            });
        }

        // Reset Filters Button
        $('#resetFilters').on('click', function() {
            const form = $('#filterForm');
            
            // Reset standard inputs
            form[0].reset();
            
            // Reset Select2 dropdowns
            $('.select2-simple').val('').trigger('change');
            
            // Set default report type
            $('#yearlyReport').prop('checked', true).trigger('change');
            
            // Fetch default data
            fetchSalesData("{{ route('pos.list') }}");
        });

        // Intercept Filter Form Submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchSalesData();
        });

        // Intercept Pagination Clicks (using event delegation)
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url) {
                fetchSalesData(url);
                // Scroll to table top
                $('html, body').animate({
                    scrollTop: $("#table-container").offset().top - 100
                }, 200);
            }
        });

        function initializeTableScripts() {
            // Quick Search Table Functionality with Debounce
            let salesSearchTimeout;
            $('#salesSearch').off('input').on('input', function() {
                const value = $(this).val().toLowerCase();
                clearTimeout(salesSearchTimeout);
                
                salesSearchTimeout = setTimeout(function() {
                    $('#salesTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                }, 300);
            });
        }

        initializeTableScripts();
    });

    function exportData(format) {
        const form = document.getElementById('filterForm');
        const originalAction = form.action;
        const originalTarget = form.target;

        if (format === 'excel' || format === 'csv') {
            form.action = "{{ route('pos.export.excel') }}";
            form.target = "_blank";
            form.submit();
        } else if (format === 'pdf') {
            form.action = "{{ route('pos.export.pdf') }}";
            form.target = "_blank";
            form.submit();
        } 

        // Restore
        form.action = originalAction;
        form.target = originalTarget;
    }
</script>
@endpush
@endsection