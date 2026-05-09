@extends('erp.master')

@section('title', 'Supplier Ledger')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Header matching Customer Ledger style -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Supplier Ledger Account</h4>
                    @if(isset($supplier))
                        <p class="text-muted small mb-0">{{ $supplier->name }} | {{ $supplier->phone }}</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if(isset($supplier))
                        <a href="{{ route('reports.supplier.ledger', ['id' => $supplier->id, 'export' => 'excel', 'report_type' => $reportType, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('reports.supplier.ledger', ['id' => $supplier->id, 'export' => 'pdf', 'report_type' => $reportType, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filters with Report Type -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('reports.supplier.ledger') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-uppercase text-muted">Supplier</label>
                            <select name="supplier_id" class="form-select form-select-sm select2">
                                <option value="">Select Supplier...</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ (isset($supplier) && $supplier->id == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-uppercase text-muted">Report Type</label>
                            <select name="report_type" class="form-select form-select-sm" id="reportTypeSelect">
                                <option value="all" {{ $reportType == 'all' ? 'selected' : '' }}>All Transactions</option>
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        
                        <!-- Daily/All Date Range -->
                        <div class="col-md-2 date-range-field {{ $reportType == 'monthly' || $reportType == 'yearly' ? 'd-none' : '' }}">
                            <label class="form-label small fw-bold text-uppercase text-muted">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2 date-range-field {{ $reportType == 'monthly' || $reportType == 'yearly' ? 'd-none' : '' }}">
                            <label class="form-label small fw-bold text-uppercase text-muted">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                        
                        <!-- Monthly Fields -->
                        <div class="col-md-2 month-field {{ $reportType != 'monthly' ? 'd-none' : '' }}">
                            <label class="form-label small fw-bold text-uppercase text-muted">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (request('month', date('n')) == $m) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Yearly Fields -->
                        <div class="col-md-2 year-field {{ $reportType != 'monthly' && $reportType != 'yearly' ? 'd-none' : '' }}">
                            <label class="form-label small fw-bold text-uppercase text-muted">Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @foreach(range(date('Y'), date('Y') - 5) as $y)
                                    <option value="{{ $y }}" {{ (request('year', date('Y')) == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.location.href='{{ route('reports.supplier.ledger') }}'" title="Reset">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const reportTypeSelect = document.getElementById('reportTypeSelect');
                    const dateRangeFields = document.querySelectorAll('.date-range-field');
                    const monthField = document.querySelector('.month-field');
                    const yearField = document.querySelector('.year-field');
                    
                    function updateFields() {
                        const reportType = reportTypeSelect.value;
                        
                        if (reportType === 'monthly') {
                            // Show month and year, hide date range
                            dateRangeFields.forEach(f => f.classList.add('d-none'));
                            monthField.classList.remove('d-none');
                            yearField.classList.remove('d-none');
                        } else if (reportType === 'yearly') {
                            // Show only year, hide date range and month
                            dateRangeFields.forEach(f => f.classList.add('d-none'));
                            monthField.classList.add('d-none');
                            yearField.classList.remove('d-none');
                        } else {
                            // All or Daily - show date range, hide month/year
                            dateRangeFields.forEach(f => f.classList.remove('d-none'));
                            monthField.classList.add('d-none');
                            yearField.classList.add('d-none');
                        }
                    }
                    
                    reportTypeSelect.addEventListener('change', updateFields);
                    updateFields(); // Run on page load
                });
            </script>

            @if(isset($supplier))
                @php 
                    $viewType = request('view_type', 'info_wise');
                    
                    // Calculate totals for info wise view
                    $totalPurchase = $transactions->where('type', 'Purchase Bill')->sum('credit');
                    $totalPayment = $transactions->where('type', 'Payment')->sum('debit');
                    $totalReturn = $transactions->where('type', 'Purchase Return')->sum('debit');
                    $totalPaid = $totalPayment + $totalReturn; // Payments + Returns
                    $totalDueAmount = ($openingBalance ?? 0) + $transactions->sum('credit') - $transactions->sum('debit');
                    
                    // Legacy calculations
                    $totalDebit = $transactions->sum('debit'); // Payments
                    $totalCredit = $transactions->sum('credit'); // Purchases
                    $finalBalance = ($openingBalance ?? 0) + ($totalCredit - $totalDebit);
                @endphp

                <!-- Summary Cards matching Customer Ledger -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center p-4">
                                <div class="small text-muted text-uppercase mb-2">Total Purchase (Debit)</div>
                                <h4 class="fw-bold mb-0">Tk. {{ number_format($totalCredit, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body text-center p-4">
                                <div class="small text-muted text-uppercase mb-2">Total Payment (Credit)</div>
                                <h4 class="fw-bold mb-0 text-success">Tk. {{ number_format($totalDebit, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 h-100" style="background-color: {{ $finalBalance > 0 ? '#fef2f2' : '#f0fdf4' }};">
                            <div class="card-body text-center p-4">
                                <div class="small text-muted text-uppercase mb-2">Current Balance</div>
                                <h4 class="fw-bold mb-0 {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    Tk. {{ number_format(abs($finalBalance), 2) }} {{ $finalBalance > 0 ? '(Due)' : '(Adv)' }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Wise Ledger Table matching Customer Ledger -->
                <div class="card border shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead style="background-color: #166534; color: white;">
                                    <tr>
                                        <th class="ps-3 py-3 small fw-bold" style="width: 5%;">SN</th>
                                        <th class="py-3 small fw-bold" style="width: 10%;">Date</th>
                                        <th class="py-3 small fw-bold" style="width: 15%;">Bill/Challan</th>
                                        <th class="py-3 small fw-bold" style="width: 25%;">Particulars</th>
                                        <th class="text-end py-3 small fw-bold" style="width: 12%;">Total</th>
                                        <th class="text-end py-3 small fw-bold" style="width: 10%;">Paid</th>
                                        <th class="text-end pe-3 py-3 small fw-bold" style="width: 23%;">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $sn = 1; 
                                        $runningBalance = $openingBalance ?? 0;
                                    @endphp
                                    
                                    <!-- Opening Balance Row -->
                                    @if($runningBalance != 0)
                                    <tr style="background-color: #f9fafb;">
                                        <td class="ps-3 py-2 small">-</td>
                                        <td class="py-2 small text-muted">-</td>
                                        <td class="py-2 font-monospace extra-small">-</td>
                                        <td class="py-2 small fw-semibold">Opening Balance</td>
                                        <td class="text-end py-2">-</td>
                                        <td class="text-end py-2">-</td>
                                        <td class="text-end pe-3 py-2 fw-bold">
                                            @if($runningBalance > 0)
                                                <span class="text-danger">Due: {{ number_format($runningBalance, 2) }}</span>
                                            @else
                                                <span class="text-primary">Advance: {{ number_format(abs($runningBalance), 2) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif

                                    @foreach($transactions as $txn)
                                        @php 
                                            // Calculate due for this transaction
                                            if($txn['type'] == 'Purchase Bill') {
                                                $txnDue = $txn['credit'];
                                                $txnPaid = 0;
                                                $runningBalance += $txn['credit'];
                                            } elseif($txn['type'] == 'Payment') {
                                                $txnDue = 0;
                                                $txnPaid = $txn['debit'];
                                                $runningBalance -= $txn['debit'];
                                            } elseif($txn['type'] == 'Purchase Return') {
                                                $txnDue = 0;
                                                $txnPaid = $txn['debit'];
                                                $runningBalance -= $txn['debit'];
                                            } else {
                                                $txnDue = $txn['credit'];
                                                $txnPaid = $txn['debit'];
                                                $runningBalance += ($txn['credit'] - $txn['debit']);
                                            }
                                        @endphp
                                        <tr style="{{ $sn % 2 == 0 ? 'background-color: #f9fafb;' : '' }}">
                                            <td class="ps-3 py-2 small">{{ $sn++ }}</td>
                                            <td class="py-2 small">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                                            <td class="py-2 font-monospace extra-small">{{ $txn['reference'] ?? '-' }}</td>
                                            <td class="py-2 small">
                                                <span class="fw-semibold">{{ $txn['type'] }}</span>
                                                @if($txn['note']) <div class="extra-small text-muted">{{ $txn['note'] }}</div> @endif
                                            </td>
                                            <td class="text-end py-2">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                                            <td class="text-end py-2 text-success">{{ $txnPaid > 0 ? number_format($txnPaid, 2) : '-' }}</td>
                                            <td class="text-end pe-3 py-2 fw-bold">
                                                @if($runningBalance > 0)
                                                    <span class="text-danger">Due: {{ number_format($runningBalance, 2) }}</span>
                                                @elseif($runningBalance < 0)
                                                    <span class="text-primary">Advance: {{ number_format(abs($runningBalance), 2) }}</span>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background-color: #166534; color: white; font-weight: bold;">
                                    <tr>
                                        <td colspan="4" class="ps-3 py-3">Total</td>
                                        <td class="text-end py-3">{{ number_format($totalCredit, 2) }}</td>
                                        <td class="text-end py-3">{{ number_format($totalDebit, 2) }}</td>
                                        <td class="text-end pe-3 py-3">
                                            @if($finalBalance > 0)
                                                <span>Due: {{ number_format($finalBalance, 2) }}</span>
                                            @elseif($finalBalance < 0)
                                                <span>Advance: {{ number_format(abs($finalBalance), 2) }}</span>
                                            @else
                                                <span>0.00</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5 border rounded bg-light">
                    <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                    <h5 class="text-muted">Select a supplier account to view statement</h5>
                </div>
            @endif
        </div>
    </div>

    <style>
        .table-sm td, .table-sm th { padding: 0.5rem 0.5rem; }
        .bg-light { background-color: #f8fafc !important; }
        .extra-small { font-size: 0.7rem; }
        .italic { font-style: italic; }
        .form-label-premium { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; display: block; }
        .text-primary { color: #3b82f6 !important; }
        .text-danger { color: #dc2626 !important; }
    </style>
@endsection
