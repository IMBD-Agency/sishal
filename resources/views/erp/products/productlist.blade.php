@extends('erp.master')

@section('title', 'Product List')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Top Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0 text-dark">Product List</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('product.create') }}" class="btn btn-primary fw-bold shadow-sm">
                    <i class="fas fa-plus me-2"></i>New Product
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-3 shadow-sm">
            <div class="card-body p-3">
                <form action="{{ route('product.list') }}" method="GET" id="filterForm" autocomplete="off">
                    <!-- Report type toggles -->
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-2 date-range-field">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                        </div>
                        <div class="col-md-2 date-range-field">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                        </div>

                        <div class="col-md-2 month-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 year-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                            <select name="year" class="form-select form-select-sm">
                                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Product</label>
                            <select name="product_id" class="form-select form-select-sm select2-simple" data-placeholder="All Product">
                                <option value="">All Product</option>
                                @foreach($allProducts as $p)
                                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Style Number</label>
                            <select name="style_number" class="form-select form-select-sm select2-simple" data-placeholder="All Style Number">
                                <option value="">All Style Number</option>
                                @foreach($allStyleNumbers as $style)
                                    <option value="{{ $style }}" {{ request('style_number') == $style ? 'selected' : '' }}>{{ $style }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                            <select name="category_id" class="form-select form-select-sm select2-simple" data-placeholder="All Category">
                                <option value="">All Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->full_path_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                            <select name="brand_id" class="form-select form-select-sm select2-simple" data-placeholder="All Brand">
                                <option value="">All Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                            <select name="season_id" class="form-select form-select-sm select2-simple" data-placeholder="All Season">
                                <option value="">All Season</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                            <select name="gender_id" class="form-select form-select-sm select2-simple" data-placeholder="All Gender">
                                <option value="">All Gender</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                            <select name="branch_id" class="form-select form-select-sm select2-simple" data-placeholder="All Branch">
                                <option value="">All Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Warehouse</label>
                            <select name="warehouse_id" class="form-select form-select-sm select2-simple" data-placeholder="All Warehouse">
                                <option value="">All Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $selectedWarehouseId == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mt-2">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Global Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name, SKU, Style...">
                        </div>

                        <div class="col-md-2 mt-2">
                            <div class="d-flex gap-2" style="margin-top: 25px;">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill text-white fw-bold shadow-sm filter-btn">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                                <a href="{{ route('product.list') }}" class="btn btn-light border btn-sm flex-fill fw-bold shadow-sm filter-btn">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Export and Table Search -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="btn-group shadow-sm">
                <a href="{{ route('product.export.csv', request()->all()) }}" class="btn btn-secondary btn-sm fw-bold">CSV</a>
                <a href="{{ route('product.export.excel', request()->all()) }}" class="btn btn-secondary btn-sm fw-bold">Excel</a>
                <a href="{{ route('product.export.pdf', request()->all()) }}" class="btn btn-secondary btn-sm fw-bold">PDF</a>
                <button class="btn btn-secondary btn-sm fw-bold" onclick="window.print()">Print</button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small fw-bold text-muted mb-0">Search:</label>
                <input type="text" id="tableSearch" class="form-control form-control-sm table-search-input" value="{{ request('search') }}" placeholder="Press Enter to search all...">
            </div>
        </div>

        <!-- Product Table -->
        <div class="premium-card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table reporting-table mb-0" id="productTable">
                        <thead>
                            <tr>
                                <th class="text-center">#SN.</th>
                                <th>Entry Date</th>
                                <th class="text-center">Image</th>
                                <th>Product Name</th>
                                <th>Style Number</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Season</th>
                                <th>Gender</th>
                                <th class="text-end">Purchase Price</th>
                                <th class="text-end">MRP</th>
                                <th class="text-end">Whole Sale</th>
                                <th class="text-center">Total Stock</th>
                                <th class="text-center">Ooption</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $index => $product)
                            <tr>
                                <td class="text-center">{{ $products->firstItem() + $index }}</td>
                                <td>{{ $product->created_at->format('d-m-Y') }}</td>
                                <td class="text-center">
                                    <div class="thumbnail-box mx-auto product-thumb-container">
                                        @if($product->image)
                                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-thumb-img">
                                        @else
                                            <i class="fas fa-shopping-cart text-muted opacity-50 small"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('product.show', $product->id) }}" class="text-info text-decoration-none fw-bold">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td>{{ $product->style_number ?? $product->sku }}</td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>{{ $product->brand->name ?? '-' }}</td>
                                <td>{{ strtoupper($product->season->name ?? 'ALL') }}</td>
                                <td>{{ strtoupper($product->gender->name ?? 'ALL') }}</td>
                                <td class="text-end fw-bold">{{ number_format($product->cost, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($product->price, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($product->wholesale_price ?? 0, 2) }}</td>
                                <td class="text-center">
                                    @php
                                        $totalVarStock = $product->total_stock_variation ?? 0;
                                        $totalSimpleStock = ($product->total_stock_branch ?? 0) + ($product->total_stock_warehouse ?? 0);
                                        $displayStock = $product->has_variations ? $totalVarStock : $totalSimpleStock;
                                    @endphp
                                    <span class="badge {{ $displayStock > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($displayStock, 0) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('product.show', $product->id) }}" class="btn btn-info btn-xs text-white" title="View"><i class="fas fa-eye fa-xs"></i></a>
                                        <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success btn-xs" title="Edit"><i class="fas fa-edit fa-xs"></i></a>
                                        <form action="{{ route('product.delete', $product->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash fa-xs"></i></button>
                                        </form>
                                        <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary btn-xs text-white" title="Variations"><i class="fas fa-layer-group fa-xs"></i></a>
                                         <a href="{{ route('barcodes.index') }}?style_no={{ $product->style_number ?? $product->sku }}" class="btn btn-warning btn-xs text-white" title="Barcode"><i class="fas fa-barcode fa-xs"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">No data found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-top-0 py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="text-muted small mb-0">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} entries</p>
                    {{ $products->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var products = @json($products->items());

    if (typeof showToast !== 'function') {
        window.showToast = function(message, type = 'info') {
            console.log(type.toUpperCase() + ": " + message);
        };
    }

    $(document).ready(function() {
        // Report type toggle logic
        function toggleReportFields() {
            const reportType = $('.report-type-radio:checked').val();
            
            if (reportType === 'daily') {
                $('.date-range-field').show();
                $('.month-field').hide();
                $('.year-field').hide();
            } else if (reportType === 'monthly') {
                $('.date-range-field').hide();
                $('.month-field').show();
                $('.year-field').show();
            } else if (reportType === 'yearly') {
                $('.date-range-field').hide();
                $('.month-field').hide();
                $('.year-field').show();
            }
        }

        toggleReportFields();
        $('.report-type-radio').on('change', toggleReportFields);

        // Handle table search with Enter key for server-side search across all products
        $('#tableSearch').on('keypress', function(e) {
            if (e.which == 13) { // Enter key
                const value = $(this).val();
                $('input[name="search"]').val(value);
                $('#filterForm').submit();
            }
        });

        // Current page client-side filtering for quick results
        let searchTimeout;
        $('#tableSearch').on('input', function() {
            clearTimeout(searchTimeout);
            const value = $(this).val().toLowerCase();
            searchTimeout = setTimeout(function() {
                $("#productTable tbody tr").filter(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(value) > -1);
                });
            }, 300);
            
            // Sync with global search input
            $('input[name="search"]').val($(this).val());
        });
    });
</script>
@endpush
@endsection