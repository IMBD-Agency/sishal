@extends('erp.master')

@section('title', 'Supplier Details - ' . $supplier->name)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
                    <li class="breadcrumb-item active">{{ $supplier->name }}</li>
                </ol>
            </nav>

            <div class="row g-4">
                <!-- Supplier Info Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4 text-center">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-truck-loading fa-2x text-primary"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1">{{ $supplier->name }}</h4>
                            <p class="text-muted small mb-4">Supplier ID: #{{ $supplier->id }}</p>

                            <div class="d-grid gap-2 mb-4">
                                <a href="mailto:{{ $supplier->email }}" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-envelope"></i> {{ $supplier->email ?: 'No Email' }}
                                </a>
                                <a href="tel:{{ $supplier->phone }}" class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-phone"></i> {{ $supplier->phone ?: 'No Phone' }}
                                </a>
                            </div>

                            <div class="text-start border-top pt-4">
                                <label class="small text-muted fw-bold text-uppercase mb-2">Company Information</label>
                                <div class="mb-3">
                                    <h6 class="mb-1 fw-bold">Address</h6>
                                    <p class="text-muted small mb-0">{{ $supplier->address ?: 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <h6 class="mb-1 fw-bold">Status</h6>
                                    <span class="badge {{ $supplier->status == 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3">
                                        {{ ucfirst($supplier->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-center gap-2 mt-4 pt-4 border-top">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary px-4">
                                    <i class="fas fa-edit me-1"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics and Recent Purchases -->
                <div class="col-lg-8">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fs-4 fw-bold">Total Orders</div>
                                    <i class="fas fa-shopping-bag fa-2x opacity-50"></i>
                                </div>
                                <div class="h2 fw-bold mb-1">{{ $supplier->purchases->count() }}</div>
                                <div class="small opacity-75">All time orders placed</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 bg-info text-white p-4 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fs-4 fw-bold">Total Amount</div>
                                    <i class="fas fa-wallet fa-2x opacity-50"></i>
                                </div>
                                <div class="h2 fw-bold mb-1">${{ number_format($supplier->purchases->sum('total_amount'), 2) }}</div>
                                <div class="small opacity-75">Total order value</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0">Recent Purchases</h5>
                            <a href="{{ route('purchase.list', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-light text-primary fw-bold">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 ps-4">Order ID</th>
                                            <th class="border-0">Date</th>
                                            <th class="border-0">Items</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0 pe-4 text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($supplier->purchases->take(10) as $purchase)
                                        <tr>
                                            <td class="ps-4">
                                                @if($purchase->id)
                                                    <a href="{{ route('purchase.show', $purchase->id) }}" class="fw-bold text-primary">#{{ $purchase->id }}</a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $purchase->date ? date('M d, Y', strtotime($purchase->date)) : $purchase->created_at->format('M d, Y') }}</td>
                                            <td>{{ $purchase->items_count ?? $purchase->purchase_items()->count() }} items</td>
                                            <td>
                                                <span class="badge bg-{{ $purchase->status == 'completed' ? 'success' : 'warning' }}-subtle text-{{ $purchase->status == 'completed' ? 'success' : 'warning' }} rounded-pill px-3">
                                                    {{ ucfirst($purchase->status) }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end fw-bold text-dark">${{ number_format($purchase->total_amount, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No purchase history found for this supplier.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
