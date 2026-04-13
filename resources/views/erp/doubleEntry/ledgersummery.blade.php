@extends('erp.master')

@section('title', 'Ledger Summary')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <!-- Premium Header (Glass Style) -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Accounting & Finance</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">General Ledger Summary</h4>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                            Consolidated Report
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    
                    <button class="btn btn-create-premium text-nowrap" onclick="exportLedger()">
                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Filters (Matching productStockList UI) -->
            <div class="premium-card mb-3 shadow-sm">
                <div class="card-body p-3">
                    <form action="{{ route('ledger.index') }}" method="GET" id="filterForm" autocomplete="off">
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
                            <!-- Field Blocks (Daily, Monthly, Yearly) -->
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') ?: $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2 report-field daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') ?: $endDate->format('Y-m-d') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-2 report-field monthly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Month</label>
                                <select name="month" class="form-select form-select-sm select2-simple">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly Fields -->
                            <div class="col-md-2 report-field monthly-group yearly-group d-none">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                <select name="year" class="form-select form-select-sm select2-simple">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Account Filter</label>
                                <select class="form-select form-select-sm select2-simple" name="account_id" data-placeholder="All Chart Accounts">
                                    <option value="">All Chart Accounts</option>
                                    @foreach($chartAccounts as $account)
                                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ $account->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select class="form-select form-select-sm select2-simple" name="account_type" data-placeholder="All Categories">
                                    <option value="">All Categories</option>
                                    <option value="Asset" {{ request('account_type') == 'Asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="Liability" {{ request('account_type') == 'Liability' ? 'selected' : '' }}>Liability</option>
                                    <option value="Income" {{ request('account_type') == 'Income' ? 'selected' : '' }}>Income</option>
                                    <option value="Revenue" {{ request('account_type') == 'Revenue' ? 'selected' : '' }}>Revenue</option>
                                    <option value="Expense" {{ request('account_type') == 'Expense' ? 'selected' : '' }}>Expense</option>
                                    <option value="Equity" {{ request('account_type') == 'Equity' ? 'selected' : '' }}>Equity</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Search Keywords</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Voucher # or Account..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="card-footer bg-light border-top p-3 mt-4 mx-n3 mb-n3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3 shadow-sm no-loader" onclick="exportExcel()">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm no-loader" onclick="exportLedger()">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 shadow-sm no-loader" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('ledger.index') }}" class="btn btn-light border px-4 fw-bold text-muted justify-content-center" style="height: 42px; display: flex; align-items: center;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </a>
                                    <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Bar (Premium Design) -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="premium-card bg-primary bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-primary text-white rounded-3 p-2">
                                <i class="fas fa-arrow-down fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-primary opacity-75">Total Debits</div>
                                <div class="h5 mb-0 fw-bold text-primary">৳{{ number_format($totalDebits, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card bg-success bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-success text-white rounded-3 p-2">
                                <i class="fas fa-arrow-up fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-success opacity-75">Total Credits</div>
                                <div class="h5 mb-0 fw-bold text-success">৳{{ number_format($totalCredits, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    @php $net = $totalDebits - $totalCredits; @endphp
                    <div class="premium-card bg-info bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-info text-white rounded-3 p-2">
                                <i class="fas fa-balance-scale fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-info opacity-75">Net Position</div>
                                <div class="h5 mb-0 fw-bold text-info">
                                    ৳{{ number_format(abs($net), 2) }} 
                                    <small class="fw-normal">({{ $net >= 0 ? 'Dr' : 'Cr' }})</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card bg-dark bg-opacity-10 border-0">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="icon-box me-3 bg-dark text-white rounded-3 p-2">
                                <i class="fas fa-receipt fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-uppercase small fw-bold text-dark opacity-75">Total Entries</div>
                                <div class="h5 mb-0 fw-bold text-dark">{{ $totalEntries }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list me-2 text-primary"></i>Ledger Data List</h6>
                <div class="search-wrapper-premium" style="width: 300px;">
                    <input type="text" id="tableSearch" class="form-control rounded-pill search-input-premium table-search-input" placeholder="Quick find in this registry...">
                    <i class="fas fa-search search-icon-premium"></i>
                </div>
            </div>

            <!-- Ledger Table (Premium Style) -->
            <div class="premium-card shadow-sm mb-5">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table reporting-table mb-0 align-middle" id="ledgerTable">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Voucher Details</th>
                                    <th>Account Name</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th class="text-end">Position</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledgerEntries as $entry)
                                    <tr>
                                        <td class="ps-4 text-dark fw-500">{{ \Carbon\Carbon::parse($entry->journal->entry_date)->format('d M, Y') }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-light text-dark border mb-1 align-self-start py-1 px-2 fw-normal">{{ $entry->journal->voucher_no }}</span>
                                                <span class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $entry->journal->description }}">{{ $entry->journal->description }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary mb-0">{{ $entry->chartOfAccount->name }}</div>
                                            <div class="text-muted small fs-xs text-uppercase">{{ $entry->chartOfAccount->code }}</div>
                                        </td>
                                        <td class="text-end">
                                            @if($entry->debit > 0)
                                                <span class="text-danger fw-bold">৳{{ number_format($entry->debit, 2) }}</span>
                                            @else
                                                <span class="text-muted opacity-50">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($entry->credit > 0)
                                                <span class="text-success fw-bold">৳{{ number_format($entry->credit, 2) }}</span>
                                            @else
                                                <span class="text-muted opacity-50">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">
                                            @php $bal = $entry->debit - $entry->credit; @endphp
                                            <span class="{{ $bal >= 0 ? 'text-primary' : 'text-danger' }}">
                                                ৳{{ number_format(abs($bal), 2) }}
                                                <small class="fw-normal text-muted fs-xs">{{ $bal >= 0 ? 'Dr' : 'Cr' }}</small>
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('journal.show', $entry->journal_id) }}" class="action-circle bg-light" title="View Voucher">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>
                                                <a href="{{ route('ledger.account', $entry->chart_of_account_id) }}" class="action-circle bg-light" title="View Full Account Ledger">
                                                    <i class="fas fa-book text-info"></i>
                                                </a>
                                                <form action="{{ route('journal.destroy', $entry->journal_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this journal?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-circle bg-light border-0" title="Delete Journal">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-5">
                                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                    <i class="fas fa-search-dollar fa-2x text-muted"></i>
                                                </div>
                                                <h5 class="text-dark fw-bold">No Records Found</h5>
                                                <p class="text-muted mx-auto" style="max-width: 300px;">Adjust your filters or date range to find the transactions you're looking for.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Correct Pagination UI -->
                @if($ledgerEntries->hasPages())
                    <div class="card-footer bg-white border-top-0 py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="text-muted small mb-0">Displaying {{ $ledgerEntries->firstItem() }} to {{ $ledgerEntries->lastItem() }} of {{ $ledgerEntries->total() }} entries</p>
                            {{ $ledgerEntries->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <style>
        /* Extra styles if not in premium-theme.css */
        .fw-500 { font-weight: 500; }
        .fw-600 { font-weight: 600; }
        .btn-white { background: #fff; border: none; }
        .btn-white:hover { background: #f8f9fc; }
        .fs-xs { font-size: 0.7rem; }
        
        @media print {
            .sidebar, #sidebar, .main-content > .header, .navbar, .glass-header .breadcrumb, .sticky-top, .btn-group, .card-footer, form, .btn-create-premium, #mainContent > .header { 
                display: none !important; 
            }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .card, .premium-card { border: none !important; box-shadow: none !important; background: transparent !important; }
            body { background: white !important; }
            .container-fluid { padding: 0 !important; }
            .table { width: 100% !important; border: 1px solid #ddd !important; }
            .glass-header { padding: 0 !important; border: none !important; margin-bottom: 20px !important; }
        }
    </style>
    <script>
        $(document).ready(function() {
            // Live Search Logic
            let searchTimeout;
            $('#tableSearch').on('input', function() {
                clearTimeout(searchTimeout);
                const value = $(this).val().toLowerCase();
                searchTimeout = setTimeout(function() {
                    $("#ledgerTable tbody tr").filter(function() {
                        const text = $(this).text().toLowerCase();
                        $(this).toggle(text.indexOf(value) > -1);
                    });
                }, 300);
            });

            // Handle Report Toggles (Daily, Monthly, Yearly)
            function toggleDateGroups() {
                const type = $('.report-type-radio:checked').val() || 'daily';
                $('.report-field').addClass('d-none');
                
                if (type === 'daily') {
                    $('.daily-group').removeClass('d-none');
                } else if (type === 'monthly') {
                    $('.monthly-group').removeClass('d-none');
                } else if (type === 'yearly') {
                    $('.yearly-group').removeClass('d-none');
                }
            }

            $('.report-type-radio').on('change', function() {
                toggleDateGroups();
            });

            // Initial setup run
            toggleDateGroups();
        });

        function exportLedger() {
            const url = new URL(window.location);
            url.searchParams.set('export', 'pdf');
            window.open(url.toString(), '_blank');
        }

        function exportExcel() {
            const url = new URL(window.location);
            url.searchParams.set('export', 'excel');
            window.open(url.toString(), '_blank');
        }
    </script>
    @endpush
@endsection