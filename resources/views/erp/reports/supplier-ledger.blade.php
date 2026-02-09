@extends('erp.master')

@section('title', 'Supplier Ledger')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Supplier Ledger Account</h4>
                    @if(isset($supplier))
                        <p class="text-muted small mb-0">{{ $supplier->name }} | {{ $supplier->phone }}</p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    @if(isset($supplier))
                        <a href="{{ route('reports.supplier.ledger', ['id' => $supplier->id, 'export' => 'excel']) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="{{ route('reports.supplier.ledger', ['id' => $supplier->id, 'export' => 'pdf']) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('reports.supplier.ledger') }}" method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Select Supplier</label>
                            <select name="supplier_id" class="form-select form-select-sm select2" onchange="this.form.submit()">
                                <option value="">Choose Supplier...</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ (isset($supplier) && $supplier->id == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Sync Statement</button>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($supplier))
                @php 
                    $totalDebit = $transactions->sum('debit'); // Payments
                    $totalCredit = $transactions->sum('credit'); // Purchases
                    $finalBalance = ($openingBalance ?? 0) + ($totalCredit - $totalDebit);
                @endphp

                <!-- Summary Row -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted text-uppercase mb-1">Purchase Sum</div>
                            <h5 class="fw-bold mb-0">Tk. {{ number_format($totalCredit, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted text-uppercase mb-1">Payment Sum</div>
                            <h5 class="fw-bold mb-0 text-success">Tk. {{ number_format($totalDebit, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded {{ $finalBalance > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }}">
                            <div class="small text-muted text-uppercase mb-1">Balance Due</div>
                            <h5 class="fw-bold mb-0 {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}">
                                Tk. {{ number_format(abs($finalBalance), 2) }} {{ $finalBalance > 0 ? '(Due)' : '(Adv)' }}
                            </h5>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table -->
                <div class="card border shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3 py-3 text-muted small fw-bold">Date</th>
                                        <th class="py-3 text-muted small fw-bold">Transaction</th>
                                        <th class="py-3 text-muted small fw-bold">Ref</th>
                                        <th class="text-end py-3 text-muted small fw-bold">Debit (Dr)</th>
                                        <th class="text-end py-3 text-muted small fw-bold">Credit (Cr)</th>
                                        <th class="text-end pe-3 py-3 text-muted small fw-bold">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $runningBalance = $openingBalance ?? 0; @endphp
                                    <tr class="bg-light-50">
                                        <td colspan="3" class="ps-3 py-2 text-muted italic">Opening Balance Statement</td>
                                        <td class="text-end py-2">-</td>
                                        <td class="text-end py-2">-</td>
                                        <td class="text-end pe-3 py-2 fw-bold">
                                            {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Cr' : 'Dr' }}
                                        </td>
                                    </tr>

                                    @foreach($transactions as $txn)
                                        @php $runningBalance += ($txn['credit'] - $txn['debit']); @endphp
                                        <tr>
                                            <td class="ps-3 py-2 small text-muted">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                                            <td class="py-2">
                                                <span class="fw-semibold small text-uppercase">{{ $txn['type'] }}</span>
                                                @if($txn['note']) <div class="extra-small text-muted">{{ $txn['note'] }}</div> @endif
                                            </td>
                                            <td class="py-2 font-monospace extra-small">{{ $txn['reference'] }}</td>
                                            <td class="text-end py-2 text-success">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                                            <td class="text-end py-2 text-danger">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                                            <td class="text-end pe-3 py-2 fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Cr' : 'Dr' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="3" class="ps-3 py-3">Total Statement Summary</td>
                                        <td class="text-end py-3 text-success">{{ number_format($totalDebit, 2) }}</td>
                                        <td class="text-end py-3 text-danger">{{ number_format($totalCredit, 2) }}</td>
                                        <td class="text-end pe-3 py-3">Tk. {{ number_format(abs($finalBalance), 2) }}</td>
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
    </style>
@endsection
