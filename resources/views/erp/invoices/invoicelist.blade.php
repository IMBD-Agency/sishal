@extends('erp.master')

@section('title', 'Invoice Management')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}"
                                    class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Invoice List</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Invoice List</h2>
                    <p class="text-muted mb-0">Manage invoice information, contacts, and transactions efficiently.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('invoice.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Invoice
                        </a>
                        <a href="{{ route('invoices.export.excel', request()->query()) }}" class="btn btn-success text-white">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </a>
                        <a href="{{ route('invoices.export.pdf', request()->query()) }}" class="btn btn-danger text-white">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="container-fluid px-4 py-4">
            <!-- Premium Filter Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('invoice.list') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Primary Filters Row -->
                            <div class="col-md-5">
                                <label class="form-label small text-muted fw-bold">General Search</label>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Invoice #, Customer, Salesman...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted fw-bold">Payment Status</label>
                                <select name="status" class="form-select shadow-sm border-0 bg-light">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ (request('status')) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold">Select Customer</label>
                                <select name="customer_id" class="form-select shadow-sm border-0 bg-light">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ (request('customer_id')) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mt-2">
                                <hr class="my-3 opacity-10">
                            </div>

                            <!-- Row 2 -->
                            <div class="col-md-3 mt-0">
                                <label class="form-label small text-muted fw-bold">Invoice Source</label>
                                <select class="form-select shadow-sm border-0 bg-light" name="source">
                                    <option value="">All Sources</option>
                                    @if(auth()->user()->can('view invoices') || auth()->user()->hasRole('Super Admin') || auth()->user()->is_admin)
                                    <option value="pos" {{ request('source') == 'pos' ? 'selected' : '' }}>POS / In-Store</option>
                                    @endif
                                    @if(auth()->user()->can('view internal invoices') || auth()->user()->hasRole('Super Admin') || auth()->user()->is_admin)
                                    <option value="ecommerce" {{ request('source') == 'ecommerce' ? 'selected' : '' }}>E-commerce</option>
                                    @endif
                                    @if((auth()->user()->can('view invoices') && auth()->user()->can('view internal invoices')) || auth()->user()->hasRole('Super Admin') || auth()->user()->is_admin)
                                    <option value="manual" {{ request('source') == 'manual' ? 'selected' : '' }}>Manual Entry</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 mt-0">
                                <label class="form-label small text-muted fw-bold">Issue Date</label>
                                <input type="date" name="issue_date" class="form-control shadow-sm border-0 bg-light" value="{{ request('issue_date') }}">
                            </div>
                            <div class="col-md-4 mt-0 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary fw-bold shadow-sm py-2 px-4">
                                    <i class="fas fa-filter me-2"></i>APPLY FILTERS
                                </button>
                                <a href="{{ route('invoice.list') }}" class="btn btn-link text-danger text-decoration-none small fw-bold">
                                    <i class="fas fa-times-circle me-1"></i> RESET
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Recent Invoices</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 py-3 ps-4">Invoice #</th>
                                    <th class="border-0 py-3">Source & Date</th>
                                    <th class="border-0 py-3">Customer Information</th>
                                    <th class="border-0 py-3 text-end">Grand Total</th>
                                    <th class="border-0 py-3 text-center">Payment Status</th>
                                    <th class="border-0 py-3 text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('invoice.show',$invoice->id) }}" class="fw-bold text-decoration-none text-primary">
                                                #{{ $invoice->invoice_number }}
                                            </a>
                                            <div class="extra-small text-muted mt-1">ID: {{ $invoice->id }}</div>
                                        </td>
                                        <td>
                                            @if($invoice->order)
                                                <span class="badge bg-secondary-subtle text-dark border px-2 py-1 mb-1" style="font-size: 0.7rem;">
                                                    <i class="fas fa-shopping-cart me-1"></i>Ecommerce
                                                </span>
                                            @elseif($invoice->pos)
                                                <span class="badge bg-success-subtle text-success border px-2 py-1 mb-1" style="font-size: 0.7rem;">
                                                    <i class="fas fa-desktop me-1"></i>POS: {{ $invoice->pos->branch?->name ?? 'Main' }}
                                                </span>
                                            @else
                                                <span class="badge bg-info-subtle text-info border px-2 py-1 mb-1" style="font-size: 0.7rem;">
                                                    <i class="fas fa-pencil-alt me-1"></i>Manual
                                                </span>
                                            @endif
                                            <div class="small text-muted mb-1">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M, Y') }}</div>
                                            <div class="extra-small text-muted"><i class="fas fa-user-tie me-1"></i> {{ trim((optional($invoice->salesman)->first_name ?? '') . ' ' . (optional($invoice->salesman)->last_name ?? '')) ?: 'System' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $invoice->order?->name ?? (optional($invoice->customer)->name ?? 'Walk-in-Customer') }}</div>
                                            <div class="extra-small text-muted">{{ $invoice->order?->phone ?? optional($invoice->customer)->phone ?? 'No phone' }}</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-bold text-dark">{{ number_format($invoice->total_amount, 2) }} ৳</div>
                                            @if($invoice->due_amount > 0)
                                                <div class="extra-small text-danger">Due: {{ number_format($invoice->due_amount, 2) }} ৳</div>
                                            @else
                                                <div class="extra-small text-success">Fully Paid</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusColor = match($invoice->status) {
                                                    'paid' => 'success',
                                                    'partial' => 'warning',
                                                    'unpaid' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }} border-opacity-25 rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm rounded-circle border shadow-sm" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                                    <li><a class="dropdown-item py-2" href="{{ route('invoice.show', $invoice->id) }}"><i class="fas fa-eye me-2 text-primary"></i>View Details</a></li>
                                                    <li><a class="dropdown-item py-2" href="{{ route('invoice.edit', $invoice->id) }}"><i class="fas fa-edit me-2 text-warning"></i>Edit Invoice</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item py-2 text-primary" href="{{ route('invoice.print', ['invoice_number' => $invoice->invoice_number]) }}" target="_blank"><i class="fas fa-print me-2"></i>Print Invoice</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-file-invoice fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">No invoices found match your criteria</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} entries
                        </span>
                        {{ $invoices->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')

@endpush