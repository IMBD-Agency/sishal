@extends('erp.master')

@section('title', 'Create Invoice')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-3">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="row align-items-center mb-4">
                        <div class="col">
                            <h3 class="fw-bold mb-0">Create New Invoice</h3>
                            <p class="text-muted mb-0">Generate a professional invoice for your customers.</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('invoice.list') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-4">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('invoice.store') }}" class="row">
                            @csrf

                            <!-- Customer & Address Section -->
                            <div class="col-md-7">
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                        <h5 class="fw-bold mb-0"><i class="fas fa-user-circle text-primary me-2"></i>Customer & Address</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label small text-muted fw-bold">Select Customer <span class="text-danger">*</span></label>
                                                <select name="customer_id" id="customerSelect" class="form-select shadow-sm" required style="width:100%">
                                                    <option value="">Search and select customer...</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6 pe-lg-4" style="border-right: 1px dashed #dee2e6;">
                                                <label class="form-label small text-muted fw-bold mb-3 d-block"><i class="fas fa-file-invoice me-2"></i>Billing Address</label>
                                                <div class="mb-3">
                                                    <input type="text" name="billing_address_1" class="form-control bg-light border-0 shadow-sm" placeholder="Address Line 1 *" required>
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" name="billing_address_2" class="form-control bg-light border-0 shadow-sm" placeholder="Address Line 2">
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="billing_city" class="form-control bg-light border-0 shadow-sm" placeholder="City">
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="billing_state" class="form-control bg-light border-0 shadow-sm" placeholder="State">
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="billing_country" class="form-control bg-light border-0 shadow-sm" placeholder="Country">
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="billing_zip_code" class="form-control bg-light border-0 shadow-sm" placeholder="Zip Code">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ps-lg-4">
                                                <label class="form-label small text-muted fw-bold mb-3 d-block"><i class="fas fa-truck me-2"></i>Shipping Address</label>
                                                <div class="mb-3">
                                                    <input type="text" name="shipping_address_1" class="form-control bg-light border-0 shadow-sm" placeholder="Address Line 1">
                                                </div>
                                                <div class="mb-3">
                                                    <input type="text" name="shipping_address_2" class="form-control bg-light border-0 shadow-sm" placeholder="Address Line 2">
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="shipping_city" class="form-control bg-light border-0 shadow-sm" placeholder="City">
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="shipping_state" class="form-control bg-light border-0 shadow-sm" placeholder="State">
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="shipping_country" class="form-control bg-light border-0 shadow-sm" placeholder="Country">
                                                    </div>
                                                    <div class="col-6 mb-2">
                                                        <input type="text" name="shipping_zip_code" class="form-control bg-light border-0 shadow-sm" placeholder="Zip Code">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Settings Row -->
                            <div class="col-md-5">
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                        <h5 class="fw-bold mb-0"><i class="fas fa-cog text-primary me-2"></i>Invoice Details</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label small text-muted fw-bold">Select Template <span class="text-danger">*</span></label>
                                                <select name="template_id" id="templateSelect" class="form-select bg-light border-0 shadow-sm" required>
                                                    <option value="">Select Template</option>
                                                    @foreach($templates as $template)
                                                        <option value="{{ $template->id }}"
                                                            data-footer="{!! addslashes($template->footer_note) !!}"
                                                            {{ strtolower($template->name) == 'primary' ? 'selected' : '' }}>
                                                            {{ $template->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted fw-bold">Issue Date <span class="text-danger">*</span></label>
                                                <input type="date" name="issue_date" class="form-control bg-light border-0 shadow-sm" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted fw-bold">Due Date</label>
                                                <input type="date" name="due_date" class="form-control bg-light border-0 shadow-sm">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label small text-muted fw-bold">Internal Notes</label>
                                                <textarea name="note" class="form-control bg-light border-0 shadow-sm" rows="3" placeholder="Add private notes for internal reference..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                        <h5 class="fw-bold mb-0"><i class="fas fa-list text-primary me-2"></i>Invoice Items</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="table-responsive mb-3">
                                            <table class="table table-bordered align-middle" id="itemsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="25%">Product</th>
                                                        <th width="15%">Variation</th>
                                                        <th width="10%">Quantity</th>
                                                        <th width="12%">Unit Price</th>
                                                        <th width="10%">Discount</th>
                                                        <th width="13%">Total Price</th>
                                                        <th width="15%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <select name="items[0][product_id]"
                                                                class="form-select product-select" required
                                                                style="width:100%">
                                                                <option value="">Search and select product...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="items[0][variation_id]"
                                                                class="form-select variation-select"
                                                                style="width:100%" disabled>
                                                                <option value="">No Variation</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[0][quantity]"
                                                                class="form-control item-qty" min="1"
                                                                required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[0][unit_price]"
                                                                class="form-control item-unit" min="0" step="0.01" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[0][discount]"
                                                                class="form-control item-discount" min="0" step="0.01" value="0">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[0][total_price]"
                                                                class="form-control item-total" min="0" step="0.01" readonly
                                                                required>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm remove-item"
                                                                disabled>
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                                                <i class="fas fa-plus"></i> Add Item
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary & Payment Information -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                        <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-primary me-2"></i>Payment & Totals</h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Discount Amount</label>
                                                    <input type="number" name="discount_apply" class="form-control" min="0"
                                                        step="0.01" value="0" id="discountAmount">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Paid Amount</label>
                                                    <input type="number" name="paid_amount" class="form-control" min="0"
                                                        step="0.01" value="0" id="paidAmount">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light p-3 rounded">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Subtotal:</span>
                                                        <span class="fw-bold" id="subtotalDisplay">0.00৳</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Discount:</span>
                                                        <span class="fw-bold text-danger" id="discountDisplay">-0.00৳</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Tax ({{ $tax_rate }}%):</span>
                                                        <span class="fw-bold" id="taxDisplay">0.00৳</span>
                                                    </div>
                                                    <hr>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="fw-bold">Total:</span>
                                                        <span class="fw-bold fs-5" id="totalDisplay">0.00৳</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Paid:</span>
                                                        <span class="fw-bold text-success" id="paidDisplay">0.00৳</span>
                                                    </div>
                                                    <hr>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="fw-bold">Due Amount:</span>
                                                        <span class="fw-bold fs-5 text-warning" id="dueDisplay">0.00৳</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Text -->
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Footer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Footer Text</label>
                                            <div id="footerTextEditor" style="height: 120px;">{!! old('footer_text') !!}
                                            </div>
                                            <input type="hidden" name="footer_text" id="footerTextInput"
                                                value="{{ old('footer_text') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Create Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .select2 {
        border: 1px solid #dee2e6;
        border-radius: 7px;
    }
</style>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#customerSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search and select customer...',
                ajax: {
                    url: '/erp/customers/search',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (customer) {
                                return {
                                    id: customer.id,
                                    text: customer.name + (customer.email ? ' (' + customer.email + ')' : '') + (customer.phone ? ' [' + customer.phone + ']' : '')
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });

            // Autofill address on customer select
            $('#customerSelect').on('select2:select', function (e) {
                var customerId = e.params.data.id;
                $.get('/erp/customers/' + customerId + '/address', function (data) {
                    // Only autofill if fields are empty
                    if (!$('input[name="billing_address_1"]').val()) $('input[name="billing_address_1"]').val(data.address_1);
                    if (!$('input[name="billing_address_2"]').val()) $('input[name="billing_address_2"]').val(data.address_2);
                    if (!$('input[name="billing_city"]').val()) $('input[name="billing_city"]').val(data.city);
                    if (!$('input[name="billing_state"]').val()) $('input[name="billing_state"]').val(data.state);
                    if (!$('input[name="billing_country"]').val()) $('input[name="billing_country"]').val(data.country);
                    if (!$('input[name="billing_zip_code"]').val()) $('input[name="billing_zip_code"]').val(data.zip_code);
                    // Optionally autofill shipping as well if empty
                    if (!$('input[name="shipping_address_1"]').val()) $('input[name="shipping_address_1"]').val(data.address_1);
                    if (!$('input[name="shipping_address_2"]').val()) $('input[name="shipping_address_2"]').val(data.address_2);
                    if (!$('input[name="shipping_city"]').val()) $('input[name="shipping_city"]').val(data.city);
                    if (!$('input[name="shipping_state"]').val()) $('input[name="shipping_state"]').val(data.state);
                    if (!$('input[name="shipping_country"]').val()) $('input[name="shipping_country"]').val(data.country);
                    if (!$('input[name="shipping_zip_code"]').val()) $('input[name="shipping_zip_code"]').val(data.zip_code);
                });
            });



            function initProductSelect2(selector) {
                $(selector).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search and select product...',
                    ajax: {
                        url: '/erp/products/search',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(function (product) {
                                    return {
                                        id: product.id,
                                        text: product.name
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                });
            }
            initProductSelect2('.product-select');

            // Fix for select2 search focus
            $(document).on('select2:open', function(e) {
                window.setTimeout(function () {
                    const searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 10);
            });

            $('#addItemBtn').on('click', function () {
                setTimeout(function () {
                    initProductSelect2('.product-select');
                }, 100);
            });
        });
        let itemIndex = 1;

        function recalcRow(row) {
            const qty = parseFloat(row.find('.item-qty').val()) || 0;
            const unit = parseFloat(row.find('.item-unit').val()) || 0;
            const discount = parseFloat(row.find('.item-discount').val()) || 0;
            const total = (qty * unit) - discount;
            row.find('.item-total').val(total.toFixed(2));
            updateTotals();
        }

        function updateTotals() {
            let subtotal = 0;

            $('#itemsTable tbody tr').each(function () {
                const total = parseFloat($(this).find('.item-total').val()) || 0;
                subtotal += total;
            });

            const taxRate = {{ $tax_rate ?? 0 }};
            const discount = parseFloat($('#discountAmount').val()) || 0;
            const paid = parseFloat($('#paidAmount').val()) || 0;
            
            const tax = (subtotal * taxRate) / 100;
            const total = subtotal + tax - discount;
            const due = total - paid;

            $('#subtotalDisplay').text(subtotal.toFixed(2) + '৳');
            $('#discountDisplay').text(discount.toFixed(2) + '৳');
            $('#taxDisplay').text(tax.toFixed(2) + '৳');
            $('#totalDisplay').text(total.toFixed(2) + '৳');
            $('#paidDisplay').text(paid.toFixed(2) + '৳');
            $('#dueDisplay').text(due.toFixed(2) + '৳');
        }

        $(document).on('input', '.item-qty, .item-unit, .item-discount', function () {
            const row = $(this).closest('tr');
            recalcRow(row);
        });

        $(document).on('change', '.product-select', function() {
            var row = $(this).closest('tr');
            var productId = $(this).val();
            var variationSelect = row.find('.variation-select');
            
            if (!productId) {
                variationSelect.html('<option value="">No Variation</option>').prop('disabled', true);
                return;
            }

            // Check if product has variations
            $.get('/erp/products/' + productId + '/variations-list', function(variations) {
                variationSelect.empty();
                
                if (variations && variations.length > 0) {
                    variationSelect.append('<option value="">Select Variation</option>');
                    variations.forEach(function(v) {
                        variationSelect.append('<option value="' + v.id + '" data-base-price="' + v.base_price + '" data-discount="' + v.discount + '" data-stock="' + v.stock + '">' + v.display_name + '</option>');
                    });
                    variationSelect.prop('disabled', false).prop('required', true);
                    row.find('.item-unit').val('');
                    row.find('.item-discount').val(0);
                } else {
                    variationSelect.append('<option value="">No Variation</option>');
                    variationSelect.prop('disabled', true).prop('required', false);
                    
                    // Fetch basic product price
                    $.get('/erp/products/' + productId + '/price', function(data) {
                        row.find('.item-unit').val(data.base_price);
                        row.find('.item-discount').val(data.discount);
                        row.find('.item-qty').attr('data-stock', data.stock);
                        row.find('.item-qty').val(1);
                        
                        if (data.stock <= 0) {
                            alert('Warning: This product is out of stock (Stock: 0)');
                        }
                        
                        recalcRow(row);
                    });
                }
            });
        });

        $(document).on('change', '.variation-select', function() {
            var row = $(this).closest('tr');
            var selectedOption = $(this).find('option:selected');
            var basePrice = selectedOption.data('base-price');
            var discount = selectedOption.data('discount') || 0;
            var stock = selectedOption.data('stock') || 0;
            
            if (basePrice !== undefined) {
                row.find('.item-unit').val(basePrice);
                row.find('.item-discount').val(discount);
                row.find('.item-qty').attr('data-stock', stock);
                
                if (stock <= 0) {
                    alert('Warning: This variation is out of stock (Stock: 0)');
                }
                
                if (!row.find('.item-qty').val()) {
                    row.find('.item-qty').val(1);
                }
                recalcRow(row);
            }
        });

        // Stock check on quantity change
        $(document).on('input', '.item-qty', function() {
            var row = $(this).closest('tr');
            var qty = parseFloat($(this).val()) || 0;
            var stock = parseFloat($(this).attr('data-stock')) || 0;
            var productName = row.find('.product-select option:selected').text() || 'Product';

            if (qty > stock) {
                alert('Warning: Quantity (' + qty + ') exceeds available stock (' + stock + ') for ' + productName);
                $(this).val(stock);
                recalcRow(row);
            }
        });

        $(document).on('input', '#discountAmount', function () {
            updateTotals();
        });

        $(document).on('input', '#paidAmount', function () {
            updateTotals();
        });

        $('#addItemBtn').on('click', function () {
            const row = $('#itemsTable tbody tr:first').clone();
            
            // Remove select2 initialization from cloned row
            row.find('.select2-container').remove();
            row.find('select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id').show();
            
            row.find('select, input').each(function () {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\d+/, itemIndex);
                    $(this).attr('name', newName);
                }
                if ($(this).is('select')) {
                    $(this).val('');
                    if ($(this).hasClass('variation-select')) {
                        $(this).prop('disabled', true).html('<option value="">No Variation</option>');
                    }
                }
                else {
                    if ($(this).hasClass('item-discount')) {
                        $(this).val('0');
                    } else {
                        $(this).val('');
                    }
                }
            });
            row.find('.remove-item').prop('disabled', false);
            $('#itemsTable tbody').append(row);
            
            // Re-initialize Select2 for the new product-select
            initProductSelect2(row.find('.product-select'));
            
            itemIndex++;
            updateTotals();
        });

        $(document).on('click', '.remove-item', function () {
            if ($('#itemsTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                updateTotals();
            }
        });

        // Initialize totals on page load
        $(document).ready(function () {
            updateTotals();
        });
    </script>
    <!-- Quill Editor CDN -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        $(document).ready(function () {
            var quill = new Quill('#footerTextEditor', {
                theme: 'snow',
                placeholder: 'Add footer text that will appear at the bottom of the invoice...',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        ['link', 'clean']
                    ]
                }
            });
            // Set Quill content from hidden input (for old input)
            var oldFooter = $('#footerTextInput').val();
            if (oldFooter) {
                quill.root.innerHTML = oldFooter;
            }
            // On form submit, copy Quill HTML to hidden input
            $('form').on('submit', function () {
                $('#footerTextInput').val(quill.root.innerHTML);
            });
            // On template select, set Quill content to selected template's footer_note
            $('#templateSelect').on('change', function () {
                var selected = $(this).find('option:selected');
                var footer = selected.data('footer') || '';
                quill.root.innerHTML = footer;
            }).trigger('change');
        });
    </script>
@endpush