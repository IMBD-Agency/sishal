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
                <th>Branch</th>
                <th>Customer</th>
                <th>Product Name</th>
                <th>Style #</th>
                <th>Color</th>
                <th>Size</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Exchange</th>
                <th class="text-end">Refund</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Due</th>
            </tr>
</thead>
        <tbody>
            @php 
                $tEx = 0; $tRef = 0; $tDisc = 0; $tPaid = 0; $tDue = 0;
            @endphp
            @foreach($items as $index => $exchange)
                @php
                    $originalSale = $exchange->originalPos;
                @endphp
                @foreach($exchange->items as $i => $item)
                    @php
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

                        $isFirst = ($i == 0);
                        if($isFirst) {
                            $tEx += $exchange->total_new_amount;
                            $tRef += $exchange->refund_amount;
                            $tDisc += $exchange->discount_amount;
                            $tPaid += $exchange->extra_payable;
                            $tDue += 0;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}{{ $isFirst ? '' : '.'.($i+1) }}</td>
                        <td class="fw-bold">{{ $exchange->exchange_number }}</td>
                        <td>{{ $originalSale->sale_number ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($exchange->exchange_date)->format('d/m/Y') }}</td>
                        <td>{{ $exchange->branch->name ?? '-' }}</td>
                        <td>{{ $exchange->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->style_number }}</td>
                        <td>{{ $color }}</td>
                        <td>{{ $size }}</td>
                        <td class="text-center">{{ $item->quantity }} <br>({{ ucfirst($item->type) }})</td>
                        <td class="text-end">{{ $isFirst ? number_format($exchange->total_new_amount, 2) : '' }}</td>
                        <td class="text-end">{{ $isFirst ? number_format($exchange->refund_amount, 2) : '' }}</td>
                        <td class="text-end">{{ $isFirst ? number_format($exchange->discount_amount, 2) : '' }}</td>
                        <td class="text-end">{{ $isFirst ? number_format($exchange->extra_payable, 2) : '' }}</td>
                        <td class="text-end">{{ $isFirst ? '0.00' : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot class="bg-light">
            <tr class="fw-bold">
                <td colspan="11" class="text-end">Grand Total</td>
                <td class="text-end">{{ number_format($tEx, 2) }}</td>
                <td class="text-end">{{ number_format($tRef, 2) }}</td>
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
