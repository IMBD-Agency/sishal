<!DOCTYPE html>
<html>
<head>
    <title>Stock Value Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; text-align: left; }
        td { padding: 10px; border: 1px solid #ddd; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { background: #f0f7ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .low-stock { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Value Report</h1>
        <p>Generated on {{ date('M d, Y H:i') }}</p>
        <div style="font-size: 10px; color: #666; margin-top: 5px;">
            Branch: {{ $branchName }} | Category: {{ $categoryName }}
        </div>
    </div>

    <div class="summary-box">
        <span style="font-size: 14px; color: #666;">Total Integrated Valuation:</span><br>
        <span style="font-size: 24px; font-weight: bold; color: #007bff;">{{ number_format($totalValue, 2) }} TK</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-center">Stock</th>
                <th class="text-end">Unit Cost</th>
                <th class="text-end">Total Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    <td>
                        {{ $item['product']->name }}<br>
                        <small style="color: #888;">{{ $item['product']->sku }}</small>
                    </td>
                    <td>{{ $item['product']->category->name ?? 'N/A' }}</td>
                    <td class="text-center {{ $item['is_low'] ? 'low-stock' : '' }}">
                        {{ $item['total_stock'] }}
                    </td>
                    <td class="text-end">{{ number_format($item['unit_cost'], 2) }}</td>
                    <td class="text-end">{{ number_format($item['total_value'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">GRAND TOTAL</th>
                <th class="text-end">{{ number_format($totalValue, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        © {{ date('Y') }} ERP Accounting System - Inventory Analytics
    </div>
</body>
</html>
