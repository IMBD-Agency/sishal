@extends('erp.master')

@section('title', 'Create Exchange')

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
                            <li class="breadcrumb-item"><a href="{{ route('exchange.list') }}" class="text-decoration-none text-muted">Exchange</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">New Exchange</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">Process Product Exchange</h4>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('exchange.list') }}" class="btn btn-light fw-bold shadow-sm">
                        <i class="fas fa-list me-2"></i>Exchange List
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Invoice Search Card -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-uppercase text-muted small"><i class="fas fa-search me-2 text-primary"></i>Sales Exchange Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-end g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sales Invoice No. *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-file-invoice"></i></span>
                                <input type="text" id="invoice_search" class="form-control border-start-0" placeholder="Invoice Number">
                                <button type="button" id="btnSearch" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form id="exchangeForm" action="{{ route('exchange.store') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="original_pos_id" id="original_pos_id">
                
                <div class="row g-4">
                    <!-- Left: Metadata -->
                    <div class="col-lg-12">
                        <div class="premium-card mb-4">
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Customer</label>
                                        <input type="text" id="customer_display" class="form-control bg-light fw-bold" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Exchange Date</label>
                                        <input type="date" name="exchange_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Return Items -->
                    <div class="col-lg-6">
                        <div class="premium-card">
                            <div class="card-header bg-danger bg-opacity-10 py-3 border-bottom">
                                <h6 class="mb-0 fw-bold text-uppercase text-danger small"><i class="fas fa-undo me-2"></i>Items to Return</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table premium-table mb-0" id="returnItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Item Details</th>
                                                <th class="text-center">Sale Qty</th>
                                                <th class="text-center" style="width: 100px;">Ret Qty</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3" class="text-end text-uppercase small">Total Return Value</td>
                                                <td id="totalReturnValue" class="text-end text-danger">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Items -->
                    <div class="col-lg-6">
                        <div class="premium-card">
                            <div class="card-header bg-success bg-opacity-10 py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-uppercase text-success small"><i class="fas fa-shopping-cart me-2"></i>New Items to Buy</h6>
                                <button type="button" class="btn btn-sm btn-success" id="btnAddNewItem"><i class="fas fa-plus small"></i></button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table premium-table mb-0" id="newItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th class="text-center" style="width: 80px;">Qty</th>
                                                <th class="text-end" style="width: 100px;">Price</th>
                                                <th class="text-end">Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3" class="text-end text-uppercase small">Total Purchase Value</td>
                                                <td id="totalPurchaseValue" class="text-end text-success">0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Calculation -->
                    <div class="col-lg-12">
                        <div class="premium-card">
                            <div class="card-body p-4">
                                <div class="row justify-content-end">
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Return Credit:</span>
                                            <span class="fw-bold text-danger" id="summaryReturn">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">New Purchase:</span>
                                            <span class="fw-bold text-success" id="summaryPurchase">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Discount:</span>
                                            <input type="number" name="discount" id="discountInput" class="form-control form-control-sm text-end" style="width: 100px;" value="0" step="0.01">
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-3">
                                            <h5 class="fw-bold">Net Amount:</h5>
                                            <h5 class="fw-bold text-primary" id="netAmount">0.00</h5>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Amount to Pay Now</label>
                                            <input type="number" name="paid_amount" id="paidInput" class="form-control text-end fw-bold" value="0" step="0.01">
                                        </div>
                                        <button type="submit" class="btn btn-create-premium w-100 py-3 shadow-lg">
                                            <i class="fas fa-check-circle me-2"></i>COMPLETE EXCHANGE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modals & Scripts -->
    <div class="modal fade" id="productPickerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                         <input type="text" id="productSearchInput" class="form-control" placeholder="Search product by name or style number...">
                    </div>
                    <div id="productListResults" class="list-group"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const $btnSearch = $('#btnSearch');
            const $invoiceInput = $('#invoice_search');
            const $exchangeForm = $('#exchangeForm');
            const $returnItemsBody = $('#returnItemsTable tbody');
            const $newItemsBody = $('#newItemsTable tbody');
            
            let allProducts = [];

            // Load products for purchase selection
            $.get("{{ route('products.search') }}", function(res) {
                // Simplified for brevity, usually you'd search via AJAX
            });

            $btnSearch.on('click', function() {
                const invoiceNo = $invoiceInput.val().trim();
                if (!invoiceNo) return Swal.fire('Error', 'Enter invoice number', 'error');

                $btnSearch.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('exchange.search.invoice') }}",
                    method: 'GET',
                    data: { invoice_no: invoiceNo },
                    success: function(res) {
                        if (res.success) {
                            populateExchange(res.data);
                            $exchangeForm.fadeIn();
                        } else {
                            Swal.fire('Not Found', res.message, 'warning');
                        }
                    },
                    complete: function() {
                        $btnSearch.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Search');
                    }
                });
            });

            let originalDiscountRatio = 0;

            function populateExchange(data) {
                $('#original_pos_id').val(data.id);
                $('#customer_display').val(data.customer_name + ' (' + data.customer_phone + ')');
                $returnItemsBody.empty();
                
                originalDiscountRatio = data.sub_total > 0 ? (data.discount / data.sub_total) : 0;

                data.items.forEach((item, index) => {
                    const row = `
                        <tr>
                            <td>
                                <strong>${item.product_name}</strong><br>
                                <small class="text-muted">${item.style_number} | ${item.color} | ${item.size}</small>
                                <input type="hidden" name="return_items[${index}][pos_item_id]" value="${item.id}">
                                <input type="hidden" name="return_items[${index}][product_id]" value="${item.product_id}">
                                <input type="hidden" name="return_items[${index}][variation_id]" value="${item.variation_id || ''}">
                                <input type="hidden" name="return_items[${index}][unit_price]" value="${item.unit_price}">
                            </td>
                            <td class="text-center text-muted">
                                ${item.quantity}<br>
                                ${item.returned_qty > 0 ? `<small class="text-danger">(-${item.returned_qty} ret)</small><br>` : ''}
                                <small class="text-xs text-muted">@ ${item.unit_price}</small>
                            </td>
                            <td class="text-center">
                                <input type="number" name="return_items[${index}][qty]" class="form-control form-control-sm text-center return-qty" 
                                    min="0" max="${item.available_qty}" value="0" ${item.available_qty <= 0 ? 'disabled' : ''}>
                                ${item.available_qty <= 0 ? '<span class="badge bg-danger">Full Return</span>' : ''}
                            </td>
                            <td class="text-end">
                                <div class="row-return-total fw-bold">0.00</div>
                                <div class="text-xs text-danger row-return-discount" style="font-size: 0.7rem;"></div>
                            </td>
                        </tr>
                    `;
                    $returnItemsBody.append(row);
                });
                calculateAll();
            }

            $(document).on('input', '.return-qty', function() {
                const $row = $(this).closest('tr');
                const qty = parseFloat($(this).val()) || 0;
                const unitPrice = parseFloat($row.find('input[name*="unit_price"]').val());
                
                const grossTotal = qty * unitPrice;
                const discountDeduction = grossTotal * originalDiscountRatio;
                const netCredit = grossTotal - discountDeduction;

                $row.find('.row-return-total').text(netCredit.toFixed(2)).data('net', netCredit);
                
                if (discountDeduction > 0) {
                    $row.find('.row-return-discount').text('-' + discountDeduction.toFixed(2) + ' (Disc.)');
                } else {
                    $row.find('.row-return-discount').text('');
                }

                calculateAll();
            });

            $('#btnAddNewItem').on('click', function() {
                $('#productPickerModal').modal('show');
            });

            $('#productSearchInput').on('input', function() {
                const q = $(this).val();
                if (q.length < 2) return;
                $.get("{{ route('products.search') }}", { q: q }, function(res) {
                    let html = '';
                    res.forEach(p => {
                        html += `<button type="button" class="list-group-item list-group-item-action select-product" 
                            data-id="${p.id}" data-name="${p.name}" data-sku="${p.sku || ''}">${p.name} (${p.sku || ''})</button>`;
                    });
                    $('#productListResults').html(html);
                });
            });

            $(document).on('click', '.select-product', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const sku = $(this).data('sku');
                const index = $newItemsBody.find('tr').length;
                
                // Fetch price
                $.get(`/erp/products/${id}/sale-price`, function(priceRes) {
                    const price = priceRes.price || 0;
                    const row = `
                        <tr>
                            <td>
                                <strong>${name}</strong><br>
                                <small class="text-muted">${sku}</small>
                                <input type="hidden" name="new_items[${index}][product_id]" value="${id}">
                                <input type="hidden" name="new_items[${index}][variation_id]" value="">
                            </td>
                            <td>
                                <input type="number" name="new_items[${index}][qty]" class="form-control form-control-sm text-center new-qty" value="1">
                            </td>
                            <td>
                                <input type="number" name="new_items[${index}][unit_price]" class="form-control form-control-sm text-end new-price" value="${price}">
                            </td>
                            <td class="text-end row-new-total">${price}</td>
                            <td><button type="button" class="btn btn-sm btn-link text-danger remove-item"><i class="fas fa-times"></i></button></td>
                        </tr>
                    `;
                    $newItemsBody.append(row);
                    calculateAll();
                    $('#productPickerModal').modal('hide');
                });
            });

            $(document).on('input', '.new-qty, .new-price, #discountInput', function() {
                const $row = $(this).closest('tr');
                const qty = parseFloat($row.find('.new-qty').val()) || 0;
                const price = parseFloat($row.find('.new-price').val()) || 0;
                $row.find('.row-new-total').text((qty * price).toFixed(2));
                calculateAll();
            });

            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                calculateAll();
            });

            function calculateAll() {
                let totalReturn = 0;
                $('.row-return-total').each(function() {
                    totalReturn += parseFloat($(this).text()) || 0;
                });
                
                let totalPurchase = 0;
                $('.row-new-total').each(function() {
                    totalPurchase += parseFloat($(this).text()) || 0;
                });

                const discount = parseFloat($('#discountInput').val()) || 0;
                const net = totalPurchase - totalReturn - discount;

                $('#totalReturnValue').text(totalReturn.toFixed(2));
                $('#totalPurchaseValue').text(totalPurchase.toFixed(2));
                $('#summaryReturn').text(totalReturn.toFixed(2));
                $('#summaryPurchase').text(totalPurchase.toFixed(2));
                
                if (net < 0) {
                    $('#netAmount').text('Credit: ' + Math.abs(net).toFixed(2)).addClass('text-success').removeClass('text-primary');
                    $('#paidInput').val(0);
                } else {
                    $('#netAmount').text(net.toFixed(2)).addClass('text-primary').removeClass('text-success');
                    $('#paidInput').val(net.toFixed(2));
                }
            }

            $('#exchangeForm').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $form.find('button[type="submit"]');
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
                
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Success', res.message, 'success').then(() => {
                                window.location.href = res.redirect;
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>COMPLETE EXCHANGE');
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Calculation error or server error', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>COMPLETE EXCHANGE');
                    }
                });
            });
        });
    </script>
@endsection
