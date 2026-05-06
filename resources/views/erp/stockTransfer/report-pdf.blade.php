<!DOCTYPE html>
<html>
<head>
    <title>Detailed Stock Transfer Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; font-size: 20px; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        .summary-box { margin-bottom: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 4px; text-align: left; word-wrap: break-word; }
        th { background-color: #f1f3f5; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .status-badge { padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detailed Stock Transfer Report</h1>
        <p>Report Period: {{ date('d M Y') }} | Generated: {{ date('h:i A') }}</p>
    </div>

    <div class="summary-box">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none;">Total Records: <strong>{{ $transfers->count() }}</strong></td>
                <td style="border: none;" class="text-right">Total Transferred Qty: <strong>{{ number_format($transfers->sum('quantity'), 0) }}</strong></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Invoice No</th>
                <th style="width: 7%;">Date</th>
                <th style="width: 8%;">Source</th>
                <th style="width: 8%;">Destination</th>
                <th style="width: 8%;">Branch</th>
                <th style="width: 8%;">Category</th>
                <th style="width: 8%;">Brand</th>
                <th style="width: 12%;">Product Name</th>
                <th style="width: 8%;">Style #</th>
                <th style="width: 6%;">Color</th>
                <th style="width: 5%;">Size</th>
                <th style="width: 4%;" class="text-center">Qty</th>
                <th style="width: 7%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $transfer)
                @php
                    $product = $transfer->product;
                    $variation = $transfer->variation;
                    
                    $color = '-'; $size = '-';
                    if ($variation && $variation->attributeValues) {
                        foreach($variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) $color = $val->value;
                            elseif (str_contains($attrName, 'size')) $size = $val->value;
                        }
                    }
                @endphp
                <tr>
                    <td class="fw-bold">{{ $transfer->invoice_number ?? 'N/A' }}</td>
                    <td class="text-center">{{ $transfer->requested_at ? \Carbon\Carbon::parse($transfer->requested_at)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $transfer->from_type == 'branch' ? ($transfer->fromBranch->name ?? '-') : ($transfer->fromWarehouse->name ?? '-') }}</td>
                    <td>{{ $transfer->to_type == 'branch' ? ($transfer->toBranch->name ?? '-') : ($transfer->toWarehouse->name ?? '-') }}</td>
                    <td>{{ $transfer->to_type == 'branch' ? ($transfer->toBranch->name ?? '-') : ($transfer->toWarehouse->name ?? '-') }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ $product->name ?? '-' }}</td>
                    <td>{{ $product->style_number ?? $product->sku ?? '-' }}</td>
                    <td class="text-center">{{ $color }}</td>
                    <td class="text-center">{{ $size }}</td>
                    <td class="text-center fw-bold">{{ number_format($transfer->quantity, 0) }}</td>
                    <td class="text-center">
                        <span class="status-badge">{{ strtoupper($transfer->status) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. This report is computer generated.
    </div>
</body>
</html>
