<!DOCTYPE html>
<html>
<head>
    <title>Supplier Payment Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 10px; color: #555; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; color: #1e293b; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0; color: #64748b; }
        .footer { margin-top: 30px; text-align: right; font-weight: bold; font-size: 14px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .meta { margin-bottom: 20px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Supplier Disbursement Report</h2>
        <p>Generated on {{ date('d M, Y h:i A') }}</p>
    </div>

    <div class="meta">
        <strong>Report Period:</strong> 
        {{ $startDate ? $startDate->format('d M, Y') : 'Beginning' }} - {{ $endDate ? $endDate->format('d M, Y') : 'Today' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Voucher #</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Bill Reference</th>
                <th>Method</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td><strong>SP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                <td>{{ $payment->supplier->name }}</td>
                <td>{{ $payment->bill->bill_number ?? 'Advance Payment' }}</td>
                <td>{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td style="text-align: right;">{{ number_format($payment->amount, 2) }}৳</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Total Disbursed: {{ number_format($payments->sum('amount'), 2) }}৳
    </div>
</body>
</html>
