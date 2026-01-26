<!DOCTYPE html>
<html>
<head>
    <title>Sale Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .report-title { font-size: 14px; margin-top: 5px; color: #555; }
        .date-range { margin-bottom: 15px; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .summary-box { margin-top: 20px; width: 300px; margin-left: auto; }
        .summary-item { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #eee; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Detailed Sale Report</div>
        <div class="report-title">Sales Performance Analysis</div>
        <div class="date-range">
            Period: {{ $startDate->format('d M, Y') }} to {{ $endDate->format('d M, Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Product Name</th>
                <th>Style #</th>
                <th>Variation</th>
                <th class="text-right">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Disc.</th>
                <th class="text-right">Total</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $item->invoice }}</td>
                <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/y') }}</td>
                <td>{{ $item->customer_name }}</td>
                <td>{{ $item->product->name ?? 'Deleted' }}</td>
                <td>{{ $item->product->style_number ?? '-' }}</td>
                <td>
                    @if($item->variation)
                        {{ $item->variation->attributeValues->pluck('value')->implode(', ') }}
                    @else
                        Standard
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right text-danger">{{ number_format($item->discount, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($item->total_price, 2) }}</td>
                <td>{{ $item->source }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #efefef;">
            <tr>
                <td colspan="8" class="text-right fw-bold">GRAND TOTAL</td>
                <td class="text-center fw-bold">{{ number_format($summary['total_qty']) }}</td>
                <td class="text-right fw-bold text-danger">{{ number_format($summary['total_discount'], 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($summary['total_amount'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ date('d M, Y H:i:s') }}
    </div>
</body>
</html>
