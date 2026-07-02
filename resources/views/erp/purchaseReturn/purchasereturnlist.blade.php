@extends('erp.master')

@section('title', 'Purchase Return List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            /* Premium Sticky Header & Horizontal Scroll Fix */
            .premium-card { overflow: hidden !important; border: 1px solid #edf2f7; }
            .table-responsive { max-height: 80vh; overflow: auto !important; position: relative; background: #fff; }
            #returnTable { border-collapse: separate; border-spacing: 0; width: 100%; }
            #returnTable thead th { 
                position: sticky; top: 0; z-index: 1000 !important; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
                padding-top: 12px !important; padding-bottom: 12px !important;
                background-color: #f8f9fa;
            }
            #returnTable tbody td { background-color: #fff; }
            
            /* Slim Scrollbar */
            .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
            .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            
            .glass-header { box-shadow: none !important; border-bottom: 1px solid rgba(0,0,0,0.05) !important; margin-bottom: 1rem !important; }
        </style>
        
    <!-- Premium Header -->
    <div class="glass-header px-4 py-3 bg-white">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-uppercase" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted small">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-bold small">Return Registry</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Procurement Return Audit</h4>
                    <span class="badge bg-light text-success border border-success small rounded-pill px-3 py-1">{{ $items->total() }} Returns</span>
                </div>
            </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    @can('create purchase returns')
                    <a href="{{ route('purchaseReturn.create') }}" class="btn btn-primary fw-bold px-4 shadow-sm" style="border-radius: 10px;">
                        <i class="fas fa-plus-circle me-2"></i>New Return Entry
                    </a>
                    @endcan
                </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-3 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('purchaseReturn.list') }}" method="GET" id="filterForm">
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input filter-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filter-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-2 date-group daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                        </div>
                        <div class="col-md-2 date-group daily-group">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                        </div>
                        <div class="col-md-2 date-group monthly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Month</label>
                            <select name="month" class="form-select select2-setup">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 date-group monthly-group yearly-group" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Year</label>
                            <select name="year" class="form-select select2-setup">
                                @foreach(range(date('Y'), date('Y') - 5) as $y)
                                    <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Invoice #</label>
                            <input type="text" name="search" class="form-control" placeholder="Return ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Supplier</label>
                            <select name="supplier_id" class="form-select select2-setup" data-placeholder="Choose Supplier">
                                <option value=""></option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Product</label>
                            <select name="product_id" class="form-select select2-setup" data-placeholder="Choose Product">
                                <option value=""></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Style Code</label>
                            <input type="text" name="style_number" class="form-control" placeholder="Style SKU..." value="{{ request('style_number') }}">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Category</label>
                            <select name="category_id" class="form-select select2-setup" data-placeholder="All Categories">
                                <option value=""></option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Brand</label>
                            <select name="brand_id" class="form-select select2-setup" data-placeholder="All Brands">
                                <option value=""></option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Season</label>
                            <select name="season_id" class="form-select select2-setup" data-placeholder="All Seasons">
                                <option value=""></option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Gender</label>
                            <select name="gender_id" class="form-select select2-setup" data-placeholder="All Genders">
                                <option value=""></option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-excel-export" class="btn btn-outline-success btn-sm fw-bold px-3">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </button>
                            <button type="button" id="btn-pdf-export" class="btn btn-outline-danger btn-sm fw-bold px-3">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="resetBtn" class="btn btn-light border px-4 fw-bold text-muted" style="height: 42px;">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                <i class="fas fa-search me-2"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success') || session('error'))
            <div class="row mb-3">
                <div class="col-12">
                    @if(session('success'))
                        <script>window.addEventListener('DOMContentLoaded', () => erpNotify.success("{{ session('success') }}"));</script>
                    @endif
                    @if(session('error'))
                        <script>window.addEventListener('DOMContentLoaded', () => erpNotify.error("{{ session('error') }}"));</script>
                    @endif
                </div>
            </div>
        @endif

            <!-- Script for Date Toggling -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const reportRadios = document.querySelectorAll('input[name="report_type"]');
                    
                    function toggleDateGroups() {
                        const type = document.querySelector('input[name="report_type"]:checked').value;
                        document.querySelectorAll('.date-group').forEach(el => el.style.display = 'none');
                        
                        if (type === 'daily') {
                            document.querySelectorAll('.daily-group').forEach(el => el.style.display = 'block');
                        } else if (type === 'monthly') {
                            document.querySelectorAll('.monthly-group').forEach(el => el.style.display = 'block');
                        } else if (type === 'yearly') {
                            document.querySelectorAll('.yearly-group').forEach(el => el.style.display = 'block');
                        }
                    }

                    reportRadios.forEach(radio => {
                        radio.addEventListener('change', toggleDateGroups);
                    });
                    
                    // Init
                    toggleDateGroups();
                });
            </script>

            <!-- Table Container for AJAX -->
            <div id="table-container">
                @include('erp.purchaseReturn.partials.table')
            </div>
        </div>
    </div>

    <!-- Global Delete Form -->
    <form id="global-delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Select2 Configuration -->
    @push('scripts')
    <script>
        $(document).ready(function() {

            // AJAX Filtering Logic
            function fetchReturnsData(url = null) {
                const form = $('#filterForm');
                const targetUrl = url || form.attr('action');
                const data = url ? null : form.serialize();

                $('#table-container').css('opacity', '0.5');

                $.ajax({
                    url: targetUrl,
                    data: data,
                    success: function (response) {
                        $('#table-container').html(response);
                        $('#table-container').css('opacity', '1');
                    },
                    error: function () {
                        $('#table-container').css('opacity', '1');
                        alert('Error loading data. Please try again.');
                    }
                });
            }

            // Intercept Filter Form Submission
            $('#filterForm').on('submit', function (e) {
                e.preventDefault();
                fetchReturnsData();
            });



            // Intercept Pagination Clicks
            $(document).on('click', '.pagination a', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (url) {
                    fetchReturnsData(url);
                    $('html, body').animate({
                        scrollTop: $("#table-container").offset().top - 100
                    }, 200);
                }
            });

            // Reset Filters Button
            $('#resetBtn').on('click', function () {
                const form = $('#filterForm');
                form[0].reset();
                $('.select2-setup').val('').trigger('change');
                
                const today = new Date().toISOString().split('T')[0];
                $('input[name="start_date"]').val(today);
                $('input[name="end_date"]').val(today);

                $('#dailyReport').prop('checked', true).trigger('change');
                fetchReturnsData("{{ route('purchaseReturn.list') }}");
            });

            // Quick Search Table Functionality with Debounce via Event Delegation
            let returnSearchTimeout;
            $(document).on('input', '#returnSearch', function() {
                const value = $(this).val().toLowerCase();
                clearTimeout(returnSearchTimeout);
                
                returnSearchTimeout = setTimeout(function() {
                    $('#returnTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                }, 300);
            });

            // Delete Confirmation (Standard ERP Pattern)
            $(document).on('click', '.delete-return', function() {
                const url = $(this).data('url');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This return record will be deleted and stock/accounts will be reversed!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'px-4 py-2 rounded-3 fw-bold',
                        cancelButton: 'px-4 py-2 rounded-3 fw-bold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('#global-delete-form');
                        form.attr('action', url);
                        form.submit();
                    }
                });
            });

            // Quick Approval Action
            $(document).on('click', '.approve-return', function() {
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Approve Return?',
                    text: "This will mark the return as processed and finalize the transaction.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'px-4 py-2 rounded-3 fw-bold',
                        cancelButton: 'px-4 py-2 rounded-3 fw-bold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: 'processed'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Success!', response.message, 'success').then(() => {
                                        fetchReturnsData();
                                    });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'An error occurred during approval.', 'error');
                            }
                        });
                    }
                });
            });

            // Excel & PDF Export Click Handlers
            $('#btn-excel-export').on('click', function () {
                let data = $('#filterForm').serialize();
                window.open("{{ route('purchaseReturn.export.excel') }}?" + data, '_blank');
            });

            // PDF Export Click Handler
            $('#btn-pdf-export').on('click', function () {
                let data = $('#filterForm').serialize();
                window.open("{{ route('purchaseReturn.export.pdf') }}?" + data, '_blank');
            });
        });
    </script>
    @endpush
@endsection