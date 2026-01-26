<!DOCTYPE html>
<html>
<head>
    <title>Purchase Audit Report</title>
    <style>
        @page { margin: 10px; size: A4 landscape; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8.5px; color: #333; line-height: 1.2; }
        .header { text-align: center; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #444; }
        .header h1 { margin: 0; color: #1a73e8; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .summary-box { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #e0e0e0; }
        .summary-table { width: 100%; border: none; }
        .summary-table td { border: none; padding: 2px; font-size: 10px; }
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        table.main-table th { background-color: #f1f3f4; color: #202124; font-weight: bold; text-transform: uppercase; font-size: 7.5px; border: 1px solid #bdc1c6; padding: 4px 2px; }
        table.main-table td { border: 1px solid #dadce0; padding: 3px 2px; word-wrap: break-word; vertical-align: middle; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-primary { color: #1a73e8; }
        .badge { padding: 2px 4px; border-radius: 8px; font-size: 7px; font-weight: bold; }
        .bg-success { background: #e6f4ea; color: #137333; }
        .bg-warning { background: #fef7e0; color: #b06000; }
        .bg-danger { background: #fce8e6; color: #c5221f; }
        .small-code { font-family: monospace; font-size: 7.5px; color: #d93025; }
        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #70757a; border-top: 1px solid #dadce0; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Audit Report</h1>
        <p>Complete Forensic Procurement Data Sync</p>
        <p>Generated: {{ date('F d, Y | h:i A') }}</p>
    </div>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td width="20%"><strong>Lines:</strong> {{ $items->count() }}</td>
                <td width="20%"><strong>Pur. Qty:</strong> {{ number_format($items->sum('quantity'), 2) }}</td>
                <td width="20%"><strong>Ret. Qty:</strong> {{ number_format($items->sum(fn($i) => $i->returnItems->sum('returned_qty')), 2) }}</td>
                <td width="20%"><strong>Act. Qty:</strong> {{ number_format($items->sum(fn($i) => $i->quantity - $i->returnItems->sum('returned_qty')), 2) }}</td>
                <td width="20%" class="text-end"><strong>Net Value:</strong> <span class="text-primary fw-bold">৳{{ number_format($items->sum(fn($i) => $i->total_price - $i->returnItems->sum('total_price')), 2) }}</span></td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="2.5%">SL</th>
                <th width="7.5%">Inv #</th>
                <th width="10%">Supplier</th>
                <th width="5%">Cat.</th>
                <th width="5%">Brand</th>
                <th width="15%">Product</th>
                <th width="8%">Style</th>
                <th width="4%">Clr</th>
                <th width="4%">Size</th>
                <th width="5%">P.Qty</th>
                <th width="7%">P.Val</th>
                <th width="5%">R.Qty</th>
                <th width="7%">R.Val</th>
                <th width="5%">A.Qty</th>
                <th width="7%">A.Val</th>
                <th width="5%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $purchase = $item->purchase;
                    $bill = $purchase->bill;
                    $product = $item->product;
                    $variation = $item->variation;
                    
                    $color = '-'; $size = '-';
                    if ($variation && $variation->attributeValues) {
                        foreach($variation->attributeValues as $val) {
                            $attrName = strtolower($val->attribute->name ?? '');
                            if (str_contains($attrName, 'color') || (isset($val->attribute) && $val->attribute->is_color)) {
                                $color = $val->value;
                            } elseif (str_contains($attrName, 'size')) {
                                $size = $val->value;
                            }
                        }
                    }

                    $retQty = $item->returnItems->sum('returned_qty');
                    $retAmt = $item->returnItems->sum('total_price');
                    $actQty = $item->quantity - $retQty;
                    $actAmt = $item->total_price - $retAmt;

                    $statusClass = match($purchase->status) {
                        'received' => 'bg-success',
                        'pending' => 'bg-warning',
                        default => 'bg-secondary'
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold text-primary">{{ $bill->bill_number ?? 'PUR-'.$purchase->id }}</td>
                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $product->category->name ?? '-' }}</td>
                    <td class="text-center">{{ $product->brand->name ?? '-' }}</td>
                    <td class="fw-bold">{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center small-code">{{ $item->product->sku ?? $item->product->style_number ?? '-' }}</td>
                    <td class="text-center">{{ $color }}</td>
                    <td class="text-center">{{ $size }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                    <td class="text-center text-danger">{{ number_format($retQty, 0) }}</td>
                    <td class="text-end text-danger">{{ number_format($retAmt, 2) }}</td>
                    <td class="text-center fw-bold text-success">{{ number_format($actQty, 0) }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($actAmt, 2) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ ucfirst($purchase->status) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential Document | Printed by: {{ auth()->user()->name ?? 'System' }} | &copy; {{ date('Y') }} ERP Logic Suite
    </div>
</body>
</html>
