@extends('erp.master')

@section('title', 'Dashboard')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid p-2 p-md-4">
            <h1 class="dash-welcome-title">{{ $siteTitle }}</h1>

            <!-- Financial KPI Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="dash-card" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); min-height: 140px;">
                        <h2>{{ $financialKPIs['total_sales'] }}</h2>
                        <p>Today's Total Sales</p>
                        <i class="fas fa-cash-register icon-bg"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dash-card" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); min-height: 140px;">
                        <h2>{{ $financialKPIs['total_collection'] }}</h2>
                        <p>Today's Total Collection</p>
                        <i class="fas fa-hand-holding-usd icon-bg"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="dash-card" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); min-height: 140px;">
                        <h2>{{ $financialKPIs['total_due'] }}</h2>
                        <p>Today's Total Due</p>
                        <i class="fas fa-file-invoice icon-bg"></i>
                    </div>
                </div>
            </div>

            <!-- Operations Summary Cards -->
            <div class="dash-summary-cards mb-4">
                <div class="dash-card">
                    <h2>{{ $todayPurchases['gross_amount'] }}</h2>
                    <p>Today's Purchase</p>
                    <i class="fas fa-shopping-basket icon-bg"></i>
                </div>
                <div class="dash-card">
                    <h2>{{ str_pad($todayPurchases['gross_qty'], 2, '0', STR_PAD_LEFT) }}</h2>
                    <p>Today's Purchase Qty</p>
                    <i class="fas fa-chart-bar icon-bg"></i>
                </div>
                <div class="dash-card">
                    <h2>{{ $todayPurchases['actual_amount'] }}</h2>
                    <p>Today's Actual Purchase</p>
                    <i class="fas fa-shopping-basket icon-bg"></i>
                </div>
                <div class="dash-card">
                    <h2>{{ str_pad($todayPurchases['actual_qty'], 2, '0', STR_PAD_LEFT) }}</h2>
                    <p>Today's Actual Purchase Qty</p>
                    <i class="fas fa-chart-bar icon-bg"></i>
                </div>
                <div class="dash-card card-alt">
                    <h2>{{ $todayExpenses }}</h2>
                    <p>Today's Expense</p>
                    <i class="fas fa-file-invoice-dollar icon-bg"></i>
                </div>
            </div>

            <div class="row">
                <!-- Top Selling Products -->
                <div class="col-lg-5">
                    <div class="dash-section-header">Top Selling Products</div>
                    <div class="dash-table-container table-responsive">
                        <table class="premium-table compact table-hover w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Product Details</th>
                                    <th class="text-center">Sold</th>
                                    <th class="text-center">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topSellingItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="action-circle me-2 bg-{{ $item['color'] }}-soft text-{{ $item['color'] }}">
                                                <i class="{{ $item['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $item['name'] }}</div>
                                                <div class="extra-small text-muted">{{ $item['category'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold">{{ (int)$item['sales'] }}</td>
                                    <td class="text-center text-success fw-bold">{{ $item['revenue'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{ route('simple-accounting.top-products') }}" class="dash-btn-more">View More</a>
                    </div>
                </div>

                <!-- Outlet Performance -->
                <div class="col-lg-7">
                    <div class="dash-section-header blue">Outlet Monthly Performance</div>
                    <div class="dash-table-container table-responsive">
                        <table class="premium-table compact table-hover w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>Outlet</th>
                                    <th class="text-center">Today Sales</th>
                                    <th class="text-center">Today Qty</th>
                                    <th class="text-center">Month Sales</th>
                                    <th class="text-center">Month Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $gtAmountMonth = 0; $gtQtyMonth = 0; @endphp
                                @foreach($outletPerformance as $outlet)
                                    @php 
                                        $gtAmountMonth += $outlet['month_amount']; 
                                        $gtQtyMonth += $outlet['month_qty']; 
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $outlet['name'] }}</td>
                                        <td class="text-center">{{ number_format($outlet['today_amount'], 2) }}</td>
                                        <td class="text-center">{{ $outlet['today_qty'] }}</td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($outlet['month_amount'], 2) }}</td>
                                        <td class="text-center">{{ $outlet['month_qty'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold" style="background: #f8fafc;">
                                    <td class="text-end pe-4">Total</td>
                                    <td colspan="2"></td>
                                    <td class="text-center text-success">{{ number_format($gtAmountMonth, 2) }}</td>
                                    <td class="text-center text-success">{{ $gtQtyMonth }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Low Stock Products Information -->
            <div class="dash-section-header blue mt-4">Low Stock Products Information</div>
            <div class="dash-table-container table-responsive">
                <table class="premium-table compact table-hover w-100 mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">#SN.</th>
                            <th>Outlet</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Product Name</th>
                            <th>Style</th>
                            <th>Size</th>
                            <th class="text-center">Limit</th>
                            <th class="text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockDetailed as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item['branch'] }}</td>
                            <td>{{ $item['category'] }}</td>
                            <td>{{ $item['brand'] }}</td>
                            <td>{{ $item['product_name'] }}</td>
                            <td>{{ $item['style_number'] }}</td>
                            <td class="text-center">{{ $item['size'] }}</td>
                            <td class="text-center">{{ $item['limit'] }}</td>
                            <td class="text-center text-danger fw-bold">{{ $item['current'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ route('productstock.list') }}" class="dash-btn-more">View More</a>
            </div>

            <!-- Recent Sales -->
            <div class="dash-section-header">Recent Sales</div>
            <div class="dash-table-container table-responsive">
                <table class="premium-table compact table-hover w-100 mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">No.</th>
                            <th>Invoice / Challan</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Paid</th>
                            <th class="text-center">Due</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSalesDetailed as $index => $sale)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold">{{ $sale['invoice_no'] }}</div>
                                <div class="extra-small text-muted">CH: {{ $sale['challan_no'] }}</div>
                            </td>
                            <td>{{ $sale['date'] }}</td>
                            <td>{{ $sale['customer'] }}</td>
                            <td class="text-center fw-bold">{{ number_format($sale['total'], 2) }}</td>
                            <td class="text-center text-success">{{ number_format($sale['paid'], 2) }}</td>
                            <td class="text-center text-danger">{{ number_format($sale['due'], 2) }}</td>
                            <td class="text-center">
                                <span class="badge @if($sale['status'] == 'delivered') bg-success @else bg-primary @endif">
                                    {{ ucfirst($sale['status']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ route('pos.list') }}" class="dash-btn-more">View More</a>
            </div>

            <!-- Chart Section -->
            <div class="dash-chart-card premium-shadow border-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="dash-chart-title mb-1">Sales Performance Trends</h3>
                        <p class="text-muted small mb-0">Analysis of daily quantity and revenue for the last 7 days</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Last 7 Days
                        </button>
                    </div>
                </div>
                <div style="height: 420px; position: relative;">
                    <canvas id="salesTrendsChart"></canvas>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesTrendsChart').getContext('2d');
    const chartLabels = @json($salesQtyChart['labels']);
    const qtyData = @json($salesQtyChart['qtyData']);
    const revData = @json($salesQtyChart['revData']);

    // Create Gradients
    const gradientRev = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRev.addColorStop(0, 'rgba(15, 118, 110, 0.2)');
    gradientRev.addColorStop(1, 'rgba(15, 118, 110, 0)');

    const gradientQty = ctx.createLinearGradient(0, 0, 0, 400);
    gradientQty.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    gradientQty.addColorStop(1, 'rgba(59, 130, 246, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Revenue (৳)',
                    data: revData,
                    borderColor: '#0f766e',
                    backgroundColor: gradientRev,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0f766e',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'yRevenue'
                },
                {
                    label: 'Quantity',
                    data: qtyData,
                    borderColor: '#3b82f6',
                    backgroundColor: gradientQty,
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    yAxisID: 'yQty'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 13 },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.datasetIndex === 0) {
                                label += context.raw.toLocaleString() + ' ৳';
                            } else {
                                label += context.raw;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                yRevenue: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: { display: true, text: 'Revenue (৳)', font: { weight: 'bold' } },
                    grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                    ticks: { color: '#64748b' }
                },
                yQty: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: { display: true, text: 'Quantity', font: { weight: 'bold' } },
                    grid: { display: false },
                    ticks: { color: '#64748b' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endpush