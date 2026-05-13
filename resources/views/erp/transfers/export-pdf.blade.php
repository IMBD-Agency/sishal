<!DOCTYPE html>
<html>
<head>
    <title>Fund Transfer Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #198754; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #198754; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #475569; text-transform: uppercase; font-size: 9px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8px; color: #94a3b8; }
        .badge { padding: 2px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .bg-soft-primary { background-color: #e0e7ff; color: #4338ca; }
        .bg-soft-info { background-color: #e0f2fe; color: #0369a1; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Fund Transfer Report</h2>
        <p>Period: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'All Time' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Present' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Date</th>
                <th width="20%">From (Source)</th>
                <th width="20%">To (Destination)</th>
                <th width="10%" class="text-end">Amount</th>
                <th width="15%">Reference</th>
                <th width="15%">Memo</th>
                <th width="10%">Created By</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @foreach($items as $item)
            <tr>
                <td>{{ $item->transfer_date->format('d/m/Y') }}</td>
                <td>
                    <div class="fw-bold">{{ $item->fromAccount->provider_name ?? 'N/A' }}</div>
                    <div style="font-size: 8px; color: #64748b;">
                        {{ $item->from_location }}
                    </div>
                </td>
                <td>
                    <div class="fw-bold">{{ $item->toAccount->provider_name ?? 'N/A' }}</div>
                    <div style="font-size: 8px; color: #64748b;">
                        {{ $item->to_location }}
                    </div>
                </td>
                <td class="text-end fw-bold">{{ number_format($item->amount, 2) }}৳</td>
                <td>{{ $item->reference ?: '-' }}</td>
                <td>{{ $item->memo ?: '-' }}</td>
                <td>{{ $item->creator->name ?? 'N/A' }}</td>
            </tr>
            @php $totalAmount += $item->amount; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background-color: #f8fafc;">
                <td colspan="3" class="text-end">Grand Total</td>
                <td class="text-end">{{ number_format($totalAmount, 2) }}৳</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Printed on: {{ date('d/m/Y H:i A') }}
    </div>
</body>
</html>
