@php
    // Include helper functions
    require_once app_path('Helpers/NumberHelper.php');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Print</title>
    <style>
        /* === PRINT SAFE AREA === */
        @page {
            size: A4;
            margin: 15mm 20mm 15mm 20mm; /* ✅ fixed: keeps right padding safe */
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

        .invoice-box {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #222;
            padding: 24px 32px; /* ✅ restored padding */
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

        .header .invoice-title {
            text-align: right;
            color: #007bff;
            font-size: 2rem;
            font-weight: 400;
        }

        .barcode {
            text-align: right;
            margin-top: 8px;
        }

        .barcode img {
            height: 40px;
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

        /* === PRINT MODE === */
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .invoice-box {
                border: none;
                box-shadow: none;
                margin: 0 auto;
                padding: 24px 32px; /* ✅ keep inner padding on print */
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
    <div class="invoice-box">
        <div class="header">
            <div>
                <img src="{{ asset($general_settings->site_logo) }}" alt="{{ $general_settings->site_title }}" class="logo" style="width: 60px;">
            </div>
            <div>
                <div class="invoice-title">INVOICE</div>
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
                <td style="text-align:right;"><strong>INVOICE #</strong> {{$invoice->invoice_number}}</td>
            </tr>
            <tr>
                <td>{{ @$invoice->customer->name }}</td>
                <td style="text-align:right;">DATE: {{$invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y, h:i A') : ''}}</td>
            </tr>
            <tr>
                <td>Phone: {{ @$invoice->customer->phone }}</td>
            </tr>
            @php
                $addressParts = [];
                if (!empty($invoice->invoiceAddress->billing_address_1)) $addressParts[] = $invoice->invoiceAddress->billing_address_1;
                if (!empty($invoice->invoiceAddress->billing_address_2)) $addressParts[] = $invoice->invoiceAddress->billing_address_2;
                if (!empty($invoice->invoiceAddress->billing_city)) $addressParts[] = $invoice->invoiceAddress->billing_city;
                if (!empty($invoice->invoiceAddress->billing_state)) $addressParts[] = $invoice->invoiceAddress->billing_state;
                if (!empty($invoice->invoiceAddress->billing_zip_code)) $addressParts[] = $invoice->invoiceAddress->billing_zip_code;
            @endphp
            <tr>
                <td>Address: {{ implode(', ', $addressParts) }}</td>
            </tr>
        </table>

        <table class="items-table" style="margin-top: 18px;">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>DESCRIPTION</th>
                    <th>QUANTITY</th>
                    <th>UNIT</th>
                    <th>PRICE</th>
                    <th>DISCOUNT</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                @if(@$item->product->type == 'product')
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="text-align:left;">
                        {{ @$item->product->name }}
                        @if($item->variation)
                            <br><small style="color: #666;">Variation: {{ $item->variation->name ?? $item->variation->sku }}</small>
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 0) }}</td>
                    <td>PCS</td>
                    <td>{{ @$item->unit_price }} Tk</td>
                    @php
                        $originalPrice = @$item->product->discount ?? @$item->product->price;
                        $unitPrice = @$item->unit_price;
                        $discountPercent = ($originalPrice && $originalPrice > 0 && $unitPrice < $originalPrice)
                            ? number_format((($originalPrice - $unitPrice) / $originalPrice) * 100, 2)
                            : '0.00';
                    @endphp
                    <td>{{ $discountPercent }}%</td>
                    <td>{{ @$item->total_price }} Tk</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr><td>SUB TOTAL :</td><td>{{ number_format($invoice->subtotal ?? 0, 2) }} Tk</td></tr>
            <tr><td>DIS. AMOUNT :</td><td>{{ number_format($invoice->discount_apply ?? 0, 2) }} Tk</td></tr>
            <tr><td>VAT :</td><td>{{ number_format($invoice->tax ?? 0, 2) }} Tk</td></tr>
            @php $onlineDelivery = isset($order) ? ($order->delivery ?? 0) : 0; @endphp
            @if(($onlineDelivery ?? 0) > 0)
                <tr><td>DELIVERY :</td><td>{{ number_format($onlineDelivery, 2) }} Tk</td></tr>
            @elseif(optional($invoice->pos)->delivery && optional($invoice->pos)->delivery > 0)
                <tr><td>DELIVERY :</td><td>{{ number_format($invoice->pos->delivery, 2) }} Tk</td></tr>
            @endif
            <tr><td>NET BILL :</td><td>{{ number_format($invoice->total_amount ?? 0, 2) }} Tk</td></tr>
            <tr><td>ADVANCE :</td><td>{{ number_format($invoice->paid_amount ?? 0, 2) }} Tk</td></tr>
            <tr><td>DUE :</td><td>{{ number_format($invoice->due_amount ?? 0, 2) }} Tk</td></tr>
        </table>

        <div style="clear:both;"></div>

        <div class="inword"><strong>IN-WORD :</strong> {{ numberToWords($invoice->total_amount ?? 0) }}</div>
        <div class="bill-note"><strong>BILL NOTE :</strong> {{ $invoice->note }}</div>
        <p style="margin-top: 10px;"><b>- Payment Method :</b> {{ @$invoice->payments->first()->payment_method ?? 'Not specified' }}</p>

        <div class="footer">
            {!! $invoice->footer_text ?? $template->footer_note !!}
        </div>

        <div class="sign-row">
            <div style="border-top: 1px dotted black; padding-top: 10px;">Received By</div>
            <div style="border-top: 1px dotted black; padding-top: 10px;">Sales Person : {{ @$invoice->salesman->first_name }} {{ @$invoice->salesman->last_name }}</div>
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
