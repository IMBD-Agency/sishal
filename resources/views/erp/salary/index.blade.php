@extends('erp.master')

@section('title', 'Staff / Employee Payments')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Staff / Employee Payments</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Staff Salary</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('salary.create') }}" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i>New Payment
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filters -->
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-info text-white fw-bold btn-sm w-100">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Area -->
            <div class="card premium-card">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group btn-group-sm mb-0">
                            <a href="{{ route('salary.export.excel', request()->all()) }}" class="btn btn-export border-end">Excel</a>
                            <a href="{{ route('salary.export.pdf', request()->all()) }}" class="btn btn-export border-end">PDF</a>
                            <button class="btn btn-export" onclick="window.print()">Print</button>
                        </div>
                        <div class="d-flex align-items-center">
                            <label class="small fw-bold text-muted me-2 mb-0">Search:</label>
                            <input type="text" id="customSearch" class="form-control form-control-sm" style="width: 200px;">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="paymentTable">
                        <thead class="bg-erp-success text-white">
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
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('salary.show', $payment->id) }}" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
                                            <button class="btn btn-outline-danger" onclick="deletePayment({{ $payment->id }})"><i class="fas fa-trash"></i></button>
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
                <div class="card-footer bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                        </div>
                        <div class="pagination-sm mb-0">
                            {{ $payments->links() }}
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
