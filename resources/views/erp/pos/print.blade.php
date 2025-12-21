@php
    // Include helper functions
    require_once app_path('Helpers/NumberHelper.php');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Receipt - {{ $pos->sale_number }}</title>
    <style>
        /* === PRINT SAFE AREA === */
        @page {
            size: A4;
            margin: 15mm 20mm 15mm 20mm;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
            background: #fff;
            overflow: visible !important;
            -webkit-print-color-adjust: exact;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #222;
        }

        .receipt-box {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #222;
            padding: 24px 32px;
            background: #fff;
            page-break-after: avoid;
            page-break-before: avoid;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .header .logo {
            width: 120px;
        }

        .header .receipt-title {
            text-align: right;
            color: #007bff;
            font-size: 2rem;
            font-weight: 400;
        }

        .barcode {
            text-align: right;
            margin-top: 8px;
        }

        .info-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .info-table td {
            padding: 2px 0;
            font-size: 14px;
        }

        .items-table th, .items-table td {
            border: 1px solid #222;
            padding: 6px 8px;
            text-align: center;
            font-size: 10px;
        }

        .items-table th {
            background: #f5f5f5;
        }

        .summary-table {
            width: 40%;
            float: right;
            margin-top: 16px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 4px 8px;
            font-size: 10px;
        }

        .summary-table tr td:first-child {
            text-align: right;
        }

        .summary-table tr td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .inword, .bill-note, .footer, .sign-row {
            margin-top: 16px;
            font-size: 12px;
        }

        .footer {
            color: #444;
            margin-top: 24px;
        }

        .sign-row {
            margin-top: 48px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .receipt-box {
                border: none;
                box-shadow: none;
                margin: 0 auto;
                padding: 24px 32px;
                page-break-inside: avoid;
            }

            .header,
            .barcode,
            .info-table,
            .items-table,
            .summary-table,
            .inword,
            .bill-note,
            .footer,
            .sign-row {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="header">
            <div>
                <img src="{{ asset($general_settings->site_logo) }}" alt="{{ $general_settings->site_title }}" class="logo" style="width: 60px;">
            </div>
            <div>
                <div class="receipt-title">POS RECEIPT</div>
            </div>
        </div>

        <div class="header" style="margin-top: 4px;">
            <div style="font-size: 14px; max-width: 300px;">
                {{ $general_settings->contact_address }} <br>
                Phone: <a style="color: black; text-decoration: none;" href="callto:{{ $general_settings->contact_phone }}">{{ $general_settings->contact_phone }}</a><br>
                Email: <a style="color: black; text-decoration: none;" href="mailto:{{ $general_settings->contact_email }}">{{ $general_settings->contact_email }}</a><br>
                Website: <a style="color: black; text-decoration: none;" target="_blank" href="{{ $general_settings->website_url ?? '#' }}">{{ $general_settings->website_url ?? 'N/A' }}</a>
            </div>
            <div class="barcode">{!! $qrCodeSvg !!}</div>
        </div>

        <table class="info-table">
            <tr>
                <td><strong>Customer Info.</strong></td>
                <td style="text-align:right;"><strong>SALE #</strong> {{ $pos->sale_number }}</td>
            </tr>
            <tr>
                <td>{{ $pos->customer ? $pos->customer->name : 'Walk-in Customer' }}</td>
                <td style="text-align:right;">DATE: {{ \Carbon\Carbon::parse($pos->sale_date)->format('d M, Y') }}</td>
            </tr>
            @if($pos->customer)
            <tr>
                <td>Phone: {{ $pos->customer->phone }}</td>
            </tr>
            @endif
            @if($pos->branch)
            <tr>
                <td>Branch: {{ $pos->branch->name }}</td>
            </tr>
            @endif
        </table>

        <table class="items-table" style="margin-top: 18px;">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>DESCRIPTION</th>
                    <th>QUANTITY</th>
                    <th>UNIT</th>
                    <th>PRICE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pos->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="text-align:left;">
                        {{ $item->product->name }}
                        @if($item->variation)
                            <br><small style="color: #666;">Variation: {{ $item->variation->name ?? $item->variation->sku }}</small>
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 0) }}</td>
                    <td>PCS</td>
                    <td>{{ number_format($item->unit_price, 2) }} Tk</td>
                    <td>{{ number_format($item->total_price, 2) }} Tk</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr><td>SUB TOTAL :</td><td>{{ number_format($pos->sub_total ?? 0, 2) }} Tk</td></tr>
            <tr><td>DISCOUNT :</td><td>{{ number_format($pos->discount ?? 0, 2) }} Tk</td></tr>
            @if($pos->invoice && $pos->invoice->tax)
            <tr><td>VAT :</td><td>{{ number_format($pos->invoice->tax ?? 0, 2) }} Tk</td></tr>
            @endif
            @if($pos->delivery > 0)
            <tr><td>DELIVERY :</td><td>{{ number_format($pos->delivery, 2) }} Tk</td></tr>
            @endif
            <tr><td>NET BILL :</td><td>{{ number_format($pos->total_amount ?? 0, 2) }} Tk</td></tr>
            @if($pos->invoice)
            <tr><td>PAID :</td><td>{{ number_format($pos->invoice->paid_amount ?? 0, 2) }} Tk</td></tr>
            <tr><td>DUE :</td><td>{{ number_format($pos->invoice->due_amount ?? 0, 2) }} Tk</td></tr>
            @endif
        </table>

        <div style="clear:both;"></div>

        <div class="inword"><strong>IN-WORD :</strong> {{ numberToWords($pos->total_amount ?? 0) }}</div>
        @if($pos->notes)
        <div class="bill-note"><strong>NOTE :</strong> {{ $pos->notes }}</div>
        @endif
        @if($pos->invoice && $pos->invoice->payments->count() > 0)
        <p style="margin-top: 10px;"><b>- Payment Method :</b> {{ $pos->invoice->payments->first()->payment_method ?? 'Not specified' }}</p>
        @endif

        @if($template)
        <div class="footer">
            {!! $template->footer_note !!}
        </div>
        @endif

        <div class="sign-row">
            <div style="border-top: 1px dotted black; padding-top: 10px;">Received By</div>
            <div style="border-top: 1px dotted black; padding-top: 10px;">Sales Person : {{ $pos->soldBy ? $pos->soldBy->name : 'N/A' }}</div>
        </div>
    </div>
</body>
</html>

@if($action == 'print' || (isset($action) && $action == 'print'))
<script>
    window.onload = () => window.print();
    window.onafterprint = () => window.close();
</script>
@endif

