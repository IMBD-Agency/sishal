@extends('erp.master')
@section('title', 'Manual Sale Create')

@push('css')
<style>
    .manual-sale-wrapper { padding: 1.5rem; background: #f8fafc; min-height: calc(100vh - 70px); }
    .premium-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: #fff; margin-bottom: 1.5rem; }
    .checkout-sidebar { position: sticky; top: 90px; }
    .form-label-premium { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; display: block; }
    .summary-box { background: #f1f5f9; border-radius: 12px; padding: 1.5rem; }
    .grand-total-display { font-size: 1.75rem; font-weight: 800; color: var(--primary-color); }
    .item-table thead th { background: #f8fafc; text-transform: uppercase; font-size: 0.7rem; color: #64748b; padding: 12px; border: none; letter-spacing: 0.5px; }
    .item-table tbody td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    
    .qty-control { display: flex; align-items: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; width: 110px; margin: 0 auto; }
    .qty-btn { width: 32px; height: 32px; border: none; background: #f8fafc; color: #475569; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 0.8rem; }
    .qty-btn:hover { background: #e2e8f0; color: var(--primary-color); }
    .qty-val { flex: 1; text-align: center; border: none; font-weight: 800; font-size: 0.9rem; background: var(--primary-color); color: #fff; height: 32px; width: 40px; }
    
    .variation-select-inline { font-size: 0.8rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #1e293b; width: 100%; cursor: pointer; }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="manual-sale-wrapper">
            <div class="mb-4">
                <h4 class="fw-bold text-dark mb-1">Manual Sale Terminal</h4>
                <span class="text-muted small">Instant search & multi-variation auto-fill</span>
            </div>

            <form id="manualSaleForm">
                @csrf
                <input type="hidden" name="sub_total" id="subtotalInput">
                <input type="hidden" name="total_amount" id="totalAmountInput">

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card premium-card p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label-premium">Invoice No</label>
                                    <input type="text" name="invoice_no" class="form-control bg-light fw-bold" value="{{ $invoiceNo }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-premium">Challan No</label>
                                    <input type="text" name="challan_no" class="form-control bg-light fw-bold" value="{{ $challanNo }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-premium">Date</label>
                                    <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-premium">Customer</label>
                                    <div class="d-flex gap-1">
                                        <div class="flex-grow-1">
                                            <select name="customer_id" id="customerSelect" class="form-select select2-setup">
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name ?: 'Unnamed' }} ({{ $customer->phone ?: 'No Phone' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickCustomerModal" style="height: 38px;"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card premium-card p-4">
                            <label class="form-label-premium">Search Product</label>
                            <select id="styleNumberSelect" class="form-select"></select>
                        </div>

                        <div class="card premium-card overflow-hidden">
                            <div class="table-responsive">
                                <table class="table item-table mb-0" id="cartTable">
                                    <thead>
                                        <tr>
                                            <th width="40">#</th>
                                            <th>Product Details</th>
                                            <th width="180">Variation</th>
                                            <th class="text-end" width="100">Price</th>
                                            <th class="text-center" width="140">Quantity</th>
                                            <th class="text-end" width="110">Total</th>
                                            <th class="text-center" width="100">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="emptyRow"><td colspan="7" class="text-center py-5 text-muted">Scan or search product to start...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="checkout-sidebar">
                            <div class="card premium-card p-4">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">SETTINGS</h6>
                                <div class="mb-3">
                                    <label class="form-label-premium">Branch</label>
                                    <select name="branch_id" id="branchSelect" class="form-select">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label-premium">Type</label>
                                        <select name="sale_type" id="saleType" class="form-select">
                                            <option value="MRP">MRP</option>
                                            <option value="Wholesale">Wholesale</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-premium">Bank</label>
                                        <select name="account_id" class="form-select">
                                            @foreach($bankAccounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->provider_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card premium-card p-4">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">ORDER SUMMARY</h6>
                                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><span class="fw-bold" id="subtotalDisplay">0.00৳</span></div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="text-muted">Discount</span>
                                        <span class="badge bg-info-subtle text-info ms-1 d-none" id="discountAmountBadge" style="font-size: 0.65rem;"></span>
                                    </div>
                                    <input type="text" id="discountInput" class="form-control form-control-sm text-end fw-bold w-25" value="0">
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-4"><span class="text-muted">Delivery</span><input type="number" id="deliveryInput" class="form-control form-control-sm text-end fw-bold w-25" value="0"></div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1"><label class="form-label-premium text-success mb-0">Paid Amount</label><button type="button" onclick="setExactManual()" class="btn btn-link btn-sm text-success p-0 text-decoration-none fw-bold" style="font-size: 0.7rem;">EXACT</button></div>
                                    <input type="number" name="paid_amount" id="paidInput" class="form-control form-control-lg text-end fw-bold text-success border-success" value="0">
                                </div>
                                <div class="d-flex justify-content-between mb-3"><span class="fw-bold small" id="manualDueLabel">DUE BALANCE</span><span class="fw-bold" id="dueDisplay">0.00৳</span></div>
                                
                                <div class="summary-visual-box p-3 border rounded-3 mb-4 bg-white shadow-sm">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold text-dark">Subtotal</span>
                                        <span class="fw-bold text-dark" id="visualSubtotal">0.00৳</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold text-danger">Discount</span>
                                        <span class="fw-bold text-danger" id="visualDiscount">0.00৳</span>
                                    </div>
                                    <hr class="my-2 opacity-10">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">PAYABLE</span>
                                        <span class="fw-bold text-primary" style="font-size: 1.85rem;" id="visualTotal">0</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" id="submitBtn">COMPLETE SALE</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="quickCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 premium-card">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickCustomerForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label-premium">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Name (Optional if Phone exists)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-premium">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Enter Phone (Optional if Name exists)">
                        </div>
                        <div id="customerModalError" class="text-danger small mb-2 d-none"></div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mt-2" id="saveCustomerBtn">SAVE CUSTOMER</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cartItems = [];
    $('.select2-setup').select2({ theme: 'bootstrap-5', width: '100%' });

    $('#styleNumberSelect').select2({
        theme: 'bootstrap-5', width: '100%', placeholder: 'Search Product...',
        ajax: {
            url: "{{ route('products.search.style') }}", dataType: 'json', delay: 250,
            data: p => ({ q: p.term, branch_id: $('#branchSelect').val() }),
            processResults: data => ({ results: data.results.map(p => ({ id: p.id, text: p.text, product: p })) }),
            cache: true
        }
    }).on('select2:select', function(e) {
        const prod = e.params.data.product;
        const type = $('#saleType').val();
        if (prod.has_variations) {
            prod.variations.forEach(v => { if (v.stock > 0) addToCart(prod, v, type); });
        } else {
            if (prod.stock > 0) addToCart(prod, null, type);
            else alert('Out of stock!');
        }
        $(this).val(null).trigger('change');
        renderCart();
    });

    function addToCart(prod, variation, type) {
        const vid = variation ? variation.id : null;
        const existing = cartItems.find(item => item.product_id === prod.id && item.variation_id === vid);
        if (existing) {
            if (existing.quantity < existing.max_stock) {
                existing.quantity++;
                existing.total = existing.unit_price * existing.quantity;
            }
            return;
        }

        const mrp = parseFloat(variation ? (variation.price || prod.price || prod.discount) : (prod.price || prod.discount)) || 0;
        const wholesale = parseFloat(variation ? (variation.wholesale_price || prod.wholesale_price || mrp) : (prod.wholesale_price || mrp)) || 0;
        const currentPrice = (type === 'Wholesale') ? wholesale : mrp;

        cartItems.push({
            product_id: prod.id, 
            product_name: prod.name || prod.text.split(' [')[0], 
            style_no: variation ? variation.sku : prod.sku, 
            variation_id: vid,
            unit_price: currentPrice, 
            mrp_price: mrp,
            wholesale_price: wholesale,
            quantity: 1, 
            max_stock: variation ? (variation.stock || 0) : (prod.stock || 0), 
            total: currentPrice, 
            raw_product: prod
        });
    }

    function renderCart() {
        const $tbody = $('#cartTable tbody').empty();
        if (!cartItems.length) { $tbody.append('<tr id="emptyRow"><td colspan="7" class="text-center py-5 text-muted">Scan or search product to start...</td></tr>'); updateTotals(0); return; }
        let sub = 0;
        cartItems.forEach((it, idx) => {
            sub += it.total;
            let varHtml = `<span class="badge bg-light text-dark border p-2">Standard</span>`;
            if (it.raw_product.has_variations) {
                varHtml = `<select class="variation-select-inline" onchange="changeVariation(${idx}, this.value)">`;
                it.raw_product.variations.forEach(v => { varHtml += `<option value="${v.id}" ${v.id == it.variation_id ? 'selected' : ''} ${v.stock <= 0 && v.id != it.variation_id ? 'disabled' : ''}>${v.name} (${v.stock})</option>`; });
                varHtml += `</select>`;
            }
            $tbody.append(`<tr><td class="small text-muted">${idx+1}</td><td><div class="fw-bold">${it.style_no}</div><div class="extra-small text-muted">${it.product_name}</div></td><td>${varHtml}</td><td class="text-end fw-bold text-primary">${it.unit_price.toFixed(2)}</td><td><div class="qty-control"><button type="button" class="qty-btn" onclick="updateItemQty(${idx},-1)"><i class="fas fa-minus fa-xs"></i></button><input type="text" class="qty-val" value="${it.quantity}"><button type="button" class="qty-btn" onclick="updateItemQty(${idx},1)"><i class="fas fa-plus fa-xs"></i></button></div></td><td class="text-end fw-bold">${it.total.toFixed(2)}</td><td class="text-center"><button type="button" class="btn btn-link text-primary p-0 me-2" onclick="duplicateItem(${idx})"><i class="fas fa-copy"></i></button><button type="button" class="btn btn-link text-danger p-0" onclick="removeItem(${idx})"><i class="fas fa-trash-alt"></i></button></td></tr>`);
        });
        updateTotals(sub);
    }

    window.changeVariation = (idx, newVid) => {
        const item = cartItems[idx];
        const v = item.raw_product.variations.find(v => v.id == newVid);
        if (v) {
            item.variation_id = v.id; item.style_no = v.sku; item.max_stock = v.stock; if (item.quantity > v.stock) item.quantity = v.stock;
            const type = $('#saleType').val();
            item.unit_price = (type === 'Wholesale') ? (v.wholesale_price || item.raw_product.wholesale_price) : (v.price || item.raw_product.price);
            item.total = item.unit_price * item.quantity;
            renderCart();
        }
    };
    window.duplicateItem = idx => { cartItems.push(JSON.parse(JSON.stringify(cartItems[idx]))); renderCart(); };
    window.updateItemQty = (idx, d) => { const it = cartItems[idx]; const n = it.quantity + d; if (n > 0 && n <= it.max_stock) { it.quantity = n; it.total = it.unit_price * n; renderCart(); } };
    window.removeItem = idx => { cartItems.splice(idx, 1); renderCart(); };

    function updateTotals(sub = null) {
        if (sub === null) sub = cartItems.reduce((a, i) => a + i.total, 0);
        const discStr = $('#discountInput').val() || '0';
        let disc = 0;
        if (discStr.toString().includes('%')) {
            disc = (sub * parseFloat(discStr)/100);
            $('#discountAmountBadge').text(`-${disc.toFixed(2)}৳`).removeClass('d-none');
        } else {
            disc = parseFloat(discStr) || 0;
            $('#discountAmountBadge').addClass('d-none');
        }
        const del = parseFloat($('#deliveryInput').val()) || 0;
        const total = Math.round(sub - disc + del);
        const paid = Math.round(parseFloat($('#paidInput').val()) || 0);
        const due = total - paid;

        // Update Visual Summary Box
        $('#visualSubtotal').text(sub.toFixed(2) + '৳');
        $('#visualDiscount').text(`- ${disc.toFixed(2)}৳`);
        $('#visualTotal').text(total);

        $('#subtotalDisplay').text(sub.toFixed(2) + '৳'); 
        $('#subtotalInput').val(sub.toFixed(2)); $('#totalAmountInput').val(total);
        if (due <= 0) { $('#manualDueLabel').text('CHANGE').removeClass('text-danger').addClass('text-success'); $('#dueDisplay').text(Math.abs(due).toFixed(2) + '৳').removeClass('text-danger').addClass('text-success'); }
        else { $('#manualDueLabel').text('DUE BALANCE').removeClass('text-success').addClass('text-danger'); $('#dueDisplay').text(due.toFixed(2) + '৳').removeClass('text-success').addClass('text-danger'); }
    }
    $('#discountInput, #deliveryInput, #paidInput').on('input', () => updateTotals());
    window.setExactManual = () => $('#paidInput').val($('#totalAmountInput').val()).trigger('input');

    $('#manualSaleForm').on('submit', function(e) {
        e.preventDefault();
        if (!$('#customerSelect').val()) return alert('Please select a customer first.');
        if (!cartItems.length) return alert('Add items first.');
        const fd = new FormData(this);
        cartItems.forEach((it, i) => { fd.append(`items[${i}][product_id]`, it.product_id); fd.append(`items[${i}][variation_id]`, it.variation_id || ''); fd.append(`items[${i}][quantity]`, it.quantity); fd.append(`items[${i}][unit_price]`, it.unit_price); });
        const $btn = $('#submitBtn').prop('disabled', true).text('PROCESSING...');
        $.ajax({
            url: "{{ route('pos.manual.store') }}", method: 'POST', data: fd, processData: false, contentType: false,
            success: res => res.success ? window.location.href = "{{ route('pos.list') }}" : alert(res.message),
            error: xhr => alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error'),
            complete: () => $btn.prop('disabled', false).text('COMPLETE SALE')
        });
    });

    $('#quickCustomerForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#saveCustomerBtn').prop('disabled', true).text('SAVING...');
        const $err = $('#customerModalError').addClass('d-none');
        
        $.ajax({
            url: "{{ route('customers.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: res => {
                if (res.success && res.customer) {
                    const c = res.customer;
                    const label = (c.name || 'Unnamed') + ' (' + (c.phone || 'No Phone') + ')';
                    $('#customerSelect').append(new Option(label, c.id, true, true)).trigger('change');
                    $('#quickCustomerModal').modal('hide');
                    $('#quickCustomerForm')[0].reset();
                } else {
                    $err.text(res.message || 'Unknown error').removeClass('d-none');
                }
            },
            error: xhr => {
                let msg = 'Validation Error';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $err.text(msg).removeClass('d-none');
            },
            complete: () => $btn.prop('disabled', false).text('SAVE CUSTOMER')
        });
    });
});
</script>
@endpush
