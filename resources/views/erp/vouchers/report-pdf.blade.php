<!DOCTYPE html>
<html>
<head>
    <title>Voucher Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h2 { text-align: center; margin-bottom: 4px; font-size: 16px; }
        p.sub { text-align: center; color: #666; margin: 0 0 16px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2d3e6b; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) td { background: #f8f9fb; }
        .total-row td { font-weight: bold; background: #eef1f8; }
        .text-right { text-align: right; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; text-transform: uppercase; }
        .badge-payment { background: #fee2e2; color: #b91c1c; }
        .badge-receipt { background: #dcfce7; color: #15803d; }
        .badge-contra  { background: #e0e7ff; color: #3730a3; }
        .badge-journal { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
    <h2>Voucher Report</h2>
    <p class="sub">Generated: {{ date('d M Y, h:i A') }} &nbsp;|&nbsp; Total Records: {{ $vouchers->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Voucher No</th>
                <th>Date</th>
                <th>Type</th>
                <th>Account</th>
                <th>Party</th>
                <th>Branch</th>
                <th class="text-right">{{ isset($selectedAccount) && $selectedAccount ? $selectedAccount->name . ' Amount' : 'Amount' }}</th>
                <th class="text-right">Paid</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $i => $v)
            @php
                $party = optional($v->customer)->name ?? optional($v->supplier)->name ?? '—';
                $badgeClass = [
                    'Payment' => 'badge-payment',
                    'Receipt' => 'badge-receipt',
                    'Contra'  => 'badge-contra',
                    'Journal' => 'badge-journal',
                ][$v->type] ?? '';

                $rowAmount = $v->voucher_amount;
                if (isset($selectedAccount) && $selectedAccount) {
                    $matchingEntries = $v->entries->where('chart_of_account_id', $selectedAccount->id);
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
                    } elseif ($v->expense_account_id == $selectedAccount->id) {
                        $rowAmount = $v->voucher_amount;
                    }
                }
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $v->voucher_no }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($v->entry_date)->format('d M Y') }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $v->type }}</span></td>
                <td>{{ optional($v->expenseAccount)->name ?? '—' }}</td>
                <td>{{ $party }}</td>
                <td>{{ optional($v->branch)->name ?? '—' }}</td>
                <td class="text-right">{{ number_format($rowAmount, 2) }}</td>
                <td class="text-right">{{ number_format($v->paid_amount, 2) }}</td>
                <td>{{ optional($v->creator)->name ?? '—' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7" class="text-right">{{ isset($selectedAccount) && $selectedAccount ? 'TOTAL (' . strtoupper($selectedAccount->name) . ')' : 'GRAND TOTAL' }}</td>
                <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
                <td class="text-right">{{ number_format($totalPaid, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
