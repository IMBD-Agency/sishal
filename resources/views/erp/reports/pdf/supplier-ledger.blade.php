<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Ledger - {{ $supplier->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; font-size: 11px; margin: 0; padding: 0; }
        .header { padding: 30px; border-bottom: 2px solid #334155; }
        .report-title { font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        
        .content { padding: 30px; }
        
        .supplier-info { margin-bottom: 30px; width: 100%; }
        .supplier-details { width: 50%; vertical-align: top; }
        
        .kpi-grid { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .kpi-box { padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; width: 33.33%; }
        .kpi-label { font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .kpi-value { font-size: 16px; font-weight: bold; }

        .ledger-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .ledger-table th { background: #0f172a; color: white; padding: 10px; text-align: left; text-transform: uppercase; font-size: 9px; }
        .ledger-table td { padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        
        .text-end { text-align: right !important; }
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        .fw-bold { font-weight: bold; }
        
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="report-title">Statement of Supplier Ledger</div>
                </td>
                <td class="text-end">
                    <div style="font-weight: bold;">Report Date: {{ date('d M, Y') }}</div>
                    <div style="color: #64748b;">Period: {{ $startDate ? $startDate->format('d M, Y') : 'Life-to-date' }} - {{ $endDate ? $endDate->format('d M, Y') : date('d M, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <table class="supplier-info">
            <tr>
                <td class="supplier-details">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">{{ $supplier->name }}</div>
                    @if($supplier->phone) <div>Phone: {{ $supplier->phone }}</div> @endif
                    @if($supplier->address) <div style="color: #64748b; width: 250px;">{{ $supplier->address }}</div> @endif
                </td>
            </tr>
        </table>

        @php 
            $totalDebit = $transactions->sum('debit'); // Payments
            $totalCredit = $transactions->sum('credit'); // Purchases
            $finalBalance = ($openingBalance ?? 0) + ($totalCredit - $totalDebit);
        @endphp

        <table class="kpi-grid">
            <tr>
                <td class="kpi-box">
                    <span class="kpi-label">Total Purchased</span>
                    <span class="kpi-value">Tk. {{ number_format($totalCredit, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box">
                    <span class="kpi-label">Total Paid (Adjusted)</span>
                    <span class="kpi-value text-success">Tk. {{ number_format($totalDebit, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box" style="border-left: 4px solid {{ $finalBalance > 0 ? '#dc2626' : '#059669' }};">
                    <span class="kpi-label">Net Payable Balance</span>
                    <span class="kpi-value {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}">
                        Tk. {{ number_format(abs($finalBalance), 2) }}
                        <span style="font-size: 9px;">({{ $finalBalance > 0 ? 'DUE' : 'ADV' }})</span>
                    </span>
                </td>
            </tr>
        </table>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 15%;">Reference</th>
                    <th style="width: 10%;" class="text-end">Debit (Dr)</th>
                    <th style="width: 10%;" class="text-end">Credit (Cr)</th>
                    <th style="width: 15%;" class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = $openingBalance ?? 0; @endphp
                
                <tr>
                    <td colspan="3" class="fw-bold" style="color: #64748b;">Previous Opening balance</td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                        Tk. {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Cr' : 'Dr' }}
                    </td>
                </tr>

                @foreach($transactions as $txn)
                    @php 
                        $runningBalance += ($txn['credit'] - $txn['debit']);
                    @endphp
                    <tr>
                        <td style="color: #64748b;">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                        <td>
                            <div class="fw-bold text-uppercase" style="font-size: 9px;">{{ $txn['type'] }}</div>
                            @if($txn['note'])
                                <div style="font-size: 8px; color: #94a3b8; font-style: italic;">{{ $txn['note'] }}</div>
                            @endif
                        </td>
                        <td style="font-family: monospace; color: #3b82f6;">{{ $txn['reference'] }}</td>
                        <td class="text-end text-success">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                        <td class="text-end text-danger">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                        <td class="text-end fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                            Tk. {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Cr' : 'Dr' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="background: #f8fafc; font-weight: bold;">
                <tr>
                    <td colspan="3" style="padding: 15px; text-transform: uppercase; font-size: 10px;">Consolidated Summary result</td>
                    <td class="text-end text-success" style="padding: 15px;">Tk. {{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end text-danger" style="padding: 15px;">Tk. {{ number_format($totalCredit, 2) }}</td>
                    <td class="text-end {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}" style="padding: 15px; font-size: 12px;">
                         Tk. {{ number_format(abs($finalBalance), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Generated automatically by ERP System | Page 1 of 1
    </div>
</body>
</html>
