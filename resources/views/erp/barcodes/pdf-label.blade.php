<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Barcode Labels - {{ $sku }}</title>
    <style>
        @page {
            size: 108pt 71pt; /* Approx 38mm x 25mm */
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: white;
            width: 108pt;
            height: 71pt;
        }

        .label-page {
            width: 108pt;
            height: 71pt;
            padding: 4pt;
            text-align: center;
            display: block;
            position: relative;
            page-break-after: always;
        }

        .product-name {
            font-size: 7pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 2pt;
            height: 16pt;
            overflow: hidden;
            line-height: 8pt;
            text-align: center;
        }

        .barcode-container {
            width: 100%;
            height: 28pt;
            text-align: center;
            margin-bottom: 1pt;
        }

        .barcode-container img {
            width: 95%;
            height: 28pt;
        }

        .sku-text {
            font-size: 7pt;
            font-weight: bold;
            font-family: 'Courier', monospace;
            margin-bottom: 1pt;
            text-align: center;
        }

        .price-text {
            border-top: 0.5pt solid #000;
            width: 100%;
            text-align: center;
            padding-top: 1pt;
            font-size: 9pt;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $quantity; $i++)
        <div class="label-page">
            <div class="product-name">{{ $name }}</div>
            <div class="barcode-container">
                <img src="{{ $barcodeBase64 }}" alt="Barcode">
            </div>
            <div class="sku-text">{{ strtoupper($sku) }}</div>
            <div class="price-text">MRP: ৳{{ number_format($price, 2) }}</div>
        </div>
    @endfor
</body>
</html>
