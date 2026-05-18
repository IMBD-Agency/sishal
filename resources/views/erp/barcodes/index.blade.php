@extends('erp.master')

@section('title', 'Barcode Generator')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0 text-dark">Barcode Generator</h4>
                <p class="text-muted small mb-0">Generate product & combo labels by SKU / Name</p>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">

        {{-- Tab navigation --}}
        <ul class="nav nav-tabs mb-4" id="barcodeTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="product-tab" data-bs-toggle="tab" data-bs-target="#productPane" type="button" role="tab">
                    <i class="fas fa-box me-2"></i>Regular Products
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="combo-tab" data-bs-toggle="tab" data-bs-target="#comboPane" type="button" role="tab">
                    <i class="fas fa-gift me-2"></i>Combo Products
                </button>
            </li>
        </ul>

        <div class="tab-content" id="barcodeTabContent">

            {{-- ══════════════ PRODUCT TAB ══════════════ --}}
            <div class="tab-pane fade show active" id="productPane" role="tabpanel">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="premium-card mb-4 shadow-sm">
                            <div class="card-header bg-white border-bottom p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-search me-2 text-primary"></i>Product Lookup</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Style Number / SKU</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-barcode"></i></span>
                                        <input type="text" id="styleNoInput" class="form-control border-start-0" placeholder="Enter Style Number (e.g. ST-101)..." autofocus>
                                        <button class="btn btn-primary px-4" id="searchBtn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Type and press Enter to search</small>
                                </div>

                                <div id="searchResults" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="premium-card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0"><i class="fas fa-eye me-2 text-info"></i>Label Preview</h6>
                                <div id="previewActions" style="display: none;">
                                    <button class="btn btn-sm btn-info text-white fw-bold px-3" onclick="printLabels()">
                                        <i class="fas fa-print me-1"></i>Print Now
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-4 bg-light d-flex align-items-center justify-content-center" style="min-height: 400px;">
                                <div id="previewContainer" class="text-center">
                                    <i class="fas fa-barcode fa-5x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted">Enter product info to generate preview</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════ COMBO TAB ══════════════ --}}
            <div class="tab-pane fade" id="comboPane" role="tabpanel">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="premium-card mb-4 shadow-sm">
                            <div class="card-header bg-white border-bottom p-3">
                                <h6 class="fw-bold mb-0"><i class="fas fa-gift me-2 text-warning"></i>Combo Product Search</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Search by Name or SKU</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-gift"></i></span>
                                        <input type="text" id="comboSearchInput" class="form-control border-start-0" placeholder="e.g. Summer Bundle...">
                                        <button class="btn btn-warning px-4" id="comboSearchBtn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Type and press Enter to search</small>
                                </div>

                                <div id="comboResults" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="premium-card shadow-sm h-100">
                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0"><i class="fas fa-eye me-2 text-info"></i>Combo Label Preview</h6>
                                <div id="comboPrintActions" style="display: none;">
                                    <button class="btn btn-sm btn-info text-white fw-bold px-3" onclick="printComboLabels()">
                                        <i class="fas fa-print me-1"></i>Print Now
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-4 bg-light d-flex align-items-center justify-content-center" style="min-height: 400px;">
                                <div id="comboPreviewContainer" class="text-center">
                                    <i class="fas fa-gift fa-5x text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted">Search a combo product to generate its barcode</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /tab-content --}}
    </div>
</div>

