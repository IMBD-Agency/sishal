@extends('erp.master')

@section('title', 'Order Return List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
            .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
            .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
            .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
            .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
            .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
            .bg-purple-soft { background-color: rgba(111, 66, 193, 0.1); }
            .text-purple { color: #6f42c1 !important; }
            
            .transition-all { transition: all 0.2s ease-in-out; }
            #returnTable tbody tr:hover { 
                background-color: #f8faff !important;
                box-shadow: inset 0 0 0 9999px #f8faff;
            }
            .x-small { font-size: 0.7rem; }
            .avatar-sm { width: 32px; height: 32px; font-size: 0.85rem; }
            .form-select, .form-control { border-color: #f1f3f5; border-radius: 8px; }
            .form-select:focus, .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
            #returnTable thead th { letter-spacing: 0.05em; border-bottom: 2px solid #f8f9fa; }
            .filter-card { transition: all 0.3s ease; }
            
            /* Quick Filter Styles */
            .quick-filter-btn {
                background-color: #fff;
                color: #6c757d;
                border: 1px solid #e9ecef;
                padding: 0.4rem 1.25rem;
                font-size: 0.85rem;
                font-weight: 500;
                transition: all 0.2s;
            }
            .quick-filter-btn:hover {
                background-color: #f8f9fa;
                color: #0d6efd;
            }
            .btn-check:checked + .quick-filter-btn {
                background-color: #0d6efd !important;
                color: #fff !important;
                border-color: #0d6efd !important;
                box-shadow: 0 4px 6px rgba(13, 110, 253, 0.15);
            }
            .btn-group .btn-check:first-child + .quick-filter-btn {
                border-top-left-radius: 8px;
                border-bottom-left-radius: 8px;
            }
            .btn-group .btn-check:last-child + .quick-filter-btn {
                border-top-right-radius: 8px;
                border-bottom-right-radius: 8px;
            }
        </style>

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order Management</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Order Return List</h2>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('orderReturn.create') }}" class="btn btn-primary btn-sm fw-bold px-3">
                            <i class="fas fa-plus me-1"></i>CREATE RETURN
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold">
                            <i class="fas fa-print me-1"></i>PRINT
                        </button>
                        <a href="{{ route('orderReturn.export.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm fw-bold export-link-pdf">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                        <a href="{{ route('orderReturn.export.excel', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold export-link-excel">
                            <i class="fas fa-file-excel me-1"></i>EXCEL
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Filter Section -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-3">
                    <form id="filterForm" action="{{ route('orderReturn.list') }}" method="GET" autocomplete="off">
                        <!-- Report Type Radios -->
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ request('report_type', 'daily') == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ request('report_type') == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check custom-radio">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ request('report_type') == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly</label>
                            </div>
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Keywords</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Order ID, Customer..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">Select an option</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Daily Fields -->
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-4 report-field monthly-group d-none">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Month</label>
                                        <select name="month" class="form-select form-select-sm">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                        <select name="year" class="form-select form-select-sm">
                                            @foreach(range(date('Y'), date('Y') - 10) as $y)
                                                <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-2 report-field yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                <select name="year" class="form-select form-select-sm">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-auto ms-auto">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fas fa-filter me-1"></i>APPLY
                                    </button>
                                    <a href="{{ route('orderReturn.list') }}" class="btn btn-light border btn-sm px-4 fw-bold shadow-sm">
                                        <i class="fas fa-undo me-1"></i>RESET
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden" id="report-content-area">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">Order Return Logs</h5>
                            <p class="text-muted x-small mb-0">History of e-commerce product returns.</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold" id="total-records-badge">
                                {{ $returns->total() }} Records
                            </div>
                        </div>
                    </div>
                </div>
                @include('erp.orderReturn.orderreturnlist_partial')
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <form id="statusForm">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="fw-bold">Update Return Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 pb-4">
                        <input type="hidden" name="id" id="modalId">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current State</label>
                            <input type="text" class="form-control bg-light border-0" id="currentStatus" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New State</label>
                            <select class="form-select border-2" name="status" id="newStatus" required>
                                <option value="pending">Keep as Pending</option>
                                <option value="approved">Approve Return</option>
                                <option value="rejected">Reject Return</option>
                                <option value="processed">Complete Process (Restocks & Refund)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="modalAccountWrapper" style="display:none;">
                            <label class="form-label fw-semibold">Deduct From Account <span class="text-danger">*</span></label>
                            <select class="form-select border-2" name="account_id" id="modalAccountId">
                                <option value="">Select Financial Account</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->provider_name ?? ucfirst($account->type) }} ({{ number_format($account->balance, 2) }}৳)</option>
                                @endforeach
                            </select>
                            <div class="form-text mt-1 text-muted">Money will be deducted when you confirm.</div>
                        </div>
                        <div class="form-text text-danger mt-2 mb-3" id="stockWarning" style="display:none;">
                            <i class="fas fa-exclamation-triangle me-1"></i> Marking as Processed will increment inventory levels!
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control" name="notes" id="statusNotes" rows="3" placeholder="Explain status change..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Confirm Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function toggleReportFields() {
        const type = $('.report-type-radio:checked').val();
        $('.report-field').addClass('d-none');
        $('.' + type + '-group').removeClass('d-none');
    }

    function refreshOrderReturn() {
        const form = $('#filterForm');
        const container = $('#report-content-area');
        
        container.css('opacity', '0.5');
        
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            success: function(response) {
                // Find and update only the table container and pagination from the partial
                const $response = $(response);
                $('#order-return-table-container').replaceWith($response.filter('#order-return-table-container').length ? $response.filter('#order-return-table-container') : $response.find('#order-return-table-container'));
                $('#order-return-pagination').replaceWith($response.filter('#order-return-pagination').length ? $response.filter('#order-return-pagination') : $response.find('#order-return-pagination'));
                
                // Update record count if badge exists
                const totalText = $response.find('.text-muted').first().text();
                const totalMatch = totalText.match(/of (\d+) returns/);
                if (totalMatch) {
                    $('#total-records-badge').text(totalMatch[1] + ' Records');
                }

                // Update Export URLs with current form data
                const queryStr = form.serialize();
                const pdfBaseUrl = "{{ route('orderReturn.export.pdf') }}";
                const excelBaseUrl = "{{ route('orderReturn.export.excel') }}";
                $('.export-link-pdf').attr('href', pdfBaseUrl + '?' + queryStr);
                $('.export-link-excel').attr('href', excelBaseUrl + '?' + queryStr);

                container.css('opacity', '1');
                
                // Re-bind status badge click
                bindStatusBadges();
            },
            error: function() {
                container.css('opacity', '1');
                alert('Failed to load return data.');
            }
        });
    }

    let currentRefundType = '';

    function bindStatusBadges() {
        $('.status-badge').off('click').on('click', function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            currentRefundType = $(this).data('refund-type');
            
            $('#modalId').val(id);
            $('#currentStatus').val(status.charAt(0).toUpperCase() + status.slice(1));
            $('#newStatus').val(status);
            $('#statusNotes').val('');
            
            // Clean state toggle
            $('#stockWarning').hide();
            $('#modalAccountWrapper').hide();
            
            $('#statusModal').modal('show');
        });
    }

    $(document).ready(function() {
        toggleReportFields();
        bindStatusBadges();

        $('.report-type-radio').change(function() {
            const type = $(this).val();
            if (type === 'daily') {
                const today = new Date().toISOString().split('T')[0];
                $('#start_date').val(today);
                $('#end_date').val(today);
            }
            toggleReportFields();
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            refreshOrderReturn();
        });

        // Handle pagination links
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            const container = $('#report-content-area');
            container.css('opacity', '0.5');
            
            $.ajax({
                url: url,
                success: function(response) {
                    const $response = $(response);
                    $('#order-return-table-container').replaceWith($response.filter('#order-return-table-container').length ? $response.filter('#order-return-table-container') : $response.find('#order-return-table-container'));
                    $('#order-return-pagination').replaceWith($response.filter('#order-return-pagination').length ? $response.filter('#order-return-pagination') : $response.find('#order-return-pagination'));
                    container.css('opacity', '1');
                    bindStatusBadges();
                }
            });
        });

        $('#newStatus').on('change', function() {
            const status = $(this).val();
            const showStockWarning = (status === 'processed');
            const showAccountSelect = (status === 'processed' && currentRefundType !== 'credit');
            
            $('#stockWarning').toggle(showStockWarning);
            $('#modalAccountWrapper').toggle(showAccountSelect);
            $('#modalAccountId').prop('required', showAccountSelect);
        });

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#modalId').val();
            const $btn = $(this).find('button[type="submit"]');
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

            $.ajax({
                url: `/erp/order-return/${id}/update-status`,
                method: 'POST',
                data: $(this).serialize() + '&_token={{ csrf_token() }}',
                success: (res) => {
                    $('#statusModal').modal('hide');
                    location.reload();
                },
                error: (err) => alert(err.responseJSON?.message || 'Error occurred'),
                complete: () => $btn.prop('disabled', false).text('Confirm Change')
            });
        });

        // No-op for deleted IDs
    });
    </script>
    @endpush
@endsection
