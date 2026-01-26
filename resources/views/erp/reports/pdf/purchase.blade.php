<!DOCTYPE html>
<html>
<head>
    <title>Purchase Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Itemized Purchase Report</h2>
        <p>Period: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Ref #</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Product Name</th>
                <th>Style #</th>
                <th>Variation</th>
                <th class="text-right">Rate</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>#{{ $item->purchase_id }}</td>
                <td>{{ \Carbon\Carbon::parse($item->purchase->purchase_date)->format('d/m/y') }}</td>
                <td>{{ $item->purchase->supplier->name ?? 'N/A' }}</td>
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
                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #efefef;">
                <td colspan="8" class="text-right"><strong>GRAND TOTAL</strong></td>
                <td class="text-center"><strong>{{ number_format($summary['total_qty']) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['total_amount'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
