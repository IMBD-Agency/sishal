@extends('erp.master')

@section('title', 'Supplier Payment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 breadcrumb-premium">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Supplier Payments</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-success text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Supplier Payment History</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('supplier-payments.create') }}" class="btn btn-create-premium">
                        <i class="fas fa-plus-circle me-2"></i>Record New Payment
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 fw-bold">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <!-- Filters Section -->
            <div class="premium-card mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Advanced Payment Filter</h6>
                        <div class="d-flex gap-3">
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type_active" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="dailyReport">Manual Range</label>
                            </div>
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type_active" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="monthlyReport">Monthly</label>
                            </div>
                            <div class="form-check cursor-pointer">
                                <input class="form-check-input report-type-radio cursor-pointer" type="radio" name="report_type_active" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted cursor-pointer" for="yearlyReport">Yearly</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('supplier-payments.index') }}" id="filterForm">
                        <input type="hidden" name="report_type" id="report_type_hidden" value="{{ $reportType }}">
                        
                        <div class="row g-3">
                            <!-- Daily Range -->
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control shadow-none" value="{{ request('start_date') }}">
                            </div>

                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control shadow-none" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-2 month-field {{ $reportType != 'monthly' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select shadow-none">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly/Shared Year Field -->
                            <div class="col-md-2 year-field {{ $reportType == 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Fiscal Year</label>
                                <select name="year" class="form-select shadow-none">
                                    @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Voucher ID</label>
                                <select name="payment_no" class="form-select select2-simple shadow-none">
                                    <option value="all">All Vouchers</option>
                                    @foreach($allPayments as $payment)
                                        <option value="{{ $payment->id }}" {{ request('payment_no') == $payment->id ? 'selected' : '' }}>
                                            #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Supplier</label>
                                <select name="supplier_id" class="form-select select2-simple shadow-none">
                                    <option value="all">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Bill Reference</label>
                                <select name="challan_no" class="form-select select2-simple shadow-none">
                                    <option value="all">All Bills</option>
                                    @foreach($allBills as $bill)
                                        <option value="{{ $bill->id }}" {{ request('challan_no') == $bill->id ? 'selected' : '' }}>
                                            {{ $bill->bill_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Method</label>
                                <select name="payment_method" class="form-select shadow-none">
                                    <option value="all">All Methods</option>
                                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-2">
                            <button type="submit" class="btn btn-create-premium px-4">
                                <i class="fas fa-search me-2"></i>Apply Filters
                            </button>
                            <a href="{{ route('supplier-payments.index') }}" class="btn btn-light border fw-bold px-4">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <a href="{{ route('supplier-payments.export.excel', request()->all()) }}" class="btn btn-outline-dark btn-sm fw-bold shadow-sm">
                        <i class="fas fa-file-excel me-1 text-success"></i> Excel
                    </a>
                    <a href="{{ route('supplier-payments.export.pdf', request()->all()) }}" class="btn btn-outline-dark btn-sm fw-bold shadow-sm">
                        <i class="fas fa-file-pdf me-1 text-danger"></i> PDF
                    </a>
                </div>
                <div class="position-relative search-wrap-premium">
                    <i class="fas fa-search position-absolute top-50 start-3 translate-middle-y text-muted opacity-50"></i>
                    <input type="text" id="tableSearch" class="form-control form-control-sm ps-5 border-2 rounded-pill shadow-none" placeholder="Search history...">
                </div>
            </div>

            <!-- Table Section -->
            <div class="premium-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table table-hover align-middle mb-0" id="paymentTable">
                            <thead>
                                <tr>
                                    <th class="ps-3">Serial</th>
                                    <th>Voucher ID</th>
                                    <th>Payment Date</th>
                                    <th>Supplier</th>
                                    <th>Branch/Outlet</th>
                                    <th>Purchase Bill</th>
                                    <th class="text-end">Paid Amount</th>
                                    <th>Method</th>
                                    <th>Recorded By</th>
                                    <th class="text-center pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $index => $payment)
                                <tr>
                                    <td class="ps-3 small text-muted">{{ $payments->firstItem() + $index }}</td>
                                    <td><span class="fw-bold text-primary">SP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                    <td class="small">{{ $payment->payment_date->format('d M, Y') }}</td>
                                    <td><div class="fw-bold text-dark">{{ $payment->supplier->name }}</div></td>
                                    <td class="small">{{ $payment->bill && $payment->bill->purchase ? $payment->bill->purchase->branch->name ?? 'Head Office' : 'N/A' }}</td>
                                    <td>
                                        @if($payment->bill)
                                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $payment->bill->bill_number }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-3 rounded-pill">ADVANCE</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">{{ number_format($payment->amount, 2) }}৳</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info px-2 py-1">{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $payment->creator->name ?? 'System' }}</td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('supplier-payments.show', $payment->id) }}" class="btn btn-sm btn-light border-0 action-circle" title="View Voucher">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            <form action="{{ route('supplier-payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border-0 action-circle" onclick="return confirm('Are you sure? This will reverse the ledger entry.')" title="Void Payment">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted opacity-50">
                                            <i class="fas fa-money-check-alt fa-3x mb-3"></i>
                                            <p class="fw-bold">No payment history found matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($payments->hasPages())
                    <div class="card-footer bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-500">Showing entries {{ $payments->firstItem() }} to {{ $payments->lastItem() }}</small>
                        {{ $payments->links('vendor.pagination.bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Total Amount Display -->
            <div class="mt-4 text-end">
                <div class="d-inline-flex align-items-center gap-3 bg-white border premium-card px-4 py-3 shadow-sm">
                    <span class="fw-bold text-muted text-uppercase small">Total Disbursed:</span>
                    <span class="h4 fw-bold text-success mb-0">{{ number_format($payments->sum('amount'), 2) }}৳</span>
                </div>
            </div>
        </div>
    </div>

@push('css')
        .breadcrumb-premium { font-size: 0.85rem; }
        .search-wrap-premium { width: 250px; }
        .action-circle {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50% !important;
            transition: all 0.2s;
        }
        .action-circle:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Report type toggles
            $('.report-type-radio').on('change', function() {
                const val = $(this).val();
                $('#report_type_hidden').val(val);
                
                // Toggle visibility
                if(val === 'daily') {
                    $('.date-range-field').removeClass('d-none').show();
                    $('.month-field, .year-field').addClass('d-none').hide();
                } else if(val === 'monthly') {
                    $('.month-field, .year-field').removeClass('d-none').show();
                    $('.date-range-field').addClass('d-none').hide();
                } else if(val === 'yearly') {
                    $('.year-field').removeClass('d-none').show();
                    $('.date-range-field, .month-field').addClass('d-none').hide();
                }
            });

            // Table search functionality
            $('#tableSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#paymentTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
    @endpush
@endsection
