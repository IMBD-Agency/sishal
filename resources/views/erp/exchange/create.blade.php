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
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-uppercase text-muted small"><i class="fas fa-search me-2 text-primary"></i>Sales Exchange Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-end g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted text-uppercase">Sales Invoice No. *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-file-invoice"></i></span>
                                <input type="text" id="invoice_search" class="form-control border-start-0" placeholder="Invoice Number/Scan Barcode">
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
                        <div class="premium-card h-100">
                            <div class="card-header bg-danger bg-opacity-10 py-3 px-4 border-bottom">
                                <h6 class="mb-0 fw-bold text-uppercase text-danger small"><i class="fas fa-undo me-2"></i>Items to Return</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table premium-table mb-0 align-middle" id="returnItemsTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="py-3 ps-3">Item Details</th>
                                                <th class="text-center py-3">Action</th>
                                                <th class="text-center py-3" style="width: 120px;">Ret Qty</th>
                                                <th class="text-end py-3 pe-3">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3" class="text-end text-uppercase small py-3">Total Return Value</td>
                                                <td id="totalReturnValue" class="text-end text-danger py-3 pe-3">0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Items -->
                    <div class="col-lg-6">
                        <div class="premium-card h-100">
                            <div class="card-header bg-success bg-opacity-10 py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-uppercase text-success small"><i class="fas fa-shopping-cart me-2"></i>New Items to Buy</h6>
                                <button type="button" class="btn btn-sm btn-success shadow-sm px-3" id="btnAddNewItem"><i class="fas fa-plus small me-1"></i>Add Product</button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table premium-table mb-0 align-middle" id="newItemsTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="py-3 ps-3">Product</th>
                                                <th class="text-center py-3" style="width: 120px;">Qty</th>
                                                <th class="text-end py-3" style="width: 140px;">Price</th>
                                                <th class="text-end py-3">Total</th>
                                                <th style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3" class="text-end text-uppercase small py-3">Total Purchase Value</td>
                                                <td id="totalPurchaseValue" class="text-end text-success py-3">0.00</td>
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
                                    <div class="col-md-5 col-lg-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Return Credit:</span>
                                            <span class="fw-bold text-danger" id="summaryReturn">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">New Purchase:</span>
                                            <span class="fw-bold text-success" id="summaryPurchase">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Discount Adjustment:</span>
                                            <input type="number" name="discount" id="discountInput" class="form-control text-end" style="width: 140px;" value="0" step="0.01">
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-3 align-items-center">
                                            <h5 class="fw-bold mb-0">Net Payable:</h5>
                                            <h4 class="fw-bold text-primary mb-0" id="netAmount">0.00</h4>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Amount to Pay Now</label>
                                            <input type="number" name="paid_amount" id="paidInput" class="form-control form-control-lg text-end fw-bold" value="0" step="0.01">
                                            <div class="form-text text-end small" id="paymentStatusText"></div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-lg fw-bold text-uppercase">
                                            <i class="fas fa-check-circle me-2"></i>Complete Exchange
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

    <!-- Product Picker Modal -->
    <div class="modal fade" id="productPickerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold text-dark">Select Product to Exchange/Buy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 sticky-top bg-white pb-2">
                         <div class="input-group">
                             <div class="input-group-text border-end-0 bg-white text-muted"><i class="fas fa-search"></i></div>
                             <input type="text" id="productSearchInput" class="form-control border-start-0" placeholder="Type product name, SKU or style number..." autocomplete="off">
                         </div>
                    </div>
                    <div id="productListResults" class="list-group list-group-flush">
                        <div class="text-center text-muted mt-4">Type at least 2 characters to search...</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Variation Picker Modal -->
    <div class="modal fade" id="variationPickerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="variationModalTitle">Select Size/Variant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="variationList" class="list-group list-group-flush">
                        <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
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
            
            // Focus invoice search on load
            $invoiceInput.focus();

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
            
            // Allow Enter key for search
            $invoiceInput.on('keypress', function(e) {
                if(e.which == 13) {
                    $btnSearch.click();
                }
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
                            <td class="ps-3">
                                <strong>${item.product_name}</strong><br>
                                <small class="text-muted"><i class="fas fa-tag me-1"></i>${item.style_number} | ${item.color} | ${item.size}</small>
                                <div class="small text-muted mt-1">Sold Price: ${item.unit_price}</div>
                                <input type="hidden" name="return_items[${index}][pos_item_id]" value="${item.id}">
                                <input type="hidden" name="return_items[${index}][product_id]" value="${item.product_id}">
                                <input type="hidden" name="return_items[${index}][variation_id]" value="${item.variation_id || ''}">
                                <input type="hidden" name="return_items[${index}][unit_price]" value="${item.unit_price}">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill btn-quick-exchange" 
                                    data-product-name="${item.product_name}" title="Exchange this item with another size/color">
                                    <i class="fas fa-exchange-alt me-1"></i> Exchange
                                </button>
                            </td>
                            <td class="text-center align-middle">
                                 <div class="input-group input-group-sm justify-content-center">
                                    <input type="number" name="return_items[${index}][qty]" class="form-control text-center return-qty" 
                                    min="0" max="${item.available_qty}" value="0" style="max-width: 80px" ${item.available_qty <= 0 ? 'disabled' : ''}>
                                 </div>
                                ${item.available_qty <= 0 ? '<span class="badge bg-danger mt-1">Returned</span>' : 
                                    `<small class="text-muted d-block mt-1">Max: ${item.available_qty}</small>`}
                            </td>
                            <td class="text-end pe-3 align-middle">
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

            // Quick Exchange Button Handler
            $(document).on('click', '.btn-quick-exchange', function() {
                const productName = $(this).data('product-name');
                const $row = $(this).closest('tr');
                // Auto-set return qty to 1 if it's 0, to be helpful
                const $qtyInput = $row.find('.return-qty');
                if($qtyInput.val() == 0 && !$qtyInput.prop('disabled')) {
                    $qtyInput.val(1).trigger('input');
                }

                $('#productPickerModal').modal('show');
                $('#productSearchInput').val(productName).trigger('input');
                setTimeout(function() { $('#productSearchInput').focus(); }, 500);
            });

            $('#btnAddNewItem').on('click', function() {
                $('#productPickerModal').modal('show');
                $('#productSearchInput').val(''); // Clear previous search
                $('#productListResults').html('<div class="text-center text-muted mt-4">Type at least 2 characters to search...</div>');
                // Auto focus on search input
                setTimeout(function() { $('#productSearchInput').focus(); }, 500);
            });

            let searchTimeout;
            $('#productSearchInput').on('input', function() {
                const q = $(this).val();
                clearTimeout(searchTimeout);
                
                if (q.length < 2) return;
                
                // Show loading indicator
                $('#productListResults').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin text-muted"></i> Searching...</div>');
                
                searchTimeout = setTimeout(() => {
                    $.get("{{ route('products.search') }}", { q: q }, function(res) {
                        let html = '';
                        if(res.length === 0) {
                            html = '<div class="text-center text-muted py-3">No products found</div>';
                        } else {
                            res.forEach(p => {
                                // Check if has variations
                                const hasVar = p.has_variations ? 1 : 0;
                                const badge = hasVar ? '<span class="badge bg-primary bg-opacity-10 text-primary ms-2"><i class="fas fa-layer-group me-1"></i>Variants</span>' : '';
                                
                                html += `<button type="button" class="list-group-item list-group-item-action select-product d-flex justify-content-between align-items-center mb-1 border rounded-3" 
                                    data-id="${p.id}" data-name="${p.name}" data-sku="${p.sku || ''}" data-has-variations="${hasVar}">
                                    <div>
                                        <div class="fw-bold text-dark">${p.name}</div>
                                        <div class="small text-muted">SKU: ${p.sku || '-'}</div>
                                    </div>
                                    ${badge}
                                    </button>`;
                            });
                        }
                        $('#productListResults').html(html);
                    });
                }, 300); // Debounce search
            });

            // Global temporary storage for selected product in first step
            let selectedProductTemp = null;

            $(document).on('click', '.select-product', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const sku = $(this).data('sku');
                const hasVariations = $(this).data('has-variations') == 1;
                
                selectedProductTemp = { id, name, sku };

                if (hasVariations) {
                    // Open variation modal
                    $('#productPickerModal').modal('hide');
                    $('#variationModalTitle').html(`Select Option for <span class="text-primary">${name}</span>`);
                    $('#variationPickerModal').modal('show');
                    
                    // Fetch variations
                    $('#variationList').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><div class="mt-2 text-muted">Loading variations...</div></div>');
                    
                    $.get(`/erp/products/${id}/variations-list`, function(variations) {
                        if (variations.length === 0) {
                            // Fallback if no variations return but flag said true
                            addProductToTable(id, name, sku, null, '', null);
                            $('#variationPickerModal').modal('hide');
                            return;
                        }
                        
                        let html = '';
                        variations.forEach(v => {
                             const priceDisplay = v.has_discount ? 
                                 `<span class="text-decoration-line-through text-muted small me-1">${v.base_price}</span> <span class="fw-bold text-dark">${v.price}</span>` : 
                                 `<span class="fw-bold">${v.price}</span>`;
                                 
                             const stockColor = v.stock > 0 ? 'success' : 'danger';
                             const stockText = v.stock > 0 ? `${v.stock} in stock` : 'Out of Stock';
                             const disabled = v.stock <= 0 ? 'disabled style="opacity: 0.6;"' : '';
                             
                             html += `<button type="button" class="list-group-item list-group-item-action select-variation mb-1 border rounded-3"
                                 data-vid="${v.id}" data-vname="${v.display_name}" data-price="${v.price}" data-stock="${v.stock}" ${disabled}>
                                 <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-dark fw-bold">${v.name || v.display_name}</h6> 
                                        <div class="small text-muted">SKU: ${v.sku || '-'}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="mb-1">${priceDisplay}</div>
                                        <div class="badge bg-${stockColor} bg-opacity-10 text-${stockColor} rounded-pill">${stockText}</div>
                                    </div>
                                 </div>
                             </button>`;
                        });
                        $('#variationList').html(html);
                    });
                    
                } else {
                    // Direct add
                    addProductToTable(id, name, sku, null, '', null);
                    // For direct products, we can opt to keep it open or close. 
                    const $btn = $(this);
                    const originalHtml = $btn.html();
                    $btn.html('<i class="fas fa-check text-success"></i> Added').prop('disabled', true);
                    setTimeout(() => {
                        $btn.html(originalHtml).prop('disabled', false);
                    }, 1000);
                }
            });
            
            $(document).on('click', '.select-variation', function() {
                const vid = $(this).data('vid');
                const vname = $(this).data('vname');
                const price = $(this).data('price');
                const stock = $(this).data('stock');
                const $btn = $(this);
                
                if (selectedProductTemp) {
                    addProductToTable(selectedProductTemp.id, selectedProductTemp.name, selectedProductTemp.sku, vid, price, vname, stock);
                    
                    // Visual feedback
                    $btn.addClass('bg-success bg-opacity-10 border-success');
                }
            });

            function addProductToTable(id, name, sku, variationId, priceOverride, variationName, maxStock) {
                // Check if already exists in table
                let existingRow = null;
                $newItemsBody.find('tr').each(function() {
                    const pid = $(this).find('input[name*="[product_id]"]').val();
                    const vid = $(this).find('input[name*="[variation_id]"]').val();
                    // Handle potential null/empty values comparison
                    const rowVid = vid || '';
                    const newVid = variationId || '';
                    
                    if (pid == id && rowVid == newVid) {
                        existingRow = $(this);
                        return false;
                    }
                });

                if (existingRow) {
                    const $qtyInput = existingRow.find('.new-qty');
                    let currentQty = parseFloat($qtyInput.val()) || 0;
                    
                    // Determine max stock to check against
                    // If maxStock is passed (e.g. from variation click), use it
                    // Otherwise check the data-max attribute on the input
                    let limit = maxStock;
                    if (limit === undefined || limit === null) {
                        limit = $qtyInput.data('max');
                    }

                    // Check limit if it exists
                    if (limit !== undefined && limit !== null && limit !== '') {
                        limit = parseFloat(limit);
                        if (currentQty >= limit) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'warning',
                                title: `Max stock (${limit}) reached`,
                                showConfirmButton: false,
                                timer: 1500
                            });
                             // Highlight row even on failure to show where it is
                            existingRow.addClass('bg-danger bg-opacity-10');
                            setTimeout(() => existingRow.removeClass('bg-danger bg-opacity-10'), 300);
                            return; 
                        }
                    }

                    // Increment
                    $qtyInput.val(currentQty + 1).trigger('input');
                    
                    // Visual feedback
                    existingRow.addClass('bg-success bg-opacity-10');
                    setTimeout(() => existingRow.removeClass('bg-success bg-opacity-10'), 300);
                    return;
                }

                const index = $newItemsBody.find('tr').length;
                
                // If priceOverride is provided, use it. Otherwise fetch sale price
                if (priceOverride !== '' && priceOverride !== null) {
                    appendRow(id, name, sku, variationId, priceOverride, index, variationName, maxStock);
                } else {
                    $.get(`/erp/products/${id}/sale-price`, function(priceRes) {
                        const price = priceRes.price || 0;
                        const stock = priceRes.stock || 0;
                        appendRow(id, name, sku, variationId, price, index, variationName, stock);
                    });
                }
            }

            function appendRow(id, name, sku, variationId, price, index, variationName, maxStock) {
                // Determine display name: Product Name <br> Variation Name
                const displayVar = variationName ? `<div class="badge bg-light text-dark border align-middle mt-1">${variationName}</div>` : '';
                const maxAttr = maxStock ? `max="${maxStock}"` : '';
                const stockData = maxStock ? `data-max="${maxStock}"` : '';
                const stockInfo = maxStock ? `<div class="text-xs text-muted mt-1 stock-info">Max: ${maxStock}</div>` : '';

                const row = `
                    <tr>
                        <td class="ps-3 py-3">
                            <div class="fw-bold text-dark">${name}</div>
                            ${displayVar}
                            <div class="small text-muted mt-1">SKU: ${sku}</div>
                            <input type="hidden" name="new_items[${index}][product_id]" value="${id}">
                            <input type="hidden" name="new_items[${index}][variation_id]" value="${variationId || ''}">
                        </td>
                        <td class="align-middle text-center py-3">
                            <input type="number" name="new_items[${index}][qty]" class="form-control text-center new-qty" value="1" min="1" ${maxAttr} ${stockData} style="max-width: 100px; margin: 0 auto;">
                            ${stockInfo}
                        </td>
                        <td class="align-middle text-end py-3">
                            <input type="number" name="new_items[${index}][unit_price]" class="form-control text-end new-price" value="${price}" style="max-width: 120px; margin-left: auto;">
                        </td>
                        <td class="text-end fw-bold align-middle py-3 row-new-total">${price}</td>
                        <td class="align-middle py-3 text-center"><button type="button" class="btn btn-sm btn-link text-danger remove-item p-0"><i class="fas fa-times"></i></button></td>
                    </tr>
                `;
                $newItemsBody.append(row);
                calculateAll();
            }

            $(document).on('input', '.new-qty, .new-price, #discountInput', function() {
                const $row = $(this).closest('tr');
                const $qtyInput = $row.find('.new-qty');
                const qty = parseFloat($qtyInput.val()) || 0;
                const price = parseFloat($row.find('.new-price').val()) || 0;
                const max = parseFloat($qtyInput.data('max'));

                if (max && qty > max) {
                    $qtyInput.addClass('is-invalid');
                    // Optional: Validation feedback
                    if ($row.find('.invalid-feedback').length === 0) {
                        $row.find('.stock-info').addClass('text-danger').removeClass('text-muted').text(`Max Limit: ${max}`);
                    }
                } else {
                    $qtyInput.removeClass('is-invalid');
                    if(max) {
                         $row.find('.stock-info').removeClass('text-danger').addClass('text-muted').text(`Max: ${max}`);
                    }
                }

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
                    let val = parseFloat($(this).data('net'));
                    if(isNaN(val)) val = parseFloat($(this).text()) || 0;
                    totalReturn += val;
                });
                
                let totalPurchase = 0;
                $('.row-new-total').each(function() {
                    totalPurchase += parseFloat($(this).text()) || 0;
                });

                let discount = parseFloat($('#discountInput').val()) || 0;
                
                // Logic: Net = Purchase - Return - Discount
                const net = totalPurchase - totalReturn - discount;
                
                // Update text
                $('#totalReturnValue').text(totalReturn.toFixed(2));
                $('#totalPurchaseValue').text(totalPurchase.toFixed(2));
                $('#summaryReturn').text(totalReturn.toFixed(2));
                $('#summaryPurchase').text(totalPurchase.toFixed(2));
                
                const returnCredit = totalReturn;
                const grandTotal = totalPurchase;
                
                if (net < 0) {
                    // Credit due to customer
                    const creditAmount = Math.abs(net);
                    $('#netAmount').html(`<span class="text-success">Refund: ${creditAmount.toFixed(2)}</span>`);
                    $('#paidInput').val(0);
                    $('#paymentStatusText').text('Customer receives change');
                } else {
                    // Customer needs to pay
                    $('#netAmount').html(`${net.toFixed(2)}`);
                    $('#paidInput').val(net.toFixed(2));
                     $('#paymentStatusText').text('Customer pays difference');
                }
            }

            $('#exchangeForm').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $form.find('button[type="submit"]');
                
                // Validation: Must have return OR new items? Not strictly, but typically yes for exchange
                // Let's allow flexibility but warn if empty
                if ($newItemsBody.find('tr').length === 0 && $returnItemsBody.find('.return-qty').filter((i, el) => $(el).val() > 0).length === 0) {
                    Swal.fire('Warning', 'Please select items to return or items to buy.', 'warning');
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
                
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: 'Success', 
                                text: res.message, 
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = res.redirect;
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Complete Exchange');
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Calculation error or server error', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Complete Exchange');
                    }
                });
            });
        });
    </script>
@endsection
