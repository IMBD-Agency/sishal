@extends('erp.master')

@section('title', 'Comprehensive Sales Report')

@push('head')
<style>
    .premium-card { border: none; border-radius: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; background: white; }
    .filter-bar { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border: 1px solid #e5e7eb; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 2rem; }
    .nav-report .nav-link { color: #64748b !important; font-weight: 600; padding: 1rem 1.5rem; border: none !important; border-bottom: 3px solid transparent !important; border-radius: 0; background: transparent !important; }
    .nav-report .nav-link.active { color: var(--primary-color) !important; border-bottom-color: var(--primary-color) !important; background: rgba(var(--primary-rgb), 0.05) !important; }
    .stats-card { padding: 1.25rem; border-radius: 1rem; border: 1px solid #e2e8f0; background: #fff; }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-gray-800 mb-1">Sales Report</h2>
                    <p class="text-muted mb-0">Detailed breakdown of sales performance across products, categories, and variations</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-export me-1"></i> Export Data
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('simple-accounting.export-excel', request()->all()) }}">
                                    <i class="fas fa-file-excel text-success me-2"></i> Export Excel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('simple-accounting.export-pdf', request()->all()) }}">
                                    <i class="fas fa-file-pdf text-danger me-2"></i> Export PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Detailed Filter Bar -->
            <div class="filter-bar">
                <form method="GET" action="{{ route('simple-accounting.sales-report') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Range</label>
                            <select class="form-select" name="range" id="range" onchange="this.form.submit()">
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                                <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                        </div>

                        <div id="customDates" class="col-md-3 {{ $dateRange != 'custom' ? 'd-none' : '' }}">
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" class="form-control" name="date_from" value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control" name="date_to" value="{{ $endDate->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Branch</label>
                            <select class="form-select" name="branch_id" onchange="this.form.submit()">
                                <option value="">All Branches (Inc. E-com)</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select" name="category_id" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->display_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Source</label>
                            <select class="form-select" name="source" onchange="this.form.submit()" {{ $branchId ? 'disabled' : '' }}>
                                <option value="all" {{ $source == 'all' ? 'selected' : '' }}>All Sources</option>
                                <option value="online" {{ $source == 'online' ? 'selected' : '' }}>Online Store</option>
                                <option value="pos" {{ $source == 'pos' ? 'selected' : '' }}>POS Outlet</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted small mb-1">Total Revenue</div>
                        <div class="h4 fw-bold text-primary mb-0">{{ number_format($productProfits->sum('revenue'), 2) }} TK</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted small mb-1">Total Cost</div>
                        <div class="h4 fw-bold mb-0 text-gray-700">{{ number_format($productProfits->sum('cost'), 2) }} TK</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted small mb-1">Total Profit</div>
                        <div class="h4 fw-bold text-success mb-0">{{ number_format($productProfits->sum('profit'), 2) }} TK</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-muted small mb-1">Items Sold</div>
                        <div class="h4 fw-bold text-warning mb-0">{{ number_format($productProfits->sum('quantity_sold')) }}</div>
                    </div>
                </div>
            </div>

            <div class="premium-card">
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-tabs nav-report border-0" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#productWise">Product Wise</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#categoryWise">Category Wise</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#variationWise">Variation Wise</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="productWise">
                            @include('erp.simple-accounting.components.sales-table', ['data' => $productProfits, 'type' => 'product'])
                        </div>
                        <div class="tab-pane fade" id="categoryWise">
                            @include('erp.simple-accounting.components.sales-table', ['data' => $categoryProfits, 'type' => 'category'])
                        </div>
                        <div class="tab-pane fade" id="variationWise">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Product Variation</th>
                                            <th>Style No</th>
                                            <th class="text-center">Sold</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Cost</th>
                                            <th class="text-end pe-4">Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($variationProfits as $var)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-gray-800">{{ $var['product']->name }}</div>
                                                <div class="small text-muted">{{ $var['variation_name'] }}</div>
                                            </td>
                                            <td><code class="text-primary">{{ $var['variation']->sku ?? 'N/A' }}</code></td>
                                            <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $var['quantity_sold'] }}</span></td>
                                            <td class="text-end">{{ number_format($var['revenue'], 2) }}</td>
                                            <td class="text-end text-muted">{{ number_format($var['cost'], 2) }}</td>
                                            <td class="text-end pe-4 fw-bold text-success">{{ number_format($var['profit'], 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center py-5">No variations sold in this period</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@push('scripts')
<script>
    document.getElementById('range').addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('customDates').classList.remove('d-none');
        } else {
            document.getElementById('customDates').classList.add('d-none');
        }
    });
</script>
@endpush

@endsection
