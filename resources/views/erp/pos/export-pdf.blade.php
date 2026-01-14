<!DOCTYPE html>
<html>
<head>
    <title>POS Sales Report</title>
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
        <h2>POS Sales Report</h2>
        <p>Report Type: {{ ucfirst($reportType) }} | Period: 
            {{ $startDate ? $startDate->format('d/m/Y') : 'All Time' }} - {{ $endDate ? $endDate->format('d/m/Y') : 'Now' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15px;">SN</th>
                <th style="width: 35px;">Invoice</th>
                <th style="width: 35px;">Date</th>
                <th style="width: 45px;">Customer</th>
                <th style="width: 30px;">Created</th>
                <th style="width: 30px;">Cat.</th>
                <th style="width: 30px;">Brand</th>
                <th style="width: 45px;">Product</th>
                <th style="width: 30px;">Style</th>
                <th style="width: 25px;">Color</th>
                <th style="width: 20px;">Size</th>
                <th style="width: 20px;">Qty</th>
                <th style="width: 35px;">Amt.</th>
                <th style="width: 20px;">SR-Q</th>
                <th style="width: 35px;">SR-A</th>
                <th style="width: 20px;">AS-Q</th>
                <th style="width: 30px;">Deliv.</th>
                <th style="width: 30px;">Disc.</th>
                <th style="width: 30px;">Exch.</th>
                <th style="width: 35px;">AS-A</th>
                <th style="width: 40px;">Total</th>
                <th style="width: 35px;">Paid</th>
                <th style="width: 35px;">Due</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $gSellQty = 0; $gSellAmt = 0; $gRetQty = 0; $gRetAmt = 0;
                $gActQty = 0; $gActAmt = 0; $gDelivery = 0; $gDiscount = 0;
                $gExchange = 0; $gFinalTotal = 0; $gReceived = 0; $gDue = 0;
            @endphp
            @foreach($items as $index => $item)
                @php
                    $sale = $item->pos;
                    $invoice = $sale->invoice;
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

                    $retQty = $item->returnItems->sum('returned_qty');
                    $retAmt = $item->returnItems->sum('total_price');
                    $actualQty = $item->quantity - $retQty;
                    $actualAmt = $item->total_price - $retAmt;

                    $gSellQty += $item->quantity; $gSellAmt += $item->total_price;
                    $gRetQty += $retQty; $gRetAmt += $retAmt;
                    $gActQty += $actualQty; $gActAmt += $actualAmt;

                    $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);
                    if ($isFirst) {
                        $gDelivery += $sale->delivery;
                        $gDiscount += $sale->discount;
                        $gExchange += ($sale->exchange_amount ?? 0);
                        $gFinalTotal += $sale->total_amount;
                        $gReceived += ($invoice->paid_amount ?? 0);
                        $gDue += ($invoice->due_amount ?? 0);
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $sale->sale_number ?? '-' }}</td>
                    <td class="text-center">{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/y') : '-' }}</td>
                    <td>{{ substr($sale->customer->name ?? 'Walk-in', 0, 15) }}</td>
                    <td>{{ substr($sale->soldBy->name ?? '-', 0, 10) }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ substr($product->name ?? '-', 0, 20) }}</td>
                    <td>{{ $product->style_number ?? '-' }}</td>
                    <td>{{ $color }}</td>
                    <td>{{ $size }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                    <td class="text-center">{{ $retQty ?: 0 }}</td>
                    <td class="text-right">{{ number_format($retAmt, 2) }}</td>
                    <td class="text-center">{{ $actualQty }}</td>
                    <td class="text-right">{{ $isFirst ? number_format($sale->delivery, 2) : '-' }}</td>
                    <td class="text-right">{{ $isFirst ? number_format($sale->discount, 2) : '-' }}</td>
                    <td class="text-right">{{ $isFirst ? number_format($sale->exchange_amount ?? 0, 2) : '-' }}</td>
                    <td class="text-right fw-bold">{{ number_format($actualAmt, 2) }}</td>
                    <td class="text-right fw-bold">{{ $isFirst ? number_format($sale->total_amount, 2) : '-' }}</td>
                    <td class="text-right">{{ $isFirst ? number_format($invoice->paid_amount ?? 0, 2) : '-' }}</td>
                    <td class="text-right">{{ $isFirst ? number_format($invoice->due_amount ?? 0, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background: #f8f9fa;">
                <td colspan="11" class="text-right">TOTAL</td>
                <td class="text-center">{{ $gSellQty }}</td>
                <td class="text-right">{{ number_format($gSellAmt, 2) }}</td>
                <td class="text-center">{{ $gRetQty }}</td>
                <td class="text-right">{{ number_format($gRetAmt, 2) }}</td>
                <td class="text-center">{{ $gActQty }}</td>
                <td class="text-right">{{ number_format($gDelivery, 2) }}</td>
                <td class="text-right">{{ number_format($gDiscount, 2) }}</td>
                <td class="text-right">{{ number_format($gExchange, 2) }}</td>
                <td class="text-right">{{ number_format($gActAmt, 2) }}</td>
                <td class="text-right">{{ number_format($gFinalTotal, 2) }}</td>
                <td class="text-right">{{ number_format($gReceived, 2) }}</td>
                <td class="text-right">{{ number_format($gDue, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
