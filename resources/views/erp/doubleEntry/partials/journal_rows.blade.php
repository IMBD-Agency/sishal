@forelse($journals ?? [] as $journal)
    <tr>
        <td class="ps-4">
            <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1">{{ $journal->voucher_no }}</span>
        </td>
        <td class="text-dark fw-500 text-nowrap">{{ $journal->entry_date->format('d M, Y') }}</td>
        <td class="text-nowrap">
            <span class="small fw-500">{{ $journal->branch ? $journal->branch->name : 'N/A' }}</span>
        </td>
        <td>
            @php
                $accounts = $journal->entries->map(function($e) {
                    if ($e->financialAccount) return $e->financialAccount->provider_name;
                    return $e->chartOfAccount ? $e->chartOfAccount->name : '';
                })->filter()->unique()->implode(', ');
            @endphp
            <div class="small text-muted text-truncate" style="max-width: 150px;" title="{{ $accounts ?: 'N/A' }}">
                {{ $accounts ?: 'N/A' }}
            </div>
        </td>
        <td>
            <div class="text-truncate text-muted small" style="max-width: 200px;" title="{{ $journal->description }}">
                {{ $journal->description ?? 'N/A' }}
            </div>
        </td>
        <td>
            @if($journal->type)
                @php
                    $typeColors = [
                        'Journal'    => 'bg-primary',
                        'Payment'    => 'bg-success',
                        'Receipt'    => 'bg-info',
                        'Contra'     => 'bg-warning',
                        'Adjustment' => 'bg-secondary',
                    ];
                    $color = $typeColors[$journal->type] ?? 'bg-secondary';
                @endphp
                <span class="badge {{ $color }} bg-opacity-10 text-{{ str_replace('bg-', '', $color) }} border border-{{ str_replace('bg-', '', $color) }} border-opacity-25 px-2 py-1">
                    {{ $journal->type }}
                </span>
            @else
                <span class="badge bg-light text-dark border px-2 py-1">General</span>
            @endif
        </td>
        <td class="text-end fw-bold text-success">৳{{ number_format($journal->total_debit, 2) }}</td>
        <td class="text-end fw-bold text-warning">৳{{ number_format($journal->total_credit, 2) }}</td>
        <td class="text-center">
            @if($journal->isBalanced())
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">
                    <i class="fas fa-check-circle me-1"></i>Balanced
                </span>
            @else
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-1 px-2">
                    <i class="fas fa-times-circle me-1"></i>Unbalanced
                </span>
            @endif
        </td>
        <td class="small text-muted">
            {{ $journal->createdBy ? ($journal->createdBy->first_name . ' ' . $journal->createdBy->last_name) : 'N/A' }}
        </td>
        <td class="text-center pe-4">
            <a href="{{ route('journal.show', $journal->id) }}" class="btn btn-sm btn-light border shadow-sm text-primary" title="View">
                <i class="fas fa-eye"></i>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center text-muted py-4">
            <i class="fas fa-book fa-2x mb-2"></i>
            <h6>No Journal Entries Found</h6>
            <p>Adjust your filters to find entries.</p>
        </td>
    </tr>
@endforelse
