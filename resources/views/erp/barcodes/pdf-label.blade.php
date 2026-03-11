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
            width: 38mm;
            height: 25mm;
            color: #000;
        }

        .label-page {
            width: 38mm;
            height: 25mm;
            padding: 1mm;
            text-align: center;
            display: block;
            position: relative;
            page-break-after: always;
        }

        .barcode-container {
            width: 100%;
            height: 28pt;
            text-align: center;
            margin-bottom: 1pt;
            display: block;
        }

        .barcode-img-box {
            width: 85%;
            height: 22pt;
            margin: 0 auto;
        }

        .barcode-img-box img {
            width: 100%;
            height: 100%;
        }

        .sku-text {
            font-size: 6pt;
            font-weight: bold;
            font-family: 'Courier', monospace;
            margin-top: 1pt;
            text-align: center;
        }

        .details-section {
            margin-top: 1pt;
            line-height: 1.1;
        }

        .detail-item {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .product-brief {
            font-size: 5.5pt;
            color: #333;
            height: 8pt;
            overflow: hidden;
        }

        .price-line {
            border-top: 0.5pt solid #000;
            width: 90%;
            margin: 2pt auto 0;
            padding-top: 1pt;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $quantity; $i++)
        <div class="label-page">
            <div class="barcode-container">
                <div class="barcode-img-box">
                    <img src="{{ $barcodeBase64 }}" alt="Barcode">
                </div>
                <div class="sku-text">{{ strtoupper($sku) }}</div>
            </div>

            <div class="details-section">
                @if($color || $size)
                    <div class="detail-item">
                        {{ $color }} {{ $color && $size ? '|' : '' }} {{ $size }}
                    </div>
                @endif
                <div class="product-brief">{{ $name }}</div>
            </div>

            <div class="price-line">
                MRP: ৳{{ number_format($price, 2) }}
            </div>
        </div>
    @endfor
</body>
</html>
