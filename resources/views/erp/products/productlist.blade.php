@extends('erp.master')
@section('title', 'Product List')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <style>
        .premium-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: #fff; margin-bottom: 1.5rem; overflow: hidden; border: 1px solid #edf2f7; }
        .glass-header { position: relative !important; top: 0 !important; box-shadow: none !important; border-bottom: 1px solid rgba(0,0,0,0.05) !important; margin-bottom: 1rem !important; }
        .table-responsive { max-height: 80vh; overflow: auto !important; position: relative; background: #fff; }
        #productTable { border-collapse: separate; border-spacing: 0; width: 100%; }
        #productTable thead th { position: sticky !important; top: 0 !important; z-index: 100 !important; background-color: #f8fafc !important; color: #64748b !important; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; padding: 16px 20px !important; border: none !important; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        #productTable tbody td { padding: 16px 20px !important; border-bottom: 1px solid #f1f5f9 !important; vertical-align: middle !important; background: #fff !important; font-size: 0.85rem; }
        
        .thumbnail-box { width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; }
        .product-thumb-img { width: 100%; height: 100%; object-fit: cover; }
        
        .main-inventory-container { padding: 0 2rem; }
    </style>

    <div class="main-inventory-container">
        <!-- Top Header -->
        <div class="glass-header py-3 bg-white border-bottom mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-0 text-dark">Product Inventory Report</h4>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('product.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus me-2"></i>New Product
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-0 py-4">
            <!-- Advanced Filters -->
            <div class="premium-card mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('product.list') }}" method="GET" id="filterForm" autocomplete="off">
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

                        <div class="row g-3">
                            <div class="col-md-2 date-range-field">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate ? $startDate->toDateString() : '' }}">
                            </div>
                            <div class="col-md-2 date-range-field">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate ? $endDate->toDateString() : '' }}">
                            </div>

                            <div class="col-md-2 month-field" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Month</label>
                                <select name="month" class="form-select select2-simple">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 year-field" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Year</label>
                                <select name="year" class="form-select select2-simple">
                                    @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Product</label>
                                <select name="product_id" class="form-select select2-simple" data-placeholder="All Products">
                                    <option value="">All Products</option>
                                    @foreach($allProducts as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Style Number</label>
                                <select name="style_number" class="form-select select2-simple" data-placeholder="All Styles">
                                    <option value="">All Styles</option>
                                    @foreach($allStyleNumbers as $style)
                                        <option value="{{ $style }}">{{ $style }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Category</label>
                                <select name="category_id" class="form-select select2-simple" data-placeholder="All Categories">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->full_path_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Brand</label>
                                <select name="brand_id" class="form-select select2-simple" data-placeholder="All Brands">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Branch</label>
                                <select name="branch_id" class="form-select select2-simple" data-placeholder="All Branches">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Global Search</label>
                                <input type="text" name="search" class="form-control" placeholder="SKU, Name...">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Per Page</label>
                                <select name="per_page" class="form-select">
                                    <option value="50" selected>50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-top p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <a href="{{ route('product.export.excel', request()->all()) }}" class="btn btn-outline-success btn-sm fw-bold px-3 no-loader export-link-excel" target="_blank">
                                    <i class="fas fa-file-excel me-2"></i>Excel
                                </a>
                                <a href="{{ route('product.export.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm fw-bold px-3 no-loader export-link-pdf" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" id="resetBtn" class="btn btn-light border px-4 fw-bold text-muted" style="height: 42px;">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>

            <!-- Product Table Container -->
            <div id="product-data-container">
                @include('erp.products.partials.table')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        function toggleReportFields() {
            const reportType = $('.report-type-radio:checked').val();
            $('.date-range-field, .month-field, .year-field').hide();
            if (reportType === 'daily') $('.date-range-field').show();
            else if (reportType === 'monthly') { $('.month-field').show(); $('.year-field').show(); }
            else if (reportType === 'yearly') $('.year-field').show();
        }

        toggleReportFields();
        $('.report-type-radio').on('change', toggleReportFields);

        function refreshProducts(url = null) {
            const form = $('#filterForm');
            const targetUrl = url || form.attr('action');
            const container = $('#product-data-container');
            container.css('opacity', '0.5');
            
            // Use the data from the form even for pagination links to ensure filters persist
            let requestData = form.serialize();
            
            $.ajax({
                url: targetUrl,
                method: 'GET',
                data: requestData,
                success: function(response) {
                    container.html(response);
                    container.css('opacity', '1');
                    $('.export-link-excel').attr('href', '{{ route("product.export.excel") }}?' + requestData);
                    $('.export-link-pdf').attr('href', '{{ route("product.export.pdf") }}?' + requestData);
                },
                error: function() { container.css('opacity', '1'); alert('Error loading data'); }
            });
        }

        $('#filterForm').on('submit', function(e) { e.preventDefault(); refreshProducts(); });
        
        $('select[name="per_page"]').on('change', function() {
            refreshProducts();
        });
        
        $('#resetBtn').on('click', function() {
            $('#filterForm')[0].reset();
            $('.select2-simple').val('').trigger('change');
            toggleReportFields();
            refreshProducts("{{ route('product.list') }}");
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            refreshProducts($(this).attr('href'));
            window.scrollTo(0, 0);
        });
    });
</script>
@endpush
@endsection