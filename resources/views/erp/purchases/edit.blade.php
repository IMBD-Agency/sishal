@extends('erp.master')

@section('title', 'Edit Purchase')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .form-section-title { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6c757d; margin-bottom: 1.5rem; display: flex; align-items: center; }
            .form-section-title::after { content: ""; flex: 1; height: 1px; background: #eee; margin-left: 1rem; }
            .bg-primary-soft { background-color: rgba(13, 110, 253, 0.05); }
            .form-control, .form-select { border-color: #e9ecef; padding: 0.6rem 0.85rem; }
            .form-control:focus, .form-select:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.05); border-color: #0d6efd; }
            
            #itemsTable thead th { background-color: #f8f9fa; font-weight: 600; color: #495057; border-bottom: 2px solid #e9ecef; font-size: 0.85rem; text-transform: uppercase; padding: 1rem 0.75rem; }
            #itemsTable tbody td { padding: 1rem 0.75rem; border-color: #f1f3f5; }
            .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
            
            .select2-container--default .select2-selection--single { height: 42px; border: 1px solid #e9ecef; border-radius: 0.375rem; }
            .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-left: 12px; }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        </style>

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('purchase.list') }}" class="text-decoration-none">Purchase</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Purchase</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Edit Purchase #{{ $purchase->id }}</h2>
                    <p class="text-muted mb-0">Update inventory purchase details or status.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('purchase.list') }}" class="btn btn-light border px-4 rounded-3 text-muted">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 pb-5">
            <form id="purchaseForm" action="{{ route('purchase.update', $purchase->id) }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <!-- Left Sidebar -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="form-section-title"><i class="fas fa-info-circle me-2"></i>Purchase Info</div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-muted text-uppercase">Target Branch (Delivery)</label>
                                    <input type="hidden" name="ship_location_type" value="branch">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="location_id" class="form-label fw-semibold small text-muted text-uppercase">Destination Branch</label>
                                    <select name="location_id" id="location_id" class="form-select border-2 rounded-3" required>
                                        @foreach($branches as $loc)
                                            <option value="{{ $loc->id }}" {{ $purchase->location_id == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="purchase_date" class="form-label fw-semibold small text-muted text-uppercase">Purchase Date</label>
                                    <input type="date" name="purchase_date" id="purchase_date" class="form-control border-2 rounded-3" value="{{ $purchase->purchase_date }}" required>
                                </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label fw-semibold small text-muted text-uppercase">Status</label>
                                    <select name="status" id="status" class="form-select border-2 rounded-3 shadow-sm {{ $purchase->status == 'received' ? 'bg-light' : '' }}" {{ $purchase->status == 'received' ? 'disabled' : '' }}>
                                        <option value="pending" {{ $purchase->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="received" {{ $purchase->status == 'received' ? 'selected' : '' }}>Received</option>
                                        <option value="cancelled" {{ $purchase->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @if($purchase->status == 'received')
                                        <small class="text-info mt-1 d-block"><i class="fas fa-lock me-1"></i> Stock already updated. Status cannot be changed.</small>
                                        <input type="hidden" name="status" value="received">
                                    @endif
                                </div>
                                
                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-semibold small text-muted text-uppercase">Purchase Notes</label>
                                    <textarea name="notes" id="notes" class="form-control border-2 rounded-3" rows="4">{{ $purchase->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="card-body p-0">
                                <div class="p-4 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">Assigned Items</h5>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light border btn-sm px-3 rounded-pill" id="addItemRow" {{ $purchase->status == 'received' ? 'disabled' : '' }}>
                                                <i class="fas fa-plus me-1"></i>Add Row
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th width="45%">Product Selection</th>
                                                <th width="15%">Quantity</th>
                                                <th width="18%">Unit Price</th>
                                                <th width="15%" class="text-end">Line Total</th>
                                                <th width="7%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($purchase->items as $i => $item)
                                                <tr class="item-row">
                                                    <td>
                                                        <select name="items[{{ $i }}][product_id]" class="form-select product-select" required 
                                                                data-selected-id="{{ $item->product_id }}" 
                                                                data-selected-text="{{ $item->product->name ?? '' }}"
                                                                {{ $purchase->status == 'received' ? 'disabled' : '' }}></select>
                                                        
                                                        @if($purchase->status == 'received')
                                                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                                                        @endif

                                                        <select name="items[{{ $i }}][variation_id]" class="form-select mt-2 variation-select {{ $item->variation_id ? '' : 'd-none' }}"
                                                                data-initial-id="{{ $item->variation_id }}"
                                                                {{ $purchase->status == 'received' ? 'disabled' : '' }}></select>
                                                        
                                                        @if($purchase->status == 'received' && $item->variation_id)
                                                            <input type="hidden" name="items[{{ $i }}][variation_id]" value="{{ $item->variation_id }}">
                                                        @endif

                                                        <div class="small mt-2 stock-indicator"></div>
                                                        <div class="mt-2">
                                                            <textarea class="form-control description x-small border-dashed" name="items[{{ $i }}][description]" rows="1" placeholder="Description..." {{ $purchase->status == 'received' ? 'readonly' : '' }}>{{ $item->description }}</textarea>
                                                        </div>
                                                    </td>
                                                    <td class="align-top">
                                                        <input type="number" name="items[{{ $i }}][quantity]" class="form-control quantity fw-bold border-2" min="0.01" step="0.01" value="{{ $item->quantity }}" required {{ $purchase->status == 'received' ? 'readonly' : '' }}>
                                                    </td>
                                                    <td class="align-top">
                                                        <div class="input-group">
                                                            <span class="input-group-text px-2 small">৳</span>
                                                            <input type="number" name="items[{{ $i }}][unit_price]" class="form-control unit_price border-2" min="0" step="0.01" value="{{ $item->unit_price }}" required {{ $purchase->status == 'received' ? 'readonly' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td class="text-end align-top pt-3 fw-bold">
                                                        ৳<span class="item-total">{{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                                                    </td>
                                                    <td class="text-center align-top pt-3">
                                                        <div class="d-flex flex-column gap-2 align-items-center">
                                                            <button type="button" class="btn btn-light btn-action duplicate-row border shadow-sm" title="Duplicate" {{ $purchase->status == 'received' ? 'disabled' : '' }}>
                                                                <i class="fas fa-copy text-primary small"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-light btn-action remove-row border shadow-sm" title="Remove" {{ $purchase->status == 'received' ? 'disabled' : '' }}>
                                                                <i class="fas fa-trash text-danger small"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="alert alert-info border-0 bg-primary-soft rounded-4 mb-md-0">
                                            <div class="d-flex small text-muted">
                                                <i class="fas fa-info-circle mt-1 me-3 fs-5"></i>
                                                <span>You are editing an existing assignment. If you change products or quantities, ensure the stock is correctly balanced if already 'Received'.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-4 p-4 text-end">
                                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                                <span class="text-muted fw-semibold uppercase small">Subtotal</span>
                                                <span class="fw-bold">৳<span id="subtotalCell">0.00</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                                <span class="text-muted fw-semibold uppercase small">Discount</span>
                                                <div class="input-group input-group-sm w-50 ms-auto">
                                                    <input type="number" name="discount_value" id="discount_value" class="form-control text-end fw-bold" value="{{ $purchase->bill->discount_value ?? 0 }}" step="0.01" {{ $purchase->status == 'received' ? 'readonly' : '' }}>
                                                    <div class="btn-group btn-group-sm ms-1" role="group">
                                                        <input type="radio" class="btn-check" name="discount_type" id="discount_flat" value="flat" {{ ($purchase->bill->discount_type ?? 'flat') == 'flat' ? 'checked' : '' }} {{ $purchase->status == 'received' ? 'disabled' : '' }}>
                                                        <label class="btn btn-outline-secondary" for="discount_flat">৳</label>
                                                        
                                                        <input type="radio" class="btn-check" name="discount_type" id="discount_percent" value="percent" {{ ($purchase->bill->discount_type ?? '') == 'percent' ? 'checked' : '' }} {{ $purchase->status == 'received' ? 'disabled' : '' }}>
                                                        <label class="btn btn-outline-secondary" for="discount_percent">%</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between border-top pt-2 mt-2 align-items-center">
                                                <span class="fw-bold fs-5">Grand Total</span>
                                                <span class="fw-bold fs-5 text-primary">৳<span id="grandTotalCell">0.00</span></span>
                                                <input type="hidden" name="total_amount" id="total_amount" value="{{ $purchase->bill->total_amount ?? 0 }}">
                                            </div>
                                            <hr class="my-3">
                                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow fw-bold">
                                                <i class="fas fa-save me-2"></i>Update Purchase
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        function initProductSelect2(selector) {
            $(selector).select2({
                placeholder: 'Search product...',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: '{{ route('products.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({ q: params.term }),
                    processResults: (data) => ({
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name + (item.sku ? ` (${item.sku})` : '')
                        }))
                    }),
                    cache: true
                }
            });
        }

        $(document).ready(function() {
            $('.product-select').each(function() {
                const $s = $(this);
                initProductSelect2($s);
                if ($s.data('selected-id')) {
                    const opt = new Option($s.data('selected-text'), $s.data('selected-id'), true, true);
                    $s.append(opt).trigger('change');
                    // Special logic for initial variation loading if needed
                }
            });
            $(document).on('change', '.product-select', function() { handleProductChange(this); });
            updateTotals();
        });

        // ... Add Item Row and other logic similar to create.blade.php ...
        let itemIndex = {{ count($purchase->items) }};
        function addItemRow() {
            const tbody = $('#itemsTable tbody');
            const row = `
                <tr class="item-row">
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-select product-select" required></select>
                        <select name="items[${itemIndex}][variation_id]" class="form-select mt-2 variation-select d-none"></select>
                        <div class="small mt-2 stock-indicator"></div>
                        <div class="mt-2 text-center">
                            <textarea class="form-control description x-small border-dashed" name="items[${itemIndex}][description]" rows="1" placeholder="Description..."></textarea>
                        </div>
                    </td>
                    <td class="align-top">
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity fw-bold border-2" min="0.01" step="0.01" required>
                    </td>
                    <td class="align-top">
                        <div class="input-group">
                            <span class="input-group-text px-2 small">৳</span>
                            <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit_price border-2" min="0" step="0.01" required>
                        </div>
                    </td>
                    <td class="text-end align-top pt-3 fw-bold">
                        ৳<span class="item-total">0.00</span>
                    </td>
                    <td class="text-center align-top pt-3">
                        <div class="d-flex flex-column gap-2 align-items-center">
                            <button type="button" class="btn btn-light btn-action duplicate-row border shadow-sm"><i class="fas fa-copy text-primary small"></i></button>
                            <button type="button" class="btn btn-light btn-action remove-row border shadow-sm"><i class="fas fa-trash text-danger small"></i></button>
                        </div>
                    </td>
                </tr>
            `;
            const $row = $(row);
            tbody.append($row);
            initProductSelect2($row.find('.product-select'));
            itemIndex++;
            updateRemoveButtons();
        }

        $('#addItemRow').on('click', addItemRow);
        $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); updateTotals(); updateRemoveButtons(); });

        const branches = @json($branches);
        const warehouses = @json($warehouses);
        // ship_location_type is now forced to 'branch'
        /*
        document.getElementById('ship_location_type').addEventListener('change', function() {
            const select = document.getElementById('location_id');
            select.innerHTML = '<option value="">Select Location</option>';
            const data = this.value === 'branch' ? branches : (this.value === 'warehouse' ? warehouses : []);
            data.forEach(loc => select.innerHTML += `<option value="${loc.id}">${loc.name}</option>`);
        });
        */

        function handleProductChange(selectEl) {
            const $s = $(selectEl);
            const pid = $s.val();
            const row = $s.closest('tr')[0];
            if(!pid) return;

            $.get('{{ url('/erp/products') }}/' + pid + '/variations-list', (vars) => {
                const vs = row.querySelector('.variation-select');
                if (vars && vars.length > 0) {
                    $(vs).removeClass('d-none').attr('required', 'required').empty().append('<option value="">Select Variation</option>');
                    vars.forEach(v => $(vs).append(`<option value="${v.id}" data-price="${v.price || ''}">${v.display_name || v.name}</option>`));
                    // If it was already set (on initial load)
                    const initialId = $(vs).data('initial-id');
                    if(initialId) $(vs).val(initialId).trigger('change').data('initial-id', null);
                } else {
                    $(vs).addClass('d-none').removeAttr('required').empty();
                    $.get('{{ url('/erp/products') }}/' + pid + '/price', (r) => {
                        const up = row.querySelector('.unit_price');
                        if(r && r.price && !up.value) up.value = r.price;
                        updateTotals();
                    });
                }
            });
        }

        $(document).on('change', '.variation-select', function() {
            const opted = this.options[this.selectedIndex];
            if(opted.dataset.price) {
                this.closest('tr').querySelector('.unit_price').value = opted.dataset.price;
                updateTotals();
            }
        });

        function updateTotals() {
            let subtotal = 0;
            $('.item-row').each(function() {
                const q = parseFloat($(this).find('.quantity').val()) || 0;
                const p = parseFloat($(this).find('.unit_price').val()) || 0;
                const t = q * p;
                $(this).find('.item-total').text(t.toFixed(2));
                subtotal += t;
            });
            $('#subtotalCell').text(subtotal.toFixed(2));

            const discountVal = parseFloat($('#discount_value').val()) || 0;
            const discountType = $('input[name="discount_type"]:checked').val();
            let discountAmount = 0;

            if (discountType === 'percent') {
                discountAmount = (subtotal * discountVal) / 100;
            } else {
                discountAmount = discountVal;
            }

            const grandTotal = Math.max(0, subtotal - discountAmount);
            $('#grandTotalCell').text(grandTotal.toFixed(2));
            $('#total_amount').val(grandTotal.toFixed(2));
        }

        $(document).on('input change', '.quantity, .unit_price, #discount_value, input[name="discount_type"]', updateTotals);
        function updateRemoveButtons() { $('.remove-row').prop('disabled', $('.item-row').length <= 1); }
    </script>
@endsection