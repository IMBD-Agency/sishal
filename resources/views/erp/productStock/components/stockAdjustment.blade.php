<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" action="{{ route('stock.adjust') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div>
                        <div class="col-md-12">
                            {{-- Hardcoded to branch to align with Supplier -> Branch model --}}
                            <input type="hidden" name="location_type" value="branch">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select class="form-select" id="productSelect" name="product_id" style="width: 100%">
                                <option value="">Select Product...</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6" id="variationSelectGroup" style="display: none;">
                            <label class="form-label">Variation <span class="text-muted">(Required for products with variations)</span></label>
                            <select class="form-select" id="variationSelect" name="variation_id" style="width: 100%">
                                <option value="">Select Variation...</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 location-select-group" id="branchSelectGroup">
                            <label class="form-label">Branch</label>
                            <select class="form-select" name="branch_id" id="branchSelect">
                                <option value="">Select Branch...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{$branch->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adjustment Type</label>
                            <select class="form-select" name="type">
                                <option value="stock_in">Stock In</option>
                                <option value="stock_out">Stock Out</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" placeholder="Enter quantity" name="quantity">
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0 d-flex align-items-center" id="currentStockDisplay" style="display: none;">
                                <i class="fas fa-box me-2"></i>
                                <strong>Current Stock:</strong> 
                                <span id="currentStockQuantity" class="ms-2 fw-bold">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Adjust Stock</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script>
$(document).ready(function() {
    // Store product data globally
    var productDataMap = {};

    // Simplified: Adjustments are always for Branches now
    function updateCurrentStock() {
        var productId = $('#productSelect').val();
        var variationId = $('#variationSelect').val();
        var locationType = 'branch';
        var locationId = $('#branchSelect').val();

        // Hide stock display if required fields are missing
        if (!productId || !locationId || locationId === '') {
            $('#currentStockDisplay').hide();
            return;
        }

        // If product has variations but no variation is selected, don't show stock
        var hasVariations = productDataMap[productId]?.has_variations;
        if (hasVariations && (!variationId || variationId === '')) {
            $('#currentStockDisplay').hide();
            return;
        }

        // Make API call to get current stock
        $.ajax({
            url: '/erp/stock/current',
            type: 'GET',
            data: {
                product_id: productId,
                variation_id: variationId || null,
                location_type: locationType,
                branch_id: locationId
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    $('#currentStockQuantity').text(response.quantity);
                    $('#currentStockDisplay').fadeIn(200);
                } else {
                    $('#currentStockDisplay').hide();
                }
            },
            error: function(xhr) {
                console.error('Error fetching current stock:', xhr);
                $('#currentStockDisplay').hide();
            }
        });
    }

    $('#branchSelect').on('change', function() {
        updateCurrentStock();
    });

    $('#productSelect').select2({
        placeholder: 'Search or select a product',
        allowClear: true,
        ajax: {
            url: '/erp/products/search',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        productDataMap[item.id] = {
                            has_variations: item.has_variations || false
                        };
                        return { 
                            id: item.id, 
                            text: item.name,
                            has_variations: item.has_variations || false
                        };
                    })
                };
            },
            cache: true
        },
        width: 'resolve',
        dropdownParent: $('#stockAdjustmentModal'),
    });

    $('#productSelect').on('change', function() {
        var productId = $(this).val();
        $('#variationSelect').val('').trigger('change');
        $('#variationSelectGroup').hide();
        $('#currentStockDisplay').hide();
        
        if (productId) {
            var hasVariations = productDataMap[productId]?.has_variations;
            if (hasVariations === undefined) {
                $.ajax({
                    url: '/erp/products/search',
                    type: 'GET',
                    data: { q: '' },
                    dataType: 'json',
                    success: function(products) {
                        var product = products.find(function(p) { return p.id == productId; });
                        if (product) {
                            productDataMap[productId] = { has_variations: product.has_variations || false };
                            hasVariations = product.has_variations || false;
                        } else {
                            hasVariations = false;
                        }
                        if (hasVariations) {
                            $('#variationSelectGroup').show();
                            loadProductVariations(productId);
                        } else {
                            updateCurrentStock();
                        }
                    }
                });
            } else {
                if (hasVariations) {
                    $('#variationSelectGroup').show();
                    loadProductVariations(productId);
                } else {
                    updateCurrentStock();
                }
            }
        }
    });

    $('#variationSelect').select2({
        placeholder: 'Select Variation...',
        allowClear: true,
        width: 'resolve',
        dropdownParent: $('#stockAdjustmentModal'),
    });

    $('#variationSelect').on('change', function() {
        updateCurrentStock();
    });

    function loadProductVariations(productId) {
        if (!productId) return;
        $.ajax({
            url: '/erp/products/' + productId + '/variations-list',
            type: 'GET',
            dataType: 'json',
            success: function(variations) {
                $('#variationSelect').empty().append('<option value="">Select Variation...</option>');
                if (variations && variations.length > 0) {
                    variations.forEach(function(variation) {
                        var option = new Option(variation.display_name || variation.name, variation.id, false, false);
                        $('#variationSelect').append(option);
                    });
                }
                $('#variationSelect').trigger('change');
            }
        });
    }

    // Update stock when modal is shown (in case fields are pre-filled)
    $('#stockAdjustmentModal').on('shown.bs.modal', function() {
        setTimeout(function() {
            updateCurrentStock();
        }, 100);
    });

    // Reset form when modal is closed
    $('#stockAdjustmentModal').on('hidden.bs.modal', function() {
        $('#productSelect').val('').trigger('change');
        $('#variationSelect').val('').trigger('change');
        $('#variationSelectGroup').hide();
        $('#currentStockDisplay').hide();
        $('input[name="quantity"]').val('');
        $('select[name="type"]').val('stock_in');
        $('#branchSelect').val('');
        $('#warehouseSelect').val('');
        productDataMap = {}; // Clear product data map
    });

    // Form validation before submission
    $('#stockAdjustmentModal form').on('submit', function(e) {
        var productId = $('#productSelect').val();
        var hasVariations = productDataMap[productId]?.has_variations || false;
        var variationId = $('#variationSelect').val();
        
        if (!productId) {
            e.preventDefault();
            alert('Please select a product.');
            return false;
        }
        
        if (hasVariations && !variationId) {
            e.preventDefault();
            alert('Please select a variation for this product.');
            return false;
        }
        
        // Remove variation_id from form if product doesn't have variations
        if (!hasVariations) {
            $('#variationSelect').removeAttr('name');
        }
        
        return true;
    });
});
</script>
@endpush







