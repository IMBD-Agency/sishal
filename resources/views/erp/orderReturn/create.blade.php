@extends('erp.master')

@section('title', 'Process Order Return')

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
                                    <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('orderReturn.list') }}" class="text-decoration-none">Order Returns</a></li>
                                    <li class="breadcrumb-item active">Create Return</li>
                                </ol>
                            </nav>
                            <h2 class="fw-bold mb-0">Create Order Return</h2>
                            <p class="text-muted">Select an order to load items and process customer refund.</p>
                        </div>
                        <a href="{{ route('orderReturn.list') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>

                    <form action="{{ route('orderReturn.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Left Column: Source -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <h5 class="fw-bold mb-0">Return Source</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Order Reference <span class="text-danger">*</span></label>
                                            <select name="order_id" id="order_id" class="form-select @error('order_id') is-invalid @enderror" required>
                                                <option value="">Search by Order #, Customer Name...</option>
                                            </select>
                                            @error('order_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Customer (Auto-filled)</label>
                                            <select name="customer_id" id="customer_id" class="form-select" required>
                                                <option value="">Search Customer...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <h5 class="fw-bold mb-0">Return Settings</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Return Date <span class="text-danger">*</span></label>
                                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Restock To <span class="text-danger">*</span></label>
                                            <div class="row g-2">
                                                <div class="col-5">
                                                    <select name="return_to_type" id="return_to_type" class="form-select" required>
                                                        <option value="branch">Branch</option>
                                                        <option value="warehouse">Warehouse</option>
                                                    </select>
                                                </div>
                                                <div class="col-7">
                                                    <select name="return_to_id" id="return_to_id" class="form-select" required>
                                                        <!-- Loaded via JS -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Refund Method <span class="text-danger">*</span></label>
                                            <select name="refund_type" id="refund_method" class="form-select" required>
                                                <option value="cash">Cash</option>
                                                <option value="bank">Bank Transfer</option>
                                                <option value="credit">Store Credit</option>
                                            </select>
                                        </div>

                                        <div id="refund_account_wrapper" style="display:none;">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Refund From Account <span class="text-danger">*</span></label>
                                                <select name="account_id" id="account_id" class="form-select">
                                                    <option value="">Select Account</option>
                                                    @foreach($bankAccounts as $acc)
                                                        <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                                            {{ $acc->provider_name ?? ucfirst($acc->type) }} 
                                                            {{ $acc->account_number ? '('.$acc->account_number.')' : '' }} 
                                                            - {{ number_format($acc->balance, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Items -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">Returnable Items</h5>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="addManualItem">
                                            <i class="fas fa-plus me-1"></i>Add Manual Item
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0 d-none" id="itemsTable">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th class="ps-4 py-3" style="width: 35%;">Product</th>
                                                        <th class="py-3" style="width: 15%;">Return Qty</th>
                                                        <th class="py-3" style="width: 15%;">Unit Price</th>
                                                        <th class="py-3" style="width: 15%;">Reason</th>
                                                        <th class="py-3 text-end" style="width: 15%;">Total</th>
                                                        <th class="pe-4 py-3 text-center" style="width: 5%;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Loaded via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="emptyState" class="text-center py-5">
                                            <i class="fas fa-shopping-basket fs-1 text-muted opacity-25 mb-3"></i>
                                            <p class="text-muted">Select an order to load returnable items.</p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="bg-light p-3 rounded-3">
                                                    <span class="text-muted small text-uppercase d-block mb-1">Total Refund Amount</span>
                                                    <h3 class="fw-bold mb-0 text-primary" id="refund_amount_display">৳ 0.00</h3>
                                                    <input type="hidden" name="refund_amount" id="refund_amount_input" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button type="submit" class="btn btn-primary px-5 py-3 rounded-3 shadow-sm fw-bold">
                                                    <i class="fas fa-check-circle me-2"></i>Complete Return
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
    <script>
        $(document).ready(function() {
            // Select2 Init
            $('#order_id').select2({
                placeholder: 'Search by Order #, Customer Name...',
                width: '100%',
                ajax: {
                    url: '{{ route("order.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term }),
                    processResults: data => ({ results: data }),
                    cache: true
                }
            });

            $('#customer_id').select2({
                placeholder: 'Search Customer...',
                width: '100%'
            });

            // Handle Order Selection
            $('#order_id').on('change', function() {
                const orderId = $(this).val();
                if (orderId) loadOrderDetails(orderId);
            });

            // Manual Item Logic
            let manualItemIndex = 999; // High index to avoid collision with order items
            $('#addManualItem').on('click', function() {
                $('#emptyState').hide();
                $('#itemsTable').removeClass('d-none');
                
                const index = manualItemIndex++;
                const row = $(`
                    <tr class="product-row manual-row">
                        <td class="ps-4">
                            <select name="items[${index}][product_id]" class="form-select manual-product-select" required>
                                <option value="">Search Product...</option>
                            </select>
                            <div class="variation-wrapper mt-2" style="display:none;">
                                <select name="items[${index}][variation_id]" class="form-select form-select-sm manual-variation-select">
                                    <option value="">Select Variation</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="items[${index}][returned_qty]" class="form-control form-control-sm return-qty" 
                                value="1" min="0.01" step="any">
                        </td>
                        <td>
                            <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm unit_price" 
                                value="0" step="0.01">
                        </td>
                        <td>
                            <input type="text" name="items[${index}][reason]" class="form-control form-control-sm" placeholder="Reason">
                        </td>
                        <td class="pe-4 text-end fw-bold row-total">৳ 0.00</td>
                        <td class="pe-2">
                             <button type="button" class="btn btn-link text-danger p-0 btn-remove shadow-none">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                `);

                $('#itemsTable tbody').append(row);

                // Init Select2 for the new product search
                row.find('.manual-product-select').select2({
                    placeholder: 'Search Product...',
                    width: '100%',
                    ajax: {
                        url: '/erp/products/search', // Adjust based on your actual route
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
                }).on('change', function() {
                    const productId = $(this).val();
                    const $varWrapper = row.find('.variation-wrapper');
                    const $varSelect = row.find('.manual-variation-select');
                    const $priceInput = row.find('.unit_price');

                    if (productId) {
                        $.get(`/erp/products/${productId}/variations-list`, function(variations) {
                            if (variations && variations.length > 0) {
                                $varSelect.empty().append('<option value="">Select Variation</option>');
                                variations.forEach(v => {
                                    $varSelect.append(`<option value="${v.id}" data-price="${v.price}">${v.display_name || v.name}</option>`);
                                });
                                $varWrapper.show();
                            } else {
                                $varWrapper.hide();
                                // Load product price if no variations
                                $.get(`/erp/products/${productId}/sale-price`, resp => {
                                    if(resp && resp.price) $priceInput.val(resp.price);
                                    calculateTotals();
                                });
                            }
                        });
                    }
                });

                calculateTotals();
            });

            $(document).on('change', '.manual-variation-select', function() {
                const price = $(this).find('option:selected').data('price');
                if (price) {
                    $(this).closest('tr').find('.unit_price').val(price);
                    calculateTotals();
                }
            });

            function loadOrderDetails(orderId) {
                const $tableBody = $('#itemsTable tbody');
                $tableBody.html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading items...</td></tr>');
                
                $.get(`/erp/order/${orderId}/details`, function(data) {
                    $tableBody.empty();
                    
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

                        // Load items into table
                        data.items.forEach((item, index) => {
                            $tableBody.append(`
                                <tr class="product-row">
                                    <td class="ps-4">
                                        <div class="fw-bold">${item.product_name}</div>
                                        <small class="text-muted">${item.variation_name || 'Standard'}</small>
                                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                        <input type="hidden" name="items[${index}][variation_id]" value="${item.variation_id || ''}">
                                        <input type="hidden" name="items[${index}][order_item_id]" value="${item.id}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[${index}][returned_qty]" class="form-control form-control-sm return-qty" 
                                            value="${item.quantity}" min="0" max="${item.quantity}" step="any">
                                        <small class="text-muted">Max: ${item.quantity}</small>
                                    </td>
                                    <td>
                                        <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm unit_price" 
                                            value="${item.unit_price}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="text" name="items[${index}][reason]" class="form-control form-control-sm" placeholder="Reason">
                                    </td>
                                    <td class="pe-4 text-end fw-bold row-total">৳ 0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link text-danger p-0 btn-remove shadow-none">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                        
                        if (data.items.length === 0) {
                            $tableBody.html('<tr><td colspan="5" class="text-center py-4">No items found.</td></tr>');
                        } else {
                            $('#emptyState').hide();
                            $('#itemsTable').removeClass('d-none');
                        }
                        
                        calculateTotals();
                    }
                }).fail(function() {
                    $tableBody.html('<tr><td colspan="5" class="text-center py-4 text-danger">Error fetching order details.</td></tr>');
                });
            }

            // Calculations
            $(document).on('input', '.return-qty, .unit_price', calculateTotals);

            function calculateTotals() {
                let subtotal = 0;
                $('.product-row').each(function() {
                    const qty = parseFloat($(this).find('.return-qty').val()) || 0;
                    const price = parseFloat($(this).find('.unit_price').val()) || 0;
                    const rowTotal = qty * price;
                    $(this).find('.row-total').text('৳ ' + rowTotal.toFixed(2));
                    subtotal += rowTotal;
                });

                $('#refund_amount_display').text('৳ ' + subtotal.toFixed(2));
                $('#refund_amount_input').val(subtotal.toFixed(2));
            }

            // Remove Row - Enhanced Delegation
            $(document).on('click', '.btn-remove', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
                calculateTotals();
                if ($('#itemsTable tbody tr').length === 0) {
                    $('#emptyState').show();
                    $('#itemsTable').addClass('d-none');
                }
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

            // Refund Method Logic with Filtering
            $('#refund_method').on('change', function() {
                const method = $(this).val();
                const $accountSelect = $('#account_id');
                const $accountOptions = $accountSelect.find('option:not([value=""])');
                
                if (['cash', 'bank'].includes(method)) {
                    $('#refund_account_wrapper').slideDown();
                    $accountSelect.prop('required', true);
                    
                    // Filter options
                    $accountOptions.hide().each(function() {
                        if ($(this).data('type') === method) {
                            $(this).show();
                        }
                    });
                    
                    // Auto-select first visible or reset if none visible
                    if ($accountOptions.filter(':visible').length === 1) {
                        $accountSelect.val($accountOptions.filter(':visible').val()).trigger('change');
                    } else {
                        $accountSelect.val('').trigger('change');
                    }
                } else {
                    $('#refund_account_wrapper').slideUp();
                    $accountSelect.prop('required', false).val('').trigger('change');
                }
            }).trigger('change');

            $('form').on('submit', function() {
                $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
            });
        });
    </script>
@endpush
