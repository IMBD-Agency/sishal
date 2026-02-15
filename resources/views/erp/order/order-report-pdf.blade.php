<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 15px; font-size: 11px; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; font-size: 24px; }
        .filters { margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; }
        .filters h3 { margin: 0 0 10px 0; font-size: 14px; color: #666; }
        .filter-item { display: inline-block; margin-right: 20px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle; }
        th { background-color: #f8f9fa; font-weight: bold; color: #333; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; }
        .status-shipping { background-color: #cff4fc; color: #055160; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Report</h1>
        <p>Generated on: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(!empty($filters['search'])) <span class="filter-item">Search: {{ $filters['search'] }}</span> @endif
        @if(!empty($filters['status'])) <span class="filter-item">Status: {{ ucfirst($filters['status']) }}</span> @endif
        @if(!empty($filters['start_date'])) <span class="filter-item">From: {{ $filters['start_date'] }}</span> @endif
        @if(!empty($filters['end_date'])) <span class="filter-item">To: {{ $filters['end_date'] }}</span> @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Order #</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Bill Status</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Delivery</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->created_at->format('d-m-Y') }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->name }}</td>
                    <td>{{ $order->phone }}</td>
                    <td>
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>{{ ucfirst($order->invoice->status ?? 'N/A') }}</td>
                    <td class="text-end">{{ number_format($order->subtotal, 2) }}৳</td>
                    <td class="text-end">{{ number_format($order->discount, 2) }}৳</td>
                    <td class="text-end">{{ number_format($order->delivery, 2) }}৳</td>
                    <td class="text-end fw-bold">{{ number_format($order->total, 2) }}৳</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="6" class="text-end">Totals:</td>
                <td class="text-end">{{ number_format($orders->sum('subtotal'), 2) }}৳</td>
                <td class="text-end">{{ number_format($orders->sum('discount'), 2) }}৳</td>
                <td class="text-end">{{ number_format($orders->sum('delivery'), 2) }}৳</td>
                <td class="text-end">{{ number_format($orders->sum('total'), 2) }}৳</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the system. | Total Records: {{ $orders->count() }}</p>
    </div>
</body>
</html>
