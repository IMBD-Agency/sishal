@extends('erp.master')

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
                        <li class="breadcrumb-item active text-primary fw-600">Manage Combo</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">{{ $product->name }}</h4>
                <p class="text-muted small mb-0">Manage combo items and pricing</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="{{ route('erp.combo-products.index') }}" class="btn btn-light border px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-arrow-left me-2"></i>Back to Combos
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Edit Combo Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-edit me-2 text-primary"></i>Edit Combo Details</h6>
            </div>
            <div class="card-body p-4">
                @can('manage combos')
                <form action="{{ route('product.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Combo Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $product->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">SKU</label>
                            <input type="text" class="form-control" name="sku" value="{{ $product->sku }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Combo Price</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="price" value="{{ $product->price }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Original Price (for discount calculation)</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" class="form-control" name="combo_original_price" value="{{ $product->combo_original_price ?? 0 }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Short Description</label>
                            <textarea class="form-control" name="short_desc" rows="2">{{ $product->short_desc }}</textarea>
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Details
                            </button>
                        </div>
                    </div>
                </form>
                @else
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Combo Name</label>
                        <div class="form-control-plaintext">{{ $product->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">SKU</label>
                        <div class="form-control-plaintext">{{ $product->sku }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Combo Price</label>
                        <div class="form-control-plaintext">৳{{ number_format($product->price, 2) }}</div>
                    </div>
                </div>
                @endcan
            </div>
        </div>

    {{-- Add Combo Item Form --}}
    <div class="row">
        @can('manage combos')
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Product to Combo</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('erp.combo-products.add-item', $product) }}" method="POST">
                        @csrf
                        @if(!$userBranchId)
                        <div class="mb-3">
                            <label for="location_filter" class="form-label fw-bold small">Check Stock at Location</label>
                            <select class="form-select" id="location_filter" onchange="filterByLocationStock()">
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
                            <input type="hidden" id="branch_filter" value="">
                            <input type="hidden" id="warehouse_filter" value="">
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Your Branch Stock</label>
                            <div class="form-control bg-light">
                                @php
                                    $userBranch = $branches->firstWhere('id', $userBranchId);
                                @endphp
                                {{ $userBranch ? $userBranch->name : 'Unknown Branch' }}
                            </div>
                            <input type="hidden" id="branch_filter" value="{{ $userBranchId }}">
                            <input type="hidden" id="warehouse_filter" value="">
                        </div>
                        @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Select Product (with Stock)</label>
                                <select class="form-select select2-ajax" id="product_select" style="width: 100%;" required>
                                    <option value=""></option>
                                </select>
                                <input type="hidden" name="product_id" id="hidden_product_id">
                                <input type="hidden" name="variation_id" id="hidden_variation_id">
                            </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Custom Price (optional)</label>
                            <input type="number" name="combo_price" class="form-control" step="0.01" placeholder="Leave empty for regular price">
                        </div>
                        <button type="submit" class="btn btn-primary">Add to Combo</button>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    </div>

    {{-- Combo Items List --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list me-2 text-primary"></i>Combo Items</h6>
                </div>
                <div class="card-body p-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Regular Price</th>
                                <th>Combo Price</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalOriginal = 0; $totalCombo = 0; @endphp
                            @forelse($comboItems as $item)
                                @php
                                    $regularPrice = $item->product->price;
                                    $comboPrice = $item->combo_price ?? $regularPrice;
                                    $subtotalRegular = $regularPrice * $item->quantity;
                                    $subtotalCombo = $comboPrice * $item->quantity;
                                    $totalOriginal += $subtotalRegular;
                                    $totalCombo += $subtotalCombo;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $item->product->name }}
                                        @if($item->product->style_number)
                                            <br><small class="text-muted">Style No: {{ $item->product->style_number }}</small>
                                        @endif
                                        @if($item->variation)
                                            <br><small class="text-muted">{{ $item->variation->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>৳{{ number_format($regularPrice, 2) }}</td>
                                    <td>৳{{ number_format($comboPrice, 2) }}</td>
                                    <td>৳{{ number_format($subtotalCombo, 2) }}</td>
                                    <td>
                                        @can('manage combos')
                                        <form action="{{ route('erp.combo-products.destroy', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item?')">Remove</button>
                                        </form>
                                        @else
                                        <span class="text-muted small">No Access</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No items in this combo yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="4">Total Original Price:</th>
                                <th>৳{{ number_format($totalOriginal, 2) }}</th>
                                <th></th>
                            </tr>
                            <tr class="table-success">
                                <th colspan="4">Combo Offer Price:</th>
                                <th>৳{{ number_format($product->price, 2) }}</th>
                                <th></th>
                            </tr>
                            @if($totalOriginal > $product->price)
                            <tr class="table-warning">
                                <th colspan="4">Customer Saves:</th>
                                <th>৳{{ number_format($totalOriginal - $product->price, 2) }}</th>
                                <th></th>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#product_select').select2({
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
                    branch_id: $('#branch_filter').val(),
                    warehouse_id: $('#warehouse_filter').val(),
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

    $('#product_select').on('select2:select', function (e) {
        const data = e.params.data;
        if (data && data.product) {
            $('#hidden_product_id').val(data.product.id);
            $('#hidden_variation_id').val(data.variation ? data.variation.id : '');
        }
    });

    $('#product_select').on('select2:unselect', function (e) {
        $('#hidden_product_id').val('');
        $('#hidden_variation_id').val('');
    });
});

function filterByLocationStock() {
    const val = $('#location_filter').val();
    if (val.startsWith('branch_')) {
        $('#branch_filter').val(val.split('_')[1]);
        $('#warehouse_filter').val('');
    } else if (val.startsWith('warehouse_')) {
        $('#warehouse_filter').val(val.split('_')[1]);
        $('#branch_filter').val('');
    } else {
        $('#branch_filter').val('');
        $('#warehouse_filter').val('');
    }
    $('#product_select').val(null).trigger('change');
}
</script>
@endpush
@endsection
