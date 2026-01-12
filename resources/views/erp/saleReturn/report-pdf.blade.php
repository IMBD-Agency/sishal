<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Returns Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 24px; color: #1a1a1a; }
        .header p { margin: 5px 0 0; color: #666; }
        .filters { margin-bottom: 20px; padding: 10px; background: #f9f9f9; border-radius: 5px; }
        .filters h3 { margin: 0 0 5px; font-size: 12px; color: #555; }
        .filter-item { display: inline-block; margin-right: 20px; color: #777; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #eee; padding: 10px 8px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; color: #444; text-transform: uppercase; font-size: 10px; }
        .status-badge { padding: 3px 7px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fff8e1; color: #b78103; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
        .status-processed { background: #e3f2fd; color: #1565c0; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sale Returns Report</h1>
        <p>Generated on: {{ $date }}</p>
    </div>

    @if(!empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['status']))
    <div class="filters">
        <h3>Report Filters:</h3>
        @if(!empty($filters['start_date'])) <span class="filter-item">From: {{ $filters['start_date'] }}</span> @endif
        @if(!empty($filters['end_date'])) <span class="filter-item">To: {{ $filters['end_date'] }}</span> @endif
        @if(!empty($filters['status'])) <span class="filter-item">Status: {{ ucfirst($filters['status']) }}</span> @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Date</th>
                <th>Customer</th>
                <th>POS Sale</th>
                <th>Location</th>
                <th class="text-end">Items</th>
                <th class="text-end">Total Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($returns as $return)
                @php $totalPrice = $return->items->sum('total_price'); $grandTotal += $totalPrice; @endphp
                <tr>
                    <td>#SR-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d M, Y') }}</td>
                    <td>{{ $return->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $return->posSale->sale_number ?? 'N/A' }}</td>
                    <td>
                        @if($return->return_to_type == 'branch') {{ $return->branch->name ?? 'Branch' }}
                        @elseif($return->return_to_type == 'warehouse') {{ $return->warehouse->name ?? 'Warehouse' }}
                        @elseif($return->return_to_type == 'employee') {{ $return->employee->user->first_name ?? 'Employee' }}
                        @endif
                    </td>
                    <td class="text-end">{{ $return->items->count() }}</td>
                    <td class="text-end">{{ number_format($totalPrice, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $return->status }}">
                            {{ ucfirst($return->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
            <tr style="background: #f8f9fa;">
                <td colspan="6" class="text-end fw-bold">Grand Total</td>
                <td class="text-end fw-bold">{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system generated report. Total Records: {{ $returns->count() }}</p>
    </div>
</body>
</html>
