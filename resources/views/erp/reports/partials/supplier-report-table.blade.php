<style>
    .supplier-summary-table thead th { 
        background-color: #1b4d3e !important; 
        color: white !important; 
        border: none !important; 
        font-size: 0.75rem !important;
        padding: 12px 8px !important;
    }
</style>

<table class="table table-hover align-middle mb-0 supplier-summary-table">
    <thead>
        <tr>
            <th class="ps-3 small fw-bold text-uppercase">#SN</th>
            <th class="small fw-bold text-uppercase">Supplier Name</th>
            <th class="small fw-bold text-uppercase">Supplier ID</th>
            <th class="small fw-bold text-uppercase">Mobile</th>
            <th class="small fw-bold text-uppercase">Outlet</th>
            <th class="text-end small fw-bold text-uppercase">Opening</th>
            <th class="text-end small fw-bold text-uppercase">Total</th>
            <th class="text-end small fw-bold text-uppercase">Paid</th>
            <th class="text-end small fw-bold text-uppercase">Payment</th>
            <th class="text-end small fw-bold text-uppercase">Return</th>
            <th class="text-end pe-3 small fw-bold text-uppercase">Due</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $tOpening = 0; $tPurchase = 0; $tPaid = 0; $tPayment = 0; $tReturn = 0; $tDue = 0;
            $sl = 1;
        @endphp
        @forelse($suppliers as $s)
            @if($s->is_empty) @continue @endif
            @php 
                $tOpening += $s->opening; $tPurchase += $s->purchase; $tPaid += $s->paid; 
                $tPayment += $s->payment; $tReturn += $s->return; $tDue += $s->due;
            @endphp
            <tr onclick="window.location='{{ route('reports.supplier.ledger', ['supplier_id' => $s->id]) }}'" class="cursor-pointer">
                <td class="ps-3">{{ $sl++ }}</td>
                <td class="fw-bold text-dark">{{ $s->name }}</td>
                <td class="text-muted small">{{ $s->supplier_id }}</td>
                <td class="text-muted small">{{ $s->mobile }}</td>
                <td><span class="badge bg-light text-dark border">{{ $s->outlet }}</span></td>
                <td class="text-end">{{ number_format($s->opening, 2) }}</td>
                <td class="text-end fw-semibold">{{ number_format($s->purchase, 2) }}</td>
                <td class="text-end text-success">{{ number_format($s->paid, 2) }}</td>
                <td class="text-end text-primary">{{ number_format($s->payment, 2) }}</td>
                <td class="text-end text-warning">{{ number_format($s->return, 2) }}</td>
                <td class="text-end pe-3 fw-bold {{ $s->due > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format(abs($s->due), 2) }}
                </td>
            </tr>
        @empty
            <tr><td colspan="11" class="text-center py-5 text-muted">No records found matching criteria</td></tr>
        @endforelse
    </tbody>
    @if($sl > 1)
    <tfoot class="bg-light fw-bold border-top">
        <tr>
            <td colspan="5" class="ps-3 py-3 text-end">Grand Total</td>
            <td class="text-end">{{ number_format($tOpening, 2) }}</td>
            <td class="text-end">{{ number_format($tPurchase, 2) }}</td>
            <td class="text-end text-success">{{ number_format($tPaid, 2) }}</td>
            <td class="text-end text-primary">{{ number_format($tPayment, 2) }}</td>
            <td class="text-end text-warning">{{ number_format($tReturn, 2) }}</td>
            <td class="text-end pe-3 {{ $tDue > 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format(abs($tDue), 2) }}
            </td>
        </tr>
    </tfoot>
    @endif
</table>
