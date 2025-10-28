@extends('erp.master')

@section('title', 'Sales Summary')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Sales Summary</h4>
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" id="dateRange" onchange="updateDateRange()">
                                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ $dateRange == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ $dateRange == 'month' ? 'selected' : '' }}>This Month</option>
                                    <option value="quarter" {{ $dateRange == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                                    <option value="year" {{ $dateRange == 'year' ? 'selected' : '' }}>This Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ number_format($salesData['total_revenue'], 2) }}৳</h4>
                                                    <p class="mb-0">Total Revenue</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ number_format($profitData['gross_profit'], 2) }}৳</h4>
                                                    <p class="mb-0">Gross Profit</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-chart-line fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ number_format($costData['total_costs'], 2) }}৳</h4>
                                                    <p class="mb-0">Total Costs</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-coins fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0">{{ number_format($profitData['profit_margin'], 1) }}%</h4>
                                                    <p class="mb-0">Profit Margin</p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-percentage fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Breakdown -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Sales Breakdown</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Online Orders</strong></td>
                                                            <td class="text-end">{{ number_format($salesData['order_revenue'], 2) }}৳</td>
                                                            <td class="text-end">{{ $salesData['order_count'] }} orders</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>POS Sales</strong></td>
                                                            <td class="text-end">{{ number_format($salesData['pos_revenue'], 2) }}৳</td>
                                                            <td class="text-end">{{ $salesData['pos_count'] }} sales</td>
                                                        </tr>
                                                        <tr class="table-primary">
                                                            <td><strong>Total Sales</strong></td>
                                                            <td class="text-end"><strong>{{ number_format($salesData['total_revenue'], 2) }}৳</strong></td>
                                                            <td class="text-end"><strong>{{ $salesData['total_sales_count'] }} transactions</strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Cost Breakdown</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Order Costs</strong></td>
                                                            <td class="text-end">{{ number_format($costData['order_costs'], 2) }}৳</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>POS Costs</strong></td>
                                                            <td class="text-end">{{ number_format($costData['pos_costs'], 2) }}৳</td>
                                                        </tr>
                                                        <tr class="table-warning">
                                                            <td><strong>Total Costs</strong></td>
                                                            <td class="text-end"><strong>{{ number_format($costData['total_costs'], 2) }}৳</strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profit Analysis -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Profit Analysis</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="text-center">
                                                        <h6 class="text-muted">Revenue</h6>
                                                        <h4 class="text-primary">{{ number_format($salesData['total_revenue'], 2) }}৳</h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center">
                                                        <h6 class="text-muted">Costs</h6>
                                                        <h4 class="text-warning">{{ number_format($costData['total_costs'], 2) }}৳</h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center">
                                                        <h6 class="text-muted">Profit</h6>
                                                        <h4 class="text-success">{{ number_format($profitData['gross_profit'], 2) }}৳</h4>
                                                        <small class="text-muted">{{ number_format($profitData['profit_margin'], 1) }}% margin</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateDateRange() {
        const range = document.getElementById('dateRange').value;
        const url = new URL(window.location);
        url.searchParams.set('range', range);
        window.location.href = url.toString();
    }
    </script>
@endsection