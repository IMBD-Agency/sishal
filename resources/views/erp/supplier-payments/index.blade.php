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

            <!-- Filters Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filtering</h6>
                        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill">
                            <input type="radio" class="btn-check report-type-radio" name="report_type_active" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary border-0 rounded-pill px-3" for="dailyReport">Custom Range</label>
                            
                            <input type="radio" class="btn-check report-type-radio" name="report_type_active" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary border-0 rounded-pill px-3" for="monthlyReport">Monthly</label>
                            
                            <input type="radio" class="btn-check report-type-radio" name="report_type_active" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary border-0 rounded-pill px-3" for="yearlyReport">Yearly</label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('supplier-payments.index') }}" id="filterForm">
                        <input type="hidden" name="report_type" id="report_type_hidden" value="{{ $reportType }}">
                        
                        <div class="row g-2">
                            <!-- Daily Range -->
                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>

                            <div class="col-md-2 date-range-field {{ $reportType != 'daily' ? 'd-none' : '' }}">
                                <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>

                            <!-- Monthly Fields -->
                            <div class="col-md-2 month-field {{ $reportType != 'monthly' ? 'd-none' : '' }}">
                                <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Month</label>
                                <select name="month" class="form-select form-select-sm">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Yearly/Shared Year Field -->
                            <div class="col-md-1 year-field {{ $reportType == 'daily' ? 'd-none' : '' }}">
                                <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Year</label>
                                <select name="year" class="form-select form-select-sm">
                                    @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Supplier</label>
                                <select name="supplier_id" class="form-select form-select-sm select2 shadow-none">
                                    <option value="all">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label extra-small fw-bold text-muted text-uppercase mb-1">Method</label>
                                <select name="payment_method" class="form-select form-select-sm">
                                    <option value="all">Any Method</option>
                                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-1 align-items-end">
                                <button type="submit" class="btn btn-theme flex-grow-1 fw-bold btn-sm">
                                    <i class="fas fa-filter me-1"></i> APPLY FILTERS
                                </button>
                                <a href="{{ route('supplier-payments.index') }}" class="btn btn-light btn-sm border fw-bold text-muted">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="premium-card">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <a href="{{ route('supplier-payments.export.excel', request()->all()) }}" class="btn btn-light btn-sm border fw-bold text-success">
                            <i class="fas fa-file-excel me-1"></i> EXCEL
                        </a>
                        <a href="{{ route('supplier-payments.export.pdf', request()->all()) }}" class="btn btn-light btn-sm border fw-bold text-danger">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                    <div class="search-wrapper-premium">
                        <input type="text" id="tableSearch" class="form-control rounded-pill search-input-premium" placeholder="Search by Voucher, Supplier, Amount...">
                        <i class="fas fa-search search-icon-premium"></i>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table premium-table table-hover align-middle mb-0" id="paymentTable">
                        <thead>
                            <tr>
                                <th class="ps-4">Voucher Info</th>
                                <th>Supplier Name</th>
                                <th>Bill Reference</th>
                                <th class="text-end">Disbursed Amount</th>
                                <th>Payment Method</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td class="ps-4">
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
                                        <span class="badge bg-soft-warning text-warning fw-bold">ADVANCE PAYMENT</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark fs-6">{{ number_format($payment->amount, 2) }}৳</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($payment->financialAccount)
                                            <i class="fas {{ $payment->financialAccount->type == 'bank' ? 'fa-university text-primary' : ($payment->financialAccount->type == 'cash' ? 'fa-wallet text-success' : 'fa-mobile-alt text-info') }} small"></i>
                                            <span class="small fw-bold text-uppercase">{{ $payment->financialAccount->provider_name }}</span>
                                        @else
                                            @if($payment->payment_method == 'cash')
                                                <i class="fas fa-wallet text-success small"></i>
                                            @elseif($payment->payment_method == 'bank_transfer')
                                                <i class="fas fa-university text-primary small"></i>
                                            @else
                                                <i class="fas fa-money-check text-warning small"></i>
                                            @endif
                                            <span class="small fw-bold text-uppercase">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center pe-4">
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
