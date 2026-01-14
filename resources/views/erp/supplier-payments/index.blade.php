@extends('erp.master')

@section('title', 'Supplier Payment')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">Supplier Payment</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Supplier Payment</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('supplier-payments.create') }}" class="btn btn-primary px-4">
                        <i class="fas fa-plus me-2"></i>New Supplier Payment
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
            @endif

            <!-- Filters Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Supplier Payment List</h6>
                </div>
                <div class="card-body">
                    <!-- Report Type Tabs -->
                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="report_type" id="daily" value="daily" checked>
                            <label class="btn btn-outline-primary btn-sm" for="daily">Daily Reports</label>
                            
                            <input type="radio" class="btn-check" name="report_type" id="monthly" value="monthly">
                            <label class="btn btn-outline-primary btn-sm" for="monthly">Monthly Reports</label>
                            
                            <input type="radio" class="btn-check" name="report_type" id="yearly" value="yearly">
                            <label class="btn btn-outline-primary btn-sm" for="yearly">Yearly Reports</label>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('supplier-payments.index') }}">
                        <div class="row g-3">
                            <!-- Start Date -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>

                            <!-- End Date -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>

                            <!-- Payment No -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Payment No</label>
                                <select name="payment_no" class="form-select">
                                    <option value="all">All Payment No</option>
                                    @foreach($allPayments as $payment)
                                        <option value="{{ $payment->id }}" {{ request('payment_no') == $payment->id ? 'selected' : '' }}>
                                            #{{ $payment->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Challan No -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Challan No</label>
                                <select name="challan_no" class="form-select">
                                    <option value="all">All Challan No</option>
                                    @foreach($allBills as $bill)
                                        <option value="{{ $bill->id }}" {{ request('challan_no') == $bill->id ? 'selected' : '' }}>
                                            {{ $bill->bill_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Supplier -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Supplier</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="all">All Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select Account -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Select Account</label>
                                <select name="payment_method" class="form-select">
                                    <option value="all">All Account</option>
                                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="check" {{ request('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                                    <option value="bkash" {{ request('payment_method') == 'bkash' ? 'selected' : '' }}>bKash</option>
                                    <option value="nagad" {{ request('payment_method') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="{{ route('supplier-payments.index') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="mb-3">
                <div class="btn-group" role="group">
                    <button class="btn btn-dark btn-sm px-3">CSV</button>
                    <button class="btn btn-dark btn-sm px-3">Excel</button>
                    <button class="btn btn-dark btn-sm px-3">PDF</button>
                    <button class="btn btn-dark btn-sm px-3">Print</button>
                </div>
                <input type="text" id="tableSearch" class="form-control form-control-sm d-inline-block ms-2" style="width: 200px;" placeholder="Search...">
            </div>

            <!-- Table Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="paymentTable">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th class="ps-3">Serial No</th>
                                    <th>Payment No</th>
                                    <th>Payment Date</th>
                                    <th>Challan Date</th>
                                    <th>Supplier</th>
                                    <th>Outlet</th>
                                    <th>Purchase Challan No</th>
                                    <th>Bill</th>
                                    <th>Paid Amount</th>
                                    <th>Account</th>
                                    <th>Pay by</th>
                                    <th class="text-center pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $index => $payment)
                                <tr>
                                    <td class="ps-3 small">{{ $payments->firstItem() + $index }}</td>
                                    <td class="small fw-bold" style="color: #e83e8c;">SP-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td class="small">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td class="small">{{ $payment->bill ? $payment->bill->bill_date->format('d/m/Y') : '-' }}</td>
                                    <td class="small fw-bold">{{ $payment->supplier->name }}</td>
                                    <td class="small">{{ $payment->bill && $payment->bill->purchase ? $payment->bill->purchase->branch->name ?? 'Main' : 'Main' }}</td>
                                    <td class="small">{{ $payment->bill ? $payment->bill->bill_number : 'Advance' }}</td>
                                    <td class="small">{{ $payment->bill ? number_format($payment->bill->total_amount, 2) : '-' }}</td>
                                    <td class="fw-bold small text-success">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="small">
                                        <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    </td>
                                    <td class="small">{{ $payment->creator->name ?? 'System' }}</td>
                                    <td class="pe-3">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('supplier-payments.show', $payment->id) }}" 
                                               class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                               style="width: 26px; height: 26px; color: #0dcaf0;" title="View">
                                                <i class="fas fa-eye fa-xs"></i>
                                            </a>
                                            <form action="{{ route('supplier-payments.destroy', $payment->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm p-0 d-flex align-items-center justify-content-center border bg-white" 
                                                        style="width: 26px; height: 26px; color: #dc3545;" 
                                                        onclick="return confirm('Are you sure? This will reverse the ledger entry.')" title="Delete">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p>No data available in table</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($payments->hasPages())
                    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
                        <div class="small text-muted">
                            Showing {{ $payments->firstItem() ?: 0 }} to {{ $payments->lastItem() ?: 0 }} of {{ $payments->total() }} entries
                        </div>
                        <div>
                            {{ $payments->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Total Amount Display -->
            <div class="mt-3 text-end">
                <div class="d-inline-block bg-white border rounded px-4 py-2 shadow-sm">
                    <span class="fw-bold">Total Amount:</span>
                    <span class="text-success fw-bold ms-2">{{ number_format($payments->sum('amount'), 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Table search functionality
        $('#tableSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#paymentTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    </script>
    @endpush
@endsection
