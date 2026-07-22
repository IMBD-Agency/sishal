@extends('erp.master')

@section('title', 'Create Combo')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('erp.combo-products.index') }}" class="text-decoration-none text-muted">Manage Combos</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Create Combo</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Create New Combo</h4>
                <p class="text-muted small mb-0">Bundle existing products together</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('erp.combo-products.index') }}" class="btn btn-light border px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Combos
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <form action="{{ route('erp.combo-products.store') }}" method="POST" id="comboForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <!-- Left Column: Combo Details -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-info-circle me-2 text-primary"></i>Combo Details</h6>
                        </div>
                        <div class="card-body p-4">
                            @if ($errors->any())
                                <div class="alert alert-danger mb-4 border-0 bg-danger bg-opacity-10 text-danger">
                                    <ul class="mb-0 small fw-bold">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold small text-uppercase">Combo Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Summer Special Bundle">
                            </div>

                            <div class="mb-3">
                                <label for="sku" class="form-label fw-bold small text-uppercase">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sku" name="sku" required placeholder="e.g., COMBO-SUMMER-001">
                            </div>

                            <div class="mb-3">
                                <label for="discount_percent" class="form-label fw-bold small text-uppercase">Discount Percentage (Optional)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="discount_percent" name="discount_percent" min="0" max="100" step="0.1" placeholder="e.g., 20" oninput="calculateComboPrice()">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Leave empty to set manual price</small>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label fw-bold small text-uppercase">Combo Price (Manual)</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" placeholder="Auto-calculated if discount set" oninput="clearDiscount()">
                                </div>
                                <small class="text-muted">Auto-calculated from discount if set</small>
                            </div>

                            @if(!$userBranchId)
                            {{-- Only show location selector for global users --}}
                            <div class="mb-3">
                                <label for="location_id" class="form-label fw-bold small text-uppercase">Check Stock at Location</label>
                                <select class="form-select" id="location_id" onchange="filterByLocationStock()">
                                    <option value="">All Locations (Total Stock)</option>
                                    <optgroup label="Branches">
                                        @foreach($branches as $branch)
                                            <option value="branch_{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Warehouses">
                                        @foreach($warehouses as $warehouse)
                                            <option value="warehouse_{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <input type="hidden" id="branch_id" name="branch_id" value="">
                                <input type="hidden" id="warehouse_id" name="warehouse_id" value="">
                                <small class="text-muted">Select branch or warehouse to check stock availability</small>
                            </div>
                            @else
                            {{-- Branch user: show their branch info --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Your Branch Stock</label>
                                <div class="form-control bg-light">
                                    @php
                                        $userBranch = $branches->firstWhere('id', $userBranchId);
                                    @endphp
                                    {{ $userBranch ? $userBranch->name : 'Unknown Branch' }}
                                    <span class="badge bg-primary ms-2">Branch ID: {{ $userBranchId }}</span>
                                </div>
                                <input type="hidden" id="branch_id" name="branch_id" value="{{ $userBranchId }}">
                                <input type="hidden" id="warehouse_id" name="warehouse_id" value="">
                                <small class="text-muted">Showing stock from your assigned branch only</small>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label for="short_desc" class="form-label fw-bold small text-uppercase">Short Description</label>
                                <textarea class="form-control" id="short_desc" name="short_desc" rows="3" placeholder="Brief highlight of the combo..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold small text-uppercase">Full Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Detailed description..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label fw-bold small text-uppercase">Combo Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <small class="text-muted">Optional. This image will show up in the POS.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Product Selection -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom p-4">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-boxes me-2 text-primary"></i>Select Products</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="product_search" class="form-label fw-bold small">Search Products (with Stock)</label>
                                <select class="form-select select2-ajax" id="product_search" style="width: 100%;">
                                    <option value=""></option>
                                </select>
                                <small class="text-muted">Search a product or its variation and click "Add to Combo"</small>
                            </div>

                            <button type="button" class="btn btn-outline-primary w-100 mb-4" onclick="addSelectedProduct()">
                                <i class="fas fa-plus me-2"></i>Add Selected Product to Combo
                            </button>

                            <!-- Selected Products -->
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th style="width: 100px;">Quantity</th>
                                            <th style="width: 120px;">Custom Price</th>
                                            <th style="width: 50px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected_products">
                                        <!-- Products will be added here -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <th colspan="3">Total Original Price:</th>
                                            <th id="total_original">৳0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body p-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Create Combo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let selectedProducts = [];

$(document).ready(function() {
    $('#product_search').select2({
        theme: 'bootstrap-5',
        placeholder: "Search for a product...",
        allowClear: true,
        ajax: {
            url: "{{ route('products.search.style') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    branch_id: $('#branch_id').val(),
                    warehouse_id: $('#warehouse_id').val(),
                    exclude_combo: 1
                };
            },
            processResults: function (data) {
                let formattedResults = [];
                data.results.forEach(p => {
                    if (p.has_variations && p.variations && p.variations.length > 0) {
                        let children = p.variations.map(v => {
                            return {
                                id: p.id + '-' + v.id,
                                text: p.name + ' - ' + v.name + ' (Stock: ' + v.stock + ')',
                                product: p,
                                variation: v
                            };
                        });
                        formattedResults.push({
                            text: p.name,
                            children: children
                        });
                    } else {
                        formattedResults.push({
                            id: p.id + '-0',
                            text: p.text,
                            product: p,
                            variation: null
                        });
                    }
                });
                return {
                    results: formattedResults
                };
            },
            cache: true
        }
    });
});

