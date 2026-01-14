@extends('erp.master')

@section('title', 'Create Sale Return')

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
        </style>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Return</h4>
                </div>
                <a href="{{ route('saleReturn.list') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-list me-1"></i> Return List
                </a>
            </div>

            <!-- Invoice Search Card -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Return Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row items-center">
                        <div class="col-md-6">
                            <label class="form-label">Invoice No.<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="invoice_search" class="form-control" placeholder="Invoice Number" aria-label="Invoice Number">
                            </div>
                            <button type="button" id="btnSearch" class="btn btn-primary mt-3 px-4">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <form id="saleReturnForm" action="{{ route('saleReturn.store') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="pos_sale_id" id="pos_sale_id">
                <input type="hidden" name="customer_id" id="customer_id">

                <div class="row g-4">
                    <!-- Left: Metadata -->
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Customer</label>
                                        <input type="text" id="customer_display" class="form-control bg-light" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Return Date *</label>
                                        <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Refund Method *</label>
                                        <select name="refund_type" class="form-select" required>
                                            <option value="none">No Refund</option>
                                            <option value="cash">Cash Refund</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="credit">Store Credit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Restock To *</label>
                                        <select name="return_to_type" id="return_to_type" class="form-select mb-2" required>
                                            <option value="branch">Branch</option>
                                            <option value="warehouse">Warehouse</option>
                                        </select>
                                        <select name="return_to_id" id="return_to_id" class="form-select" required>
                                            <option value="">Select Location</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 fw-bold">Return Items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-items mb-0" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">SL.</th>
                                                <th>Product Name</th>
                                                <th class="text-center">Sale Qty</th>
                                                <th class="text-center">Return Qty</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Total Price</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be populated here -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="3" class="text-end">Total Return Qty:</td>
                                                <td id="totalReturnQty" class="text-center">0</td>
                                                <td class="text-end">Grand Total:</td>
                                                <td id="grandTotal" class="text-end">0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white p-4 text-end">
                                <button type="submit" class="btn btn-success px-5 py-2 fw-bold">
                                    <i class="fas fa-check-circle me-2"></i> Submit Return
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
            const $saleReturnForm = $('#saleReturnForm');
            const $itemsTableBody = $('#itemsTable tbody');

            $btnSearch.on('click', function() {
                const invoiceNo = $invoiceInput.val().trim();
                if (!invoiceNo) {
                    Swal.fire('Error', 'Please enter an invoice number.', 'error');
                    return;
                }

                $btnSearch.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Searching...');

                $.ajax({
                    url: "{{ route('saleReturn.search.invoice') }}",
                    method: 'GET',
                    data: { invoice_no: invoiceNo },
                    success: function(res) {
                        if (res.success) {
                            populateReturnForm(res.data);
                            $saleReturnForm.fadeIn();
                        } else {
                            $saleReturnForm.hide();
                            Swal.fire('Not Found', res.message || 'Invoice not found.', 'warning');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while searching.', 'error');
                    },
                    complete: function() {
                        $btnSearch.prop('disabled', false).html('<i class="fas fa-search me-2"></i> Search');
                    }
                });
            });

            function populateReturnForm(data) {
                $('#pos_sale_id').val(data.id);
                $('#customer_id').val(data.customer_id);
                $('#customer_display').val(data.customer_name + ' (' + data.customer_phone + ')');
                
                // Set branch logic
                if (data.branch_id) {
                    $('#return_to_type').val('branch').trigger('change');
                    setTimeout(() => $('#return_to_id').val(data.branch_id), 200);
                }

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
                                <input type="hidden" name="items[${index}][variation_id]" value="${item.variation_id}">
                            </td>
                            <td class="text-center text-muted">${item.quantity}</td>
                            <td class="text-center">
                                <input type="number" name="items[${index}][returned_qty]" class="form-control form-control-sm mx-auto text-center return-qty" 
                                    style="width: 80px;" min="0" max="${item.quantity}" step="1" value="0">
                            </td>
                            <td class="text-end">
                                <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm text-end unit-price" 
                                    style="width: 100px; display: inline-block;" step="0.01" value="${item.unit_price}">
                            </td>
                            <td class="text-end fw-bold row-total">0.00</td>
                            <td>
                                <input type="text" name="items[${index}][reason]" class="form-control form-control-sm" placeholder="Reason...">
                            </td>
                        </tr>
                    `;
                    $itemsTableBody.append(row);
                });
                calculateTotals();
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

            $('#return_to_type').on('change', function() {
                const type = $(this).val();
                const $idSelect = $('#return_to_id');
                $idSelect.empty().append('<option value="">Select Location</option>');
                
                const locations = type === 'branch' ? @json($branches) : @json($warehouses);
                locations.forEach(loc => {
                    $idSelect.append(`<option value="${loc.id}">${loc.name}</option>`);
                });
            });

            // Initial trigger
            $('#return_to_type').trigger('change');
        });
    </script>
@endsection