@extends('erp.master')

@section('title', 'Create Sale Return')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('saleReturn.list') }}" class="text-decoration-none text-muted">Returns</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Create</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Process Sale Return</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('saleReturn.list') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-list me-2"></i>Return List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Invoice Search Card -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-uppercase text-muted small"><i class="fas fa-search me-2 text-primary"></i>Find Original Sale</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-end g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted text-uppercase">Invoice / Sale Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-file-invoice"></i></span>
                                <input type="text" id="invoice_search" class="form-control border-start-0" placeholder="e.g. POS-000001">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-create-premium w-100">
                                <i class="fas fa-search me-2"></i>Search
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
                        <div class="premium-card mb-4">
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Customer</label>
                                        <input type="text" id="customer_display" class="form-control bg-light fw-bold" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Return Date</label>
                                        <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Refund Method</label>
                                        <select name="refund_type" class="form-select" required>
                                            <option value="none">No Refund</option>
                                            <option value="cash">Cash Refund</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="credit">Store Credit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Restock Location</label>
                                        <div class="input-group">
                                            <select name="return_to_type" id="return_to_type" class="form-select" style="max-width: 120px;" required>
                                                <option value="branch">Branch</option>
                                                <option value="warehouse">Warehouse</option>
                                            </select>
                                            <select name="return_to_id" id="return_to_id" class="form-select" required>
                                                <option value="">Select...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="col-lg-12">
                        <div class="premium-card">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-uppercase text-muted small"><i class="fas fa-shopping-basket me-2 text-primary"></i>Return Items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table premium-table mb-0" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>Product Information</th>
                                                <th class="text-center">Sale Qty</th>
                                                <th class="text-center" style="width: 120px;">Return Qty</th>
                                                <th class="text-end" style="width: 140px;">Unit Price</th>
                                                <th class="text-end" style="width: 140px;">Subtotal</th>
                                                <th>Return Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be populated here -->
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3" class="text-end text-uppercase small text-muted">Totals</td>
                                                <td id="totalReturnQty" class="text-center text-primary h6 mb-0">0</td>
                                                <td></td>
                                                <td id="grandTotal" class="text-end text-success h5 mb-0">0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white p-4 text-end border-top">
                                <button type="submit" class="btn btn-create-premium px-5 py-2">
                                    <i class="fas fa-check-circle me-2"></i>COMPLETE RETURN PROCESS
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