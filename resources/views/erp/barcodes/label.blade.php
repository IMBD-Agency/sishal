<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Label - {{ $sku }}</title>
    <style>
        @page {
            margin: 0 !important;
            size: 38mm 25mm; /* Standard 1.50" x 1.00" barcode thermal sticker */
        }
        
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #e2e8f0;
            color: #000;
        }

        .no-print-area {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 20px;
            border-bottom: 3px solid #10b981;
            text-align: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .no-print-area h3 {
            margin: 0 0 8px 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .guide-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 16px;
            max-width: 600px;
            margin: 12px auto;
            text-align: left;
            font-size: 0.85rem;
            line-height: 1.5;
            border-left: 4px solid #f59e0b;
        }

        .guide-box strong {
            color: #fbbf24;
        }

        .btn-print {
            padding: 10px 28px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: transform 0.1s ease;
        }

        .btn-print:hover {
            background: #059669;
            transform: scale(1.02);
        }

        .print-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
        }

        @media print {
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 38mm !important;
                height: 25mm !important;
            }
            .no-print-area {
                display: none !important;
            }
            .print-container {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
            }
            .label-page {
                box-shadow: none !important;
                margin: 0 !important;
                border: none !important;
                page-break-after: always;
                page-break-inside: avoid;
            }
        }

        .label-page {
            width: 38mm;
            height: 25mm;
            max-width: 38mm;
            max-height: 25mm;
            overflow: hidden;
            padding: 0.8mm 1.5mm 0.6mm 1.5mm;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
        }

        .product-title {
            width: 100%;
            font-size: 6.8pt;
            font-weight: 800;
            color: #000000;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
        }

        .product-variant {
            font-size: 5.5pt;
            font-weight: 700;
            color: #222;
            line-height: 1;
            margin-top: 0.2mm;
        }

        .barcode-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0.2mm 0;
        }

        .barcode-image-box {
            width: 95%;
            height: 8.8mm; /* Perfectly proportioned to leave space for price and name */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-image-box img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
            shape-rendering: crispEdges;
        }

        .sku-code {
            font-size: 6.5pt;
            font-weight: 800;
            font-family: 'Courier New', Courier, monospace;
            color: #000000;
            letter-spacing: 0.3px;
            line-height: 1;
            margin-top: 0.3mm;
        }

        .price-tag {
            width: 95%;
            border-top: 0.3mm solid #000000;
            font-size: 8.5pt;
            font-weight: 900;
            color: #000000;
            line-height: 1.1;
            padding-top: 0.3mm;
            letter-spacing: -0.2px;
        }
    </style>
</head>
<body>
    <div class="no-print-area">
        <h3>{{ $quantity }} Label(s) ready for {{ $sku }}</h3>
        
        <div class="guide-box">
            <strong>⚠️ থার্মাল প্রিন্টারে পারফেক্ট স্ক্যানের জন্য Chrome Print সেটিংস:</strong>
            <ul style="margin: 6px 0 0 0; padding-left: 20px;">
                <li><strong>Margins:</strong> অবশ্যই <strong>"None"</strong> সিলেক্ট করবেন (Default রাখা যাবে না)।</li>
                <li><strong>Paper size:</strong> <strong>"38mm x 25mm"</strong> বা <strong>"1.50 x 1.00 inch"</strong> দিন।</li>
                <li><strong>Headers and footers:</strong> আনচেক (খালি) রাখবেন।</li>
            </ul>
        </div>

        <button class="btn-print" onclick="window.print()">
            🖨️ Print Labels Now
        </button>
    </div>

    <div class="print-container">
        @for ($i = 0; $i < $quantity; $i++)
        <div class="label-page">
            <!-- 1. Product Name -->
            <div style="width: 100%;">
                <div class="product-title" title="{{ $name }}">{{ $name }}</div>
                @if($color || $size)
                    <div class="product-variant">
                        {{ $color ? $color : '' }} {{ ($color && $size) ? '|' : '' }} {{ $size ? 'Size: '.$size : '' }}
                    </div>
                @endif
            </div>

            <!-- 2. Optimized Crisp Barcode + SKU -->
            <div class="barcode-wrapper">
                <div class="barcode-image-box">
                    <img src="{{ $barcodeBase64 }}" alt="Barcode">
                </div>
                <div class="sku-code">{{ strtoupper($sku) }}</div>
            </div>

            <!-- 3. Price Tag -->
            <div class="price-tag">
                MRP: ৳{{ number_format($price, 2) }}
            </div>
        </div>
        @endfor
    </div>
</body>
</html>
