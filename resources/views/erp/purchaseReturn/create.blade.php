@extends('erp.master')

@section('title', 'Create Purchase Return')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            .form-section-title {
                font-size: 0.85rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #374151;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
            }
            .form-section-title::after {
                content: '';
                flex: 1;
                height: 1px;
                background: #e5e7eb;
                margin-left: 1rem;
            }
            .card { border-radius: 12px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            .form-label { font-size: 0.85rem; font-weight: 600; color: #4b5563; }
            .form-control, .form-select {
                padding: 0.6rem 0.8rem;
                border-color: #d1d5db;
                border-radius: 8px;
                font-size: 0.9rem;
            }
            .form-control:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            .btn-primary { background-color: #2563eb; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 600; }
            .btn-primary:hover { background-color: #1d4ed8; }
            
            .table-items thead th {
                background: #f9fafb;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                color: #6b7280;
                padding: 12px;
                border-bottom: 2px solid #e5e7eb;
            }
            .table-items tbody td {
                padding: 12px;
                vertical-align: middle;
                border-bottom: 1px solid #f3f4f6;
                font-size: 0.85rem;
            }
            .product-info-badge {
                font-size: 0.7rem;
                padding: 2px 8px;
                border-radius: 4px;
                background: #f3f4f6;
                color: #4b5563;
                margin-right: 4px;
            }
            .stock-badge {
                font-size: 0.75rem;
                font-weight: 600;
                padding: 4px 10px;
                border-radius: 20px;
            }
            .stock-ok { background: #dcfce7; color: #166534; }
            .stock-low { background: #fee2e2; color: #991b1b; }
        </style>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Purchase Return</h4>
                </div>
                <a href="{{ route('purchaseReturn.list') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-list me-1"></i> Return List
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-triangle me-2"></i>Error(s):</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Purchase Search Card -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Find Purchase Invoice</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row items-center">
                        <div class="col-md-6">
                            <label class="form-label">Invoice / Purchase ID<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="invoice_search" class="form-control" placeholder="Enter Purchase Invoice Number">
                            </div>
                            <button type="button" id="btnSearch" class="btn btn-primary mt-3 px-4">
                                <i class="fas fa-search me-2"></i> Search Purchase
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <form id="purchaseReturnForm" action="{{ route('purchaseReturn.store') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="purchase_id" id="purchase_id">
                <input type="hidden" name="supplier_id" id="supplier_id">

                <div class="row g-4">
                    <!-- Metadata Card -->
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Supplier</label>
                                        <input type="text" id="supplier_display" class="form-control bg-light" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Return Date *</label>
                                        <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Return Type *</label>
                                        <select name="return_type" class="form-select" required>
                                            <option value="refund">Refund (Cash/Bank)</option>
                                            <option value="adjust_to_due">Adjust to Due Balance</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Default Return From</label>
                                        <select id="default_location_type" class="form-select mb-2">
                                            <option value="branch">Branch</option>
                                            <option value="warehouse">Warehouse</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 fw-bold">Items to Return</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-items mb-0" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">SL.</th>
                                                <th>Product Name</th>
                                                <th style="width: 180px;">Return From</th>
                                                <th class="text-center" style="width: 100px;">Current Stock</th>
                                                <th class="text-center" style="width: 120px;">Return Qty</th>
                                                <th class="text-end" style="width: 120px;">Purchase Price</th>
                                                <th class="text-end" style="width: 120px;">Tot. Amount</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be populated here -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="4" class="text-end">Total Return Qty:</td>
                                                <td id="totalReturnQty" class="text-center">0</td>
                                                <td class="text-end">Grand Total:</td>
                                                <td id="grandTotal" class="text-end">0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card-body border-top p-4">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Explain the reason for this large return..."></textarea>
                            </div>

                            <div class="card-footer bg-white p-4 text-end">
                                <button type="submit" class="btn btn-success px-5 py-2 fw-bold" style="background-color: #059669; border: none;">
                                    <i class="fas fa-check-circle me-2"></i> Process Return
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const $btnSearch = $('#btnSearch');
            const $invoiceInput = $('#invoice_search');
            const $purchaseReturnForm = $('#purchaseReturnForm');
            const $itemsTableBody = $('#itemsTable tbody');

            $btnSearch.on('click', function() {
                const invoiceNo = $invoiceInput.val().trim();
                if (!invoiceNo) {
                    Swal.fire('Error', 'Please enter an invoice number.', 'error');
                    return;
                }

                $btnSearch.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Searching...');

                $.ajax({
                    url: "{{ route('purchaseReturn.search.invoice.detail') }}",
                    method: 'GET',
                    data: { invoice_no: invoiceNo },
                    success: function(res) {
                        if (res.success) {
                            populateReturnForm(res.data);
                            $purchaseReturnForm.fadeIn();
                        } else {
                            $purchaseReturnForm.hide();
                            Swal.fire('Not Found', res.message || 'Purchase invoice not found.', 'warning');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while searching.', 'error');
                    },
                    complete: function() {
                        $btnSearch.prop('disabled', false).html('<i class="fas fa-search me-2"></i> Search Purchase');
                    }
                });
            });

            function populateReturnForm(data) {
                $('#purchase_id').val(data.id);
                $('#supplier_id').val(data.supplier_id);
                $('#supplier_display').val(data.supplier_name);
                
                $itemsTableBody.empty();
                data.items.forEach((item, index) => {
                    const row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>
                                <div class="fw-bold text-dark">${item.product_name}</div>
                                <div class="mt-1">
                                    <span class="product-info-badge">Style: ${item.style_number}</span>
                                    <span class="product-info-badge">Color: ${item.color}</span>
                                    <span class="product-info-badge">Size: ${item.size}</span>
                                </div>
                                <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                <input type="hidden" name="items[${index}][variation_id]" value="${item.variation_id ?? ''}">
                                <input type="hidden" name="items[${index}][purchase_item_id]" value="${item.id}">
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <select name="items[${index}][return_from]" class="form-select form-select-sm return-from-type" data-index="${index}">
                                        <option value="branch" ${item.location_type === 'branch' ? 'selected' : ''}>Branch</option>
                                        <option value="warehouse" ${item.location_type === 'warehouse' ? 'selected' : ''}>Warehouse</option>
                                    </select>
                                    <select name="items[${index}][from_id]" class="form-select form-select-sm return-from-id" data-index="${index}">
                                        <option value="">Select Location</option>
                                    </select>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="stock-badge stock-ok current-stock">-</span>
                            </td>
                            <td class="text-center">
                                <input type="number" name="items[${index}][returned_qty]" class="form-control form-control-sm mx-auto text-center return-qty" 
                                    style="width: 80px;" min="0" max="${item.quantity}" step="1" value="0">
                                <div class="text-muted mt-1" style="font-size: 0.7rem;">Max: ${item.quantity}</div>
                            </td>
                            <td class="text-end">
                                <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm text-end unit-price" 
                                    style="width: 100px; display: inline-block;" step="0.01" value="${item.unit_price}">
                            </td>
                            <td class="text-end fw-bold row-total">0.00</td>
                            <td>
                                <input type="text" name="items[${index}][reason]" class="form-control form-control-sm" placeholder="Defect/Wrong item...">
                            </td>
                        </tr>
                    `;
                    $itemsTableBody.append(row);
                    
                    // Trigger initial location load for this row
                    const $row = $itemsTableBody.find('tr').last();
                    loadLocations($row, item.location_type, item.location_id);
                });
                calculateTotals();
            }

            $(document).on('change', '.return-from-type', function() {
                const $row = $(this).closest('tr');
                const type = $(this).val();
                loadLocations($row, type);
            });

            $(document).on('change', '.return-from-id', function() {
                updateStockDisplay($(this).closest('tr'));
            });

            function loadLocations($row, type, selectedId = null) {
                const $idSelect = $row.find('.return-from-id');
                $idSelect.empty().append('<option value="">Select Location</option>');
                
                const locations = type === 'branch' ? @json($branches) : @json($warehouses);
                locations.forEach(loc => {
                    const selected = selectedId == loc.id ? 'selected' : '';
                    $idSelect.append(`<option value="${loc.id}" ${selected}>${loc.name}</option>`);
                });

                if (selectedId) {
                    updateStockDisplay($row);
                }
            }

            function updateStockDisplay($row) {
                const productId = $row.find('input[name*="[product_id]"]').val();
                const fromType = $row.find('.return-from-type').val();
                const fromId = $row.find('.return-from-id').val();
                const $stockElem = $row.find('.current-stock');

                if (productId && fromId) {
                    $.ajax({
                        url: `/erp/purchase-return/stock/${productId}/${fromId}`,
                        method: 'GET',
                        data: { return_from: fromType },
                        success: function(stock) {
                            const qty = stock && stock.quantity ? stock.quantity : 0;
                            $stockElem.text(qty).removeClass('stock-ok stock-low');
                            $stockElem.addClass(qty > 0 ? 'stock-ok' : 'stock-low');
                        }
                    });
                } else {
                    $stockElem.text('-').removeClass('stock-ok stock-low');
                }
            }

            $(document).on('input', '.return-qty, .unit-price', function() {
                const $row = $(this).closest('tr');
                const qty = parseFloat($row.find('.return-qty').val()) || 0;
                const price = parseFloat($row.find('.unit-price').val()) || 0;
                const total = qty * price;
                $row.find('.row-total').text(total.toFixed(2));
                calculateTotals();
            });

            function calculateTotals() {
                let gQty = 0;
                let gAmt = 0;
                $('.return-qty').each(function() {
                    gQty += parseFloat($(this).val()) || 0;
                });
                $('.row-total').each(function() {
                    gAmt += parseFloat($(this).text()) || 0;
                });
                $('#totalReturnQty').text(gQty);
                $('#grandTotal').text(gAmt.toFixed(2));
            }
        });
    </script>
@endsection
