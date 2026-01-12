@extends('erp.master')

@section('title', 'Create Sale Return')

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
                            <li class="breadcrumb-item"><a href="{{ route('saleReturn.list') }}" class="text-decoration-none">Sale Returns</a></li>
                            <li class="breadcrumb-item active">Create Return</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Create Sale Return</h2>
                    <p class="text-muted mb-0">Record a new product return and update inventory accordingly.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('saleReturn.list') }}" class="btn btn-light border px-4 rounded-3">
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

            <form id="saleReturnForm" action="{{ route('saleReturn.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <!-- Left Column: Basic Info -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="form-section-title">Return Source</div>
                                
                                <div class="mb-3">
                                    <label for="pos_sale_id" class="form-label fw-bold small">POS Sale Reference <span class="text-danger">*</span></label>
                                    <select name="pos_sale_id" id="pos_sale_id" class="form-select" required>
                                        <option value="">Search by POS ID or Customer...</option>
                                    </select>
                                    <small class="text-muted mt-1 d-block" id="pos_sale_hint">
                                        <i class="fas fa-search me-1"></i> You can search by POS ID, Customer Name, or Phone.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_id" class="form-label fw-bold small">Customer <span class="text-secondary">(Auto-filled)</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select">
                                        <option value="">Select or search POS first</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                @if(isset($selectedPosSale) && $selectedPosSale && $selectedPosSale->customer_id == $customer->id) selected @endif>
                                                {{ $customer->name }}
                                            </option>
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
                        <div class="card border-0 shadow-sm h-100">
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
                                    <i class="fas fa-receipt fs-1 text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted">Select a POS sale to automatically load items.</p>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-0 p-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 shadow-sm fw-bold">
                                    <i class="fas fa-save me-2"></i>Process Return
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
        }

        $(document).ready(function() {
            // Initial POS Sale Select2 (Leading field)
            $('#pos_sale_id').select2({
                placeholder: 'Search by POS ID, Customer Name or Phone...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '/erp/pos/search',
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

            // Customer Select2 (Used as filter or auto-filled)
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

            $('#customer_id').on('change', function() {
                const customerId = $(this).val();
                if (customerId) {
                    $('#pos_sale_hint').html('<i class="fas fa-filter me-1 text-primary"></i> Filtering sales for selected customer.');
                } else {
                    $('#pos_sale_hint').html('<i class="fas fa-search me-1"></i> You can search by POS ID, Customer Name, or Phone.');
                }
                // We don't clear the POS Sale here to allow switching customers, 
                // but Select2 will naturally filter on next open
            });

            $('#pos_sale_id').on('change select2:select', function() {
                const posSaleId = $(this).val();
                if (posSaleId) {
                    loadPosSaleDetails(posSaleId);
                } else {
                    availableProducts = [];
                    $('#itemsTable tbody').empty();
                    toggleEmptyState();
                }
            });

            function loadPosSaleDetails(posSaleId) {
                const $tableBody = $('#itemsTable tbody');
                $tableBody.html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading items...</td></tr>');
                
                $.ajax({
                    url: `/erp/pos/${posSaleId}/details`,
                    method: 'GET',
                    success: function(response) {
                        $tableBody.empty();
                        if (response.success && response.data) {
                            const data = response.data;
                            
                            // Auto-set Customer if not already correctly selected
                            if (data.customer_id) {
                                const $customerSelect = $('#customer_id');
                                if ($customerSelect.val() != data.customer_id) {
                                    const option = new Option(data.customer_name || 'Customer #' + data.customer_id, data.customer_id, true, true);
                                    $customerSelect.empty().append(option).trigger('change');
                                }
                            }

                            if (data.branch_id) {
                                $('#return_to_type').val('branch').trigger('change');
                                setTimeout(() => $('#return_to_id').val(data.branch_id).trigger('change'), 200);
                            }

                            if (data.items && data.items.length > 0) {
                                availableProducts = data.items.map(item => ({
                                    id: parseInt(item.product_id),
                                    name: item.product_name || 'Product #' + item.product_id
                                }));
                                
                                data.items.forEach((item, index) => addItemRow(item, index));
                            }
                        }
                        toggleEmptyState();
                    },
                    error: function() {
                        $tableBody.html('<tr><td colspan="5" class="text-center text-danger py-4">Failed to load sale details.</td></tr>');
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
                            <div class="variation-wrapper mt-2" style="display:none;">
                                <select name="items[${i}][variation_id]" class="form-select variation-select small py-1">
                                    <option value="">Standard Variation</option>
                                </select>
                            </div>
                            <input type="hidden" name="items[${i}][sale_item_id]" value="${itemData ? itemData.id : ''}">
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
                
                if (itemData && itemData.product_id) {
                    loadVariationsForProduct($productSelect, itemData.product_id, itemData.variation_id);
                }

                $productSelect.on('change', function() {
                    const productId = $(this).val();
                    if (productId) loadVariationsForProduct($(this), productId, null);
                });

                toggleEmptyState();
            }

            $(document).on('click', '.btn-remove', function() {
                $(this).closest('tr').fadeOut(200, function() {
                    $(this).remove();
                    toggleEmptyState();
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
                    if (resp && resp.price) $input.val(parseFloat(resp.price).toFixed(2));
                });
            }

            $(document).on('change', '.variation-select', function() {
                const price = $(this).find('option:selected').data('price');
                if (price) $(this).closest('tr').find('.unit_price').val(parseFloat(price).toFixed(2));
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