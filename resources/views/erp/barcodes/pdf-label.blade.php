<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Barcode Labels - {{ $sku }}</title>
    <style>
        @page {
            size: 108pt 71pt; /* Exact 38mm x 25mm */
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
            padding: 1.0mm 1.5mm 0.8mm 1.5mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            position: relative;
            page-break-after: always;
        }

        .product-brief {
            font-size: 7pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-variant {
            font-size: 5.5pt;
            font-weight: bold;
            color: #444;
            line-height: 1;
            margin-top: 0.5pt;
        }

        .barcode-container {
            width: 100%;
            text-align: center;
            margin: 1pt 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .barcode-img-box {
            width: 96%;
            height: 32pt; /* Taller barcode lines for instant scanner reading */
            margin: 0 auto;
        }

        .barcode-img-box img {
            width: 100%;
            height: 100%;
        }

        .sku-text {
            font-size: 7pt;
            font-weight: bold;
            font-family: 'Courier', monospace;
            letter-spacing: 0.5pt;
            margin-top: 1pt;
            text-align: center;
            line-height: 1;
        }

        .price-line {
            border-top: 0.8pt solid #000;
            width: 95%;
            margin: 1pt auto 0;
            padding-top: 1pt;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.1;
        }
    </style>
</head>
<body>
    @for ($i = 0; $i < $quantity; $i++)
        <div class="label-page">
            <div style="width: 100%;">
                <div class="product-brief">{{ $name }}</div>
                @if($color || $size)
                    <div class="product-variant">
                        {{ $color ? $color : '' }} {{ ($color && $size) ? '|' : '' }} {{ $size ? 'Size: '.$size : '' }}
                    </div>
                @endif
            </div>

            <div class="barcode-container">
                <div class="barcode-img-box">
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
