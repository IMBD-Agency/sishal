@extends('erp.master')

@section('title', 'Top Products Analytics')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-4">
            
            <!-- Modern Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1 text-dark">Top Products Performance</h3>
                    <p class="text-muted mb-0">Best performing products and variations from <span class="badge bg-light text-primary border">{{ $startDate->format('d M, Y') }}</span> to <span class="badge bg-light text-primary border">{{ $endDate->format('d M, Y') }}</span></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('simple-accounting.top-products-export-excel', request()->all()) }}" class="btn btn-success px-4 rounded-pill shadow-sm">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="{{ route('simple-accounting.top-products-export-pdf', request()->all()) }}" class="btn btn-outline-danger px-4 rounded-pill shadow-sm">
                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                    </a>
                </div>
            </div>

            <!-- Advanced Filter Grid (Modern Style) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4 bg-light bg-opacity-50">
                    <form method="GET" action="{{ route('simple-accounting.top-products') }}" id="filterForm">
                        <!-- Row 1: Search & Basic Filters -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Global Search</label>
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Search by product name, SKU, or style number...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Category</label>
                                <select class="form-select form-select-sm select2-simple" name="category_id">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Source</label>
                                <select class="form-select form-select-sm" name="source">
                                    <option value="all" {{ request('source') == 'all' ? 'selected' : '' }}>All Sources</option>
                                    <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS Only</option>
                                    <option value="online" {{ request('source') == 'online' ? 'selected' : '' }}>Online Only</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Show Top</label>
                                <select class="form-select form-select-sm" name="limit">
                                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10</option>
                                    <option value="20" {{ $limit == 20 ? 'selected' : '' }}>Top 20</option>
                                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>Top 50</option>
                                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Top 100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Time & Date Filters -->
                        <div class="row g-3 mb-4 p-3 bg-white rounded-3 border border-dashed">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Quick Range</label>
                                <select class="form-select form-select-sm" name="range" id="quickRangeSelector">
                                    <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
                                    <option value="custom" {{ request('range') == 'custom' ? 'selected' : '' }}>Custom Date</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Year</label>
                                <select class="form-select form-select-sm" name="filter_year" id="filter_year">
                                    <option value="">Year</option>
                                    @for ($y = date('Y'); $y >= 2023; $y--)
                                        <option value="{{ $y }}" {{ request('filter_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Select Month</label>
                                <select class="form-select form-select-sm" name="filter_month" id="filter_month">
                                    <option value="">Full Year</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ request('filter_month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" class="form-control form-control-sm custom-date" name="date_from" value="{{ $startDate->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" class="form-control form-control-sm custom-date" name="date_to" value="{{ $endDate->toDateString() }}">
                            </div>
                        </div>

                        <!-- Row 3: Organizational Filters -->
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Branch</label>
                                <select class="form-select form-select-sm" name="branch_id">
                                    <option value="">All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Brand</label>
                                <select class="form-select form-select-sm select2-simple" name="brand_id">
                                    <option value="">All Brands</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Season</label>
                                <select class="form-select form-select-sm select2-simple" name="season_id">
                                    <option value="">All Seasons</option>
                                    @foreach ($seasons as $season)
                                        <option value="{{ $season->id }}" {{ request('season_id') == $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Gender</label>
                                <select class="form-select form-select-sm select2-simple" name="gender_id">
                                    <option value="">All Genders</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Specific Size</label>
                                <select class="form-select form-select-sm select2-simple" name="variation_value_id">
                                    <option value="">All Variations</option>
                                    @foreach($variationValues as $val)
                                        <option value="{{ $val->id }}" {{ request('variation_value_id') == $val->id ? 'selected' : '' }}>{{ $val->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label opacity-0 d-block">Action</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 w-100 rounded-pill"><i class="fas fa-filter me-2"></i>Apply</button>
                                    <a href="{{ route('simple-accounting.top-products') }}" class="btn btn-light btn-sm rounded-circle"><i class="fas fa-undo"></i></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <!-- Top by Revenue -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-primary py-3 border-0">
                            <h6 class="fw-bold mb-0 text-white"><i class="fas fa-chart-line me-2"></i>Top {{ $limit }} by Revenue</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="ps-3 small text-muted text-uppercase py-3">#</th>
                                        <th class="small text-muted text-uppercase py-3">Product</th>
                                        <th class="text-end pe-3 small text-muted text-uppercase py-3">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(collect($topByRevenue)->take($limit) as $index => $data)
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-light text-primary border">{{ $index + 1 }}</span></td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $data['product']->name }}</div>
                                                <div class="extra-small text-muted">SKU: {{ $data['product']->sku }} | Sold: {{ $data['quantity_sold'] }}</div>
                                            </td>
                                            <td class="text-end pe-3">
                                                <span class="fw-bold text-success fs-6">৳{{ number_format($data['revenue'], 2) }}</span>
                                                <div class="extra-small text-muted">Profit: ৳{{ number_format($data['profit'], 2) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top by Quantity -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-warning py-3 border-0">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-shopping-bag me-2"></i>Top {{ $limit }} by Quantity Sold</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="ps-3 small text-muted text-uppercase py-3">#</th>
                                        <th class="small text-muted text-uppercase py-3">Product</th>
                                        <th class="text-center small text-muted text-uppercase py-3">Sold Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(collect($topByQuantity)->take($limit) as $index => $data)
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-light text-dark border">{{ $index + 1 }}</span></td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $data['product']->name }}</div>
                                                <div class="extra-small text-muted">Category: {{ $data['product']->category->name ?? '-' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-dark rounded-pill px-3">{{ $data['quantity_sold'] }} units</span>
                                                <div class="extra-small text-muted mt-1">৳{{ number_format($data['revenue'], 2) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .table > tbody > tr > td { border-bottom: 1px solid #f1f5f9; padding: 1rem 0.5rem; }
        .bg-light-50 { background-color: #fbfcfe; }
        .extra-small { font-size: 0.75rem; }
        .rounded-4 { border-radius: 1rem !important; }
        .custom-date { border: 1px solid #dee2e6; padding: 0.375rem 0.75rem; border-radius: 0.375rem; }
        .badge { font-weight: 600; }
        .border-dashed { border-style: dashed !important; }
        .select2-container--default .select2-selection--single { height: 31px !important; line-height: 31px !important; }
    </style>

    <script>
        $(document).ready(function() {
            // Auto-submit on change for range
            $('#quickRangeSelector').on('change', function() {
                if($(this).val() != 'custom') {
                    $('#filterForm').submit();
                }
            });

            // Month/Year sync
            $('#filter_year, #filter_month').on('change', function() {
                if ($('#filter_year').val()) {
                    $('#quickRangeSelector').val('custom');
                    $('#filterForm').submit();
                }
            });
        });
    </script>
@endsection
