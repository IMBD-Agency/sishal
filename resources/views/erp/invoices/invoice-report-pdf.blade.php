<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 11px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 11px;
        }
        .summary-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .summary-row {
            width: 100%;
            margin-bottom: 0;
        }
        .summary-item {
            width: 20%;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: top;
        }
        .summary-item .label {
            display: block;
            font-size: 11px;
            color: #666;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-item .value {
            display: block;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totals-row {
            background-color: #e8f4fd !important;
            font-weight: bold;
        }
        .totals-row td {
            border-top: 2px solid #007bff;
        }
        .status-badge {
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-unpaid { background-color: #f8d7da; color: #721c24; }
        .status-partial { background-color: #fff3cd; color: #856404; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice Report</h1>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <table class="summary-row" style="width: 100%; border-collapse: separate; border-spacing: 5px;">
            <tr>
                <td class="summary-item">
                    <span class="label">Total Invoices:</span>
                    <span class="value">{{ $summary['total_invoices'] }}</span>
                </td>
                <td class="summary-item">
                    <span class="label">Total Amount:</span>
                    <span class="value">{{ $summary['total_amount'] }} taka</span>
                </td>
                <td class="summary-item">
                    <span class="label">Paid:</span>
                    <span class="value">{{ $summary['paid_invoices'] }}</span>
                </td>
                <td class="summary-item">
                    <span class="label">Unpaid:</span>
                    <span class="value">{{ $summary['unpaid_invoices'] }}</span>
                </td>
                <td class="summary-item">
                    <span class="label">Partial:</span>
                    <span class="value">{{ $summary['partial_invoices'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($filters['issue_date_from']) || !empty($filters['issue_date_to']) || !empty($filters['due_date_from']) || !empty($filters['due_date_to']) || !empty($filters['status']) || !empty($filters['issue_date']) || !empty($filters['due_date']))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(!empty($filters['issue_date']))
            <span class="filter-item">Issue Date: {{ \Carbon\Carbon::parse($filters['issue_date'])->format('d-m-Y') }}</span>
        @endif
        @if(!empty($filters['due_date']))
            <span class="filter-item">Due Date: {{ \Carbon\Carbon::parse($filters['due_date'])->format('d-m-Y') }}</span>
        @endif
        @if(!empty($filters['issue_date_from']))
            <span class="filter-item">Issue From: {{ $filters['issue_date_from'] }}</span>
        @endif
        @if(!empty($filters['issue_date_to']))
            <span class="filter-item">Issue To: {{ $filters['issue_date_to'] }}</span>
        @endif
        @if(!empty($filters['due_date_from']))
            <span class="filter-item">Due From: {{ $filters['due_date_from'] }}</span>
        @endif
        @if(!empty($filters['due_date_to']))
            <span class="filter-item">Due To: {{ $filters['due_date_to'] }}</span>
        @endif
        @if(!empty($filters['status']))
            <span class="filter-item">Status: {{ ucfirst($filters['status']) }}</span>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    @foreach($selectedColumns as $column)
                        @switch($column)
                            @case('invoice_number')
                                <td>{{ $invoice->invoice_number ?? '-' }}</td>
                                @break
                            @case('order_id')
                                <td>{{ $invoice->order ? $invoice->order->order_number : '-' }}</td>
                                @break
                            @case('customer')
                                <td>{{ $invoice->order ? $invoice->order->name : (optional($invoice->customer)->name ?? 'Walk-in Customer') }}</td>
                                @break
                            @case('salesman')
                                <td>{{ trim((optional($invoice->salesman)->first_name ?? '') . ' ' . (optional($invoice->salesman)->last_name ?? '')) ?: 'System' }}</td>
                                @break
                            @case('issue_date')
                                <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d-m-Y') : '-' }}</td>
                                @break
                            @case('due_date')
                                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') : '-' }}</td>
                                @break
                            @case('status')
                                <td>
                                    <span class="status-badge status-{{ $invoice->status ?? 'unknown' }}">
                                        {{ ucfirst($invoice->status ?? '-') }}
                                    </span>
                                </td>
                                @break
                            @case('subtotal')
                                <td>{{ number_format($invoice->subtotal, 2) }} taka</td>
                                @break
                            @case('tax')
                                <td>{{ number_format($invoice->tax, 2) }} taka</td>
                                @break
                            @case('discount')
                                <td>{{ number_format($invoice->discount_apply, 2) }} taka</td>
                                @break
                            @case('total')
                                <td>{{ number_format($invoice->total_amount, 2) }} taka</td>
                                @break
                            @case('paid_amount')
                                <td>{{ number_format($invoice->paid_amount, 2) }} taka</td>
                                @break
                            @case('due_amount')
                                <td>{{ number_format($invoice->due_amount, 2) }} taka</td>
                                @break
                            @default
                                <td>-</td>
                        @endswitch
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center;">No data found</td>
                </tr>
            @endforelse
            
            <!-- Totals Row -->
            @if($invoices->count() > 0)
                @php
                    $totalSubtotal = $invoices->sum('subtotal');
                    $totalTax = $invoices->sum('tax');
                    $totalDiscount = $invoices->sum('discount_apply');
                    $totalAmount = $invoices->sum('total_amount');
                    $totalPaidAmount = $invoices->sum('paid_amount');
                    $totalDueAmount = $invoices->sum('due_amount');
                @endphp
                <tr class="totals-row">
                    @foreach($selectedColumns as $column)
                        @switch($column)
                            @case('invoice_number')
                                <td>{{ $invoices->count() }} Invoices</td>
                                @break
                            @case('order_id')
                                <td>-</td>
                                @break
                            @case('customer')
                                <td>-</td>
                                @break
                            @case('salesman')
                                <td>-</td>
                                @break
                            @case('issue_date')
                                <td>-</td>
                                @break
                            @case('due_date')
                                <td>-</td>
                                @break
                            @case('status')
                                <td>-</td>
                                @break
                            @case('subtotal')
                                <td>{{ number_format($totalSubtotal, 2) }} taka</td>
                                @break
                            @case('tax')
                                <td>{{ number_format($totalTax, 2) }} taka</td>
                                @break
                            @case('discount')
                                <td>{{ number_format($totalDiscount, 2) }} taka</td>
                                @break
                            @case('total')
                                <td>{{ number_format($totalAmount, 2) }} taka</td>
                                @break
                            @case('paid_amount')
                                <td>{{ number_format($totalPaidAmount, 2) }} taka</td>
                                @break
                            @case('due_amount')
                                <td>{{ number_format($totalDueAmount, 2) }} taka</td>
                                @break
                            @default
                                <td>-</td>
                        @endswitch
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the system on {{ date('d-m-Y H:i:s') }}</p>
        <p>Total records: {{ $invoices->count() }} | Generated by: {{ Auth::user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
