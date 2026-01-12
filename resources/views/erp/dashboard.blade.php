@extends('erp.master')

@section('title', 'Dashboard')

@push('head')
<style>
    .premium-card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        overflow: hidden;
        background: white;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .premium-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .stat-trend {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
    }
    .stat-trend.up {
        background: #dcfce7;
        color: #16a34a;
    }
    .stat-trend.down {
        background: #fee2e2;
        color: #dc2626;
    }
    .product-item {
        padding: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s ease;
    }
    .product-item:hover {
        background: #f9fafb;
    }
    .product-item:last-child {
        border-bottom: none;
    }
    .stock-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-weight: 600;
    }
    .stock-critical {
        background: #fee2e2;
        color: #dc2626;
    }
    .stock-low {
        background: #fef3c7;
        color: #92400e;
    }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-3 p-md-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-4">
                <div>
                    <h2 class="fw-bold text-gray-800 mb-1">Dashboard Overview</h2>
                    <p class="text-muted mb-0">Monitor your business performance and key metrics</p>
                </div>
                <form method="GET">
                    <select id="range-select" name="range" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="day" {{ ($range ?? 'week')==='day' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ ($range ?? 'week')==='week' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="month" {{ ($range ?? 'week')==='month' ? 'selected' : '' }}>Last 30 days</option>
                        <option value="year" {{ ($range ?? 'week')==='year' ? 'selected' : '' }}>Last 12 months</option>
                    </select>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <!-- Row 1: Primary Metrics -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Total Revenue</p>
                                <h3 class="mb-2"><span id="stat-total-sales">{{ $stats['totalSales']['value'] ?? '0.00' }}</span>৳</h3>
                                <span class="stat-trend {{ $stats['totalSales']['trend'] ?? 'up' }}">
                                    <i class="fas fa-arrow-{{ $stats['totalSales']['trend'] === 'up' ? 'up' : 'down' }} me-1"></i>
                                    {{ abs($stats['totalSales']['percentage'] ?? 0) }}%
                                </span>
                            </div>
                            <div class="stats-icon green">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Total Profit</p>
                                <h3 class="mb-2"><span id="stat-profit">{{ $profitMetrics['profit'] ?? '0.00' }}</span>৳</h3>
                                <span class="stat-trend {{ $profitMetrics['trend'] ?? 'up' }}">
                                    <i class="fas fa-arrow-{{ $profitMetrics['trend'] === 'up' ? 'up' : 'down' }} me-1"></i>
                                    {{ abs($profitMetrics['percentage'] ?? 0) }}%
                                </span>
                            </div>
                            <div class="stats-icon green">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Profit Margin</p>
                                <h3 class="mb-2"><span id="stat-margin">{{ $profitMetrics['margin'] ?? '0.0' }}</span>%</h3>
                                <small class="text-muted">Gross margin</small>
                            </div>
                            <div class="stats-icon purple">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Pending Orders</p>
                                <h3 class="mb-2 text-warning"><span id="stat-pending">{{ $channelBreakdown['pending'] ?? 0 }}</span></h3>
                                <small class="text-muted">Need attention</small>
                            </div>
                            <div class="stats-icon orange">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Secondary Metrics -->
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Total Orders</p>
                                <h3 class="mb-2" id="stat-total-orders">{{ $stats['totalOrders']['value'] ?? 0 }}</h3>
                                <span class="stat-trend {{ $stats['totalOrders']['trend'] ?? 'up' }}">
                                    <i class="fas fa-arrow-{{ $stats['totalOrders']['trend'] === 'up' ? 'up' : 'down' }} me-1"></i>
                                    {{ abs($stats['totalOrders']['percentage'] ?? 0) }}%
                                </span>
                            </div>
                            <div class="stats-icon blue">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Avg. Order Value</p>
                                <h3 class="mb-2"><span id="stat-avg-order">{{ $stats['averageOrder']['value'] ?? '0.00' }}</span>৳</h3>
                                <span class="stat-trend {{ $stats['averageOrder']['trend'] ?? 'up' }}">
                                    <i class="fas fa-arrow-{{ $stats['averageOrder']['trend'] === 'up' ? 'up' : 'down' }} me-1"></i>
                                    {{ abs($stats['averageOrder']['percentage'] ?? 0) }}%
                                </span>
                            </div>
                            <div class="stats-icon blue">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-12 col-xl-4">
                    <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 small opacity-75">Sales Channels</p>
                                <div class="d-flex gap-4 mt-2">
                                    <div>
                                        <div class="small opacity-75">POS</div>
                                        <div class="h5 mb-0 fw-bold">{{ $channelBreakdown['pos']['percentage'] ?? 0 }}%</div>
                                        <div class="small opacity-75">{{ $channelBreakdown['pos']['orders'] ?? 0 }} orders</div>
                                    </div>
                                    <div>
                                        <div class="small opacity-75">Online</div>
                                        <div class="h5 mb-0 fw-bold">{{ $channelBreakdown['online']['percentage'] ?? 0 }}%</div>
                                        <div class="small opacity-75">{{ $channelBreakdown['online']['orders'] ?? 0 }} orders</div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-store fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Revenue Chart -->
                <div class="col-12 col-lg-8 mb-4">
                    <div class="premium-card">
                        <div class="card-header bg-white border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1 fw-bold">Revenue Overview</h5>
                                    <p class="text-muted small mb-0">Sales performance over time</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div style="height: 300px;">
                                <canvas id="chart-revenue"></canvas>
                            </div>
                            <div class="mt-3 d-flex justify-content-around text-center border-top pt-3">
                                <div>
                                    <div class="small text-muted">Total</div>
                                    <div class="fw-bold" id="revenue-total">{{ $salesOverview['totalSales'] ?? '0.00' }}৳</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Average</div>
                                    <div class="fw-bold" id="revenue-avg">{{ $salesOverview['average'] ?? '0.00' }}৳</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Peak Day</div>
                                    <div class="fw-bold" id="revenue-peak">{{ $salesOverview['peakDay'] ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Status -->
                <div class="col-12 col-lg-4 mb-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="mb-1 fw-bold">Order Status</h5>
                            <p class="text-muted small mb-0">Distribution by status</p>
                        </div>
                        <div class="card-body">
                            @if(($orderStatus['total'] ?? 0) > 0)
                                <div style="height: 250px;">
                                    <canvas id="chart-status"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-chart-pie fa-3x mb-3 opacity-25"></i>
                                    <p>No order data for selected range</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Top Selling Products -->
                <div class="col-12 col-lg-6 mb-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-white border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1 fw-bold">Top Selling Products</h5>
                                    <p class="text-muted small mb-0">Best performers this period</p>
                                </div>
                                <a href="{{ route('simple-accounting.top-products') }}" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @forelse($topSellingItems ?? [] as $item)
                            <div class="product-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-gray-800">{{ $item['name'] }}</div>
                                        <div class="small text-muted">{{ $item['category'] }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary">{{ $item['sales'] }} sold</div>
                                        <div class="small text-muted">{{ $item['revenue'] }}৳</div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                                <p>No sales data available</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="col-12 col-lg-6 mb-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-white border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1 fw-bold">Low Stock Alert</h5>
                                    <p class="text-muted small mb-0">Products running low</p>
                                </div>
                                <a href="{{ route('simple-accounting.stock-value') }}" class="btn btn-sm btn-outline-warning">Manage Stock</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @forelse($lowStockItems ?? [] as $item)
                            <div class="product-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-gray-800">{{ $item['name'] }}</div>
                                        <div class="small text-muted">{{ $item['category'] }} • SKU: {{ $item['sku'] }}</div>
                                    </div>
                                    <div>
                                        <span class="stock-badge {{ $item['stock'] < 5 ? 'stock-critical' : 'stock-low' }}">
                                            {{ $item['stock'] }} left
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-check-circle fa-3x mb-3 opacity-25 text-success"></i>
                                <p>All products are well stocked</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Online Orders (Actionable) -->
            <div class="premium-card">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold">Recent Online Orders</h5>
                            <p class="text-muted small mb-0">Orders requiring processing & fulfillment</p>
                        </div>
                        <a href="{{ route('order.list') }}" class="btn btn-sm btn-outline-primary">View All Orders</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0 align-middle">
                            <thead>
                                <tr class="small text-uppercase">
                                    <th class="ps-4">Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentOrders = \App\Models\Order::with('customer')
                                        ->whereIn('status', ['pending', 'approved', 'processing'])
                                        ->latest()
                                        ->limit(5)
                                        ->get();
                                @endphp
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td class="fw-bold ps-4">#{{ $order->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->customer->first_name ?? $order->name ?? 'Guest' }} {{ $order->customer->last_name ?? '' }}</div>
                                        <div class="small text-muted">{{ $order->customer->phone ?? $order->phone }}</div>
                                    </td>
                                    <td class="text-nowrap small">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($order->status === 'pending') bg-warning
                                            @elseif($order->status === 'approved') bg-info
                                            @elseif($order->status === 'processing') bg-primary
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($order->total, 2) }}৳</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('order.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-check-circle fa-3x mb-3 opacity-25 text-success"></i>
                                        <p>All orders processed! No pending orders.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const revenueLabels = @json($salesOverview['labels'] ?? []);
    const revenueData = @json(array_map('floatval', $salesOverview['data'] ?? []));
    const ctxRev = document.getElementById('chart-revenue');
    if (ctxRev) {
        new Chart(ctxRev, {
            type: 'line',
            data: { 
                labels: revenueLabels, 
                datasets: [{ 
                    label: 'Sales', 
                    data: revenueData, 
                    borderColor: 'rgb(25, 135, 84)', 
                    backgroundColor: 'rgba(25, 135, 84, 0.1)', 
                    fill: true, 
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }] 
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 }
                    }
                }, 
                scales: { 
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                } 
            }
        });
    }

    const statusTotal = {{ $orderStatus['total'] ?? 0 }};
    const statusCanvas = document.getElementById('chart-status');
    if (statusCanvas && statusTotal > 0) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Pending','Delivered','Shipping','Cancelled'],
                datasets: [{ 
                    data: [
                        {{ $orderStatus['pending'] ?? 0 }}, 
                        {{ $orderStatus['delivered'] ?? 0 }}, 
                        {{ $orderStatus['shipping'] ?? 0 }}, 
                        {{ $orderStatus['cancelled'] ?? 0 }}
                    ], 
                    backgroundColor: ['#fbbf24','#10b981','#3b82f6','#ef4444'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
})();
</script>
@endpush