@extends('erp.master')

@section('title', 'Product Catalog')

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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Product Management</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Master Catalog</h4>
                    <span class="badge bg-light text-primary border border-primary small rounded-pill px-3 py-1">{{ $products->total() }} Items</span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                 <a href="{{ route('product.export.excel', request()->all()) }}" class="btn btn-outline-dark shadow-sm">
                    <i class="fas fa-file-excel small"></i>
                </a>
                <a href="{{ route('product.export.pdf', request()->all()) }}" class="btn btn-outline-dark shadow-sm">
                    <i class="fas fa-file-pdf small"></i>
                </a>
                <a href="{{ route('product.create') }}" class="btn btn-create-premium text-nowrap">
                    <i class="fas fa-plus-circle me-2"></i>Add Product
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Advanced Filters -->
        <div class="premium-card mb-4">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Targeted Search</h6>
                    <!-- Report Period Toggles -->
                    <div class="d-flex gap-3">
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="dailyReport">
                                Custom Range
                            </label>
                        </div>
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthlyReport">Monthly</label>
                        </div>
                        <div class="form-check cursor-pointer">
                            <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearlyReport">Yearly</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('product.list') }}" method="GET" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 date-range-field">
                            <label class="form-label small fw-bold text-muted text-uppercase">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                        </div>
                        <div class="col-md-3 date-range-field">
                            <label class="form-label small fw-bold text-muted text-uppercase">To Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                        </div>

                        <div class="col-md-2 month-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase">Month</label>
                            <select name="month" class="form-select">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 year-field" style="display: none;">
                            <label class="form-label small fw-bold text-muted text-uppercase">Fiscal Year</label>
                            <select name="year" class="form-select">
                                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                            <select name="category_id" class="form-select select2-simple">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Brand</label>
                            <select name="brand_id" class="form-select select2-simple">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-4 pt-3 border-top">
                            <div class="accordion" id="advancedFilters">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 px-0 bg-transparent shadow-none small fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#moreFilters">
                                            <i class="fas fa-sliders-h me-2"></i>More Filters (Season, Gender, Style No.)
                                        </button>
                                    </h2>
                                    <div id="moreFilters" class="accordion-collapse collapse" data-bs-parent="#advancedFilters">
                                        <div class="accordion-body px-0 py-3">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Style Code</label>
                                                    <input type="text" name="style_number" class="form-control" placeholder="Search Style..." value="{{ request('style_number') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Season</label>
                                                    <select name="season_id" class="form-select select2-simple">
                                                        <option value="">All Seasons</option>
                                                        @foreach($seasons as $season)
                                                            <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Target Gender</label>
                                                    <select name="gender_id" class="form-select select2-simple">
                                                        <option value="">All Genders</option>
                                                        @foreach($genders as $gender)
                                                            <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('product.list') }}" class="btn btn-light border fw-bold px-4">
                                    <i class="fas fa-undo me-2"></i>Clear
                                </a>
                                <button type="submit" class="btn btn-create-premium px-5">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Table -->
        <div class="premium-card shadow-sm">
            <div class="card-header bg-white border-bottom p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="tableSearch" class="form-control" placeholder="Live search in table...">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table mb-0" id="productTable">
                        <thead>
                            <tr>
                                <th class="ps-4">Preview</th>
                                <th>Item Descriptor</th>
                                <th>Style / SKU</th>
                                <th>Classification</th>
                                <th>Financials (MRP)</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $index => $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="thumbnail-box" style="width: 50px; height: 50px;">
                                        @if($product->image)
                                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}">
                                        @else
                                            <i class="fas fa-image text-muted opacity-50"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('product.show', $product->id) }}" class="text-decoration-none">
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        <div class="text-muted d-flex gap-2 small">
                                            @if($product->season) <span><i class="fas fa-sun me-1 opacity-50"></i>{{ $product->season->name }}</span> @endif
                                            @if($product->brand) <span><i class="fas fa-tag me-1 opacity-50"></i>{{ $product->brand->name }}</span> @endif
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <code class="text-primary bg-light px-2 py-1 rounded small mb-1">{{ $product->sku }}</code>
                                        @if($product->style_number)
                                        <span class="text-muted small">Style: {{ $product->style_number }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="category-tag">
                                        {{ $product->category->name ?? 'General' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">৳{{ number_format($product->price, 2) }}</div>
                                    <div class="small text-muted text-decoration-line-through">Cost: ৳{{ number_format($product->cost, 2) }}</div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-action" title="Barcode" onclick="openBarcodeModal({{ $product->id }})">
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                        <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-action" title="Variations">
                                            <i class="fas fa-layer-group"></i>
                                        </a>
                                        <a href="{{ route('product.edit', $product->id) }}" class="btn btn-action" title="Edit">
                                            <i class="fas fa-pen-nib"></i>
                                        </a>
                                        <a href="{{ route('product.show', $product->id) }}" class="btn btn-action" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-box-open fa-3x text-light mb-3"></i>
                                        <h5 class="text-muted">Catalog Empty</h5>
                                        <p class="text-muted small mb-0">No products match the current filters.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-top-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="text-muted small mb-0">Displaying {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} items</p>
                    {{ $products->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    @include('erp.pos.components.barcode-modal')
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    var products = @json($products->items());

    if (typeof showToast !== 'function') {
        window.showToast = function(message, type = 'info') {
            // Placeholder for toast notification
            console.log(type.toUpperCase() + ": " + message);
        };
    }

    $(document).ready(function() {
        $('.select2-simple').select2({
            width: '100%',
            dropdownParent: $('body')
        });

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

        $('#tableSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $("#productTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@endpush
@endsection