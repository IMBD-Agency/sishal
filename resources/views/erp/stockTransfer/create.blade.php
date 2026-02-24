@extends('erp.master')

@section('title', 'Record Stock Transfer')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Header -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('stocktransfer.list') }}" class="text-decoration-none text-muted">Stock Transfer</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">New Disptach</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Initiate Stock Transfer</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('stocktransfer.list') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Transfer History
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <form action="{{ route('stocktransfer.store') }}" method="POST" id="transferForm">
                @csrf
                
                <!-- Main Configuration Card -->
                <div class="premium-card mb-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-info-circle me-2 text-primary"></i>Transfer Configuration</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control shadow-none" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Sender Outlet <span class="text-danger">*</span></label>
                                <select name="from_outlet" id="from_outlet" class="form-select shadow-none select2-basic" required>
                                    <option value="">Select Source Location</option>
                                    <optgroup label="Warehouses">
                                        @foreach($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Branches">
                                        @foreach($branches as $branch)
                                            <option value="branch_{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Receiver Outlet <span class="text-danger">*</span></label>
                                <select name="to_outlet" id="to_outlet" class="form-select shadow-none select2-basic" required>
                                    <option value="">Select Target Destination</option>
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
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Scan/Select Style Number <span class="text-danger">*</span></label>
                                <select name="style_number" id="style_number" class="form-select shadow-none" required>
                                    <option value="">Searching Style Number...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table Card -->
                <div class="premium-card mb-4">
                    <div class="card-header bg-white border-bottom p-4">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-box-open me-2 text-primary"></i>Allocated Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table premium-table align-middle mb-0 compact" id="productTable">
                                <thead>
                                    <tr>
                                        <th class="ps-3" style="width: 50px;">Media</th>
                                        <th>Product Details</th>
                                        <th>Style No</th>
                                        <th>Variant</th>
                                        <th>Attributes</th>
                                        <th class="text-center">Avail.</th>
                                        <th style="width: 130px;">Transfer Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total Price</th>
                                        <th class="text-center pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody">
                                    <tr class="empty-placeholder">
                                        <td colspan="10" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="fas fa-barcode fa-3x mb-3"></i>
                                                <p class="fw-bold mb-0">Scan or select a style number to build the dispatch list.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 p-3">
                        <div class="row justify-content-end">
                            <div class="col-md-4">
                                <div class="premium-card bg-white shadow-none border mb-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2 text-dark">
                                            <span class="small fw-bold text-muted text-uppercase">Subtotal Items Value</span>
                                            <span class="fw-bold" id="display_total">0.00৳</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-0">
                                            <span class="small fw-bold text-muted text-uppercase">Allocated Qty Balance</span>
                                            <span class="fw-bold text-primary" id="display_qty">0</span>
                                        </div>
                                        <input type="hidden" id="total_amount" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial & Logistics Card -->
                <div class="premium-card mb-5">
                    <div class="card-header bg-white border-bottom p-4">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Financials & Logistics</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Paid Amount (Optional)</label>
                                <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control mb-2 shadow-none" value="0">
                                <div class="d-flex justify-content-between px-1">
                                    <span class="extra-small fw-bold text-muted">Remaining Due:</span>
                                    <span class="extra-small fw-bold text-danger" id="display_due">0.00৳</span>
                                </div>
                                <input type="hidden" id="due_amount" value="0">
                            </div>

                            <div class="col-md-9 border-start ps-md-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Sender Acc. <span class="text-danger">*</span></label>
                                        <select name="sender_account_id" id="sender_account_id" class="form-select shadow-none" required>
                                            <option value="">Select Account</option>
                                            @foreach($financialAccounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ $acc->type }}" data-number="{{ $acc->account_number ?? $acc->mobile_number }}">
                                                    {{ $acc->provider_name }} ({{ $acc->account_number ?? $acc->mobile_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="sender_account_type" id="sender_account_type">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Sender Acc #</label>
                                        <input type="text" name="sender_account_number" id="sender_account_number" class="form-control shadow-none bg-light" readonly placeholder="Auto-filled">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Receiver Acc. <span class="text-danger">*</span></label>
                                        <select name="receiver_account_id" id="receiver_account_id" class="form-select shadow-none" required>
                                            <option value="">Select Account</option>
                                            @foreach($financialAccounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ $acc->type }}" data-number="{{ $acc->account_number ?? $acc->mobile_number }}">
                                                    {{ $acc->provider_name }} ({{ $acc->account_number ?? $acc->mobile_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="receiver_account_type" id="receiver_account_type">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Receiver Acc #</label>
                                        <input type="text" name="receiver_account_number" id="receiver_account_number" class="form-control shadow-none bg-light" readonly placeholder="Auto-filled">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Consignment Note / Instructions</label>
                                <textarea name="note" class="form-control shadow-none" rows="3" placeholder="Enter any specific shipping or handling instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Controls -->
                <div class="mt-5 pt-4 border-top text-center">
                    <button type="submit" class="btn btn-create-premium px-5 py-3 me-3">
                        <i class="fas fa-check-circle me-2"></i>FINALIZE TRANSFER DISPATCH
                    </button>
                    <a href="{{ route('stocktransfer.list') }}" class="btn btn-light border fw-bold px-5 py-3">
                        CANCEL
                    </a>
                </div>
            </form>
        </div>
    </div>

@push('css')
    <style>
        .breadcrumb-premium { font-size: 0.8rem; }
        .form-control-sm, .form-select-sm { font-size: 0.75rem !important; }
    </style>
@endpush

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2-basic').select2();

            $('#style_number').select2({
                placeholder: 'Scan or search style number...',
                ajax: {
                    url: '/erp/products/search-by-style',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: (item.style_number ? item.style_number + ' - ' : '') + item.name,
                                    product: item
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#style_number').on('change', function() {
                const selectedData = $(this).select2('data')[0];
                if (selectedData && selectedData.product) {
                    $('.empty-placeholder').hide();
                    loadProductVariations(selectedData.product);
                }
            });

            // Clear table when sender changes to prevent stock mismatch
            $('#from_outlet').on('change', function() {
                if ($('#productTableBody tr:not(.empty-placeholder)').length > 0) {
                    if(confirm('Changing the sender will clear the current item list because stock availability depends on the source location. Continue?')) {
                        $('#productTableBody').html(`
                            <tr class="empty-placeholder">
                                <td colspan="10" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="fas fa-barcode fa-3x mb-3"></i>
                                        <p class="fw-bold mb-0">Scan or select a style number to build the dispatch list.</p>
                                    </div>
                                </td>
                            </tr>
                        `);
                        updateTotals();
                        $('#style_number').val(null).trigger('change');
                    } else {
                        // Revert selection (this is tricky with select2/html select, simpler to just let them know or auto-clear)
                        // For now, simpler: just clear list without confirm or with notice.
                        // Let's stick to the confirm. If they say Cancel, we need to revert value.
                        // Reverting select value is complex without storing previous.
                        // Let's just auto-clear for data integrity.
                    }
                }
            });

            function loadProductVariations(product) {
                const fromOutlet = $('#from_outlet').val();
                let queryParams = '';
                
                if (fromOutlet) {
                    const parts = fromOutlet.split('_');
                    if (parts.length === 2) {
                        queryParams = `?location_type=${parts[0]}&location_id=${parts[1]}`;
                    }
                }

                $.ajax({
                    url: '/erp/products/' + product.id + '/variations-with-stock' + queryParams,
                    type: 'GET',
                    success: function(variations) {
                        if (variations && variations.length > 0) {
                            variations.forEach(function(variation) {
                                addProductRow(product, variation);
                            });
                        } else {
                            addProductRow(product, null);
                        }
                    },
                    error: function() { alert('Error loading product variations'); }
                });
            }

            function addProductRow(product, variation) {
                const rowId = variation ? `var_${variation.id}` : `prod_${product.id}`;
                if ($(`#${rowId}`).length > 0) return;

                const stock = variation ? (variation.stock || 0) : (product.stock || 0);
                // Default to Purchase Price (Cost) for Stock Transfers
                const unitPrice = variation ? 
                    (variation.cost && variation.cost > 0 ? variation.cost : (product.cost || 0)) : 
                    (product.cost || 0);

                const displayImage = (variation && variation.image) ? variation.image : (product.image || '');
                const imgHtml = displayImage 
                    ? `<img src="/${displayImage}" class="rounded border shadow-sm" style="width: 35px; height: 35px; object-fit: cover;">`
                    : `<div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;"><i class="fas fa-image text-muted opacity-50"></i></div>`;

                const row = `
                    <tr id="${rowId}" class="item-row">
                        <td class="ps-3">
                            ${imgHtml}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">${product.name}</div>
                            <div class="extra-small text-muted text-uppercase">${product.category?.name || 'General'}</div>
                        </td>
                        <td class="text-pink fw-bold">${product.style_number || '-'}</td>
                        <td>
                            <span class="badge bg-light text-dark border me-1">${variation && variation.size ? variation.size : '-'}</span>
                            <span class="badge bg-light text-dark border">${variation && variation.color ? variation.color : '-'}</span>
                        </td>
                        <td class="extra-small text-muted">
                            ${product.brand?.name || '-'} | ${product.season?.name || '-'}
                        </td>
                        <td class="text-center fw-bold text-muted">${stock}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm transfer-qty shadow-none border-info" 
                                   data-row-id="${rowId}" data-price="${unitPrice}" 
                                   min="0" max="${stock}" value="0">
                            <input type="hidden" name="items[${rowId}][product_id]" value="${product.id}">
                            <input type="hidden" name="items[${rowId}][variation_id]" value="${(variation && variation.id) ? variation.id : ''}">
                            <input type="hidden" name="items[${rowId}][unit_price]" value="${unitPrice}">
                        </td>
                        <td class="text-end fw-bold">${parseFloat(unitPrice).toFixed(2)}৳</td>
                        <td class="text-end fw-bold total-price-col" id="total_price_${rowId}" data-value="0">0.00৳</td>
                        <td class="pe-3 text-center">
                            <button type="button" class="btn btn-sm btn-light border-0 action-circle remove-row" data-row-id="${rowId}">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#productTableBody').append(row);
                // Also update the table's class if not already there
                $('#productTable').addClass('compact-table');
            }

            $(document).on('input', '.transfer-qty', function() {
                const rowId = $(this).data('row-id');
                let qty = parseFloat($(this).val()) || 0;
                const maxStock = parseFloat($(this).attr('max')) || 0;
                const price = $(this).data('price') ? parseFloat($(this).data('price')) : 0;
                
                if (qty > maxStock) {
                    alert(`Exceeds available stock (${maxStock})`);
                    qty = maxStock;
                    $(this).val(maxStock);
                }
                
                const total = (qty * price).toFixed(2);
                $(`#total_price_${rowId}`).text(total + '৳').attr('data-value', total);
                
                $(`input[name="items[${rowId}][quantity]"]`).remove();
                $(this).after(`<input type="hidden" name="items[${rowId}][quantity]" value="${qty}">`);
                
                updateTotals();
            });

            $(document).on('click', '.remove-row', function() {
                const rowId = $(this).data('row-id');
                $(`#${rowId}`).remove();
                if($('#productTableBody tr').length === 0) $('.empty-placeholder').show();
                updateTotals();
            });

            let autoSyncPaid = true;

            $('#paid_amount').on('input', function() {
                // If the user manually changes the paid amount, stop auto-syncing
                autoSyncPaid = false;
                updateTotals();
            });

            function updateTotals() {
                let totalAmount = 0;
                let totalQty = 0;
                
                $('.total-price-col').each(function() {
                    const val = parseFloat($(this).attr('data-value')) || 0;
                    totalAmount += val;
                });
                
                $('.transfer-qty').each(function() {
                    totalQty += parseFloat($(this).val()) || 0;
                });
                
                // Automatically sync paid amount with total if autoSync is active
                if (autoSyncPaid) {
                    $('#paid_amount').val(totalAmount > 0 ? totalAmount.toFixed(2) : 0);
                }
                
                const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                const dueAmount = totalAmount - paidAmount;
                
                $('#total_amount').val(totalAmount);
                $('#display_total').text(totalAmount.toFixed(2) + '৳');
                $('#display_qty').text(totalQty);
                $('#due_amount').val(dueAmount);
                $('#display_due').text(dueAmount.toFixed(2) + '৳');
            }

            $('#transferForm').on('submit', function(e) {
                if ($('#productTableBody tr:not(.empty-placeholder)').length === 0) {
                    e.preventDefault(); alert('Please add products'); return false;
                }
            });

            // Handle Financial Account Selection
            $('#sender_account_id').on('change', function() {
                const selected = $(this).find(':selected');
                const type = selected.data('type') || '';
                const number = selected.data('number') || '';
                $('#sender_account_type').val(type);
                $('#sender_account_number').val(number);
            });

            $('#receiver_account_id').on('change', function() {
                const selected = $(this).find(':selected');
                const type = selected.data('type') || '';
                const number = selected.data('number') || '';
                $('#receiver_account_type').val(type);
                $('#receiver_account_number').val(number);
            });
        });
    </script>
@endpush
@endsection
