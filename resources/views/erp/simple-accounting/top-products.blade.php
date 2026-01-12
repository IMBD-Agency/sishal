@extends('erp.master')

@section('title', 'Top Products Analytics')

@push('head')
<style>
    .premium-card { border: none; border-radius: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; }
    .filter-bar { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border: 1px solid #e5e7eb; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 2rem; }
    .table-custom thead th { background: #f9fafb; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
    .table-custom tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
    .rank-badge { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; font-size: 0.875rem; }
    .rank-1 { background: #fef3c7; color: #92400e; border: 2px solid #fbbf24; }
    .rank-2 { background: #f3f4f6; color: #374151; border: 2px solid #d1d5db; }
    .rank-3 { background: #ffedd5; color: #9a3412; border: 2px solid #fdba74; }
    .rank-other { background: #f9fafb; color: #6b7280; border: 1px solid #e5e7eb; }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-gray-800 mb-1">Top Products Analytics</h2>
                    <p class="text-muted mb-0">Discover your best-performing products by revenue, profit, and volume</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('simple-accounting.top-products-export-excel', request()->all()) }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i> Excel
                    </a>
                    <a href="{{ route('simple-accounting.top-products-export-pdf', request()->all()) }}" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i> PDF
                    </a>
                </div>
            </div>

            <!-- Advanced Filter Bar -->
            <div class="filter-bar">
                <form method="GET" action="{{ route('simple-accounting.top-products') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Time Range</label>
                            <select class="form-select" name="range" id="range" onchange="this.form.submit()">
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                                <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>

                        <div id="customDateRange" class="col-md-3 {{ $dateRange != 'custom' ? 'd-none' : '' }}">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">From</label>
                                    <input type="date" class="form-control" name="date_from" value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">To</label>
                                    <input type="date" class="form-control" name="date_to" value="{{ $endDate->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Branch</label>
                            <select class="form-select" name="branch_id" onchange="this.form.submit()">
                                <option value="">All Branches</option>
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
                            <label class="form-label small fw-bold">Show Top</label>
                            <select class="form-select" name="limit" onchange="this.form.submit()">
                                <option value="5" {{ $limit == 5 ? 'selected' : '' }}>Top 5</option>
                                <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10</option>
                                <option value="20" {{ $limit == 20 ? 'selected' : '' }}>Top 20</option>
                                <option value="50" {{ $limit == 50 ? 'selected' : '' }}>Top 50</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row g-4">
                <!-- Top by Revenue -->
                <div class="col-lg-6">
                    <div class="premium-card bg-white h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 text-success"><i class="fas fa-money-bill-wave me-2"></i> Top by Revenue</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Product</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Sold</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topByRevenue as $index => $data)
                                            <tr>
                                                <td><span class="rank-badge {{ $index < 3 ? 'rank-'.($index+1) : 'rank-other' }}">{{ $index + 1 }}</span></td>
                                                <td>
                                                    <div class="fw-bold">{{ $data['product']->name }}</div>
                                                    <small class="text-muted">{{ $data['product']->category->name ?? 'N/A' }}</small>
                                                </td>
                                                <td class="text-end fw-bold text-success">{{ number_format($data['revenue'], 2) }}</td>
                                                <td class="text-end">{{ $data['quantity_sold'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top by Profit -->
                <div class="col-lg-6">
                    <div class="premium-card bg-white h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-line me-2"></i> Top by Profit</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Product</th>
                                            <th class="text-end">Profit</th>
                                            <th class="text-end">Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topByProfit as $index => $data)
                                            <tr>
                                                <td><span class="rank-badge {{ $index < 3 ? 'rank-'.($index+1) : 'rank-other' }}">{{ $index + 1 }}</span></td>
                                                <td>
                                                    <div class="fw-bold">{{ $data['product']->name }}</div>
                                                    <small class="text-muted">{{ $data['product']->category->name ?? 'N/A' }}</small>
                                                </td>
                                                <td class="text-end fw-bold text-primary">{{ number_format($data['profit'], 2) }}</td>
                                                <td class="text-end">
                                                    @php $margin = $data['revenue'] > 0 ? ($data['profit'] / $data['revenue']) * 100 : 0; @endphp
                                                    <span class="badge {{ $margin >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                        {{ number_format($margin, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top by Quantity -->
                <div class="col-12">
                    <div class="premium-card bg-white">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 text-warning"><i class="fas fa-cubes me-2"></i> Top by Quantity Sold</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th class="text-center">Quantity Sold</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Avg Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topByQuantity as $index => $data)
                                            <tr>
                                                <td><span class="rank-badge {{ $index < 3 ? 'rank-'.($index+1) : 'rank-other' }}">{{ $index + 1 }}</span></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($data['product']->image)
                                                            <img src="{{ asset($data['product']->image) }}" class="rounded me-3" width="40" height="40" style="object-fit: cover;">
                                                        @endif
                                                        <div class="fw-bold">{{ $data['product']->name }}</div>
                                                    </div>
                                                </td>
                                                <td>{{ $data['product']->category->name ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning-subtle text-warning fs-6 px-3">{{ $data['quantity_sold'] }}</span>
                                                </td>
                                                <td class="text-end">{{ number_format($data['revenue'], 2) }}</td>
                                                <td class="text-end text-muted">{{ number_format($data['quantity_sold'] > 0 ? $data['revenue'] / $data['quantity_sold'] : 0, 2) }}</td>
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
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('range').addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('customDateRange').classList.remove('d-none');
        } else {
            document.getElementById('customDateRange').classList.add('d-none');
        }
    });
</script>
@endpush
