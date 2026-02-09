@extends('erp.master')

@section('title', 'Top Products Analytics')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-4">
            
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Top Products Performance</h4>
                    <p class="text-muted small mb-0">Best performing products from {{ $startDate->format('d M, Y') }} to {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('simple-accounting.top-products-export-excel', request()->all()) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="{{ route('simple-accounting.top-products-export-pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('simple-accounting.top-products') }}" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Period</label>
                            <select class="form-select form-select-sm" name="range" onchange="this.form.submit()">
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select form-select-sm select2" name="category_id" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Search Product</label>
                            <input type="text" class="form-control form-control-sm" name="search" value="{{ $search }}" placeholder="Name, SKU...">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <!-- Top by Revenue -->
                <div class="col-lg-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-light py-3 border-0">
                            <h6 class="fw-bold mb-0">Top 10 by Revenue</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr class="bg-light-50">
                                        <th class="ps-3 small text-muted text-uppercase">#</th>
                                        <th class="small text-muted text-uppercase">Product</th>
                                        <th class="text-end pe-3 small text-muted text-uppercase">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(collect($topByRevenue)->take(10) as $index => $data)
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-light text-dark border">{{ $index + 1 }}</span></td>
                                            <td>
                                                <div class="fw-bold">{{ $data['product']->name }}</div>
                                                <div class="extra-small text-muted">Sold: {{ $data['quantity_sold'] }}</div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-success">Tk. {{ number_format($data['revenue'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top by Quantity -->
                <div class="col-lg-6">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-light py-3 border-0">
                            <h6 class="fw-bold mb-0">Top 10 by Quantity Sold</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr class="bg-light-50">
                                        <th class="ps-3 small text-muted text-uppercase">#</th>
                                        <th class="small text-muted text-uppercase">Product</th>
                                        <th class="text-center small text-muted text-uppercase">Sold Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(collect($topByQuantity)->take(10) as $index => $data)
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-light text-dark border">{{ $index + 1 }}</span></td>
                                            <td>
                                                <div class="fw-bold">{{ $data['product']->name }}</div>
                                                <div class="extra-small text-muted">Revenue: Tk. {{ number_format($data['revenue'], 2) }}</div>
                                            </td>
                                            <td class="text-center"><span class="badge bg-warning text-dark px-3">{{ $data['quantity_sold'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analysis Table -->
            <div class="card border shadow-sm mt-4 mb-5">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0">Comprehensive Performance Matrix</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3 text-muted small fw-bold">Rank</th>
                                <th class="py-3 text-muted small fw-bold">Product Details</th>
                                <th class="text-center py-3 text-muted small fw-bold">Stock</th>
                                <th class="text-center py-3 text-muted small fw-bold">Sold</th>
                                <th class="text-end py-3 text-muted small fw-bold">Avg Price</th>
                                <th class="text-end pe-3 py-3 text-muted small fw-bold">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topByRevenue as $index => $data)
                                <tr>
                                    <td class="ps-3"><span class="badge bg-light text-dark border">{{ $index + 1 }}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $data['product']->name }}</div>
                                        <div class="extra-small text-muted">Style: {{ $data['product']->style_number ?? $data['product']->sku ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge border {{ $data['product']->total_variation_stock <= 5 ? 'bg-danger-subtle text-danger' : 'bg-light text-dark' }}">
                                            {{ $data['product']->total_variation_stock }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $data['quantity_sold'] }}</td>
                                    <td class="text-end">Tk. {{ number_format($data['revenue'] / max(1, $data['quantity_sold']), 2) }}</td>
                                    <td class="text-end pe-3 fw-bold">Tk. {{ number_format($data['revenue'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table > tbody > tr > td { border-bottom: 1px solid #f1f5f9; }
        .bg-light-50 { background-color: #fbfcfe; }
        .extra-small { font-size: 0.75rem; }
    </style>
@endsection
