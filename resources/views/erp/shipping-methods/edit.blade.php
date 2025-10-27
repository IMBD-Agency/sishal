@extends('erp.master')

@section('title', 'Edit Shipping Method')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-8">
                    <!-- Page Header -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h2 class="mb-1 fw-bold text-dark">Edit Shipping Method</h2>
                            <p class="text-muted mb-0">Update shipping method details</p>
                        </div>
                        <a href="{{ route('shipping-methods.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>

                    <!-- Form Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('shipping-methods.update', $shippingMethod) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-tag me-2 text-primary"></i>Method Name *
                                        </label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                               placeholder="e.g., Standard Shipping" value="{{ old('name', $shippingMethod->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-dollar-sign me-2 text-success"></i>Shipping Cost *
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="cost" class="form-control @error('cost') is-invalid @enderror" 
                                                   placeholder="0.00" step="0.01" min="0" value="{{ old('cost', $shippingMethod->cost) }}" required>
                                            <span class="input-group-text">৳</span>
                                        </div>
                                        @error('cost')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-info-circle me-2 text-info"></i>Description
                                        </label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                                  rows="3" placeholder="Optional description for this shipping method">{{ old('description', $shippingMethod->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-clock me-2 text-warning"></i>Min Delivery Days
                                        </label>
                                        <input type="number" name="estimated_days_min" class="form-control @error('estimated_days_min') is-invalid @enderror" 
                                               placeholder="1" min="1" value="{{ old('estimated_days_min', $shippingMethod->estimated_days_min) }}">
                                        @error('estimated_days_min')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-clock me-2 text-warning"></i>Max Delivery Days
                                        </label>
                                        <input type="number" name="estimated_days_max" class="form-control @error('estimated_days_max') is-invalid @enderror" 
                                               placeholder="7" min="1" value="{{ old('estimated_days_max', $shippingMethod->estimated_days_max) }}">
                                        @error('estimated_days_max')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-sort me-2 text-secondary"></i>Sort Order
                                        </label>
                                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                                               placeholder="0" min="0" value="{{ old('sort_order', $shippingMethod->sort_order) }}">
                                        <small class="text-muted">Lower numbers appear first</small>
                                        @error('sort_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-toggle-on me-2 text-success"></i>Status
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                                   {{ old('is_active', $shippingMethod->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label">Active (visible to customers)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Shipping Method
                                            </button>
                                            <a href="{{ route('shipping-methods.index') }}" class="btn btn-outline-secondary">
                                                Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
