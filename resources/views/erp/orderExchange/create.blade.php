@extends('erp.master')

@section('title', 'Process Order Exchange')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('orderExchange.list') }}" class="text-decoration-none">Order Exchanges</a></li>
                                    <li class="breadcrumb-item active">Create Exchange</li>
                                </ol>
                            </nav>
                            <h2 class="fw-bold mb-0">Create Order Exchange</h2>
                            <p class="text-muted">Record return items and select new items for exchange in one step.</p>
                        </div>
                        <a href="{{ route('orderExchange.list') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>

                    <form id="orderExchangeForm" action="{{ route('orderExchange.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Left Column: Source & Details -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <h5 class="fw-bold mb-0">Exchange Source</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Track Invoice Receipt <span class="text-danger">*</span></label>
                                            <select name="order_id" id="order_id" class="form-select select2-ajax" required>
                                                <option value="">Search by Invoice #, Order #, or Name...</option>
                                            </select>
                                            <div class="small text-muted mt-2"><i class="fas fa-receipt me-1 text-primary"></i>Enter the Invoice Number from the customer's receipt.</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Customer (Auto-filled)</label>
                                            <select name="customer_id" id="customer_id" class="form-select select2-ajax" required>
                                                <option value="">Search Customer...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <h5 class="fw-bold mb-0">Exchange Details</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Exchange Date <span class="text-danger">*</span></label>
                                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Restock Location <span class="text-danger">*</span></label>
                                            <div class="row g-2">
                                                <div class="col-5">
                                                    <select name="return_to_type" id="return_to_type" class="form-select" required>
                                                        <option value="branch" {{ (isset($generalSettings) && $generalSettings->ecommerce_source_type == 'branch') ? 'selected' : '' }}>Branch</option>
                                                        <option value="warehouse" {{ (isset($generalSettings) && $generalSettings->ecommerce_source_type == 'warehouse') ? 'selected' : '' }}>Warehouse</option>
                                                    </select>
                                                </div>
                                                <div class="col-7">
                                                    <select name="return_to_id" id="return_to_id" class="form-select" required>
                                                        <!-- Loaded via JS -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Reason / Notes</label>
                                            <textarea name="reason" class="form-control" rows="3" placeholder="Why is the customer exchanging?"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Items -->
                            <div class="col-lg-8">
                                <!-- Returned Items Section -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <h5 class="fw-bold mb-0 text-danger">Items being Returned</h5>
                                        <small class="text-muted">Select items that the customer is bringing back.</small>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0" id="returnItemsTable">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th class="ps-4 py-3" style="width: 10%;">Select</th>
                                                        <th class="py-3" style="width: 35%;">Product Specification</th>
                                                        <th class="py-3" style="width: 15%;">Qty</th>
                                                        <th class="py-3" style="width: 15%;">Credit (৳)</th>
                                                        <th class="pe-4 py-3 text-center" style="width: 15%;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Loaded via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="returnEmptyState" class="text-center py-5">
                                            <i class="fas fa-undo fs-1 text-muted opacity-25 mb-3"></i>
                                            <p class="text-muted">Select an order to load returnable items.</p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top p-3 text-end fw-bold">
                                        Total Return Credit: <span id="totalReturnAmount" class="text-danger">৳ 0.00</span>
                                    </div>
                                </div>

                                <!-- New Items Section -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="fw-bold mb-0 text-success">New Items to Take</h5>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" id="addNewItemRow">
                                                <i class="fas fa-plus me-1"></i>Add New Item
                                            </button>
                                        </div>
                                        <small class="text-muted">Add items the customer wants to receive in exchange.</small>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0" id="newItemsTable">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th class="ps-4 py-3" style="width: 45%;">Product Specification</th>
                                                        <th class="py-3" style="width: 15%;">Qty</th>
                                                        <th class="py-3" style="width: 20%;">Price (৳)</th>
                                                        <th class="pe-4 py-3"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="newItemsBody">
                                                    <!-- Added via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="newItemsEmptyState" class="text-center py-5">
                                            <i class="fas fa-cart-plus fs-1 text-muted opacity-25 mb-3"></i>
                                            <p class="text-muted">No new items added yet.</p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="bg-light p-3 rounded-3 border">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>New Order Total:</span>
                                                        <span id="newOrderTotal">৳ 0.00</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1 text-danger">
                                                        <span>Return Credit:</span>
                                                        <span id="returnCreditTotal">- ৳ 0.00</span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between fw-bold fs-5">
                                                        <span>Net Payable:</span>
                                                        <span id="netPayableAmount" class="text-primary">৳ 0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button type="submit" class="btn btn-primary px-5 py-3 rounded-3 shadow-sm fw-bold">
                                                    <i class="fas fa-check-circle me-2"></i>Complete Exchange
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let availableProducts = [];
            let itemIndex = 0;

            // Select2 Init
            $('#order_id').select2({
                placeholder: 'Search Invoice or Order...',
                width: '100%',
                ajax: {
                    url: '{{ route("order.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            $('#customer_id').select2({
                placeholder: 'Search Customer...',
                width: '100%',
                ajax: {
                    url: '/erp/customers/search',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({
                        results: data.map(i => ({ id: i.id, text: i.name + (i.phone ? ' (' + i.phone + ')' : '') }))
                    }),
                    cache: true
                }
            });

            // Handle Order Selection
            $('#order_id').on('change', function() {
                const orderId = $(this).val();
                if (orderId) loadOrderDetails(orderId);
            });

            // Flash Message Handling
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#3085d6'
                });
            @endif

            $('form').on('submit', function(e) {
                if ($('.is-invalid').length > 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Stock Error',
                        text: 'One or more items exceed available stock. Please adjust quantities.',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }

                // Show loading state
                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
            });

            function loadOrderDetails(orderId) {
                $.get(`/erp/order/${orderId}/details`, function(data) {
                    if (data && data.items) {
                        // Set Customer
                        if (data.customer_id) {
                            const opt = new Option(data.customer_name || 'Customer #' + data.customer_id, data.customer_id, true, true);
                            $('#customer_id').empty().append(opt).trigger('change');
                        }

                        // Auto-set Location based on first item fulfillment
                        const firstItem = data.items[0];
                        if (firstItem && firstItem.current_position_type) {
                            $('#return_to_type').val(firstItem.current_position_type).trigger('change');
                            setTimeout(() => {
                                $('#return_to_id').val(firstItem.current_position_id).trigger('change');
                            }, 150);
                        }

                        // Load Returnable Items
                        const $tbody = $('#returnItemsTable tbody').empty();
                        data.items.forEach(item => {
                            $tbody.append(`
                                <tr class="return-row">
                                    <td class="ps-4 text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input item-check" type="checkbox" style="transform: scale(1.4);">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">${item.product_name}</div>
                                        <small class="text-muted">${item.variation_name || 'Standard'}</small>
                                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${item.product_id}" disabled>
                                        <input type="hidden" name="items[${itemIndex}][variation_id]" value="${item.variation_id || ''}" disabled>
                                        <input type="hidden" name="items[${itemIndex}][order_item_id]" value="${item.id}" disabled>
                                    </td>
                                    <td>
                                        <input type="number" name="items[${itemIndex}][returned_qty]" class="form-control form-control-sm return-qty" 
                                            value="0" min="0" max="${item.quantity}" step="any" data-max="${item.quantity}" disabled>
                                        <small class="text-muted">Bought: ${item.quantity}</small>
                                    </td>
                                    <td>
                                        <input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm return-price" 
                                            value="${item.unit_price}" readonly disabled>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="btn btn-sm btn-soft-success exchange-item-btn" 
                                            data-product-id="${item.product_id}" 
                                            data-product-name="${item.product_name}"
                                            data-qty="${item.quantity}"
                                            title="Exchange for another size/product" disabled>
                                            <i class="fas fa-sync me-1"></i>Swap
                                        </button>
                                    </td>
                                </tr>
                            `);
                            itemIndex++;
                        });
                        $('#returnEmptyState').hide();
                        calculateTotals();
                    }
                });
            }

            // Calculations
            $(document).on('input', '.return-qty, .new-qty, .new-price', calculateTotals);
            $(document).on('change', '.item-check', function() {
                const $row = $(this).closest('tr');
                const $inputs = $row.find('input:not(.item-check), button');
                const $qtyInput = $row.find('.return-qty');
                
                if ($(this).is(':checked')) {
                    $row.addClass('table-primary');
                    $inputs.prop('disabled', false);
                    if ($qtyInput.val() == 0) $qtyInput.val($qtyInput.data('max'));
                } else {
                    $row.removeClass('table-primary');
                    $inputs.prop('disabled', true);
                    $qtyInput.val(0);
                }
                calculateTotals();
            });

            function calculateTotals() {
                let returnTotal = 0;
                $('.return-qty').each(function() {
                    const qty = parseFloat($(this).val()) || 0;
                    const price = parseFloat($(this).closest('tr').find('.return-price').val()) || 0;
                    returnTotal += qty * price;
                });

                let newTotal = 0;
                $('.new-qty').each(function() {
                    const qty = parseFloat($(this).val()) || 0;
                    const price = parseFloat($(this).closest('tr').find('.new-price').val()) || 0;
                    newTotal += qty * price;
                });

                $('#totalReturnAmount').text('৳ ' + returnTotal.toFixed(2));
                $('#newOrderTotal').text('৳ ' + newTotal.toFixed(2));
                $('#returnCreditTotal').text('- ৳ ' + returnTotal.toFixed(2));
                
                const net = newTotal - returnTotal;
                $('#netPayableAmount').text('৳ ' + (net > 0 ? net.toFixed(2) : '0.00 (Credit)'));
            }

            // Add New Item Row
            let newIndex = 0;
            $('#addNewItemRow').on('click', function() {
                const row = `
                    <tr class="new-item-row">
                        <td class="ps-4">
                            <select name="new_items[${newIndex}][product_id]" class="form-select select2-new-product" required></select>
                            <div class="variation-wrapper mt-2" style="display:none;">
                                <select name="new_items[${newIndex}][variation_id]" class="form-select form-select-sm variation-select"></select>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="new_items[${newIndex}][qty]" class="form-control form-control-sm new-qty" value="1" min="0.01" step="any" required>
                        </td>
                        <td>
                            <input type="number" name="new_items[${newIndex}][unit_price]" class="form-control form-control-sm new-price" placeholder="Price" step="any" required>
                        </td>
                        <td class="pe-4">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `;
                $('#newItemsBody').append(row);
                $('#newItemsEmptyState').hide();

                const $newSelect = $(`.new-item-row:last .select2-new-product`);
                initProductSelect($newSelect);
                newIndex++;
            });

            function initProductSelect($select) {
                $select.select2({
                    placeholder: 'Search product...',
                    width: '100%',
                    ajax: {
                        url: '/erp/products/search',
                        dataType: 'json',
                        data: params => ({ q: params.term }),
                        processResults: data => ({ results: data.map(i => ({ id: i.id, text: i.name })) })
                    }
                }).on('change', function() {
                    const productId = $(this).val();
                    const $row = $(this).closest('tr');
                    const $varWrapper = $row.find('.variation-wrapper');
                    const $varSelect = $row.find('.variation-select');
                    const $priceInput = $row.find('.new-price');

                    const locationType = $('#return_to_type').val();
                    const locationId = $('#return_to_id').val();
                    
                    $.get(`/erp/products/${productId}/variations-with-stock?location_type=${locationType}&location_id=${locationId}`, resp => {
                        $varSelect.empty();
                        if (resp && resp.length > 0) {
                            // Check if it's a simple product (one variation named Standard with null ID)
                            const isSimple = resp.length === 1 && resp[0].id === null;
                            
                            if (isSimple) {
                                $varWrapper.hide();
                                $varSelect.append(`<option value="" data-price="${resp[0].price}" data-stock="${resp[0].stock}">Standard</option>`);
                            } else {
                                $varWrapper.show();
                                resp.forEach(v => {
                                    const stockInfo = v.stock !== undefined ? ` (Stock: ${v.stock})` : '';
                                    $varSelect.append(`<option value="${v.id}" data-price="${v.price}" data-stock="${v.stock}">${v.name} - ৳${v.price}${stockInfo}</option>`);
                                });
                            }
                            
                            // Initialize units with first option
                            const first = resp[0];
                            $priceInput.val(parseFloat(first.price).toFixed(2));
                            $row.find('.new-qty').attr('max', first.stock);
                        } else {
                            $varWrapper.hide();
                            $priceInput.val('0.00');
                            $row.find('.new-qty').attr('max', 0);
                        }
                        calculateTotals();
                    }).fail(function() {
                        console.error("Failed to load variations");
                        $priceInput.val('0.00');
                    });
                });

                $select.closest('tr').find('.variation-select').on('change', function() {
                    const $opt = $(this).find('option:selected');
                    const price = $opt.data('price');
                    const stock = $opt.data('stock');
                    const $row = $(this).closest('tr');
                    $row.find('.new-price').val(price);
                    $row.find('.new-qty').attr('max', stock);
                    calculateTotals();
                });

                $select.closest('tr').find('.new-qty').on('input', function() {
                    const max = parseFloat($(this).attr('max')) || 0;
                    const val = parseFloat($(this).val()) || 0;
                    if (val > max) {
                        $(this).addClass('is-invalid');
                        if (!$(this).next('.stock-warning').length) {
                            $(this).after(`<small class="text-danger stock-warning d-block">Max stock: ${max}</small>`);
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.stock-warning').remove();
                    }
                    calculateTotals();
                });
            }

            // Exchange Button Logic (POS Style)
            $(document).on('click', '.exchange-item-btn', function() {
                const $btn = $(this);
                const $row = $btn.closest('tr');
                const productId = $btn.data('product-id');
                const productName = $btn.data('product-name');
                const qty = $btn.data('qty');

                // 1. Fill return quantity and check the box
                $row.find('.return-qty').val(qty);
                $row.find('.item-check').prop('checked', true);
                
                // 2. Add New Item row
                $('#addNewItemRow').click();
                
                // 3. Select the same product in the new row
                const $newRow = $('#newItemsBody tr:last');
                const $select = $newRow.find('.select2-new-product');
                
                const newOption = new Option(productName, productId, true, true);
                $select.append(newOption).trigger('change');
                
                // Scroll to new items section
                $('html, body').animate({
                    scrollTop: $("#newItemsTable").offset().top - 100
                }, 500);
                
                calculateTotals();
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                if ($('#newItemsBody tr').length === 0) $('#newItemsEmptyState').show();
                calculateTotals();
            });

            // Location Logic
            $('#return_to_type').on('change', function() {
                const type = $(this).val();
                const $target = $('#return_to_id').empty();
                const branchData = @json($branches);
                const warehouseData = @json($warehouses);
                const data = type === 'branch' ? branchData : warehouseData;
                data.forEach(item => $target.append(`<option value="${item.id}">${item.name}</option>`));
            }).trigger('change');

            // Pre-load if order_id in URL
            @if(isset($preSelectedOrder))
                const preOpt = new Option("{{ $preSelectedOrder->order_number }}", "{{ $preSelectedOrder->id }}", true, true);
                $('#order_id').append(preOpt).trigger('change');
            @endif
        });
    </script>
    <style>
        .btn-soft-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        .btn-soft-success:hover {
            background-color: #198754;
            color: white;
        }
    </style>
@endpush