function addSelectedProduct() {
    const dataArray = $('#product_search').select2('data');
    if (!dataArray || dataArray.length === 0 || !dataArray[0].id) {
        alert('Please search and select a product first');
        return;
    }

    const data = dataArray[0];
    const productId = data.product.id;
    const variationId = data.variation ? data.variation.id : null;
    const productName = data.variation ? data.product.name + ' - ' + data.variation.name : data.product.name;
    const productPrice = parseFloat(data.variation ? (data.variation.price || data.product.price) : data.product.price) || 0;
    const availableStock = data.variation ? data.variation.stock : data.product.stock;

    const styleNumber = data.product.style_number || '';

    if (availableStock < 1) {
        alert('This product is out of stock in the selected branch(es)');
        return;
    }

    // Check if already added
    if (selectedProducts.find(p => p.product_id === productId && p.variation_id === variationId)) {
        alert('Product already added to combo');
        return;
    }

    selectedProducts.push({
        product_id: productId,
        variation_id: variationId,
        product_name: productName,
        style_number: styleNumber,
        regular_price: productPrice,
        quantity: 1,
        combo_price: productPrice,
        available_stock: availableStock
    });

    renderSelectedProducts();
    $('#product_search').val(null).trigger('change');
}

function renderSelectedProducts() {
    const tbody = document.getElementById('selected_products');
    tbody.innerHTML = '';
    
    let totalOriginal = 0;

    selectedProducts.forEach((product, index) => {
        totalOriginal += product.regular_price * product.quantity;

        const stockWarning = product.quantity > product.available_stock 
            ? '<span class="text-danger">⚠️ Insufficient Stock (Available: ' + product.available_stock + ')</span>' 
            : '<span class="text-success">✓ Stock OK</span>';

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${index}][product_id]" value="${product.product_id}">
                ${product.variation_id ? `<input type="hidden" name="items[${index}][variation_id]" value="${product.variation_id}">` : ''}
                <strong>${product.product_name}</strong><br>
                ${product.style_number ? `<small class="text-muted">Style No: ${product.style_number}</small><br>` : ''}
                <small class="text-muted">Regular: ৳${product.regular_price.toFixed(2)}</small><br>
                <small>${stockWarning}</small>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]" value="${product.quantity}" min="1" max="${product.available_stock}"
                       class="form-control form-control-sm" onchange="updateQuantity(${index}, this.value)">
            </td>
            <td>
                <input type="number" name="items[${index}][combo_price]" value="${product.combo_price}" min="0" step="0.01"
                       class="form-control form-control-sm" placeholder="Optional">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeProduct(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('total_original').textContent = '৳' + totalOriginal.toFixed(2);
    
    // Auto-calculate combo price if discount is set
    calculateComboPrice();
}

function updateQuantity(index, value) {
    selectedProducts[index].quantity = parseInt(value) || 1;
    renderSelectedProducts();
}

function removeProduct(index) {
    selectedProducts.splice(index, 1);
    renderSelectedProducts();
}

function calculateComboPrice() {
    const discountPercent = parseFloat(document.getElementById('discount_percent').value);
    const totalOriginal = selectedProducts.reduce((sum, p) => sum + (p.regular_price * p.quantity), 0);
    
    if (discountPercent && discountPercent > 0 && totalOriginal > 0) {
        const discountedPrice = totalOriginal * (1 - discountPercent / 100);
        document.getElementById('price').value = discountedPrice.toFixed(2);
    }
}

function clearDiscount() {
    // User entered manual price, clear discount
    document.getElementById('discount_percent').value = '';
}

function filterByLocationStock() {
    const val = $('#location_id').val();
    if (val.startsWith('branch_')) {
        $('#branch_id').val(val.split('_')[1]);
        $('#warehouse_id').val('');
    } else if (val.startsWith('warehouse_')) {
        $('#warehouse_id').val(val.split('_')[1]);
        $('#branch_id').val('');
    } else {
        $('#branch_id').val('');
        $('#warehouse_id').val('');
    }
    $('#product_search').val(null).trigger('change');
}
</script>
@endpush
@endsection
