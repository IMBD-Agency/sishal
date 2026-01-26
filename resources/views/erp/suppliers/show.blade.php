@extends('erp.master')

@section('title', 'Supplier Details - ' . $supplier->name)

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        
        <div class="glass-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none text-muted">Suppliers</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">Supplier Overview</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold">
                            {{ strtoupper(substr($supplier->name, 0, 1)) }}
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $supplier->name }}</h4>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column flex-md-row justify-content-md-end gap-2 align-items-md-center">
                    <a href="{{ route('suppliers.ledger', $supplier->id) }}" class="btn btn-warning fw-bold shadow-sm">
                        <i class="fas fa-book me-2"></i>Supplier Ledger
                    </a>
                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-create-premium">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row g-4">
                <!-- Supplier Info Card -->
                <div class="col-lg-4">
                    <div class="premium-card h-100">
                        <div class="card-body p-4 p-xl-5 text-center">
                            <div class="avatar-lg bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px; border: 4px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                                <i class="fas fa-truck-loading fa-3x text-primary shadow-sm"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $supplier->name }}</h5>
                            <p class="text-muted small mb-4">Master Supplier #{{ $supplier->id }}</p>

                            <div class="d-grid gap-3 mb-5">
                                <div class="p-3 bg-light rounded-3 text-start">
                                    <label class="small fw-bold text-muted text-uppercase d-block mb-1">Email Connection</label>
                                    <span class="text-dark small fw-500">{{ $supplier->email ?: 'Not Configured' }}</span>
                                </div>
                                <div class="p-3 bg-light rounded-3 text-start">
                                    <label class="small fw-bold text-muted text-uppercase d-block mb-1">Primary Phone</label>
                                    <span class="text-dark small fw-500">{{ $supplier->phone ?: 'Not Configured' }}</span>
                                </div>
                            </div>

                            <div class="text-start border-top pt-4">
                                <h6 class="small text-primary fw-bold text-uppercase mb-3">Company Information</h6>
                                <div class="mb-4">
                                    <label class="extra-small fw-bold text-muted d-block mb-1">REGISTERED ADDRESS</label>
                                    <p class="text-dark small mb-0 lh-base">{{ $supplier->address ?: 'N/A' }}</p>
                                    <p class="text-muted extra-small mt-1">{{ $supplier->city }}{{ $supplier->city && $supplier->country ? ', ' : '' }}{{ $supplier->country }}</p>
                                </div>
                                <div class="mb-0">
                                    <label class="extra-small fw-bold text-muted d-block mb-1">TAX COMPLIANCE</label>
                                    <span class="badge bg-dark text-white rounded-pill px-3">{{ $supplier->tax_number ?: 'UNREGISTERED' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics and Recent Purchases -->
                <div class="col-lg-8">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="premium-card bg-primary text-white border-0 shadow-lg" style="background: linear-gradient(45deg, #1e293b, #334155) !important;">
                                <div class="card-body p-4 p-xl-5">
                                    <div class="d-flex align-items-center justify-content-between mb-3 text-white">
                                        <h6 class="fw-bold mb-0 text-uppercase small opacity-75">Procurement Orders</h6>
                                        <i class="fas fa-shopping-bag fa-2x opacity-25"></i>
                                    </div>
                                    <h2 class="fw-bold mb-0 text-white">{{ $supplier->purchases->count() }}</h2>
                                    <p class="small mb-0 mt-2 opacity-50">Total transaction count</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="premium-card bg-success text-white border-0 shadow-lg" style="background: linear-gradient(45deg, #0f172a, #1e293b) !important;">
                                <div class="card-body p-4 p-xl-5">
                                    <div class="d-flex align-items-center justify-content-between mb-3 text-white">
                                        <h6 class="fw-bold mb-0 text-uppercase small opacity-75">Total Volume (৳)</h6>
                                        <i class="fas fa-chart-line fa-2x opacity-25"></i>
                                    </div>
                                    <h2 class="fw-bold mb-0 text-white">{{ number_format($supplier->bills->sum('total_amount'), 2) }}৳</h2>
                                    <p class="small mb-0 mt-2 opacity-50">Cumulative spend history</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="premium-card">
                        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-uppercase text-muted small"><i class="fas fa-history me-2 text-primary"></i>Purchase History</h6>
                            <a href="{{ route('purchase.list', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-light border fw-bold px-3">View Master List</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table premium-table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Reference No</th>
                                            <th>Transaction Date</th>
                                            <th>Quantity Profile</th>
                                            <th>Workflow Status</th>
                                            <th class="text-end pe-4">Order Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($supplier->purchases->take(10) as $purchase)
                                            <td class="ps-4">
                                                <a href="{{ route('purchase.show', $purchase->id) }}" class="fw-bold text-primary">#{{ $purchase->id }}</a>
                                            </td>
                                            <td>{{ $purchase->date ? date('d M, Y', strtotime($purchase->date)) : $purchase->created_at->format('d M, Y') }}</td>
                                            <td class="small fw-500 text-dark">{{ $purchase->items_count ?? $purchase->items->count() }} Selected SKU(s)</td>
                                            <td>
                                                <span class="badge {{ $purchase->status == 'completed' ? 'bg-success' : 'bg-warning' }} rounded-pill px-3">
                                                    {{ strtoupper($purchase->status) }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end fw-bold text-dark">{{ number_format($purchase->bill->total_amount ?? 0, 2) }}৳</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <div class="mb-2"><i class="fas fa-inbox fa-2x opacity-25"></i></div>
                                                    <p class="mb-0 small">No recent procurement tracks found.</p>
                                                </div>
                                            </td>
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
