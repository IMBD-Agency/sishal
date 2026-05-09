<div class="table-responsive">
    <table class="table premium-table compact reporting-table table-hover align-middle mb-0" id="paymentTable">
        <thead>
            <tr>
                <th class="ps-3">Voucher Info</th>
                <th>Supplier Name</th>
                <!-- <th>Branch</th> -->
                <th>Bill Reference</th>
                <th class="text-end">Disbursed Amount</th>
                <th>Payment Method</th>
                <th class="text-center pe-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td class="ps-3">
                    <div class="fw-bold text-primary mb-0">SP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="extra-small text-muted fw-bold">{{ $payment->payment_date->format('d M, Y') }}</div>
                </td>
                <td>
                    <div class="fw-bold text-dark">{{ $payment->supplier->name }}</div>
                    <div class="extra-small text-muted">{{ $payment->creator->name ?? 'System' }}</div>
                </td>
                <td>
                    @if($payment->bill)
                        <span class="badge bg-soft-primary text-primary fw-bold">{{ $payment->bill->bill_number }}</span>
                    @else
                        <span class="badge bg-soft-warning text-warning fw-bold">ADVANCE</span>
                    @endif
                </td>
                <td class="text-end fw-bold text-dark">{{ number_format($payment->amount, 2) }}৳</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($payment->financialAccount)
                            <i class="fas {{ $payment->financialAccount->type == 'bank' ? 'fa-university text-primary' : ($payment->financialAccount->type == 'cash' ? 'fa-wallet text-success' : 'fa-mobile-alt text-info') }}"></i>
                            <span class="fw-bold text-uppercase" style="font-size: 11px;">{{ $payment->financialAccount->provider_name }}</span>
                        @else
                            @if($payment->payment_method == 'cash')
                                <i class="fas fa-wallet text-success"></i>
                            @elseif($payment->payment_method == 'bank_transfer')
                                <i class="fas fa-university text-primary"></i>
                            @else
                                <i class="fas fa-money-check text-warning"></i>
                            @endif
                            <span class="fw-bold text-uppercase" style="font-size: 11px;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                        @endif
                    </div>
                </td>
                <td class="text-center pe-3">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                            <i class="fas fa-ellipsis-v text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg premium-dropdown">
                            <li><a class="dropdown-item" href="{{ route('supplier-payments.show', $payment->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Voucher</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2 text-dark"></i>Print Receipt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('supplier-payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Void this payment?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Void Payment</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted opacity-50">
                        <i class="fas fa-receipt fa-4x mb-3"></i>
                        <p class="fw-bold mb-0">No payment history found for this period.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($payments->hasPages())
<div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
    <div class="small text-muted fw-bold">Records found: {{ $payments->total() }}</div>
    {{ $payments->links('vendor.pagination.bootstrap-5') }}
</div>
@endif
