<!DOCTYPE html>
<html>
<head>
    <title>Purchase Return Audit Report</title>
    <style>
        @page { margin: 10px; size: A4 landscape; }
        body { font-family: 'Helvetica', sans-serif; font-size: 8.5px; color: #333; line-height: 1.2; }
        .header { text-align: center; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #e74c3c; }
        .header h1 { margin: 0; color: #c0392b; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #666; font-size: 10px; }
        .summary-box { background: #fff5f5; padding: 10px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #feb2b2; }
        .summary-table { width: 100%; border: none; }
        .summary-table td { border: none; padding: 2px; font-size: 10px; }
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        table.main-table th { background-color: #fce8e6; color: #202124; font-weight: bold; text-transform: uppercase; font-size: 7.5px; border: 1px solid #feb2b2; padding: 4px 2px; }
        table.main-table td { border: 1px solid #fed7d7; padding: 3px 2px; word-wrap: break-word; vertical-align: middle; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #c0392b; }
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
        <h1>Purchase Return Audit</h1>
        <p>Reverse Procurement & Asset Reversal Intelligence</p>
        <p>Generated: {{ date('F d, Y | h:i A') }}</p>
    </div>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td width="20%"><strong>Lines Tracked:</strong> {{ $items->count() }}</td>
                <td width="20%"><strong>Total Ret. Qty:</strong> {{ number_format($items->sum('returned_qty'), 2) }}</td>
                <td width="60%" class="text-end"><strong>Total Reversal Value:</strong> <span class="text-danger fw-bold">৳{{ number_format($items->sum('total_price'), 2) }}</span></td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="3%">SL</th>
                <th width="8%">Ret. Date</th>
                <th width="8%">Ret #</th>
                <th width="8%">Orig #</th>
                <th width="10%">Source</th>
                <th width="12%">Supplier</th>
                <th width="6%">Cat.</th>
                <th width="15%">Product Component</th>
                <th width="8%">Style #</th>
                <th width="4%">Clr</th>
                <th width="4%">Size</th>
                <th width="4%">Qty</th>
                <th width="7%">Amount</th>
                <th width="5%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $return = $item->purchaseReturn;
                    if (!$return) continue;
                    
                    $purchase = $return->purchase;
                    $product = $item->product;
                    $variation = $item->purchaseItem ? $item->purchaseItem->variation : null;
                    
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

                    $source = 'N/A';
                    if ($item->return_from_type == 'branch') {
                        $loc = \App\Models\Branch::find($item->return_from_id);
                        $source = 'B: ' . ($loc->name ?? $item->return_from_id);
                    } elseif ($item->return_from_type == 'warehouse') {
                        $loc = \App\Models\Warehouse::find($item->return_from_id);
                        $source = 'W: ' . ($loc->name ?? $item->return_from_id);
                    }

                    $statusClass = match($return->status) {
                        'approved' => 'bg-success',
                        'pending' => 'bg-warning',
                        default => 'bg-secondary'
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d-m-Y', strtotime($return->return_date)) }}</td>
                    <td class="fw-bold text-danger">RET-{{ str_pad($return->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-center fw-bold">#{{ $purchase ? ($purchase->bill->bill_number ?? $purchase->id) : 'N/A' }}</td>
                    <td class="text-center small">{{ $source }}</td>
                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $product->category->name ?? '-' }}</td>
                    <td class="fw-bold">{{ $product->name ?? 'N/A' }}</td>
                    <td class="text-center small-code">{{ $product->sku ?? $product->style_number ?? '-' }}</td>
                    <td class="text-center">{{ $color }}</td>
                    <td class="text-center">{{ $size }}</td>
                    <td class="text-center fw-bold text-danger">{{ number_format($item->returned_qty, 0) }}</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($item->total_price, 2) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ ucfirst($return->status) }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential Asset Reversal Document | Printed by: {{ auth()->user()->name ?? 'System' }} | &copy; {{ date('Y') }} ERP Logic Suite
    </div>
</body>
</html>
