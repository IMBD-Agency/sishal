@extends('erp.master')

@section('title', 'Shipping Methods')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <!-- Page Header -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h2 class="mb-1 fw-bold text-dark">Shipping Methods</h2>
                            <p class="text-muted mb-0">Manage shipping options and delivery methods</p>
                        </div>
                        <a href="{{ route('shipping-methods.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Method
                        </a>
                    </div>

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Shipping Methods Table -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            @if($shippingMethods->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Name</th>
                                                <th class="border-0">Description</th>
                                                <th class="border-0">Cost</th>
                                                <th class="border-0">Delivery Time</th>
                                                <th class="border-0">Status</th>
                                                <th class="border-0">Sort Order</th>
                                                <th class="border-0 text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($shippingMethods as $method)
                                            <tr>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-shipping-fast text-primary me-3"></i>
                                                        <div>
                                                            <div class="fw-semibold">{{ $method->name }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-muted">{{ $method->description ?? 'No description' }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="fw-semibold text-success">{{ number_format($method->cost, 2) }}৳</span>
                                                </td>
                                                <td class="align-middle">
                                                    @if($method->delivery_time)
                                                        <span class="badge bg-info">{{ $method->delivery_time }}</span>
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if($method->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-light text-dark">{{ $method->sort_order }}</span>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('shipping-methods.edit', $method) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('shipping-methods.destroy', $method) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this shipping method?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
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
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Shipping Methods Found</h5>
                                    <p class="text-muted">Get started by adding your first shipping method.</p>
                                    <a href="{{ route('shipping-methods.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add First Method
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
