@forelse($vouchers as $index => $voucher)
    <tr>
        <td class="ps-3 text-muted">{{ $vouchers->firstItem() + $index }}</td>
        <td class="fw-bold">{{ $voucher->voucher_no }}</td>
        <td><span class="badge bg-light text-dark border">{{ $voucher->type }}</span></td>
        <td>{{ \Carbon\Carbon::parse($voucher->entry_date)->format('d/m/Y') }}</td>
        <td>{{ $voucher->branch->name ?? '-' }}</td>
        <td>{{ $voucher->customer->name ?? '-' }}</td>
        <td>{{ $voucher->expenseAccount->name ?? '-' }}</td>
        <td>{{ Str::limit($voucher->description, 30) }}</td>
        <td class="text-end fw-bold">{{ number_format($voucher->voucher_amount, 2) }}৳</td>
        <td class="text-end fw-bold">{{ number_format($voucher->paid_amount, 2) }}৳</td>
        <td>{{ $voucher->entries->where('credit', '>', 0)->first()->chartOfAccount->name ?? 'N/A' }}</td>
        <td class="pe-3 text-center">
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('journal.show', $voucher->id) }}" class="action-circle" title="View">
                    <i class="fas fa-eye text-primary"></i>
                </a>
                <a href="#" class="action-circle bg-light" title="Edit">
                    <i class="fas fa-edit text-secondary"></i>
                </a>
                <button type="button" class="action-circle bg-light border-0" title="Delete" onclick="deleteVoucher({{ $voucher->id }}, '{{ $voucher->voucher_no }}')">
                    <i class="fas fa-trash text-danger"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
            <p>No vouchers found for the selected criteria.</p>
        </td>
    </tr>
@endforelse
