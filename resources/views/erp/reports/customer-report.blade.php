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
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Summary
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form id="filterForm" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Report Type</label>
                            <select name="report_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $reportType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Outlet</label>
                            <select name="branch_id" class="form-select form-select-sm select2" onchange="this.form.submit()">
                                <option value="">All Outlets</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->toDateString() }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->toDateString() }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Find</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="card border shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-3 text-muted small fw-bold text-uppercase">Customer Identity</th>
                                    <th class="py-3 text-muted small fw-bold text-uppercase">Branch</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase">Opening</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase">Sales (Dr)</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase text-success">Paid (Cr)</th>
                                    <th class="text-end py-3 text-muted small fw-bold text-uppercase text-warning">Return/Exch</th>
                                    <th class="text-end pe-3 py-3 text-muted small fw-bold text-uppercase">Closing Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $tOpening = 0; $tSales = 0; $tPaid = 0; $tReturn = 0; $tDue = 0;
                                @endphp
                                @forelse($customers as $c)
                                    @php 
                                        $tOpening += $c->opening; $tSales += $c->sales; $tPaid += ($c->paid + $c->payment); 
                                        $tReturn += ($c->return + $c->exchange); $tDue += $c->due;
                                    @endphp
                                    <tr onclick="window.location='{{ route('reports.customer.ledger', $c->id) }}'" class="cursor-pointer">
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $c->name }}</div>
                                            <div class="extra-small text-muted">{{ $c->mobile ?? 'Guest' }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $c->outlet }}</span></td>
                                        <td class="text-end">{{ number_format($c->opening, 2) }}</td>
                                        <td class="text-end">{{ number_format($c->sales, 2) }}</td>
                                        <td class="text-end text-success">{{ number_format($c->paid + $c->payment, 2) }}</td>
                                        <td class="text-end text-warning">{{ number_format($c->return + $c->exchange, 2) }}</td>
                                        <td class="text-end pe-3 fw-bold {{ $c->due > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($c->due), 2) }} {{ $c->due > 0 ? 'Dr' : 'Cr' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No data available</td></tr>
                                @endforelse
                            </tbody>
                            @if($customers->count() > 0)
                            <tfoot class="bg-light fw-bold border-top">
                                <tr>
                                    <td colspan="2" class="ps-3 py-3">GRAND TOTAL</td>
                                    <td class="text-end text-dark">{{ number_format($tOpening, 2) }}</td>
                                    <td class="text-end text-dark">{{ number_format($tSales, 2) }}</td>
                                    <td class="text-end text-success">{{ number_format($tPaid, 2) }}</td>
                                    <td class="text-end text-warning">{{ number_format($tReturn, 2) }}</td>
                                    <td class="text-end pe-3 {{ $tDue > 0 ? 'text-danger' : 'text-success' }}">
                                        Tk. {{ number_format(abs($tDue), 2) }} {{ $tDue > 0 ? 'Dr' : 'Cr' }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $customers->links() }}
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
    </style>
@endsection
