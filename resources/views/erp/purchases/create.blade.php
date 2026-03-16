@extends('erp.master')

@section('title', 'Purchase Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        


    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-uppercase" style="font-size: 0.75rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('purchase.list') }}" class="text-decoration-none text-muted">Purchases</a></li>
                        <li class="breadcrumb-item active text-primary fw-bold">New Procurement</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Create New Purchase</h4>
                <p class="text-muted small mb-0">Record a new procurement entry for inventory stock</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('purchase.list') }}" class="btn btn-light border px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                    <i class="fas fa-history me-2"></i>Purchase History
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <form id="purchaseForm" action="{{ route('purchase.store') }}" method="POST">
            @csrf
            
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-file-invoice me-2 text-primary"></i>Procurement Details</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Top Row: General Info & Style Search -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label for="purchase_date" class="form-label small fw-bold text-muted text-uppercase mb-2">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="location_selector" class="form-label small fw-bold text-muted text-uppercase mb-2">Receive At (Location) <span class="text-danger">*</span></label>
                            <select id="location_selector" class="form-select select2-simple" required>
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
                            <label for="supplier_id" class="form-label small fw-bold text-muted text-uppercase mb-2">Select Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="form-select select2-premium-42" required>
                                <option value="">Select One</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="styleNumberSearch" class="form-label small fw-bold text-muted text-uppercase mb-2">Product Search (Name/Style/SKU) <span class="text-danger">*</span></label>
                            <select id="styleNumberSearch" class="form-select select2-premium-42"></select>
                        </div>
                    </div>

                    <!-- Product Table -->
                    <div class="table-responsive mb-4 border rounded">
                        <table class="table premium-table mb-0" id="itemsTable">
                            <thead>
                                <tr class="bg-light">
                                    <th width="60">IMAGE</th>
                                    <th>SPECIFICATIONS</th>
                                    <th width="200">PRODUCT NAME</th>
                                    <th>STYLE NO.</th>
                                    <th width="120">SIZE</th>
                                    <th width="120">COLOR</th>
                                    <th width="90" class="text-center">QTY</th>
                                    <th width="130" class="text-end">UNIT PRICE</th>
                                    <th width="140" class="text-end">TOTAL</th>
                                    <th width="60" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows added dynamically via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Grand Totals Section -->
                    <div class="row g-4 pt-4 border-top">
                        <div class="col-md-7">
                            <div class="premium-card bg-light border-0 shadow-none">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-uppercase text-muted small"><i class="fas fa-credit-card me-2"></i>Payment & Audit Notes</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="payment_method" class="form-label small fw-bold text-muted text-uppercase mb-2">Account Type <span class="text-danger">*</span></label>
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
                                        <div class="col-md-6">
                                            <label for="account_id" class="form-label small fw-bold text-muted text-uppercase mb-2">Account Number <span class="text-danger">*</span></label>
                                            <select name="account_id" id="account_id" class="form-select">
                                                <option value="">Select Account</option>
                                                @foreach($bankAccounts as $acc)
                                                    <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                                        {{ $acc->provider_name }} — {{ $acc->account_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="notes" class="form-label small fw-bold text-muted text-uppercase mb-2">Internal Note</label>
                                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Optional audit notes..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="p-4 bg-white rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small fw-bold text-muted text-uppercase">Total Quantity</span>
                                    <span id="total_qty_display_text" class="fw-bold fs-5">0</span>
                                    <input type="hidden" id="total_qty_display" value="0">
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small fw-bold text-muted text-uppercase">Subtotal</span>
                                    <span id="sub_total_display_text" class="fw-bold fs-5">0.00৳</span>
                                    <input type="hidden" id="sub_total_display" value="0.00">
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-muted text-uppercase">Discount</span>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="discount_type" id="discount_flat" value="flat" checked>
                                            <label class="btn btn-outline-secondary py-0" for="discount_flat">৳</label>
                                            <input type="radio" class="btn-check" name="discount_type" id="discount_percent" value="percent">
                                            <label class="btn btn-outline-secondary py-0" for="discount_percent">%</label>
                                        </div>
                                    </div>
                                    <input type="number" name="discount_value" id="discount_value" class="form-control text-end fw-bold" value="0" step="0.01">
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-primary text-uppercase">Grand Total</span>
                                    <span id="total_amount_display_text" class="fw-bold fs-4 text-primary">0.00৳</span>
                                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted text-uppercase mb-2">Paid Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="paid_amount" id="paid_amount" class="form-control text-end fw-bold fs-5 text-success border-2" value="0" step="0.01">
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-danger text-uppercase">Total Due</span>
                                    <span id="due_amount_display_text" class="fw-bold fs-5 text-danger">0.00৳</span>
                                    <input type="hidden" id="due_amount_display" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                        <a href="{{ route('purchase.list') }}" class="btn btn-light border px-5 fw-bold text-muted">
                            <i class="fas fa-times-circle me-2"></i>Discard
                        </a>
                        <button type="submit" class="btn btn-create-premium px-5 py-3 fw-bold">
                            <i class="fas fa-check-circle me-2"></i>COMPLETE PROCUREMENT
                        </button>
                    </div>
                </div>
            </div>
        </form>
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
            if (product.has_variations && product.variations && product.variations.length > 0) {
                product.variations.forEach(v => addOneRow(product, v));
            } else {
                addOneRow(product, null);
            }
            updateTotals();
        }

        function addOneRow(p, v = null, options = {}) {
            const tableBody = $('#itemsTable tbody');
            let sizeOptions = `<option value="">Size</option>`;
            let colorOptions = `<option value="">Color</option>`;
            let selectedSize = options.size_id || '';
            let selectedColor = options.color_id || '';
            let showSize = false;
            let showColor = false;
            let variationId = v ? v.id : (options.variation_id || '');
            let unitPrice = options.unitPrice !== undefined ? options.unitPrice : (v ? (v.cost || p.cost || 0) : (p.cost || 0));

            if (p.has_variations) {
                if (p.sizes && p.sizes.length > 0) {
                    p.sizes.forEach(s => sizeOptions += `<option value="${s.id}">${s.name}</option>`);
                    showSize = true;
                }
                if (p.colors && p.colors.length > 0) {
                    p.colors.forEach(c => colorOptions += `<option value="${c.id}">${c.name}</option>`);
                    showColor = true;
                }

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
                    <td class="text-center"><img src="${p.image}" width="40" height="40" class="rounded bg-light shadow-sm"></td>
                    <td class="small text-muted text-uppercase" style="font-size: 0.65rem; line-height: 1.2;">
                        <div><span class="fw-bold text-dark">CAT:</span> ${p.category || '-'}</div>
                        <div><span class="fw-bold text-dark">BRD:</span> ${p.brand || '-'}</div>
                        <div><span class="fw-bold text-dark">SEA:</span> ${p.season || '-'} / ${p.gender || '-'}</div>
                    </td>
                    <td class="fw-bold text-dark">${p.name}</td>
                    <td><code class="text-primary bg-light px-2 py-1 rounded small">${p.style_number ?? p.sku ?? '-'}</code></td>
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
                        <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm quantity text-center border-2 fw-bold" value="${options.quantity || 1}" min="1" step="1">
                        <input type="hidden" name="items[${rowIndex}][product_id]" value="${p.id}">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm unit_price text-end border-2 fw-bold" value="${unitPrice}" step="0.01">
                    </td>
                    <td class="text-end fw-bold text-primary">
                        <span class="item-total-val">${(parseFloat(unitPrice) * (options.quantity || 1)).toFixed(2)}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-light border p-1 rounded-circle copy-row" title="Add variation">
                                <i class="fas fa-plus-circle text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border p-1 rounded-circle remove-row">
                                <i class="fas fa-times-circle text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            
            const $row = $(rowTemplate);
            tableBody.append($row);
            
            if (selectedSize) $row.find('.size-select').val(selectedSize);
            if (selectedColor) $row.find('.color-select').val(selectedColor);
            
            $row.data('pdata', p);
            rowIndex++;
        }

        // Duplication logic
        $(document).on('click', '.copy-row', function() {
            const $row = $(this).closest('tr');
            const p = $row.data('pdata');
            const currentVals = {
                size_id: $row.find('.size-select').val(),
                color_id: $row.find('.color-select').val(),
                quantity: $row.find('.quantity').val(),
                unitPrice: $row.find('.unit_price').val(),
                variation_id: $row.find('.variation-id-input').val()
            };
            addOneRow(p, null, currentVals);
            updateTotals();
        });

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
                // Update unit price from cost (purchasing)
                if (foundVal.cost && parseFloat(foundVal.cost) > 0) {
                    $row.find('.unit_price').val(parseFloat(foundVal.cost).toFixed(2));
                }
                updateTotals();
            } else {
                $row.find('.variation-id-input').val('');
            }
        });

        function updateTotals() {
            let subtotal = 0;
            let totalQty = 0;
            $('.item-row').each(function() {
                const qtyVal = parseFloat($(this).find('.quantity').val()) || 0;
                const priceVal = parseFloat($(this).find('.unit_price').val()) || 0;
                const rowTotal = qtyVal * priceVal;
                $(this).find('.item-total-val').text(rowTotal.toFixed(2));
                subtotal += rowTotal;
                totalQty += qtyVal;
            });

            $('#total_qty_display_text').text(totalQty);
            $('#total_qty_display').val(totalQty);
            $('#sub_total_display_text').text(subtotal.toFixed(2) + '৳');
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
            $('#total_amount_display_text').text(grandTotal.toFixed(2) + '৳');
            $('#total_amount').val(grandTotal.toFixed(2));

            const paidVal = parseFloat($('#paid_amount').val()) || 0;
            const dueVal = grandTotal - paidVal;
            $('#due_amount_display_text').text(dueVal.toFixed(2) + '৳');
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