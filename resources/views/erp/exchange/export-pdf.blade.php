<!DOCTYPE html>
<html>
<head>
    <title>Exchange Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #2d5a4c; color: white; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .bg-light { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Exchange Report</h2>
        <p>Period: {{ $startDate ? $startDate->format('d/m/Y') : 'All' }} - {{ $endDate ? $endDate->format('d/m/Y') : 'All' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Ex. Invoice</th>
                <th>Sale Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Product Name</th>
                <th>Style #</th>
                <th>Color</th>
                <th>Size</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Exchange</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Due</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $tEx = 0; $tDisc = 0; $tPaid = 0; $tDue = 0;
            @endphp
            @foreach($items as $index => $item)
                @php
                    $sale = $item->pos;
                    $product = $item->product;
                    $variation = $item->variation;
                    $invoice = $sale->invoice;

                    $color = '-'; $size = '-';
                    if ($variation && $variation->attributeValues) {
                        foreach($variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color')) $color = $val->value;
                            elseif (str_contains($attrName, 'size')) $size = $val->value;
                        }
                    }

                    $isFirst = ($index == 0 || $items[$index-1]->pos_sale_id != $item->pos_sale_id);
                    if($isFirst) {
                        $tEx += $sale->exchange_amount;
                        $tDisc += $sale->discount;
                        $tPaid += ($invoice->paid_amount ?? 0);
                        $tDue += ($invoice->due_amount ?? 0);
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $sale->sale_number }}</td>
                    <td>{{ $sale->originalPos->sale_number ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->style_number }}</td>
                    <td>{{ $color }}</td>
                    <td>{{ $size }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $isFirst ? number_format($sale->exchange_amount, 2) : '' }}</td>
                    <td class="text-end">{{ $isFirst ? number_format($sale->discount, 2) : '' }}</td>
                    <td class="text-end">{{ $isFirst ? number_format($invoice->paid_amount ?? 0, 2) : '' }}</td>
                    <td class="text-end">{{ $isFirst ? number_format($invoice->due_amount ?? 0, 2) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-light">
            <tr class="fw-bold">
                <td colspan="10" class="text-end">Grand Total</td>
                <td class="text-end">{{ number_format($tEx, 2) }}</td>
                <td class="text-end">{{ number_format($tDisc, 2) }}</td>
                <td class="text-end">{{ number_format($tPaid, 2) }}</td>
                <td class="text-end">{{ number_format($tDue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Printed on: {{ date('d/m/Y H:i A') }}
    </div>
</body>
</html>
