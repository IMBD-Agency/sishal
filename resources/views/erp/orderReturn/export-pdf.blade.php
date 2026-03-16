<!DOCTYPE html>
<html>
<head>
    <title>Order Return Report</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; margin: 0; padding: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2d5a4c; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #2d5a4c; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; }
        th { background: #2d5a4c; color: #fff; font-size: 10px; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .status-badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-processed { background: #cce5ff; color: #004085; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        @page { margin: 0.5cm; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Order Return Report</h2>
        <p>Generated on: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">SN</th>
                <th style="width: 60px;">Date</th>
                <th style="width: 70px;">Return ID</th>
                <th style="width: 80px;">Order #</th>
                <th>Customer</th>
                <th style="width: 80px;">Refund Type</th>
                <th style="width: 100px;">Location</th>
                <th style="width: 70px;">Status</th>
                <th style="width: 80px;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @foreach($returns as $index => $return)
                @php 
                    $refundAmount = $return->items->sum('total_price');
                    $totalAmount += $refundAmount;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') }}</td>
                    <td class="fw-bold">#RET-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $return->order?->order_number ?? '-' }}</td>
                    <td>
                        <div class="fw-bold">{{ $return->customer->name ?? 'Walk-in' }}</div>
                        <div style="font-size: 8px; color: #666;">{{ $return->customer->phone ?? '-' }}</div>
                    </td>
                    <td class="text-center">{{ ucfirst($return->refund_type) }}</td>
                    <td>{{ $return->destination_name }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $return->status }}">
                            {{ $return->status }}
                        </span>
                    </td>
                    <td class="text-right fw-bold">{{ number_format($refundAmount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background: #f8f9fa;">
                <td colspan="8" class="text-right">GRAND TOTAL</td>
                <td class="text-right text-primary">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
