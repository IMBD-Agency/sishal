@php 
    $totalDebit = $transactions->sum('debit');
    $totalCredit = $transactions->sum('credit');
    $finalBalance = ($openingBalance ?? 0) + ($totalDebit - $totalCredit);
@endphp

<!-- KPI Row -->
<div class="row g-3 mb-4 text-center">
    <div class="col-md-4">
        <div class="p-3 border rounded bg-light">
            <div class="small text-muted text-uppercase mb-1">Total Sales (Debit)</div>
            <h5 class="fw-bold mb-0">Tk. {{ number_format($totalDebit, 2) }}</h5>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 border rounded bg-light">
            <div class="small text-muted text-uppercase mb-1">Total Collection (Credit)</div>
            <h5 class="fw-bold mb-0 text-success">Tk. {{ number_format($totalCredit, 2) }}</h5>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 border rounded {{ $finalBalance > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }}">
            <div class="small text-muted text-uppercase mb-1">Current Balance</div>
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
                        <th class="py-3 text-muted small fw-bold">Transaction Detail</th>
                        <th class="py-3 text-muted small fw-bold">Ref No</th>
                        <th class="text-end py-3 text-muted small fw-bold">Debit (Sales)</th>
                        <th class="text-end py-3 text-muted small fw-bold">Credit (Paid)</th>
                        <th class="text-end pe-3 py-3 text-muted small fw-bold">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = $openingBalance ?? 0; @endphp
                    <tr class="bg-light-50">
                        <td colspan="3" class="ps-3 py-2 text-muted italic">Opening Balance Carried Forward</td>
                        <td class="text-end py-2">-</td>
                        <td class="text-end py-2">-</td>
                        <td class="text-end pe-3 py-2 fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                        </td>
                    </tr>

                    @foreach($transactions as $txn)
                        @php $runningBalance += ($txn['debit'] - $txn['credit']); @endphp
                        <tr>
                            <td class="ps-3 py-2 small text-muted">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                            <td class="py-2">
                                <div class="fw-bold small text-uppercase">{{ $txn['type'] }}</div>
                                @if(isset($txn['note']) && $txn['note']) <div class="extra-small text-muted">{{ $txn['note'] }}</div> @endif
                            </td>
                            <td class="py-2 font-monospace extra-small text-primary">{{ $txn['reference'] }}</td>
                            <td class="text-end py-2">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                            <td class="text-end py-2 text-success">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                            <td class="text-end pe-3 py-2 fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light fw-bold border-top">
                    <tr>
                        <td colspan="3" class="ps-3 py-3">Closing Statement Total</td>
                        <td class="text-end py-3">{{ number_format($totalDebit, 2) }}</td>
                        <td class="text-end py-3 text-success">{{ number_format($totalCredit, 2) }}</td>
                        <td class="text-end pe-3 py-3">Tk. {{ number_format(abs($finalBalance), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
