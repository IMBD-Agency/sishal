@extends('erp.master')

@section('title', 'Profit & Loss Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Profit & Loss Report</h4>
                    <p class="text-muted small mb-0">{{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                            <li><a class="dropdown-item py-2" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">PDF Version</a></li>
                            <li><a class="dropdown-item py-2" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">Excel Sheet</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('reports.profit-loss') }}" id="filterForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Report Period</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="daily" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }} onclick="setDateRange('daily')">
                                        <label class="form-check-label small" for="daily">Daily</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="monthly" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }} onclick="setDateRange('monthly')">
                                        <label class="form-check-label small" for="monthly">Monthly</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="report_type" id="yearly" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }} onclick="setDateRange('yearly')">
                                        <label class="form-check-label small" for="yearly">Yearly</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Branch</label>
                                <select name="branch_id" class="form-select form-select-sm">
                                    <option value="">Consolidated View</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ ($branchId ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-dark btn-sm w-100">Analyze</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Detailed Report Table -->
            <div class="card border shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="w-50 text-center py-3 text-uppercase small text-secondary">Income & Revenue Sources</th>
                                    <th class="w-50 text-center py-3 text-uppercase small text-secondary">Expenses & Outflows</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr>
                                    <td class="p-0 align-top">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Sales Revenue</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($salesAmount, 2) }}</td>
                                            </tr>
                                            @foreach($creditVoucherDetails as $detail)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">{{ $detail->name }}</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($detail->amount, 2) }}</td>
                                            </tr>
                                            @endforeach
                                            @if($creditVoucherDetails->isEmpty() && $creditVoucher > 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Credit Vouchers (General)</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($creditVoucher, 2) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Money Receipts</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($moneyReceipt, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Purchase Returns</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($purchaseReturnAmount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Exchange Adjustments</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($exchangeAmount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Transfers In</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark">Tk. {{ number_format($senderTransferAmount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary border-top">Stock Valuation (Asset)</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-muted border-top">Tk. {{ number_format($stockAmount, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="p-0 align-top border-start">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Cost of Goods Sold</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($cogsAmount, 2) }}</td>
                                            </tr>
                                            @if($purchaseAmount > 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Purchase (Inventory)</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($purchaseAmount, 2) }}</td>
                                            </tr>
                                            @endif
                                            @foreach($debitVoucherDetails as $detail)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">{{ $detail->name }}</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($detail->amount, 2) }}</td>
                                            </tr>
                                            @endforeach
                                            @if($debitVoucherDetails->isEmpty() && $debitVoucher > 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Debit Vouchers (General)</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($debitVoucher, 2) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Employee Salaries</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($employeePayment, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Supplier Payments</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($supplierPay, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Sales Returns</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($salesReturnAmount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Transfers Out</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-danger">Tk. {{ number_format($receiverTransferAmount, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <!-- Totals -->
                                <tr class="bg-light">
                                    <td class="p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-uppercase small text-dark">Total Gross Income</span>
                                            <span class="fw-bold fs-5 text-dark">Tk. {{ number_format($totalIncome, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 border-start">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-uppercase small text-dark">Total Gross Expenses</span>
                                            <span class="fw-bold fs-5 text-danger">Tk. {{ number_format($totalExpense, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Net Result -->
            <div class="mt-4 p-4 border rounded text-center shadow-sm {{ ($totalIncome - $totalExpense) >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }}">
                <h6 class="text-uppercase fw-bold mb-1 opacity-75">Net Performance Result</h6>
                <h1 class="fw-bold mb-0">Tk. {{ number_format($totalIncome - $totalExpense, 2) }}</h1>
                <p class="mb-0 fw-bold small mt-2">{{ ($totalIncome - $totalExpense) >= 0 ? 'Surplus (Profit)' : 'Deficit (Loss)' }} for this period</p>
            </div>

        </div>
    </div>

    <script>
        function setDateRange(type) {
            const today = new Date();
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            
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
                startInput.value = formatDate(today);
                endInput.value = formatDate(today);
            } else if (type === 'monthly') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                startInput.value = formatDate(firstDay);
                endInput.value = formatDate(lastDay);
            } else if (type === 'yearly') {
                const firstDay = new Date(today.getFullYear(), 0, 1);
                const lastDay = new Date(today.getFullYear(), 11, 31);
                startInput.value = formatDate(firstDay);
                endInput.value = formatDate(lastDay);
            }
        }
    </script>
@endsection
