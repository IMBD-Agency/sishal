@extends('erp.master')

@section('title', 'Profit & Loss Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <h4 class="mb-4 text-dark">Profit / Loss Reports</h4>

            <!-- Filter Section -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.profit-loss') }}" id="filterForm">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="report_type" id="daily" value="daily" checked onclick="setDateRange('daily')">
                                <label class="form-check-label fw-bold" for="daily">Daily Reports</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="report_type" id="monthly" value="monthly" onclick="setDateRange('monthly')">
                                <label class="form-check-label fw-bold" for="monthly">Monthly Reports</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="report_type" id="yearly" value="yearly" onclick="setDateRange('yearly')">
                                <label class="form-check-label fw-bold" for="yearly">Yearly Reports</label>
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label small fw-bold">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label class="form-label small fw-bold">End Date *</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-info text-white fw-bold"><i class="fas fa-search me-1"></i> Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Table -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr class="bg-success text-white">
                                    <th class="w-50 text-center py-2">Income</th>
                                    <th class="w-50 text-center py-2">Expense</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Sales Amount</span>
                                            <span>{{ number_format($salesAmount, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Purchase Amount</span>
                                            <span>{{ number_format($purchaseAmount, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr>
                                    <td class="p-0 bg-light">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Credit Voucher</span>
                                            <span>{{ number_format($creditVoucher, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0 bg-light">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Debit Voucher</span>
                                            <span>{{ number_format($debitVoucher, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Stock Amount</span>
                                            <span>{{ number_format($stockAmount, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Employee Payment</span>
                                            <span>{{ number_format($employeePayment, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr>
                                    <td class="p-0 bg-light">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Money Receipt</span>
                                            <span>{{ number_format($moneyReceipt, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0 bg-light">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Supplier Pay</span>
                                            <span>{{ number_format($supplierPay, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 5 -->
                                <tr>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Purchase Returns</span>
                                            <span>{{ number_format($purchaseReturnAmount, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Sales Returns</span>
                                            <span>{{ number_format($salesReturnAmount, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 6 -->
                                <tr>
                                    <td class="p-0 bg-light">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Exchange Amount</span>
                                            <span>{{ number_format($exchangeAmount, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0 bg-light">
                                        <div class="d-flex justify-content-between p-2">
                                            <!-- Empty Cell -->
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 7 -->
                                <tr>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Sender Transfer Amount</span>
                                            <span>{{ number_format($senderTransferAmount, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2">
                                            <span>Receiver Transfer Amount</span>
                                            <span>{{ number_format($receiverTransferAmount, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Totals -->
                                <tr class="fw-bold">
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2 border-top">
                                            <span>Total Income</span>
                                            <span>{{ number_format($totalIncome, 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-0">
                                        <div class="d-flex justify-content-between p-2 border-top">
                                            <span>Total Expense</span>
                                            <span>{{ number_format($totalExpense, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Net Profit -->
            <div class="text-center mt-4">
                <h5 class="fw-bold fs-5">Net Profit / Loss <span class="ms-3 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfit, 2) }}</span></h5>
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
