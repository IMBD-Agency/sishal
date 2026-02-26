<!DOCTYPE html>
<html>
<head>
    <title>Product Catalog Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2d5a4c; padding-bottom: 10px; }
        .header h1 { color: #2d5a4c; margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2d5a4c; color: white; padding: 10px 5px; text-transform: uppercase; font-size: 10px; text-align: left; }
        td { padding: 8px 5px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .price { font-weight: bold; color: #1e40af; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding: 10px 0; }
        .img-placeholder { width: 30px; height: 30px; background: #f3f4f6; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Catalog Report</h1>
        <p>Generated on {{ date('F d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 20%;">Product Details</th>
                <th style="width: 12%;">Style/SKU</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 10%;">Brand</th>
                <th style="width: 8%;">Season</th>
                <th style="width: 8%;">Gender</th>
                <th style="width: 10%; text-align: right;">Cost (৳)</th>
                <th style="width: 10%; text-align: right;">MRP (৳)</th>
                <th style="width: 9%; text-align: center;">Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
                @php
                    $totalVarStock = $product->total_stock_variation ?? 0;
                    $totalSimpleStock = ($product->total_stock_branch ?? 0) + ($product->total_stock_warehouse ?? 0);
                    $displayStock = $product->has_variations ? $totalVarStock : $totalSimpleStock;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $product->name }}</div>
                        <div style="color: #666; font-size: 8px;">Date: {{ $product->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td>{{ $product->style_number ?? $product->sku }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ $product->season->name ?? 'ALL' }}</td>
                    <td>{{ $product->gender->name ?? 'ALL' }}</td>
                    <td style="text-align: right;">{{ number_format($product->cost, 2) }}</td>
                    <td style="text-align: right;" class="price">{{ number_format($product->price, 2) }}</td>
                    <td style="text-align: center;">{{ number_format($displayStock, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} IMBD Agency - Inventory Management System | Page {PAGE_NUM}
    </div>
</body>
</html>
