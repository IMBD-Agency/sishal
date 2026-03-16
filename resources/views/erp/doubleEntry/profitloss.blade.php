@extends('erp.master')

@section('title', 'Profit & Loss Statement')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <!-- Header Section -->
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Profit & Loss Statement</h2>
                    <p class="text-muted mb-0">Double-entry accounting performance overview</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-2 shadow-sm bg-white" onclick="printProfitLoss()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li><a class="dropdown-item py-2" href="#" onclick="exportPDF()"><i class="far fa-file-pdf me-2 text-danger"></i> Export as PDF</a></li>
                            <li><a class="dropdown-item py-2" href="#" onclick="exportExcel()"><i class="far fa-file-excel me-2 text-success"></i> Export as Excel</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filter Card -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="profitLossFilterForm" action="{{ route('profitLoss.index') }}" method="GET" autocomplete="off">
                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ ($reportType ?? 'daily') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Report</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ ($reportType ?? 'daily') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ ($reportType ?? 'daily') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly</label>
                            </div>
                        </div>

                        <div class="row g-2 align-items-end">
                            <!-- Field Blocks (Daily) -->
                            <div class="col-md-3 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') ?: $startDate }}">
                            </div>
                            <div class="col-md-3 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') ?: $endDate }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-3 report-field monthly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Month</label>
                                <select name="month" class="form-select form-select-sm select2-simple">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-3 report-field monthly-group yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                <select name="year" class="form-select form-select-sm select2-simple">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill text-white fw-bold shadow-sm filter-btn">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                    <a href="{{ route('profitLoss.index') }}" class="btn btn-light border btn-sm flex-fill fw-bold shadow-sm filter-btn">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Premium Summary Widgets -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" 
                         style="background: linear-gradient(135deg, #0f172a 0%, #334155 100%);">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                                    <i class="fas fa-coins fa-lg"></i>
                                </div>
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill">Revenue</span>
                            </div>
                            <h3 class="fw-bold mb-1">৳{{ $profitLossData['totals']['revenue_formatted'] ?? '0.00' }}</h3>
                            <p class="text-white-50 small mb-0">Total Income generated</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" 
                         style="background: linear-gradient(135deg, #be123c 0%, #fb7185 100%);">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                                    <i class="fas fa-wallet fa-lg"></i>
                                </div>
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill">Expenses</span>
                            </div>
                            <h3 class="fw-bold mb-1">৳{{ $profitLossData['totals']['expenses_formatted'] ?? '0.00' }}</h3>
                            <p class="text-white-50 small mb-0">Operational costs incurred</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    @php
                        $netProfit = $profitLossData['totals']['net_profit'] ?? 0;
                        $profitGradient = $netProfit >= 0 
                            ? 'linear-gradient(135deg, #047857 0%, #10b981 100%)' 
                            : 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)';
                    @endphp
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" style="background: {{ $profitGradient }};">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                                    <i class="fas fa-chart-line fa-lg"></i>
                                </div>
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill">Net Result</span>
                            </div>
                            <h3 class="fw-bold mb-1">৳{{ $profitLossData['totals']['net_profit_formatted'] ?? '0.00' }}</h3>
                            <p class="text-white-50 small mb-0">{{ $netProfit >= 0 ? 'Surplus for period' : 'Deficit for period' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100" 
                         style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-white bg-opacity-10 p-2 rounded-3">
                                    <i class="fas fa-percentage fa-lg"></i>
                                </div>
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill">Margin</span>
                            </div>
                            <h3 class="fw-bold mb-1">{{ $profitLossData['totals']['profit_percentage'] ?? '0.0' }}%</h3>
                            <p class="text-white-50 small mb-0">Revenue to profit ratio</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statement Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Financial Statement</h4>
                        <span class="text-muted small">Comprehensive breakdown of all accounts</span>
                    </div>
                    <div class="badge bg-light text-dark border px-3 py-2 rounded-3">
                        <i class="far fa-clock me-1"></i>
                        {{ date('d M Y', strtotime($startDate)) }} — {{ date('d M Y', strtotime($endDate)) }}
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="profitLossTable">
                            <thead>
                                <tr class="bg-light border-bottom">
                                    <th class="ps-4 py-3 text-uppercase small fw-bold text-muted" style="width: 15%;">Account Code</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted" style="width: 50%;">Account Name</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted text-end" style="width: 20%;">Amount</th>
                                    <th class="py-3 text-uppercase small fw-bold text-muted text-center pe-4" style="width: 15%;">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- REVENUE SECTION -->
                                <tr class="bg-opacity-10" style="background-color: #f1f5f9;">
                                    <td colspan="4" class="ps-4 py-3 fw-bold text-dark border-bottom border-top">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success p-1 rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                            INCOME / REVENUE ACCOUNTS
                                        </div>
                                    </td>
                                </tr>
                                @forelse($profitLossData['revenue'] ?? [] as $revenue)
                                    <tr class="border-bottom">
                                        <td class="ps-4">
                                            <span class="badge bg-light text-dark border fw-semibold px-2">#{{ $revenue['code'] }}</span>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $revenue['name'] }}</td>
                                        <td class="text-end fw-bold text-success">
                                            ৳{{ $revenue['formatted_balance'] }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3">Income</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5 bg-white">
                                            <div class="mb-2"><i class="fas fa-box-open fa-3x opacity-25"></i></div>
                                            No revenue accounts found for this period
                                        </td>
                                    </tr>
                                @endforelse
                                <tr class="bg-light bg-opacity-50">
                                    <td colspan="2" class="ps-4 py-3 fw-bold text-dark">Total Consolidated Income</td>
                                    <td class="text-end py-3 fw-bold text-dark fs-5">৳{{ $profitLossData['totals']['revenue_formatted'] ?? '0.00' }}</td>
                                    <td class="pe-4"></td>
                                </tr>

                                <!-- EXPENSES SECTION -->
                                <tr class="bg-opacity-10" style="background-color: #f1f5f9;">
                                    <td colspan="4" class="ps-4 py-3 fw-bold text-dark border-bottom border-top">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-danger p-1 rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                            EXPENDITURE / OPERATING ACCOUNTS
                                        </div>
                                    </td>
                                </tr>
                                @forelse($profitLossData['expenses'] ?? [] as $expense)
                                    <tr class="border-bottom">
                                        <td class="ps-4">
                                            <span class="badge bg-light text-dark border fw-semibold px-2">#{{ $expense['code'] }}</span>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $expense['name'] }}</td>
                                        <td class="text-end fw-bold text-danger">
                                            ৳{{ $expense['formatted_balance'] }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3">Expense</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5 bg-white">
                                            <div class="mb-2"><i class="fas fa-box-open fa-3x opacity-25"></i></div>
                                            No expense accounts found for this period
                                        </td>
                                    </tr>
                                @endforelse
                                <tr class="bg-light bg-opacity-50">
                                    <td colspan="2" class="ps-4 py-3 fw-bold text-dark">Total Consolidated Expenses</td>
                                    <td class="text-end py-3 fw-bold text-dark fs-5">৳{{ $profitLossData['totals']['expenses_formatted'] ?? '0.00' }}</td>
                                    <td class="pe-4"></td>
                                </tr>

                                <!-- THE BOTTOM LINE -->
                                <tr style="background-color: #0f172a;" class="text-white">
                                    <td colspan="2" class="ps-4 py-4 fw-bold fs-5">
                                        <i class="fas fa-balance-scale me-2 text-info"></i>
                                        THE BOTTOM LINE (NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }})
                                    </td>
                                    <td class="text-end py-4 fw-bold fs-4 {{ $netProfit >= 0 ? 'text-info' : 'text-danger' }}">
                                        ৳{{ $profitLossData['totals']['net_profit_formatted'] ?? '0.00' }}
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="badge bg-white bg-opacity-20 text-white border-0 px-4 py-2 rounded-pill">
                                            {{ $netProfit >= 0 ? 'Net Surplus' : 'Net Deficit' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Profit & Loss</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('profitLoss.index') }}" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modal_start_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="modal_start_date" name="start_date" 
                                   value="{{ $startDate ?? date('Y-m-01') }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="modal_end_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="modal_end_date" name="end_date" 
                                   value="{{ $endDate ?? date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Function to handle report type toggling
            function toggleReportFields() {
                var reportType = $('.report-type-radio:checked').val();
                
                // Hide all groups first
                $('.report-field').addClass('d-none');
                
                // Show relevant group based on selection
                if (reportType === 'daily') {
                    $('.daily-group').removeClass('d-none');
                } else if (reportType === 'monthly') {
                    $('.monthly-group').removeClass('d-none');
                } else if (reportType === 'yearly') {
                    $('.yearly-group').removeClass('d-none');
                }
            }

            // Run on initial load
            toggleReportFields();

            // Run on change
            $('.report-type-radio').change(toggleReportFields);

            // Date range validation
            $('#start_date, #end_date').on('change', function() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                
                if (startDate && endDate && startDate > endDate) {
                    alert('Start date cannot be after end date!');
                    $(this).val('');
                }
            });
        });

        function exportProfitLoss() {
            const startDate = $('#start_date').val() || '{{ $startDate ?? date('Y-m-01') }}';
            const endDate = $('#end_date').val() || '{{ $endDate ?? date('Y-m-d') }}';
            
            const url = new URL(window.location);
            url.searchParams.set('start_date', startDate);
            url.searchParams.set('end_date', endDate);
            url.searchParams.set('export', 'pdf');
            window.open(url.toString(), '_blank');
        }

        function printProfitLoss() {
            window.print();
        }

        function exportPDF() {
            const startDate = $('#start_date').val() || '{{ $startDate ?? date('Y-m-01') }}';
            const endDate = $('#end_date').val() || '{{ $endDate ?? date('Y-m-d') }}';
            
            const url = new URL(window.location);
            url.searchParams.set('start_date', startDate);
            url.searchParams.set('end_date', endDate);
            url.searchParams.set('export', 'pdf');
            window.open(url.toString(), '_blank');
        }

        function exportExcel() {
            const startDate = $('#start_date').val() || '{{ $startDate ?? date('Y-m-01') }}';
            const endDate = $('#end_date').val() || '{{ $endDate ?? date('Y-m-d') }}';
            
            const url = new URL(window.location);
            url.searchParams.set('start_date', startDate);
            url.searchParams.set('end_date', endDate);
            url.searchParams.set('export', 'excel');
            window.open(url.toString(), '_blank');
        }
    </script>
    @endpush
@endsection 