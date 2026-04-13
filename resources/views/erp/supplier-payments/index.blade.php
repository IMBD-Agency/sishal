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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
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

            <!-- Summary Bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="summary-card-premium p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 p-3">
                            <i class="fas fa-file-invoice-dollar fs-4"></i>
                        </div>
                        <div>
                            <div class="small opacity-75 text-uppercase fw-bold">Month Total</div>
                            <h4 class="fw-bold mb-0">{{ number_format($payments->where('payment_date', '>=', now()->startOfMonth())->sum('amount'), 2) }}৳</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card p-3 d-flex align-items-center gap-3 border-0 shadow-sm">
                        <div class="rounded-circle bg-success-subtle p-3 text-success">
                            <i class="fas fa-check-double fs-4"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Total Vouchers</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $payments->total() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card p-3 d-flex align-items-center gap-3 border-0 shadow-sm">
                        <div class="rounded-circle bg-info-subtle p-3 text-info">
                            <i class="fas fa-university fs-4"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Active Suppliers</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $suppliers->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="premium-card p-3 d-flex align-items-center gap-3 border-0 shadow-sm">
                        <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                            <i class="fas fa-money-bill-wave fs-4"></i>
                        </div>
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Total Disbursed</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($payments->sum('amount'), 2) }}৳</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Analytics Filters -->
            <div class="premium-card mb-4 shadow-sm">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('supplier-payments.index') }}" id="filterForm">
                        <input type="hidden" name="report_type" id="report_type_hidden" value="{{ $reportType }}">
                        
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="dailyReport">Daily Reports</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="monthlyReport">Monthly Reports</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input report-type-radio" type="radio" name="report_type_active" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold small text-muted" for="yearlyReport">Yearly Reports</label>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <!-- Daily Range -->
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>

                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-2 month-field {{ $reportType != 'monthly' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Month</label>
                                <select name="month" class="form-select select2-setup">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 month-field year-field {{ $reportType == 'daily' ? 'd-none' : '' }}">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Year</label>
                                <select name="year" class="form-select select2-setup">
                                    @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Supplier</label>
                                <select name="supplier_id" class="form-select select2-setup shadow-none">
                                    <option value="all">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Method</label>
                                <select name="payment_method" class="form-select select2-setup">
                                    <option value="all">Any Method</option>
                                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                </select>
                            </div>

                        </div>
                        <div class="card-footer bg-light border-top p-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('supplier-payments.export.excel', request()->all()) }}" class="btn btn-outline-success btn-sm fw-bold px-3 no-loader" target="_blank">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a>
                                    <a href="{{ route('supplier-payments.export.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm fw-bold px-3 no-loader" target="_blank">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('supplier-payments.index') }}" class="btn btn-light border px-4 fw-bold text-muted" style="height: 42px; display: flex; align-items: center;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </a>
                                    <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                        <i class="fas fa-search me-2"></i>Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Search Wrapper -->
            <div class="d-flex justify-content-end align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted mb-0">Search:</label>
                    <input type="text" id="tableSearch" class="form-control form-control-sm table-search-input" placeholder="Quick Search..." style="width: 250px;">
                </div>
            </div>

            <!-- Table Card -->
            <div class="premium-card shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-money-bill-wave me-2 text-success"></i>Disbursement Registry</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table premium-table compact reporting-table table-hover align-middle mb-0" id="paymentTable">
                            <thead>
                                <tr>
                                    <th class="ps-3">Voucher Info</th>
                                    <th>Supplier Name</th>
                                    <th>Bill Reference</th>
                                    <th class="text-end">Disbursed Amount</th>
                                    <th>Payment Method</th>
                                    <th class="text-center pe-3">Actions</th>
                                </tr>
                            </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-primary mb-0">SP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    <div class="extra-small text-muted fw-bold">{{ $payment->payment_date->format('d M, Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $payment->supplier->name }}</div>
                                    <div class="extra-small text-muted">{{ $payment->creator->name ?? 'System' }}</div>
                                </td>
                                <td>
                                    @if($payment->bill)
                                        <span class="badge bg-soft-primary text-primary fw-bold">{{ $payment->bill->bill_number }}</span>
                                    @else
                                        <span class="badge bg-soft-warning text-warning fw-bold">ADVANCE</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format($payment->amount, 2) }}৳</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($payment->financialAccount)
                                            <i class="fas {{ $payment->financialAccount->type == 'bank' ? 'fa-university text-primary' : ($payment->financialAccount->type == 'cash' ? 'fa-wallet text-success' : 'fa-mobile-alt text-info') }}"></i>
                                            <span class="fw-bold text-uppercase" style="font-size: 11px;">{{ $payment->financialAccount->provider_name }}</span>
                                        @else
                                            @if($payment->payment_method == 'cash')
                                                <i class="fas fa-wallet text-success"></i>
                                            @elseif($payment->payment_method == 'bank_transfer')
                                                <i class="fas fa-university text-primary"></i>
                                            @else
                                                <i class="fas fa-money-check text-warning"></i>
                                            @endif
                                            <span class="fw-bold text-uppercase" style="font-size: 11px;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 rounded-circle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg premium-dropdown">
                                            <li><a class="dropdown-item" href="{{ route('supplier-payments.show', $payment->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Voucher</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2 text-dark"></i>Print Receipt</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('supplier-payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Void this payment?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i>Void Payment</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="fas fa-receipt fa-4x mb-3"></i>
                                        <p class="fw-bold mb-0">No payment history found for this period.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="small text-muted fw-bold">Records found: {{ $payments->total() }}</div>
                    {{ $payments->links('vendor.pagination.bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>


@push('css')
    <style>
        /* Dropdown Priority */
        .premium-dropdown {
            z-index: 1060 !important;
        }

        /* Filter Pills Theme Fix */
        .btn-check:checked + .btn-outline-primary {
            background-color: var(--primary-green, #198754) !important;
            border-color: var(--primary-green, #198754) !important;
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
        }
        .btn-outline-primary {
            color: var(--primary-green, #198754) !important;
            border-color: transparent !important;
            font-weight: 600;
        }
        .btn-outline-primary:hover {
            background-color: rgba(25, 135, 84, 0.08) !important;
            color: #157347 !important;
        }

        /* Theme Button Utility */
        .btn-theme {
            background-color: var(--primary-green, #198754) !important;
            border-color: var(--primary-green, #198754) !important;
            color: white !important;
            transition: all 0.3s;
        }
        .btn-theme:hover {
            background-color: #157347 !important;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
            transform: translateY(-1px);
            color: white !important;
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

            // Table search functionality with Debounce
            let searchTimeout;
            $('#tableSearch').on('input', function() {
                const value = $(this).val().toLowerCase();
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(function() {
                    $('#paymentTable tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                }, 300);
            });
        });
    </script>
    @endpush
@endsection
