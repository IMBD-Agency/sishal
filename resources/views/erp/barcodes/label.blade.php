<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Label - {{ $sku }}</title>
    <style>
        @page {
            margin: 0;
            size: 38mm 25mm; /* Standard single label size */
        }
        
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial Narrow', Arial, sans-serif;
            background-color: white;
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
            justify-content: space-between;
            width: 38mm;
            height: 25mm;
            overflow: hidden;
            padding: 1.5mm 1mm;
            position: relative;
            page-break-after: always;
            border: 0.1mm solid transparent; /* Helps with alignment */
        }

        .company-name {
            font-size: 9pt;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 0.2mm;
            color: #000;
            text-align: center;
            width: 100%;
            line-height: 1;
        }

        .product-name {
            font-size: 7.5pt;
            margin-bottom: 0.5mm;
            color: #111;
            text-align: center;
            width: 100%;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.1;
            font-weight: 500;
        }

        .barcode-svg {
            width: 100%;
            height: 8.5mm;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.2mm;
        }

        .barcode-svg img {
            max-width: 98%;
            height: 100%;
            object-fit: contain;
        }

        .sku-text {
            font-size: 7pt;
            font-weight: 700;
            font-family: 'Courier New', Courier, monospace;
            margin-bottom: 0.3mm;
            margin-top: 0.2mm;
            letter-spacing: 0.5px;
        }

        .price-text {
            border-top: 0.3pt solid #000;
            width: 95%;
            text-align: center;
            padding-top: 0.5mm;
            font-size: 10pt;
            font-weight: 900;
            color: #000;
            line-height: 1;
        }

        .btn {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 18px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="no-print-area">
        <h3 style="margin-bottom: 15px;">Printer Ready: {{ $quantity }} Labels for {{ $sku }}</h3>
        <button class="btn" onclick="window.print()">
            Click to Start Printing Labels
        </button>
        <p style="margin-top: 10px; font-size: 12px; color: #666;">
            Make sure your printer settings are set to <strong>38mm x 25mm</strong> (or your sticker size) and <strong>Margins: None</strong>.
        </p>
    </div>

    @for ($i = 0; $i < $quantity; $i++)
    <div class="label-page">
        <div class="product-name">{{ $name }}</div>
        <div class="barcode-svg">
            <img src="{{ $barcodeBase64 }}" alt="Barcode">
        </div>
        <div class="sku-text">{{ strtoupper($sku) }}</div>
        <div class="price-text">MRP: ৳{{ number_format($price, 2) }}</div>
    </div>
    @endfor

    <script>
        // Optional: auto trigger print
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
