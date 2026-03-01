@extends('erp.master')

@section('title', 'Create Purchase Return')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .premium-card { border-radius: 16px; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.02); background: #fff; overflow: hidden; transition: all 0.3s ease; }
            .card-header-premium { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 1.5rem; }
            .form-label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 0.5rem; }
            .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 0.75rem 1rem; font-size: 0.95rem; transition: all 0.2s; }
            .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08); }
            
            .return-type-toggle { background: #f1f5f9; padding: 5px; border-radius: 12px; display: inline-flex; }
            .return-type-btn { padding: 10px 24px; border-radius: 10px; border: none; font-weight: 600; font-size: 0.875rem; transition: all 0.3s; color: #64748b; background: transparent; }
            .return-type-btn.active { background: #fff; color: #2563eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

            .table-premium thead th { background: #f8fafc; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em; padding: 1.25rem 1rem; border-bottom: 2px solid #f1f5f9; }
            .table-premium tbody td { padding: 1.25rem 1rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }
            
            .select2-container--bootstrap-5 .select2-selection { border-radius: 10px; border: 1.5px solid #e2e8f0; height: calc(2.75rem + 2px); }
            .remove-row { color: #cbd5e1; cursor: pointer; transition: all 0.2s; font-size: 1.1rem; }
            .remove-row:hover { color: #ef4444; transform: scale(1.1); }
            
            .badge-stock { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 0.725rem; }
            .badge-stock-ok { background: #f0fdf4; color: #166534; border: 1px solid #bbfcce; }
            .badge-stock-warning { background: #fff7ed; color: #9a3412; border: 1px solid #ffedd5; }
            .badge-stock-checking { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

            .product-name-cell { max-width: 280px; }
            .product-name-cell .name { color: #1e293b; font-weight: 700; font-size: 1rem; margin-bottom: 2px; }
            .product-name-cell .details { color: #64748b; font-size: 0.8rem; }
        </style>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-slate-800 mb-1">Create Purchase Return</h4>
                    <p class="text-muted small mb-0">Manage product returns by invoice or global supplier-wise</p>
                </div>
                <div class="return-type-toggle">
                    <button type="button" class="return-type-btn active" id="btnByInvoice"><i class="fas fa-file-invoice me-2"></i>By Invoice</button>
                    <button type="button" class="return-type-btn" id="btnBySupplier"><i class="fas fa-tags me-2"></i>Global Return</button>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                </div>
            @endif

            <form action="{{ route('purchaseReturn.store') }}" method="POST" id="mainReturnForm">
                @csrf
                <input type="hidden" name="return_mode" id="return_mode" value="invoice">

                <div class="row g-4">
                    <!-- Step 1: Selection -->
                    <div class="col-xl-4">
                        <div class="premium-card mb-4">
                            <div class="card-header-premium">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-search me-2 text-primary"></i>Selection</h6>
                            </div>
                            <div class="card-body p-4">
                                <div id="invoiceSelectionSection">
                                    <label class="form-label">Search Purchase Invoice</label>
                                    <div class="input-group">
                                        <input type="text" id="invoice_search" class="form-control" placeholder="PUR-XXXXX or Bill No">
                                        <button type="button" id="btnSearchInvoice" class="btn btn-primary px-3"><i class="fas fa-search"></i></button>
                                    </div>
                                    <input type="hidden" name="purchase_id" id="purchase_id">
                                </div>

                                <div id="supplierSelectionSection" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Select Supplier</label>
                                        <select name="supplier_id" id="supplier_id" class="form-select select2">
                                            <option value="">Choose Supplier</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->company_name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Search & Add Products</label>
                                        <select id="product_search" class="form-select"></select>
                                        <small class="text-muted">Type product name, SKU or Style no.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="premium-card">
                            <div class="card-header-premium">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Settings</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label">Return Date</label>
                                    <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Adjustment Type</label>
                                    <select name="return_type" id="return_type" class="form-select" required>
                                        <option value="adjust_to_due">Adjust to Supplier Balance (Payable)</option>
                                        <option value="refund">Cash/Bank Refund</option>
                                        <option value="none">No Adjustment</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="accountSection" style="display: none;">
                                    <label class="form-label">Refund Account</label>
                                    <select name="account_id" class="form-select">
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->provider_name }} - {{ $acc->account_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Reason/Notes</label>
                                    <textarea name="reason" class="form-control" rows="3" placeholder="Explain the reason..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Items -->
                    <div class="col-xl-8">
                        <div class="premium-card min-vh-50 d-flex flex-column">
                            <div class="card-header-premium d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Return Items</h6>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold" id="itemCount">0 Items</span>
                            </div>
                            <div class="card-body p-0 flex-grow-1">
                                <div class="table-responsive">
                                    <table class="table table-premium mb-0" id="returnItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Item Details</th>
                                                <th style="width: 180px;">Return From</th>
                                                <th class="text-center" style="width: 100px;">Qty</th>
                                                <th class="text-end" style="width: 120px;">Unit Price</th>
                                                <th class="text-end" style="width: 130px;">Total</th>
                                                <th style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="emptyPlaceholder">
                                                <td colspan="6" class="text-center py-5 text-muted italic">
                                                    <i class="fas fa-shopping-basket fa-3x mb-3 opacity-20"></i><br>
                                                    Search an invoice or add products manually to start
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light border-top-2">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold py-3 text-slate-600">Grand Total</td>
                                                <td class="text-end fw-bold py-3 text-primary text-lg" id="grandTotal">0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white p-4 border-top">
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="btnSubmit">
                                    <i class="fas fa-check-circle me-2"></i> Confirm and Process Return
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
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let itemIndex = 0;
            const branches = @json($branches);
            const warehouses = @json($warehouses);

            // Select2 Init
            $('.select2').select2({ theme: 'bootstrap-5' });

            $('#product_search').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search Product by Name/Style/SKU',
                minimumInputLength: 0,
                ajax: {
                    url: "{{ route('purchaseReturn.search.product') }}",
                    dataType: 'json',
                    delay: params => (params.term ? 250 : 0), // Instant on empty, delayed while typing
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({ results: data.results })
                }
            }).on('select2:select', function(e) {
                const data = e.params.data;
                addRow(data);
                $(this).val(null).trigger('change');
            }).on('select2:open', function() {
                // Manually trigger a search if it's empty to show results immediately
                const searchField = document.querySelector('.select2-search__field');
                if (searchField && !searchField.value) {
                    $('#product_search').data('select2').trigger('query', { term: '' });
                }
            });

            // Toggle Modes
            $('#btnByInvoice').click(function() {
                $('.return-type-btn').removeClass('active');
                $(this).addClass('active');
                $('#return_mode').val('invoice');
                $('#invoiceSelectionSection').show();
                $('#supplierSelectionSection').hide();
                resetForm();
            });

            $('#btnBySupplier').click(function() {
                $('.return-type-btn').removeClass('active');
                $(this).addClass('active');
                $('#return_mode').val('global');
                $('#invoiceSelectionSection').hide();
                $('#supplierSelectionSection').show();
                resetForm();
            });

            $('#return_type').change(function() {
                if ($(this).val() === 'refund') $('#accountSection').slideDown();
                else $('#accountSection').slideUp();
            });

            // Search Invoice
            $('#btnSearchInvoice').click(function() {
                const q = $('#invoice_search').val();
                if (!q) return;

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('purchaseReturn.search.invoice.detail') }}",
                    data: { invoice_no: q },
                    success: res => {
                        if (res.success) {
                            resetForm();
                            $('#purchase_id').val(res.data.id);
                            res.data.items.forEach(item => {
                                addRow({
                                    product_id: item.product_id,
                                    variation_id: item.variation_id,
                                    text: item.product_name,
                                    price: item.unit_price,
                                    style: item.style_number,
                                    max_qty: item.quantity,
                                    purchase_item_id: item.id,
                                    from_type: item.location_type,
                                    from_id: item.location_id
                                });
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    complete: () => {
                        $(this).prop('disabled', false).html('<i class="fas fa-search"></i>');
                    }
                });
            });

            function addRow(data) {
                $('#emptyPlaceholder').hide();
                
                const productId = data.product_id;
                const variationId = data.variation_id || '';
                const idx = itemIndex++;

                let locationOptions = '';
                warehouses.forEach(w => locationOptions += `<option value="warehouse|${w.id}" ${(data.from_type == 'warehouse' && data.from_id == w.id) ? 'selected' : ''}>[W] ${w.name}</option>`);
                branches.forEach(b => locationOptions += `<option value="branch|${b.id}" ${(data.from_type == 'branch' && data.from_id == b.id) ? 'selected' : ''}>[B] ${b.name}</option>`);

                const row = `
                    <tr class="return-row" data-index="${idx}">
                        <td class="product-name-cell">
                            <div class="name">${data.text}</div>
                            <input type="hidden" name="items[${idx}][product_id]" value="${productId}">
                            <input type="hidden" name="items[${idx}][variation_id]" value="${variationId}">
                            <input type="hidden" name="items[${idx}][purchase_item_id]" value="${data.purchase_item_id || ''}">
                        </td>
                        <td>
                            <select name="items[${idx}][location_combined]" class="form-select form-select-sm location-picker">
                                ${locationOptions}
                            </select>
                            <input type="hidden" name="items[${idx}][return_from]" class="hidden-from" value="${data.from_type || 'warehouse'}">
                            <input type="hidden" name="items[${idx}][from_id]" class="hidden-from-id" value="${data.from_id || warehouses[0]?.id || 1}">
                            <div class="mt-2"><span class="badge-stock badge-stock-checking stock-display"><i class="fas fa-spinner fa-spin me-1"></i>Checking...</span></div>
                        </td>
                        <td>
                            <input type="number" name="items[${idx}][returned_qty]" class="form-control form-control-sm text-center return-qty" value="1" min="1" step="1">
                            ${data.max_qty ? `<small class="text-muted d-block text-center mt-1 fw-bold" style="font-size: 0.7rem;">MAX: ${data.max_qty}</small>` : ''}
                        </td>
                        <td>
                            <input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm text-end unit-price font-monospace" value="${data.price || 0}" step="0.01">
                        </td>
                        <td class="text-end fw-bold row-total font-monospace text-slate-700" style="font-size: 1.1rem;">0.00</td>
                        <td class="text-center">
                            <i class="fas fa-minus-circle remove-row"></i>
                        </td>
                    </tr>
                `;

                $('#returnItemsTable tbody').append(row);
                
                const $row = $(`tr[data-index="${idx}"]`);
                updateStock($row);
                calculateAll();
            }

            $(document).on('change', '.location-picker', function() {
                const val = $(this).val();
                const [type, id] = val.split('|');
                const $row = $(this).closest('tr');
                $row.find('.hidden-from').val(type);
                $row.find('.hidden-from-id').val(id);
                updateStock($row);
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                if ($('#returnItemsTable tbody tr.return-row').length === 0) $('#emptyPlaceholder').show();
                calculateAll();
            });

            $(document).on('input', '.return-qty, .unit-price', function() {
                calculateAll();
            });

            function updateStock($row) {
                const productId = $row.find('input[name*="[product_id]"]').val();
                const variationId = $row.find('input[name*="[variation_id]"]').val();
                const fromType = $row.find('.hidden-from').val();
                const fromId = $row.find('.hidden-from-id').val();
                const $display = $row.find('.stock-display');

                $display.html('<i class="fas fa-spinner fa-spin me-1"></i>Checking...').removeClass('badge-stock-ok badge-stock-warning').addClass('badge-stock-checking');

                $.ajax({
                    url: `/erp/purchase-return/stock/${productId}/${fromId}`,
                    data: { return_from: fromType, variation_id: variationId },
                    success: res => {
                        const qty = (res && typeof res.quantity !== 'undefined') ? res.quantity : 0;
                        $display.html(`<i class="fas fa-box-open me-1"></i>Stock: ${qty}`).removeClass('badge-stock-checking badge-stock-ok badge-stock-warning');
                        $display.addClass(qty > 0 ? 'badge-stock-ok' : 'badge-stock-warning');
                    },
                    error: () => {
                        $display.text('Error').removeClass('badge-stock-checking').addClass('badge-stock-warning');
                    }
                });
            }

            function calculateAll() {
                let grand = 0;
                let count = 0;
                $('.return-row').each(function() {
                    const q = parseFloat($(this).find('.return-qty').val()) || 0;
                    const p = parseFloat($(this).find('.unit-price').val()) || 0;
                    const tot = q * p;
                    $(this).find('.row-total').text(tot.toFixed(2));
                    grand += tot;
                    count++;
                });
                $('#grandTotal').text(grand.toFixed(2));
                $('#itemCount').text(`${count} Item${count != 1 ? 's' : ''}`);
            }

            function resetForm() {
                $('#returnItemsTable tbody tr.return-row').remove();
                $('#emptyPlaceholder').show();
                $('#purchase_id').val('');
                $('#grandTotal').text('0.00');
                $('#itemCount').text('0 Items');
                itemIndex = 0;
            }
        });
    </script>
@endsection
