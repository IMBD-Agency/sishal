@extends('erp.master')

@section('title', 'Purchase Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            :root {
                --primary-teal: #17a2b8;
                --primary-hover: #138496;
                --danger-red: #dc3545;
                --danger-hover: #c82333;
                --gray-light: #f9fafb;
                --border-color: #d1d5db;
                --text-main: #1f2937;
                --text-muted: #6b7280;
            }

            .purchase-card { 
                background: #fff;
                border-radius: 6px; 
                border: 1px solid #e5e7eb; 
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); 
            }
            .purchase-card-header { 
                background: #fff; 
                border-bottom: 1px solid #f3f4f6; 
                padding: 1.25rem 1.5rem; 
                font-weight: 700; 
                color: var(--text-main);
                font-size: 1.1rem;
            }
            .form-label { 
                font-size: 0.85rem; 
                font-weight: 700; 
                color: #374151; 
                margin-bottom: 0.5rem; 
            }
            .form-control, .form-select { 
                border-radius: 4px; 
                border: 1px solid var(--border-color); 
                font-size: 0.9rem; 
                padding: 0.6rem 0.75rem;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
            .form-control:focus, .form-select:focus { 
                border-color: var(--primary-teal); 
                box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1); 
                outline: 0;
            }
            
            #itemsTable thead th { 
                background: var(--gray-light); 
                color: var(--text-muted); 
                font-size: 0.7rem; 
                font-weight: 700; 
                text-transform: uppercase; 
                letter-spacing: 0.05em; 
                padding: 0.75rem 0.5rem; 
                border-bottom: 2px solid #f3f4f6; 
            }
            #itemsTable tbody td { 
                padding: 0.75rem 0.5rem; 
                border-bottom: 1px solid #f3f4f6; 
                font-size: 0.85rem; 
                vertical-align: middle;
            }
            
            .summary-label { 
                font-size: 0.95rem; 
                font-weight: 700; 
                color: var(--text-main); 
                text-align: right; 
                padding-right: 1.5rem; 
            }
            .summary-input { 
                background: var(--gray-light) !important; 
                font-weight: 700; 
                border-color: #e5e7eb; 
                text-align: right; 
                font-size: 1rem;
            }
            
            .btn-teal { 
                background-color: var(--primary-teal); 
                color: #fff; 
                border: none; 
                padding: 0.75rem 2rem; 
                border-radius: 4px; 
                font-weight: 600;
                transition: background-color 0.2s;
            }
            .btn-teal:hover { background-color: var(--primary-hover); color: #fff; }
            .btn-red { 
                background-color: var(--danger-red); 
                color: #fff; 
                border: none; 
                padding: 0.75rem 2rem; 
                border-radius: 4px; 
                font-weight: 600;
                transition: background-color 0.2s;
            }
            .btn-red:hover { background-color: var(--danger-hover); color: #fff; }
            
            .select2-container--default .select2-selection--single { 
                border: 1px solid var(--border-color); 
                border-radius: 4px; 
                height: 42px; 
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered { 
                line-height: 40px; 
                font-size: 0.9rem; 
                color: #374151; 
                padding-left: 0.75rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
            
            .variant-tag {
                display: inline-block;
                padding: 0.1rem 0.5rem;
                border-radius: 9999px;
                background: #eff6ff;
                color: #1e40af;
                font-size: 0.75rem;
                font-weight: 600;
            }
        </style>

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
                        <div class="row g-4 mb-5">
                            <div class="col-md-3">
                                <label for="purchase_date" class="form-label">Purchase Date *</label>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="supplier_id" class="form-label">Select Supplier *</label>
                                <select name="supplier_id" id="supplier_id" class="form-select select2-supplier" required>
                                    <option value="">Select One</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="styleNumberSearch" class="form-label">Style Number Search (Auto-Add) *</label>
                                <select id="styleNumberSearch" class="form-select"></select>
                            </div>
                        </div>

                        <!-- Product Table -->
                        <div class="table-responsive mb-5">
                            <table class="table align-middle" id="itemsTable">
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
                                    <div class="col-6 summary-label">Total Amount *</div>
                                    <div class="col-6">
                                        <input type="text" id="total_amount_display" class="form-control summary-input" readonly value="0.00">
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
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile">Mobile Banking</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="account_id" class="form-label">Account Number *</label>
                                <select name="account_id" id="account_id" class="form-select">
                                    <option value="">Select Account</option>
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">{{ $acc->provider_name }} - {{ $acc->account_number }}</option>
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

                <!-- Mandatory Hidden Fields -->
                <input type="hidden" name="ship_location_type" value="warehouse">
                <input type="hidden" name="location_id" value="1">
            </form>
        </div>
    </div>

    <!-- Scripting Engine -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Global Select2 for static inputs
            $('.select2-supplier').select2({ width: '100%', placeholder: 'Select Supplier' });

            // ENHANCED: Auto-add on Style Number Select
            $('#styleNumberSearch').select2({
                placeholder: 'Search Style Number...',
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
            $('#paid_amount').on('input', updateTotals);
        });

        let rowIndex = 0;

        function addItemRow(product) {
            const tableBody = $('#itemsTable tbody');
            
            // Generate metadata labels or selects
            let sizeOptions = `<option value="">Size</option>`;
            let colorOptions = `<option value="">Color</option>`;
            let showSize = false;
            let showColor = false;
            
            if (product.has_variations) {
                if (product.sizes && product.sizes.length > 0) {
                    product.sizes.forEach(s => sizeOptions += `<option value="${s.id}">${s.name}</option>`);
                    showSize = true;
                }
                if (product.colors && product.colors.length > 0) {
                    product.colors.forEach(c => colorOptions += `<option value="${c.id}">${c.name}</option>`);
                    showColor = true;
                }
            }

            // Fallback for non-variable or missing attributes
            if (!showSize) sizeOptions = `<option value="">-</option>`;
            if (!showColor) colorOptions = `<option value="">-</option>`;

            const rowTemplate = `
                <tr class="item-row" data-index="${rowIndex}">
                    <td><img src="${product.image}" width="40" height="40" class="img-thumbnail border-0 bg-light"></td>
                    <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${product.category || '-'}</td>
                    <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${product.brand || '-'}</td>
                    <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${product.season || '-'}</td>
                    <td class="text-uppercase text-muted" style="font-size: 0.75rem;">${product.gender || '-'}</td>
                    <td class="fw-bold">${product.name}</td>
                    <td class="text-secondary small">#${product.style_number}</td>
                    <td>
                        <select name="items[${rowIndex}][size_id]" class="form-select form-select-sm size-select" ${!showSize ? 'disabled' : ''}>
                            ${sizeOptions}
                        </select>
                    </td>
                    <td>
                        <select name="items[${rowIndex}][color_id]" class="form-select form-select-sm color-select" ${!showColor ? 'disabled' : ''}>
                            ${colorOptions}
                        </select>
                        <input type="hidden" name="items[${rowIndex}][variation_id]" class="variation-id-input">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm quantity text-center border-2 fw-bold" value="1" min="0.01" step="0.01">
                        <input type="hidden" name="items[${rowIndex}][product_id]" value="${product.id}">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIndex}][unit_price]" class="form-control form-control-sm unit_price text-end border-2 fw-bold" value="${product.cost || 0}" step="0.01">
                    </td>
                    <td class="text-end fw-bold">
                        <span class="item-total-val text-primary">${parseFloat(product.cost || 0).toFixed(2)}</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            tableBody.append(rowTemplate);
            
            // Embed product object for later attribute matching
            tableBody.find(`tr[data-index="${rowIndex}"]`).data('pdata', product);
            
            rowIndex++;
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
            let grand = 0;
            $('.item-row').each(function() {
                const qtyVal = parseFloat($(this).find('.quantity').val()) || 0;
                const priceVal = parseFloat($(this).find('.unit_price').val()) || 0;
                const subTotal = qtyVal * priceVal;
                $(this).find('.item-total-val').text(subTotal.toFixed(2));
                grand += subTotal;
            });

            $('#total_amount_display').val(grand.toFixed(2));
            const paidVal = parseFloat($('#paid_amount').val()) || 0;
            const dueVal = grand - paidVal;
            $('#due_amount_display').val(dueVal.toFixed(2));
        }
    </script>
@endsection