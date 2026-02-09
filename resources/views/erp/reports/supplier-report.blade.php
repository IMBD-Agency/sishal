@extends('erp.master')

@section('title', 'Supplier Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Supplier Summary Report</h4>
                    <p class="text-muted small mb-0">Statement for {{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- Clean KPI Metrics -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border shadow-sm">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Total Purchase</div>
                            <h4 class="fw-bold mb-0 text-dark">Tk. {{ number_format($totals['purchase'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Total Paid</div>
                            <h4 class="fw-bold mb-0 text-success">Tk. {{ number_format($totals['paid'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Total Return</div>
                            <h4 class="fw-bold mb-0 text-warning">Tk. {{ number_format($totals['return'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm bg-light">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Total Due</div>
                            <h4 class="fw-bold mb-0 text-danger">Tk. {{ number_format($totals['due'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Standard Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('reports.supplier-summary') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Type</label>
                            <select name="report_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Outlet</label>
                            <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Outlets</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($reportType == 'daily')
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->toDateString() }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->toDateString() }}">
                        </div>
                        @else
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Select Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @foreach(range(date('Y'), date('Y')-5) as $y)
                                    <option value="{{ $y }}" {{ $startDate->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Simple Data Table -->
            <div class="card border shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light shadow-none">
                                <tr>
                                    <th class="ps-3 py-3 text-muted small fw-bold text-uppercase">Supplier</th>
                                    <th class="py-3 text-muted small fw-bold text-uppercase">Outlet</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase">Opening</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase">Purchase</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase">Paid</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase">Return</th>
                                    <th class="text-end pe-3 py-3 text-muted small fw-bold text-uppercase">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $s)
                                    <tr onclick="window.location='{{ route('reports.supplier.ledger', $s->id) }}'" class="cursor-pointer">
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $s->name }}</div>
                                            <div class="extra-small text-muted">{{ $s->mobile }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $s->outlet }}</span></td>
                                        <td class="text-end">{{ number_format($s->opening, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($s->purchase, 2) }}</td>
                                        <td class="text-end text-success">{{ number_format($s->paid, 2) }}</td>
                                        <td class="text-end text-warning">{{ number_format($s->return, 2) }}</td>
                                        <td class="text-end pe-3 fw-bold {{ $s->due > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($s->due), 2) }} {{ $s->due > 0 ? 'Due' : 'Adv' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No records found</td></tr>
                                @endforelse
                            </tbody>
                            @if($suppliers->count() > 0)
                            <tfoot class="bg-light fw-bold border-top">
                                <tr>
                                    <td colspan="2" class="ps-3 py-3">GRAND TOTAL</td>
                                    <td class="text-end">{{ number_format($totals['opening'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['purchase'], 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($totals['paid'], 2) }}</td>
                                    <td class="text-end text-warning">{{ number_format($totals['return'], 2) }}</td>
                                    <td class="text-end pe-3 {{ $totals['due'] > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format(abs($totals['due']), 2) }} {{ $totals['due'] > 0 ? 'Due' : 'Adv' }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table > :not(caption) > * > * { border-bottom-width: 1px; }
        .bg-light { background-color: #f8fafc !important; }
        .extra-small { font-size: 0.75rem; }
        .cursor-pointer { cursor: pointer; }
        @media print {
            .main-content { margin-left: 0 !important; padding-top: 0 !important; }
            .sidebar, .header, .btn, form { display: none !important; }
        }
    </style>
@endsection
