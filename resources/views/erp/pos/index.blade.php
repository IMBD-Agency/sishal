@extends('erp.master')

@section('title', 'Sale Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
    @include('erp.components.header')
    <style>
        .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
        .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
        .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
        .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
        .bg-secondary-soft { background-color: rgba(108, 117, 125, 0.1); }
        
        .transition-all { transition: all 0.2s ease-in-out; }
        
        /* Stable Hover Effect */
        #saleTable tbody tr:hover { 
            background-color: #f8faff !important;
            box-shadow: inset 0 0 0 9999px #f8faff; /* Fix for table background color override */
        }
        
        .x-small { font-size: 0.7rem; }
        .avatar-sm { width: 32px; height: 32px; font-size: 0.85rem; }
        
        .form-select, .form-control { border-color: #f1f3f5; }
        .form-select:focus, .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25 margin; }
        
        #saleTable thead th { letter-spacing: 0.05em; border-bottom: 2px solid #f8f9fa; }
        #saleTable tbody tr { border-bottom: 1px solid #f8f9fa; }

        /* Quick Filter Styles */
        .quick-filter-btn {
            background-color: #fff;
            color: #6c757d;
            border: 1px solid #e9ecef;
            padding: 0.4rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .quick-filter-btn:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
        .btn-check:checked + .quick-filter-btn {
            background-color: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.15);
        }
        .btn-group .btn-check:first-child + .quick-filter-btn {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        .btn-group .btn-check:last-child + .quick-filter-btn,
        .btn-group label:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
    </style>
    <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sale List</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Sale List</h2>
                    <p class="text-muted mb-0">Manage sale information, contacts, and transactions efficiently.</p>
                </div>
                <div class="col-md-4 text-end">
                    @can('make sale')
                    <a href="{{ route('pos.add') }}" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>Add Sale
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Advanced Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Search</label>
                            <div class="input-group border rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" name="search" class="form-control border-0 px-2" placeholder="ID, Name, Phone, Email..." value="{{ request('search') }}">
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Branch</label>
                            <select name="branch_id" class="form-select border rounded-3">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Status</label>
                            <select name="status" class="form-select border rounded-3">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Bill Status</label>
                            <select name="bill_status" class="form-select border rounded-3">
                                <option value="">All Payment</option>
                                <option value="unpaid" {{ request('bill_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ request('bill_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partial" {{ request('bill_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-12">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Sale Date Range</label>
                            <div class="input-group border rounded-3 overflow-hidden">
                                <input type="date" name="start_date" class="form-control border-0 border-end" value="{{ request('start_date') }}" title="Start Date">
                                <span class="input-group-text bg-light border-0 px-2 text-muted small">to</span>
                                <input type="date" name="end_date" class="form-control border-0" value="{{ request('end_date') }}" title="End Date">
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center gap-3">
                                <label class="fw-semibold text-muted small text-uppercase mb-0">Quick Filter:</label>
                                <div class="btn-group shadow-sm" role="group" aria-label="Quick Filters">
                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_all" value="" {{ !request('quick_filter') ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_all">All</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_today" value="today" {{ request('quick_filter') == 'today' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_today">Today</label>

                                    <input type="radio" class="btn-check" name="quick_filter" id="filter_monthly" value="monthly" {{ request('quick_filter') == 'monthly' ? 'checked' : '' }} onchange="applyQuickFilter(this.value)">
                                    <label class="btn quick-filter-btn" for="filter_monthly">Monthly</label>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('pos.list') }}" class="btn btn-light border px-4 rounded-3 text-muted">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                                <button type="submit" class="btn btn-primary px-4 rounded-3 shadow-sm">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sale Listing Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Transaction History</h5>
                            <p class="text-muted small mb-0">Overview of all sales and their current fulfillment status.</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="btn-group shadow-sm">
                                <a href="{{ route('pos.export.pdf', array_merge(request()->all(), ['columns' => 'pos_id,sale_date,customer,phone,branch,status,payment_status,total,paid_amount,due_amount', 'action' => 'print'])) }}" target="_blank" class="btn btn-sm btn-light border px-3">
                                    <i class="fas fa-print me-1"></i> Print
                                </a>
                                <a href="{{ route('pos.export.pdf', array_merge(request()->all(), ['columns' => 'pos_id,sale_date,customer,phone,branch,status,payment_status,total,paid_amount,due_amount'])) }}" class="btn btn-sm btn-light border px-3 text-danger">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                                <a href="{{ route('pos.export.excel', array_merge(request()->all(), ['columns' => 'pos_id,sale_date,customer,phone,branch,status,payment_status,total,paid_amount,due_amount'])) }}" class="btn btn-sm btn-light border px-3 text-success">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </a>
                            </div>
                            @if($sales->total() > 0)
                                <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                    Total: {{ $sales->total() }} Records
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="saleTable">
                            <thead class="bg-light text-muted small text-uppercase fw-bold">
                                <tr>
                                    <th class="ps-4 border-0 py-3">POS ID</th>
                                    <th class="border-0 py-3">Customer Information</th>
                                    <th class="border-0 py-3">Branch & Date</th>
                                    <th class="border-0 py-3 text-center">Order Status</th>
                                    <th class="border-0 py-3 text-center">Payment Status</th>
                                    <th class="border-0 py-3 text-end">Grand Total</th>
                                    <th class="pe-4 border-0 py-3 text-end">Remaining Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    @php
                                        $invoice = $sale->invoice;
                                        $total = $sale->total_amount ?? 0;
                                        $paidRaw = $invoice ? ($invoice->paid_amount ?? 0) : ($sale->payments_total ?? 0);
                                        $paidAmount = min($paidRaw, $total);
                                        $dueAmount = max(0, $total - $paidAmount);
                                    @endphp
                                    <tr class="transition-all">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 rounded-3 bg-light text-primary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-receipt"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('pos.show',$sale->id) }}" class="text-decoration-none fw-bold text-dark d-block">
                                                        {{ $sale->sale_number ?? '-' }}
                                                    </a>
                                                    <small class="text-muted">ID: #{{ $sale->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                                            @if($sale->customer && $sale->customer->phone)
                                                <div class="small text-muted d-flex align-items-center">
                                                    <i class="fas fa-phone-alt me-1 x-small"></i> {{ $sale->customer->phone }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark"><i class="fas fa-store me-1 text-muted small"></i> {{ $sale->branch->name ?? '-' }}</div>
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1 x-small"></i> {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') : '-' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = [
                                                    'pending' => 'bg-warning-soft text-warning border-warning',
                                                    'delivered' => 'bg-success-soft text-success border-success',
                                                    'cancelled' => 'bg-danger-soft text-danger border-danger',
                                                ][$sale->status] ?? 'bg-secondary-soft text-secondary border-secondary';
                                            @endphp
                                            <span class="badge border rounded-pill px-3 py-2 fw-medium {{ $statusClass }}">
                                                <i class="fas fa-circle me-1 x-small"></i> {{ ucfirst($sale->status ?? '-') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $paymentStatus = $invoice ? ($invoice->status ?? 'unpaid') : 'unpaid';
                                                $paymentClass = [
                                                    'unpaid' => 'bg-danger-soft text-danger border-danger',
                                                    'paid' => 'bg-success-soft text-success border-success',
                                                    'partial' => 'bg-warning-soft text-warning border-warning',
                                                ][$paymentStatus] ?? 'bg-secondary-soft text-secondary border-secondary';
                                            @endphp
                                            <span class="badge border rounded-pill px-3 py-2 fw-medium {{ $paymentClass }}">
                                                {{ ucfirst($paymentStatus) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            {{ number_format($total, 2) }} <span class="text-muted small fw-normal">৳</span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="{{ $dueAmount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                {{ number_format($dueAmount, 2) }} <span class="small fw-normal">৳</span>
                                            </div>
                                            @if($dueAmount > 0)
                                                <div class="progress mt-1" style="height: 3px; width: 60px; margin-left: auto;">
                                                    @php $percent = ($paidAmount / max($total, 1)) * 100; @endphp
                                                    <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                                                </div>
                                            @else
                                                <small class="text-success x-small fw-bold">FULL PAID</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty   
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-4">
                                                <div class="mb-3">
                                                    <i class="fas fa-folder-open fa-3x text-light"></i>
                                                </div>
                                                <h6 class="text-muted">No sales found matching your criteria</h6>
                                                <a href="{{ route('pos.list') }}" class="btn btn-sm btn-outline-primary mt-2">Clear all filters</a>
                                            </div>
                                        </td>
                                    </tr> 
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing <span class="fw-bold text-dark">{{ $sales->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $sales->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $sales->total() }}</span> transactions
                        </span>
                        <div>
                            {{ $sales->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyQuickFilter(value) {
        document.getElementsByName('start_date')[0].value = '';
        document.getElementsByName('end_date')[0].value = '';
        document.querySelector('form.row.g-3').submit();
    }

document.addEventListener('DOMContentLoaded', function() {

    // Cancel Sale functionality
    document.querySelectorAll('.cancel-sale-btn').forEach(button => {
        button.addEventListener('click', function() {
            const saleId = this.getAttribute('data-id');
            
            if (confirm('Are you sure you want to cancel this sale? This will restore the stock to the branch.')) {
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cancelling...';
                this.classList.add('disabled');

                fetch(`/erp/pos/update-status/${saleId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: 'cancelled'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sale cancelled successfully.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        this.innerHTML = originalContent;
                        this.classList.remove('disabled');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the sale.');
                    this.innerHTML = originalContent;
                    this.classList.remove('disabled');
                });
            }
        });
    });
});
</script>
@endpush