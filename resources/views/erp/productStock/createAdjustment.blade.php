@extends('erp.master')

@section('title', 'Product Stock Adjustment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <style>
            :root {
                --primary-blue: #0ea5e9;
                --primary-hover: #0284c7;
                --danger-red: #ef4444;
                --text-main: #0f172a;
                --border-color: #e2e8f0;
            }

            .adjustment-card {
                background: #fff;
                border: 0;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                border-radius: 12px;
            }

            .page-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--text-main);
                margin-bottom: 0.5rem;
            }

            .form-label {
                font-weight: 600;
                font-size: 0.875rem;
                color: #475569;
                margin-bottom: 0.5rem;
            }

            .table thead th {
                background: #f8fafc;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                color: #64748b;
                border-bottom: 1px solid var(--border-color);
                padding: 1rem 0.75rem;
            }

            .table td {
                padding: 1rem 0.75rem;
                vertical-align: middle;
            }

            .select2-container--default .select2-selection--single {
                height: 42px;
                border-color: var(--border-color);
                border-radius: 8px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 40px;
                padding-left: 12px;
            }

            .input-qty {
                width: 100px;
                border-radius: 6px;
                text-align: center;
                font-weight: 600;
            }
        </style>

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Adjustment Information</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('productstock.list') }}">Stock Management</a></li>
                            <li class="breadcrumb-item active">Adjustment</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('stock.adjustment.store') }}" method="POST" id="adjustmentForm">
                @csrf
                <div class="card adjustment-card">
                    <div class="card-body p-4">
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Date *</label>
                                <input type="text" class="form-control" value="{{ date('m/d/Y') }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Branch (Adjustment Location) *</label>
                                <select name="branch_id" id="branch_id" class="form-select select2-simple" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Product (Search by Name or Style Number) *</label>
                                <select id="product_search" class="form-select">
                                    <option value="">Select One</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover border">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Season</th>
                                        <th>Gender</th>
                                        <th>Product Name</th>
                                        <th>Style Number</th>
                                        <th>Size *</th>
                                        <th>Color</th>
                                        <th class="text-center">Current</th>
                                        <th class="text-center">New Qty</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="adjustmentTableBody">
                                    <!-- Rows added dynamically -->
                                    <tr id="emptyRow">
                                        <td colspan="12" class="text-center py-5 text-muted">
                                            Search and select a product to begin adjustment
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="If have any note"></textarea>
                        </div>

                        <div class="d-flex justify-content-center gap-3 mt-5">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                                <i class="fas fa-save me-2"></i>Submit
                            </button>
                            <a href="{{ route('productstock.list') }}" class="btn btn-danger px-5 py-2 fw-bold">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.select2-simple').select2();

            $('#product_search').select2({
                placeholder: 'Search by Product Name or Style Number...',
                ajax: {
                    url: '{{ route('products.search.style') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results.map(function(item) {
                                return { id: item.id, text: item.text };
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#product_search').on('change', function() {
                const styleNumber = $(this).val();
                if (!styleNumber) return;

                const branchId = $('#branch_id').val();
                if (!branchId) {
                    alert('Please select a branch first to check current stock.');
                    $(this).val('').trigger('change');
                    return;
                }

                $.ajax({
                    url: '/erp/products/find-by-style/' + styleNumber,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            response.products.forEach(function(product) {
                                if (product.has_variations) {
                                    product.variations.forEach(function(variation) {
                                        fetchAndAddRow(product, variation, branchId);
                                    });
                                } else {
                                    fetchAndAddRow(product, null, branchId);
                                }
                            });
                        }
                    }
                });
                
                $(this).val('').trigger('change');
            });

            function fetchAndAddRow(product, variation, branchId) {
                const variationId = variation ? variation.id : '';
                
                // Get current stock for this specific location
                $.ajax({
                    url: '{{ route('stock.current') }}',
                    type: 'GET',
                    data: {
                        product_id: product.id,
                        variation_id: variationId,
                        location_type: 'branch',
                        branch_id: branchId
                    },
                    success: function(stockRes) {
                        addRowToTable(product, variation, stockRes.quantity || 0);
                    }
                });
            }

            function addRowToTable(product, variation, currentStock) {
                $('#emptyRow').hide();
                const rowId = variation ? `var_${variation.id}` : `prod_${product.id}`;
                
                if ($(`#${rowId}`).length > 0) return;

                let size = '-';
                let color = '-';
                
                if (variation && variation.attributes) {
                    variation.attributes.forEach(attr => {
                        const name = attr.value.toLowerCase();
                        // Crude check for size/color names or just show values
                        // In findProductByStyle we already format things well but let's be safe
                        if (attr.value) {
                            if (attr.name && attr.name.toLowerCase() === 'color') color = attr.value;
                            else size = attr.value; 
                        }
                    });
                }

                const row = `
                    <tr id="${rowId}">
                        <td><img src="${product.image}" alt="" style="width: 40px; height: 40px; object-fit: cover;" class="rounded border"></td>
                        <td class="small text-muted">${product.category}</td>
                        <td class="small text-muted">${product.brand}</td>
                        <td class="small text-muted">${product.season}</td>
                        <td class="small text-muted">${product.gender}</td>
                        <td class="fw-bold">${product.name}</td>
                        <td class="text-info mono small font-monospace">${product.style_number}</td>
                        <td class="small fw-bold">${variation ? variation.name.split('-')[1] || variation.name : '-'}</td>
                        <td class="small">${variation ? variation.name.split('-')[0] || '-' : '-'}</td>
                        <td class="text-center fw-bold text-muted">${currentStock}</td>
                        <td>
                            <input type="number" name="items[${rowId}][quantity]" class="form-control input-qty mx-auto" value="${currentStock}" required>
                            <input type="hidden" name="items[${rowId}][product_id]" value="${product.id}">
                            <input type="hidden" name="items[${rowId}][variation_id]" value="${variation ? variation.id : ''}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#adjustmentTableBody').append(row);
            }

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                if ($('#adjustmentTableBody tr').length === 1) { // only emptyRow left
                    $('#emptyRow').show();
                }
            });

            $('#adjustmentForm').on('submit', function(e) {
                if ($('#adjustmentTableBody tr').length <= 1) {
                    e.preventDefault();
                    alert('Please add at least one product for adjustment.');
                }
            });
        });
    </script>
    @endpush
@endsection
