<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report</title>
    <style>
        @page { size: A4 landscape; margin: 30px; }
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #eee; padding: 8px 5px; text-align: left; word-wrap: break-word; }
        th { background-color: #2d5a4c; color: white; text-transform: uppercase; font-size: 9px; }
        .header { text-align: center; margin-bottom: 20px; }
        .low-stock { color: #dc3545; font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .footer { position: fixed; bottom: -10px; width: 100%; text-align: center; font-size: 8px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin-bottom: 5px; color: #2d5a4c;">Inventory Status Report</h1>
        <p style="margin-top: 0; color: #666;">Generated on {{ date('F d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 22%;">Product Name</th>
                <th style="width: 12%;">Style/SKU</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 10%;">Brand</th>
                <th style="width: 8%;">Season</th>
                <th style="width: 8%;">Gender</th>
                <th style="width: 8%; text-align: right;">Cost</th>
                <th style="width: 8%; text-align: right;">MRP</th>
                <th style="width: 7%; text-align: center;">Qty</th>
                <th style="width: 12%; text-align: right;">Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
                @php
                    $total = 0;
                    if ($product->has_variations) {
                        foreach($product->variations as $v) { $total += $v->stocks ? $v->stocks->sum('quantity') : 0; }
                    } else {
                        $total = ($product->branchStock ? $product->branchStock->sum('quantity') : 0) + 
                                ($product->warehouseStock ? $product->warehouseStock->sum('quantity') : 0);
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $product->name }}</td>
                    <td>{{ $product->style_number ?? $product->sku }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td style="text-transform: uppercase;">{{ $product->season->name ?? 'ALL' }}</td>
                    <td style="text-transform: uppercase;">{{ $product->gender->name ?? 'ALL' }}</td>
                    <td class="text-end">{{ number_format($product->cost, 2) }}</td>
                    <td class="text-end">{{ number_format($product->price, 2) }}</td>
                    <td class="text-center {{ $total <= 5 ? 'low-stock' : '' }}">
                        {{ $total }}
                    </td>
                    <td class="text-end" style="font-weight: bold;">
                        {{ number_format($total * $product->cost, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Automated Inventory Report - {{ date('Y-m-d') }}
    </div>
</body>
</html>
