@extends('erp.master')

@section('title', 'Supplier Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Header Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Supplier Summary Report</h4>
                    <p class="text-muted small mb-0">Track purchases, payments, and balances across all suppliers.</p>
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

            <!-- Enhanced Filters -->
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body p-3">
                    <form id="supplierFilterForm" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Period Type</label>
                            <select name="report_type" id="reportTypeSelector" class="form-select form-select-sm period-toggle-only">
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily Range</option>
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ $reportType == 'custom' ? 'selected' : '' }}>Custom Dates</option>
                            </select>
                        </div>

                        <!-- Daily Range -->
                        <div class="col-md-2 period-input daily-input {{ $reportType == 'daily' ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm manual-filter-only" value="{{ $startDate->toDateString() }}">
                        </div>

                        <!-- Monthly -->
                        <div class="col-md-2 period-input monthly-input {{ $reportType == 'monthly' ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Select Month</label>
                            <select name="month" class="form-select form-select-sm manual-filter-only">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $startDate->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Yearly -->
                        <div class="col-md-2 period-input yearly-input {{ ($reportType == 'monthly' || $reportType == 'yearly') ? '' : 'd-none' }}">
                            <label class="form-label small fw-bold">Select Year</label>
                            <select name="year" class="form-select form-select-sm manual-filter-only">
                                @foreach(range(date('Y'), date('Y')-5) as $y)
                                    <option value="{{ $y }}" {{ $startDate->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Custom Dates -->
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
                            <select name="branch_id" class="form-select form-select-sm manual-filter-only">
                                <option value="">All Outlets</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <button type="button" id="resetBtn" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Container -->
            <div class="card border-0 shadow-sm overflow-hidden position-relative" style="min-height: 400px;">
                <!-- Loader -->
                <div id="loader" class="position-absolute top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(255,255,255,0.7); z-index: 10;">
                    <div class="spinner-border text-success" role="status"></div>
                </div>
                
                <div id="tableContent">
                    @include('erp.reports.partials.supplier-report-table')
                </div>
            </div>
        </div>
    </div>

    <style>
        .cursor-pointer { cursor: pointer; transition: all 0.2s; }
        .cursor-pointer:hover { background-color: #f8fafc !important; transform: scale(1.002); }
        .extra-small { font-size: 0.7rem; }
        
        @media print {
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .sidebar, .header, .card:first-child, .btn, .d-flex.gap-2 { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
        }
    </style>

    @push('scripts')
    <script>
        $(document).ready(function() {
            function fetchReport() {
                $('#loader').removeClass('d-none').addClass('d-flex');
                $('#tableContent').css('opacity', '0.5');

                const formData = $('#supplierFilterForm').serialize();
                $.ajax({
                    url: "{{ route('reports.supplier-summary') }}",
                    type: "GET",
                    data: formData,
                    success: function(response) {
                        $('#tableContent').html(response);
                        $('#loader').removeClass('d-flex').addClass('d-none');
                        $('#tableContent').css('opacity', '1');
                    }
                });
            }

            $('#reportTypeSelector').on('change', function() {
                const type = $(this).val();
                $('.period-input').addClass('d-none');
                $(`.${type}-input`).removeClass('d-none');
                if (type === 'monthly' || type === 'yearly') {
                    $('.yearly-input').removeClass('d-none');
                }
            });

            $('#supplierFilterForm').on('submit', function(e) {
                e.preventDefault();
                fetchReport();
            });

            $('#resetBtn').on('click', function() {
                $('#supplierFilterForm')[0].reset();
                $('.period-input').addClass('d-none');
                $('.daily-input').removeClass('d-none');
                fetchReport();
            });
        });
    </script>
    @endpush
@endsection

