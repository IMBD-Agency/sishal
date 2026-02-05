@extends('erp.master')

@section('title', 'Supplier Ledger')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('reports.supplier-summary') }}" class="text-decoration-none text-muted">Supplier Report</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">{{ $supplier->name }} - Ledger</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">{{ $supplier->name }} <span class="text-muted fs-6">({{ $supplier->phone }})</span></h4>
                </div>
                <div>
                     <form method="GET" class="d-flex gap-2">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                        <button class="btn btn-sm btn-dark"><i class="fas fa-filter"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="window.print()"><i class="fas fa-print"></i></button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4">Date</th>
                                    <th class="py-3">Description / Type</th>
                                    <th class="py-3">Reference No</th>
                                    <th class="py-3 text-end">Debit (Purchase)</th>
                                    <th class="py-3 text-end">Credit (Paid)</th>
                                    <th class="py-3 text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $balance = 0; @endphp
                                
                                @forelse($transactions as $txn)
                                    @php 
                                        // For suppliers: Purchase increases what we owe (Credit in normal accounting, but Debit here means "Bill Amount")
                                        // Payment decreases what we owe.
                                        // Let's stick to: Balance = Purchase - Paid
                                        $balance += ($txn['debit'] - $txn['credit']);
                                    @endphp
                                    <tr>
                                        <td class="ps-4">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                                        <td>
                                            <span class="badge {{ $txn['type'] == 'Purchase' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' }}">
                                                {{ $txn['type'] }}
                                            </span>
                                            @if($txn['note']) <br><small class="text-muted">{{ $txn['note'] }}</small> @endif
                                        </td>
                                        <td class="font-monospace small">{{ $txn['reference'] }}</td>
                                        <td class="text-end">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                                        <td class="text-end">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                                        <td class="text-end fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($balance), 2) }} {{ $balance > 0 ? 'Due' : 'Adv' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No transactions found in this period</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end text-uppercase">Closing Balance</td>
                                    <td class="text-end {{ $balance > 0 ? 'text-danger' : 'text-success' }} fs-6">
                                        {{ number_format(abs($balance), 2) }} {{ $balance > 0 ? 'Due' : 'Adv' }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
