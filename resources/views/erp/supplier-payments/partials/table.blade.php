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
                    <div class="fw-bold text-dark">{{ $payment->supplier?->name ?? 'Deleted Supplier' }}</div>
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
                            <i
                                class="fas {{ $payment->financialAccount->type == 'bank' ? 'fa-university text-primary' : ($payment->financialAccount->type == 'cash' ? 'fa-wallet text-success' : 'fa-mobile-alt text-info') }}"></i>
                            <span class="fw-bold text-uppercase"
                                style="font-size: 11px;">{{ $payment->financialAccount->provider_name }}</span>
                        @else
                            @if($payment->payment_method == 'cash')
                                <i class="fas fa-wallet text-success"></i>
                            @elseif($payment->payment_method == 'bank_transfer')
                                <i class="fas fa-university text-primary"></i>
                            @else
                                <i class="fas fa-money-check text-warning"></i>
                            @endif
                            <span class="fw-bold text-uppercase"
                                style="font-size: 11px;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                        @endif
                    </div>
                </td>
                <td class="text-center pe-3">
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('supplier-payments.show', $payment->id) }}"
                            class="action-circle bg-light border-0" title="View Voucher">
                            <i class="fas fa-eye text-primary"></i>
                        </a>
                        @can('delete payments')
                        <form action="{{ route('supplier-payments.destroy', $payment->id) }}" method="POST"
                            onsubmit="return confirm('Void this payment?\n\nThis will reverse the following:\n1. Revert and increase the purchase bill due amount by {{ number_format($payment->amount, 2) }}৳.\n2. Refund and increase the Cash/Bank account balance.\n3. Delete the transaction from Supplier Ledger.\n4. Recalibrate Supplier Balance.\n5. Delete the double-entry Journal records.')"
                            class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-circle bg-light border-0" title="Void Payment">
                                <i class="fas fa-trash-alt text-danger"></i>
                            </button>
                        </form>
                        @endif
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