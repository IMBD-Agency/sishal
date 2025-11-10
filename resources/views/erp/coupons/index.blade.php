@extends('erp.master')

@section('title', 'Coupon Management')

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
                            <li class="breadcrumb-item active" aria-current="page">Coupon Management</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Coupon Management</h2>
                    <p class="text-muted mb-0">Manage promo codes and discount coupons.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('coupons.create') }}" class="btn btn-primary" id="createCouponBtn" onclick="console.log('Create button clicked'); return true;">
                        <i class="fas fa-plus me-2"></i>Create Coupon
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" placeholder="Search by code or name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Coupons Table -->
            <div class="card">
                <div class="card-body">
                    @if($coupons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Usage</th>
                                        <th>Status</th>
                                        <th>Valid Period</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coupons as $coupon)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $coupon->code }}</strong>
                                            </td>
                                            <td>{{ $coupon->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($coupon->type) }}</span>
                                            </td>
                                            <td>
                                                @if($coupon->type === 'percentage')
                                                    {{ $coupon->value }}%
                                                @else
                                                    {{ number_format($coupon->value, 2) }}৳
                                                @endif
                                            </td>
                                            <td>
                                                {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                                            </td>
                                            <td>
                                                <button class="btn btn-sm status-toggle {{ $coupon->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                                        data-coupon-id="{{ $coupon->id }}" 
                                                        data-current-status="{{ $coupon->is_active }}">
                                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </td>
                                            <td>
                                                @if($coupon->start_date || $coupon->end_date)
                                                    <small>
                                                        @if($coupon->start_date)
                                                            {{ \Carbon\Carbon::parse($coupon->start_date)->format('M d, Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                        @if($coupon->end_date)
                                                            <br>to {{ \Carbon\Carbon::parse($coupon->end_date)->format('M d, Y') }}
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="text-muted">No limit</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('coupons.show', $coupon) }}" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Showing {{ $coupons->firstItem() }} to {{ $coupons->lastItem() }} of {{ $coupons->total() }} results
                            </div>
                            <div>
                                {{ $coupons->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No coupons found</h5>
                            <p class="text-muted">Get started by creating your first coupon.</p>
                            <a href="{{ route('coupons.create') }}" class="btn btn-primary" onclick="console.log('Create button clicked from empty state'); return true;">
                                <i class="fas fa-plus me-2"></i>Create Coupon
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Status toggle functionality
        document.querySelectorAll('.status-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const couponId = this.dataset.couponId;
                
                fetch(`/erp/coupons/${couponId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button appearance
                        this.className = `btn btn-sm ${data.is_active ? 'btn-success' : 'btn-secondary'} status-toggle`;
                        this.textContent = data.is_active ? 'Active' : 'Inactive';
                        this.dataset.currentStatus = data.is_active;
                        
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
                        alert.style.top = '20px';
                        alert.style.right = '20px';
                        alert.style.zIndex = '9999';
                        alert.innerHTML = `
                            <i class="fas fa-check-circle me-2"></i>${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(alert);
                        
                        setTimeout(() => {
                            alert.remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the coupon status.');
                });
            });
        });
    </script>
@endsection

