<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            padding: 0;
        }
        
        .invoice-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header {
            border-bottom: 2px solid #000;
            padding: 10px 0 15px 0;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding: 10px 0;
        }
        
        .invoice-info-row {
            display: table-row;
        }
        
        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .invoice-info-right {
            text-align: right;
            padding-right: 0;
            padding-left: 20px;
        }
        
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .info-value {
            margin-bottom: 8px;
        }
        
        .address-section {
            margin-bottom: 20px;
            padding: 10px 0 15px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #000;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .items-table td {
            border: 1px solid #000;
            padding: 10px 8px;
            font-size: 11px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .summary {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
            padding: 10px 0;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .summary-label,
        .summary-value {
            display: table-cell;
            padding: 6px 0;
            font-size: 12px;
        }
        
        .summary-value {
            text-align: right;
            padding-left: 20px;
        }
        
        .summary-row.total {
            border-top: 2px solid #000;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <div class="info-value">
                <strong>Order #:</strong> {{ $order->order_number }}
            </div>
            <div class="info-value">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('F j, Y') }}
            </div>
        </div>
        
        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="invoice-info-row">
                <div class="invoice-info-left">
                    <div class="info-label">Bill To:</div>
                    <div class="info-value">{{ $order->name ?? 'N/A' }}</div>
                    @if($order->email)
                        <div class="info-value">{{ $order->email }}</div>
                    @endif
                    @if($order->phone)
                        <div class="info-value">{{ $order->phone }}</div>
                    @endif
                </div>
                <div class="invoice-info-right">
                    <div class="info-label">Payment Method:</div>
                    <div class="info-value">
                        @if($order->payment_method == 'online-payment')
                            Online Payment
                        @elseif($order->payment_method == 'cash')
                            Cash on Delivery
                        @else
                            {{ ucfirst(str_replace('-', ' ', $order->payment_method ?? 'N/A')) }}
                        @endif
                    </div>
                    <div class="info-value" style="margin-top: 10px;">
                        <div class="info-label">Status:</div>
                        {{ ucfirst($order->status ?? 'Pending') }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shipping Address -->
        @if($order->invoice && $order->invoice->invoiceAddress)
        <div class="address-section">
            <div class="info-label">Shipping Address:</div>
            <div class="info-value">
                {{ $order->invoice->invoiceAddress->shipping_address_1 ?? 'N/A' }}
                @if($order->invoice->invoiceAddress->shipping_address_2)
                    , {{ $order->invoice->invoiceAddress->shipping_address_2 }}
                @endif
                <br>
                {{ $order->invoice->invoiceAddress->shipping_city ?? '' }}
                @if($order->invoice->invoiceAddress->shipping_state), {{ $order->invoice->invoiceAddress->shipping_state }} @endif
                @if($order->invoice->invoiceAddress->shipping_zip_code) - {{ $order->invoice->invoiceAddress->shipping_zip_code }} @endif
                @if($order->invoice->invoiceAddress->shipping_country)
                    <br>{{ $order->invoice->invoiceAddress->shipping_country }}
                @endif
            </div>
        </div>
        @endif
        
        <!-- Order Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item</th>
                    <th style="width: 15%;" class="text-center">Quantity</th>
                    <th style="width: 17.5%;" class="text-right">Unit Price</th>
                    <th style="width: 17.5%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product->name ?? 'Product' }}
                        @if($item->variation)
                            <br><small>{{ $item->variation->name ?? 'N/A' }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}TK</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}TK</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Order Summary -->
        <div class="summary">
            <div class="summary-row">
                <div class="summary-label">Subtotal:</div>
                <div class="summary-value">{{ number_format($order->subtotal ?? 0, 2) }}TK</div>
            </div>
            @if($order->discount > 0)
            <div class="summary-row">
                <div class="summary-label">Discount:</div>
                <div class="summary-value">-{{ number_format($order->discount, 2) }}TK</div>
            </div>
            @endif
            @if($order->vat > 0)
            <div class="summary-row">
                <div class="summary-label">Tax (VAT):</div>
                <div class="summary-value">{{ number_format($order->vat, 2) }}TK</div>
            </div>
            @endif
            @if($order->delivery > 0)
            <div class="summary-row">
                <div class="summary-label">Shipping:</div>
                <div class="summary-value">{{ number_format($order->delivery, 2) }}TK</div>
            </div>
            @endif
            <div class="summary-row total">
                <div class="summary-label">Total Amount:</div>
                <div class="summary-value">{{ number_format($order->total, 2) }}TK</div>
            </div>
            @if($order->invoice && $order->invoice->due_amount > 0)
            <div class="summary-row" style="margin-top: 10px;">
                <div class="summary-label" style="color: #000;">Due Amount:</div>
                <div class="summary-value" style="color: #000;">{{ number_format($order->invoice->due_amount, 2) }}Tk</div>
            </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="footer">
            @if($generalSettings)
                <div>{{ $generalSettings->site_title ?? config('app.name') }}</div>
                @if($generalSettings->contact_email)
                    <div style="margin-top: 5px;">{{ $generalSettings->contact_email }}</div>
                @endif
            @else
                <div>{{ config('app.name') }}</div>
            @endif
        </div>
    </div>
</body>
</html>

