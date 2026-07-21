@extends('erp.master')

@section('title', 'Cash Profit Report')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-white min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Simple Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Cash Profit / Collection Based Profit Report</h4>
                    <p class="text-muted small mb-0">{{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('reports.cash-profit') }}" id="filterForm">
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

            
            @if(($totalPurchaseAmount ?? 0) > 0 || ($totalSupplierDue ?? 0) > 0 || ($totalDue ?? 0) > 0)
            <!-- Activity & Liability Summary Cards -->
            <div class="row g-3 mb-4">
                @if(($totalPurchaseAmount ?? 0) > 0)
                <div class="col-md-{{ (($totalSupplierDue ?? 0) > 0 && ($totalDue ?? 0) > 0) ? '4' : ((($totalSupplierDue ?? 0) > 0 || ($totalDue ?? 0) > 0) ? '6' : '12') }}">
                    <div class="card border-0 bg-secondary bg-opacity-10 border-start border-secondary border-4 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-secondary bg-opacity-25 p-2 me-3 text-secondary">
                                    <i class="fas fa-boxes fs-4"></i>
                                </div>
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Total Purchase Value</span>
                                    <h5 class="fw-bold mb-0 text-dark">Tk. {{ number_format($totalPurchaseAmount, 2) }}</h5>
                                    <small class="text-muted" style="font-size: 7.5pt;">*Total inventory purchase bills created in this period.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(($totalSupplierDue ?? 0) > 0)
                <div class="col-md-{{ (($totalPurchaseAmount ?? 0) > 0 && ($totalDue ?? 0) > 0) ? '4' : ((($totalPurchaseAmount ?? 0) > 0 || ($totalDue ?? 0) > 0) ? '6' : '12') }}">
                    <div class="card border-0 bg-warning bg-opacity-10 border-start border-warning border-4 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning bg-opacity-25 p-2 me-3 text-warning">
                                    <i class="fas fa-truck-loading fs-4"></i>
                                </div>
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Supplier Purchase Due (Payable)</span>
                                    <h5 class="fw-bold mb-0 text-dark">Tk. {{ number_format($totalSupplierDue, 2) }}</h5>
                                    <small class="text-muted" style="font-size: 7.5pt;">*Pending purchase liability for this period. Profit accounts for COGS.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(($totalDue ?? 0) > 0)
                <div class="col-md-{{ (($totalPurchaseAmount ?? 0) > 0 && ($totalSupplierDue ?? 0) > 0) ? '4' : ((($totalPurchaseAmount ?? 0) > 0 || ($totalSupplierDue ?? 0) > 0) ? '6' : '12') }}">
                    <div class="card border-0 bg-info bg-opacity-10 border-start border-info border-4 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info bg-opacity-25 p-2 me-3 text-info">
                                    <i class="fas fa-hand-holding-usd fs-4"></i>
                                </div>
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Customer Invoice Due (Receivable)</span>
                                    <h5 class="fw-bold mb-0 text-dark">Tk. {{ number_format($totalDue, 2) }}</h5>
                                    <small class="text-muted" style="font-size: 7.5pt;">*Uncollected invoice amounts generated during this period.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
            <!-- Collection Channel Breakdown (Cash, Bank, Mobile MFS) -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-success bg-opacity-10 border-start border-success border-4 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-25 p-2 me-3 text-success">
                                    <i class="fas fa-money-bill-wave fs-4"></i>
                                </div>
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Cash Book Movement</span>
                                    <h5 class="fw-bold mb-0 {{ ($cashCollection ?? 0) < 0 ? 'text-danger' : 'text-dark' }}">
                                        {{ ($cashCollection ?? 0) < 0 ? '- Tk. ' . number_format(abs($cashCollection), 2) : 'Tk. ' . number_format($cashCollection ?? 0, 2) }}
                                    </h5>
                                    <small class="text-muted" style="font-size: 7.5pt;">*Net cash book movement (Debit - Credit)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 bg-primary bg-opacity-10 border-start border-primary border-4 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-25 p-2 me-3 text-primary">
                                    <i class="fas fa-university fs-4"></i>
                                </div>
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Bank Book Movement</span>
                                    <h5 class="fw-bold mb-0 {{ ($bankCollection ?? 0) < 0 ? 'text-danger' : 'text-dark' }}">
                                        {{ ($bankCollection ?? 0) < 0 ? '- Tk. ' . number_format(abs($bankCollection), 2) : 'Tk. ' . number_format($bankCollection ?? 0, 2) }}
                                    </h5>
                                    <small class="text-muted" style="font-size: 7.5pt;">*Net bank & card movement (Debit - Credit)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 border-start border-4 shadow-sm h-100" style="background-color: #f3e8ff; border-left-color: #9333ea !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle p-2 me-3 text-white" style="background-color: #9333ea;">
                                    <i class="fas fa-mobile-alt fs-4"></i>
                                </div>
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Mobile Book Movement (MFS)</span>
                                    <h5 class="fw-bold mb-0 {{ ($mobileCollection ?? 0) < 0 ? 'text-danger' : 'text-dark' }}">
                                        {{ ($mobileCollection ?? 0) < 0 ? '- Tk. ' . number_format(abs($mobileCollection), 2) : 'Tk. ' . number_format($mobileCollection ?? 0, 2) }}
                                    </h5>
                                    <small class="text-muted" style="font-size: 7.5pt;">*Net bKash, Nagad, Rocket (Debit - Credit)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Left/Right Summary Table -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="w-50 text-center py-3 text-uppercase small text-secondary">Cash Inflows & Realized Profit</th>
                                    <th class="w-50 text-center py-3 text-uppercase small text-secondary">Expenses & Outflows</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-0 align-top">
                                        <table class="table table-sm table-borderless mb-0">
                                            {{-- Core collection income --}}
                                            <tr class="table-light">
                                                <td class="ps-3 py-2 text-secondary fw-semibold" colspan="2"><small class="text-uppercase text-muted">Collection Income</small></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3 py-2 text-dark fw-bold">Current Sales Collection (Net)</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-dark fs-6">Tk. {{ number_format($totalCollected, 2) }}</td>
                                            </tr>
                                            @if(($totalGrossPayments ?? 0) > 0 || ($totalReturnRefunds ?? 0) > 0 || ($totalExchangeRefunds ?? 0) > 0)
                                            <tr>
                                                <td class="ps-4 py-1 text-secondary small">↳ Gross Sales Payment Collected</td>
                                                <td class="pe-3 py-1 text-end text-secondary small">Tk. {{ number_format($totalGrossPayments ?? 0, 2) }}</td>
                                            </tr>
                                            @if(($totalReturnRefunds ?? 0) > 0)
                                            <tr>
                                                <td class="ps-4 py-1 text-danger small">↳ Less: Sale Returns Cash Refunded</td>
                                                <td class="pe-3 py-1 text-end text-danger small">- Tk. {{ number_format($totalReturnRefunds, 2) }}</td>
                                            </tr>
                                            @endif
                                            @if(($totalExchangeRefunds ?? 0) > 0)
                                            <tr>
                                                <td class="ps-4 py-1 text-warning small">↳ Less: POS Exchange Cash Refunded</td>
                                                <td class="pe-3 py-1 text-end text-warning small">- Tk. {{ number_format($totalExchangeRefunds, 2) }}</td>
                                            </tr>
                                            @elseif(($totalExchangeCount ?? 0) > 0)
                                            <tr>
                                                <td class="ps-4 py-1 text-warning small">
                                                    ↳ POS Exchange Adjusted <small class="text-muted">({{ $totalExchangeCount }} exchange(s) processed)</small>
                                                </td>
                                                <td class="pe-3 py-1 text-end text-muted small">Tk. 0.00</td>
                                            </tr>
                                            @endif
                                            @endif
                                             <tr>
                                                 <td class="ps-3 py-2 text-secondary">↳ Less: Estimated Cost Portion</td>
                                                 <td class="pe-3 py-2 text-end text-warning">— Tk. {{ number_format($totalEstimatedCost, 2) }}</td>
                                             </tr>
                                            <tr>
                                                <td class="ps-3 py-2 fw-semibold">Gross Cash Profit on Sales</td>
                                                <td class="pe-3 py-2 text-end fw-bold text-success">Tk. {{ number_format($totalCashProfit, 2) }}</td>
                                            </tr>

                                            {{-- Other incomes (credit vouchers) --}}
                                            @if($creditVoucherDetails->isNotEmpty())
                                            <tr class="table-light">
                                                <td class="ps-3 py-2 text-secondary fw-semibold" colspan="2"><small class="text-uppercase text-muted">Other Incomes</small></td>
                                            </tr>
                                            @foreach($creditVoucherDetails as $detail)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">{{ $detail->name }}</td>
                                                <td class="pe-3 py-2 text-end text-success">Tk. {{ number_format($detail->amount, 2) }}</td>
                                            </tr>
                                            @endforeach
                                            @endif
                                        </table>
                                    </td>
                                    <td class="p-0 align-top border-start">
                                        <table class="table table-sm table-borderless mb-0">
                                            {{-- Operating expenses --}}
                                            <tr class="table-light">
                                                <td class="ps-3 py-2 text-secondary fw-semibold" colspan="2"><small class="text-uppercase text-muted">Operating Expenses</small></td>
                                            </tr>
                                            @foreach($debitVoucherDetails as $detail)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">{{ $detail->name }}</td>
                                                <td class="pe-3 py-2 text-end text-danger">Tk. {{ number_format($detail->amount, 2) }}</td>
                                            </tr>
                                            @endforeach
                                            @if($employeePayment > 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-secondary">Employee Salaries</td>
                                                <td class="pe-3 py-2 text-end text-danger">Tk. {{ number_format($employeePayment, 2) }}</td>
                                            </tr>
                                            @endif
                                            @if($debitVoucherDetails->isEmpty() && $employeePayment == 0)
                                            <tr>
                                                <td class="ps-3 py-2 text-muted fst-italic" colspan="2">No operating expenses.</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td class="p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-uppercase small text-dark">Total Gross Cash Profit
                                                @if(($totalOtherIncome ?? 0) > 0 || ($exchangeProfitChange ?? 0) != 0)
                                                <small class="text-muted fw-normal">(incl. other items & exchanges)</small>
                                                @endif
                                            </span>
                                            <span class="fw-bold fs-5 text-success">Tk. {{ number_format($totalCashProfit + ($totalOtherIncome ?? 0) + ($exchangeProfitChange ?? 0), 2) }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 border-start">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-uppercase small text-dark">Total Outflow</span>
                                            <span class="fw-bold fs-5 text-danger">Tk. {{ number_format($totalOperatingExpenses, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Net Result (Final Summary Banner) -->
            <div class="mb-4 p-4 border rounded text-center shadow-sm {{ ($netCashProfit ?? 0) >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }}">
                <h6 class="text-uppercase fw-bold mb-1 opacity-75">Net Cash Profit</h6>
                <h1 class="fw-bold mb-0">Tk. {{ number_format($netCashProfit ?? 0, 2) }}</h1>
                <p class="mb-0 fw-bold small mt-2">Gross Cash Profit - Operating Expenses</p>
            </div>

            <!-- Sale Returns Breakdown Table -->
            <!-- @if($saleReturnDetails->isNotEmpty())
            <div class="card border shadow-sm mb-4">
                <div class="card-header bg-danger bg-opacity-10 border-bottom py-2">
                    <h6 class="fw-bold mb-0 text-danger"><i class="fas fa-undo me-2"></i>Sale Returns — {{ $startDate->format('d M, Y') }} to {{ $endDate->format('d M, Y') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle table-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2 small text-secondary">Date</th>
                                    <th class="py-2 small text-secondary">Reference (Original Sale)</th>
                                    <th class="py-2 small text-secondary text-center">Refund Type</th>
                                    <th class="py-2 small text-secondary text-end">Returned Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleReturnDetails as $ret)
                                <tr>
                                    <td class="py-2 small">{{ \Carbon\Carbon::parse($ret->date)->format('d M, Y') }}</td>
                                    <td class="py-2 small fw-bold">{{ $ret->reference }}</td>
                                    <td class="py-2 small text-center">
                                        @if($ret->refund_type === 'cash')
                                            <span class="badge bg-danger">Cash Refund</span>
                                        @elseif($ret->refund_type === 'exchange')
                                            <span class="badge bg-warning text-dark">Exchange</span>
                                        @else
                                            <span class="badge bg-secondary">No Refund</span>
                                        @endif
                                    </td>
                                    <td class="py-2 small text-end text-danger fw-bold">Tk. {{ number_format($ret->return_amount, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr class="table-light">
                                    <td colspan="3" class="py-2 small fw-bold text-end">Total Returned Amount:</td>
                                    <td class="py-2 small text-end fw-bold text-danger">Tk. {{ number_format($saleReturnCashRefund, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif -->

            <!-- Detailed Report Table -->
            <!-- <div class="card border shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 small text-secondary">Date</th>
                                    <th class="py-3 small text-secondary">Reference / Invoice</th>
                                    <th class="py-3 small text-secondary text-end">Sale Amount</th>
                                    <th class="py-3 small text-secondary text-end">Collection Amount</th>
                                    <th class="py-3 small text-secondary text-center">Profit Margin</th>
                                    <th class="py-3 small text-secondary text-end">Estimated Cost</th>
                                    <th class="py-3 small text-secondary text-end">Cash Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashProfits as $profit)
                                <tr>
                                    <td class="py-2 small">{{ \Carbon\Carbon::parse($profit->date)->format('d M, Y') }}</td>
                                    <td class="py-2 small fw-bold">{{ $profit->reference }}</td>
                                    <td class="py-2 small text-end">Tk. {{ number_format($profit->sale_amount, 2) }}</td>
                                    <td class="py-2 small text-end text-primary fw-semibold">Tk. {{ number_format($profit->collection_amount, 2) }}</td>
                                    <td class="py-2 small text-center">
                                        <span class="badge bg-secondary">{{ number_format($profit->profit_margin, 2) }}%</span>
                                    </td>
                                    <td class="py-2 small text-end text-warning">Tk. {{ number_format($profit->estimated_cost, 2) }}</td>
                                    <td class="py-2 small text-end text-success fw-bold">Tk. {{ number_format($profit->cash_profit, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No collection data found for the selected period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> -->

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
