<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Barcode Labels</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            margin: 10pt;
        }

        .labels-container {
            display: block;
        }

        .label-page {
            width: 144pt; /* 2 inches */
            height: 72pt; /* 1 inch */
            display: inline-block;
            text-align: center;
            padding: 6pt;
            border: 0.5pt solid #ddd;
            position: relative;
            margin: 5pt;
            vertical-align: top;
        }

        .barcode-container {
            width: 100%;
            height: 100%;
            display: table;
        }

        .barcode-content {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .barcode-image {
            margin: 0 auto 3pt auto;
            text-align: center;
        }

        .barcode-image img {
            max-width: 130pt;
            height: auto;
            max-height: 42pt;
            display: inline-block;
        }

        .sku {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 9pt;
            color: #000;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            text-align: center;
            margin-top: 2pt;
        }

        @page {
            size: A4 portrait;
            margin: 10pt;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="labels-container">
        @for ($i = 0; $i < $quantity; $i++)
            <div class="label-page">
                <div class="barcode-container">
                    <div class="barcode-content">
                        <div class="barcode-image">
                            <img src="{{ $barcodeBase64 }}" alt="Barcode">
                        </div>
                        <div class="sku">{{ strtoupper($sku) }}</div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</body>
</html>
