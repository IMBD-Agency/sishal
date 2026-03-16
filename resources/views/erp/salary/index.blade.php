@extends('erp.master')

@section('title', 'Staff / Employee Payments')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <!-- Premium Header (Glass Style) -->
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb pe-3 mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Staff Salary</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-dark">Staff / Employee Payments</h4>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                            Salary Registry
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('salary.create') }}" class="btn btn-create-premium shadow-sm">
                        <i class="fas fa-plus me-2"></i>New Payment
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="container-fluid px-4 py-4">
            <div class="card premium-card report-filter-card mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('salary.index') }}" method="GET" id="filterForm">
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input filter-radio" type="radio" name="report_type" id="monthlyReport" value="monthly" checked>
                                <label class="form-check-label fw-bold small" for="monthlyReport">Monthly Reports</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input filter-radio" type="radio" name="report_type" id="yearlyReport" value="yearly">
                                <label class="form-check-label fw-bold small" for="yearlyReport">Yearly Reports</label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-2" id="monthCol">
                                <label class="form-label-small">Select Month *</label>
                                <select name="month" class="form-select form-select-sm filter-select">
                                    <option>Select One</option>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                                        <option value="{{ $m }}" {{ request('month', date('F')) == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-small">Select Year *</label>
                                <select name="year" class="form-select form-select-sm filter-select">
                                    <option>Select One</option>
                                    @for($y = date('Y')-2; $y <= date('Y')+1; $y++)
                                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-small">Select Employee *</label>
                                <select name="employee_id" class="form-select form-select-sm filter-select select2-premium-42">
                                    <option value="all">All Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->user->first_name }} {{ $emp->user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-small">Select Account *</label>
                                <select name="account_id" class="form-select form-select-sm filter-select select2-premium-42">
                                    <option value="all">All Account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="card-footer bg-light border-top p-3 mt-4 mx-n4 mb-n4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('salary.export.excel', request()->all()) }}" class="btn btn-outline-success btn-sm fw-bold px-3 shadow-sm no-loader">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a>
                                    <a href="{{ route('salary.export.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm no-loader">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 shadow-sm no-loader" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('salary.index') }}" class="btn btn-light border px-4 fw-bold text-muted justify-content-center" style="height: 42px; display: flex; align-items: center;">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </a>
                                    <button type="submit" class="btn btn-create-premium px-5" style="height: 42px;">
                                        <i class="fas fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Section Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-list me-2 text-primary"></i>Salary Registry List</h6>
                <div class="search-wrapper-premium" style="width: 300px;">
                    <input type="text" id="customSearch" class="form-control rounded-pill search-input-premium table-search-input" placeholder="Quick find in this registry...">
                    <i class="fas fa-search search-icon-premium"></i>
                </div>
            </div>

            <div class="card premium-card shadow-sm mb-5">
                <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table reporting-table mb-0 align-middle" id="paymentTable">
                        <thead>
                            <tr>
                                <th>Serial No</th>
                                <th>Staff Name</th>
                                <th>Outlet</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th class="text-end">Paid Amount</th>
                                <th>Account Type</th>
                                <th>Note</th>
                                <th class="text-center">Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $index => $payment)
                                <tr id="row-{{ $payment->id }}">
                                    <td class="small">{{ $payments->firstItem() + $index }}</td>
                                    <td class="fw-bold">{{ $payment->employee->user->first_name }} {{ $payment->employee->user->last_name }}</td>
                                    <td class="small text-muted">{{ $payment->branch->name ?? '-' }}</td>
                                    <td>{{ $payment->year }}</td>
                                    <td>{{ $payment->month }}</td>
                                    <td class="text-end fw-bold">{{ number_format($payment->paid_amount, 2) }}৳</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $payment->payment_method }}</span>
                                        @if($payment->chartOfAccount)
                                            <div class="small text-muted">{{ $payment->chartOfAccount->name }}</div>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ Str::limit($payment->note, 30) }}</td>
                                    <td class="text-center pe-3">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('salary.show', $payment->id) }}" class="action-circle bg-light" title="View">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <button type="button" class="action-circle bg-light border-0" title="Delete" onclick="deletePayment({{ $payment->id }})">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <div class="py-3">
                                            <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                                            <p>No data available in table</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($payments->count() > 0)
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">Total Amount</td>
                                    <td class="text-end text-primary">{{ number_format($payments->sum('paid_amount'), 2) }}৳</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                <div class="card-footer bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small fw-500 text-muted">
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                        </div>
                        <div class="pagination-sm mb-0">
                            {{ $payments->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Live Search
            $("#customSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#paymentTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Report Type Toggle
            $('input[name="report_type"]').on('change', function() {
                if ($(this).val() === 'yearly') {
                    $('#monthCol').addClass('opacity-50');
                    $('#monthCol select').prop('disabled', true);
                } else {
                    $('#monthCol').removeClass('opacity-50');
                    $('#monthCol select').prop('disabled', false);
                }
            });
        });

        function deletePayment(id) {
            if (confirm('Are you sure you want to delete this salary payment? This will also remove the associated journal entries.')) {
                $.ajax({
                    url: "{{ url('erp/salary') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#row-' + id).fadeOut(300, function() {
                                $(this).remove();
                            });
                            alert(response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error deleting salary payment.');
                    }
                });
            }
        }
    </script>
    @endpush
@endsection
