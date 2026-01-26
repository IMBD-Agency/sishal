<!-- Barcode Generation Modal -->
<div class="modal fade" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-barcode me-2"></i>Generate Barcode
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Product Information -->
                <div class="product-info mb-3 p-3 bg-light rounded">
                    <h6 class="mb-2 fw-bold" id="barcodeProductName"></h6>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Style No:</span>
                        <span class="fw-semibold" id="barcodeProductSku"></span>
                    </div>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Price:</span>
                        <span class="fw-semibold text-success" id="barcodeProductPrice"></span>
                    </div>
                </div>

                <!-- Variation Selection (hidden by default) -->
                <div id="barcodeVariationWrapper" class="mb-3" style="display: none;">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-tags me-1"></i>Select Variation
                    </label>
                    <select class="form-select" id="barcodeVariationSelect">
                        <option value="">Select a variation...</option>
                    </select>
                </div>

                <!-- Barcode Preview -->
                <div class="barcode-preview text-center mb-3 p-4 border rounded bg-white">
                    <div id="barcodePreviewLoading" class="text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                        <p>Select options to generate barcode</p>
                    </div>
                    <div id="barcodePreviewImage" style="display: none;">
                        <!-- Barcode SVG will be inserted here -->
                    </div>
                </div>

                <!-- Quantity Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-copy me-1"></i>Number of Labels
                    </label>
                    <div class="input-group" style="max-width: 200px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="decrementBarcodeQuantity()">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="barcodeQuantity" value="1" min="1" max="100">
                        <button class="btn btn-outline-secondary" type="button" onclick="incrementBarcodeQuantity()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <small class="text-muted">Print 1-100 labels at once</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-info" id="downloadBarcodeBtn" onclick="downloadBarcodePDF()">
                    <i class="fas fa-download me-1"></i>Download PDF
                </button>
                <button type="button" class="btn btn-primary" id="printBarcodeBtn" onclick="printBarcode()">
                    <i class="fas fa-print me-1"></i>Print Labels
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .barcode-preview {
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #barcodePreviewImage svg {
        max-width: 100%;
        height: auto;
        max-height: 200px;
    }

    .text-sm {
        font-size: 0.875rem;
    }
</style>

