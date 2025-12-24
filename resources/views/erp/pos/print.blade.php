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
        /* Base Styles */
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .receipt-container {
            width: 220pt; /* Fixed width in points for stability (~78mm) */
            margin: 0;
            padding: 10pt;
            box-sizing: border-box;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .header { margin-bottom: 15pt; }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            display: block;
            margin-bottom: 2pt;
        }
        .company-info {
            font-size: 10pt;
            display: block;
            margin-bottom: 2pt;
        }
        .invoice-title {
            margin-top: 10pt;
            font-size: 11pt;
            font-weight: bold;
            display: block;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5pt 0;
            margin-bottom: 10pt;
        }

        .sale-info {
            margin-bottom: 10pt;
            width: 100%;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 2pt 0;
            font-size: 10pt;
        }
        .info-label {
            width: 80pt;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 4pt 2pt;
            font-size: 9pt;
            text-align: center;
        }
        .items-table th { font-weight: bold; background: #f0f0f0; }
        .items-table .text-left { text-align: left; padding-left: 3pt; }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }
        .summary-table td {
            border: 1px solid #000;
            padding: 4pt 5pt;
            font-size: 10pt;
        }
        .summary-label { text-align: right; font-weight: bold; width: 70%; }
        .summary-value { text-align: right; width: 30%; font-weight: bold; }

        .footer { 
            margin-top: 20pt; 
            font-size: 11pt;
            padding-bottom: 30pt;
        }

        .item-sku { font-size: 8pt; display: block; color: #555; }

        /* PDF specific layout */
        @if(isset($action) && $action == 'download')
        body { width: 260pt; }
        .receipt-container { width: 250pt; }
        @endif

        /* Browser Print specific */
        @media print {
            body { width: 80mm; }
            .receipt-container { width: 76mm; padding: 2mm; }
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
                    <th style="width: 10%;">SN</th>
                    <th style="width: 45%;">Product</th>
                    <th style="width: 25%;">S & C</th>
                    <th style="width: 10%;">QTY</th>
                    <th style="width: 10%;">AMT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pos->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">
                        {{ $item->product->name }}
                        <span class="item-sku">{{ $item->product->sku }}</span>
                    </td>
                    <td>
                        @if($item->variation)
                            @php
                                $vals = [];
                                foreach($item->variation->attributeValues as $val) {
                                    $vals[] = $val->value;
                                }
                                echo implode(', ', $vals);
                            @endphp
                        @else
                            ALL
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 0) }}</td>
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
            <tr>
                <td class="summary-label">Delivery Charge</td>
                <td class="summary-value">{{ number_format($pos->delivery ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">VAT & Tax (+)</td>
                <td class="summary-value">{{ number_format($pos->invoice->tax ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Discount (-)</td>
                <td class="summary-value">{{ number_format($pos->discount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Total Amount</td>
                <td class="summary-value bold">{{ number_format($pos->total_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Paid Amount</td>
                <td class="summary-value">{{ number_format($pos->invoice->paid_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Due Amount</td>
                <td class="summary-value">{{ number_format($pos->invoice->due_amount ?? 0, 2) }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer text-center">
            <div>Thank you, come again @ {{ $general_settings->site_title }}</div>
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
