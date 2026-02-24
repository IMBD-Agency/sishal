<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 30px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; font-size: 10px; line-height: 1.4; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; text-align: center; }
        .company-title { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; }
        .report-title { font-size: 14px; color: #64748b; font-weight: bold; margin-top: 5px; text-transform: uppercase; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-td { width: 50%; vertical-align: top; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th { background: #0f172a; color: white; text-transform: uppercase; font-size: 9px; padding: 10px; text-align: left; border: 1px solid #0f172a; }
        .data-table td { padding: 8px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .amount { font-family: 'Courier', monospace; font-weight: bold; text-align: right; font-size: 11px; }
        
        .total-row { background: #f8fafc; font-weight: bold; }
        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 8px; color: #94a3b8; text-align: center; }
        
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        .text-primary { color: #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = $general_settings && $general_settings->site_logo ? public_path($general_settings->site_logo) : null;
            $logoBase64 = null;
            if ($logoPath && file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
            }
        @endphp
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo" style="height: 60px; margin-bottom: 10px;">
        @endif
        <h1 class="company-title">{{ $general_settings->site_title ?? 'Sisal Fashion' }}</h1>
        <div class="report-title">{{ $title }}</div>
        <div style="font-size: 11px; font-weight: bold; margin-top: 5px;">
            {{ $branchId ? 'Branch: ' . (\App\Models\Branch::find($branchId)->name ?? '') : 'Consolidated View (All Branches)' }}
        </div>
        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
            Period: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Account Details</th>
                <th>Branch</th>
                <th style="text-align: right;">Opening</th>
                <th style="text-align: right;">Debit (+)</th>
                <th style="text-align: right;">Credit (-)</th>
                <th style="text-align: right;">Closing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
            <tr>
                <td>
                    <div style="font-weight: bold;">{{ $account->account_holder_name ?? $account->chartOfAccount->name }}</div>
                    <div style="font-size: 8px; color: #64748b;">
                        {{ $account->provider_name }} {{ $account->account_number ? '#'.$account->account_number : '' }}
                    </div>
                </td>
                <td>{{ $account->branch_name ?? '-' }}</td>
                <td class="amount">৳{{ number_format($account->opening, 2) }}</td>
                <td class="amount text-success">৳{{ number_format($account->debit, 2) }}</td>
                <td class="amount text-danger">৳{{ number_format($account->credit, 2) }}</td>
                <td class="amount text-primary">৳{{ number_format($account->closing, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align: right; padding: 10px;">GRAND TOTAL</td>
                <td class="amount">৳{{ number_format($accounts->sum('opening'), 2) }}</td>
                <td class="amount text-success">৳{{ number_format($accounts->sum('debit'), 2) }}</td>
                <td class="amount text-danger">৳{{ number_format($accounts->sum('credit'), 2) }}</td>
                <td class="amount text-primary">৳{{ number_format($accounts->sum('closing'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d M Y, h:i A') }} • ERP Financial System • Page 1 of 1
    </div>
</body>
</html>
