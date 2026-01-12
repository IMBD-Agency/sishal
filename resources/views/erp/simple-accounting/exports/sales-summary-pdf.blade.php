<!DOCTYPE html>
<html>
<head>
    <title>Sales Summary Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .metrics-grid { width: 100%; margin-bottom: 30px; }
        .metric-box { padding: 15px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px; }
        .metric-label { font-weight: bold; color: #666; font-size: 10px; text-transform: uppercase; }
        .metric-value { font-size: 18px; font-weight: bold; display: block; margin-top: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8f9fa; color: #444; font-weight: bold; text-align: left; padding: 10px; border-bottom: 2px solid #dee2e6; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-end { text-align: right; }
        .footer { margin-top: 50px; text-align: center; color: #999; font-size: 10px; }
        .profit { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Summary Report</h1>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }} ({{ ucfirst($dateRange) }})</p>
        <div style="margin-top: 10px; font-size: 10px; color: #666;">
            <span><strong>Branch:</strong> {{ $branchName }}</span> | 
            <span><strong>Category:</strong> {{ $categoryName }}</span> | 
            <span><strong>Source:</strong> {{ ucfirst($source) }}</span>
        </div>
    </div>

    <table style="margin-bottom: 40px;">
        <tr>
            <td>
                <div class="metric-label">Total Revenue</div>
                <div class="metric-value">{{ number_format($salesData['total_revenue'], 2) }} TK</div>
            </td>
            <td>
                <div class="metric-label">Gross Profit</div>
                <div class="metric-value profit">{{ number_format($profitData['gross_profit'], 2) }} TK</div>
            </td>
            <td>
                <div class="metric-label">Total Costs</div>
                <div class="metric-value">{{ number_format($costData['total_costs'], 2) }} TK</div>
            </td>
            <td>
                <div class="metric-label">Margin</div>
                <div class="metric-value">{{ number_format($profitData['profit_margin'], 1) }}%</div>
            </td>
        </tr>
    </table>

    <h3>Product & Variation Performance</h3>
    <table>
        <thead>
            <tr>
                <th>Product / Variation</th>
                <th>Source</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Revenue</th>
                <th class="text-end">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($variationProfits as $data)
                <tr>
                    <td>
                        {{ $data['product']->name }}
                        @if($data['variation'])
                            <br><small style="color: #666;">
                            @foreach($data['variation']->combinations as $comb)
                                {{ $comb->attribute->name }}: {{ $comb->attributeValue->value }} 
                            @endforeach
                            </small>
                        @endif
                    </td>
                    <td>{{ $data['source'] }}</td>
                    <td class="text-end">{{ $data['quantity_sold'] }}</td>
                    <td class="text-end">{{ number_format($data['revenue'], 2) }}</td>
                    <td class="text-end profit">{{ number_format($data['profit'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>
