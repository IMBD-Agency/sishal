@php
    // Include helper functions if needed
    // require_once app_path('Helpers/NumberHelper.php');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>POS Receipt - {{ $pos->sale_number }}</title>
    <style>
        /* Base Styles for 80mm Thermal Printer */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace; /* Classic receipt font */
            font-size: 9pt;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
        }

        .receipt-container {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm 3mm;
            box-sizing: border-box;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .header { margin-bottom: 5mm; }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            display: block;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        .company-info {
            font-size: 8pt;
            display: block;
            margin-bottom: 1mm;
        }
        .invoice-title {
            margin-top: 3mm;
            font-size: 10pt;
            font-weight: bold;
            display: block;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
            margin-bottom: 3mm;
            text-transform: uppercase;
        }

        .sale-info {
            margin-bottom: 3mm;
            width: 100%;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 0.5mm 0;
            font-size: 8pt;
        }
        .info-label {
            width: 25mm;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            border-bottom: 1px dashed #000;
        }
        .items-table th, .items-table td {
            padding: 1.5mm 0.5mm;
            font-size: 8pt;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        .items-table th { 
            font-weight: bold; 
            text-align: right;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        .items-table .text-left { text-align: left; }
        .items-table .text-center { text-align: center; }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1mm;
        }
        .summary-table td {
            padding: 1mm 0;
            font-size: 9pt;
        }
        .summary-label { text-align: right; padding-right: 3mm; width: 70%; }
        .summary-value { text-align: right; width: 30%; font-weight: bold; }
        .total-row td { 
            border-top: 1px dashed #000; 
            font-size: 11pt; 
            padding-top: 2mm;
        }

        .footer { 
            margin-top: 5mm; 
            font-size: 8pt;
            border-top: 1px dashed #000;
            padding-top: 3mm;
            padding-bottom: 10mm;
        }

        .item-sku { font-size: 7pt; display: block; color: #444; }

        /* QR Code integration */
        .qr-code {
            margin-top: 5mm;
            margin-bottom: 2mm;
        }

        /* PDF specific layout */
        @if(isset($action) && $action == 'download')
        body { width: 80mm; }
        .receipt-container { width: 80mm; }
        @endif

        /* Browser Print specific */
        @media print {
            body { width: 80mm; }
            .receipt-container { width: 80mm; padding: 2mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header text-center">
            <span class="company-name">{{ $general_settings->site_title }}</span>
            <span class="company-info">{{ $general_settings->contact_address }}</span>
            <span class="company-info">Tel: {{ $general_settings->contact_phone }}</span>
            <span class="invoice-title">IN POS SALE INVOICE COPY</span>
        </div>

        <!-- Sale Details -->
        <div class="sale-info">
            <table class="info-table">
                <tr>
                    <td class="info-label">Date:</td>
                    <td>{{ \Carbon\Carbon::parse($pos->sale_date)->format('d-m-Y | h:i A') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Invoice No.:</td>
                    <td>{{ $pos->sale_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Payment Mode:</td>
                    <td>
                        @php
                            $pm = 'Cash In Hand';
                            if($pos->invoice && $pos->invoice->payments->count() > 0) {
                                $pm = $pos->invoice->payments->first()->payment_method ?? 'Cash In Hand';
                            }
                        @endphp
                        {{ $pm }}
                        @if($pos->branch)
                            ({{ strtoupper($pos->branch->name) }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Customer:</td>
                    <td>{{ $pos->customer ? $pos->customer->name : 'Admin' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Mobile:</td>
                    <td>{{ $pos->customer ? $pos->customer->phone : '' }}</td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left">Item Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pos->items as $item)
                <tr>
                    <td class="text-left">
                        {{ $item->product->name }}
                        @if($item->variation)
                            @php
                                $vals = [];
                                foreach($item->variation->attributeValues as $val) {
                                    $vals[] = $val->value;
                                }
                                echo '<span class="item-sku">[' . implode(', ', $vals) . ']</span>';
                            @endphp
                        @endif
                        <span class="item-sku">SKU: {{ $item->product->sku }}</span>
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td>{{ number_format($item->total_price, 1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Table -->
        <table class="summary-table">
            <tr>
                <td class="summary-label">Sub Total</td>
                <td class="summary-value">{{ number_format($pos->sub_total ?? 0, 2) }}</td>
            </tr>
            @if($pos->delivery > 0)
            <tr>
                <td class="summary-label">Delivery Charge</td>
                <td class="summary-value">{{ number_format($pos->delivery ?? 0, 2) }}</td>
            </tr>
            @endif
            @if(($pos->invoice->tax ?? 0) > 0)
            <tr>
                <td class="summary-label">VAT & Tax (+)</td>
                <td class="summary-value">{{ number_format($pos->invoice->tax ?? 0, 2) }}</td>
            </tr>
            @endif
            @if($pos->discount > 0)
            <tr>
                <td class="summary-label">Discount (-)</td>
                <td class="summary-value">{{ number_format($pos->discount ?? 0, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="summary-label">NET PAYABLE</td>
                <td class="summary-value bold">{{ number_format($pos->total_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Cash Received</td>
                <td class="summary-value">{{ number_format($pos->invoice->paid_amount ?? 0, 2) }}</td>
            </tr>
            @php
                $paid = $pos->invoice->paid_amount ?? 0;
                $total = $pos->total_amount ?? 0;
                $diff = $paid - $total;
            @endphp
            @if($diff > 0)
            <tr>
                <td class="summary-label">Change Return</td>
                <td class="summary-value">{{ number_format($diff, 2) }}</td>
            </tr>
            @elseif($diff < 0)
            <tr>
                <td class="summary-label">Due Amount</td>
                <td class="summary-value" style="color: #d32f2f;">{{ number_format(abs($diff), 2) }}</td>
            </tr>
            @endif
        </table>

        <!-- Footer -->
        <div class="footer text-center">
            @if(isset($qrCodeSvg))
                <div class="qr-code">
                    {!! $qrCodeSvg !!}
                    <div style="font-size: 7pt; margin-top: 2mm;">Scan to verify invoice</div>
                </div>
            @endif
            <div class="bold">Thank you for shopping with us!</div>
            <div>Powered by {{ $general_settings->site_title }}</div>
        </div>
    </div>

    @if(!isset($action) || $action == 'print')
    <script>
        window.onload = () => {
            window.print();
            setTimeout(() => {
                window.close();
            }, 700);
        }
    </script>
    @endif
</body>
</html>
