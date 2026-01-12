@extends('erp.master')

@section('title', 'Edit Sale Return')

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
                            <li class="breadcrumb-item active">Edit Return #{{ $saleReturn->id }}</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Edit Sale Return</h2>
                    <p class="text-muted mb-0">Update information for return reference #SR-{{ str_pad($saleReturn->id, 5, '0', STR_PAD_LEFT) }}</p>
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

            <form id="saleReturnForm" action="{{ route('saleReturn.update', $saleReturn->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <!-- Left Column: Basic Info -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="form-section-title">Return Source</div>
                                
                                <div class="mb-3">
                                    <label for="pos_sale_id" class="form-label fw-bold small">POS Sale Reference <span class="text-danger">*</span></label>
                                    <select name="pos_sale_id" id="pos_sale_id" class="form-select" required>
                                        <option value="{{ $saleReturn->pos_sale_id }}" selected>POS #{{ $saleReturn->pos_sale_id }}</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_id" class="form-label fw-bold small">Customer <span class="text-secondary">(Auto-filled)</span></label>
                                    <select name="customer_id" id="customer_id" class="form-select">
                                        <option value="{{ $saleReturn->customer_id }}" selected>{{ $saleReturn->customer->name }}</option>
                                    </select>
                                </div>

                                <div class="form-section-title mt-4">Return Details</div>

                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-bold small">Return Date <span class="text-danger">*</span></label>
                                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $saleReturn->return_date }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="refund_type" class="form-label fw-bold small">Refund Method <span class="text-danger">*</span></label>
                                    <select name="refund_type" id="refund_type" class="form-select" required>
                                        <option value="none" {{ $saleReturn->refund_type == 'none' ? 'selected' : '' }}>No Refund</option>
                                        <option value="cash" {{ $saleReturn->refund_type == 'cash' ? 'selected' : '' }}>Cash Refund</option>
                                        <option value="bank" {{ $saleReturn->refund_type == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="credit" {{ $saleReturn->refund_type == 'credit' ? 'selected' : '' }}>Store Credit</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="return_to_type" class="form-label fw-bold small">Restock Location <span class="text-danger">*</span></label>
                                    <select name="return_to_type" id="return_to_type" class="form-select mb-2" required>
                                        <option value="">Select Location Type</option>
                                        <option value="branch" {{ $saleReturn->return_to_type == 'branch' ? 'selected' : '' }}>Branch Office</option>
                                        <option value="warehouse" {{ $saleReturn->return_to_type == 'warehouse' ? 'selected' : '' }}>Central Warehouse</option>
                                        <option value="employee" {{ $saleReturn->return_to_type == 'employee' ? 'selected' : '' }}>Field Employee</option>
                                    </select>
                                    <select name="return_to_id" id="return_to_id" class="form-select" required>
                                        @if($saleReturn->return_to_id)
                                            <option value="{{ $saleReturn->return_to_id }}" selected>
                                                @if($saleReturn->return_to_type == 'branch') {{ $saleReturn->branch->name ?? 'Branch' }}
                                                @elseif($saleReturn->return_to_type == 'warehouse') {{ $saleReturn->warehouse->name ?? 'Warehouse' }}
                                                @elseif($saleReturn->return_to_type == 'employee') {{ $saleReturn->employee->user->first_name ?? 'Employee' }} @endif
                                            </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="form-section-title">Additional Info</div>
                                <div class="mb-3">
                                    <label for="reason" class="form-label fw-bold small">Primary Reason</label>
                                    <input type="text" name="reason" id="reason" class="form-control" value="{{ $saleReturn->reason }}" placeholder="e.g., Damaged item">
                                </div>
                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-bold small">Internal Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ $saleReturn->notes }}</textarea>
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
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="addItemRow">
                                        <i class="fas fa-plus me-1"></i>Add Item
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
                                            @foreach($saleReturn->items as $i => $item)
                                            <tr class="product-row" data-index="{{ $i }}">
                                                <td class="ps-4">
                                                    <select name="items[{{ $i }}][product_id]" class="form-select product-select" required>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="variation-wrapper mt-2" style="display:none;" data-product-id="{{ $item->product_id }}" data-variation-id="{{ $item->variation_id ?? '' }}">
                                                        <select name="items[{{ $i }}][variation_id]" class="form-select variation-select small py-1">
                                                            <option value="">Standard Variation</option>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="items[{{ $i }}][sale_item_id]" value="{{ $item->sale_item_id }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $i }}][returned_qty]" class="form-control" min="0.01" step="0.01" value="{{ $item->returned_qty }}" required>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text border-0 bg-transparent">৳</span>
                                                        <input type="number" name="items[{{ $i }}][unit_price]" class="form-control border-0 bg-light unit_price" min="0" step="0.01" value="{{ $item->unit_price }}" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $i }}][reason]" class="form-control form-control-sm border-0 bg-light" value="{{ $item->reason }}" placeholder="Defect?">
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <button type="button" class="btn btn-link text-danger p-0 btn-remove shadow-none">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-0 p-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 shadow-sm fw-bold">
                                    <i class="fas fa-save me-2"></i>Update Return
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
        let itemIndex = {{ count($saleReturn->items) }};
        
        function safeDestroySelect2($element) {
            if ($element.length && $element.hasClass('select2-hidden-accessible')) {
                try { $element.select2('destroy'); } catch(e) {}
            }
        }

        $(document).ready(function() {
            // Initial POS Sale Select2
            $('#pos_sale_id').select2({
                placeholder: 'Search POS Sale...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '/erp/pos/search',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term || '', customer_id: $('#customer_id').val() }),
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

            $('#pos_sale_id').on('change select2:select', function() {
                const posSaleId = $(this).val();
                if (posSaleId) {
                    $.get(`/erp/pos/${posSaleId}/details`, function(response) {
                        if (response.success && response.data) {
                            const data = response.data;
                            if (data.customer_id) {
                                const $customerSelect = $('#customer_id');
                                if ($customerSelect.val() != data.customer_id) {
                                    const option = new Option(data.customer_name || 'Customer #' + data.customer_id, data.customer_id, true, true);
                                    $customerSelect.empty().append(option).trigger('change');
                                }
                            }
                        }
                    });
                }
            });

            function initRowEvents($row) {
                const $productSelect = $row.find('.product-select');
                $productSelect.select2({ width: '100%' });
                
                $productSelect.on('change', function() {
                    const productId = $(this).val();
                    if (productId) loadVariationsForProduct($(this), productId, null);
                });
            }

            $('.product-row').each(function() {
                const $row = $(this);
                initRowEvents($row);
                
                const $wrapper = $row.find('.variation-wrapper');
                const productId = $wrapper.data('product-id');
                const variationId = $wrapper.data('variation-id');
                if (productId) {
                    loadVariationsForProduct($row.find('.product-select'), productId, variationId);
                }
            });

            $('#addItemRow').on('click', function() {
                const i = itemIndex++;
                const row = $(`
                    <tr class="product-row">
                        <td class="ps-4">
                            <select name="items[${i}][product_id]" class="form-select product-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            <div class="variation-wrapper mt-2" style="display:none;">
                                <select name="items[${i}][variation_id]" class="form-select variation-select small py-1">
                                    <option value="">Standard Variation</option>
                                </select>
                            </div>
                        </td>
                        <td><input type="number" name="items[${i}][returned_qty]" class="form-control" min="0.01" step="0.01" required></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-0 bg-transparent">৳</span>
                                <input type="number" name="items[${i}][unit_price]" class="form-control border-0 bg-light unit_price" min="0" step="0.01" required>
                            </div>
                        </td>
                        <td><input type="text" name="items[${i}][reason]" class="form-control form-control-sm border-0 bg-light"></td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove shadow-none">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </td>
                    </tr>
                `);
                $('#itemsTable tbody').append(row);
                initRowEvents(row);
            });

            $(document).on('click', '.btn-remove', function() {
                $(this).closest('tr').fadeOut(200, function() { $(this).remove(); });
            });

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
                    }
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
                    $idSelect.show().prop('required', true).select2({ width: '100%' });
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

            // Initialize existing location select if needed
            if ($('#return_to_type').val() && $('#return_to_type').val() !== 'employee') {
                $('#return_to_id').select2({ width: '100%' });
            } else if ($('#return_to_type').val() === 'employee') {
                $('#return_to_type').trigger('change');
            }
        });
    </script>
@endsection