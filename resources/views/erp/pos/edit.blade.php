@extends('erp.master')

@section('title', 'Edit POS Sale - ' . $pos->sale_number)

@section('body')
@include('erp.components.sidebar')

@push('css')
    <link href="{{ asset('pos-premium.css') }}?v={{ time() }}" rel="stylesheet">
    <style>
        .main-content { overflow: hidden; height: 100vh; }
        .cart-table-container { flex-grow: 1; overflow-y: auto; background: #f8fafc; }
        .terminal-sidebar { width: 35%; display: flex; flex-direction: column; background: #fff; border-left: 1px solid #e2e8f0; }
        .products-panel { width: 65%; display: flex; flex-direction: column; }
    </style>
@endpush

<div class="main-content d-flex flex-column" id="mainContent">
    @include('erp.components.header')

    <div class="container-fluid px-0 flex-grow-1 position-relative" style="overflow: hidden;">
        <div class="d-flex h-100">
            <!-- Left Side: Product Selection (65%) -->
            <div class="products-panel h-100 border-end">
                <!-- Search & Filters -->
                <div class="bg-white p-3 border-bottom shadow-sm z-1">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text border-end-0"> <i class="fas fa-search"></i> </span>
                                <input type="text" id="searchInput" class="form-control border-start-0 premium-search-input" placeholder="Search product name or SKU...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text border-end-0 bg-success bg-opacity-10 text-success"> <i class="fas fa-barcode"></i> </span>
                                <input type="text" id="barcodeInput" class="form-control border-start-0 premium-search-input scanner-input" placeholder="Scan Barcode (F2)" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text border-end-0"> <i class="fas fa-list-ul"></i> </span>
                                <select class="form-select border-start-0 select2-simple" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->full_path_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Filters Row 2 -->
                    <div class="d-flex gap-3 mt-3 align-items-center flex-wrap">
                        <div class="input-group input-group-sm w-auto shadow-sm">
                            <span class="input-group-text border-end-0"> <i class="fas fa-store-alt"></i> </span>
                            <select class="form-select border-start-0 fw-bold branch-select-field" id="branchFilter">
                                 @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $pos->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sale-type-toggle">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saleType" id="retailPrice" value="MRP" {{ $pos->sale_type == 'MRP' ? 'checked' : '' }}>
                                <label class="form-check-label" for="retailPrice">Retail (MRP)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="saleType" id="wholesalePrice" value="Wholesale" {{ $pos->sale_type == 'Wholesale' ? 'checked' : '' }}>
                                <label class="form-check-label" for="wholesalePrice">Wholesale</label>
                            </div>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-primary px-3 py-2">Editing Sale: {{ $pos->sale_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-grow-1 overflow-auto p-3 bg-light" id="productsContainer">
                    <div class="row row-cols-2 row-cols-md-4 row-cols-xl-5 g-3" id="productsGrid">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Loading products...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: POS Terminal (35%) -->
            <div class="terminal-sidebar h-100 shadow-lg z-2">
                <!-- 1. Customer Selection -->
                <div class="p-2 px-3 border-bottom terminal-header">
                     <div class="terminal-section-title mb-1">Customer Information</div>
                     <div class="input-group input-group-sm">
                        <select class="form-select select2-customer" id="customerSelect">
                            <option value="walk-in" {{ !$pos->customer_id ? 'selected' : '' }}>Generic Walk-in Customer</option>
                             @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $pos->customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->phone }})
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn-success px-3" type="button" title="Add New Customer" data-bs-toggle="modal" data-bs-target="#quickAddCustomerModal">
                            <i class="fas fa-user-plus"></i>
                        </button>
                     </div>
                </div>

                <!-- 2. Cart Items -->
                <div class="cart-table-container p-3">
                    <div class="cart-header d-flex justify-content-between mb-2">
                        <span class="fw-bold text-muted small">Items List</span>
                        <span id="cartCount" class="badge bg-secondary">0 Items</span>
                    </div>
                    <table class="table table-hover mb-0 cart-table">
                        <tbody id="cartTableBody">
                             <tr id="emptyCartMessage">
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="mb-3"><i class="fas fa-shopping-basket fa-3x opacity-10"></i></div>
                                    <p class="mb-0 fw-bold">Cart is empty</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 3. Checkout Calculations -->
                <div class="p-3 bg-white border-top shadow-up">
                    <form id="posForm" action="{{ route('pos.update', $pos->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="branch_id" id="hiddenBranchId" value="{{ $pos->branch_id }}">
                        <input type="hidden" name="customer_id" id="hiddenCustomerId" value="{{ $pos->customer_id ?? 'walk-in' }}">
                        <input type="hidden" name="sale_type" id="hiddenSaleType" value="{{ $pos->sale_type }}">

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="terminal-section-title d-block mb-1">Discount</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control border-start-0 text-end fw-bold" id="discountInput" name="discount" value="{{ $pos->discount }}" placeholder="0 or 10%">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="terminal-section-title d-block mb-1">Shipping</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-shipping-fast"></i></span>
                                    <input type="number" class="form-control border-start-0 text-end fw-bold" id="deliveryInput" name="delivery" value="{{ $pos->delivery }}">
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                            <div class="d-flex justify-content-between border-top pt-1 mt-1">
                                <span class="terminal-section-title m-0 text-primary opacity-75">Payable</span>
                                <span class="h4 mb-0 fw-bold text-primary" id="finalTotalDisplay">0.00</span>
                            </div>
                        </div>

                        <!-- Payment & Account -->
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <div class="btn-group btn-group-sm w-100 payment-method-toggle" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="payCash">Cash</label>
                                    <input type="radio" class="btn-check" name="payment_method" id="payMobile" value="mobile" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="payMobile">Mobile</label>
                                    <input type="radio" class="btn-check" name="payment_method" id="payBank" value="bank" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="payBank">Bank</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <select class="form-select form-select-sm fw-bold" id="accountSelect" name="account_id">
                                     @foreach($bankAccounts as $acc)
                                         <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                             {{ $acc->provider_name }} ({{ $acc->mobile_number ?? $acc->account_number ?? '...' }})
                                         </option>
                                     @endforeach
                                 </select>
                            </div>
                            <div class="col-12">
                                <select class="form-select form-select-sm" name="courier_id" id="courierSelect">
                                    <option value="">No Courier / In-Store</option>
                                    @foreach($shippingMethods as $method)
                                        <option value="{{ $method->id }}" {{ $pos->courier_id == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="summary-card mb-3 p-2 bg-light rounded border">
                             <div class="d-flex justify-content-between align-items-center mb-1">
                                 <label class="terminal-section-title h-auto m-0">Paid (৳)</label>
                                 <button class="btn btn-link btn-sm p-0 text-success text-decoration-none extra-small fw-bold" type="button" onclick="setExactAmount()">EXACT</button>
                             </div>
                             <input type="number" class="form-control form-control-sm text-end fw-bold" id="paidAmountInput" name="paid_amount" value="{{ $pos->invoice->paid_amount ?? 0 }}">
                             <div class="d-flex justify-content-between border-top pt-1 mt-1" id="changeRow">
                                <span class="terminal-section-title m-0" id="changeLabel" style="font-size: 0.7rem;">Due Amount</span>
                                <span class="fw-bold text-danger" id="changeDisplay" style="font-size: 0.9rem;">0.00</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 shadow" id="completeOrderBtn">
                            <i class="fas fa-save me-2"></i>UPDATE SALE
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="variationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vModalTitle">Select Variation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"><div id="vModalOptions" class="d-flex flex-wrap gap-2 justify-content-center"></div></div>
        </div>
    </div>
</div>

<!-- JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // 1. Init
    $('#customerSelect, #categoryFilter').select2({ width: '100%' });
    $('#barcodeInput').focus();

    // 2. State & Load Existing
    @php
        $cartData = $pos->items->map(function($item) use ($pos) {
            $stock = 0;
            if ($item->variation_id) {
                $vStock = \App\Models\ProductVariationStock::where('variation_id', $item->variation_id)
                    ->where('branch_id', $pos->branch_id)
                    ->whereNull('warehouse_id')
                    ->first();
                $stock = $vStock ? ($vStock->available_quantity ?? ($vStock->quantity - ($vStock->reserved_quantity ?? 0))) : 0;
            } else {
                $bStock = \App\Models\BranchProductStock::where('product_id', $item->product_id)
                    ->where('branch_id', $pos->branch_id)
                    ->first();
                $stock = $bStock ? $bStock->quantity : 0;
            }

            return [
                'cartId' => $item->variation_id ? ($item->product_id.'-'.$item->variation_id) : $item->product_id,
                'productId' => $item->product_id,
                'variation_id' => $item->variation_id,
                'name' => $item->product->name . ($item->variation ? ' - '.$item->variation->name : ''),
                'price' => (float)$item->unit_price,
                'mrp_price' => (float)($item->variation_id ? ($item->variation->discount ?? $item->variation->price) : ($item->product->discount ?? $item->product->price)),
                'wholesale_price' => (float)($item->variation_id ? ($item->variation->wholesale_price ?? $item->product->wholesale_price) : $item->product->wholesale_price),
                'qty' => (float)$item->quantity,
                'maxStock' => (float)$stock + (float)$item->quantity
            ];
        });
    @endphp
    let cart = @json($cartData);
    
    let products = [];
    let currentBranch = $('#branchFilter').val();

    loadProducts();
    renderCart();

    // 3. Handlers
    function loadProducts() {
        $('#productsGrid').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');
        $.ajax({
            url: `/erp/products/search-with-filters/${currentBranch}`,
            data: { search: $('#searchInput').val(), category_id: $('#categoryFilter').val() },
            success: function(res) {
                products = res.data || res;
                renderProductGrid(products);
            }
        });
    }

    function renderProductGrid(items) {
        const grid = $('#productsGrid').empty();
        if(!items.length) { grid.html('<div class="col-12 text-center py-5">No products.</div>'); return; }
        items.forEach(p => {
            let price = getPrice(p);
            let stock = p.branch_stock?.quantity || 0;
            grid.append(`
                <div class="col">
                    <div class="card h-100 pos-product-card border-0 shadow-sm" onclick="handleProductClick(${p.id})">
                        <div class="pos-img-box position-relative">
                            ${p.image ? `<img src="/${p.image}">` : '<i class="fas fa-box fa-2x opacity-25"></i>'}
                            ${stock <= 5 ? '<span class="badge bg-danger position-absolute bottom-0 start-0 m-2">Low Stock</span>' : ''}
                        </div>
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title text-truncate small fw-bold mb-1">${p.name}</h6>
                            <div class="fw-bold text-primary">${price}৳</div>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    window.handleProductClick = function(id) {
        const p = products.find(i => i.id === id);
        if(!p) return;
        if(p.has_variations && p.variations?.length) {
            $('#vModalTitle').text('Variation: ' + p.name);
            $('#vModalOptions').empty();
            p.variations.forEach(v => {
                let stock = v.stock || 0;
                $('<button>').addClass('btn btn-outline-dark m-1 ' + (stock <= 0 ? 'disabled' : ''))
                    .html(`${v.name}<br><small>${getPrice(v, p)}৳ | Stock: ${stock}</small>`)
                    .click(() => { if(stock > 0) { addToCart(p, v, stock); $('#variationModal').modal('hide'); } else alert('Out of stock'); })
                    .appendTo('#vModalOptions');
            });
            $('#variationModal').modal('show');
        } else {
            let stock = p.branch_stock?.quantity || 0;
            if(stock > 0) addToCart(p, null, stock);
            else alert('Out of stock');
        }
    };

    function addToCart(product, variation = null, maxStock = 999) {
        let cartId = variation ? `${product.id}-${variation.id}` : `${product.id}`;
        let existing = cart.find(c => c.cartId === cartId);
        if(existing) {
            if(existing.qty + 1 > maxStock) return alert('Stock Limit Reached');
            existing.qty++;
        } else {
            let mrp = variation ? (variation.discount || variation.price || product.discount || product.price) : (product.discount || product.price);
            let wholesale = variation ? (variation.wholesale_price || product.wholesale_price) : product.wholesale_price;
            let currentPrice = $('input[name="saleType"]:checked').val() === 'Wholesale' ? wholesale : mrp;

            cart.push({
                cartId, productId: product.id, variationId: variation?.id || null,
                name: product.name + (variation ? ` - ${variation.name}` : ''),
                price: parseFloat(currentPrice),
                mrp_price: parseFloat(mrp),
                wholesale_price: parseFloat(wholesale),
                qty: 1, maxStock: maxStock
            });
        }
        renderCart();
    }

    function renderCart() {
        const tbody = $('#cartTableBody').empty();
        if(!cart.length) { tbody.html('<tr><td colspan="4" class="text-center py-5 text-muted">Cart empty</td></tr>'); calculateTotals(); return; }
        cart.forEach(item => {
            tbody.append(`
                <tr class="align-middle border-bottom">
                    <td class="ps-2 py-3">
                        <div class="fw-bold text-dark mb-0">${item.name}</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-success bg-opacity-10 text-success border-0 extra-small">${item.price.toFixed(2)}৳</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 extra-small">Stock: ${item.maxStock}</span>
                        </div>
                    </td>
                    <td class="text-center px-0">
                        <div class="d-flex align-items-center justify-content-center bg-light rounded-pill p-1" style="width: fit-content; margin: 0 auto;">
                            <button type="button" class="qty-control border-0 shadow-sm" onclick="updateQty('${item.cartId}', -1)"><i class="fas fa-minus"></i></button>
                            <span class="qty-value mx-2 fw-bold" style="min-width: 25px;">${item.qty}</span>
                            <button type="button" class="qty-control border-0 shadow-sm" onclick="updateQty('${item.cartId}', 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </td>
                    <td class="text-end fw-bold text-dark pe-3 h6 mb-0">${(item.price * item.qty).toFixed(2)}</td>
                    <td class="text-center pe-2">
                        <button type="button" class="btn-remove-item text-danger border-0 bg-transparent p-2" onclick="removeFromCart('${item.cartId}')" title="Remove Item">
                            <i class="fas fa-times-circle fa-lg"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        calculateTotals();
    }

    window.updateQty = (id, d) => { 
        let i = cart.find(c => c.cartId === id); 
        if(i) { 
            let newQty = i.qty + d;
            if(d > 0 && newQty > i.maxStock) return alert('Stock Limit Reached');
            i.qty = newQty;
            if(i.qty <= 0) removeFromCart(id); 
            else renderCart(); 
        } 
    };
    window.removeFromCart = (id) => { 
        if(confirm('Remove this item?')) {
            cart = cart.filter(c => c.cartId !== id); 
            renderCart(); 
        }
    };

    function calculateTotals() {
        let sub = cart.reduce((a, i) => a + (i.price * i.qty), 0);
        let discStr = $('#discountInput').val() || '0';
        let disc = discStr.includes('%') ? (sub * parseFloat(discStr)/100) : parseFloat(discStr);
        let del = parseFloat($('#deliveryInput').val()) || 0;
        let final = Math.round((sub + del) - disc);

        $('#subtotalDisplay').text(sub.toFixed(2) + '৳');
        $('#discountRow').toggle(disc > 0);
        $('#discountAmountDisplay').text(disc.toFixed(2));
        $('#finalTotalDisplay').text(final);
        $('#cartCount').text(cart.length + ' Items');

        // Due / Change calculation
        let paid = Math.round(parseFloat($('#paidAmountInput').val()) || 0);
        let change = paid - final;
        
        if(change >= 0) {
            $('#changeLabel').text('Change').removeClass('text-danger').addClass('text-success');
            $('#changeDisplay').text(change.toFixed(2) + '৳').removeClass('text-danger').addClass('text-success');
        } else {
            $('#changeLabel').text('Due Amount').addClass('text-danger');
            $('#changeDisplay').text('Due: ' + Math.abs(change).toFixed(2) + '৳').removeClass('text-success').addClass('text-danger');
        }
    }

    function getPrice(p, parent = null) {
        let type = $('input[name="saleType"]:checked').val();
        if(type === 'Wholesale') return p.wholesale_price || parent?.wholesale_price || p.price;
        return p.discount || p.price || parent?.discount || parent?.price || 0;
    }

    // Events
    $('#searchInput').on('input', loadProducts);
    $('#categoryFilter, #branchFilter').change(function() { 
        if(this.id === 'branchFilter') currentBranch = $(this).val(); 
        loadProducts(); 
    });
    $('input[name="saleType"]').change(function() {
        let type = $(this).val();
        cart.forEach(item => {
            item.price = type === 'Wholesale' ? item.wholesale_price : item.mrp_price;
        });
        loadProducts();
        renderCart();
    });
    $('#discountInput, #deliveryInput, #paidAmountInput').on('input', calculateTotals);
    $('input[name="payment_method"]').change(function() {
        let type = $(this).val();
        $('#accountSelect option').each(function() { $(this).toggle($(this).data('type') == type); });
        $('#accountSelect option:visible:first').prop('selected', true);
    }).trigger('change');

    window.setExactAmount = () => {
        $('#paidAmountInput').val($('#finalTotalDisplay').text()).trigger('input');
    };

    $('#posForm').submit(function(e) {
        e.preventDefault();
        if(!cart.length) return alert('Cart empty');
        let $btn = $('#completeOrderBtn').prop('disabled', true).text('UPDATING...');
        
        let data = {
            _token: $('input[name="_token"]').val(),
            branch_id: $('#branchFilter').val(),
            customer_id: $('#customerSelect').val() === 'walk-in' ? null : $('#customerSelect').val(),
            sale_date: '{{ $pos->sale_date }}',
            sub_total: cart.reduce((a, i) => a + (i.price * i.qty), 0),
            discount: $('#discountRow:visible').length ? parseFloat($('#discountAmountDisplay').text()) : parseFloat($('#discountInput').val() || 0),
            delivery: parseFloat($('#deliveryInput').val()) || 0,
            total_amount: parseFloat($('#finalTotalDisplay').text()),
            paid_amount: parseFloat($('#paidAmountInput').val()) || 0,
            payment_method: $('input[name="payment_method"]:checked').val(),
            account_id: $('#accountSelect').val(),
            courier_id: $('#courierSelect').val(),
            items: cart.map(i => ({ product_id: i.productId, variation_id: i.variationId, quantity: i.qty, unit_price: i.price }))
        };

        $.ajax({
            url: $(this).attr('action'), method: 'POST', data: data,
            success: (res) => res.success ? (alert('Updated!'), location.href="{{ route('pos.show', $pos->id) }}") : alert(res.message),
            error: (xhr) => alert('Error updating')
        }).always(() => $btn.prop('disabled', false).text('UPDATE SALE'));
    });
});
</script>
@endsection