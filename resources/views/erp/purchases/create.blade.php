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
            
            $.get('{{ url('/erp/order/product-stocks') }}/' + productId, function (resp) {
                if (!resp || !resp.success || !Array.isArray(resp.stocks)) {
                    // Fallback or error
                } else {
                    const stocks = resp.stocks;
                    // Calculate total warehouse stock (assuming source is warehouse)
                    const totalWarehouseStock = stocks
                        .filter(s => s.type === 'warehouse')
                        .reduce((sum, s) => sum + parseFloat(s.quantity), 0);

                    // If total warehouse stock is 0, prevent selection
                    if (totalWarehouseStock <= 0) {
                        alert('This product has 0 stock in Warehouses and cannot be assigned.');
                        // Clear selection
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
                            stockIndicator.classList.add('text-info');
                        }
                    }
                }
            });

            // Reset variation select and clear unit price
            if (variationSelect) {
                variationSelect.classList.add('d-none');
                variationSelect.innerHTML = '';
                variationSelect.removeAttribute('required');
            }
            if (unitPriceInput) {
                unitPriceInput.value = '';
            }

            // Load variations first to check if product has variations
            if (variationSelect) {
                $.get('{{ url('/erp/products') }}/' + productId + '/variations-list', function (vars) {
                    if (Array.isArray(vars) && vars.length > 0) {
                        // Product has variations - show variation select and require it
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
                        // Don't auto-fill price here - wait for variation selection
                    } else {
                        // No variations - auto-fill price from product (with discount logic)
                        variationSelect.classList.add('d-none');
                        variationSelect.removeAttribute('required');
                        $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                            // Use display price (with discount if applicable)
                            if (unitPriceInput && resp && typeof resp.price !== 'undefined' && resp.price !== null && resp.price !== '' && resp.price > 0) {
                                unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                                updateTotals();
                            }
                        });
                    }
                });
            } else {
                // No variation select element - just get product price
                $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                    // Use display price (with discount if applicable)
                    if (unitPriceInput && resp && typeof resp.price !== 'undefined' && resp.price !== null && resp.price !== '' && resp.price > 0) {
                        unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                        updateTotals();
                    }
                });
            }

            // Show current stock at selected location (if location selected)
            if (locationType && locationId && stockIndicator) {
                $.get('{{ url('/erp/order/product-stocks') }}/' + productId, function (resp) {
                    if (!resp || !resp.success || !Array.isArray(resp.stocks)) {
                        stockIndicator.textContent = '';
                        return;
                    }
                    const stocks = resp.stocks;
                    let match = null;
                    if (locationType === 'branch') {
                        match = stocks.find(s => s.type === 'branch' && String(s.branch_id) === String(locationId));
                    } else if (locationType === 'warehouse') {
                        match = stocks.find(s => s.type === 'warehouse' && String(s.warehouse_id) === String(locationId));
                    }
                    const qty = match ? match.quantity : 0;
                    stockIndicator.textContent = 'Current stock here: ' + qty;
                }).fail(function() {
                    stockIndicator.textContent = '';
                });
            } else if (stockIndicator) {
                stockIndicator.textContent = 'Select location to see current stock.';
            }
        }

        // Function to add a new item row
        function addItemRow(copyFromRow = null) {
            const tbody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');
            
            // Get values from row to copy, if provided
            let copiedProductId = '';
            let copiedVariationId = '';
            let copiedPrice = '';
            let copiedQuantity = '';
            let copiedDescription = '';
            
            if (copyFromRow) {
                const productSelect = copyFromRow.querySelector('.product-select');
                const variationSelect = copyFromRow.querySelector('.variation-select');
                const priceInput = copyFromRow.querySelector('.unit_price');
                const quantityInput = copyFromRow.querySelector('.quantity');
                const descTextarea = copyFromRow.querySelector('.description');
                
                copiedProductId = $(productSelect).val() || '';
                copiedVariationId = $(variationSelect).val() || '';
                copiedPrice = priceInput ? priceInput.value : '';
                copiedQuantity = quantityInput ? quantityInput.value : '';
                copiedDescription = descTextarea ? descTextarea.value : '';
            }
            
            row.innerHTML = `
                <td>
                    <select name="items[${itemIndex}][product_id]" class="form-select product-select" required data-copied-id="${copiedProductId}"></select>
                    <select name="items[${itemIndex}][variation_id]" class="form-select mt-1 variation-select d-none" data-copied-id="${copiedVariationId}"></select>
                    <div class="small text-muted mt-1 stock-indicator"></div>
                </td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" min="0.01" step="0.01" value="${copiedQuantity}" required></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit_price" min="0" step="0.01" value="${copiedPrice}" required></td>
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
            `;
            tbody.appendChild(row);
            
            // Add description row
            const descRow = document.createElement('tr');
            descRow.innerHTML = `
                <td colspan="6">
                    <textarea class="form-control description" name="items[${itemIndex}][description]" placeholder="Description">${copiedDescription}</textarea>
                </td>
            `;
            tbody.appendChild(descRow);
            
            // Initialize Select2
            reinitProductSelect2();
            const newProductSelect = row.querySelector('.product-select');
            const newVariationSelect = row.querySelector('.variation-select');
            
            // Bind Select2 events
            if (newProductSelect) {
                $(newProductSelect).on('select2:select', function() {
                    handleProductChange(this);
                });
            }
            
            // Restore copied values if duplicating
            if (copyFromRow && copiedProductId) {
                // Get the product name from the original select
                const originalProductSelect = copyFromRow.querySelector('.product-select');
                const $originalSelect = $(originalProductSelect);
                const productText = $originalSelect.find('option:selected').text() || $originalSelect.select2('data')[0]?.text || '';
                
                // Wait for Select2 to initialize, then restore product
                setTimeout(function() {
                    const $newSelect = $(newProductSelect);
                    
                    // Create option if it doesn't exist (for Select2 AJAX)
                    if (!$newSelect.find(`option[value="${copiedProductId}"]`).length && productText) {
                        const newOption = new Option(productText, copiedProductId, true, true);
                        $newSelect.append(newOption);
                    }
                    
                    // Set value and trigger change to load variations
                    $newSelect.val(copiedProductId).trigger('change');
                    
                    // After product variations are loaded, restore variation
                    setTimeout(function() {
                        if (copiedVariationId && newVariationSelect) {
                            const $newVariationSelect = $(newVariationSelect);
                            const originalVariationSelect = copyFromRow.querySelector('.variation-select');
                            const $originalVariationSelect = $(originalVariationSelect);
                            const variationText = $originalVariationSelect.find('option:selected').text() || '';
                            
                            // Wait a bit more for variations to load
                            setTimeout(function() {
                                if ($newVariationSelect.find(`option[value="${copiedVariationId}"]`).length) {
                                    $newVariationSelect.val(copiedVariationId).trigger('change');
                                } else if (variationText) {
                                    $newVariationSelect.append(new Option(variationText, copiedVariationId, true, true));
                                    $newVariationSelect.val(copiedVariationId).trigger('change');
                                }
                            }, 300);
                        }
                    }, 1000);
                }, 500);
            }
            
            // Auto-focus on quantity field for quick entry (only if not duplicating)
            if (!copyFromRow) {
                setTimeout(function() {
                    const quantityInput = row.querySelector('.quantity');
                    if (quantityInput) {
                        quantityInput.focus();
                        quantityInput.select();
                    }
                }, 200);
            } else {
                // If duplicating, focus on quantity to allow quick edit
                setTimeout(function() {
                    const quantityInput = row.querySelector('.quantity');
                    if (quantityInput) {
                        quantityInput.focus();
                        quantityInput.select();
                    }
                }, 1000);
            }
            
            itemIndex++;
            updateTotals();
        }

        // Dynamic items
        let itemIndex = 1;
        
        // Add single item
        document.getElementById('addItemRow').addEventListener('click', function() {
            addItemRow();
        });
        
        // Add multiple items at once
        document.getElementById('addMultipleRows').addEventListener('click', function() {
            for (let i = 0; i < 5; i++) {
                addItemRow();
            }
        });
        
        // Duplicate row functionality
        $(document).on('click', '.duplicate-row', function() {
            const row = $(this).closest('tr');
            addItemRow(row[0]);
        });
        
        // Auto-add new row on Enter key in quantity or price field
        $(document).on('keydown', '.quantity, .unit_price', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const currentRow = $(this).closest('tr');
                // Check if this is the last row
                const allRows = $('#itemsTable tbody tr').filter(function() {
                    return $(this).find('.product-select').length > 0;
                });
                if (currentRow.index() === allRows.length - 1) {
                    addItemRow();
                } else {
                    // Move to next row
                    const nextRow = allRows.eq(allRows.index(currentRow) + 1);
                    const nextInput = nextRow.find('.quantity');
                    if (nextInput.length) {
                        nextInput.focus().select();
                    }
                }
            }
        });
        // Remove row functionality
        $(document).on('click', '.remove-row', function() {
            const itemRow = $(this).closest('tr');
            const descRow = itemRow.next('tr');
            itemRow.remove();
            if (descRow.length && descRow.find('textarea.description').length) {
                descRow.remove();
            }
            updateTotals();
            
            // Re-enable remove button on first row if it was disabled
            const firstRow = $('#itemsTable tbody tr').first();
            if (firstRow.length) {
                const firstRemoveBtn = firstRow.find('.remove-row');
                if (firstRemoveBtn.length && $('#itemsTable tbody tr').length <= 2) {
                    firstRemoveBtn.prop('disabled', true);
                }
            }
        });

        // Handle variation selection change
        $(document).on('change', '.variation-select', function() {
            const selectEl = this;
            const row = selectEl.closest('tr');
            const unitPriceInput = row ? row.querySelector('.unit_price') : null;
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            
            if (unitPriceInput && selectedOption) {
                const price = selectedOption.getAttribute('data-price');
                console.log('Variation price selected:', price);
                if (price && price !== '' && price !== 'null' && price !== 'undefined') {
                    const priceValue = parseFloat(price);
                    if (!isNaN(priceValue) && priceValue > 0) {
                        // Use variation price (already includes discount if applicable)
                        unitPriceInput.value = priceValue.toFixed(2);
                        updateTotals();
                    } else {
                        // If variation price is invalid, fallback to product price
                        const productSelect = row.querySelector('.product-select');
                        const productId = $(productSelect).val();
                        if (productId) {
                            $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                                console.log('Product price for variation (fallback):', resp);
                                if (resp && typeof resp.price !== 'undefined' && resp.price !== null && resp.price !== '' && resp.price > 0) {
                                    unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                                    updateTotals();
                                }
                            });
                        } else {
                            unitPriceInput.value = '';
                            updateTotals();
                        }
                    }
                } else {
                    // If no price in variation, use product price
                    const productSelect = row.querySelector('.product-select');
                    const productId = $(productSelect).val();
                    if (productId) {
                        $.get('{{ url('/erp/products') }}/' + productId + '/price', function (resp) {
                            console.log('Product price for variation:', resp);
                            if (resp && typeof resp.price !== 'undefined' && resp.price !== null && resp.price !== '' && resp.price > 0) {
                                unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                                updateTotals();
                            }
                        });
                    } else {
                        unitPriceInput.value = '';
                        updateTotals();
                    }
                }
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