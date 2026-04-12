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
            background-color: #e9ecef;
            color: #000;
        }

        .print-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
        }

        .no-print-area {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #dee2e6;
            text-align: center;
        }

        @media print {
            body {
                background-color: white;
            }
            .no-print-area {
                display: none !important;
            }
            .print-container {
                display: block;
                padding: 0;
                gap: 0;
            }
            .label-page {
                box-shadow: none !important;
                margin: 0 !important;
                page-break-after: always;
            }
        }

        .label-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center; /* Guarantee perfect vertical centering */
            width: 38mm;
            height: 25mm;
            overflow: hidden;
            padding: 1mm;
            position: relative;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .barcode-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
            font-size: 8pt;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            margin-top: 0.5mm;
            margin-bottom: 0.5mm;
            letter-spacing: 0.3px;
        }

        .info-section {
            width: 100%;
            padding: 0 1.5mm;
            text-align: center;
            line-height: 1.1;
        }

        .detail-line {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.1mm;
            white-space: normal;
        }

        .product-name-footer {
            font-size: 7.5pt;
            color: #111;
            margin-top: 0.5mm;
            font-weight: bold;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Allow 2 lines */
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.1;
        }

        .price-section {
            border-top: 0.3mm solid #000;
            width: 90%;
            margin-top: 0.5mm;
            padding-top: 0.5mm;
            font-size: 10pt;
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

    <div class="print-container">
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
                <div class="product-name-footer">{{ $name }}</div>
            </div>

            <!-- 3. Price Section (Bottom) -->
            <div class="price-section">
                MRP: ৳{{ number_format($price, 2) }}
            </div>
        </div>
        @endfor
    </div>

    <script>
        // Optional: auto trigger print
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
