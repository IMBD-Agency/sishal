@extends('erp.master')

@section('title', 'Purchase Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        


        <div class="container-fluid px-4 py-4 bg-white border-bottom mb-4">
            <h2 class="fw-bold mb-0" style="font-size: 26px; color: var(--text-main);">Purchase</h2>
        </div>

        <div class="container-fluid px-4 pb-5">
            <form id="purchaseForm" action="{{ route('purchase.store') }}" method="POST">
                @csrf
                
                <div class="card purchase-card border-0">
                    <div class="purchase-card-header">
                        Purchase Information
                    </div>
                    <div class="card-body p-4">
                        <!-- Top Row: General Info & Style Search -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-2">
                                <label for="purchase_date" class="form-label">Purchase Date *</label>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="location_selector" class="form-label">Receive At (Location) *</label>
                                <select id="location_selector" class="form-select" required>
                                    <option value="">Select Location</option>
                                    <optgroup label="Branches">
                                        @foreach($branches as $branch)
                                            <option value="branch_{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Warehouses">
                                        @foreach($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <input type="hidden" name="location_id" id="location_id" value="">
                                <input type="hidden" name="ship_location_type" id="ship_location_type" value="">
                            </div>
                            <div class="col-md-3">
                                <label for="supplier_id" class="form-label">Select Supplier *</label>
                                <select name="supplier_id" id="supplier_id" class="form-select select2 select2-premium-42" required>
                                    <option value="">Select One</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="styleNumberSearch" class="form-label">Product Search (Name/Style/SKU) *</label>
                                <select id="styleNumberSearch" class="form-select select2-premium-42"></select>
                            </div>
                        </div>

                        <!-- Product Table -->
                        <div class="table-responsive mb-5">
                            <table class="table premium-form-table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th width="60">IMAGE</th>
                                        <th>CATEGORY</th>
                                        <th>BRAND</th>
                                        <th>SEASON</th>
                                        <th>GENDER</th>
                                        <th width="200">PRODUCT NAME</th>
                                        <th>STYLE NO.</th>
                                        <th width="130">SIZE</th>
                                        <th width="130">COLOR</th>
                                        <th width="90">QTY</th>
                                        <th width="130">UNIT PRICE</th>
                                        <th width="140" class="text-end">TOTAL</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows added dynamically via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Grand Totals Section -->
                        <div class="row justify-content-end mb-5">
                            <div class="col-md-4 border-top pt-4">
                                <div class="row align-items-center mb-3">
                                    <div class="col-6 summary-label">Subtotal</div>
                                    <div class="col-6">
                                        <input type="text" id="sub_total_display" class="form-control summary-input" readonly value="0.00">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <div class="col-6 summary-label">Discount</div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="discount_value" id="discount_value" class="form-control text-end fw-bold" value="0" step="0.01">
                                            <div class="btn-group btn-group-sm ms-1" role="group">
                                                <input type="radio" class="btn-check" name="discount_type" id="discount_flat" value="flat" checked>
                                                <label class="btn btn-outline-secondary" for="discount_flat">৳</label>
                                                
                                                <input type="radio" class="btn-check" name="discount_type" id="discount_percent" value="percent">
                                                <label class="btn btn-outline-secondary" for="discount_percent">%</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <div class="col-6 summary-label">Grand Total</div>
                                    <div class="col-6">
                                        <input type="text" id="total_amount_display" class="form-control summary-input fw-bold text-primary" readonly value="0.00">
                                        <input type="hidden" name="total_amount" id="total_amount" value="0">
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <div class="col-6 summary-label">Paid *</div>
                                    <div class="col-6">
                                        <input type="number" name="paid_amount" id="paid_amount" class="form-control text-end fw-bold text-success border-2" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-6 summary-label text-danger">Due *</div>
                                    <div class="col-6">
                                        <input type="text" id="due_amount_display" class="form-control summary-input text-danger fw-bold" readonly value="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Notes Row -->
                        <div class="row g-4 mt-2 mb-5 border-top pt-5">
                            <div class="col-md-4">
                                <label for="payment_method" class="form-label">Account Type *</label>
                                <select name="payment_method" id="payment_method" class="form-select">
                                    <option value="">Select Type</option>
                                    @php
                                        $availableTypes = $bankAccounts->pluck('type')->unique();
                                        $typeLabels = ['cash' => 'Cash', 'bank' => 'Bank Account', 'mobile' => 'Mobile Banking'];
                                    @endphp
                                    @foreach($typeLabels as $typeVal => $typeLabel)
                                        @if($availableTypes->contains($typeVal))
                                            <option value="{{ $typeVal }}">{{ $typeLabel }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="account_id" class="form-label">Account Number *</label>
                                <select name="account_id" id="account_id" class="form-select">
                                    <option value="">Select Account</option>
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                            {{ $acc->provider_name }} — {{ $acc->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="notes" class="form-label">Purchase Note</label>
                                <textarea name="notes" id="notes" class="form-control" rows="1" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-center pt-4">
                            <button type="submit" class="btn btn-teal px-5 shadow-sm me-3">
                                <i class="fas fa-check-circle me-2"></i>COMPLETE PURCHASE
                            </button>
                            <a href="{{ route('purchase.list') }}" class="btn btn-red px-5 shadow-sm">
                                <i class="fas fa-times-circle me-2"></i>DISCARD
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Location logic handled in header row -->
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // New Location Selector Logic
            $('#location_selector').on('change', function() {
                const val = $(this).val();
                if (!val) {
                    $('#ship_location_type').val('');
                    $('#location_id').val('');
                    return;
                }
                
                const parts = val.split('_');
                if (parts.length === 2) {
                    $('#ship_location_type').val(parts[0]);
                    $('#location_id').val(parts[1]);
                }
            });

            // Supplier dropdown is handled by global .select2 class in master.blade.php


            // ENHANCED: Auto-add on Style Number Select
            $('#styleNumberSearch').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search by Name, Style or SKU...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('products.search.style') }}',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({ q: params.term }),
                    processResults: (data) => ({ results: data.results }),
                    cache: true
                }
            }).on('select2:select', function (e) {
                const styleNo = e.params.data.id;
                if (!styleNo) return;
                
                // Fetch product data and add row immediately
                $.get(`{{ url('/erp/products/find-by-style') }}/${styleNo}`, function(resp) {
                    if (resp.success && resp.products.length > 0) {
                        resp.products.forEach(p => addItemRow(p));
                        $('#styleNumberSearch').val(null).trigger('change');
                    } else {
                        alert('Error: Could not find product details for ' + styleNo);
                    }
                }).fail(err => alert('Failed to fetch product data.'));
            });

            // Account Filtering logic
            $('#payment_method').on('change', function() {
                const method = $(this).val();
                const $acc = $('#account_id');
                $acc.val('').find('option').each(function() {
                    const type = $(this).data('type');
                    if (!type) return; 
                    $(this).toggle(type === method);
                });
            });

            // Calculation hook
            $('#paid_amount, #discount_value, input[name="discount_type"]').on('input change', updateTotals);
        });

        let rowIndex = 0;

        function addItemRow(product) {
            const tableBody = $('#itemsTable tbody');
            const productsToLoad = [];
            
            // If product has variations, load all of them as separate rows
            if (product.has_variations && product.variations && product.variations.length > 0) {
                product.variations.forEach(v => productsToLoad.push({p: product, v: v}));
            } else {
                // Otherwise just load the base product
                productsToLoad.push({p: product, v: null});
            }

            productsToLoad.forEach(item => {
                const p = item.p;
                const v = item.v;
                
                let sizeOptions = `<option value="">Size</option>`;
                let colorOptions = `<option value="">Color</option>`;
                let selectedSize = '';
                let selectedColor = '';
                let showSize = false;
                let showColor = false;
                let variationId = v ? v.id : '';
                let unitPrice = v ? (v.cost || p.cost || 0) : (p.cost || 0);

                if (p.has_variations) {
                    if (p.sizes && p.sizes.length > 0) {
                        p.sizes.forEach(s => sizeOptions += `<option value="${s.id}">${s.name}</option>`);
                        showSize = true;
                    }
                    if (p.colors && p.colors.length > 0) {
                        p.colors.forEach(c => colorOptions += `<option value="${c.id}">${c.name}</option>`);
                        showColor = true;
                    }

                    // Identify pre-selected variations
                    if (v && v.attributes) {
                        v.attributes.forEach(attr => {
                            if (p.sizes && p.sizes.some(s => s.id == attr.value_id)) selectedSize = attr.value_id;
                            if (p.colors && p.colors.some(c => c.id == attr.value_id)) selectedColor = attr.value_id;
                        });
                    }
                }

                if (!showSize) sizeOptions = `<option value="">-</option>`;
                if (!showColor) colorOptions = `<option value="">-</option>`;

                const rowTemplate = `
                    <tr class="item-row" data-index="${rowIndex}">
                        <td><img src="${p.image}" width="40" height="40" class="img-thumbnail border-0 bg-light"></td>
                        <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${p.category || '-'}</td>
                        <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${p.brand || '-'}</td>
                        <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${p.season || '-'}</td>
                        <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${p.gender || '-'}</td>
                        <td class="fw-bold">${p.name}</td>
                        <td class="text-secondary small">${p.style_number ? '#' + p.style_number : '-'}</td>
                        <td>
                            <select name="items[${rowIndex}][size_id]" class="form-select form-select-sm size-select" ${!showSize ? 'disabled' : ''}>
                                ${sizeOptions}
                            </select>
                        </td>
                        <td>
                            <select name="items[${rowIndex}][color_id]" class="form-select form-select-sm color-select" ${!showColor ? 'disabled' : ''}>
                                ${colorOptions}
                            </select>
                            <input type="hidden" name="items[${rowIndex}][variation_id]" class="variation-id-input" value="${variationId}">
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm quantity text-center border-2 fw-bold" value="1" min="0.01" step="0.01">
                            <input type="hidden" name="items[${rowIndex}][product_id]" value="${p.id}">
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm unit_price text-end border-2 fw-bold" value="${unitPrice}" step="0.01">
                        </td>
                        <td class="text-end fw-bold">
                            <span class="item-total-val text-primary">${parseFloat(unitPrice).toFixed(2)}</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                const $row = $(rowTemplate);
                tableBody.append($row);
                
                if (selectedSize) $row.find('.size-select').val(selectedSize);
                if (selectedColor) $row.find('.color-select').val(selectedColor);
                
                $row.data('pdata', p);
                rowIndex++;
            });

            updateTotals();
        }

        // Generic Event Listeners
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        $(document).on('input', '.quantity, .unit_price', updateTotals);

        // ENHANCED: Dynamic Variation Selection Logic
        $(document).on('change', '.size-select, .color-select', function() {
            const $row = $(this).closest('tr');
            const data = $row.data('pdata');
            const sz = $row.find('.size-select').val();
            const cl = $row.find('.color-select').val();

            if (!data.has_variations) return;

            // Determine what's required based on available data
            const hasSizeOpts = data.sizes && data.sizes.length > 0;
            const hasColorOpts = data.colors && data.colors.length > 0;

            // Match logic:
            // 1. If product has sizes, user MUST select a size if they want a match.
            // 2. If product has colors, user MUST select a color if they want a match.
            
            if ((hasSizeOpts && !sz) || (hasColorOpts && !cl)) {
                $row.find('.variation-id-input').val('');
                return;
            }

            // Find matching variation
            const foundVal = data.variations.find(v => {
                let match = true;
                if (hasSizeOpts) {
                    if (!v.attributes.some(a => a.value_id == sz)) match = false;
                }
                if (match && hasColorOpts) {
                    if (!v.attributes.some(a => a.value_id == cl)) match = false;
                }
                return match;
            });

            if (foundVal) {
                $row.find('.variation-id-input').val(foundVal.id);
                // Update price and totals
                if (foundVal.price && parseFloat(foundVal.price) > 0) {
                    $row.find('.unit_price').val(parseFloat(foundVal.price).toFixed(2));
                }
                updateTotals();
            } else {
                $row.find('.variation-id-input').val('');
            }
        });

        function updateTotals() {
            let subtotal = 0;
            $('.item-row').each(function() {
                const qtyVal = parseFloat($(this).find('.quantity').val()) || 0;
                const priceVal = parseFloat($(this).find('.unit_price').val()) || 0;
                const rowTotal = qtyVal * priceVal;
                $(this).find('.item-total-val').text(rowTotal.toFixed(2));
                subtotal += rowTotal;
            });

            $('#sub_total_display').val(subtotal.toFixed(2));

            const discountVal = parseFloat($('#discount_value').val()) || 0;
            const discountType = $('input[name="discount_type"]:checked').val();
            let discountAmount = 0;

            if (discountType === 'percent') {
                discountAmount = (subtotal * discountVal) / 100;
            } else {
                discountAmount = discountVal;
            }

            const grandTotal = Math.max(0, subtotal - discountAmount);
            $('#total_amount_display').val(grandTotal.toFixed(2));
            $('#total_amount').val(grandTotal.toFixed(2));

            const paidVal = parseFloat($('#paid_amount').val()) || 0;
            const dueVal = grandTotal - paidVal;
            $('#due_amount_display').val(dueVal.toFixed(2));

            // Sync required status visually
            if (paidVal > 0) {
                $('#payment_method, #account_id').addClass('border-warning');
            } else {
                $('#payment_method, #account_id').removeClass('border-warning');
            }
        }

        // Form Validation on Submit
        $('#purchaseForm').on('submit', function(e) {
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            const paymentMethod = $('#payment_method').val();
            const accountId = $('#account_id').val();

            if (paidAmount > 0) {
                if (!paymentMethod || !accountId) {
                    e.preventDefault();
                    alert('Please select BOTH Account Type and Account Number because you entered a Paid Amount.');
                    $('#payment_method').focus();
                    return false;
                }
            }
            
            // Basic sanity check for items
            if ($('.item-row').length === 0) {
                e.preventDefault();
                alert('Please add at least one product to the purchase.');
                return false;
            }
        });
    </script>
    @endpush
@endsection