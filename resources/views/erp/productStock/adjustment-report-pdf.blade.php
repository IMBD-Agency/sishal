<!DOCTYPE html>
<html>
<head>
    <title>Stock Adjustment Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #3d6b52; color: white; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Stock Adjustment Report</h2>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Season</th>
                <th>Product Name</th>
                <th>Style No</th>
                <th>Old Qty</th>
                <th>New Qty</th>
                <th>Diff</th>
                <th>By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($adjustments as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->adjustment->adjustment_number }}</td>
                    <td>{{ $item->adjustment->date }}</td>
                    <td>{{ $item->product->category->name ?? '-' }}</td>
                    <td>{{ $item->product->brand->name ?? '-' }}</td>
                    <td>{{ $item->product->season->name ?? '-' }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->style_number }}</td>
                    <td>{{ $item->old_quantity }}</td>
                    <td>{{ $item->new_quantity }}</td>
                    <td>{{ $item->new_quantity - $item->old_quantity }}</td>
                    <td>{{ $item->adjustment->creator->name ?? 'Admin' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page {PAGE_NUM}
    </div>
</body>
</html>
