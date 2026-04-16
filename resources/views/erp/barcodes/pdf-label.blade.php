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
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            page-break-after: always;
        }

        .barcode-container {
            width: 100%;
            text-align: center;
            margin-bottom: 1pt;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
            font-size: 7.5pt;
            font-weight: bold;
            font-family: 'Courier', monospace;
            margin-top: 2pt;
            margin-bottom: 2pt;
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
            font-size: 7.5pt;
            font-weight: bold;
            color: #111;
            max-height: 16pt; /* roughly 2 lines depending on line-height */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-top: 1pt;
        }

        .price-line {
            border-top: 1pt solid #000;
            width: 90%;
            margin: 3pt auto 0;
            padding-top: 2pt;
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $quantity; $i++)
        <div class="label-page">
            <div class="details-section">
                <div class="product-brief">{{ $name }}</div>
                @if($color)
                    <div class="detail-item" style="font-size: 6pt; margin-top: 1pt;">
                        {{ $color }}
                    </div>
                @endif
            </div>

            <div class="barcode-container" style="margin-top: 2pt;">
                <div class="barcode-img-box" style="height: 24pt;">
                    <img src="{{ $barcodeBase64 }}" alt="Barcode">
                </div>
                <div class="sku-text">{{ strtoupper($sku) }}</div>
            </div>

            <div class="price-line">
                MRP: ৳{{ number_format($price, 2) }}
            </div>
        </div>
    @endfor
</body>
</html>
