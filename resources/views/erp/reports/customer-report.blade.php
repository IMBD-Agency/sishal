@extends('erp.master')

@section('title', 'Customer Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Customer Report</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Customer Reports</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-light fw-bold small">CSV</button>
                        <button class="btn btn-light fw-bold small">Excel</button>
                        <button class="btn btn-light fw-bold small">PDF</button>
                        <button class="btn btn-light fw-bold small" onclick="window.print()">Print</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container-fluid px-4 py-4">
            
            <!-- Advanced Filters -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-header bg-white border-bottom p-4">
                     <form id="filterForm" method="GET">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Filter Reports</h6>
                            <!-- Report Period Toggles -->
                            <div class="d-flex gap-3">
                                 <div class="form-check cursor-pointer">
                                    <input class="form-check-input filter-input cursor-pointer" type="radio" name="report_type" id="daily" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="daily">Daily Reports</label>
                                </div>
                                <div class="form-check cursor-pointer">
                                    <input class="form-check-input filter-input cursor-pointer" type="radio" name="report_type" id="monthly" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthly">Monthly Reports</label>
                                </div>
                                <div class="form-check cursor-pointer">
                                    <input class="form-check-input filter-input cursor-pointer" type="radio" name="report_type" id="yearly" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearly">Yearly Reports</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-2 daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date *</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-2 daily-group">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date *</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                            </div>
                            
                            <div class="col-md-2 monthly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select form-select-sm">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (request('month') ?? date('m')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 monthly-group yearly-group" style="display: none;">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select form-select-sm">
                                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                                        <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Outlet (Branch)</label>
                                <select name="branch_id" class="form-select form-select-sm select2-simple">
                                    <option value="">All Outlets</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                             <div class="col-md-2">
                                 <button type="submit" class="btn btn-create-premium btn-sm w-100" style="height: 31px;">
                                     <i class="fas fa-search me-1"></i> Search
                                 </button>
                             </div>
                        </div>
                     </form>
                </div>
            </div>

            <!-- Table -->
             <div class="premium-card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact-reporting-table table-bordered mb-0">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th class="text-center">#SN</th>
                                    <th>Customer Name</th>
                                    <th>Outlet</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Payment</th>
                                    <th class="text-end text-danger">Discount</th>
                                    <th class="text-end text-warning">Return</th>
                                    <th class="text-end text-info">Exchange</th>
                                    <th class="text-end fw-bold">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totals = [
                                        'opening' => 0, 'sales' => 0, 'paid' => 0, 'payment' => 0, 
                                        'discount' => 0, 'return' => 0, 'exchange' => 0, 'due' => 0
                                    ];
                                @endphp
                                @forelse($customers as $index => $c)
                                    @php
                                        $totals['opening'] += $c->opening;
                                        $totals['sales'] += $c->sales;
                                        $totals['paid'] += $c->paid;
                                        $totals['payment'] += $c->payment;
                                        $totals['discount'] += $c->discount;
                                        $totals['return'] += $c->return;
                                        $totals['exchange'] += $c->exchange;
                                        $totals['due'] += $c->due;
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td class="fw-bold">
                                            <a href="{{ route('reports.customer.ledger', $c->id) }}" class="text-decoration-none text-primary">
                                                {{ $c->name }}
                                            </a>
                                        </td>
                                        <td>{{ $c->outlet }}</td>
                                        <td class="text-end">{{ number_format($c->opening, 2) }}</td>
                                        <td class="text-end">{{ number_format($c->sales, 2) }}</td>
                                        <td class="text-end">{{ number_format($c->paid, 2) }}</td>
                                        <td class="text-end">{{ number_format($c->payment, 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($c->discount, 2) }}</td>
                                        <td class="text-end text-warning">{{ number_format($c->return, 2) }}</td>
                                        <td class="text-end text-info">{{ number_format($c->exchange, 2) }}</td>
                                        <td class="text-end fw-bold {{ $c->due > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($c->due, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">No data available in table</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end text-uppercase">Grand Total</td>
                                    <td class="text-end">{{ number_format($totals['opening'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['sales'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['paid'], 2) }}</td>
                                    <td class="text-end">{{ number_format($totals['payment'], 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($totals['discount'], 2) }}</td>
                                    <td class="text-end text-warning">{{ number_format($totals['return'], 2) }}</td>
                                    <td class="text-end text-info">{{ number_format($totals['exchange'], 2) }}</td>
                                    <td class="text-end {{ $totals['due'] > 0 ? 'text-danger' : 'text-success' }} fs-6">
                                        {{ number_format($totals['due'], 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
             </div>
             
             <div class="text-center mt-4">
                 <button class="btn btn-primary px-4 fw-bold shadow-sm" onclick="window.print()">
                     <i class="fas fa-print me-2"></i> Print
                 </button>
             </div>
        </div>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-simple').select2({
                width: '100%'
            });

            // Toggle date groups based on report type
            $('input[name="report_type"]').on('change', function() {
                toggleDateGroups();
            });

            function toggleDateGroups() {
                const type = $('input[name="report_type"]:checked').val();
                $('.daily-group, .monthly-group, .yearly-group').hide();
                
                if (type === 'daily') {
                    $('.daily-group').show();
                } else if (type === 'monthly') {
                    $('.monthly-group').show();
                    $('.yearly-group').show();
                } else if (type === 'yearly') {
                    $('.yearly-group').show();
                }
            }

            toggleDateGroups();
        });
    </script>
    @endpush
@endsection
