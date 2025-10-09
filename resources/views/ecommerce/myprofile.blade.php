@extends('ecommerce.master')

@section('main-section')
<style>
    .profile-page {
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .profile-header {
        background: white;
        border-bottom: 1px solid #e9ecef;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #0da2e7;
    }
    
    .card-simple {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }
    
    .card-header-simple {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .card-body-simple {
        padding: 1.5rem;
    }
    
    .form-control-simple {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        font-size: 14px;
    }
    
    .form-control-simple:focus {
        border-color: #0da2e7;
        box-shadow: 0 0 0 0.2rem rgba(13, 162, 231, 0.25);
        outline: none;
    }
    
    .btn-simple {
        background: #0da2e7;
        border: 1px solid #0da2e7;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .btn-simple:hover {
        background: #0b8cc7;
        border-color: #0b8cc7;
        color: white;
    }
    
    .btn-outline-simple {
        background: transparent;
        border: 1px solid #6c757d;
        color: #6c757d;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .btn-outline-simple:hover {
        background: #6c757d;
        color: white;
    }
    
    .form-label-simple {
        font-weight: 500;
        color: #555;
        margin-bottom: 0.25rem;
        font-size: 14px;
    }
    
    .nav-tabs-simple {
        border: none;
        background: #f8f9fa;
    }
    
    .nav-tabs-simple .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
        background: transparent;
    }
    
    .nav-tabs-simple .nav-link:hover {
        color: #0da2e7;
        background: rgba(13, 162, 231, 0.1);
    }
    
    .nav-tabs-simple .nav-link.active {
        color: #0da2e7;
        background: white;
        border-bottom: 2px solid #0da2e7;
    }
    
    .order-card-simple {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .text-muted-simple {
        color: #6c757d;
        font-size: 13px;
    }
</style>

<div class="container py-4">
    <!-- Simple Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) . '&background=0da2e7&color=fff&size=80' }}" 
                         alt="Profile Avatar" class="profile-avatar me-3">
                    <div>
                        <h3 class="mb-1">{{$user->first_name}} {{$user->last_name}}</h3>
                        <p class="mb-1 text-muted-simple">{{$user->email}}</p>
                        <small class="text-muted-simple">Member since {{ $user->created_at->format('M Y') }} • {{ $orders->count() }} Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <small class="text-muted-simple">{{ $orders->where('status', 'delivered')->count() }} completed orders</small>
            </div>
        </div>
    </div>

        <!-- Simple Tab Navigation -->
        <div class="card-simple">
            <div class="card-header p-0">
                <ul class="nav nav-tabs-simple nav-fill" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                            type="button" role="tab" aria-controls="profile" aria-selected="true">
                            My Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security"
                            type="button" role="tab" aria-controls="security" aria-selected="false">
                            Security
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button"
                            role="tab" aria-controls="orders" aria-selected="false">
                            My Orders
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="card-body-simple">
                <div class="tab-content" id="profileTabContent">
                    <!-- My Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <form class="row g-3" action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-6">
                                <h6 class="mb-3">Personal Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label-simple">First Name</label>
                                        <input type="text" class="form-control-simple" id="firstName" name="first_name" value="{{ auth()->user()->first_name }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label-simple">Last Name</label>
                                        <input type="text" class="form-control-simple" id="lastName" name="last_name" value="{{ auth()->user()->last_name }}">
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label-simple">Email Address</label>
                                        <input type="email" class="form-control-simple" id="email" name="email" value="{{ auth()->user()->email }}">
                                    </div>
                                    <div class="col-12">
                                        <label for="phone" class="form-label-simple">Phone Number</label>
                                        <input type="tel" class="form-control-simple" id="phone" name="phone" value="{{ optional(auth()->user()->customer)->phone }}" placeholder="+1 (555) 123-4567">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Address Information</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="street" class="form-label-simple">Street Address</label>
                                        <input type="text" class="form-control-simple" id="street" name="address_1" value="{{ optional(auth()->user()->customer)->address_1 }}" placeholder="123 Main Street">
                                    </div>
                                    <div class="col-12">
                                        <label for="address_2" class="form-label-simple">Address Line 2</label>
                                        <input type="text" class="form-control-simple" id="address_2" name="address_2" value="{{ optional(auth()->user()->customer)->address_2 }}" placeholder="Apartment, suite, unit, etc.">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="city" class="form-label-simple">City</label>
                                        <input type="text" class="form-control-simple" id="city" name="city" value="{{ optional(auth()->user()->customer)->city }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="state" class="form-label-simple">State/Province</label>
                                        <input type="text" class="form-control-simple" id="state" name="state" value="{{ optional(auth()->user()->customer)->state }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zip" class="form-label-simple">ZIP/Postal Code</label>
                                        <input type="text" class="form-control-simple" id="zip" name="zip_code" value="{{ optional(auth()->user()->customer)->zip_code }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="country" class="form-label-simple">Country</label>
                                        <input type="text" class="form-control-simple" id="country" name="country" value="{{ optional(auth()->user()->customer)->country }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-center pt-2">
                                <button type="submit" class="btn-simple">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <h6 class="mb-3">Change Password</h6>
                                <form class="row g-3" action="{{ route('password.store') }}" method="POST">
                                    @csrf
                                    <div class="col-12">
                                        <label for="currentPassword" class="form-label-simple">Current Password</label>
                                        <input type="password" class="form-control-simple" id="currentPassword" name="current_password" required autocomplete="current-password">
                                    </div>
                                    <div class="col-12">
                                        <label for="newPassword" class="form-label-simple">New Password</label>
                                        <input type="password" class="form-control-simple" id="newPassword" name="password" required autocomplete="new-password">
                                    </div>
                                    <div class="col-12">
                                        <label for="confirmPassword" class="form-label-simple">Confirm New Password</label>
                                        <input type="password" class="form-control-simple" id="confirmPassword" name="password_confirmation" required autocomplete="new-password">
                                    </div>
                                    <div class="col-12 text-center pt-2">
                                        <button type="submit" class="btn-simple">
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- My Orders Tab -->
                    <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order History</h6>
                            <select class="form-control-simple w-auto">
                                <option>All Orders</option>
                                <option>Completed</option>
                                <option>Pending</option>
                                <option>Cancelled</option>
                            </select>
                        </div>

                        @forelse ($orders as $order)
                        <div class="order-card-simple">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">{{$order->order_number}}</h6>
                                    <small class="text-muted-simple">
                                        Placed on {{$order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('M d, Y') : '-'}}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="h6 mb-1 fw-bold text-primary">{{$order->total}}৳</div>
                                    <span class="badge 
                                        {{ 
                                            $order->status == 'pending' ? 'bg-secondary' : 
                                            ($order->status == 'approved' ? 'bg-warning' : 
                                            ($order->status == 'shipping' ? 'bg-info' : 
                                            ($order->status == 'delivered' ? 'bg-success' : 
                                            ($order->status == 'cancelled' ? 'bg-danger' : 'bg-secondary')))) 
                                        }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="row g-2">
                                @foreach ($order->items as $item)
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-2 bg-light rounded">
                                        <div class="me-2">
                                            @if($item->product)
                                                <img src="{{ asset($item->product->image) }}" alt="Product" class="rounded"
                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded d-flex align-items-center justify-content-center bg-white"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            @if($item->product)
                                                <a href="{{ route('product.details', $item->product->slug) }}" class="d-block fw-semibold text-dark text-decoration-none small">{{ $item->product->name }}</a>
                                            @else
                                                <span class="d-block fw-semibold text-muted small">Product Not Available</span>
                                            @endif
                                            <small class="text-muted-simple">Qty: {{number_format($item->quantity,0)}}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                                <a href="{{ route('order.details',$order->order_number) }}" class="btn-outline-simple btn-sm">
                                    View Details
                                </a>
                                <div class="d-flex gap-2">
                                    @if($order->status != 'pending' && $order->status != 'cancelled')
                                    <button class="btn-simple btn-sm">
                                        Reorder
                                    </button>
                                    @elseif($order->status != 'cancelled')
                                    <form action="{{ route('order.cancel', $order->id) }}" method="POST" class="d-inline cancel-order-form">
                                        @csrf
                                        <button type="button" class="btn-outline-simple btn-sm btn-cancel-order" data-bs-toggle="modal" data-bs-target="#cancelOrderModal" data-order-id="{{$order->id}}">
                                            Cancel
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                            <div class="text-center py-4">
                                <h6 class="text-muted mb-2">No Orders Found</h6>
                                <p class="text-muted-simple">You haven't placed any orders yet.</p>
                                <a href="{{ route('ecommerce.home') }}" class="btn-simple">
                                    Start Shopping
                                </a>
                            </div>
                        @endforelse

                        @if($orders->count() > 0)
                        <div class="d-flex justify-content-center mt-3">
                            {{ $orders->links('vendor.pagination.bootstrap-5') }}
                        </div>
                        @endif
                    </div>

                    
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-simple" data-bs-dismiss="modal">No, Keep Order</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmCancelOrderBtn">Yes, Cancel Order</button>
                </div>
            </div>
        </div>
    </div>

@endsection

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let formToSubmit = null;
            document.querySelectorAll('.btn-cancel-order').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    formToSubmit = this.closest('form');
                });
            });
            document.getElementById('confirmCancelOrderBtn').addEventListener('click', function() {
                if(formToSubmit) {
                    formToSubmit.submit();
                }
            });
        });
    </script>