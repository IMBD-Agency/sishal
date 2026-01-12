@extends('erp.master')

@section('title', 'Sales Summary')

@push('head')
<style>
    :root {
        --premium-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --success-gradient: linear-gradient(135deg, #22c55e 0%, #10b981 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .premium-card {
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: white;
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .metric-card {
        color: white;
        position: relative;
    }

    .metric-card .card-body {
        padding: 2rem;
        z-index: 1;
        position: relative;
    }

    .metric-card.primary { background: var(--premium-gradient); }
    .metric-card.success { background: var(--success-gradient); }
    .metric-card.warning { background: var(--warning-gradient); }
    .metric-card.info { background: var(--info-gradient); }

    .metric-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.15;
        transform: rotate(-15deg);
    }

    .filter-bar {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .table-container {
        background: white;
        border-radius: 1.25rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .table-custom thead th {
        background: #f9fafb;
        color: #4b5563;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-custom tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }

    .badge-pill {
        border-radius: 9999px;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .variation-tag {
        background: #f3f4f6;
        color: #4b5563;
        border-radius: 0.375rem;
        padding: 0.125rem 0.375rem;
        font-size: 0.7rem;
        margin-right: 0.25rem;
        border: 1px solid #e5e7eb;
    }

    .chart-container {
        height: 350px;
    }

    .source-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }
    .source-online { background-color: #6366f1; }
    .source-pos { background-color: #f59e0b; }

    @media (max-width: 768px) {
        .metric-card .card-body {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
            
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-gray-800 mb-1">Sales Analytics</h2>
                    <p class="text-muted mb-0">Track your performance and profit across all channels</p>
                </div>
                <div>
                   <div class="btn-group">
                       <a href="{{ route('simple-accounting.summary-export-excel', request()->all()) }}" class="btn btn-outline-success">
                           <i class="fas fa-file-excel me-2"></i> Excel
                       </a>
                       <a href="{{ route('simple-accounting.summary-export-pdf', request()->all()) }}" class="btn btn-outline-danger">
                           <i class="fas fa-file-pdf me-2"></i> PDF
                       </a>
                       <button class="btn btn-outline-primary" onclick="window.print()">
                           <i class="fas fa-print me-2"></i> Print
                       </button>
                   </div>
                </div>
            </div>

            <!-- Enhanced Filter Bar -->
            <div class="filter-bar">
                <form id="filterForm" method="GET" action="{{ route('simple-accounting.sales-summary') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Time Range</label>
                            <select class="form-select" name="range" id="range" onchange="toggleCustomDate()">
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
                            <select class="form-select" name="branch_id">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Source</label>
                            <select class="form-select" name="source">
                                <option value="all" {{ $source == 'all' ? 'selected' : '' }}>All Sources</option>
                                <option value="online" {{ $source == 'online' ? 'selected' : '' }}>Online Store</option>
                                <option value="pos" {{ $source == 'pos' ? 'selected' : '' }}>POS Outlet</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->display_name }}</option>
                                @endforeach
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

            <!-- Metric Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="premium-card metric-card primary">
                        <div class="card-body">
                            <i class="fas fa-coins metric-icon"></i>
                            <p class="mb-1 opacity-75">Total Revenue</p>
                            <h2 class="fw-bold mb-0">{{ number_format($salesData['total_revenue'], 2) }} <span class="fs-6">TK</span></h2>
                            <small class="opacity-75">{{ $salesData['total_sales_count'] }} Transactions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card metric-card success">
                        <div class="card-body">
                            <i class="fas fa-chart-line metric-icon"></i>
                            <p class="mb-1 opacity-75">Gross Profit</p>
                            <h2 class="fw-bold mb-0">{{ number_format($profitData['gross_profit'], 2) }} <span class="fs-6">TK</span></h2>
                            <small class="opacity-75">In {{ $dateRange }} period</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card metric-card warning">
                        <div class="card-body">
                            <i class="fas fa-tags metric-icon"></i>
                            <p class="mb-1 opacity-75">Total Costs</p>
                            <h2 class="fw-bold mb-0">{{ number_format($costData['total_costs'], 2) }} <span class="fs-6">TK</span></h2>
                            <small class="opacity-75">{{ number_format($profitData['cost_percentage'], 1) }}% of revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card metric-card info">
                        <div class="card-body">
                            <i class="fas fa-percentage metric-icon"></i>
                            <p class="mb-1 opacity-75">Profit Margin</p>
                            <h2 class="fw-bold mb-0">{{ number_format($profitData['profit_margin'], 1) }} %</h2>
                            <small class="opacity-75">Average margin</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <!-- Source Breakdown Chart -->
                <div class="col-lg-8">
                    <div class="premium-card">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Financial Overview</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="chart-container">
                                <canvas id="revenueProfitChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Source Distribution -->
                <div class="col-lg-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Source Distribution</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="sourceChart"></canvas>
                            </div>
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-muted"><span class="source-dot source-online"></span>Online Store</span>
                                    <span class="fw-bold small">{{ number_format($salesData['order_revenue'], 2) }} TK</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted"><span class="source-dot source-pos"></span>POS Outlet</span>
                                    <span class="fw-bold small">{{ number_format($salesData['pos_revenue'], 2) }} TK</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product & Variation Performance Table -->
            <div class="premium-card mb-5">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Product & Variation Performance</h5>
                    <div class="small text-muted">Sorted by Gross Profit</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Product / Variation</th>
                                <th>Category</th>
                                <th>Source</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Approx. Cost</th>
                                <th class="text-end">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($variationProfits as $data)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($data['product']->image)
                                                <img src="{{ asset($data['product']->image) }}" class="rounded me-3" width="40" height="40" style="object-fit: cover;">
                                            @else
                                                <div class="rounded me-3 bg-light d-flex align-items-center justify-content-center" width="40" height="40">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $data['product']->name }}</div>
                                                @if($data['variation'])
                                                    <div class="variation-details mt-1">
                                                        @foreach($data['variation']->combinations as $comb)
                                                            <span class="variation-tag">{{ $comb->attribute->name }}: {{ $comb->attributeValue->value }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <small class="text-muted">No Variation</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $data['product']->category->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $data['source'] == 'Online' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} badge-pill">
                                            {{ $data['source'] }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $data['quantity_sold'] }}</td>
                                    <td class="text-end fw-medium">{{ number_format($data['revenue'], 2) }}</td>
                                    <td class="text-end text-muted">{{ number_format($data['cost'], 2) }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($data['profit'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <img src="{{ asset('static/no-data.svg') }}" width="150" class="mb-3 opacity-50">
                                        <p class="text-muted">No sales data found for the selected filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function toggleCustomDate() {
        const range = document.getElementById('range').value;
        const customDiv = document.getElementById('customDateRange');
        if (range === 'custom') {
            customDiv.classList.remove('d-none');
        } else {
            customDiv.classList.add('d-none');
            // Auto submit if not custom
            document.getElementById('filterForm').submit();
        }
    }

    // Chart.js Configuration
    document.addEventListener('DOMContentLoaded', function() {
        // Financial Overview Chart
        const ctxRev = document.getElementById('revenueProfitChart').getContext('2d');
        new Chart(ctxRev, {
            type: 'bar',
            data: {
                labels: ['Total Metrics'],
                datasets: [
                    {
                        label: 'Revenue',
                        data: [{{ $salesData['total_revenue'] }}],
                        backgroundColor: '#6366f1',
                        borderRadius: 8,
                        barThickness: 60
                    },
                    {
                        label: 'Gross Profit',
                        data: [{{ $profitData['gross_profit'] }}],
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        barThickness: 60
                    },
                    {
                        label: 'Total Costs',
                        data: [{{ $costData['total_costs'] }}],
                        backgroundColor: '#f59e0b',
                        borderRadius: 8,
                        barThickness: 60
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false,
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Source Distribution Chart
        const ctxSource = document.getElementById('sourceChart').getContext('2d');
        new Chart(ctxSource, {
            type: 'doughnut',
            data: {
                labels: ['Online', 'POS'],
                datasets: [{
                    data: [{{ $salesData['order_revenue'] }}, {{ $salesData['pos_revenue'] }}],
                    backgroundColor: ['#6366f1', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endpush