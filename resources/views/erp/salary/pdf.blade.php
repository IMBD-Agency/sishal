<!DOCTYPE html>
<html>
<head>
    <title>Salary Payments Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4CAF50; padding-bottom: 15px; }
        .header h2 { margin: 0; color: #4CAF50; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0 0; color: #777; font-size: 14px; }
        .report-info { margin-bottom: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #4CAF50; color: white; text-align: left; padding: 10px 8px; text-transform: uppercase; font-size: 11px; }
        td { padding: 10px 8px; border-bottom: 1px solid #eee; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer { margin-top: 50px; text-align: right; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .total-row { background-color: #f9f9f9; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 10px; background: #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SISAL AGENCY</h2>
        <p>Staff Salary Payments Report</p>
        <span style="font-size: 10px; color: #888;">Generated on: {{ date('d M Y, h:i A') }}</span>
    </div>

    <div class="report-info">
        Report Criteria: 
        @if(request('month') && request('month') != 'Select One') [Month: {{ request('month') }}] @endif
        @if(request('year') && request('year') != 'Select One') [Year: {{ request('year') }}] @endif
        @if(request('employee_id') && request('employee_id') != 'all') [Individual Staff] @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Staff Name</th>
                <th>Outlet</th>
                <th>Period</th>
                <th class="text-end">Paid Amount</th>
                <th>Method</th>
                <th>Account</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($payments as $index => $payment)
                @php $total += $payment->paid_amount; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $payment->employee->user->first_name }} {{ $payment->employee->user->last_name }}</td>
                    <td>{{ $payment->branch->name ?? 'N/A' }}</td>
                    <td>{{ $payment->month }} {{ $payment->year }}</td>
                    <td class="text-end fw-bold">{{ number_format($payment->paid_amount, 2) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->chartOfAccount->name ?? 'N/A' }}</td>
                    <td>{{ date('d-m-Y', strtotime($payment->payment_date)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-end">GRAND TOTAL:</td>
                <td class="text-end">{{ number_format($total, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Page 1 of 1 | Printed by: {{ auth()->user()->name ?? 'Administrator' }}</p>
    </div>
</body>
</html>
