<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Label - {{ $sku }}</title>
    <style id="dynamic-label-style">
        @page {
            margin: 0 !important;
            size: 45mm 35mm;
        }
    </style>
    <style>
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
            margin: 0 0 12px 0;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .size-selector-bar {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.08);
            padding: 10px 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 12px;
        }

        .size-dropdown {
            padding: 8px 14px;
            border-radius: 6px;
            border: 1.5px solid #10b981;
            background: #0f172a;
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            transition: all 0.2s ease;
        }

        .size-dropdown:hover, .size-dropdown:focus {
            border-color: #34d399;
            background: #1e293b;
        }

        .guide-box {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 16px;
            max-width: 650px;
            margin: 12px auto 0 auto;
            text-align: left;
            font-size: 0.85rem;
            line-height: 1.5;
            border-left: 4px solid #f59e0b;
        }

        .guide-box strong {
            color: #fbbf24;
        }

        .btn-print {
            padding: 8px 24px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transition: transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            overflow: hidden;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .product-title {
            width: 100%;
            font-weight: 800;
            color: #000000;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
        }

        .product-variant {
            font-weight: 700;
            color: #000000;
            line-height: 1;
            margin-top: 0.2mm;
        }

        .barcode-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .barcode-image-box {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
            background: #ffffff;
        }

        .barcode-svg {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: 100%;
            shape-rendering: crispEdges;
        }

        .barcode-svg rect {
            shape-rendering: crispEdges;
        }

        .sku-code {
            font-weight: 800;
            font-family: 'Courier New', Courier, monospace;
            color: #000000;
            letter-spacing: 0.4px;
            line-height: 1;
            margin-top: 0.3mm;
        }

        .price-tag {
            width: 96%;
            border-top: 0.4mm solid #000000;
            font-weight: 900;
            color: #000000;
            line-height: 1.15;
            padding-top: 0.3mm;
            letter-spacing: -0.2px;
        }
    </style>
    <!-- JsBarcode Native Canvas Engine -->
    <script src="{{ asset('js/JsBarcode.all.min.js') }}"></script>
    <script>
        if (typeof JsBarcode === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>');
        }
    </script>
</head>
<body>
    <div class="no-print-area">
        <h3>🖨️ {{ $quantity }} Label(s) ready for {{ $sku }}</h3>
        
        <!-- Interactive Label Size Selector -->
        <div class="size-selector-bar">
            <label for="labelSizeSelect" style="font-weight: 700; font-size: 0.92rem; color: #fbbf24;">
                📏 স্টিকার সাইজ:
            </label>
            <select id="labelSizeSelect" class="size-dropdown" onchange="applyLabelSize(this.value)">
                <option value="45x35">45mm × 35mm</option>
                <option value="38x25">38mm × 25mm</option>
            </select>
            <button class="btn-print" onclick="window.print()">
                🖨️ Print Labels Now
            </button>
        </div>

        <div class="guide-box">
            <strong>⚠️ থার্মাল প্রিন্টারে নিখুঁত স্ক্যান ও নো-স্ট্রেচিং সেটিংস (<span id="guide-size-label">45mm × 35mm</span>):</strong>
            <ul style="margin: 6px 0 0 0; padding-left: 20px;">
                <li><strong>Scale:</strong> অবশ্যই <strong>"Custom: 100"</strong> সিলেক্ট করবেন (Default বা Fit to paper রাখা যাবে না)।</li>
                <li><strong>Margins:</strong> <strong>"None"</strong> সিলেক্ট করবেন।</li>
                <li><strong>Paper size:</strong> <strong id="guide-paper-size">"45mm x 35mm"</strong> সিলেক্ট করবেন।</li>
                <li><strong>Headers and footers:</strong> আনচেক (খালি) রাখবেন।</li>
            </ul>
        </div>
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

            <!-- 2. Pixel-Perfect 1-Bit Monochrome Native SVG Barcode -->
            <div class="barcode-wrapper">
                <div class="barcode-image-box">
                    <svg class="barcode-svg" data-barcode="{{ trim($sku) }}"></svg>
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

    <script>
        const LABEL_CONFIGS = {
            '45x35': {
                name: '45mm × 35mm',
                widthMm: 45,
                heightMm: 35,
                padding: '1.2mm 2.8mm 1mm 2.8mm',
                boxMaxW: 38,
                boxH: 14,
                barcodeHeight: 52,
                barcodeMargin: 8,
                titleFont: '7.5pt',
                variantFont: '6.5pt',
                skuFont: '7.5pt',
                priceFont: '9pt'
            },
            '38x25': {
                name: '38mm × 25mm',
                widthMm: 38,
                heightMm: 25,
                padding: '0.8mm 2.2mm 0.6mm 2.2mm',
                boxMaxW: 32,
                boxH: 9,
                barcodeHeight: 36,
                barcodeMargin: 6,
                titleFont: '6.5pt',
                variantFont: '5.5pt',
                skuFont: '6.5pt',
                priceFont: '8pt'
            }
        };

        function getOptimalBarWidth(rawCode, cfg) {
            var len = rawCode ? rawCode.length : 10;
            if (cfg.widthMm >= 45) {
                // 45x35 sticker: safe width ~38mm
                if (len > 16) return 1.05;
                if (len >= 13) return 1.18; // For ~14 chars (e.g. W6-GPJ: 03-4-5)
                if (len >= 9) return 1.35;
                return 1.5;
            } else {
                // 38x25 sticker: safe width ~32mm
                if (len > 16) return 0.85;
                if (len >= 13) return 0.96;
                if (len >= 9) return 1.10;
                return 1.25;
            }
        }

        function applyLabelSize(sizeKey) {
            if (!LABEL_CONFIGS[sizeKey]) sizeKey = '45x35';
            const cfg = LABEL_CONFIGS[sizeKey];

            // Save user preference in browser
            try {
                localStorage.setItem('preferred_barcode_label_size', sizeKey);
            } catch (e) {}

            // Sync dropdown selection
            const selectEl = document.getElementById('labelSizeSelect');
            if (selectEl && selectEl.value !== sizeKey) {
                selectEl.value = sizeKey;
            }

            // Update page CSS dynamically
            let styleEl = document.getElementById('dynamic-label-style');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'dynamic-label-style';
                document.head.appendChild(styleEl);
            }

            styleEl.innerHTML = `
                @page {
                    margin: 0 !important;
                    size: ${cfg.widthMm}mm ${cfg.heightMm}mm !important;
                }
                @media print {
                    html, body {
                        width: ${cfg.widthMm}mm !important;
                        height: ${cfg.heightMm}mm !important;
                    }
                }
                .label-page {
                    width: ${cfg.widthMm}mm !important;
                    height: ${cfg.heightMm}mm !important;
                    max-width: ${cfg.widthMm}mm !important;
                    max-height: ${cfg.heightMm}mm !important;
                    padding: ${cfg.padding} !important;
                }
                .barcode-image-box {
                    max-width: ${cfg.boxMaxW}mm !important;
                    height: ${cfg.boxH}mm !important;
                }
                .barcode-svg {
                    max-width: ${cfg.boxMaxW}mm !important;
                    max-height: ${cfg.boxH}mm !important;
                }
                .product-title {
                    font-size: ${cfg.titleFont} !important;
                }
                .product-variant {
                    font-size: ${cfg.variantFont} !important;
                }
                .sku-code {
                    font-size: ${cfg.skuFont} !important;
                }
                .price-tag {
                    font-size: ${cfg.priceFont} !important;
                }
            `;

            // Update Guide text
            const guideSizeEl = document.getElementById('guide-size-label');
            const guidePaperEl = document.getElementById('guide-paper-size');
            if (guideSizeEl) guideSizeEl.textContent = cfg.name;
            if (guidePaperEl) guidePaperEl.textContent = `"${cfg.name}"`;

            // Re-render barcodes with optimal dimensions
            renderPixelPerfectBarcodes(cfg);
        }

        function renderPixelPerfectBarcodes(cfg) {
            if (typeof JsBarcode === 'undefined') {
                setTimeout(function () { renderPixelPerfectBarcodes(cfg); }, 50);
                return;
            }

            if (!cfg) {
                const currentKey = localStorage.getItem('preferred_barcode_label_size') || '45x35';
                cfg = LABEL_CONFIGS[currentKey] || LABEL_CONFIGS['45x35'];
            }

            document.querySelectorAll('.barcode-svg').forEach(function (svgEl) {
                var rawCode = svgEl.getAttribute('data-barcode');
                if (!rawCode) return;

                var calculatedWidth = getOptimalBarWidth(rawCode, cfg);

                try {
                    JsBarcode(svgEl, rawCode, {
                        format: "CODE128",
                        width: calculatedWidth,
                        height: cfg.barcodeHeight,
                        displayValue: false,
                        margin: cfg.barcodeMargin,
                        background: "#ffffff",
                        lineColor: "#000000"
                    });
                } catch (err) {
                    console.error("Barcode rendering error for [" + rawCode + "]:", err);
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            let savedSize = '45x35';
            try {
                const urlParams = new URLSearchParams(window.location.search);
                savedSize = urlParams.get('size') || localStorage.getItem('preferred_barcode_label_size') || '45x35';
            } catch (e) {}

            applyLabelSize(savedSize);
        });
    </script>
</body>
</html>
