@forelse($ledgerEntries as $entry)
    <tr>
        <td class="ps-4 text-dark fw-500">{{ \Carbon\Carbon::parse($entry->journal->entry_date)->format('d M, Y') }}</td>
        <td>
            <div class="d-flex flex-column">
                <span class="badge bg-light text-dark border mb-1 align-self-start py-1 px-2 fw-normal">{{ $entry->journal->voucher_no }}</span>
                <span class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $entry->journal->description }}">{{ $entry->journal->description }}</span>
            </div>
        </td>
        <td>
            <div class="fw-bold text-primary mb-0">{{ $entry->chartOfAccount->name }}</div>
            <div class="text-muted small fs-xs text-uppercase">{{ $entry->chartOfAccount->code }}</div>
        </td>
        <td class="text-end">
            @if($entry->debit > 0)
                <span class="text-danger fw-bold">৳{{ number_format($entry->debit, 2) }}</span>
            @else
                <span class="text-muted opacity-50">-</span>
            @endif
        </td>
        <td class="text-end">
            @if($entry->credit > 0)
                <span class="text-success fw-bold">৳{{ number_format($entry->credit, 2) }}</span>
            @else
                <span class="text-muted opacity-50">-</span>
            @endif
        </td>
        <td class="text-end fw-bold">
            @php $bal = $entry->debit - $entry->credit; @endphp
            <span class="{{ $bal >= 0 ? 'text-primary' : 'text-danger' }}">
                ৳{{ number_format(abs($bal), 2) }}
                <small class="fw-normal text-muted fs-xs">{{ $bal >= 0 ? 'Dr' : 'Cr' }}</small>
            </span>
        </td>
        <td class="text-center pe-4">
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('journal.show', $entry->journal_id) }}" class="action-circle bg-light" title="View Voucher">
                    <i class="fas fa-eye text-primary"></i>
                </a>
                <a href="{{ route('ledger.account', $entry->chart_of_account_id) }}" class="action-circle bg-light" title="View Full Account Ledger">
                    <i class="fas fa-book text-info"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <div class="py-5">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-search-dollar fa-2x text-muted"></i>
                </div>
                <h5 class="text-dark fw-bold">No Records Found</h5>
                <p class="text-muted mx-auto" style="max-width: 300px;">Adjust your filters or date range to find the transactions you're looking for.</p>
            </div>
        </td>
    </tr>
@endforelse
