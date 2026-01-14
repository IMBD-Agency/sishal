<!DOCTYPE html>
<html>
<head>
    <title>Sale Return Report</title>
    <style>
        body { font-family: sans-serif; font-size: 8px; margin: 0; padding: 5px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #2d5a4c; font-size: 14px; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 3px 2px; text-align: left; word-wrap: break-word; }
        th { background: #2d5a4c; color: #fff; font-size: 6px; text-transform: uppercase; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        @page { margin: 0.3cm; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sale Return Report</h2>
        <p>Report Type: {{ ucfirst($reportType) }} | Period: 
            {{ $startDate ? $startDate->format('d/m/Y') : 'All Time' }} - {{ $endDate ? $endDate->format('d/m/Y') : 'Now' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20px;">SN</th>
                <th style="width: 40px;">Date</th>
                <th style="width: 45px;">R-Inv.</th>
                <th style="width: 45px;">S-Inv.</th>
                <th style="width: 60px;">Customer</th>
                <th style="width: 50px;">Outlet</th>
                <th style="width: 40px;">Cat.</th>
                <th style="width: 40px;">Brand</th>
                <th style="width: 70px;">Product Name</th>
                <th style="width: 45px;">Style No</th>
                <th style="width: 30px;">Color</th>
                <th style="width: 25px;">Size</th>
                <th style="width: 25px;">Qty</th>
                <th style="width: 45px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $gQty = 0; $gAmt = 0;
            @endphp
            @foreach($items as $index => $item)
                @php
                    $return = $item->saleReturn;
                    $product = $item->product;
                    $variation = $item->variation;
                    
                    $color = '-'; $size = '-';
                    if ($variation && $variation->attributeValues) {
                        foreach($variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color')) $color = $val->value;
                            elseif (str_contains($attrName, 'size')) $size = $val->value;
                        }
                    }

                    $gQty += $item->returned_qty;
                    $gAmt += $item->total_price;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/y') : '-' }}</td>
                    <td class="fw-bold">#SR-{{ str_pad($return->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $return->posSale->sale_number ?? '-' }}</td>
                    <td>{{ substr($return->customer->name ?? 'Walk-in', 0, 15) }}</td>
                    <td>{{ $return->branch->name ?? '-' }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ substr($product->name ?? '-', 0, 25) }}</td>
                    <td>{{ $product->style_number ?? '-' }}</td>
                    <td>{{ $color }}</td>
                    <td>{{ $size }}</td>
                    <td class="text-center">{{ $item->returned_qty }}</td>
                    <td class="text-right fw-bold">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background: #f8f9fa;">
                <td colspan="12" class="text-right">GRAND TOTAL</td>
                <td class="text-center">{{ $gQty }}</td>
                <td class="text-right">{{ number_format($gAmt, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
