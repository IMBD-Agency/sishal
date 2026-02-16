@extends('erp.master')

@section('title', 'Create Order Return')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .form-section-title {
                font-size: 0.85rem;
                font-weight: 700;
                text-uppercase: uppercase;
                letter-spacing: 0.05em;
                color: #6c757d;
                margin-bottom: 1.5rem;
                display: flex;
                align-items: center;
            }
            .form-section-title::after {
                content: '';
                flex: 1;
                height: 1px;
                background: #e9ecef;
                margin-left: 1rem;
            }
            .card { border-radius: 12px; }
            .form-control, .form-select {
                padding: 0.6rem 0.8rem;
                border-color: #e9ecef;
                border-radius: 8px;
            }
            .form-control:focus, .form-select:focus {
                border-color: #0d6efd;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05);
            }
            .input-group-text { border-radius: 8px 0 0 8px; border-color: #e9ecef; background: #f8f9fa; }
            .product-row { transition: all 0.2s; }
            .product-row:hover { background-color: #fcfdfe; }
            .btn-remove { 
                width: 32px; height: 32px; 
                display: flex; align-items: center; justify-content: center;
                border-radius: 8px; transition: all 0.2s;
            }
            .btn-remove:hover { background-color: #dc3545; color: white; }
            .select2-container--default .select2-selection--single {
                height: 42px !important;
                border: 1px solid #e9ecef !important;
                border-radius: 8px !important;
                display: flex;
                align-items: center;
            }
        </style>

        <!-- Header -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('orderReturn.list') }}" class="text-decoration-none">Order Returns</a></li>
                            <li class="breadcrumb-item active">Create Return</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Create Order Return</h2>
                    <p class="text-muted mb-0">Record a return for an E-commerce order and process restock.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('orderReturn.list') }}" class="btn btn-light border px-4 rounded-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="orderReturnForm" action="{{ route('orderReturn.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <!-- Left Column: Basic Info -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="form-section-title">Return Source</div>
                                
                                <div class="mb-3">
                                    <label for="order_id" class="form-label fw-bold small">Order Reference <span class="text-danger">*</span></label>
                                    <select name="order_id" id="order_id" class="form-select" required>
                                        <option value="">Search by Order # or Customer...</option>
                                    </select>
                                    <small class="text-muted mt-1 d-block" id="order_hint">
                                        <i class="fas fa-search me-1"></i> Search by Order Number, Name, or Phone.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_id" class="form-label fw-bold small">Customer <span class="text-secondary">(Auto-filled)</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select">
                                        <option value="">Select or search order first</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-section-title mt-4">Return Details</div>

                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-bold small">Return Date <span class="text-danger">*</span></label>
                                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="refund_type" class="form-label fw-bold small">Refund Method <span class="text-danger">*</span></label>
                                    <select name="refund_type" id="refund_type" class="form-select" required>
                                        <option value="none">No Refund</option>
                                        <option value="cash">Cash Refund</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="credit">Store Credit</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="return_to_type" class="form-label fw-bold small">Restock Location <span class="text-danger">*</span></label>
                                    <select name="return_to_type" id="return_to_type" class="form-select mb-2" required>
                                        <option value="">Select Location Type</option>
                                        <option value="branch">Branch Office</option>
                                        <option value="warehouse">Central Warehouse</option>
                                        <option value="employee">Field Employee</option>
                                    </select>
                                    <select name="return_to_id" id="return_to_id" class="form-select" style="display:none;" required>
                                        <option value="">Select Specific Location</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="form-section-title">Additional Info</div>
                                <div class="mb-3">
                                    <label for="reason" class="form-label fw-bold small">Primary Reason</label>
                                    <input type="text" name="reason" id="reason" class="form-control" placeholder="e.g., Damaged item, customer choice">
                                </div>
                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-bold small">Internal Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter any extra details..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Table -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100 mb-4">
                            <div class="card-header bg-white border-bottom p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Return Items</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="addItemRow" style="display:none;">
                                        <i class="fas fa-plus me-1"></i>Add Manual Item
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="itemsTable">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-4 py-3" style="width: 40%;">Product Specification</th>
                                                <th class="py-3" style="width: 15%;">Qty</th>
                                                <th class="py-3" style="width: 20%;">Price (৳)</th>
                                                <th class="py-3">Reason</th>
                                                <th class="pe-4 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Rows loaded via JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="emptyState" class="text-center py-5">
                                    <i class="fas fa-shopping-bag fs-1 text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted">Select an order to automatically load items.</p>
                                </div>
                            </div>
                        </div>


                            <div class="card-footer bg-light border-0 p-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 shadow-sm fw-bold">
                                    <i class="fas fa-save me-2"></i>Save Return
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let itemIndex = 0;
        let newItemIndex = 0;
        let availableProducts = [];
        
        function safeDestroySelect2($element) {
            if ($element.length && $element.hasClass('select2-hidden-accessible')) {
                try { $element.select2('destroy'); } catch(e) {}
            }
        }
        
        function toggleEmptyState() {
            if ($('#itemsTable tbody tr').length > 0) {
                $('#emptyState').hide();
                $('#addItemRow').show();
            } else {
                $('#emptyState').show();
                $('#addItemRow').hide();
            }

            if ($('#newItemsTable tbody tr').length > 0) {
                $('#newItemsEmptyState').hide();
            } else {
                $('#newItemsEmptyState').show();
            }
            calculateNetPayment();
        }

        function calculateNetPayment() {
            let totalReturn = 0;
            let totalPurchase = 0;

            $('#itemsTable tbody tr').each(function() {
                const qty = parseFloat($(this).find('input[name*="[returned_qty]"]').val()) || 0;
                const price = parseFloat($(this).find('input[name*="[unit_price]"]').val()) || 0;
                totalReturn += qty * price;
            });

            $('#newItemsTable tbody tr').each(function() {
                const qty = parseFloat($(this).find('.new-qty').val()) || 0;
                const price = parseFloat($(this).find('.new-price').val()) || 0;
                totalPurchase += qty * price;
            });

            const net = totalPurchase - totalReturn;
            const $netEl = $('#netPaymentAmount');
            
            if (net > 0) {
                $netEl.html(`<span class="text-danger">Customer Pays: ${net.toFixed(2)}</span>`);
                $('#refund_type').val('none').trigger('change'); // If customer pays, usually no refund, but maybe credit adjustment
            } else if (net < 0) {
                $netEl.html(`<span class="text-success">Refund/Credit: ${Math.abs(net).toFixed(2)}</span>`);
            } else {
                $netEl.text('0.00');
            }
        }

        $(document).ready(function() {
            // Initial Order Select2
            $('#order_id').select2({
                placeholder: 'Search by Order #, Customer Name or Phone...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route("order.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ 
                        q: params.term || '', 
                        customer_id: $('#customer_id').val() 
                    }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            // Customer Select2
            $('#customer_id').select2({
                placeholder: 'Search Customer...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '/erp/customers/search',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(item => ({ id: item.id, text: item.name + (item.phone ? ' (' + item.phone + ')' : '') }))
                    }),
                    cache: true
                }
            });

            // Handle Pre-selected Order
            @if(isset($preSelectedOrder) && $preSelectedOrder)
                const preOrderId = "{{ $preSelectedOrder->id }}";
                const preOrderNum = "{{ $preSelectedOrder->order_number }}";
                const preCustId = "{{ $preSelectedOrder->customer_id ?? $preSelectedOrder->created_by }}";
                const preCustName = "{{ $preSelectedOrder->name ?? ($preSelectedOrder->customer->name ?? 'Customer') }}";
                
                // Set Order
                const orderOption = new Option(preOrderNum, preOrderId, true, true);
                $('#order_id').append(orderOption).trigger('change');
                
                // Set Customer
                const custOption = new Option(preCustName, preCustId, true, true);
                $('#customer_id').append(custOption).trigger('change');
                
                loadOrderDetails(preOrderId);
            @endif

            // Default Fulfillment Source
            @if(isset($generalSettings) && $generalSettings->ecommerce_source_type)
                const defType = "{{ $generalSettings->ecommerce_source_type }}";
                const defId = "{{ $generalSettings->ecommerce_source_id }}";
                $('#return_to_type').val(defType).trigger('change');
                setTimeout(() => $('#return_to_id').val(defId).trigger('change'), 100);
            @else
                // Fallback to warehouse if no specific setting
                if ($('#return_to_type option[value="warehouse"]').length) {
                    $('#return_to_type').val('warehouse').trigger('change');
                }
            @endif

            $('#order_id').on('change select2:select', function() {
                const orderId = $(this).val();
                if (orderId) {
                    loadOrderDetails(orderId);
                } else {
                    availableProducts = [];
                    $('#itemsTable tbody').empty();
                    toggleEmptyState();
                }
            });

            function loadOrderDetails(orderId) {
                const $tableBody = $('#itemsTable tbody');
                $tableBody.html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading items...</td></tr>');
                
                $.ajax({
                    url: `/erp/order/${orderId}/details`,
                    method: 'GET',
                    success: function(response) {
                        $tableBody.empty();
                        // OrderController@show returns data directly if ajax, or in response object if you structured it that way
                        // Based on the code viewed earlier, it returns a JSON object with 'id', 'customer_id', 'items', etc.
                        const data = response; 
                        
                        if (data && data.items) {
                            // Auto-set Customer
                            if (data.customer_id) {
                                const $customerSelect = $('#customer_id');
                                if ($customerSelect.val() != data.customer_id) {
                                    const customerName = data.customer_name || 'Customer #' + data.customer_id;
                                    const option = new Option(customerName, data.customer_id, true, true);
                                    $customerSelect.empty().append(option).trigger('change');
                                }
                            }

                            // Auto-set Restock Location based on first item's fulfillment source
                            const firstItem = data.items[0];
                            if (firstItem && firstItem.current_position_type) {
                                $('#return_to_type').val(firstItem.current_position_type).trigger('change');
                                if (firstItem.current_position_id) {
                                    // Small delay to ensure return_to_id is populated by the change event
                                    setTimeout(() => {
                                        $('#return_to_id').val(firstItem.current_position_id).trigger('change');
                                    }, 150);
                                }
                            }

                            availableProducts = data.items.map(item => ({
                                id: parseInt(item.product_id),
                                name: item.product_name || 'Product #' + item.product_id
                            }));
                            
                            data.items.forEach((item, index) => addItemRow(item, index));
                        }
                        toggleEmptyState();
                    },
                    error: function() {
                        $tableBody.html('<tr><td colspan="5" class="text-center text-danger py-4">Failed to load order details.</td></tr>');
                    }
                });
            }

            function addItemRow(itemData, index) {
                const i = index ?? itemIndex++;
                const productOptions = availableProducts.map(p => 
                    `<option value="${p.id}" ${itemData && itemData.product_id == p.id ? 'selected' : ''}>${p.name}</option>`
                ).join('');

                const row = $(`
                    <tr class="product-row">
                        <td class="ps-4">
                            <select name="items[${i}][product_id]" class="form-select product-select select2-basic" required>
                                <option value="">Select Product</option>
                                ${productOptions}
                            </select>
                            <div class="variation-wrapper mt-2" style="${itemData && itemData.variation_id ? '' : 'display:none;'}">
                                <select name="items[${i}][variation_id]" class="form-select variation-select small py-1">
                                    <option value="${itemData && itemData.variation_id ? itemData.variation_id : ''}">
                                        ${itemData && itemData.variation_name ? itemData.variation_name : 'Standard Variation'}
                                    </option>
                                </select>
                            </div>
                            <input type="hidden" name="items[${i}][order_item_id]" value="${itemData ? itemData.id : ''}">
                        </td>
                        <td>
                            <input type="number" name="items[${i}][returned_qty]" class="form-control" min="0.01" step="0.01" value="${itemData ? itemData.quantity : 1}" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-0 bg-transparent">৳</span>
                                <input type="number" name="items[${i}][unit_price]" class="form-control border-0 bg-light unit_price" min="0" step="0.01" value="${itemData ? itemData.unit_price : ''}" required>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="items[${i}][reason]" class="form-control form-control-sm border-0 bg-light" placeholder="Defect?">
                        </td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove shadow-none">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                `);

                $('#itemsTable tbody').append(row);
                const $productSelect = row.find('.product-select');
                
                $productSelect.select2({ width: '100%' });
                
                if (itemData && itemData.product_id && !itemData.variation_name) {
                    loadVariationsForProduct($productSelect, itemData.product_id, itemData.variation_id);
                }

                $productSelect.on('change', function() {
                    const productId = $(this).val();
                    if (productId) loadVariationsForProduct($(this), productId, null);
                });
                
                 row.find('input').on('input', calculateNetPayment);

                toggleEmptyState();
            }
            
            // New Items Functionality
             function addNewItemRow() {
                const i = newItemIndex++;
                const row = $(`
                    <tr class="product-row bg-white">
                        <td class="ps-4">
                            <select name="new_items[${i}][product_id]" class="form-select new-product-select select2-ajax" required>
                                <option value="">Search Product...</option>
                            </select>
                            <div class="variation-wrapper mt-2" style="display:none;">
                                <select name="new_items[${i}][variation_id]" class="form-select new-variation-select small py-1">
                                    <option value="">Select Variation</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="new_items[${i}][qty]" class="form-control new-qty" min="1" step="1" value="1" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-0 bg-transparent">৳</span>
                                <input type="number" name="new_items[${i}][unit_price]" class="form-control border-0 bg-light new-price" min="0" step="0.01" required>
                            </div>
                        </td>
                        <td class="small text-muted align-middle stock-info">
                            -
                        </td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove shadow-none">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                `);

                $('#newItemsTable tbody').append(row);
                
                // Initialize Select2 with AJAX
                row.find('.new-product-select').select2({
                    width: '100%',
                    placeholder: 'Search Product...',
                    ajax: {
                        url: '{{ route("products.search") }}', // Make sure this route exists
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(function (item) {
                                    return { id: item.id, text: item.name + ' (' + (item.sku || 'No SKU') + ')' };
                                })
                            };
                        },
                        cache: true
                    }
                });

                // Events
                row.find('.new-product-select').on('change', function() {
                    const productId = $(this).val();
                    if(productId) {
                        loadNewItemVariations($(this), productId);
                    }
                });
                
                row.find('.new-qty, .new-price').on('input', calculateNetPayment);

                toggleEmptyState();
            }

            $('#addNewItemRow').on('click', addNewItemRow);

            function loadNewItemVariations($productSelect, productId) {
                const $row = $productSelect.closest('tr');
                const $variationWrapper = $row.find('.variation-wrapper');
                const $variationSelect = $row.find('.new-variation-select');
                const $priceInput = $row.find('.new-price');
                const $stockInfo = $row.find('.stock-info');
                
                $priceInput.val('');
                $stockInfo.text('Checking...');
                
                $.get(`/erp/products/${productId}/variations-list`, function(variations) {
                     // Check if product has variations based on list (or if variations array is empty but we should check product structure)
                     // The previous logic assumed if list is empty, no variations.
                    if (variations && variations.length > 0) {
                        $variationSelect.empty().append('<option value="">Select Variation</option>');
                        variations.forEach(v => {
                            $variationSelect.append(`<option value="${v.id}" data-price="${v.price || ''}" data-stock="${v.stock}">${v.display_name || v.name}</option>`);
                        });
                        $variationWrapper.show();
                        $stockInfo.text('-');
                    } else {
                        $variationWrapper.hide();
                        $variationSelect.empty(); // validation safety
                        // Load product price & stock if no variations
                         $.get(`/erp/products/${productId}/sale-price`, resp => {
                            if (resp) {
                                if(resp.price) $priceInput.val(parseFloat(resp.price).toFixed(2));
                                if(resp.stock !== undefined) {
                                     $stockInfo.text(resp.stock + ' in stock');
                                     $row.find('.new-qty').attr('max', resp.stock);
                                }
                                calculateNetPayment();
                            }
                        });
                    }
                });
            }
            
            $(document).on('change', '.new-variation-select', function() {
                const $opt = $(this).find('option:selected');
                const price = $opt.data('price');
                const stock = $opt.data('stock');
                const $row = $(this).closest('tr');
                
                if (price) $row.find('.new-price').val(parseFloat(price).toFixed(2));
                if (stock !== undefined) {
                    $row.find('.stock-info').text(stock + ' in stock');
                     $row.find('.new-qty').attr('max', stock);
                }
                calculateNetPayment();
            });


            $(document).on('click', '.btn-remove', function() {
                $(this).closest('tr').fadeOut(200, function() {
                    $(this).remove();
                    toggleEmptyState();
                    calculateNetPayment();
                });
            });

            $('#addItemRow').on('click', () => addItemRow(null));

            function loadVariationsForProduct($productSelect, productId, selectedVariationId) {
                const $row = $productSelect.closest('tr');
                const $variationWrapper = $row.find('.variation-wrapper');
                const $variationSelect = $row.find('.variation-select');
                const $priceInput = $row.find('.unit_price');
                
                $.get(`/erp/products/${productId}/variations-list`, function(variations) {
                    if (variations && variations.length > 0) {
                        $variationSelect.empty().append('<option value="">Select Variation</option>');
                        variations.forEach(v => {
                            const selected = v.id == selectedVariationId ? 'selected' : '';
                            $variationSelect.append(`<option value="${v.id}" data-price="${v.price || ''}" ${selected}>${v.display_name || v.name}</option>`);
                        });
                        $variationWrapper.show();
                    } else {
                        $variationWrapper.hide();
                        if (!$priceInput.val()) loadProductPrice(productId, $priceInput);
                    }
                });
            }

            function loadProductPrice(productId, $input) {
                $.get(`/erp/products/${productId}/sale-price`, resp => {
                    if (resp && resp.price) {
                         $input.val(parseFloat(resp.price).toFixed(2));
                         calculateNetPayment();
                    }
                });
            }

            $(document).on('change', '.variation-select', function() {
                const price = $(this).find('option:selected').data('price');
                if (price) $(this).closest('tr').find('.unit_price').val(parseFloat(price).toFixed(2));
                calculateNetPayment();
            });

            $('#return_to_type').on('change', function() {
                const type = $(this).val();
                const $idSelect = $('#return_to_id');
                $idSelect.hide().empty().prop('required', false);
                
                if (type === 'branch' || type === 'warehouse') {
                    const options = type === 'branch' ? @json($branches) : @json($warehouses);
                    $idSelect.append(`<option value="">Select ${type.charAt(0).toUpperCase() + type.slice(1)}</option>`);
                    options.forEach(opt => $idSelect.append(`<option value="${opt.id}">${opt.name}</option>`));
                    $idSelect.show().prop('required', true);
                    safeDestroySelect2($idSelect);
                } else if (type === 'employee') {
                    $idSelect.show().prop('required', true).select2({
                        placeholder: 'Search Employee...',
                        width: '100%',
                        ajax: {
                            url: '/erp/employees/search',
                            dataType: 'json',
                            data: params => ({ q: params.term }),
                            processResults: data => ({ results: data.map(i => ({ id: i.id, text: i.name })) })
                        }
                    });
                }
            });

            // Initial state
            toggleEmptyState();
        });
    </script>
@endsection
