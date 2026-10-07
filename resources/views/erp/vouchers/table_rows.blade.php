@forelse($vouchers as $index => $voucher)
@php
    $rowAmount = $voucher->voucher_amount;
    $isSpecificAccount = false;
    $accountBadgeName = null;
    $isReturnReversal = false;

    if (isset($selectedAccount) && $selectedAccount) {
        $matchingEntries = $voucher->entries->where('chart_of_account_id', $selectedAccount->id);
        $selectedTypeName = strtolower($selectedAccount->type->name ?? '');
        $isCreditNormal = in_array($selectedTypeName, ['liability', 'revenue', 'equity']) 
            || stripos($selectedAccount->name, 'vat') !== false 
            || stripos($selectedAccount->name, 'tax') !== false 
            || stripos($selectedAccount->name, 'sales') !== false
            || stripos($selectedAccount->name, 'delivery') !== false
            || stripos($selectedAccount->name, 'courier') !== false;

        if ($matchingEntries->isNotEmpty()) {
            $c = (float) $matchingEntries->sum('credit');
            $d = (float) $matchingEntries->sum('debit');
            $rowAmount = $isCreditNormal ? ($c - $d) : ($d - $c);
            $isSpecificAccount = true;
            $accountBadgeName = $selectedAccount->name;
            if ($rowAmount < 0 || ($isCreditNormal && $d > 0 && $c == 0)) {
                $isReturnReversal = true;
            }
        } elseif ($voucher->expense_account_id == $selectedAccount->id) {
            $rowAmount = $voucher->voucher_amount;
            $isSpecificAccount = true;
            $accountBadgeName = $selectedAccount->name;
        }
    }
@endphp
<tr>
    <td class="ps-3 text-muted">{{ $vouchers->firstItem() + $index }}</td>
    <td class="fw-bold">{{ $voucher->voucher_no }}</td>
    <td><span class="badge bg-light text-dark border">{{ $voucher->type }}</span></td>
    <td>{{ \Carbon\Carbon::parse($voucher->entry_date)->format('d/m/Y') }}</td>
    <td>{{ $voucher->branch->name ?? '-' }}</td>
    <td>{{ $voucher->customer->name ?? '-' }}</td>
    <td>{{ $voucher->expenseAccount->name ?? '-' }}</td>
    <td>
        <div>{{ Str::limit($voucher->description, 35) }}</div>
        @if($voucher->reference)
            <small class="text-muted"><i class="fas fa-tag me-1" style="font-size: 10px;"></i>{{ $voucher->reference }}</small>
        @endif
    </td>
    <td class="text-end fw-bold {{ $isSpecificAccount ? ($isReturnReversal ? 'text-danger' : 'text-primary') : '' }}">
        <span>{{ $rowAmount < 0 ? '-' . number_format(abs($rowAmount), 2) : number_format($rowAmount, 2) }}৳</span>
        @if($isSpecificAccount)
            <div class="mt-1">
                @if($isReturnReversal)
                    <span class="badge bg-danger-subtle text-danger border" style="font-size: 10px; font-weight: 600;">
                        <i class="fas fa-undo me-1"></i>Return Reversal
                    </span>
                @else
                    <span class="badge bg-primary-subtle text-primary border" style="font-size: 10px; font-weight: 600;">
                        {{ $accountBadgeName }}
                    </span>
                @endif
            </div>
            <div class="text-muted" style="font-size: 10.5px; font-weight: normal;">
                {{ $voucher->type == 'Payment' ? 'Return Total:' : 'Sale:' }} {{ number_format($voucher->voucher_amount, 2) }}৳
            </div>
        @endif
    </td>
    <td class="text-end fw-bold">{{ number_format($voucher->paid_amount, 2) }}৳</td>
    <td>{{ $voucher->entries->where('credit', '>', 0)->first()->chartOfAccount->name ?? 'N/A' }}</td>
    <td class="pe-3 text-center">
        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('journal.show', $voucher->id) }}" class="action-circle" title="View">
                <i class="fas fa-eye text-primary"></i>
            </a>
            @can('manage vouchers')
            <button type="button" class="action-circle bg-light border-0" title="Delete"
                onclick="deleteVoucher({{ $voucher->id }}, '{{ $voucher->voucher_no }}')">
                <i class="fas fa-trash text-danger"></i>
            </button>
            @endcan
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