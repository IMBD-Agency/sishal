@extends('erp.master')

@section('title', 'Coupon Details')

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
                            <li class="breadcrumb-item"><a href="{{ route('erp.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('coupons.index') }}" class="text-decoration-none">Coupon Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Coupon Details</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Coupon Details</h2>
                    <p class="text-muted mb-0">View coupon information and usage statistics.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Coupon Code:</dt>
                                <dd class="col-sm-9"><strong class="text-primary">{{ $coupon->code }}</strong></dd>

                                <dt class="col-sm-3">Name:</dt>
                                <dd class="col-sm-9">{{ $coupon->name ?? '-' }}</dd>

                                <dt class="col-sm-3">Description:</dt>
                                <dd class="col-sm-9">{{ $coupon->description ?? '-' }}</dd>

                                <dt class="col-sm-3">Status:</dt>
                                <dd class="col-sm-9">
                                    <span class="badge {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Discount Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Discount Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Type:</dt>
                                <dd class="col-sm-9"><span class="badge bg-info">{{ ucfirst($coupon->type) }}</span></dd>

                                <dt class="col-sm-3">Value:</dt>
                                <dd class="col-sm-9">
                                    @if($coupon->type === 'percentage')
                                        {{ $coupon->value }}%
                                    @else
                                        {{ number_format($coupon->value, 2) }}৳
                                    @endif
                                </dd>

                                <dt class="col-sm-3">Minimum Purchase:</dt>
                                <dd class="col-sm-9">{{ $coupon->min_purchase ? number_format($coupon->min_purchase, 2) . '৳' : 'No minimum' }}</dd>

                                @if($coupon->type === 'percentage')
                                <dt class="col-sm-3">Max Discount:</dt>
                                <dd class="col-sm-9">{{ $coupon->max_discount ? number_format($coupon->max_discount, 2) . '৳' : 'No limit' }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Scope Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Applicability Scope</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Scope Type:</dt>
                                <dd class="col-sm-9">
                                    @if($coupon->scope_type === 'all')
                                        All Products
                                    @elseif($coupon->scope_type === 'categories')
                                        Specific Categories
                                    @elseif($coupon->scope_type === 'products')
                                        Specific Products
                                    @elseif($coupon->scope_type === 'exclude_categories')
                                        Exclude Categories
                                    @else
                                        Exclude Products
                                    @endif
                                </dd>

                                @if($coupon->applicable_categories)
                                <dt class="col-sm-3">Categories:</dt>
                                <dd class="col-sm-9">
                                    @php
                                        $cats = \App\Models\ProductServiceCategory::whereIn('id', $coupon->applicable_categories)->pluck('name');
                                    @endphp
                                    {{ $cats->implode(', ') }}
                                </dd>
                                @endif

                                @if($coupon->applicable_products)
                                <dt class="col-sm-3">Products:</dt>
                                <dd class="col-sm-9">
                                    @php
                                        $prods = \App\Models\Product::whereIn('id', $coupon->applicable_products)->pluck('name');
                                    @endphp
                                    {{ $prods->implode(', ') }}
                                </dd>
                                @endif

                                @if($coupon->excluded_categories)
                                <dt class="col-sm-3">Excluded Categories:</dt>
                                <dd class="col-sm-9">
                                    @php
                                        $exCats = \App\Models\ProductServiceCategory::whereIn('id', $coupon->excluded_categories)->pluck('name');
                                    @endphp
                                    {{ $exCats->implode(', ') }}
                                </dd>
                                @endif

                                @if($coupon->excluded_products)
                                <dt class="col-sm-3">Excluded Products:</dt>
                                <dd class="col-sm-9">
                                    @php
                                        $exProds = \App\Models\Product::whereIn('id', $coupon->excluded_products)->pluck('name');
                                    @endphp
                                    {{ $exProds->implode(', ') }}
                                </dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Usage Statistics -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Usage Statistics</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Total Usage:</dt>
                                <dd class="col-sm-9">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</dd>

                                <dt class="col-sm-3">Usage Per User:</dt>
                                <dd class="col-sm-9">{{ $coupon->user_limit }} time(s)</dd>

                                <dt class="col-sm-3">Valid Period:</dt>
                                <dd class="col-sm-9">
                                    @if($coupon->start_date || $coupon->end_date)
                                        @if($coupon->start_date)
                                            From: {{ \Carbon\Carbon::parse($coupon->start_date)->format('M d, Y H:i') }}
                                        @endif
                                        @if($coupon->end_date)
                                            <br>To: {{ \Carbon\Carbon::parse($coupon->end_date)->format('M d, Y H:i') }}
                                        @endif
                                    @else
                                        No time limit
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Edit Coupon
                                </a>
                                <form action="{{ route('coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-trash me-2"></i>Delete Coupon
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Usage -->
                    @if($coupon->usages->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Usage</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach($coupon->usages->take(5) as $usage)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted">{{ $usage->created_at->format('M d, Y') }}</small>
                                                <br>
                                                <strong>{{ number_format($usage->discount_amount, 2) }}৳</strong> discount
                                            </div>
                                            @if($usage->order)
                                                <a href="{{ route('order.show', $usage->order->id) }}" class="btn btn-sm btn-outline-info">
                                                    View Order
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

