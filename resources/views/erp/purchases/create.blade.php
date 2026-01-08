@extends('erp.master')

@section('title', 'Assign Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Assign POS</h2>
                <a href="{{ route('purchase.list') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
            <!-- Select2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <form id="purchaseForm" action="{{ route('purchase.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Ship Location Type</label>
                        <select name="ship_location_type" id="ship_location_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="branch">Branch</option>
                            <option value="warehouse">Warehouse</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="location_id" class="form-label">Location</label>
                        <select name="location_id" id="location_id" class="form-select" required>
                            <option value="">Select Location</option>
                            <!-- Options will be populated by JS -->
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="purchase_date" class="form-label">Assign Date</label>
                        <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Items</label>
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][product_id]" class="form-select product-select" required></select>
                                    <select name="items[0][variation_id]" class="form-select mt-1 variation-select d-none"></select>
                                    <div class="small text-muted mt-1 stock-indicator"></div>
                                </td>
                                <td><input type="number" name="items[0][quantity]" class="form-control quantity" min="0.01" step="0.01" required></td>
                                <td><input type="number" name="items[0][unit_price]" class="form-control unit_price" min="0" step="0.01" required></td>
                                <td class="item-total">0.00</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary duplicate-row" title="Duplicate this row">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger remove-row" disabled title="Remove this row">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <textarea class="form-control description w-80" name="items[0][description]" placeholder="Description"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" id="addItemRow">
                            <i class="fas fa-plus me-1"></i>Add Item
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addMultipleRows">
                            <i class="fas fa-plus-circle me-1"></i>Add 5 Items
                        </button>
                    </div>

                    <!-- Summary Section -->
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Subtotal</th>
                                    <td id="subtotalCell">0.00</td>
                                </tr>
                                <tr>
                                    <th>Total Discount</th>
                                    <td id="totalDiscountCell">0.00</td>
                                </tr>
                                <tr>
                                    <th>Grand Total</th>
                                    <td id="grandTotalCell">0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mb-3 text-end">
                    <button type="submit" class="btn btn-primary">Assign POS</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialize Select2 with AJAX product search (name + SKU)
        function initProductSelect2(selector) {
            $(selector).select2({
                placeholder: 'Search product by name or SKU',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('products.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        const results = data.map(function (item) {
                            let label = item.name;
                            if (item.sku) {
                                label += ' (' + item.sku + ')';
                            }
                            return {
                                id: item.id,
                                text: label,
                                has_variations: item.has_variations
                            };
                        });
                        return { results: results };
                    },
                    cache: true
                }
            });
            
            // Attach event handler after initialization
        }

        // Initial bind
        $(document).ready(function() {
            initProductSelect2('.product-select');
            
            // Only handle standard change event which covers both user selection and programmatic updates
            // This prevents double-firing (once from select2:select, once from change)
            $(document).on('change', '.product-select', function() {
                handleProductChange(this);
            });
            
            // Handle Select2 clearing
            $(document).on('select2:clear', '.product-select', function() {
                handleProductChange(this);
            });
        });

        // Re-initialize Select2 for new product selects after adding a row
        function reinitProductSelect2() {
            initProductSelect2('.product-select');
        }

        let itemIndex = 1;

        function addItemRow() {
            const tbody = $('#itemsTable tbody');
            const row1 = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-select product-select" required></select>
                        <select name="items[${itemIndex}][variation_id]" class="form-select mt-1 variation-select d-none"></select>
                        <div class="small text-muted mt-1 stock-indicator"></div>
                    </td>
                    <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" min="0.01" step="0.01" required></td>
                    <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit_price" min="0" step="0.01" required></td>
                    <td class="item-total">0.00</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary duplicate-row" title="Duplicate this row">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn btn-danger remove-row" title="Remove this row">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            const row2 = `
                <tr>
                    <td colspan="6">
                        <textarea class="form-control description w-80" name="items[${itemIndex}][description]" placeholder="Description"></textarea>
                    </td>
                </tr>
            `;
            tbody.append(row1);
            tbody.append(row2);
            
            // Initialize Select2 for the new product select
            initProductSelect2(tbody.find('tr:last').prev('tr').find('.product-select'));
            itemIndex++;
            updateRemoveButtons();
        }

        $('#addItemRow').on('click', addItemRow);

        $('#addMultipleRows').on('click', function() {
            for (let i = 0; i < 5; i++) {
                addItemRow();
            }
        });

        // Event delegation for remove and duplicate
        $(document).on('click', '.remove-row', function() {
            const row1 = $(this).closest('tr');
            const row2 = row1.next('tr');
            row1.remove();
            row2.remove();
            updateTotals();
            updateRemoveButtons();
        });

        $(document).on('click', '.duplicate-row', function() {
            const row1 = $(this).closest('tr');
            const row2 = row1.next('tr');
            
            // Values to copy
            const productId = row1.find('.product-select').val();
            const productName = row1.find('.product-select option:selected').text();
            const variationId = row1.find('.variation-select').val();
            const quantity = row1.find('.quantity').val();
            const unitPrice = row1.find('.unit_price').val();
            const description = row2.find('.description').val();
            
            addItemRow();
            
            const newRow1 = $('#itemsTable tbody tr').last().prev('tr');
            const newRow2 = $('#itemsTable tbody tr').last();
            
            if (productId) {
                const option = new Option(productName, productId, true, true);
                newRow1.find('.product-select').append(option).trigger('change');
                
                // For variable products, we need to wait for handleProductChange to load variations
                setTimeout(() => {
                    if (variationId) {
                        newRow1.find('.variation-select').val(variationId).trigger('change');
                    }
                    newRow1.find('.quantity').val(quantity);
                    newRow1.find('.unit_price').val(unitPrice);
                    newRow2.find('.description').val(description);
                    updateTotals();
                }, 800);
            } else {
                 newRow1.find('.quantity').val(quantity);
                 newRow1.find('.unit_price').val(unitPrice);
                 newRow2.find('.description').val(description);
                 updateTotals();
            }
        });

        function updateRemoveButtons() {
            const removeButtons = $('.remove-row');
            if (removeButtons.length <= 1) {
                removeButtons.prop('disabled', true);
            } else {
                removeButtons.prop('disabled', false);
            }
        }
        
        // Initial setup for remove buttons
        $(document).ready(function() {
            updateRemoveButtons();
        });

        // Data for locations
        const branches = @json($branches);
        const warehouses = @json($warehouses);

        function populateLocations(type) {
            const select = document.getElementById('location_id');
            select.innerHTML = '<option value="">Select Location</option>';
            let data = [];
            if (type === 'branch') data = branches;
            else if (type === 'warehouse') data = warehouses;
            data.forEach(loc => {
                select.innerHTML += `<option value="${loc.id}">${loc.name}</option>`;
            });
        }
        document.getElementById('ship_location_type').addEventListener('change', function() {
            populateLocations(this.value);
        });

        // Populate variations, fetch price and current stock for a row
        function handleProductChange(selectEl) {
            // Handle both jQuery and native elements
            const $select = $(selectEl);
            const productId = $select.val() || selectEl.value;
            
            // Debug log
            console.log('Product selected:', productId);
            
            const row = $select.closest('tr').length ? $select.closest('tr')[0] : selectEl.closest('tr');
            const unitPriceInput = row ? row.querySelector('.unit_price') : null;
            const stockIndicator = row ? row.querySelector('.stock-indicator') : null;
            const variationSelect = row ? row.querySelector('.variation-select') : null;
            const locationType = document.getElementById('ship_location_type').value;
            const locationId = document.getElementById('location_id').value;

            // Clear indicators if no product selected
            if (!productId) {
                if (unitPriceInput) unitPriceInput.value = '';
                if (stockIndicator) stockIndicator.textContent = '';
                if (variationSelect) {
                    variationSelect.classList.add('d-none');
                    variationSelect.innerHTML = '';
                    variationSelect.removeAttribute('required');
                }
                updateTotals();
                return;
            }
            
            // Ensure we have a valid product ID
            if (!productId || productId === '' || productId === '0') {
                console.warn('Invalid product ID:', productId);
                return;
            }

            // Check global/warehouse stock before proceeding
            // We assume "Assign" distributes from a central Warehouse. 
            // If the user treats "Purchase" as "Assign from Warehouse", we should check if Warehouse/Global stock > 0.
            // Since we don't know the exact source warehouse, we'll check the Product's general availability or stock endpoint.
            
            // Let's use the stock endpoint (which might be warehouse specific) or a new check.
            // Since Purchase creates stock, this check is actually logically inverted for a "Purchase", 
            // but for "Assign" (Distribution), it makes sense. 
            // We will check the "order/product-stocks" endpoint which returns all stocks.
            
            // Reset variation select and clear unit price
            if (variationSelect) {
                variationSelect.classList.add('d-none');
                variationSelect.innerHTML = '';
                variationSelect.removeAttribute('required');
            }
            if (unitPriceInput) {
                unitPriceInput.value = '';
            }

            // Flag to check if we should fetch generic product stock immediately
            let fetchGenericStock = true;

            // Load variations first to check if product has variations
            if (variationSelect) {
                $.get('{{ url('/erp/products') }}/' + productId + '/variations-list', function (vars) {
                    if (Array.isArray(vars) && vars.length > 0) {
                        // Product has variations
                        fetchGenericStock = false; // Defers stock check to variation selection

                        // Show variation select and require it
                        variationSelect.classList.remove('d-none');
                        variationSelect.setAttribute('required', 'required');
                        let optionsHtml = '<option value="">Select Variation</option>';
                        vars.forEach(function (v) {
                            const label = v.display_name || v.name || ('Variation #' + v.id);
                            // Use the display price (with discount if applicable)
                            const price = (typeof v.price !== 'undefined' && v.price !== null && v.price > 0) ? v.price : '';
                            optionsHtml += '<option value="' + v.id + '" data-price="' + price + '" data-base-price="' + (v.base_price || '') + '">' + label + (v.sku ? ' (' + v.sku + ')' : '') + '</option>';
                        });
                        variationSelect.innerHTML = optionsHtml;
                        
                        // Clear stock indicator initially for variable product
                        if (stockIndicator) {
                            stockIndicator.textContent = 'Select a variation to see stock.';
                            stockIndicator.classList.remove('text-info', 'text-danger');
                        }
                    } else {
                        // No variations - auto-fill price from product
                        variationSelect.classList.add('d-none');
                        variationSelect.removeAttribute('required');
                        
                        // Fetch product price
                        $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                            if (unitPriceInput && resp && typeof resp.price !== 'undefined' && resp.price !== null && resp.price !== '' && resp.price > 0) {
                                unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                                updateTotals();
                            }
                        });

                        // Fetch generic stock since no variations
                        checkGenericStock(productId, row, $select);
                    }
                }).fail(function() {
                    // Fallback if variations check fails - assume simple product
                    checkGenericStock(productId, row, $select);
                });
            } else {
                // No variation select element found - just get product price and stock
                 $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                    if (unitPriceInput && resp && typeof resp.price !== 'undefined' && resp.price !== null && resp.price !== '' && resp.price > 0) {
                        unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                        updateTotals();
                    }
                });
                checkGenericStock(productId, row, $select);
            }
        }
        
        // Helper function to check stock for simple products
        function checkGenericStock(productId, row, $select) {
            const stockIndicator = row ? row.querySelector('.stock-indicator') : null;
            
            $.get('{{ url('/erp/order/product-stocks') }}/' + productId, function (resp) {
                if (!resp || !resp.success || !Array.isArray(resp.stocks)) {
                    // Fallback or error
                } else {
                    const stocks = resp.stocks;
                    // Calculate total warehouse stock
                    const totalWarehouseStock = stocks
                        .filter(s => s.type === 'warehouse')
                        .reduce((sum, s) => sum + parseFloat(s.quantity), 0);

                    // If total warehouse stock is 0
                    if (totalWarehouseStock <= 0) {
                        alert('This product has 0 stock in Warehouses and cannot be assigned.');
                        $select.val(null).trigger('change'); 
                        return; 
                    }

                    // Store max stock on quantity input and update indicator
                    if (row) {
                        const qtyInput = row.querySelector('.quantity');
                        if (qtyInput) {
                            qtyInput.setAttribute('max', totalWarehouseStock);
                            qtyInput.dataset.maxStock = totalWarehouseStock; // For easy access
                        }
                        if (stockIndicator) {
                            stockIndicator.textContent = 'Available Warehouse Stock: ' + totalWarehouseStock;
                            stockIndicator.className = 'small mt-1 stock-indicator text-info';
                        }
                    }
                }
            });
        }

        // Handle variation selection change
        $(document).on('change', '.variation-select', function() {
            const selectEl = this;
            const $select = $(selectEl);
            const row = selectEl.closest('tr');
            const unitPriceInput = row ? row.querySelector('.unit_price') : null;
            const stockIndicator = row ? row.querySelector('.stock-indicator') : null;
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const variationId = $select.val();
            
            // Get product ID from the sibling product select
            const productSelect = row.querySelector('.product-select');
            const productId = $(productSelect).val();

            if (!variationId) {
                if (stockIndicator) stockIndicator.textContent = '';
                return;
            }
            
            // 1. Update Price
            if (unitPriceInput && selectedOption) {
                const price = selectedOption.getAttribute('data-price');
                if (price && price !== '' && price !== 'null' && price !== 'undefined') {
                    const priceValue = parseFloat(price);
                    if (!isNaN(priceValue) && priceValue > 0) {
                        unitPriceInput.value = priceValue.toFixed(2);
                        updateTotals();
                    }
                }
            }

            // 2. Check Stock for this specific variation
            if (productId && variationId) {
                const url = `{{ url('/erp/products') }}/${productId}/variations/${variationId}/stock/levels`;
                
                $.get(url, function(resp) {
                    if (resp && resp.warehouse_stocks) {
                        // Calculate total available warehouse stock for this variation
                        // Note: resp.available_stock is global, we want per-warehouse for Assign
                        // Based on controller, 'warehouse_stocks' is an array of objects { quantity: ... }
                        
                        const totalVarWarehouseStock = resp.warehouse_stocks.reduce((sum, s) => sum + parseFloat(s.quantity), 0);
                        
                        if (totalVarWarehouseStock <= 0) {
                            alert('This variation has 0 stock in Warehouses and cannot be assigned.');
                            $select.val('').trigger('change.select2'); // Reset variation selection
                             if (stockIndicator) {
                                stockIndicator.textContent = 'Out of Stock (Warehouse)';
                                stockIndicator.className = 'small mt-1 stock-indicator text-danger';
                            }
                            // Also disable quantity?
                            const qtyInput = row.querySelector('.quantity');
                            if (qtyInput) {
                                qtyInput.value = '';
                                qtyInput.dataset.maxStock = 0;
                            }
                            return;
                        }

                        // Update Limit
                        if (row) {
                             const qtyInput = row.querySelector('.quantity');
                            if (qtyInput) {
                                qtyInput.setAttribute('max', totalVarWarehouseStock);
                                qtyInput.dataset.maxStock = totalVarWarehouseStock;
                            }
                            if (stockIndicator) {
                                stockIndicator.textContent = 'Available Warehouse Stock: ' + totalVarWarehouseStock;
                                stockIndicator.className = 'small mt-1 stock-indicator text-info';
                            }
                        }
                    } else {
                         if (stockIndicator) stockIndicator.textContent = 'Stock info unavailable';
                    }
                }).fail(function() {
                    console.error('Failed to fetch variation stock');
                });
            }
        });
        // Calculate item total and update summary
        function updateTotals() {
            let subtotal = 0;
            document.querySelectorAll('#itemsTable tbody tr').forEach((row) => {
                // Only process item rows (not description rows)
                if (!row.querySelector('.quantity')) return;
                const qty = parseFloat(row.querySelector('.quantity')?.value) || 0;
                const price = parseFloat(row.querySelector('.unit_price')?.value) || 0;
                let total = (qty * price);
                if (total < 0) total = 0;
                row.querySelector('.item-total').textContent = total.toFixed(2);
                subtotal += qty * price;
            });
            const grandTotal = subtotal;
            document.getElementById('subtotalCell').textContent = subtotal.toFixed(2);
            document.getElementById('totalDiscountCell').textContent = '0.00';
            document.getElementById('grandTotalCell').textContent = grandTotal.toFixed(2);
        }
        document.querySelector('#itemsTable').addEventListener('input', function(e) {
            if (
                e.target.classList.contains('quantity') ||
                e.target.classList.contains('unit_price')
            ) {
                // Validate quantity if it's a quantity input
                if (e.target.classList.contains('quantity')) {
                    const qty = parseFloat(e.target.value);
                    const maxStock = parseFloat(e.target.dataset.maxStock);
                    
                    if (!isNaN(qty) && !isNaN(maxStock) && qty > maxStock) {
                        alert('You cannot assign more than available stock (' + maxStock + ').');
                        e.target.value = maxStock; // Reset to max
                    }
                }
                updateTotals();
            }
        });
    </script>
@endsection