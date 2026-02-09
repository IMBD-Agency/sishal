@extends('erp.master')

@section('title', 'Executive Business Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">Executive Business Performance</h3>
                    <p class="text-muted small mb-0">Full business overview and profitability analysis</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger btn-sm px-3 rounded-pill border-2 fw-bold">
                        <i class="fas fa-file-pdf me-2"></i>PDF Report
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success btn-sm px-3 rounded-pill border-2 fw-bold">
                        <i class="fas fa-file-excel me-2"></i>Excel Export
                    </a>
                </div>
            </div>

            <!-- Enhanced Filter Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="bg-primary bg-opacity-10 px-4 py-2 border-bottom">
                        <span class="small fw-bold text-primary text-uppercase"><i class="fas fa-filter me-2"></i>Report Filters</span>
                    </div>
                    <form method="GET" action="{{ route('reports.executive') }}" class="p-4 row g-3 align-items-end" id="filterForm">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">Branch / Shop</label>
                            <select name="branch_id" class="form-select border-0 bg-light rounded-3" onchange="this.form.submit()">
                                <option value="">Global (All Branches)</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control border-0 bg-light rounded-3" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control border-0 bg-light rounded-3" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3 py-2 shadow-sm">
                                <i class="fas fa-sync-alt me-2"></i>Update
                            </button>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-2 justify-content-end mb-1 report-type-toggle">
                                <div class="form-check small p-0 m-0">
                                    <input class="btn-check" type="radio" name="report_type" id="daily" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }} onclick="setDateRange('daily')">
                                    <label class="btn btn-sm rounded-pill px-3" for="daily">Daily</label>
                                </div>
                                <div class="form-check small p-0 m-0">
                                    <input class="btn-check" type="radio" name="report_type" id="monthly" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }} onclick="setDateRange('monthly')">
                                    <label class="btn btn-sm rounded-pill px-3" for="monthly">Monthly</label>
                                </div>
                                <div class="form-check small p-0 m-0">
                                    <input class="btn-check" type="radio" name="report_type" id="yearly" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }} onclick="setDateRange('yearly')">
                                    <label class="btn btn-sm rounded-pill px-3" for="yearly">Yearly</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Premium Summary Widgets -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white;">
                        <div class="card-body p-4 position-relative z-1">
                            <p class="small text-white-50 mb-1 fw-bold text-uppercase">Gross Revenue</p>
                            <h2 class="fw-bold mb-0">৳{{ number_format($grossRevenue, 2) }}</h2>
                            <div class="mt-3 small opacity-75">
                                <i class="fas fa-shopping-cart me-1"></i> Retail + Online
                            </div>
                        </div>
                        <i class="fas fa-chart-line position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                        <div class="card-body p-4 position-relative z-1">
                            <p class="small text-white-50 mb-1 fw-bold text-uppercase">Inventory Cost</p>
                            <h2 class="fw-bold mb-0">৳{{ number_format($totalCogs, 2) }}</h2>
                            <div class="mt-3 small opacity-75">
                                <i class="fas fa-box-open me-1"></i> Cost of Goods Sold
                            </div>
                        </div>
                        <i class="fas fa-box position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white;">
                        <div class="card-body p-4 position-relative z-1">
                            <p class="small text-white-50 mb-1 fw-bold text-uppercase">Total Expenses</p>
                            <h2 class="fw-bold mb-0">৳{{ number_format($totalExpenses, 2) }}</h2>
                            <div class="mt-3 small opacity-75">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Bills & Salaries
                            </div>
                        </div>
                        <i class="fas fa-receipt position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative shadow-lg" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white;">
                        <div class="card-body p-4 position-relative z-1">
                            <p class="small text-white-50 mb-1 fw-bold text-uppercase">Net Portfolio Profit</p>
                            <h2 class="fw-bold mb-0">৳{{ number_format($netProfit, 2) }}</h2>
                            <div class="mt-3 small opacity-100 fw-bold">
                                <i class="fas fa-wallet me-1"></i> Final Profitability
                            </div>
                        </div>
                        <i class="fas fa-piggy-bank position-absolute bottom-0 end-0 p-3 fs-1 opacity-25" style="transform: scale(1.5);"></i>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Detailed Table -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark">Financial Breakdown</h5>
                            <span class="badge bg-light text-dark fw-bold border">{{ $reportType }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Category Details</th>
                                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Volume / Type</th>
                                            <th class="py-3 border-0 small text-uppercase text-muted text-end pe-4">Current Period (৳)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="3" class="ps-4 py-2 border-0 small fw-bold text-primary bg-primary bg-opacity-10 text-uppercase">Income Streams</td></tr>
                                        <tr>
                                            <td class="ps-4 border-0">
                                                <div class="fw-bold text-dark">Retail Sales (POS)</div>
                                                <div class="small text-muted">In-store transactions</div>
                                            </td>
                                            <td class="text-center border-0"><span class="badge bg-white text-dark border">{{ $posSales->count }} Orders</span></td>
                                            <td class="text-end pe-4 border-0 fw-bold">৳{{ number_format($posSales->net_sales ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 border-0">
                                                <div class="fw-bold text-dark">Online Orders</div>
                                                <div class="small text-muted">Ecommerce sales</div>
                                            </td>
                                            <td class="text-center border-0"><span class="badge bg-white text-dark border">{{ $onlineSales->count }} Orders</span></td>
                                            <td class="text-end pe-4 border-0 fw-bold">৳{{ number_format($onlineSales->net_sales ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 border-0">
                                                <div class="fw-bold text-dark">Other Income (Vouchers)</div>
                                                <div class="small text-muted">Misc. revenue & credits</div>
                                            </td>
                                            <td class="text-center border-0"><span class="badge bg-white text-dark border">-</span></td>
                                            <td class="text-end pe-4 border-0 fw-bold">৳{{ number_format($creditVoucher ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4 border-0">
                                                <div class="fw-bold text-dark">Money Receipts</div>
                                                <div class="small text-muted">Direct cash inputs</div>
                                            </td>
                                            <td class="text-center border-0"><span class="badge bg-white text-dark border">-</span></td>
                                            <td class="text-end pe-4 border-0 fw-bold">৳{{ number_format($moneyReceipt ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="bg-light">
                                            <td class="ps-4 fw-bold">TOTAL GROSS REVENUE</td>
                                            <td class="text-center">---</td>
                                            <td class="text-end pe-4 fw-bold text-primary">৳{{ number_format($grossRevenue, 2) }}</td>
                                        </tr>
                                        
                                        <tr><td colspan="3" class="ps-4 py-2 border-0 small fw-bold text-danger bg-danger bg-opacity-10 text-uppercase">Inventory Cost (COGS)</td></tr>
                                        <tr>
                                            <td class="ps-4 border-0">
                                                <div class="fw-bold text-dark text-danger">Stock Depletion Value</div>
                                                <div class="small text-muted">Actual purchase cost of items sold</div>
                                            </td>
                                            <td class="text-center border-0 small text-muted">Avg. Unit Cost</td>
                                            <td class="text-end pe-4 border-0 text-danger fw-bold">-৳{{ number_format($totalCogs, 2) }}</td>
                                        </tr>
                                        <tr class="bg-success bg-opacity-10">
                                            <td class="ps-4 fw-bold text-success text-uppercase">Gross Operational Profit</td>
                                            <td class="text-center small text-success fw-bold">{{ $grossRevenue > 0 ? number_format(($grossProfit / $grossRevenue) * 100, 1) : 0 }}% Margin</td>
                                            <td class="text-end pe-4 fw-bold text-success">৳{{ number_format($grossProfit, 2) }}</td>
                                        </tr>

                                        <tr><td colspan="3" class="ps-4 py-2 border-0 small fw-bold text-warning bg-warning bg-opacity-10 text-uppercase">Operating Expenses</td></tr>
                                        @forelse($operatingExpenses as $expense)
                                        <tr>
                                            <td class="ps-4 border-0">
                                                <div class="fw-bold text-dark">{{ $expense->name }}</div>
                                            </td>
                                            <td class="text-center border-0 small text-muted">Ledger Entry</td>
                                            <td class="text-end pe-4 border-0 text-muted">-৳{{ number_format($expense->total, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted small">No expenses logged for this period</td></tr>
                                        @endforelse
                                        <tr class="bg-dark text-white">
                                            <td class="ps-4 fw-bold py-3 text-uppercase">Net Business Profit</td>
                                            <td class="text-center py-3 small opacity-75">Bottom Line</td>
                                            <td class="text-end pe-4 fw-bold py-3 fs-5">৳{{ number_format($netProfit, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appraisal Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="fw-bold mb-0 text-dark">Stock Wealth Appraisal</h5>
                            <p class="text-muted small mb-0">Your current asset valuation</p>
                        </div>
                        <div class="card-body pt-0">
                            <div class="p-4 bg-light rounded-4 mb-4 text-center">
                                <span class="small text-muted d-block text-uppercase fw-bold mb-1">Total Items In Hand</span>
                                <h3 class="fw-bold text-dark mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Valuation Summary</h3>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="small text-muted fw-bold text-uppercase">Investment (Cost)</label>
                                    <span class="small fw-bold text-primary">৳{{ number_format($stockValue->total_cost, 0) }}</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 10px; background-color: #e9ecef;">
                                    <div class="progress-bar rounded-pill bg-primary" style="width: 35%"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="small text-muted fw-bold text-uppercase">Wholesale (B2B)</label>
                                    <span class="small fw-bold text-info">৳{{ number_format($stockValue->total_wholesale, 0) }}</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 10px; background-color: #e9ecef;">
                                    <div class="progress-bar rounded-pill bg-info" style="width: 65%"></div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <label class="small text-muted fw-bold text-uppercase">Retail (MRP)</label>
                                    <span class="small fw-bold text-success">৳{{ number_format($stockValue->total_mrp, 0) }}</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 10px; background-color: #e9ecef;">
                                    <div class="progress-bar rounded-pill bg-success" style="width: 100%"></div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <span class="small text-muted">Potential Net Margin:</span>
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-bold">
                                    {{ $stockValue->total_cost > 0 ? number_format((($stockValue->total_mrp - $stockValue->total_cost) / $stockValue->total_cost) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function setDateRange(type) {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const now = new Date();
        
        const formatDate = (date) => {
            let d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }

        if (type === 'daily') {
            startDate.value = formatDate(now);
            endDate.value = formatDate(now);
        } else if (type === 'monthly') {
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            startDate.value = formatDate(firstDay);
            endDate.value = formatDate(lastDay);
        } else if (type === 'yearly') {
            const firstDay = new Date(now.getFullYear(), 0, 1);
            const lastDay = new Date(now.getFullYear(), 11, 31);
            startDate.value = formatDate(firstDay);
            endDate.value = formatDate(lastDay);
        }
        // Smooth transition feedback
        document.body.classList.remove('loaded');
        document.body.style.opacity = "0.6";
        const bar = document.getElementById('top-progress-bar');
        if (bar) bar.style.width = '100%';

        // Auto submit form on radio click to update report
        document.getElementById('filterForm').submit();
    }
</script>
