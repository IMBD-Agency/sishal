@extends('erp.master')

@section('title', 'Print Barcode Label')

@section('body')
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode Label - {{ $name }}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-container {
                max-width: none;
                margin: 0;
                padding: 0;
                background: white;
                box-shadow: none;
            }
            
            .labels-grid {
                display: block;
                margin: 0;
                padding: 0;
                gap: 0;
            }
            
            .barcode-label {
                border: none !important;
                page-break-inside: avoid;
                page-break-after: auto;
                margin: 0;
                padding: 10px 5px;
            }
            
            @page {
                size: auto;
                margin: 5mm;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .controls {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-start;
        }

        .barcode-label {
            width: 2in;
            height: 1in;
            border: 1px solid #ddd;
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: white;
            page-break-inside: avoid;
            box-sizing: border-box;
            position: relative;
        }

        .barcode-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .barcode-image {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 3px;
            padding: 2px 0;
        }

        .barcode-image img {
            max-width: 90%;
            height: auto;
            max-height: 45px;
            display: block;
        }

        .sku {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 10px;
            color: #000;
            margin-top: 1px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .info-section {
            background: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .info-section h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #495057;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 14px;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
        }

        .info-value {
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Controls (hidden when printing) -->
        <div class="controls no-print">
            <div>
                <h2 style="margin: 0; font-size: 18px; color: #333;">Barcode Labels</h2>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c757d;">{{ $quantity }} label(s) ready to print</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Labels
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>

        <!-- Product Information (hidden when printing) -->
        <div class="info-section no-print">
            <h3>Product Information</h3>
            <div class="info-item">
                <span class="info-label">Product:</span>
                <span class="info-value">{{ $name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Style No:</span>
                <span class="info-value">{{ $sku }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Price:</span>
                <span class="info-value">{{ number_format($price, 2) }}৳</span>
            </div>
        </div>

        <!-- Labels Grid -->
        <div class="labels-grid">
            @for ($i = 0; $i < $quantity; $i++)
                <div class="barcode-label">
                    <div class="barcode-container">
                        <div class="barcode-image">
                            <img src="{{ $barcodeBase64 }}" alt="Barcode" style="max-width: 90%; height: auto; max-height: 45px;">
                        </div>
                        <div class="sku">{{ strtoupper($sku) }}</div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>
@endsection
