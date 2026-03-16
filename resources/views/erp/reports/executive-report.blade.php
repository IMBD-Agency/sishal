@extends('erp.master')

@section('title', 'Executive Business Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">Executive Business Performance</h3>
                    <p class="text-muted small mb-0">Full business overview and profitability analysis</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger btn-sm px-3 rounded-pill border-2 fw-bold">
                        <i class="fas fa-file-pdf me-2"></i>PDF Report
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success btn-sm px-3 rounded-pill border-2 fw-bold">
                        <i class="fas fa-file-excel me-2"></i>Excel Export
                    </a>
                </div>
            </div>

            <!-- Premium Filter Card -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="filterForm" action="{{ route('reports.executive') }}" method="GET" autocomplete="off">
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
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch / Shop</label>
                                <select name="branch_id" class="form-select form-select-sm select2-simple filter-input">
                                    <option value="">Global (All Branches)</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Field Blocks (Daily) -->
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm filter-input" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm filter-input" value="{{ $endDate->format('Y-m-d') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-2 report-field monthly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Month</label>
                                <select name="month" class="form-select form-select-sm select2-simple filter-input">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ ($month ?? date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-2 report-field monthly-group yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                <select name="year" class="form-select form-select-sm select2-simple filter-input">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill text-white fw-bold shadow-sm filter-btn">
                                        <i class="fas fa-sync-alt me-1"></i>Update
                                    </button>
                                    <a href="{{ route('reports.executive') }}" class="btn btn-light border btn-sm flex-fill fw-bold shadow-sm filter-btn">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dynamic Content Area -->
            <div id="report-content-area">
                @include('erp.reports.executive-report-partial')
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function toggleReportFields() {
            var reportType = $('.report-type-radio:checked').val();
            $('.report-field').addClass('d-none');
            
            if (reportType === 'daily') {
                $('.daily-group').removeClass('d-none');
            } else if (reportType === 'monthly') {
                $('.monthly-group').removeClass('d-none');
            } else if (reportType === 'yearly') {
                $('.yearly-group').removeClass('d-none');
            }
        }

        function refreshReport() {
            const form = $('#filterForm');
            const container = $('#report-content-area');
            
            // Show loading state
            container.css('opacity', '0.5');
            
            $.ajax({
                url: form.attr('action'),
                method: 'GET',
                data: form.serialize(),
                success: function(response) {
                    container.html(response);
                    container.css('opacity', '1');
                    
                    // Update the Excel/PDF links to reflect new filters
                    const queryParams = form.serialize();
                    $('.gap-2 a').each(function() {
                        const baseUrl = $(this).attr('href').split('?')[0];
                        const exportType = $(this).text().toLowerCase().includes('pdf') ? 'pdf' : 'excel';
                        $(this).attr('href', baseUrl + '?' + queryParams + '&export=' + exportType);
                    });
                },
                error: function() {
                    container.css('opacity', '1');
                    alert('Failed to load report data.');
                }
            });
        }

        toggleReportFields();
        
        $('.report-type-radio').change(function() {
            const type = $(this).val();
            if (type === 'daily') {
                const today = new Date().toISOString().split('T')[0];
                $('#start_date').val(today);
                $('#end_date').val(today);
            }
            toggleReportFields();
            // Removed refreshReport() call - only update on button click now
        });

        // Removed auto-refresh on dropdown change
        // $('.filter-input').change(function() {
        //     refreshReport();
        // });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            refreshReport();
        });
    });
</script>
@endpush
