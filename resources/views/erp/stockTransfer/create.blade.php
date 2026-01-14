@extends('erp.master')

@section('title', 'Create Stock Transfer')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            :root {
                --primary-teal: #17a2b8;
                --primary-hover: #138496;
                --danger-red: #dc3545;
                --danger-hover: #c82333;
                --gray-light: #f9fafb;
                --border-color: #d1d5db;
                --text-main: #1f2937;
                --text-muted: #6b7280;
            }

            .purchase-card { 
                background: #fff;
                border-radius: 6px; 
                border: 1px solid #e5e7eb; 
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); 
            }
            .purchase-card-header { 
                background: #fff; 
                border-bottom: 1px solid #f3f4f6; 
                padding: 1.25rem 1.5rem; 
                font-weight: 700; 
                color: var(--text-main);
                font-size: 1.1rem;
            }
            .form-label { 
                font-size: 0.85rem; 
                font-weight: 700; 
                color: #374151; 
                margin-bottom: 0.5rem; 
            }
            .form-control, .form-select { 
                border-radius: 4px; 
                border: 1px solid var(--border-color); 
                font-size: 0.9rem; 
                padding: 0.6rem 0.75rem;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }
            .form-control:focus, .form-select:focus { 
                border-color: var(--primary-teal); 
                box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1); 
                outline: 0;
            }
            
            #productTable thead th { 
                background: var(--gray-light); 
                color: var(--text-muted); 
                font-size: 0.7rem; 
                font-weight: 700; 
                text-transform: uppercase; 
                letter-spacing: 0.05em; 
                padding: 0.75rem 0.5rem; 
                border-bottom: 2px solid #f3f4f6; 
            }
            #productTable tbody td { 
                padding: 0.75rem 0.5rem; 
                border-bottom: 1px solid #f3f4f6; 
                font-size: 0.85rem; 
                vertical-align: middle;
            }
            
            .summary-label { 
                font-size: 0.95rem; 
                font-weight: 700; 
                color: var(--text-main); 
                text-align: right; 
                padding-right: 1.5rem; 
            }
            .summary-input { 
                background: var(--gray-light) !important; 
                font-weight: 700; 
                border-color: #e5e7eb; 
                text-align: right; 
                font-size: 1rem;
            }

            .select2-container--default .select2-selection--single { 
                border: 1px solid var(--border-color); 
                border-radius: 4px; 
                height: 42px; 
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered { 
                line-height: 40px; 
                font-size: 0.9rem; 
                color: #374151; 
                padding-left: 0.75rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
        </style>

        <div class="container-fluid px-4 py-4 bg-white border-bottom mb-4">
            <h2 class="fw-bold mb-0" style="font-size: 26px; color: var(--text-main);">Stock Transfer</h2>
        </div>
        
        <div class="container-fluid px-4 pb-5">
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
            @endif

            <!-- Transfer Form -->
            <form action="{{ route('stocktransfer.store') }}" method="POST" id="transferForm">
                @csrf
                
                <div class="card purchase-card border-0">
                    <div class="purchase-card-header">
                        Transfer Information
                    </div>
                    <div class="card-body p-4">
                        <!-- Top Row -->
                        <div class="row g-4 mb-4">
                            <!-- Transfer Date -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Receiver Outlet -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Receiver Outlet <span class="text-danger">*</span></label>
                                <select name="to_outlet" id="to_outlet" class="form-select" required>
                                    <option value="">Select One</option>
                                    @foreach($branches as $branch)
                                        <option value="branch_{{ $branch->id }}">{{ $branch->name }} (Branch)</option>
                                    @endforeach
                                    @foreach($warehouses as $warehouse)
                                        <option value="warehouse_{{ $warehouse->id }}">{{ $warehouse->name }} (Warehouse)</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select Style Number -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Select Style Number <span class="text-danger">*</span></label>
                                <select name="style_number" id="style_number" class="form-select" required>
                                    <option value="">Select One</option>
                                </select>
                            </div>
                        </div>

                        <!-- Product Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle mb-0" id="productTable">
                                <thead>
                                    <tr>
                                        <th class="small">Image</th>
                                        <th class="small">Category</th>
                                        <th class="small">Brand</th>
                                        <th class="small">Season</th>
                                        <th class="small">Gender</th>
                                        <th class="small">Product</th>
                                        <th class="small">Style Number</th>
                                        <th class="small">Size</th>
                                        <th class="small">Color</th>
                                        <th class="small">Stock</th>
                                        <th class="small">Transfer Quantity</th>
                                        <th class="small">Unit</th>
                                        <th class="small">Unit Price</th>
                                        <th class="small">Total Price</th>
                                        <th class="small">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody">
                                    <!-- Products will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Bottom Row -->
                        <div class="row g-4 mb-4">
                    <!-- Total Amount -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Total Amount</label>
                        <input type="number" step="0.01" id="total_amount" class="form-control bg-light" readonly value="0">
                    </div>
                

                    <!-- Paid Amount -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Paid Amount</label>
                        <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="0">
                    </div>

                    <!-- Due Amount -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Due Amount</label>
                        <input type="number" step="0.01" id="due_amount" class="form-control bg-light" readonly value="0">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Sender Account Type -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Sender Account Type <span class="text-danger">*</span></label>
                        <select name="sender_account_type" class="form-select" required>
                            <option value="">Select Account Type</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>

                    <!-- Sender Account Number -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Sender Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="sender_account_number" class="form-control" placeholder="Select Account Type First" required>
                    </div>

                    <!-- Receiver Account Type -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Receiver Account Type <span class="text-danger">*</span></label>
                        <select name="receiver_account_type" class="form-select" required>
                            <option value="">Select Account Type</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>

                    
                    <!-- Receiver Account Number -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Receiver Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="receiver_account_number" class="form-control" placeholder="Select Account Type First" required>
                    </div>

                    <!-- Note -->
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="If have any note"></textarea>
                    </div>
                </div>
            </div>
        </div>


        <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-check me-2"></i>Submit
                    </button>
                    <a href="{{ route('stocktransfer.list') }}" class="btn btn-danger px-5">
                        <i class="fas fa-times me-2"></i>Back
                    </a>
                </div>
            </form>
        </div>
    </div>


    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let selectedProducts = [];

            // Initialize Select2 for style number
            $('#style_number').select2({
                placeholder: 'Search by style number...',
                ajax: {
                    url: '/erp/products/search-by-style',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.style_number + ' - ' + item.name,
                                    product: item
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // When style number is selected, load variations
            $('#style_number').on('change', function() {
                const selectedData = $(this).select2('data')[0];
                if (selectedData && selectedData.product) {
                    loadProductVariations(selectedData.product);
                }
            });

            function loadProductVariations(product) {
                $.ajax({
                    url: '/erp/products/' + product.id + '/variations-with-stock',
                    type: 'GET',
                    success: function(variations) {
                        if (variations && variations.length > 0) {
                            variations.forEach(function(variation) {
                                addProductRow(product, variation);
                            });
                        } else {
                            // If no variations, add the product itself
                            addProductRow(product, null);
                        }
                    },
                    error: function() {
                        alert('Error loading product variations');
                    }
                });
            }

            function addProductRow(product, variation) {
                const rowId = variation ? `var_${variation.id}` : `prod_${product.id}`;
                
                // Check if already added
                if ($(`#${rowId}`).length > 0) {
                    return;
                }

                const stock = variation ? (variation.stock || 0) : (product.stock || 0);
                const unitPrice = variation ? 
                    (variation.price && variation.price > 0 ? variation.price : (product.mrp || product.price || 0)) : 
                    (product.mrp || product.price || 0);
                
                const row = `
                    <tr id="${rowId}">
                        <td>
                            <img src="${product.image || '/placeholder.png'}" alt="${product.name}" style="width: 40px; height: 40px; object-fit: cover;" class="rounded">
                        </td>
                        <td class="small">${product.category?.name || '-'}</td>
                        <td class="small">${product.brand?.name || '-'}</td>
                        <td class="small">${product.season?.name || '-'}</td>
                        <td class="small">${product.gender?.name || '-'}</td>
                        <td class="small fw-bold">${product.name}</td>
                        <td class="small" style="color: #e83e8c;">${product.style_number || '-'}</td>
                        <td class="small">${variation ? (variation.size || '-') : '-'}</td>
                        <td class="small">${variation ? (variation.color || '-') : '-'}</td>
                        <td class="small fw-bold">${stock}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm transfer-qty" 
                                   data-row-id="${rowId}" 
                                   data-price="${unitPrice}" 
                                   min="0" max="${stock}" value="0" style="width: 80px;">
                            <input type="hidden" name="items[${rowId}][product_id]" value="${product.id}">
                            <input type="hidden" name="items[${rowId}][variation_id]" value="${variation ? variation.id : ''}">
                        </td>
                        <td class="small">${product.unit || 'PCS'}</td>
                        <td class="small">${parseFloat(unitPrice).toFixed(2)}</td>
                        <td class="small fw-bold total-price" data-row-id="${rowId}">0.00</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-row" data-row-id="${rowId}">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#productTableBody').append(row);
            }

            // Calculate total when quantity changes
            $(document).on('input', '.transfer-qty', function() {
                const rowId = $(this).data('row-id');
                let qty = parseFloat($(this).val()) || 0;
                const maxStock = parseFloat($(this).attr('max')) || 0;
                const price = parseFloat($(this).data('price')) || 0;
                
                // Validate quantity doesn't exceed stock
                if (qty > maxStock) {
                    $(this).addClass('border-danger');
                    alert(`Cannot transfer more than available stock (${maxStock}). Quantity has been adjusted.`);
                    qty = maxStock;
                    $(this).val(maxStock);
                } else if (qty < 0) {
                    qty = 0;
                    $(this).val(0);
                } else {
                    $(this).removeClass('border-danger');
                }
                
                const total = qty * price;
                
                $(`.total-price[data-row-id="${rowId}"]`).text(total.toFixed(2));
                
                // Update hidden input for quantity
                $(`input[name="items[${rowId}][quantity]"]`).remove();
                $(this).after(`<input type="hidden" name="items[${rowId}][quantity]" value="${qty}">`);
                
                // Store max stock as data attribute for backend validation
                $(`input[name="items[${rowId}][max_stock]"]`).remove();
                $(this).after(`<input type="hidden" name="items[${rowId}][max_stock]" value="${maxStock}">`);
                
                updateTotals();
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                const rowId = $(this).data('row-id');
                $(`#${rowId}`).remove();
                updateTotals();
            });

            // Update paid amount
            $('#paid_amount').on('input', function() {
                updateTotals();
            });

            function updateTotals() {
                let totalAmount = 0;
                $('.total-price').each(function() {
                    totalAmount += parseFloat($(this).text()) || 0;
                });
                
                const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                const dueAmount = totalAmount - paidAmount;
                
                $('#total_amount').val(totalAmount.toFixed(2));
                $('#due_amount').val(dueAmount.toFixed(2));
            }

            // Form submission validation
            $('#transferForm').on('submit', function(e) {
                if ($('#productTableBody tr').length === 0) {
                    e.preventDefault();
                    alert('Please add at least one product to transfer');
                    return false;
                }
                
                let hasQuantity = false;
                let hasError = false;
                
                $('.transfer-qty').each(function() {
                    const qty = parseFloat($(this).val()) || 0;
                    const maxStock = parseFloat($(this).attr('max')) || 0;
                    
                    if (qty > 0) {
                        hasQuantity = true;
                    }
                    
                    // Check if any quantity exceeds stock
                    if (qty > maxStock) {
                        hasError = true;
                        $(this).addClass('border-danger');
                    }
                });
                
                if (!hasQuantity) {
                    e.preventDefault();
                    alert('Please enter transfer quantity for at least one product');
                    return false;
                }
                
                if (hasError) {
                    e.preventDefault();
                    alert('Some quantities exceed available stock. Please correct the highlighted fields.');
                    return false;
                }
            });
        });
    </script>
    @endpush
@endsection
