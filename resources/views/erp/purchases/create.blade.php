@extends('erp.master')

@section('title', 'Purchase Management')

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
            .input-group-text { background-color: #f8f9fa; border-color: #e9ecef; color: #6c757d; padding: 0.6rem 0.85rem; }
            
            #itemsTable thead th { background-color: #f8f9fa; font-weight: 600; color: #495057; border-bottom: 2px solid #e9ecef; font-size: 0.85rem; text-transform: uppercase; padding: 1rem 0.75rem; }
            #itemsTable tbody td { padding: 1rem 0.75rem; border-color: #f1f3f5; }
            .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
            
            .sticky-summary { position: sticky; bottom: 0; background: white; border-top: 1px solid #eee; padding: 1.5rem; z-index: 100; box-shadow: 0 -10px 20px rgba(0,0,0,0.02); }
            
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
                            <li class="breadcrumb-item active" aria-current="page">New Purchase</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Create New Purchase</h2>
                    <p class="text-muted mb-0">Purchase inventory items for branches or warehouses.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('purchase.list') }}" class="btn btn-light border px-4 rounded-3 text-muted">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 pb-5">
            <form id="purchaseForm" action="{{ route('purchase.store') }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <!-- Left Sidebar: Assignment Details -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <div class="form-section-title">
                                    <i class="fas fa-info-circle me-2"></i>Purchase Info
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-muted text-uppercase">Ship To Location Type</label>
                                    <select name="ship_location_type" id="ship_location_type" class="form-select border-2 rounded-3" required>
                                        <option value="">Select Type</option>
                                        <option value="branch">Branch</option>
                                        <option value="warehouse">Warehouse</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="location_id" class="form-label fw-semibold small text-muted text-uppercase">Destination Location</label>
                                    <select name="location_id" id="location_id" class="form-select border-2 rounded-3" required>
                                        <option value="">Select Type First</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="purchase_date" class="form-label fw-semibold small text-muted text-uppercase">Purchase Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                        <input type="date" name="purchase_date" id="purchase_date" class="form-control border-2 rounded-end-3" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                
                                <div class="mb-0">
                                    <label for="notes" class="form-label fw-semibold small text-muted text-uppercase">Purchase Notes</label>
                                    <textarea name="notes" id="notes" class="form-control border-2 rounded-3" rows="4" placeholder="Any specific instructions or remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Item Management -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="card-body p-0">
                                <div class="p-4 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0">Select Items to Purchase</h5>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-light border btn-sm px-3 rounded-pill" id="addItemRow">
                                                <i class="fas fa-plus me-1"></i>Add Row
                                            </button>
                                            <button type="button" class="btn btn-light border btn-sm px-3 rounded-pill" id="addMultipleRows">
                                                <i class="fas fa-layer-group me-1"></i>Add 5 Rows
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
                                            <!-- Initial Row -->
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[0][product_id]" class="form-select product-select" required></select>
                                                    <select name="items[0][variation_id]" class="form-select mt-2 variation-select d-none"></select>
                                                    <div class="small mt-2 stock-indicator"></div>
                                                    <div class="mt-2">
                                                        <textarea class="form-control description x-small border-dashed" name="items[0][description]" rows="1" placeholder="Add specific details for this item..."></textarea>
                                                    </div>
                                                </td>
                                                <td class="align-top">
                                                    <input type="number" name="items[0][quantity]" class="form-control quantity fw-bold border-2" min="0.01" step="0.01" required>
                                                </td>
                                                <td class="align-top">
                                                    <div class="input-group">
                                                        <span class="input-group-text px-2 small">৳</span>
                                                        <input type="number" name="items[0][unit_price]" class="form-control unit_price border-2" min="0" step="0.01" required>
                                                    </div>
                                                </td>
                                                <td class="text-end align-top pt-3 fw-bold">
                                                    ৳<span class="item-total">0.00</span>
                                                </td>
                                                <td class="text-center align-top pt-3">
                                                    <div class="d-flex flex-column gap-2 align-items-center">
                                                        <button type="button" class="btn btn-light btn-action duplicate-row border shadow-sm" title="Duplicate row">
                                                            <i class="fas fa-copy text-primary small"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-light btn-action remove-row border shadow-sm" disabled title="Remove row">
                                                            <i class="fas fa-trash text-danger small"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Summary and Submit -->
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="alert alert-info border-0 bg-primary-soft rounded-4 mb-md-0">
                                            <div class="d-flex">
                                                <i class="fas fa-info-circle mt-1 me-3 fs-4"></i>
                                                <div>
                                                    <h6 class="fw-bold mb-1">Stock Notice</h6>
                                                    <small>Available stock levels shown are from central warehouses. Items will be deducted from central stock and added to destination when status is set to 'Received'.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-4 p-4 text-end">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted fw-semibold uppercase small">Subtotal</span>
                                                <span class="fw-bold">৳<span id="subtotalCell">0.00</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                                <span class="fw-bold fs-5">Grand Total</span>
                                                <span class="fw-bold fs-5 text-primary">৳<span id="grandTotalCell">0.00</span></span>
                                            </div>
                                            <hr class="my-3">
                                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow fw-bold">
                                                <i class="fas fa-check-circle me-2"></i>Complete Purchase
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

    <!-- Select2 and Product Logic -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Copy most of the previous logic but refine UI interactions
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
                            text: item.name + (item.sku ? ` (${item.sku})` : ''),
                            has_variations: item.has_variations
                        }))
                    }),
                    cache: true
                }
            });
        }

        $(document).ready(function() {
            initProductSelect2('.product-select');
            $(document).on('change', '.product-select', function() { handleProductChange(this); });
            $(document).on('select2:clear', '.product-select', function() { handleProductChange(this); });
        });

        let itemIndex = 1;

        function addItemRow() {
            const tbody = $('#itemsTable tbody');
            const row = `
                <tr class="item-row">
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-select product-select" required></select>
                        <select name="items[${itemIndex}][variation_id]" class="form-select mt-2 variation-select d-none"></select>
                        <div class="small mt-2 stock-indicator"></div>
                        <div class="mt-2">
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
                            <button type="button" class="btn btn-light btn-action duplicate-row border shadow-sm" title="Duplicate row">
                                <i class="fas fa-copy text-primary small"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-action remove-row border shadow-sm" title="Remove row">
                                <i class="fas fa-trash text-danger small"></i>
                            </button>
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
        $('#addMultipleRows').on('click', () => { for(let i=0; i<5; i++) addItemRow(); });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateTotals();
            updateRemoveButtons();
        });

        $(document).on('click', '.duplicate-row', function() {
            const row = $(this).closest('tr');
            const productId = row.find('.product-select').val();
            const productName = row.find('.product-select option:selected').text();
            const variationId = row.find('.variation-select').val();
            const quantity = row.find('.quantity').val();
            const unitPrice = row.find('.unit_price').val();
            const description = row.find('.description').val();
            
            addItemRow();
            const newRow = $('#itemsTable tbody tr').last();
            
            if (productId) {
                const option = new Option(productName, productId, true, true);
                newRow.find('.product-select').append(option).trigger('change');
                setTimeout(() => {
                    if (variationId) newRow.find('.variation-select').val(variationId).trigger('change');
                    newRow.find('.quantity').val(quantity);
                    newRow.find('.unit_price').val(unitPrice);
                    newRow.find('.description').val(description);
                    updateTotals();
                }, 800);
            } else {
                 newRow.find('.quantity').val(quantity);
                 newRow.find('.unit_price').val(unitPrice);
                 newRow.find('.description').val(description);
                 updateTotals();
            }
        });

        function updateRemoveButtons() {
            $('.remove-row').prop('disabled', $('.item-row').length <= 1);
        }

        const branches = @json($branches);
        const warehouses = @json($warehouses);

        document.getElementById('ship_location_type').addEventListener('change', function() {
            const select = document.getElementById('location_id');
            select.innerHTML = '<option value="">Select Location</option>';
            const type = this.value;
            const data = type === 'branch' ? branches : (type === 'warehouse' ? warehouses : []);
            data.forEach(loc => select.innerHTML += `<option value="${loc.id}">${loc.name}</option>`);
        });

        function handleProductChange(selectEl) {
            const $select = $(selectEl);
            const productId = $select.val();
            const row = $select.closest('tr')[0];
            const unitPriceInput = row.querySelector('.unit_price');
            const stockIndicator = row.querySelector('.stock-indicator');
            const variationSelect = row.querySelector('.variation-select');

            if (!productId) {
                unitPriceInput.value = '';
                stockIndicator.textContent = '';
                $(variationSelect).addClass('d-none').empty().removeAttr('required');
                updateTotals();
                return;
            }

            $(variationSelect).empty().addClass('d-none').removeAttr('required');
            unitPriceInput.value = '';

            $.get('{{ url('/erp/products') }}/' + productId + '/variations-list', (vars) => {
                if (vars && vars.length > 0) {
                    $(variationSelect).removeClass('d-none').attr('required', 'required');
                    let html = '<option value="">Select Variation</option>';
                    vars.forEach(v => {
                        html += `<option value="${v.id}" data-price="${v.price || ''}">${v.display_name || v.name} ${v.sku ? ' ('+v.sku+')' : ''}</option>`;
                    });
                    variationSelect.innerHTML = html;
                    stockIndicator.textContent = 'Select variation for stock info';
                    stockIndicator.className = 'small mt-1 stock-indicator text-muted italic';
                } else {
                    $.get('{{ url('/erp/products') }}/' + productId + '/price', (resp) => {
                        if (resp && resp.price) unitPriceInput.value = parseFloat(resp.price).toFixed(2);
                        updateTotals();
                    });
                    checkGenericStock(productId, row, $select);
                }
            });
        }

        function checkGenericStock(productId, row, $select) {
            const stockIndicator = row.querySelector('.stock-indicator');
            $.get('{{ url('/erp/order/product-stocks') }}/' + productId, (resp) => {
                const total = (resp?.stocks || []).filter(s => s.type === 'warehouse').reduce((sum, s) => sum + parseFloat(s.quantity), 0);
                if (total <= 0) {
                    alert('No warehouse stock available.');
                    $select.val(null).trigger('change');
                    return;
                }
                const qtyInput = row.querySelector('.quantity');
                qtyInput.setAttribute('max', total);
                qtyInput.dataset.maxStock = total;
                stockIndicator.textContent = `Warehouse Stock: ${total}`;
                stockIndicator.className = 'small mt-1 stock-indicator text-info fw-semibold';
            });
        }

        $(document).on('change', '.variation-select', function() {
            const row = this.closest('tr');
            const opted = this.options[this.selectedIndex];
            const price = opted.dataset.price;
            if (price) {
                row.querySelector('.unit_price').value = parseFloat(price).toFixed(2);
                updateTotals();
            }
            
            const productSelect = row.querySelector('.product-select');
            const productId = $(productSelect).val();
            const variationId = this.value;

            if (productId && variationId) {
                $.get(`{{ url('/erp/products') }}/${productId}/variations/${variationId}/stock/levels`, (resp) => {
                    const total = (resp?.warehouse_stocks || []).reduce((sum, s) => sum + parseFloat(s.quantity), 0);
                    const indicator = row.querySelector('.stock-indicator');
                    if (total <= 0) {
                        alert('Variation out of stock in warehouses.');
                        $(this).val('').trigger('change');
                        indicator.textContent = 'No Stock (Warehouse)';
                        indicator.className = 'small mt-1 stock-indicator text-danger fw-bold';
                        return;
                    }
                    const qtyInput = row.querySelector('.quantity');
                    qtyInput.setAttribute('max', total);
                    qtyInput.dataset.maxStock = total;
                    indicator.textContent = `Warehouse Stock: ${total}`;
                    indicator.className = 'small mt-1 stock-indicator text-info fw-semibold';
                });
            }
        });

        function updateTotals() {
            let subtotal = 0;
            $('.item-row').each(function() {
                const qty = parseFloat($(this).find('.quantity').val()) || 0;
                const price = parseFloat($(this).find('.unit_price').val()) || 0;
                const total = qty * price;
                $(this).find('.item-total').text(total.toFixed(2));
                subtotal += total;
            });
            $('#subtotalCell, #grandTotalCell').text(subtotal.toFixed(2));
        }

        $(document).on('input', '.quantity, .unit_price', function() {
            if ($(this).hasClass('quantity')) {
                const qty = parseFloat($(this).val());
                const max = parseFloat($(this).data('maxStock'));
                if (qty > max) {
                    alert(`Stock exceeded! Max available: ${max}`);
                    $(this).val(max);
                }
            }
            updateTotals();
        });
    </script>
@endsection