@extends('erp.master')

@section('title', 'Edit Order Return')

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
                            <li class="breadcrumb-item active">Edit Return</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Edit Order Return #{{ str_pad($orderReturn->id, 5, '0', STR_PAD_LEFT) }}</h2>
                    <p class="text-muted mb-0">Update information for this E-commerce return record.</p>
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

            <form id="orderReturnForm" action="{{ route('orderReturn.update', $orderReturn->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <!-- Left Column: Basic Info -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="form-section-title">Return Source</div>
                                
                                <div class="mb-3">
                                    <label for="order_id" class="form-label fw-bold small">Order Reference <span class="text-danger">*</span></label>
                                    <select name="order_id" id="order_id" class="form-select" required>
                                        @if($orderReturn->order)
                                            <option value="{{ $orderReturn->order_id }}" selected>{{ $orderReturn->order->order_number }} - {{ $orderReturn->customer->name ?? 'Customer' }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_id" class="form-label fw-bold small">Customer</label>
                                    <select name="customer_id" id="customer_id" class="form-select">
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $orderReturn->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-section-title mt-4">Return Details</div>

                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-bold small">Return Date <span class="text-danger">*</span></label>
                                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ \Carbon\Carbon::parse($orderReturn->return_date)->format('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="refund_type" class="form-label fw-bold small">Refund Method <span class="text-danger">*</span></label>
                                    <select name="refund_type" id="refund_type" class="form-select" required>
                                        <option value="none" {{ $orderReturn->refund_type == 'none' ? 'selected' : '' }}>No Refund</option>
                                        <option value="cash" {{ $orderReturn->refund_type == 'cash' ? 'selected' : '' }}>Cash Refund</option>
                                        <option value="bank" {{ $orderReturn->refund_type == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="credit" {{ $orderReturn->refund_type == 'credit' ? 'selected' : '' }}>Store Credit</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="return_to_type" class="form-label fw-bold small">Restock Location <span class="text-danger">*</span></label>
                                    <select name="return_to_type" id="return_to_type" class="form-select mb-2" required>
                                        <option value="">Select Location Type</option>
                                        <option value="branch" {{ $orderReturn->return_to_type == 'branch' ? 'selected' : '' }}>Branch Office</option>
                                        <option value="warehouse" {{ $orderReturn->return_to_type == 'warehouse' ? 'selected' : '' }}>Central Warehouse</option>
                                        <option value="employee" {{ $orderReturn->return_to_type == 'employee' ? 'selected' : '' }}>Field Employee</option>
                                    </select>
                                    <select name="return_to_id" id="return_to_id" class="form-select" {{ $orderReturn->return_to_type == 'employee' ? 'style=display:none;' : '' }} required>
                                        <option value="">Select Specific Location</option>
                                        @if($orderReturn->return_to_type == 'branch')
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $orderReturn->return_to_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        @elseif($orderReturn->return_to_type == 'warehouse')
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ $orderReturn->return_to_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                            @endforeach
                                        @elseif($orderReturn->return_to_type == 'employee')
                                            <option value="{{ $orderReturn->return_to_id }}" selected>{{ $orderReturn->destination_name }}</option>
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
                                    <input type="text" name="reason" id="reason" class="form-control" value="{{ $orderReturn->reason }}" placeholder="e.g., Damaged item, customer choice">
                                </div>
                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-bold small">Internal Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter any extra details...">{{ $orderReturn->notes }}</textarea>
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
                                            @foreach($orderReturn->items as $index => $item)
                                                <tr class="product-row">
                                                    <td class="ps-4">
                                                        <select name="items[{{ $index }}][product_id]" class="form-select product-select" required>
                                                            @foreach($products as $p)
                                                                <option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="variation-wrapper mt-2" style="{{ $item->variation_id ? '' : 'display:none;' }}">
                                                            <select name="items[{{ $index }}][variation_id]" class="form-select variation-select small py-1">
                                                                <option value="{{ $item->variation_id }}">{{ $item->variation->name ?? 'Default Variation' }}</option>
                                                            </select>
                                                        </div>
                                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][returned_qty]" class="form-control" min="0.01" step="0.01" value="{{ $item->returned_qty }}" required>
                                                    </td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text border-0 bg-transparent">৳</span>
                                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control border-0 bg-light unit_price" min="0" step="0.01" value="{{ $item->unit_price }}" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $index }}][reason]" class="form-control form-control-sm border-0 bg-light" value="{{ $item->reason }}" placeholder="Defect?">
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
                                <div id="emptyState" class="text-center py-5" style="display:none;">
                                    <i class="fas fa-shopping-bag fs-1 text-muted opacity-25 mb-3"></i>
                                    <p class="text-muted">No items in this return.</p>
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
        let itemIndex = {{ count($orderReturn->items) }};
        
        function toggleEmptyState() {
            if ($('#itemsTable tbody tr').length > 0) {
                $('#emptyState').hide();
            } else {
                $('#emptyState').show();
            }
        }

        $(document).ready(function() {
            $('#order_id').select2({
                placeholder: 'Search by Order #, Customer Name or Phone...',
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

            $('#customer_id').select2({ width: '100%' });
            $('.product-select').select2({ width: '100%' });

            $('#order_id').on('change select2:select', function() {
                const orderId = $(this).val();
                if (orderId) {
                    loadOrderDetails(orderId);
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
                        const data = response; 
                        
                        if (data && data.items) {
                            if (data.customer_id) {
                                const $customerSelect = $('#customer_id');
                                const option = new Option(data.customer_name || 'Customer #' + data.customer_id, data.customer_id, true, true);
                                $customerSelect.empty().append(option).trigger('change');
                            }

                            data.items.forEach((item, index) => {
                                const i = index;
                                const row = $(`
                                    <tr class="product-row">
                                        <td class="ps-4">
                                            <select name="items[${i}][product_id]" class="form-select product-select" required>
                                                <option value="${item.product_id}" selected>${item.product_name}</option>
                                            </select>
                                            <div class="variation-wrapper mt-2" style="${item.variation_id ? '' : 'display:none;'}">
                                                <select name="items[${i}][variation_id]" class="form-select variation-select small py-1">
                                                    <option value="${item.variation_id || ''}" selected>${item.variation_name || 'Standard Variation'}</option>
                                                </select>
                                            </div>
                                            <input type="hidden" name="items[${i}][order_item_id]" value="${item.id}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[${i}][returned_qty]" class="form-control" min="0.01" step="0.01" value="${item.quantity}" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text border-0 bg-transparent">৳</span>
                                                <input type="number" name="items[${i}][unit_price]" class="form-control border-0 bg-light unit_price" min="0" step="0.01" value="${item.unit_price}" required>
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
                                $tableBody.append(row);
                                row.find('.product-select').select2({ width: '100%' });
                            });
                        }
                        toggleEmptyState();
                    },
                    error: function() {
                        $tableBody.html('<tr><td colspan="5" class="text-center text-danger py-4">Failed to load order details.</td></tr>');
                    }
                });
            }

            $(document).on('click', '.btn-remove', function() {
                $(this).closest('tr').fadeOut(200, function() {
                    $(this).remove();
                    toggleEmptyState();
                });
            });

            $('#addItemRow').on('click', function() {
                const i = itemIndex++;
                const row = $(`
                    <tr class="product-row">
                        <td class="ps-4">
                            <select name="items[${i}][product_id]" class="form-select product-select" required>
                                <option value="">Select Product</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <div class="variation-wrapper mt-2" style="display:none;">
                                <select name="items[${i}][variation_id]" class="form-select variation-select small py-1"></select>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="items[${i}][returned_qty]" class="form-control" min="0.01" step="0.01" value="1" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-0 bg-transparent">৳</span>
                                <input type="number" name="items[${i}][unit_price]" class="form-control border-0 bg-light unit_price" min="0" step="0.01" required>
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
                row.find('.product-select').select2({ width: '100%' });
                toggleEmptyState();
            });

            $(document).on('change', '.product-select', function() {
                const productId = $(this).val();
                if (!productId) return;
                const $row = $(this).closest('tr');
                const $vWrapper = $row.find('.variation-wrapper');
                const $vSelect = $row.find('.variation-select');
                const $price = $row.find('.unit_price');

                $.get(`/erp/products/${productId}/variations-list`, function(vars) {
                    if (vars.length > 0) {
                        $vSelect.empty().append('<option value="">Select Variation</option>');
                        vars.forEach(v => $vSelect.append(`<option value="${v.id}" data-price="${v.price}">${v.display_name}</option>`));
                        $vWrapper.show();
                    } else {
                        $vWrapper.hide();
                        $.get(`/erp/products/${productId}/sale-price`, resp => $price.val(resp.price));
                    }
                });
            });

            $('#return_to_type').on('change', function() {
                const type = $(this).val();
                const $idSelect = $('#return_to_id');
                $idSelect.hide().empty();
                
                if (type === 'branch' || type === 'warehouse') {
                    const opts = type === 'branch' ? @json($branches) : @json($warehouses);
                    $idSelect.append('<option value="">Select Location</option>');
                    opts.forEach(o => $idSelect.append(`<option value="${o.id}">${o.name}</option>`));
                    $idSelect.show();
                    if ($idSelect.hasClass('select2-hidden-accessible')) $idSelect.select2('destroy');
                } else if (type === 'employee') {
                    $idSelect.show().select2({
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
        });
    </script>
@endsection
