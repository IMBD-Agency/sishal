@extends('erp.master')

@section('title', 'Stock Value Report')

@push('head')
<style>
    .premium-card { border: none; border-radius: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; background: white; }
    .filter-bar { background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border: 1px solid #e5e7eb; border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 2rem; }
    .stock-badge { padding: 0.5rem 1rem; border-radius: 2rem; font-weight: 600; font-size: 0.875rem; }
    .stock-low { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .stock-normal { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .metric-card { padding: 1.5rem; border-radius: 1.25rem; color: white; margin-bottom: 1.5rem; }
</style>
@endpush

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-gray-800 mb-1">Stock Value Analysis</h2>
                    <p class="text-muted mb-0">Monitor inventory levels, valuation, and identify low stock items</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('simple-accounting.stock-export-excel', request()->all()) }}" class="btn btn-outline-success no-loader" target="_blank">
                        <i class="fas fa-file-excel me-2"></i> Excel
                    </a>
                    <a href="{{ route('simple-accounting.stock-export-pdf', request()->all()) }}" class="btn btn-outline-danger no-loader" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i> PDF
                    </a>
                </div>
            </div>

            <!-- Total Valuation Card -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="metric-card bg-primary shadow-sm">
                        <div class="small opacity-75 mb-1">Total Stock Valuation</div>
                        <div class="h3 fw-bold mb-0">{{ number_format($totalStockValue, 2) }} TK</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="metric-card bg-info shadow-sm">
                        <div class="small opacity-75 mb-1">Total Products Tracked</div>
                        <div class="h3 fw-bold mb-0">{{ $productStockValues->count() }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="metric-card bg-danger shadow-sm">
                        <div class="small opacity-75 mb-1">Low Stock Alerts</div>
                        <div class="h3 fw-bold mb-0">{{ $productStockValues->where('is_low', true)->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" action="{{ route('simple-accounting.stock-value') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Branch</label>
                            <select class="form-select" name="branch_id" onchange="this.form.submit()">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select" name="category_id" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Stock Status</label>
                            <select class="form-select" name="low_stock" onchange="this.form.submit()">
                                <option value="0" {{ !$lowStock ? 'selected' : '' }}>All Stock</option>
                                <option value="1" {{ $lowStock ? 'selected' : '' }}>Low Stock Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                             <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync-alt me-2"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
                <!-- Category Summary Table -->
                <div class="col-lg-4 mb-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 text-gray-800">Valuation by Category</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th class="text-end">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categoryStockValues as $data)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $data['category_name'] }}</div>
                                                <small class="text-muted">{{ $data['product_count'] }} products</small>
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($data['total_value'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details Table -->
                <div class="col-lg-8 mb-4">
                    <div class="premium-card h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-gray-800">Detailed Stock Inventory</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Product Name</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-end">Unit Cost</th>
                                            <th class="text-end pe-4">Total Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($productStockValues as $data)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    @if($data['product']->image)
                                                        <img src="{{ asset($data['product']->image) }}" class="rounded me-3" width="35" height="35" style="object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $data['product']->name }}</div>
                                                        <small class="text-muted">{{ $data['product']->sku }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="stock-badge {{ $data['is_low'] ? 'stock-low' : 'stock-normal' }}">
                                                    {{ $data['total_stock'] }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ number_format($data['unit_cost'], 2) }}</td>
                                            <td class="text-end fw-bold pe-4 text-primary">{{ number_format($data['total_value'], 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">No stock data found matching your filters.</td>
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
