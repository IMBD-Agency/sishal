@extends('erp.master')

@section('title', 'Dashboard')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-3 p-md-4">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-end align-items-stretch align-items-sm-center gap-2 mb-3">
                <form method="GET">
                    <select id="range-select" name="range" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="week" {{ ($range ?? 'week')==='week' ? 'selected' : '' }}>Last 7 days</option>
                        <option value="month" {{ ($range ?? 'week')==='month' ? 'selected' : '' }}>Last 30 days</option>
                        <option value="year" {{ ($range ?? 'week')==='year' ? 'selected' : '' }}>Last 12 months</option>
                        <option value="day" {{ ($range ?? 'week')==='day' ? 'selected' : '' }}>Today</option>
                    </select>
                </form>
            </div>
            <!-- Stats Cards -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
                <div class="col">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Revenue</p>
                                <h3 class="mb-1"><span id="stat-total-sales">{{ $stats['totalSales']['value'] ?? '0.00' }}</span>৳</h3>
                                
                            </div>
                            <div class="stats-icon green">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Orders</p>
                                <h3 class="mb-1" id="stat-total-orders">{{ $stats['totalOrders']['value'] ?? 0 }}</h3>
                                
                            </div>
                            <div class="stats-icon blue">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Avg. Order</p>
                                <h3 class="mb-1"><span id="stat-avg-order">{{ $stats['averageOrder']['value'] ?? '0.00' }}</span>৳</h3>
                                
                            </div>
                            <div class="stats-icon orange">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Satisfaction</p>
                                <h3 class="mb-1"><span id="stat-satisfaction">{{ $stats['customerSatisfaction']['value'] ?? '0.0' }}</span>/5</h3>
                                <small class="text-success">Improving</small>
                            </div>
                            <div class="stats-icon purple">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Charts and Activities Row -->
            <div class="row mb-4">
                <div class="col-12 col-lg-8 mb-4">
                    <div class="card card-custom">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Revenue Overview</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="ratio ratio-16x9">
                                <canvas id="chart-revenue" class="w-100"></canvas>
                            </div>
                            <div class="mt-3 small text-muted">
                                Total: <span id="revenue-total">{{ $salesOverview['totalSales'] ?? '0.00' }}</span>৳ • Avg: <span id="revenue-avg">{{ $salesOverview['average'] ?? '0.00' }}</span>৳ • Peak: <span id="revenue-peak">{{ $salesOverview['peakDay'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 mb-4">
                    <div class="card card-custom">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Status</h5>
                        </div>
                        <div class="card-body">
                            @if(($orderStatus['total'] ?? 0) > 0)
                                <div class="ratio ratio-1x1">
                                    <canvas id="chart-status" class="w-100"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">No order data for selected range</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Recent Invoices -->
            <div class="card card-custom">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Invoices</h5>
                        <span class="small text-muted">Latest 5</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Mobile list (xs-sm) -->
                    <div class="d-md-none">
                        <div class="list-group list-group-flush" id="list-invoices">
                            @forelse(($currentInvoices ?? []) as $inv)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semibold">{{ $inv['id'] }}</div>
                                    <div class="text-end fw-semibold">{{ $inv['amount'] }}৳</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <div class="text-muted small text-truncate" style="max-width: 65%">{{ $inv['customer'] }}</div>
                                    <span class="badge badge-status {{ strtolower($inv['status'])==='completed' ? 'bg-success' : (in_array(strtolower($inv['status']),['pending','in_progress']) ? 'bg-warning' : 'bg-secondary') }}">{{ strtolower($inv['status']) }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="list-group-item text-center text-muted">No data</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Table (md and up) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-custom mb-0 align-middle">
                            <thead>
                                <tr class="small text-uppercase">
                                    <th class="text-nowrap">Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="text-end text-nowrap">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="table-invoices">
                                @forelse(($currentInvoices ?? []) as $inv)
                                <tr>
                                    <td class="fw-bold text-nowrap">{{ $inv['id'] }}</td>
                                    <td class="text-truncate" style="max-width: 240px">{{ $inv['customer'] }}</td>
                                    <td class="text-nowrap"><span class="badge badge-status {{ strtolower($inv['status'])==='completed' ? 'bg-success' : (in_array(strtolower($inv['status']),['pending','in_progress']) ? 'bg-warning' : 'bg-secondary') }}">{{ $inv['status'] }}</span></td>
                                    <td class="text-end text-nowrap">{{ $inv['amount'] }}৳</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
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
            data: { labels: revenueLabels, datasets: [{ label: 'Sales', data: revenueData, borderColor: '#17a2b8', backgroundColor: 'rgba(23,162,184,.15)', fill: true, tension: .35 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    const statusTotal = {{ $orderStatus['total'] ?? 0 }};
    const statusCanvas = document.getElementById('chart-status');
    if (statusCanvas && statusTotal > 0) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Pending','Delivered','Shipping','Cancelled'],
                datasets: [{ data: [{{ $orderStatus['pending'] ?? 0 }}, {{ $orderStatus['delivered'] ?? 0 }}, {{ $orderStatus['shipping'] ?? 0 }}, {{ $orderStatus['cancelled'] ?? 0 }}], backgroundColor: ['#ffc107','#28a745','#ffc107','#dc3545'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
})();
</script>
@endpush