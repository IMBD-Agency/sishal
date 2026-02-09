@extends('erp.master')

@section('title', 'Sales Summary')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid px-4 py-4">
            
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Sales Analytics & Summary</h4>
                    <p class="text-muted small mb-0">Track performance from {{ $startDate->format('d M, Y') }} to {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('simple-accounting.summary-export-excel', request()->all()) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="{{ route('simple-accounting.summary-export-pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form id="filterForm" method="GET" action="{{ route('simple-accounting.sales-summary') }}" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Period</label>
                            <select class="form-select form-select-sm" name="range" onchange="toggleCustomDate()">
                                <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        <div id="customDateRange" class="col-md-3 {{ $dateRange != 'custom' ? 'd-none' : '' }}">
                            <div class="row g-1">
                                <div class="col-6">
                                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $endDate->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Branch</label>
                            <select class="form-select form-select-sm" name="branch_id">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                             <label class="form-label small fw-bold">Source</label>
                            <select class="form-select form-select-sm" name="source">
                                <option value="all" {{ $source == 'all' ? 'selected' : '' }}>All Sources</option>
                                <option value="online" {{ $source == 'online' ? 'selected' : '' }}>Online</option>
                                <option value="pos" {{ $source == 'pos' ? 'selected' : '' }}>POS</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase mb-1">Total Revenue</div>
                            <h4 class="fw-bold mb-0">Tk. {{ number_format($salesData['total_revenue'], 2) }}</h4>
                            <div class="extra-small text-muted">{{ $salesData['total_sales_count'] }} Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase mb-1">Gross Profit</div>
                            <h4 class="fw-bold mb-0 text-success">Tk. {{ number_format($profitData['gross_profit'], 2) }}</h4>
                            <div class="extra-small text-muted">Margin: {{ number_format($profitData['profit_margin'], 1) }}%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase mb-1">Total COGS</div>
                            <h4 class="fw-bold mb-0 text-danger">Tk. {{ number_format($costData['total_costs'], 2) }}</h4>
                            <div class="extra-small text-muted">Cost Ratio: {{ number_format($profitData['cost_percentage'], 1) }}%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border shadow-sm text-center bg-light">
                        <div class="card-body p-3">
                            <div class="text-muted small text-uppercase mb-1">Online vs POS</div>
                            <h5 class="fw-bold mb-0">{{ number_format(($salesData['order_revenue'] / max(1, $salesData['total_revenue'])) * 100, 0) }}% / {{ number_format(($salesData['pos_revenue'] / max(1, $salesData['total_revenue'])) * 100, 0) }}%</h5>
                            <div class="extra-small text-muted">Revenue Split</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-5">
                <div class="col-lg-8">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="fw-bold mb-0">Financial Performance Overview</h6>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="fw-bold mb-0">Revenue by Channel</h6>
                        </div>
                        <div class="card-body">
                            <div style="height: 200px;">
                                <canvas id="sourceChart"></canvas>
                            </div>
                            <div class="mt-4 pt-2 border-top">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted"><i class="fas fa-circle text-primary me-2"></i>Online Store</span>
                                    <span class="fw-bold small">{{ number_format($salesData['order_revenue'], 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted"><i class="fas fa-circle text-warning me-2"></i>POS Outlet</span>
                                    <span class="fw-bold small">{{ number_format($salesData['pos_revenue'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card border shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Sales Breakdown by Product</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3 text-muted small fw-bold text-uppercase">Product / Variant</th>
                                <th class="py-3 text-muted small fw-bold text-uppercase">Source</th>
                                <th class="text-center py-3 text-muted small fw-bold text-uppercase">Sold</th>
                                <th class="text-end py-3 text-muted small fw-bold text-uppercase">Revenue</th>
                                <th class="text-end py-3 text-muted small fw-bold text-uppercase">COGS</th>
                                <th class="text-end pe-3 py-3 text-muted small fw-bold text-uppercase">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($variationProfits as $data)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $data['product']->name }}</div>
                                        @if($data['variation'])
                                            <div class="extra-small text-muted">
                                                @foreach($data['variation']->combinations as $comb)
                                                    {{ $comb->attributeValue->value }}{{ !$loop->last ? ' | ' : '' }}
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge border {{ $data['source'] == 'Online' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} px-2">
                                            {{ $data['source'] }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $data['quantity_sold'] }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($data['revenue'], 2) }}</td>
                                    <td class="text-end text-muted">{{ number_format($data['cost'], 2) }}</td>
                                    <td class="text-end pe-3 fw-bold text-success">{{ number_format($data['profit'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">No sales data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table > tbody > tr > td { border-bottom: 1px solid #f1f5f9; }
        .bg-info-subtle { background-color: #e0f2fe !important; }
        .bg-warning-subtle { background-color: #fef3c7 !important; }
        .extra-small { font-size: 0.7rem; }
    </style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function toggleCustomDate() {
        if (document.getElementsByName('range')[0].value === 'custom') {
            document.getElementById('customDateRange').classList.remove('d-none');
        } else {
            document.getElementById('customDateRange').classList.add('d-none');
            document.getElementById('filterForm').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const ctxPerf = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctxPerf, {
            type: 'bar',
            data: {
                labels: ['Consolidated Metrics'],
                datasets: [
                    { label: 'Revenue', data: [{{ $salesData['total_revenue'] }}], backgroundColor: '#0d6efd', borderRadius: 4 },
                    { label: 'Profit', data: [{{ $profitData['gross_profit'] }}], backgroundColor: '#198754', borderRadius: 4 },
                    { label: 'Cost', data: [{{ $costData['total_costs'] }}], backgroundColor: '#dc3545', borderRadius: 4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });

        const ctxSrc = document.getElementById('sourceChart').getContext('2d');
        new Chart(ctxSrc, {
            type: 'doughnut',
            data: {
                labels: ['Online', 'POS'],
                datasets: [{ 
                    data: [{{ $salesData['order_revenue'] }}, {{ $salesData['pos_revenue'] }}], 
                    backgroundColor: ['#0dcaf0', '#ffc107'], 
                    borderWidth: 0 
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
        });
    });
</script>
@endpush
