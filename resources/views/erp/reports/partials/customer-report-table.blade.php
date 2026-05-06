<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-nowrap">
            <tr>
                <th class="ps-3 py-3 text-muted small fw-bold text-uppercase">SL</th>
                <th class="py-3 text-muted small fw-bold text-uppercase">Customer Name</th>
                <th class="py-3 text-muted small fw-bold text-uppercase">Number</th>
                <th class="py-3 text-muted small fw-bold text-uppercase">Outlet</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase">Opening</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase">Sales</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase text-success">Paid</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase text-info">Payment</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase text-secondary">Discount</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase text-danger">Return</th>
                <th class="text-end py-3 text-muted small fw-bold text-uppercase text-warning">Exchange</th>
                <th class="text-end pe-3 py-3 text-muted small fw-bold text-uppercase">Closing Due</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $tOpening = 0; $tSales = 0; $tPaid = 0; $tPayment = 0; $tDiscount = 0; $tReturn = 0; $tExchange = 0; $tDue = 0;
            @endphp
            @forelse($customers as $index => $c)
                @if($c->is_empty) @continue @endif
                @php 
                    $tOpening += $c->opening; $tSales += $c->sales; $tPaid += $c->paid; 
                    $tPayment += $c->payment; $tDiscount += $c->discount;
                    $tReturn += $c->return; $tExchange += $c->exchange; $tDue += $c->due;
                @endphp
                <tr onclick="window.location='{{ route('reports.customer.ledger', $c->id) }}'" class="cursor-pointer">
                    <td class="ps-3 small text-muted">{{ ($customers->currentPage() - 1) * $customers->perPage() + $index + 1 }}</td>
                    <td class="fw-bold text-dark">{{ $c->name }}</td>
                    <td class="text-muted small">{{ $c->mobile ?? 'Walk-in' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $c->outlet }}</span></td>
                    <td class="text-end">{{ number_format($c->opening, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($c->sales, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($c->paid, 2) }}</td>
                    <td class="text-end text-info">{{ number_format($c->payment, 2) }}</td>
                    <td class="text-end text-secondary small">{{ number_format($c->discount, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($c->return, 2) }}</td>
                    <td class="text-end text-warning">{{ number_format($c->exchange, 2) }}</td>
                    <td class="text-end pe-3 fw-bold {{ $c->due > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format(abs($c->due), 2) }} {{ $c->due > 0 ? 'Dr' : 'Cr' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center py-5 text-muted">No data found matching your filters</td></tr>
            @endforelse
        </tbody>
        @if($customers->count() > 0)
        <tfoot class="bg-light fw-bold border-top">
            <tr>
                <td colspan="4" class="ps-3 py-3">GRAND TOTAL</td>
                <td class="text-end text-dark">{{ number_format($tOpening, 2) }}</td>
                <td class="text-end text-dark">{{ number_format($tSales, 2) }}</td>
                <td class="text-end text-success">{{ number_format($tPaid, 2) }}</td>
                <td class="text-end text-info">{{ number_format($tPayment, 2) }}</td>
                <td class="text-end text-secondary">{{ number_format($tDiscount, 2) }}</td>
                <td class="text-end text-danger">{{ number_format($tReturn, 2) }}</td>
                <td class="text-end text-warning">{{ number_format($tExchange, 2) }}</td>
                <td class="text-end pe-3 {{ $tDue > 0 ? 'text-danger' : 'text-success' }}">
                    Tk. {{ number_format(abs($tDue), 2) }} {{ $tDue > 0 ? 'Dr' : 'Cr' }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
<div class="p-3 d-flex justify-content-between align-items-center">
    <div class="small text-muted">
        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers
    </div>
    <div class="customer-pagination">
        {{ $customers->links() }}
    </div>
</div>
