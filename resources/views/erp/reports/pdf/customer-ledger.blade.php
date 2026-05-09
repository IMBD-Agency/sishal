<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger - {{ $customer->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; font-size: 11px; margin: 0; padding: 0; }
        .header { padding: 30px; border-bottom: 2px solid #334155; }
        .company-name { font-size: 24px; font-weight: bold; color: #0f172a; margin-bottom: 5px; }
        .report-title { font-size: 14px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        
        .content { padding: 30px; }
        
        .customer-info { margin-bottom: 30px; width: 100%; }
        .customer-details { width: 50%; vertical-align: top; }
        .period-info { width: 50%; text-align: right; vertical-align: top; }
        
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
        
        .running-balance-col { background: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="report-title">Statement of Customer Ledger</div>
                </td>
                <td class="text-end">
                    <div style="font-weight: bold;">Report Date: {{ date('d M, Y') }}</div>
                    <div style="color: #64748b;">Period: {{ $startDate ? $startDate->format('d M, Y') : 'Life-to-date' }} - {{ $endDate ? $endDate->format('d M, Y') : date('d M, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        @if(($viewType ?? 'debit_credit') == 'info_wise')
        <!-- Info Wise Customer Info -->
        <table class="customer-info text-dark" style="margin-bottom: 30px;">
            <tr>
                <td style="width: 25%; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <div style="font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase;">CUSTOMER</div>
                    <div style="font-size: 12px; font-weight: bold; margin-top: 3px;">{{ $customer->name }}</div>
                </td>
                <td style="width: 25%; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <div style="font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase;">ADDRESS</div>
                    <div style="font-size: 10px; margin-top: 3px;">{{ $customer->address_1 ?? '-' }} {{ $customer->address_2 ?? '' }}</div>
                </td>
                <td style="width: 25%; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <div style="font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase;">MOBILE</div>
                    <div style="font-size: 10px; margin-top: 3px;">{{ $customer->phone ?? '-' }}</div>
                </td>
                <td style="width: 25%; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc;">
                    <div style="font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase;">OUTLET</div>
                    <div style="font-size: 10px; margin-top: 3px;">{{ $branchId ? \App\Models\Branch::find($branchId)->name ?? '-' : 'All Outlets' }}</div>
                </td>
            </tr>
        </table>
        @else
        <!-- Original Customer Info -->
        <table class="customer-info text-dark">
            <tr>
                <td class="customer-details">
                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">{{ $customer->name }}</div>
                    @if($customer->phone) <div>Phone: {{ $customer->phone }}</div> @endif
                    @if($customer->address) <div style="color: #64748b; width: 250px;">{{ $customer->address }}</div> @endif
                </td>
            </tr>
        </table>
        @endif

        @php 
            $totalDebit = $transactions->sum('debit');
            $totalCredit = $transactions->sum('credit');
            $finalBalance = ($openingBalance ?? 0) + ($totalDebit - $totalCredit);
        @endphp

        <table class="kpi-grid">
            <tr>
                <td class="kpi-box">
                    <span class="kpi-label">Total Debit (Sales)</span>
                    <span class="kpi-value">Tk. {{ number_format($totalDebit, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box">
                    <span class="kpi-label">Total Credit (Payments)</span>
                    <span class="kpi-value text-success">Tk. {{ number_format($totalCredit, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box" style="border-left: 4px solid {{ $finalBalance > 0 ? '#dc2626' : '#059669' }};">
                    <span class="kpi-label">Final Outstanding</span>
                    <span class="kpi-value {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}">
                        Tk. {{ number_format(abs($finalBalance), 2) }}
                        <span style="font-size: 9px;">({{ $finalBalance > 0 ? 'DUE' : 'ADV' }})</span>
                    </span>
                </td>
            </tr>
        </table>

        @if(($viewType ?? 'debit_credit') == 'info_wise')
        <!-- Info Wise Table -->
        <table class="ledger-table">
            <thead>
                <tr style="background-color: #166534; color: white;">
                    <th style="width: 5%; padding: 8px;">SN</th>
                    <th style="width: 10%; padding: 8px;">Date</th>
                    <th style="width: 11%; padding: 8px;">Invoice</th>
                    <th style="width: 11%; padding: 8px;">Challan</th>
                    <th style="width: 10%; padding: 8px;">Branch</th>
                    <th style="width: 18%; padding: 8px;">Particulars</th>
                    <th style="width: 10%; padding: 8px;" class="text-end">Total</th>
                    <th style="width: 8%; padding: 8px;" class="text-end">Discount</th>
                    <th style="width: 9%; padding: 8px;" class="text-end">Paid</th>
                    <th style="width: 8%; padding: 8px;" class="text-end">Due</th>
                </tr>
            </thead>
            <tbody>
                @php $sn = 1; @endphp
                @foreach($transactions as $txn)
                    <tr>
                        <td>{{ $sn++ }}</td>
                        <td style="color: #64748b; font-size: 9px;">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                        <td style="font-family: monospace; color: #3b82f6; font-size: 9px;">{{ $txn['invoice'] ?? '-' }}</td>
                        <td style="font-family: monospace; font-size: 9px;">{{ $txn['challan'] ?? '-' }}</td>
                        <td style="font-size: 9px;">{{ $txn['branch'] ?? '-' }}</td>
                        <td style="font-size: 9px;">{{ $txn['particulars'] ?? $txn['type'] }}</td>
                        <td class="text-end">{{ $txn['total'] > 0 ? number_format($txn['total'], 2) : '-' }}</td>
                        <td class="text-end">{{ $txn['discount'] > 0 ? number_format($txn['discount'], 2) : '-' }}</td>
                        <td class="text-end text-success">{{ $txn['paid'] > 0 ? number_format($txn['paid'], 2) : '-' }}</td>
                        <td class="text-end pe-3 py-1 fw-bold">
                            @if($txn['due'] > 0)
                                <span class="text-danger">Due: {{ number_format($txn['due'], 2) }}</span>
                            @elseif($txn['due'] < 0)
                                <span class="text-primary">Advance: {{ number_format(abs($txn['due']), 2) }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="background: #f8fafc; font-weight: bold;">
                <tr>
                    <td colspan="5" style="padding: 15px; text-transform: uppercase; font-size: 10px;">TOTAL</td>
                    <td class="text-end" style="padding: 15px;">Tk. {{ number_format($totalSales ?? 0, 2) }}</td>
                    <td class="text-end" style="padding: 15px;">Tk. {{ number_format($totalDiscount ?? 0, 2) }}</td>
                    <td class="text-end text-success" style="padding: 15px;">Tk. {{ number_format($totalPaid ?? 0, 2) }}</td>
                    <td class="text-end" style="padding: 15px;">
                        @if($totalDueAmount > 0)
                            <span>Tk. Due: {{ number_format($totalDueAmount, 2) }}</span>
                        @elseif($totalDueAmount < 0)
                            <span>Tk. Advance: {{ number_format(abs($totalDueAmount), 2) }}</span>
                        @else
                            <span>Tk. 0.00</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Info Wise Summary -->
        <table class="kpi-grid" style="margin-top: 30px;">
            <tr>
                <td class="kpi-box">
                    <span class="kpi-label">Opening Balance</span>
                    <span class="kpi-value">Tk. {{ number_format($openingBalance ?? 0, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box">
                    <span class="kpi-label">Total Sales</span>
                    <span class="kpi-value">Tk. {{ number_format($totalSales ?? 0, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box">
                    <span class="kpi-label">Total Paid</span>
                    <span class="kpi-value text-success">Tk. {{ number_format($totalPaid ?? 0, 2) }}</span>
                </td>
            </tr>
            <tr style="height: 20px;"></tr>
            <tr>
                <td class="kpi-box">
                    <span class="kpi-label">Total Return</span>
                    <span class="kpi-value">Tk. {{ number_format($totalReturn ?? 0, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box">
                    <span class="kpi-label">Total Exchange</span>
                    <span class="kpi-value">Tk. {{ number_format($totalExchange ?? 0, 2) }}</span>
                </td>
                <td style="width: 20px;"></td>
                <td class="kpi-box" style="border-left: 4px solid {{ $totalDueAmount > 0 ? '#dc2626' : '#059669' }};">
                    <span class="kpi-label">Due Amount</span>
                    <span class="kpi-value {{ $totalDueAmount > 0 ? 'text-danger' : 'text-success' }}">
                        Tk. {{ number_format(abs($totalDueAmount ?? 0), 2) }}
                    </span>
                </td>
            </tr>
        </table>
        @else
        <!-- Original Debit/Credit Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 15%;">Reference</th>
                    <th style="width: 10%;" class="text-end">Debit</th>
                    <th style="width: 10%;" class="text-end">Credit</th>
                    <th style="width: 15%;" class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = $openingBalance ?? 0; @endphp
                
                <tr>
                    <td colspan="3" class="fw-bold" style="color: #64748b;">Previous Opening Balance</td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                        Tk. {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                    </td>
                </tr>

                @foreach($transactions as $txn)
                    @php 
                        $runningBalance += ($txn['debit'] - $txn['credit']);
                    @endphp
                    <tr>
                        <td style="color: #64748b;">{{ \Carbon\Carbon::parse($txn['date'])->format('d M, Y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $txn['type'] }}</div>
                            @if($txn['note'])
                                <div style="font-size: 8px; color: #94a3b8; font-style: italic;">{{ $txn['note'] }}</div>
                            @endif
                        </td>
                        <td style="font-family: monospace; color: #3b82f6;">{{ $txn['reference'] }}</td>
                        <td class="text-end">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                        <td class="text-end text-success">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                        <td class="text-end fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                            Tk. {{ number_format(abs($runningBalance), 2) }} {{ $runningBalance > 0 ? 'Dr' : 'Cr' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="background: #f8fafc; font-weight: bold;">
                <tr>
                    <td colspan="3" style="padding: 15px; text-transform: uppercase; font-size: 10px;">Statement Summary Balance</td>
                    <td class="text-end" style="padding: 15px;">Tk. {{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end text-success" style="padding: 15px;">Tk. {{ number_format($totalCredit, 2) }}</td>
                    <td class="text-end {{ $finalBalance > 0 ? 'text-danger' : 'text-success' }}" style="padding: 15px; font-size: 12px;">
                         {{ $finalBalance < 0 ? '-' : '' }}Tk. {{ number_format(abs($finalBalance), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>

    <div class="footer">
        Generated automatically by ERP System | Page 1 of 1
    </div>
</body>
</html>
