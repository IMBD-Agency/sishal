@extends('erp.master')

@section('title', 'Manual Sale Creation')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-plus-circle me-2"></i>Manual Sale Entry
                    </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('pos.list') }}">POS</a></li>
                            <li class="breadcrumb-item active">Manual Sale</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('pos.list') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>

            <form id="manualSaleForm">
                @csrf
                <div class="row g-4">
                    <!-- Section 1: Sale Information -->
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold text-dark">
                                    <i class="fas fa-file-invoice me-2 text-primary"></i>Sale Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Invoice No <span class="text-danger">*</span></label>
                                        <input type="text" name="invoice_no" class="form-control" value="{{ $invoiceNo }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Challan No <span class="text-danger">*</span></label>
                                        <input type="text" name="challan_no" class="form-control" value="{{ $challanNo }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                                        <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="customerSelect" class="form-select" required>
                                            <option value="">Search Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Item Selection Area -->
                                    <div class="col-12 mt-4 pt-3 border-top">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-primary">Select Style Number (SKU)</label>
                                                <select id="styleNumberSelect" class="form-select">
                                                    <option value="">Search by Style Number...</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Variation / Size-Color</label>
                                                <select id="variationSelect" class="form-select" disabled>
                                                    <option value="">Select Variation</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Price</label>
                                                <input type="number" id="itemPrice" class="form-control" placeholder="0.00" step="0.01">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Quantity</label>
                                                <input type="number" id="itemQty" class="form-control" value="1" min="0.01" step="0.01">
                                            </div>
                                            <div class="col-12 text-end">
                                                <button type="button" id="addItemBtn" class="btn btn-primary px-4">
                                                    <i class="fas fa-cart-plus me-2"></i>Add to List
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Table -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold text-dark">
                                    <i class="fas fa-shopping-cart me-2 text-primary"></i>Selected Items
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="cartTable">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="px-4">SL</th>
                                                <th>Style No</th>
                                                <th>Internal Ref</th>
                                                <th>Variation</th>
                                                <th>Rate</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="emptyRow">
                                                <td colspan="8" class="text-center py-5 text-muted">No items added yet.</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light fw-bold">
                                            <tr>
                                                <td colspan="6" class="text-end px-4 py-3">Sub Total</td>
                                                <td class="text-end py-3" id="subtotalLabel">0.00৳</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Summary & Status -->
                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold text-dark">
                                    <i class="fas fa-cog me-2 text-primary"></i>Sale Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Select Branch <span class="text-danger">*</span></label>
                                        <select name="branch_id" class="form-select" required>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Sale Type <span class="text-danger">*</span></label>
                                        <select name="sale_type" class="form-select" required>
                                            <option value="MRP">MRP</option>
                                            <option value="Wholesale">Wholesale</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Sale Number</label>
                                        <input type="text" name="sale_no" class="form-control bg-light" value="{{ $saleNo }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Account Type</label>
                                        <select name="account_type" class="form-select" id="accountType">
                                            <option value="Cash">Cash</option>
                                            <option value="Bank">Bank</option>
                                            <option value="bKash">bKash</option>
                                            <option value="Nagad">Nagad</option>
                                            <option value="Rocket">Rocket</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Account No</label>
                                        <input type="text" name="account_no" class="form-control" placeholder="Account numbers/details">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Select Courier</label>
                                        <select name="courier_id" class="form-select">
                                            <option value="">Select Courier</option>
                                            @foreach($shippingMethods as $method)
                                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Internal remarks..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Note for Customer</label>
                                        <textarea name="note" class="form-control" rows="2" placeholder="Will appear on invoice..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Calculator -->
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="card-header bg-dark text-white py-3 border-bottom">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-calculator me-2"></i>Summary
                                </h5>
                            </div>
                            <div class="card-body bg-light">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Sub Total</span>
                                    <span class="fw-bold" id="subtotalDisplay">0.00৳</span>
                                </div>
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-6">
                                        <span class="text-muted">Discount</span>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="discount" id="discountInput" class="form-control form-control-sm text-end" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-6">
                                        <span class="text-muted">Delivery Charge</span>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="delivery_charge" id="deliveryInput" class="form-control form-control-sm text-end" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="border-top pt-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <span class="fw-bold h5">Total Amount</span>
                                        <span class="fw-bold h4 text-primary" id="totalDisplay">0.00৳</span>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-6">
                                        <span class="fw-semibold">Paid Amount</span>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="paid_amount" id="paidInput" class="form-control form-control-sm text-end fw-bold text-success border-success" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-warning fw-bold border-top pt-3">
                                    <span>Due Amount</span>
                                    <span id="dueDisplay">0.00৳</span>
                                </div>
                            </div>
                            <div class="card-footer p-3 bg-white border-top">
                                <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm" id="submitBtn">
                                    <i class="fas fa-check-double me-2"></i>Complete Sale
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Fields for logic -->
    <input type="hidden" id="subtotalInput" name="sub_total" value="0">
    <input type="hidden" id="totalAmountInput" name="total_amount" value="0">

    <style>
        .form-control:focus, .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.1);
        }
        .bg-light { background-color: #f8f9fa !important; }
        .text-primary { color: #198754 !important; }
        .btn-primary { background-color: #198754; border-color: #198754; }
        .btn-primary:hover { background-color: #00512C; border-color: #00512C; }
        .card { border-radius: 12px; }
        .text-success { color: #198754 !important; }
        .border-success { border-color: #198754 !important; }
        .btn-success { background-color: #198754; border-color: #198754; }
        .btn-success:hover { background-color: #00512C; border-color: #00512C; }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Select2 initialization
            $('#customerSelect').select2({ theme: 'classic', width: '100%', placeholder: 'Select Customer' });
            
            $('#styleNumberSelect').select2({
                theme: 'classic',
                width: '100%',
                placeholder: 'Search by Style Number...',
                ajax: {
                    url: "{{ route('products.search.style') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // Auto-focus search field when Select2 is opened
            $(document).on('select2:open', function(e) {
                window.setTimeout(function () {
                    document.querySelector('.select2-search__field').focus();
                }, 0);
            });

            let cartItems = [];
            let selectedProduct = null;

            // When Style Number is selected
            $('#styleNumberSelect').on('select2:select', function(e) {
                const styleNumber = e.params.data.id;
                $.get(`/erp/products/find-by-style/${styleNumber}`, function(response) {
                    if (response.success && response.products.length > 0) {
                        selectedProduct = response.products[0];
                        renderVariations(selectedProduct);
                        updateItemPrice();
                    }
                });
            });

            function renderVariations(product) {
                const $select = $('#variationSelect');
                $select.empty().append('<option value="">Select Variation</option>');
                
                if (product.has_variations) {
                    $select.prop('disabled', false);
                    product.variations.forEach(v => {
                        $select.append(`<option value="${v.id}" data-price="${v.price}" data-wholesale="${v.wholesale_price}" data-sku="${v.sku}">${v.name}</option>`);
                    });
                } else {
                    $select.prop('disabled', true);
                    $select.append(`<option value="" selected>No Variation</option>`);
                }
            }

            function updateItemPrice() {
                if (!selectedProduct) return;
                
                const saleType = $('select[name="sale_type"]').val();
                const $variation = $('#variationSelect option:selected');
                const variationId = $('#variationSelect').val();
                const hasVariation = variationId !== "" && variationId !== null;
                
                let price = 0;
                if (hasVariation) {
                    price = (saleType === 'Wholesale') ? $variation.data('wholesale') : $variation.data('price');
                } else {
                    price = (saleType === 'Wholesale') ? selectedProduct.wholesale_price : selectedProduct.price;
                }
                
                $('#itemPrice').val(parseFloat(price || 0).toFixed(2));
            }

            $('#variationSelect').on('change', function() {
                updateItemPrice();
            });

            $('select[name="sale_type"]').on('change', function() {
                updateItemPrice();
                updateCartPrices();
            });

            function updateCartPrices() {
                const saleType = $('select[name="sale_type"]').val();
                cartItems = cartItems.map(item => {
                    const newPrice = (saleType === 'Wholesale') ? parseFloat(item.wholesale_price) : parseFloat(item.mrp_price);
                    item.unit_price = newPrice;
                    item.total = newPrice * item.quantity;
                    return item;
                });
                updateCartTable();
            }

            // Add Item to Cart
            $('#addItemBtn').on('click', function() {
                if (!selectedProduct) {
                    alert('Please select a product first.');
                    return;
                }

                const varId = $('#variationSelect').val();
                const $varOption = $('#variationSelect option:selected');
                const varName = $varOption.text();
                const currentPrice = parseFloat($('#itemPrice').val());
                const qty = parseFloat($('#itemQty').val());

                if (isNaN(currentPrice) || currentPrice < 0 || isNaN(qty) || qty <= 0) {
                    alert('Please enter valid price and quantity.');
                    return;
                }

                if (selectedProduct.has_variations && !varId) {
                    alert('Please select a variation.');
                    return;
                }

                // Capture both prices for future switching
                const mrpPrice = selectedProduct.has_variations ? $varOption.data('price') : selectedProduct.price;
                const wholesalePrice = selectedProduct.has_variations ? $varOption.data('wholesale') : selectedProduct.wholesale_price;

                const item = {
                    product_id: selectedProduct.id,
                    style_no: selectedProduct.has_variations ? $varOption.data('sku') : selectedProduct.sku,
                    internal_ref: selectedProduct.style_number || '-',
                    variation_id: varId || null,
                    variation_name: selectedProduct.has_variations ? varName : 'Standard',
                    unit_price: currentPrice,
                    mrp_price: mrpPrice,
                    wholesale_price: wholesalePrice,
                    quantity: qty,
                    total: currentPrice * qty
                };

                cartItems.push(item);
                updateCartTable();
                resetItemFields();
            });

            function updateCartTable() {
                const $tbody = $('#cartTable tbody');
                $tbody.empty();

                if (cartItems.length === 0) {
                    $tbody.append('<tr id="emptyRow"><td colspan="8" class="text-center py-5 text-muted">No items added yet.</td></tr>');
                    updateCalculations();
                    return;
                }

                let subtotal = 0;
                cartItems.forEach((item, index) => {
                    subtotal += item.total;
                    $tbody.append(`
                        <tr>
                            <td class="px-4 font-monospace small">${index + 1}</td>
                            <td class="fw-semibold">${item.style_no}</td>
                            <td class="text-muted small">${item.internal_ref}</td>
                            <td><span class="badge bg-light text-dark border">${item.variation_name}</span></td>
                            <td class="text-primary fw-bold">${parseFloat(item.unit_price).toFixed(2)}৳</td>
                            <td class="text-center">${item.quantity}</td>
                            <td class="text-end fw-bold">${parseFloat(item.total).toFixed(2)}৳</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-link text-danger p-0 delete-item" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });

                updateCalculations(subtotal);
            }

            $(document).on('click', '.delete-item', function() {
                const index = $(this).data('index');
                cartItems.splice(index, 1);
                updateCartTable();
            });

            function updateCalculations(subtotal = null) {
                if (subtotal === null) {
                    subtotal = cartItems.reduce((sum, item) => sum + item.total, 0);
                }

                const discount = parseFloat($('#discountInput').val()) || 0;
                const delivery = parseFloat($('#deliveryInput').val()) || 0;
                const total = subtotal - discount + delivery;
                const paid = parseFloat($('#paidInput').val()) || 0;
                const due = total - paid;

                $('#subtotalLabel, #subtotalDisplay').text(subtotal.toFixed(2) + '৳');
                $('#totalDisplay').text(total.toFixed(2) + '৳');
                $('#dueDisplay').text(due.toFixed(2) + '৳');
                
                $('#subtotalInput').val(subtotal.toFixed(2));
                $('#totalAmountInput').val(total.toFixed(2));
            }

            $('#discountInput, #deliveryInput, #paidInput').on('input', function() {
                updateCalculations();
            });

            function resetItemFields() {
                $('#styleNumberSelect').val(null).trigger('change');
                $('#variationSelect').empty().append('<option value="">Select Variation</option>').prop('disabled', true);
                $('#itemPrice').val('');
                $('#itemQty').val(1);
                selectedProduct = null;
            }

            // Form Submit
            $('#manualSaleForm').on('submit', function(e) {
                e.preventDefault();

                if (cartItems.length === 0) {
                    alert('Please add at least one item to the cart.');
                    return;
                }

                const formData = new FormData(this);
                cartItems.forEach((item, index) => {
                    formData.append(`items[${index}][product_id]`, item.product_id);
                    formData.append(`items[${index}][variation_id]`, item.variation_id || '');
                    formData.append(`items[${index}][quantity]`, item.quantity);
                    formData.append(`items[${index}][unit_price]`, item.unit_price);
                });

                formData.append('sub_total', $('#subtotalInput').val());
                formData.append('total_amount', $('#totalAmountInput').val());

                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');

                $.ajax({
                    url: "{{ route('pos.manual.store') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            alert('Error: ' + response.message);
                            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-check-double me-2"></i>Complete Sale');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong.'));
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-check-double me-2"></i>Complete Sale');
                    }
                });
            });
        });
    </script>
@endsection
