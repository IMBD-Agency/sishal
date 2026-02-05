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