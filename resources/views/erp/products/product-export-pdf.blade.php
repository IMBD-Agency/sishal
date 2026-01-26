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
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Product Details</th>
                <th style="width: 15%;">Style / SKU</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 15%;">Brand</th>
                <th style="width: 15%; text-align: right;">MRP (৳)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: bold; font-size: 12px;">{{ $product->name }}</div>
                        <div style="color: #666; font-size: 9px;">Entry: {{ $product->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td>{{ $product->style_number ?? $product->sku }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td style="text-align: right;" class="price">{{ number_format($product->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} IMBD Agency - Inventory Management System | Page {PAGE_NUM}
    </div>
</body>
</html>
