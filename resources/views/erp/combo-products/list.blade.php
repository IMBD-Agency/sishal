@extends('erp.master')

@section('title', 'Manage Combos')

@section('body')
@include('erp.components.sidebar')

<div class="main-content" id="mainContent">
    @include('erp.components.header')

    <!-- Premium Header -->
    <div class="glass-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active text-primary fw-600">Manage Combos</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">Combo Products</h4>
                <p class="text-muted small mb-0">Manage your combo offers and bundles</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                @can('manage combos')
                <a href="{{ route('erp.combo-products.create') }}" class="btn btn-primary px-4" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-plus me-2"></i>Create Combo
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-4">
        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('erp.combo-products.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search combo name or SKU..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-filter me-2"></i>Filter</button>
                        <a href="{{ route('erp.combo-products.index') }}" class="btn btn-outline-secondary px-4"><i class="fas fa-sync-alt me-2"></i>Reset</a>
                    </div>
                </form>
            </div>
        </div>
        @if($combos->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Combo Products Yet</h5>
                    <p class="text-muted mb-4">Create your first combo offer to bundle products together</p>
                    <a href="{{ route('erp.combo-products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Combo
                    </a>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Combo Name</th>
                                <th>Created Branch</th>
                                <th>Items</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($combos as $combo)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($combo->image ?? 'static/default-product.jpg') }}" 
                                                 class="rounded me-3" 
                                                 width="50" height="50" 
                                                 style="object-fit: cover;">
                                            <div>
                                                <strong>{{ $combo->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $combo->sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2">
                                            {{ $combo->branch ? $combo->branch->name : 'Global/Main' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $combo->comboItems->count() }} items</span>
                                    </td>
                                    <td>
                                        <strong>৳{{ number_format($combo->price, 2) }}</strong>
                                        @if($combo->combo_original_price > $combo->price)
                                            <br>
                                            <small class="text-success">Save ৳{{ number_format($combo->combo_original_price - $combo->price, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $combo->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $combo->status }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('erp.combo-products.manage', $combo) }}" 
                                           class="btn btn-sm btn-primary me-1">
                                            <i class="fas fa-cog me-2"></i>Manage
                                        </a>
                                        @can('manage combos')
                                        <form action="{{ route('erp.combo-products.delete', $combo) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this combo? All items will be removed.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-top">
                {{ $combos->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
