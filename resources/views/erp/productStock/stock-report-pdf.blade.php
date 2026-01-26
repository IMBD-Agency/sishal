<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #eee; padding: 10px; text-align: left; }
        th { background-color: #3d6b52; color: white; text-transform: uppercase; font-size: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .low-stock { color: #dc3545; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin-bottom: 5px;">Inventory Status Report</h1>
        <p style="margin-top: 0; color: #666;">Generated on {{ date('F d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Product Name</th>
                <th style="width: 20%;">Style Number</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 15%;">Brand</th>
                <th style="width: 10%; text-align: center;">Total Qty</th>
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
                    <td style="text-align: center;" class="{{ $total <= 5 ? 'low-stock' : '' }}">
                        {{ $total }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Automated Inventory Report - Page {PAGE_NUM}
    </div>
</body>
</html>
