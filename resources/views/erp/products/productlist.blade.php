@extends('erp.master')

@section('title', 'Product List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Header Section -->
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3 class="mb-0 fw-bold text-dark">Product</h3>
                </div>
                <div class="col-md-6 text-end">
                    <nav aria-label="breadcrumb" class="d-inline-block">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-primary">Dashboard</a></li>
                            <li class="breadcrumb-item active">Product</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Product List Title & New Product Button -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Product List</h5>
                    <a href="{{ route('product.create') }}" class="btn btn-primary px-4">
                        <i class="fas fa-plus me-2"></i>New Product
                    </a>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('product.list') }}" method="GET" id="filterForm">
                        <div class="row g-3 mb-4">
                            <div class="col-12 d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input report-type-radio" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="dailyReport">
                                        <i class="fas fa-circle-dot text-primary me-1 small"></i>Daily Reports
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input report-type-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="monthlyReport">Monthly Reports</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input report-type-radio" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="yearlyReport">Yearly Reports</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-2 date-range-field">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-range-field">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 month-field" style="display: none;">
                                <label class="form-label small fw-bold">Select Month</label>
                                <select name="month" class="form-select">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2 year-field" style="display: none;">
                                <label class="form-label small fw-bold">Select Year</label>
                                <select name="year" class="form-select">
                                    @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Product</label>
                                <select name="product_id" class="form-select select2-simple">
                                    <option value="">All Product</option>
                                    @foreach($allProducts as $p)
                                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Style Number</label>
                                <input type="text" name="style_number" class="form-control" placeholder="Style Number" value="{{ request('style_number') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Category</label>
                                <select name="category_id" class="form-select select2-simple">
                                    <option value="">All Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Brand</label>
                                <select name="brand_id" class="form-select select2-simple">
                                    <option value="">All Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Season</label>
                                <select name="season_id" class="form-select select2-simple">
                                    <option value="">All Season</option>
                                    @foreach($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Gender</label>
                                <select name="gender_id" class="form-select select2-simple">
                                    <option value="">All Gender</option>
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-cyan text-white flex-grow-1 fw-bold shadow-sm">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                                <a href="{{ route('product.list') }}" class="btn btn-secondary fw-bold shadow-sm px-4">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-2">
                        <div class="d-flex gap-1" id="exportButtons">
                            <button class="btn btn-dark btn-sm px-3 shadow-none">CSV</button>
                            <button class="btn btn-dark btn-sm px-3 shadow-none">Excel</button>
                            <button class="btn btn-dark btn-sm px-3 shadow-none">PDF</button>
                            <button class="btn btn-dark btn-sm px-3 shadow-none">Print</button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Search:</span>
                            <input type="text" id="tableSearch" class="form-control form-control-sm" style="width: 200px;">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="productTable">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="ps-3">#SL</th>
                                    <th>Entry Date</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Style No</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Season</th>
                                    <th>Gender</th>
                                    <th>Purchase</th>
                                    <th>MRP</th>
                                    <th>Wholesale</th>
                                    <th class="text-center pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                <tr>
                                    <td class="ps-3 small text-muted">{{ $products->firstItem() + $index }}</td>
                                    <td class="small">{{ $product->created_at->format('d-m-Y') }}</td>
                                    <td>
                                        <div class="p-1 border rounded bg-white" style="width: 35px; height: 35px;">
                                            <img src="{{ asset($product->image ?: 'assets/images/product-placeholder.png') }}" 
                                                 class="img-fluid rounded" style="object-fit: contain; width: 100%; height: 100%;">
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('product.show', $product->id) }}" class="text-dark text-decoration-none fw-bold small">
                                            {{ Str::limit($product->name, 25) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span style="color: #e83e8c; font-weight: 500; font-size: 0.85rem;">{{ $product->style_number ?: $product->sku }}</span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-light text-dark border fw-normal" style="font-size: 0.7rem;">
                                            {{ $product->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td><span class="small">{{ $product->brand->name ?? '-' }}</span></td>
                                    <td><span class="small text-uppercase">{{ $product->season->name ?? '-' }}</span></td>
                                    <td><span class="small text-uppercase">{{ $product->gender->name ?? '-' }}</span></td>
                                    <td class="fw-bold small text-muted">{{ number_format($product->cost, 2) }}</td>
                                    <td class="fw-bold small text-primary">{{ number_format($product->price, 2) }}</td>
                                    <td class="fw-bold small text-success">{{ number_format($product->wholesale_price, 2) }}</td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('product.show', $product->id) }}" 
                                               class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                               style="width: 26px; height: 26px; color: #0dcaf0;" title="View">
                                                <i class="fas fa-eye fa-xs"></i>
                                            </a>
                                            <a href="{{ route('product.edit', $product->id) }}" 
                                               class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                               style="width: 26px; height: 26px; color: #198754;" title="Edit">
                                                <i class="fas fa-edit fa-xs"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                                    style="width: 26px; height: 26px; color: #ffc107;" title="Barcode" 
                                                    onclick="openBarcodeModal({{ $product->id }})">
                                                <i class="fas fa-barcode fa-xs"></i>
                                            </button>
                                            <a href="{{ route('erp.products.variations.index', $product->id) }}" 
                                               class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                               style="width: 26px; height: 26px; color: #6c757d;" title="Variations">
                                                <i class="fas fa-layer-group fa-xs"></i>
                                            </a>
                                            <form action="{{ route('product.delete', $product->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                                        style="width: 26px; height: 26px; color: #dc3545;" 
                                                        onclick="return confirm('Are you sure?')" title="Delete">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                                        <p>No products found matching your criteria.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
                        <div class="small text-muted">
                            Showing {{ $products->firstItem() ?: 0 }} to {{ $products->lastItem() ?: 0 }} of {{ $products->total() }} entries
                        </div>
                        <div>
                            {{ $products->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('erp.pos.components.barcode-modal')

    <style>
        .btn-cyan { background-color: #0dcaf0; border-color: #0dcaf0; }
        .btn-cyan:hover { background-color: #0baccc; border-color: #0baccc; }
        .table-header-dark th {
            background-color: #436e67 !important;
            color: white !important;
            font-weight: 500;
            font-size: 0.85rem;
            padding: 12px 10px !important;
            border: none !important;
            vertical-align: middle;
            text-align: center;
        }
        #productTable td {
            font-size: 0.85rem;
            padding: 10px 8px;
            text-align: center;
        }
        .action-btn {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 4px;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            color: #6c757d;
        }
        .select2-container--default .select2-selection--single {
            border: 1px solid #dee2e6 !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }
        .card { border-radius: 8px; }
        .pagination { margin-bottom: 0; }
    </style>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Global products array for barcode modal
    var products = @json($products->items());

    // showToast fallback if not defined
    if (typeof showToast !== 'function') {
        window.showToast = function(message, type = 'info') {
            alert(message); // Basic fallback, can be improved later
        };
    }

    $(document).ready(function() {
        $('.select2-simple').select2({
            width: '100%'
        });

        // Function to handle report type toggling
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

        // Initialize on load
        toggleReportFields();

        // Handle change
        $('.report-type-radio').on('change', function() {
            toggleReportFields();
        });

        $('#tableSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $("#productTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@endpush