@extends('erp.master')

@section('title', 'Customer Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Customer Summary Report</h4>
                    <p class="text-muted small mb-0">Period: {{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Summary
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form id="customerFilterForm" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Period Type</label>
                            <select name="report_type" id="reportTypeSelector" class="form-select form-select-sm period-toggle-only">
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ $reportType == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        
                        <!-- Daily Date Selector -->
                        <div class="col-md-2 period-input daily-input {{ $reportType == 'daily' ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Select Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm manual-filter-only" value="{{ $startDate->toDateString() }}">
                        </div>

                        <!-- Monthly Selectors -->
                        <div class="col-md-1 period-input monthly-input {{ $reportType == 'monthly' ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Month</label>
                            <select name="month" class="form-select form-select-sm manual-filter-only">
                                @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ ($startDate->month == $m) ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-1 period-input monthly-input yearly-input {{ ($reportType == 'monthly' || $reportType == 'yearly') ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Year</label>
                            <select name="year" class="form-select form-select-sm manual-filter-only">
                                @for($y=date('Y'); $y>=2020; $y--)
                                    <option value="{{ $y }}" {{ ($startDate->year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Custom Date Range -->
                        <div class="col-md-2 period-input custom-input {{ $reportType == 'custom' ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm manual-filter-only" value="{{ $startDate->toDateString() }}">
                        </div>
                        <div class="col-md-2 period-input custom-input {{ $reportType == 'custom' ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm manual-filter-only" value="{{ $endDate->toDateString() }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Outlet</label>
                            <select name="branch_id" class="form-select form-select-sm select2 manual-filter-only">
                                <option value="">All Outlets</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Name</label>
                            <input type="text" name="name" class="form-control form-control-sm manual-filter-only" placeholder="Search name...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-sm manual-filter-only" placeholder="Search phone...">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100 h-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="resetBtn" class="btn btn-outline-danger btn-sm w-100">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="card border-0 shadow-sm overflow-hidden rounded-3">
                <div class="card-body p-0" id="customerReportContainer">
                    <div class="d-flex justify-content-center py-5" id="loader" style="display:none !important;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="tableContent">
                        @include('erp.reports.partials.customer-report-table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('head')
    <style>
        .premium-table { font-size: 0.85rem; }
        .extra-small { font-size: 0.7rem; }
        .cursor-pointer { cursor: pointer; transition: all 0.2s; }
        .cursor-pointer:hover { background-color: #f1f5f9 !important; transform: scale(1.002); }
        .bg-light { background-color: #f8fafc !important; }
        .ajax-filter-input, .ajax-filter-select { transition: border-color 0.2s; }
        .ajax-filter-input:focus, .ajax-filter-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    </style>
    @endpush

    @push('scripts')
    <script>
        $(document).ready(function() {
            let timeout = null;

            function fetchReport(url = null) {
                $('#loader').attr('style', 'display: flex !important;');
                $('#tableContent').css('opacity', '0.5');

                if (!url) {
                    const formData = $('#customerFilterForm').serialize();
                    url = window.location.pathname + '?' + formData;
                }

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#tableContent').html(response).css('opacity', '1');
                        $('#loader').attr('style', 'display: none !important;');
                        window.history.pushState(null, '', url);
                    },
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr);
                        alert('Error loading report. Please try again.');
                        $('#loader').attr('style', 'display: none !important;');
                        $('#tableContent').css('opacity', '1');
                    }
                });
            }

            // Initial setup for visible inputs
            function initPeriodInputs() {
                const type = $('#reportTypeSelector').val();
                $('.period-input').addClass('d-none').find('input, select').attr('disabled', true);
                
                if (type === 'daily') {
                    $('.daily-input').removeClass('d-none').find('input, select').attr('disabled', false);
                } else if (type === 'monthly') {
                    $('.monthly-input').removeClass('d-none').find('input, select').attr('disabled', false);
                } else if (type === 'yearly') {
                    $('.yearly-input').removeClass('d-none').find('input, select').attr('disabled', false);
                } else if (type === 'custom') {
                    $('.custom-input').removeClass('d-none').find('input, select').attr('disabled', false);
                }
            }
            
            initPeriodInputs();

            // Handle Period Type change (UI VISIBILITY ONLY - NO FETCH)
            $(document).on('change', '#reportTypeSelector', function(e) {
                e.preventDefault();
                e.stopPropagation();
                initPeriodInputs();
                // We intentionally DO NOT call fetchReport() here
            });

            // Handle form submission (Filter button) - THE ONLY WAY TO FILTER
            $('#customerFilterForm').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                fetchReport();
            });

            // Pagination
            $(document).on('click', '.customer-pagination a', function(e) {
                e.preventDefault();
                fetchReport($(this).attr('href'));
                $('html, body').animate({ scrollTop: $(".card").offset().top - 100 }, 200);
            });

            // Reset Button
            $('#resetBtn').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#customerFilterForm')[0].reset();
                $('.select2').val('').trigger('change.select2'); // Trigger select2 specifically
                initPeriodInputs();
                fetchReport();
            });
        });
    </script>
    @endpush
@endsection
