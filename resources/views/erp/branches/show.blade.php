@extends('erp.master')

@section('title', 'Branch Workspace | ' . $branch->name)

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="container-fluid px-0">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('branches.index') }}" class="text-decoration-none text-muted">Branches</a></li>
                            <li class="breadcrumb-item active text-primary fw-600">{{ $branch->name }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-3">
                        <h4 class="fw-bold mb-0 text-dark">{{ $branch->name }}</h4>
                        <span class="status-pill {{ $branch->status == 'active' ? 'status-active' : 'status-inactive' }}">
                            <i class="fas {{ $branch->status == 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            {{ ucfirst($branch->status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 text-nowrap">
                    @can('edit branch')
                    <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-outline-dark px-4" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                    @endcan
                    <a href="{{ route('simple-accounting.sales-summary', ['branch_id' => $branch->id]) }}" class="btn btn-create-premium">
                        <i class="fas fa-chart-pie me-2"></i>Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-2">
        <!-- Overview Stats -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="premium-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">Live Inventory</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $products_count }} <span class="fs-6 text-muted fw-normal">SKUs</span></h3>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="premium-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">Workforce</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $employees_count }} <span class="fs-6 text-muted fw-normal">Staff</span></h3>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="premium-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-globe"></i>
                        </div>
                        <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">Ecommerce</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $branch->show_online ? 'Enabled' : 'Disabled' }}</h3>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="premium-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">Established</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem;">{{ $branch->created_at->format('M d, Y') }}</h3>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Product Inventory Section -->
            <div class="col-xl-8">
                <div class="premium-card mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">Live Product Inventory</h5>
                        @can('create warehouse record')
                        <a href="{{ route('warehouse-product-stocks.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-plus me-1"></i> Update Stock
                        </a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table premium-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th class="text-center">Base Qty</th>
                                    <th class="text-center">Variations</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branch_products as $bp)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-icon-sm bg-light rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-cube text-muted"></i>
                                            </div>
                                            <span class="fw-600">{{ $bp->product->name ?? 'Deleted Product' }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark fw-normal border">{{ $bp->product->category->name ?? 'N/A' }}</span></td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $bp->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($bp->quantity, 0) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($bp->product->has_variations)
                                            @php
                                                $varStockCount = $bp->product->variations->flatMap->stocks->where('branch_id', $branch->id)->sum('quantity');
                                            @endphp
                                            <span class="small text-muted">{{ number_format($varStockCount, 0) }} In Variations</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('product.edit', $bp->product_id) }}" class="btn-action btn-light border" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No products assigned to this branch yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Staff & Recent Activity -->
            <div class="col-xl-4">
                <!-- Staff Block -->
                <div class="premium-card mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0 text-dark">Staff Records</h5>
                    </div>
                    <div class="p-4 pt-0">
                        @forelse($employees as $emp)
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom last-border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: 700;">
                                    {{ strtoupper(substr($emp->user->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $emp->user->name }}</p>
                                    <p class="mb-0 text-muted extra-small">{{ $emp->position ?? 'Member' }}</p>
                                </div>
                            </div>
                            <span class="badge {{ $emp->status == 'active' ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 {{ $emp->status == 'active' ? 'text-success' : 'text-secondary' }} border-0 font-10">
                                {{ strtoupper($emp->status) }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-4 bg-light rounded-3">
                            <p class="text-muted small mb-0">No staff records found.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Sales Block -->
                <div class="premium-card">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0 text-dark">Recent Sales</h5>
                    </div>
                    <div class="p-4 pt-0">
                        @forelse($recent_sales as $sale)
                        <div class="recent-activity-item mb-3 d-flex gap-3">
                            <div class="activity-icon text-success">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0 fw-bold text-dark small">{{ $sale->invoice->invoice_number ?? 'INV-'.$sale->id }}</p>
                                    <span class="text-muted extra-small">{{ $sale->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mb-0 text-muted extra-small">Customer: {{ $sale->customer->name ?? 'Guest' }}</p>
                                <p class="mb-0 fw-bold text-primary small">TK. {{ number_format($sale->grand_total ?? 0, 0) }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <p class="text-muted small mb-0">No recent sales records.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Branch details page initialized
});
</script>
@endpush
@endsection