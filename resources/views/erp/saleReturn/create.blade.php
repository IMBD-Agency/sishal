@extends('erp.master')

@section('title', 'Create Sale Return')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <h2 class="mb-4">Create Sale Return</h2>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="saleReturnForm" action="{{ route('saleReturn.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    @if(isset($selectedPosSale) && $selectedPosSale && $selectedPosSale->customer_id == $customer->id) selected @endif>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="pos_sale_id" class="form-label">POS Sale</label>
                        <select name="pos_sale_id" id="pos_sale_id" class="form-select" required disabled>
                            <option value="">Select Customer First</option>
                        </select>
                        <small class="text-muted" id="pos_sale_hint">Please select a customer first</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="return_date" class="form-label">Return Date</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="refund_type" class="form-label">Refund Type</label>
                        <select name="refund_type" id="refund_type" class="form-select" required>
                            <option value="none">None</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="return_to_type" class="form-label">Return To</label>
                        <select name="return_to_type" id="return_to_type" class="form-select" required>
                            <option value="">Select Return To</option>
                            <option value="branch">Branch</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="employee">Employee</option>
                        </select>
                        <select name="return_to_id" id="return_to_id" class="form-select mt-2" style="display:none;" required>
                            <option value="">Select Location</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <input type="text" name="reason" id="reason" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Return Items</label>
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Returned Qty</th>
                                <th>Unit Price</th>
                                <th>Reason</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][product_id]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="variation-wrapper mt-2" style="display:none;">
                                        <select name="items[0][variation_id]" class="form-select variation-select">
                                            <option value="">Select Variation (if applicable)</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="items[0][sale_item_id]" class="sale-item-id">
                                </td>
                                <td><input type="number" name="items[0][returned_qty]" class="form-control returned_qty" min="1" required></td>
                                <td><input type="number" name="items[0][unit_price]" class="form-control unit_price" min="1" required></td>
                                <td><input type="text" name="items[0][reason]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row" disabled>&times;</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary btn-sm" id="addItemRow" style="display:none;">Add Item</button>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-primary">Create Sale Return</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let itemIndex = 1;
        let availableProducts = []; // Store products from selected POS sale
        
        // Helper function to safely destroy Select2
        function safeDestroySelect2($element) {
            if ($element.length && $element.hasClass('select2-hidden-accessible')) {
                try {
                    $element.select2('destroy');
                } catch(e) {
                    // Ignore errors if Select2 is not properly initialized
                }
            }
        }
        
        $(document).ready(function() {
            // Initialize Select2 for customer search
            $('#customer_id').select2({
                placeholder: 'Select Customer',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '/erp/customers/search',
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
                                return { id: item.id, text: item.name + (item.email ? ' (' + item.email + ')' : '') };
                            })
                        };
                    },
                    cache: true
                }
            });
            // Initialize Select2 for initial product dropdown
            $('.product-select').select2({
                placeholder: 'Search Product',
                allowClear: true,
                width: '100%'
            });
            // Initialize Select2 for POS Sale search (initially disabled)
            $('#pos_sale_id').select2({
                placeholder: 'Select Customer First',
                allowClear: true,
                width: '100%',
                disabled: true
            });
            
            // Handle customer selection change (handle both regular change and Select2 events)
            $('#customer_id').on('change select2:select select2:unselect', function(e) {
                // Use setTimeout to ensure Select2 value is set
                setTimeout(function() {
                    const customerId = $('#customer_id').val();
                    const $posSaleSelect = $('#pos_sale_id');
                    const $hint = $('#pos_sale_hint');
                    
                    if (customerId) {
                        // Hide hint message
                        $hint.hide();
                        
                        // Enable POS sale dropdown and update it to filter by customer
                        $posSaleSelect.prop('disabled', false);
                        $posSaleSelect.empty().append('<option value="">Select POS Sale</option>');
                        
                        // Store customer ID for use in AJAX
                        const selectedCustomerId = customerId;
                        
                        // Reinitialize Select2 with customer filter
                        safeDestroySelect2($posSaleSelect);
                        $posSaleSelect.select2({
                            placeholder: 'Select POS Sale',
                            allowClear: true,
                            width: '100%',
                            ajax: {
                                url: '/erp/pos/search',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        q: params.term || '',
                                        customer_id: selectedCustomerId
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data
                                    };
                                },
                                cache: true
                            }
                        });
                    } else {
                        // Show hint message
                        $hint.show();
                        
                        // Disable POS sale dropdown if no customer selected
                        $posSaleSelect.prop('disabled', true);
                        $posSaleSelect.val('').trigger('change');
                        safeDestroySelect2($posSaleSelect);
                        $posSaleSelect.select2({
                            placeholder: 'Select Customer First',
                            allowClear: true,
                            width: '100%',
                            disabled: true
                        });
                        clearItemsTable();
                    }
                }, 100);
            });
            
            // Auto-load POS sale details when selected
            $('#pos_sale_id').on('change select2:select', function() {
                const posSaleId = $(this).val();
                if (posSaleId) {
                    $('#addItemRow').show();
                    loadPosSaleDetails(posSaleId);
                } else {
                    // Clear items and available products if POS sale is deselected
                    availableProducts = [];
                    clearItemsTable();
                    $('#addItemRow').hide();
                }
            });
            
            // Function to load POS sale details
            function loadPosSaleDetails(posSaleId) {
                $.ajax({
                    url: '/erp/pos/' + posSaleId + '/details',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            const data = response.data;
                            
                            // Set customer (without triggering change to avoid reinitializing POS sale dropdown)
                            if (data.customer_id) {
                                const $customerSelect = $('#customer_id');
                                if ($customerSelect.val() != data.customer_id) {
                                    $customerSelect.val(data.customer_id).trigger('change');
                                }
                            }
                            
                            // Set return_to_type to branch if branch_id exists
                            if (data.branch_id) {
                                $('#return_to_type').val('branch').trigger('change');
                                setTimeout(function() {
                                    $('#return_to_id').val(data.branch_id).trigger('change');
                                }, 100);
                            }
                            
                            // Store available products from this POS sale
                            if (data.items && data.items.length > 0) {
                                availableProducts = data.items.map(function(item) {
                                    return {
                                        id: parseInt(item.product_id),
                                        name: item.product_name || 'Product #' + item.product_id
                                    };
                                });
                                // Remove duplicates
                                availableProducts = availableProducts.filter((product, index, self) =>
                                    index === self.findIndex((p) => p.id === product.id)
                                );
                                
                                clearItemsTable();
                                data.items.forEach(function(item, index) {
                                    addItemRow(item, index);
                                });
                                $('#addItemRow').show();
                            } else {
                                availableProducts = [];
                                clearItemsTable();
                                $('#addItemRow').show(); // Still show button even if no items
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading POS sale details:', xhr);
                        alert('Failed to load POS sale details. Please try again.');
                    }
                });
            }
            
            // Function to add item row with data
            function addItemRow(itemData, index) {
                // Build product options - only show products from selected POS sale if available
                let productOptions = '<option value="">Select Product</option>';
                
                if (availableProducts.length > 0) {
                    // Only show products from the selected POS sale
                    availableProducts.forEach(function(product) {
                        const itemProductId = itemData ? parseInt(itemData.product_id) : null;
                        const selected = (itemProductId && itemProductId === product.id) ? 'selected' : '';
                        productOptions += `<option value="${product.id}" ${selected}>${product.name}</option>`;
                    });
                } else {
                    // If no POS sale selected, show all products
                    @foreach($products as $product)
                        productOptions += '<option value="{{ $product->id }}"' + 
                            (itemData && itemData.product_id == {{ $product->id }} ? ' selected' : '') + 
                            '>{{ $product->name }}</option>';
                    @endforeach
                }
                
                const saleItemId = itemData ? itemData.id : '';
                const variationId = itemData ? (itemData.variation_id || '') : '';
                const quantity = itemData ? itemData.quantity : '';
                const unitPrice = itemData ? itemData.unit_price : '';
                
                const row = $('<tr>');
                row.html(`
                    <td>
                        <select name="items[${index}][product_id]" class="form-select product-select" required>
                            ${productOptions}
                        </select>
                        <div class="variation-wrapper mt-2" style="display:none;">
                            <select name="items[${index}][variation_id]" class="form-select variation-select">
                                <option value="">Select Variation (if applicable)</option>
                            </select>
                        </div>
                        <input type="hidden" name="items[${index}][sale_item_id]" class="sale-item-id" value="${saleItemId}">
                    </td>
                    <td><input type="number" name="items[${index}][returned_qty]" class="form-control returned_qty" min="1" value="${quantity}" required></td>
                    <td><input type="number" name="items[${index}][unit_price]" class="form-control unit_price" min="0" step="0.01" value="${unitPrice}" required></td>
                    <td><input type="text" name="items[${index}][reason]" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
                `);
                $('#itemsTable tbody').append(row);
                
                // Initialize Select2 for product dropdown
                const productSelect = row.find('.product-select');
                productSelect.select2({
                    placeholder: 'Search Product',
                    allowClear: true,
                    width: '100%'
                });
                
                // Set selected product if itemData provided
                if (itemData && itemData.product_id) {
                    const productId = parseInt(itemData.product_id);
                    // Use setTimeout to ensure Select2 is fully initialized
                    setTimeout(function() {
                        productSelect.val(productId).trigger('change');
                        // Load variations if product has variations
                        loadVariationsForProduct(productSelect, productId, variationId);
                    }, 100);
                }
                
                // Handle product change to load variations (works with both regular select and Select2)
                productSelect.on('change select2:select', function() {
                    const productId = $(this).val();
                    const $row = $(this).closest('tr');
                    const $unitPriceInput = $row.find('.unit_price');
                    
                    if (productId) {
                        // Clear price first when switching products
                        $unitPriceInput.val('');
                        loadVariationsForProduct($(this), productId, null);
                    } else {
                        // Product deselected - clear everything
                        $row.find('.variation-wrapper').hide();
                        $row.find('.variation-select').val('').empty();
                        $unitPriceInput.val('');
                    }
                });
                
                // Update itemIndex
                if (index >= itemIndex) {
                    itemIndex = index + 1;
                }
            }
            
            // Function to clear items table
            function clearItemsTable() {
                $('#itemsTable tbody').empty();
                itemIndex = 0;
                // Add one empty row
                addItemRow(null, 0);
            }
            
            // Load POS sale details on page load if pre-selected
            @if(isset($selectedPosSale) && $selectedPosSale)
                $(document).ready(function() {
                    // Wait for Select2 to initialize
                    setTimeout(function() {
                        // Set customer first
                        if ({{ $selectedPosSale->customer_id ?? 'null' }}) {
                            $('#customer_id').val({{ $selectedPosSale->customer_id }}).trigger('change');
                        }
                        // Then set POS sale after customer dropdown is ready
                        setTimeout(function() {
                            $('#pos_sale_id').val({{ $selectedPosSale->id }}).trigger('change');
                            loadPosSaleDetails({{ $selectedPosSale->id }});
                        }, 300);
                    }, 500);
                });
            @endif
            // Function to load variations for a product
            function loadVariationsForProduct($productSelect, productId, selectedVariationId) {
                const $row = $productSelect.closest('tr');
                const $variationWrapper = $row.find('.variation-wrapper');
                const $variationSelect = $row.find('.variation-select');
                const $unitPriceInput = $row.find('.unit_price');
                
                $.get(`/erp/products/${productId}/variations-list`, function(variations) {
                    if (variations && variations.length > 0) {
                        // Product has variations
                        $variationSelect.empty().append('<option value="">Select Variation</option>');
                        variations.forEach(function(variation) {
                            const selected = (selectedVariationId && variation.id == selectedVariationId) ? 'selected' : '';
                            const price = (variation.price && variation.price > 0) ? variation.price : '';
                            $variationSelect.append(`<option value="${variation.id}" data-price="${price}" ${selected}>${variation.display_name || variation.name}</option>`);
                        });
                        $variationWrapper.show();
                        
                        // If variation is pre-selected, auto-load its price
                        if (selectedVariationId) {
                            const selectedVariation = variations.find(v => v.id == selectedVariationId);
                            if (selectedVariation && $unitPriceInput.length) {
                                if (selectedVariation.price && selectedVariation.price > 0) {
                                    $unitPriceInput.val(parseFloat(selectedVariation.price).toFixed(2));
                                } else {
                                    // Get product price if variation doesn't have specific price
                                    loadProductPrice(productId, $unitPriceInput);
                                }
                            }
                        } else {
                            // No variation selected yet, clear price (wait for variation selection)
                            $unitPriceInput.val('');
                        }
                    } else {
                        // Product has no variations - load product price directly
                        $variationWrapper.hide();
                        $variationSelect.val('').empty();
                        loadProductPrice(productId, $unitPriceInput);
                    }
                }).fail(function() {
                    $variationWrapper.hide();
                    $variationSelect.val('').empty();
                    // Try to load product price even if variations endpoint fails
                    loadProductPrice(productId, $unitPriceInput);
                });
            }
            
            // Helper function to load product price (for sales returns, use sale price)
            function loadProductPrice(productId, $unitPriceInput) {
                if (!$unitPriceInput.length || !productId) return;
                
                $.get(`/erp/products/${productId}/sale-price`, function(resp) {
                    if (resp && typeof resp.price !== 'undefined') {
                        $unitPriceInput.val(parseFloat(resp.price).toFixed(2));
                    }
                }).fail(function() {
                    // If price fetch fails, leave it empty for manual entry
                });
            }
            
            // Handle variation selection change to auto-load price
            $(document).on('change', '.variation-select', function() {
                const $variationSelect = $(this);
                const $row = $variationSelect.closest('tr');
                const $unitPriceInput = $row.find('.unit_price');
                const selectedOption = $variationSelect.find('option:selected');
                const variationPrice = selectedOption.data('price');
                
                if ($unitPriceInput.length) {
                    if (variationPrice && variationPrice !== '') {
                        // Use variation price if available
                        $unitPriceInput.val(parseFloat(variationPrice).toFixed(2));
                    } else {
                        // If variation doesn't have specific price, get product sale price
                        const productId = $row.find('.product-select').val();
                        if (productId) {
                            loadProductPrice(productId, $unitPriceInput);
                        }
                    }
                }
            });
            
            // Handle product change for existing rows (works with both regular select and Select2)
            $(document).on('change select2:select', '.product-select', function() {
                const productId = $(this).val();
                const $row = $(this).closest('tr');
                const $variationWrapper = $row.find('.variation-wrapper');
                const $variationSelect = $row.find('.variation-select');
                const $unitPriceInput = $row.find('.unit_price');
                
                if (productId) {
                    // Clear price first when switching products
                    $unitPriceInput.val('');
                    loadVariationsForProduct($(this), productId, null);
                } else {
                    // Product deselected - clear everything
                    $variationWrapper.hide();
                    $variationSelect.val('').empty();
                    $unitPriceInput.val('');
                }
            });
            
            // Add new item row (updated to use the function)
            $('#addItemRow').on('click', function() {
                // Only allow adding items if POS sale is selected
                const posSaleId = $('#pos_sale_id').val();
                if (!posSaleId) {
                    alert('Please select a POS Sale first');
                    return;
                }
                addItemRow(null, itemIndex);
            });
            // Remove item row
            $('#itemsTable').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
            // When return_to_type changes, populate and show the return_to_id select
            $('#return_to_type').on('change', function() {
                const returnToType = $(this).val();
                const returnToIdSelect = $('#return_to_id');
                returnToIdSelect.hide().prop('required', false).val('').empty();
                if (returnToType === 'branch') {
                    returnToIdSelect.append('<option value="">Select Branch</option>');
                    @foreach ($branches as $branch)
                        returnToIdSelect.append('<option value="{{ $branch->id }}">{{ $branch->name }}</option>');
                    @endforeach
                    returnToIdSelect.show().prop('required', true);
                    safeDestroySelect2(returnToIdSelect);
                } else if (returnToType === 'warehouse') {
                    returnToIdSelect.append('<option value="">Select Warehouse</option>');
                    @foreach ($warehouses as $warehouse)
                        returnToIdSelect.append('<option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>');
                    @endforeach
                    returnToIdSelect.show().prop('required', true);
                    safeDestroySelect2(returnToIdSelect);
                } else if (returnToType === 'employee') {
                    returnToIdSelect.append('<option value="">Select Employee</option>');
                    returnToIdSelect.show().prop('required', true);
                    // Initialize AJAX select2 for employee
                    returnToIdSelect.select2({
                        placeholder: 'Select Employee',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: '/erp/employees/search',
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
                                        return { id: item.id, text: item.name + (item.email ? ' (' + item.email + ')' : '') };
                                    })
                                };
                            },
                            cache: true
                        }
                    });
                }
            });
        });
    </script>
@endsection