<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Label - {{ $sku }}</title>
    <style>
        @page {
            margin: 0;
            size: 38mm 25mm;
        }
        
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: white;
            color: #000;
        }

        .no-print-area {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #dee2e6;
            text-align: center;
        }

        @media print {
            .no-print-area {
                display: none !important;
            }
        }

        .label-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 38mm;
            height: 25mm;
            overflow: hidden;
            padding: 1mm;
            position: relative;
            page-break-after: always;
        }

        .barcode-section {
            width: 100%;
            height: 10mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 0.5mm;
        }

        .barcode-svg-container {
            width: 85%; /* Stay in a shape - don't fill whole width */
            height: 7.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-svg-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .sku-under-barcode {
            font-size: 6.5pt;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            margin-top: 0.2mm;
            letter-spacing: 0.3px;
        }

        .info-section {
            width: 100%;
            padding: 0 1.5mm;
            text-align: center;
            line-height: 1.1;
        }

        .detail-line {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.1mm;
            white-space: nowrap;
            overflow: hidden;
        }

        .product-name-footer {
            font-size: 6pt;
            color: #333;
            margin-top: 0.2mm;
            font-weight: normal;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price-section {
            border-top: 0.2mm solid #000;
            width: 90%;
            margin-top: 0.5mm;
            padding-top: 0.3mm;
            font-size: 9pt;
            font-weight: 900;
            text-align: center;
        }
        
        .btn {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="no-print-area">
        <h4 style="margin-bottom: 10px;">{{ $quantity }} Labels for {{ $sku }}</h4>
        <button class="btn" onclick="window.print()">Print Labels</button>
    </div>

    @for ($i = 0; $i < $quantity; $i++)
    <div class="label-page">
        <!-- 1. Barcode Section (Top) -->
        <div class="barcode-section">
            <div class="barcode-svg-container">
                <img src="{{ $barcodeBase64 }}" alt="Barcode">
            </div>
            <div class="sku-under-barcode">{{ strtoupper($sku) }}</div>
        </div>

        <!-- 2. Info Section (Middle) -->
        <div class="info-section">
            @if($color)
                <div class="detail-line">{{ $color }}</div>
            @endif
            @if($size)
                <div class="detail-line text-info">{{ $size }}</div>
            @endif
            <div class="product-name-footer">{{ $name }}</div>
        </div>

        <!-- 3. Price Section (Bottom) -->
        <div class="price-section">
            MRP: ৳{{ number_format($price, 2) }}
        </div>
    </div>
    @endfor

    <script>
        // Optional: auto trigger print
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
