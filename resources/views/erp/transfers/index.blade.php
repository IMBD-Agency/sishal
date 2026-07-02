@extends('erp.master')

@section('title', 'Fund Transfers')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Fund Transfers</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-info text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Fund Transfer History</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle shadow-sm border-0 bg-white" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportData('excel')"><i class="fas fa-file-excel me-2 text-success"></i>Excel Report</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportData('pdf')"><i class="fas fa-file-pdf me-2 text-danger"></i>PDF Report</a></li>
                        </ul>
                    </div>
                    @can('create fund transfers')
                        <a href="{{ route('transfers.create') }}" class="btn btn-create-premium">
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
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="summary-card-premium p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 p-3">
                            <i class="fas fa-exchange-alt fs-4"></i>
                        </div>
                        <div>
                            <div class="small opacity-75 text-uppercase fw-bold">Total Transfers</div>
                            <h4 class="fw-bold mb-0">{{ $transfers->total() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card p-3 d-flex align-items-center gap-3 border-0 shadow-sm">
                        <div class="rounded-circle bg-success-subtle p-3 text-success">
                            <i class="fas fa-money-bill-wave fs-4"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Total Amount</div>
                            <h4 class="fw-bold mb-0 text-dark" id="totalAmountText">{{ number_format($totalTransfers, 2) }}৳</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('transfers.index') }}" id="filterForm">
                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-4">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active"
                                    id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Manual Range</label>
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
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">From Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">To Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 month-field {{ $reportType != 'monthly' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select">
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
                                <select name="year" class="form-select">
                                    <option value="">All Years</option>
                                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                                <select name="branch_id" class="form-select select2-simple">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Account</label>
                                <select name="from_account_id" class="form-select select2-simple">
                                    <option value="">All Accounts</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ request('from_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->provider_name }} - {{ $account->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                                <button type="button" id="resetFilters" class="btn btn-light border" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transfers Table -->
            <div class="card border-0 shadow-sm rounded-4" id="tableContainer">
                @include('erp.transfers.partials.table')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function exportData(format) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        
        let url = '';
        if (format === 'excel') {
            url = "{{ route('transfers.export.excel') }}";
        } else {
            url = "{{ route('transfers.export.pdf') }}";
        }
        
        window.isDownloadNavigation = true;
        window.location.href = url + '?' + params;
        
        // Reset flag after a delay
        setTimeout(() => { window.isDownloadNavigation = false; }, 2000);
    }

    $(document).ready(function () {
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

        // AJAX search helper
        function fetchData(url = "{{ route('transfers.index') }}") {
            const formData = $('#filterForm').serialize();
            $('#tableContainer').css('opacity', '0.5');
            $.ajax({
                url: url,
                data: formData,
                success: function(res) {
                    $('#tableContainer').html(res.html).css('opacity', '1');
                    $('#totalAmountText').text(res.totalAmount);
                }
            });
        }

        // Form submit override
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            fetchData();
        });

        // AJAX Reset
        $('#resetFilters').on('click', function () {
            $('#filterForm')[0].reset();
            $('.select2-simple').val('').trigger('change.select2');

            // Reset report type radio to daily
            $('#dailyReport').prop('checked', true);

            // Set inputs back to today
            const today = new Date().toISOString().split('T')[0];
            $('input[name="start_date"]').val(today);
            $('input[name="end_date"]').val(today);

            $('.date-range-field').removeClass('d-none').show();
            $('.month-field, .year-field').addClass('d-none').hide();

            fetchData();
        });

        // Pagination links AJAX handler
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            fetchData(url);
            window.history.pushState({}, '', url);
        });
    });
</script>
@endpush
