@extends('erp.master')

@section('title', 'Voucher List')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        <div class="container-fluid px-4 py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Voucher List</h4>
                <a href="{{ route('vouchers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>New Voucher
                </a>
            </div>

            <!-- Filters Area -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('vouchers.index') }}" method="GET" id="filterForm">
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="report_type" id="dailyReport" value="daily" {{ $reportType == 'daily' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dailyReport">Daily Reports</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="report_type" id="monthlyReport" value="monthly" {{ $reportType == 'monthly' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="monthlyReport">Monthly Reports</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="report_type" id="yearlyReport" value="yearly" {{ $reportType == 'yearly' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="yearlyReport">Yearly Reports</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Start Date *</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">End Date *</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Customer *</label>
                                <select name="customer_id" class="form-select form-select-sm select2">
                                    <option value="all">All Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Supplier *</label>
                                <select name="supplier_id" class="form-select form-select-sm select2">
                                    <option value="all">All Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Cost Type *</label>
                                <select name="account_id" class="form-select form-select-sm select2">
                                    <option value="all">All Cost Type</option>
                                    @foreach($expenseAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Voucher Type *</label>
                                <select name="voucher_type" class="form-select form-select-sm">
                                    <option value="all">All</option>
                                    <option value="Payment" {{ request('voucher_type') == 'Payment' ? 'selected' : '' }}>Payment</option>
                                    <option value="Receipt" {{ request('voucher_type') == 'Receipt' ? 'selected' : '' }}>Receipt</option>
                                    <option value="Contra" {{ request('voucher_type') == 'Contra' ? 'selected' : '' }}>Contra</option>
                                    <option value="Journal" {{ request('voucher_type') == 'Journal' ? 'selected' : '' }}>Journal</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Select Account *</label>
                                <select name="account_id" class="form-select form-select-sm select2">
                                    <option value="all">All Account</option>
                                    @foreach($expenseAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-info w-100 text-white">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Area -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-secondary">CSV</button>
                            <button class="btn btn-secondary">Excel</button>
                            <button class="btn btn-secondary">PDF</button>
                            <button class="btn btn-secondary">Print</button>
                        </div>
                        <div class="ms-auto d-flex align-items-center">
                            <label class="me-2 small">Search:</label>
                            <input type="text" id="voucherSearch" class="form-control form-control-sm" style="width: 200px;">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle" id="voucherTable">
                            <thead class="bg-erp-success text-white">
                                <tr>
                                    <th>SL.</th>
                                    <th>Voucher No.</th>
                                    <th>Voucher Type</th>
                                    <th>Date</th>
                                    <th>Outlet</th>
                                    <th>Customer</th>
                                    <th>Expense</th>
                                    <th>Details</th>
                                    <th>Voucher Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Account</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vouchers as $index => $voucher)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $voucher->voucher_no }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $voucher->type }}</span></td>
                                        <td>{{ \Carbon\Carbon::parse($voucher->entry_date)->format('d/m/Y') }}</td>
                                        <td>{{ $voucher->branch->name ?? '-' }}</td>
                                        <td>{{ $voucher->customer->name ?? '-' }}</td>
                                        <td>{{ $voucher->expenseAccount->name ?? '-' }}</td>
                                        <td>{{ Str::limit($voucher->description, 30) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($voucher->voucher_amount, 2) }}৳</td>
                                        <td class="text-end fw-bold">{{ number_format($voucher->paid_amount, 2) }}৳</td>
                                        <td>{{ $voucher->entries->where('credit', '>', 0)->first()->chartOfAccount->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('journal.show', $voucher->id) }}" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="#" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <button class="btn btn-outline-danger" title="Delete" onclick="deleteVoucher({{ $voucher->id }}, '{{ $voucher->voucher_no }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4 text-muted">No data available in table</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="8" class="text-end fw-bold">Grand Total (Filtered)</td>
                                    <td class="text-end fw-bold">{{ number_format($totals->total_voucher ?? 0, 2) }}৳</td>
                                    <td class="text-end fw-bold">{{ number_format($totals->total_paid ?? 0, 2) }}৳</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $vouchers->firstItem() ?? 0 }} to {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} entries</small>
                        <div class="premium-pagination">
                            {{ $vouchers->links() }}
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
            $("#voucherSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#voucherTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Report Type Radio Redirection
            $('input[name="report_type"]').on('change', function() {
                $('#filterForm').submit();
            });
        });

        function deleteVoucher(id, voucherNo) {
            if (confirm('Are you sure you want to delete voucher ' + voucherNo + '?')) {
                $.ajax({
                    url: '{{ url("erp/double-entry/vouchers") }}/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Something went wrong. Please try again.');
                    }
                });
            }
        }
    </script>
    @endpush
@endsection
