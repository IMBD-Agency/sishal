<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #198754; padding-bottom: 15px; }
        .header h2 { margin: 0; color: #198754; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #198754; color: white; padding: 10px; text-align: left; border: 1px solid #198754; }
        .data-table td { padding: 10px; border: 1px solid #ddd; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        .text-end { text-align: right; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 15px; }
        .summary-box { margin-top: 20px; text-align: right; }
        .summary-box table { width: 250px; float: right; border-collapse: collapse; }
        .summary-box td { padding: 8px; border-bottom: 1px solid #eee; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Sisal Fashion</h2>
        <div style="margin-top: 5px;">Expense Business Report</div>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Period:</strong> {{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</td>
            <td class="text-end"><strong>Generated At:</strong> {{ now()->format('d M, Y h:i A') }}</td>
        </tr>
        <tr>
            <td><strong>Branch:</strong> {{ $branchName }}</td>
            <td class="text-end"><strong>Report Type:</strong> Expense Analysis</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">#</th>
                <th width="80">Date</th>
                <th width="100">Ref No.</th>
                <th>Category</th>
                <th>Note</th>
                <th width="100" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $index => $exp)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $exp['date'] }}</td>
                    <td>{{ $exp['ref_no'] }}</td>
                    <td>{{ $exp['category'] }}</td>
                    <td>{{ $exp['note'] }}</td>
                    <td class="text-end">{{ number_format($exp['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <table>
            <tr>
                <td class="fw-bold">Total Expenses:</td>
                <td class="fw-bold" style="font-size: 14px; color: #198754;">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        © {{ date('Y') }} Sisal Fashion ERP - Quality & Style Management System
    </div>

</body>
</html>