<script>
    let currentBarcodeProduct = null;
    let currentBarcodeVariation = null;

    // Open barcode modal for a product
    function openBarcodeModal(productId) {
        // Find product in the products array
        const product = products.find(p => p.id === productId);
        if (!product) {
            showToast('Product not found', 'error');
            return;
        }

        currentBarcodeProduct = product;
        currentBarcodeVariation = null;

        // Set product information
        $('#barcodeProductName').text(product.name);
        $('#barcodeProductSku').text(product.sku);
        $('#barcodeProductPrice').text(product.price + '৳');

        // Reset quantity
        $('#barcodeQuantity').val(1);

        // Check if product has variations
        if (product.has_variations) {
            // Load variations
            loadBarcodeVariations(productId);
            $('#barcodeVariationWrapper').show();
            $('#barcodePreviewLoading').show();
            $('#barcodePreviewImage').hide();
        } else {
            // No variations, generate barcode immediately
            $('#barcodeVariationWrapper').hide();
            generateBarcodePreview(productId);
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('barcodeModal'));
        modal.show();
    }

    // Load product variations for barcode generation
    function loadBarcodeVariations(productId) {
        $.get(`/erp/products/${productId}/variations-list`, function(variations) {
            const $select = $('#barcodeVariationSelect');
            $select.empty();
            $select.append('<option value="">Select a variation...</option>');

            if (variations && variations.length > 0) {
                variations.forEach(variation => {
                    const displayName = variation.display_name || variation.name;
                    $select.append(`<option value="${variation.id}">${displayName}</option>`);
                });
            }
        }).fail(function() {
            showToast('Error loading variations', 'error');
        });
    }

    // Handle variation selection change
    $('#barcodeVariationSelect').on('change', function() {
        const variationId = $(this).val();
        if (variationId && currentBarcodeProduct) {
            generateBarcodePreview(currentBarcodeProduct.id, variationId);
        } else {
            $('#barcodePreviewLoading').show();
            $('#barcodePreviewImage').hide();
        }
    });

    // Generate barcode preview
    function generateBarcodePreview(productId, variationId = null) {
        $('#barcodePreviewLoading').html('<i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>Generating barcode...</p>').show();
        $('#barcodePreviewImage').hide();

        const url = variationId 
            ? `/erp/barcodes/variation/${productId}/${variationId}`
            : `/erp/barcodes/product/${productId}`;

        $.get(url, function(response) {
            if (response.success && response.barcode) {
                $('#barcodePreviewImage').html(response.barcode).show();
                $('#barcodePreviewLoading').hide();
                
                // Store variation info if applicable
                if (variationId && response.variation) {
                    currentBarcodeVariation = response.variation;
                    $('#barcodeProductSku').text(response.variation.sku);
                    $('#barcodeProductPrice').text(response.variation.price + '৳');
                }

                // Update quantity based on available stock
                let stock = 0;
                if (variationId && response.variation) {
                    stock = response.variation.available_stock || 0;
                } else if (!variationId && response.product) {
                    stock = response.product.available_stock || 0;
                }
                
                if (stock > 0) {
                    $('#barcodeQuantity').val(Math.min(parseInt(stock), 100));
                } else {
                    $('#barcodeQuantity').val(1);
                }
            } else {
                showToast('Error generating barcode', 'error');
            }
        }).fail(function() {
            showToast('Error generating barcode', 'error');
            $('#barcodePreviewLoading').html('<i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i><p>Error generating barcode</p>');
        });
    }

    // Print barcode labels
    function printBarcode() {
        if (!currentBarcodeProduct) {
            showToast('No product selected', 'error');
            return;
        }

        // Check if variation is required but not selected
        if (currentBarcodeProduct.has_variations && !currentBarcodeVariation) {
            showToast('Please select a variation', 'warning');
            return;
        }

        const quantity = $('#barcodeQuantity').val();
        const productId = currentBarcodeProduct.id;
        const variationId = currentBarcodeVariation ? currentBarcodeVariation.id : null;

        // Build print URL
        let printUrl = `/erp/barcodes/print/${productId}`;
        if (variationId) {
            printUrl += `/${variationId}`;
        }
        printUrl += `?quantity=${quantity}`;

        // Open print page in new window
        window.open(printUrl, '_blank', 'width=800,height=600');
    }

    // Download barcode labels as PDF
    function downloadBarcodePDF() {
        if (!currentBarcodeProduct) {
            showToast('No product selected', 'error');
            return;
        }

        // Check if variation is required but not selected
        if (currentBarcodeProduct.has_variations && !currentBarcodeVariation) {
            showToast('Please select a variation', 'warning');
            return;
        }

        const quantity = $('#barcodeQuantity').val();
        const productId = currentBarcodeProduct.id;
        const variationId = currentBarcodeVariation ? currentBarcodeVariation.id : null;

        // Build download URL
        let downloadUrl = `/erp/barcodes/download/${productId}`;
        if (variationId) {
            downloadUrl += `/${variationId}`;
        }
        downloadUrl += `?quantity=${quantity}`;

        // Trigger download
        window.location.href = downloadUrl;
        showToast('Downloading barcode PDF...', 'success');
    }

    // Quantity controls
    function incrementBarcodeQuantity() {
        const $input = $('#barcodeQuantity');
        const currentVal = parseInt($input.val()) || 1;
        if (currentVal < 100) {
            $input.val(currentVal + 1);
        }
    }

    function decrementBarcodeQuantity() {
        const $input = $('#barcodeQuantity');
        const currentVal = parseInt($input.val()) || 1;
        if (currentVal > 1) {
            $input.val(currentVal - 1);
        }
    }
</script>