@push('css')
<style>
    .product-pills {
        border-left: 4px solid #0d6efd !important;
    }
    .combo-pills {
        border-left: 4px solid #ffc107 !important;
    }
    .barcode-sticker-preview {
        background: #fff;
        border: 1px dashed #4e73df;
        padding: 6px 8px;
        border-radius: 4px;
        width: 154px;
        height: 102px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        font-family: 'Arial Narrow', sans-serif;
        position: relative;
        box-sizing: border-box;
    }
    .sticker-name {
        font-size: 10px;
        font-weight: 850;
        color: #000;
        margin: 0;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        line-height: 1.2;
        width: 100%;
        text-transform: uppercase;
    }
    .sticker-barcode {
        margin: 0;
        display: flex;
        justify-content: center;
        height: 30px;
        width: 100%;
    }
    .sticker-barcode svg, .sticker-barcode img {
        max-width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .sticker-sku {
        font-family: 'Courier New', monospace;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin: 0;
        line-height: 1;
    }
    .sticker-price {
        font-size: 12px;
        font-weight: 950;
        border-top: 1px solid #000;
        padding-top: 2px;
        margin-top: 2px;
        width: 95%;
        color: #000;
    }
    .combo-search-result-item {
        cursor: pointer;
        transition: background 0.15s;
    }
    .combo-search-result-item:hover {
        background: #fff8e6;
    }
</style>
@endpush

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // ══════════ PRODUCT BARCODE LOGIC ══════════
    let currentProduct = null;
    let currentVariation = null;

    $(document).ready(function() {
        $('.select2-simple').select2({ width: '100%' });

        // Auto-search if style_no is in URL
        const urlParams = new URLSearchParams(window.location.search);
        const styleNoParam = urlParams.get('style_no');
        const comboIdParam  = urlParams.get('combo_id');

        if (styleNoParam) {
            $('#styleNoInput').val(styleNoParam);
            searchProduct();
        }

        // If combo_id in URL → switch to combo tab and auto-load
        if (comboIdParam) {
            const comboTabEl = document.getElementById('combo-tab');
            bootstrap.Tab.getOrCreateInstance(comboTabEl).show();
            autoLoadCombo(comboIdParam);
        }

        $('#styleNoInput').on('keypress', function(e) {
            if(e.which == 13) searchProduct();
        });

        $('#searchBtn').on('click', searchProduct);

        function searchProduct() {
            const styleNo = $('#styleNoInput').val().trim();
            if(!styleNo) return;

            $('#searchBtn').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#searchResults').show();
            $('#searchResults').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i><p class="text-muted mb-0">Searching product...</p></div>');
            
            $.get(`/erp/barcodes/search?style_no=${encodeURIComponent(styleNo)}`, function(res) {
                $('#searchBtn').html('<i class="fas fa-search"></i>');
                
                if(res.success) {
                    currentProduct = res.product;
                    
                    let html = `
                        <div class="product-pills p-3 bg-light rounded border mb-4">
                            <h6 class="fw-bold mb-1">${res.product.name}</h6>
                            <p class="text-muted small mb-0">Style: ${res.product.style_number || res.product.sku}</p>
                            <div class="mt-2 fw-bold text-success">MRP: ৳${parseFloat(res.product.price).toFixed(2)}</div>
                        </div>

                        <div id="variationWrapper" class="mb-4" style="${res.product.has_variations ? '' : 'display: none;'}">
                            <label class="form-label fw-bold text-muted small text-uppercase">Select Variation (Optional)</label>
                            <select class="form-select select2-simple" id="variationSelect">
                                <option value="">No Variation (Main Product Barcode)</option>
                                ${res.product.variations.map(v => `<option value="${v.id}">${v.display_name} - ৳${v.price}</option>`).join('')}
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Label Quantity</label>
                            <div class="input-group" style="max-width: 180px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(-1)"><i class="fas fa-minus"></i></button>
                                <input type="number" class="form-control text-center fw-bold" id="labelQty" value="1" min="1" max="500">
                                <button class="btn btn-outline-secondary" type="button" onclick="adjustQty(1)"><i class="fas fa-plus"></i></button>
                            </div>
                            <small class="text-muted">Maximum 500 labels at once</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary py-2 fw-bold" id="previewBtn">
                                <i class="fas fa-sync me-2"></i>Generate Preview
                            </button>
                        </div>
                    `;
                    
                    $('#searchResults').html(html);
                    $('.select2-simple').select2({ width: '100%' });
                    $('#previewBtn').on('click', generatePreview);
                    $('#previewContainer').html('<i class="fas fa-barcode fa-5x text-muted opacity-25 mb-3"></i><p class="text-muted">Selected: <strong class="text-dark">' + res.product.name + '</strong>. <br>Click "Generate Preview" to see the label.</p>');
                    $('#previewActions').hide();

                } else {
                    $('#searchResults').html(`<div class="alert alert-warning border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>${res.message}</div>`);
                }
            }).fail(function() {
                $('#searchBtn').html('<i class="fas fa-search"></i>');
                $('#searchResults').html('<div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-times-circle me-2"></i>Connection error. Try again.</div>');
            });
        }

        function generatePreview() {
            if(!currentProduct) return;

            const variationId = $('#variationSelect').val();
            const qty = $('#labelQty').val() || 1;

            $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
            
            const url = variationId 
                ? `/erp/barcodes/variation/${currentProduct.id}/${variationId}`
                : `/erp/barcodes/product/${currentProduct.id}`;

            $.get(url, function(res) {
                $('#previewBtn').html('<i class="fas fa-sync me-2"></i>Generate Preview');
                
                if(res.success) {
                    const sku   = res.variation ? res.variation.sku : (res.product.style_number || res.product.sku);
                    const price = res.variation ? res.variation.price : res.product.price;
                    const name  = res.product.name;
                    currentVariation = res.variation;

                    $('#previewContainer').html(buildStickerHtml(name, res.barcode, sku, price, qty));
                    $('#previewActions').fadeIn();
                }
            });
        }

        $('#previewBtn').on('click', generatePreview);

        // ══════════ COMBO BARCODE LOGIC ══════════
        let currentCombo = null;

        function autoLoadCombo(comboId) {
            // Show a loading state in preview while fetching
            $('#comboPreviewContainer').html(
                '<i class="fas fa-spinner fa-spin fa-3x text-warning mb-3"></i><p class="text-muted">Loading combo barcode...</p>'
            );

            $.get(`/erp/barcodes/combo/${comboId}`, function(res) {
                if (res.success) {
                    currentCombo = res.product;

                    // Pre-fill the search field so the user sees what's loaded
                    $('#comboSearchInput').val(res.product.name);

                    // Render the left-side form (combo info + qty picker)
                    renderComboFound(res.product);

                    // Auto-populate the preview immediately — no extra click needed
                    $('#comboPreviewContainer').html(
                        buildStickerHtml(res.product.name, res.barcode, res.product.sku, res.product.price, 1)
                    );
                    $('#comboPrintActions').fadeIn();

                    // Keep qty picker in sync: re-generate preview when qty changes
                    $(document).on('input', '#comboLabelQty', function() {
                        const qty = $(this).val() || 1;
                        $('#comboPreviewContainer').html(
                            buildStickerHtml(res.product.name, res.barcode, res.product.sku, res.product.price, qty)
                        );
                    });
                }
            }).fail(function() {
                $('#comboPreviewContainer').html(
                    '<div class="alert alert-danger border-0">Failed to load combo. Please try again.</div>'
                );
            });
        }

        $('#comboSearchInput').on('keypress', function(e) {
            if(e.which == 13) searchCombo();
        });
        $('#comboSearchBtn').on('click', searchCombo);

        function searchCombo() {
            const q = $('#comboSearchInput').val().trim();
            if (!q) return;

            $('#comboSearchBtn').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#comboResults').show().html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-warning"></i></div>');

            $.get(`/erp/barcodes/combo/search?q=${encodeURIComponent(q)}`, function(res) {
                $('#comboSearchBtn').html('<i class="fas fa-search"></i>');

                if (res.success && res.combos.length > 0) {
                    let html = '<div class="list-group mb-3">';
                    res.combos.forEach(c => {
                        html += `<a href="#" class="list-group-item list-group-item-action combo-search-result-item d-flex justify-content-between align-items-center" data-id="${c.id}" data-name="${c.name}" data-sku="${c.sku}" data-price="${c.price}">
                                    <div>
                                        <strong>${c.name}</strong><br>
                                        <small class="text-muted">${c.sku}</small>
                                    </div>
                                    <span class="badge bg-warning text-dark">৳${parseFloat(c.price).toFixed(2)}</span>
                                 </a>`;
                    });
                    html += '</div>';
                    $('#comboResults').html(html);

                    // Bind click
                    $('.combo-search-result-item').on('click', function(e) {
                        e.preventDefault();
                        const id    = $(this).data('id');
                        const name  = $(this).data('name');
                        const sku   = $(this).data('sku');
                        const price = $(this).data('price');
                        currentCombo = { id, name, sku, price };
                        renderComboFound(currentCombo);
                    });
                } else {
                    $('#comboResults').html('<div class="alert alert-warning border-0"><i class="fas fa-exclamation-circle me-2"></i>No combo found.</div>');
                }
            }).fail(function() {
                $('#comboSearchBtn').html('<i class="fas fa-search"></i>');
                $('#comboResults').html('<div class="alert alert-danger border-0">Connection error.</div>');
            });
        }

        function renderComboFound(combo) {
            let html = `
                <div class="combo-pills p-3 bg-light rounded border mb-4">
                    <h6 class="fw-bold mb-1">${combo.name}</h6>
                    <p class="text-muted small mb-0">SKU: ${combo.sku}</p>
                    <div class="mt-2 fw-bold text-warning">Price: ৳${parseFloat(combo.price).toFixed(2)}</div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small text-uppercase">Label Quantity</label>
                    <div class="input-group" style="max-width: 180px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="adjustComboQty(-1)"><i class="fas fa-minus"></i></button>
                        <input type="number" class="form-control text-center fw-bold" id="comboLabelQty" value="1" min="1" max="500">
                        <button class="btn btn-outline-secondary" type="button" onclick="adjustComboQty(1)"><i class="fas fa-plus"></i></button>
                    </div>
                    <small class="text-muted">Maximum 500 labels at once</small>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-warning py-2 fw-bold text-dark" id="comboPreviewBtn">
                        <i class="fas fa-sync me-2"></i>Generate Combo Preview
                    </button>
                </div>
            `;
            $('#comboResults').html(html);

            $('#comboPreviewBtn').on('click', function() {
                if (!currentCombo) return;
                $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
                const qty = $('#comboLabelQty').val() || 1;

                $.get(`/erp/barcodes/combo/${currentCombo.id}`, function(res) {
                    $('#comboPreviewBtn').html('<i class="fas fa-sync me-2"></i>Generate Combo Preview');
                    if (res.success) {
                        $('#comboPreviewContainer').html(buildStickerHtml(res.product.name, res.barcode, res.product.sku, res.product.price, qty));
                        $('#comboPrintActions').fadeIn();
                    }
                });
            });
        }

        // ══════════ SHARED ══════════
        window.buildStickerHtml = function(name, barcode, sku, price, qty) {
            return `
                <div class="barcode-sticker-preview mx-auto position-relative">
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size:12px; z-index:10; transform: translate(-30%, -30%) !important;">
                        ${qty}x
                    </span>
                    <div class="sticker-name" title="${name}">${name || 'PRODUCT NAME MISSING'}</div>
                    <div class="sticker-barcode">${barcode}</div>
                    <div class="sticker-sku">${sku || 'NO SKU'}</div>
                    <div class="sticker-price">MRP: ৳${parseFloat(price).toFixed(2)}</div>
                </div>
                <div class="mt-4">
                    <div class="alert alert-success bg-success bg-opacity-10 py-2 px-3 border-0 rounded-3 mb-0 d-inline-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2 fa-lg"></i>
                        <span>Ready to print <strong>${qty}</strong> labels for <b>${name}</b></span>
                    </div>
                </div>
            `;
        };
    }); // end ready

    function adjustQty(v) {
        const $input = $('#labelQty');
        let val = parseInt($input.val()) + v;
        if(val < 1) val = 1;
        if(val > 500) val = 500;
        $input.val(val);
    }

    function adjustComboQty(v) {
        const $input = $('#comboLabelQty');
        let val = parseInt($input.val()) + v;
        if(val < 1) val = 1;
        if(val > 500) val = 500;
        $input.val(val);
    }

    function printLabels() {
        if(!currentProduct) return;
        const qty         = $('#labelQty').val();
        const variationId = $('#variationSelect').val();
        let url = `/erp/barcodes/print/${currentProduct.id}`;
        if(variationId) url += `/${variationId}`;
        url += `?quantity=${qty}`;
        window.open(url, '_blank', 'width=800,height=600');
    }

    function printComboLabels() {
        if(!currentCombo) return;
        const qty = $('#comboLabelQty').val() || 1;
        window.open(`/erp/barcodes/combo/${currentCombo.id}/print?quantity=${qty}`, '_blank', 'width=800,height=600');
    }
</script>
@endpush
@endsection
