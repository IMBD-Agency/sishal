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

        <!-- Inventory Database -->
        <div class="premium-card shadow-sm">
            <div class="card-header bg-white p-4 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Location Inventory</h5>
                    <p class="text-muted small mb-0">Operational catalog of products assigned to this specific outlet.</p>
                </div>
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="productSearch" class="form-control" placeholder="Quick SKU or name search...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table mb-0" id="productsTable">
                        <thead>
                            <tr>
                                <th>Specification</th>
                                <th>Serial Hash</th>
                                <th class="text-end">MSRP Price</th>
                                <th class="text-center">Region</th>
                                <th class="text-center">Available Stock</th>
                                <th class="text-end">Management</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branch_products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="thumbnail-box" style="width: 44px; height: 44px;">
                                            @if($product->product?->image)
                                                <img src="{{ asset($product->product->image) }}" alt="">
                                            @else
                                                <i class="fas fa-box text-light"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-0">{{ $product->product?->name ?? 'Corrupt Data Node' }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">System Ref: #{{ $product->product?->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded text-primary" style="font-size: 0.75rem;">{{ $product->product?->sku ?? 'NO-SKU' }}</code>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-success">৳{{ number_format(($product->product?->discount && $product->product?->discount > 0) ? $product->product?->discount : ($product->product?->price ?? 0), 2) }}</div>
                                    <div class="text-muted text-decoration-line-through small" style="font-size: 0.7rem;">৳{{ number_format($product->product?->price ?? 0, 2) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="category-tag">
                                        {{ $product->product?->category?->name ?? 'Unassigned' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $qty = $product->quantity;
                                        $statusClass = $qty > 10 ? 'status-active' : ($qty > 0 ? 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-25' : 'status-inactive');
                                    @endphp
                                    <div class="status-pill {{ $statusClass }}">
                                        {{ $qty }} Units
                                    </div>
                                    @if($product->product?->has_variations)
                                        <div class="text-muted mt-1 fw-bold" style="font-size: 0.6rem;">(Split Variants)</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="/erp/product/{{ $product->product_id }}/details" class="btn btn-action" title="Full Timeline">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        @if($qty <= 0)
                                        <form action="{{ route('branches.products.remove', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deregister this empty track?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-action text-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-boxes fa-3x text-light mb-3"></i>
                                    <h5 class="fw-bold text-secondary">Inventory Empty</h5>
                                    <p class="text-muted small">No items currently registered to this physical node.</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('productSearch');
    const table = document.getElementById('productsTable');
    
    if (search && table) {
        search.addEventListener('keyup', function() {
            const val = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.cells.length > 1) { // Skip empty row
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(val) ? '' : 'none';
                }
            });
        });
    }
});
</script>
@endpush
@endsection