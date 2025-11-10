@extends('erp.master')

@section('title', 'Edit Coupon')

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
                            <li class="breadcrumb-item active" aria-current="page">Edit Coupon</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0">Edit Coupon</h2>
                    <p class="text-muted mb-0">Update coupon details.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Coupons
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4 py-4">
            <form action="{{ route('coupons.update', $coupon) }}" method="POST" id="couponForm">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                                   id="code" name="code" value="{{ old('code', $coupon->code) }}" required 
                                                   placeholder="e.g., SAVE20" style="text-transform: uppercase;">
                                            <div class="form-text">Enter a unique coupon code (uppercase)</div>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Coupon Name</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $coupon->name) }}" 
                                                   placeholder="e.g., Summer Sale 2024">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Enter coupon description...">{{ old('description', $coupon->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Discount Settings -->
                        <div class="card mb-4" id="discountSettingsCard">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Discount Settings <span class="text-muted small">(Optional - Leave empty for free delivery only)</span></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Discount Type <span class="text-danger" id="typeRequired">*</span></label>
                                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                                <option value="">Select discount type</option>
                                                <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="value" class="form-label">Discount Value <span class="text-danger" id="valueRequired">*</span></label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('value') is-invalid @enderror" 
                                                   id="value" name="value" value="{{ old('value', $coupon->value) }}">
                                            <div class="form-text" id="valueHelp">Enter percentage (0-100) or fixed amount. Leave empty if only offering free delivery.</div>
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="min_purchase" class="form-label">Minimum Purchase (৳)</label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('min_purchase') is-invalid @enderror" 
                                                   id="min_purchase" name="min_purchase" value="{{ old('min_purchase', $coupon->min_purchase) }}">
                                            <div class="form-text">Minimum order amount to use this coupon</div>
                                            @error('min_purchase')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3" id="maxDiscountField">
                                            <label for="max_discount" class="form-label">Maximum Discount (৳)</label>
                                            <input type="number" step="0.01" min="0" class="form-control @error('max_discount') is-invalid @enderror" 
                                                   id="max_discount" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}">
                                            <div class="form-text">Maximum discount amount (for percentage coupons)</div>
                                            @error('max_discount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Limits -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Usage Limits</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="usage_limit" class="form-label">Total Usage Limit</label>
                                            <input type="number" min="1" class="form-control @error('usage_limit') is-invalid @enderror" 
                                                   id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}">
                                            <div class="form-text">Leave empty for unlimited usage. Current usage: {{ $coupon->used_count }}</div>
                                            @error('usage_limit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="user_limit" class="form-label">Usage Per User <span class="text-danger">*</span></label>
                                            <input type="number" min="1" class="form-control @error('user_limit') is-invalid @enderror" 
                                                   id="user_limit" name="user_limit" value="{{ old('user_limit', $coupon->user_limit) }}" required>
                                            <div class="form-text">How many times a user can use this coupon</div>
                                            @error('user_limit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validity Period -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Validity Period</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" 
                                                   value="{{ old('start_date', $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d\TH:i') : '') }}">
                                            <div class="form-text">Leave empty to start immediately</div>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" 
                                                   value="{{ old('end_date', $coupon->end_date ? \Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d\TH:i') : '') }}">
                                            <div class="form-text">Leave empty for no expiry</div>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scope Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Applicability Scope</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="scope_type" class="form-label">Scope Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('scope_type') is-invalid @enderror" id="scope_type" name="scope_type" required>
                                        <option value="all" {{ old('scope_type', $coupon->scope_type) == 'all' ? 'selected' : '' }}>All Products</option>
                                        <option value="categories" {{ old('scope_type', $coupon->scope_type) == 'categories' ? 'selected' : '' }}>Specific Categories</option>
                                        <option value="products" {{ old('scope_type', $coupon->scope_type) == 'products' ? 'selected' : '' }}>Specific Products</option>
                                        <option value="exclude_categories" {{ old('scope_type', $coupon->scope_type) == 'exclude_categories' ? 'selected' : '' }}>Exclude Categories</option>
                                        <option value="exclude_products" {{ old('scope_type', $coupon->scope_type) == 'exclude_products' ? 'selected' : '' }}>Exclude Products</option>
                                    </select>
                                    @error('scope_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Categories Selection -->
                                <div class="mb-3" id="categoriesField" style="display: none;">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tags me-1 text-primary"></i>Select Categories
                                    </label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="text" class="form-control" id="searchCategories" placeholder="Search categories...">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllCategories">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllCategories">Deselect All</button>
                                    </div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #fff;" id="categoriesList">
                                        @php
                                            $selectedCategories = old('applicable_categories', $coupon->applicable_categories ?? []);
                                        @endphp
                                        @foreach($categories as $index => $category)
                                            <div class="form-check mb-2 category-item {{ $index >= 30 ? 'd-none' : '' }}" data-name="{{ strtolower($category->name) }}" data-index="{{ $index }}">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="applicable_categories[]" 
                                                       value="{{ $category->id }}" 
                                                       id="cat_{{ $category->id }}"
                                                       {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="cat_{{ $category->id }}">
                                                    {{ $category->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($categories) > 30)
                                        <button type="button" class="btn btn-sm btn-link p-0 mt-2" id="showMoreCategories">
                                            Show More ({{ count($categories) - 30 }} remaining)
                                        </button>
                                    @endif
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Select one or more categories to apply this coupon.
                                    </div>
                                </div>

                                <!-- Products Selection -->
                                <div class="mb-3" id="productsField" style="display: none;">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-box me-1 text-primary"></i>Select Products
                                    </label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="text" class="form-control" id="searchProducts" placeholder="Search products...">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllProducts">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllProducts">Deselect All</button>
                                    </div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #fff;" id="productsList">
                                        @php
                                            $selectedProducts = old('applicable_products', $coupon->applicable_products ?? []);
                                        @endphp
                                        @foreach($products as $index => $product)
                                            <div class="form-check mb-2 product-item {{ $index >= 30 ? 'd-none' : '' }}" data-name="{{ strtolower($product->name . ' ' . ($product->sku ?? '')) }}" data-index="{{ $index }}">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="applicable_products[]" 
                                                       value="{{ $product->id }}" 
                                                       id="prod_{{ $product->id }}"
                                                       {{ in_array($product->id, $selectedProducts) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="prod_{{ $product->id }}">
                                                    {{ $product->name }} @if($product->sku)({{ $product->sku }})@endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($products) > 30)
                                        <button type="button" class="btn btn-sm btn-link p-0 mt-2" id="showMoreProducts">
                                            Show More ({{ count($products) - 30 }} remaining)
                                        </button>
                                    @endif
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Select one or more products to apply this coupon.
                                    </div>
                                </div>

                                <!-- Excluded Categories -->
                                <div class="mb-3" id="excludedCategoriesField" style="display: none;">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-minus-circle me-1 text-warning"></i>Exclude Categories
                                    </label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="text" class="form-control" id="searchExcludedCategories" placeholder="Search categories...">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllExcludedCategories">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllExcludedCategories">Deselect All</button>
                                    </div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #fff;" id="excludedCategoriesList">
                                        @php
                                            $selectedExcludedCategories = old('excluded_categories', $coupon->excluded_categories ?? []);
                                        @endphp
                                        @foreach($categories as $index => $category)
                                            <div class="form-check mb-2 excluded-category-item {{ $index >= 30 ? 'd-none' : '' }}" data-name="{{ strtolower($category->name) }}" data-index="{{ $index }}">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="excluded_categories[]" 
                                                       value="{{ $category->id }}" 
                                                       id="ex_cat_{{ $category->id }}"
                                                       {{ in_array($category->id, $selectedExcludedCategories) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ex_cat_{{ $category->id }}">
                                                    {{ $category->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($categories) > 30)
                                        <button type="button" class="btn btn-sm btn-link p-0 mt-2" id="showMoreExcludedCategories">
                                            Show More ({{ count($categories) - 30 }} remaining)
                                        </button>
                                    @endif
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Select categories to exclude from this coupon.
                                    </div>
                                </div>

                                <!-- Excluded Products -->
                                <div class="mb-3" id="excludedProductsField" style="display: none;">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-minus-circle me-1 text-warning"></i>Exclude Products
                                    </label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="text" class="form-control" id="searchExcludedProducts" placeholder="Search products...">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllExcludedProducts">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllExcludedProducts">Deselect All</button>
                                    </div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #fff;" id="excludedProductsList">
                                        @php
                                            $selectedExcludedProducts = old('excluded_products', $coupon->excluded_products ?? []);
                                        @endphp
                                        @foreach($products as $index => $product)
                                            <div class="form-check mb-2 excluded-product-item {{ $index >= 30 ? 'd-none' : '' }}" data-name="{{ strtolower($product->name . ' ' . ($product->sku ?? '')) }}" data-index="{{ $index }}">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="excluded_products[]" 
                                                       value="{{ $product->id }}" 
                                                       id="ex_prod_{{ $product->id }}"
                                                       {{ in_array($product->id, $selectedExcludedProducts) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="ex_prod_{{ $product->id }}">
                                                    {{ $product->name }} @if($product->sku)({{ $product->sku }})@endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($products) > 30)
                                        <button type="button" class="btn btn-sm btn-link p-0 mt-2" id="showMoreExcludedProducts">
                                            Show More ({{ count($products) - 30 }} remaining)
                                        </button>
                                    @endif
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Select products to exclude from this coupon.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                                <div class="form-text mb-3">Toggle to activate or deactivate this coupon</div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="free_delivery" name="free_delivery" value="1"
                                           {{ old('free_delivery', $coupon->free_delivery) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="free_delivery">
                                        Free Delivery
                                    </label>
                                </div>
                                <div class="form-text">Enable to make delivery charge free when this coupon is applied</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Coupon
                                    </button>
                                    <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle discount fields based on free_delivery checkbox
            const freeDeliveryCheckbox = document.getElementById('free_delivery');
            const typeField = document.getElementById('type');
            const valueField = document.getElementById('value');
            const typeRequired = document.getElementById('typeRequired');
            const valueRequired = document.getElementById('valueRequired');
            
            function toggleDiscountFields() {
                const freeDeliveryEnabled = freeDeliveryCheckbox?.checked || false;
                const hasDiscountValue = valueField?.value && parseFloat(valueField.value) > 0;
                
                // If free delivery is enabled and no discount value, make discount fields optional
                if (freeDeliveryEnabled && !hasDiscountValue) {
                    if (typeField) typeField.removeAttribute('required');
                    if (valueField) valueField.removeAttribute('required');
                    if (typeRequired) typeRequired.style.display = 'none';
                    if (valueRequired) valueRequired.style.display = 'none';
                } else {
                    // If discount value is provided, make fields required
                    if (typeField && valueField?.value && parseFloat(valueField.value) > 0) {
                        typeField.setAttribute('required', 'required');
                        valueField.setAttribute('required', 'required');
                        if (typeRequired) typeRequired.style.display = 'inline';
                        if (valueRequired) valueRequired.style.display = 'inline';
                    }
                }
            }
            
            // Listen to free delivery checkbox changes
            if (freeDeliveryCheckbox) {
                freeDeliveryCheckbox.addEventListener('change', toggleDiscountFields);
            }
            
            // Listen to discount value changes
            if (valueField) {
                valueField.addEventListener('input', toggleDiscountFields);
            }
            
            // Toggle max discount field based on type
            if (typeField) {
                typeField.addEventListener('change', function() {
                    const maxDiscountField = document.getElementById('maxDiscountField');
                    const valueHelp = document.getElementById('valueHelp');
                    
                    if (maxDiscountField && valueHelp) {
                        if (this.value === 'percentage') {
                            maxDiscountField.style.display = 'block';
                            valueHelp.textContent = 'Enter percentage (0-100)';
                        } else {
                            maxDiscountField.style.display = 'none';
                            valueHelp.textContent = 'Enter fixed amount in ৳';
                        }
                    }
                });

                // Trigger on page load
                typeField.dispatchEvent(new Event('change'));
            }
            
            // Initialize on page load
            toggleDiscountFields();

            // Toggle scope fields based on scope type
            const scopeTypeField = document.getElementById('scope_type');
            if (scopeTypeField) {
                scopeTypeField.addEventListener('change', function() {
                    const categoriesField = document.getElementById('categoriesField');
                    const productsField = document.getElementById('productsField');
                    const excludedCategoriesField = document.getElementById('excludedCategoriesField');
                    const excludedProductsField = document.getElementById('excludedProductsField');

                    // Hide all fields first
                    if (categoriesField) categoriesField.style.display = 'none';
                    if (productsField) productsField.style.display = 'none';
                    if (excludedCategoriesField) excludedCategoriesField.style.display = 'none';
                    if (excludedProductsField) excludedProductsField.style.display = 'none';

                    // Show relevant field
                    if (this.value === 'categories' && categoriesField) {
                        categoriesField.style.display = 'block';
                    } else if (this.value === 'products' && productsField) {
                        productsField.style.display = 'block';
                    } else if (this.value === 'exclude_categories' && excludedCategoriesField) {
                        excludedCategoriesField.style.display = 'block';
                    } else if (this.value === 'exclude_products' && excludedProductsField) {
                        excludedProductsField.style.display = 'block';
                    }
                });

                // Trigger on page load
                scopeTypeField.dispatchEvent(new Event('change'));
            }

            // Uppercase coupon code
            const codeField = document.getElementById('code');
            if (codeField) {
                codeField.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }

            // Form validation
            const couponForm = document.getElementById('couponForm');
            if (couponForm) {
                couponForm.addEventListener('submit', function(e) {
                    const startDate = document.getElementById('start_date')?.value;
                    const endDate = document.getElementById('end_date')?.value;
                    
                    if (startDate && endDate && new Date(startDate) >= new Date(endDate)) {
                        e.preventDefault();
                        alert('End date must be after start date.');
                        return false;
                    }

                    const freeDelivery = document.getElementById('free_delivery')?.checked || false;
                    const type = document.getElementById('type')?.value;
                    const value = parseFloat(document.getElementById('value')?.value || 0);
                    
                    // Validate that at least one benefit is provided
                    if (!freeDelivery && (!type || value <= 0)) {
                        e.preventDefault();
                        alert('Either enable free delivery or provide a discount value.');
                        return false;
                    }
                    
                    // If discount is provided, validate it
                    if (type && value > 0) {
                        if (type === 'percentage' && value > 100) {
                            e.preventDefault();
                            alert('Percentage discount cannot exceed 100%.');
                            return false;
                        }
                    }
                });
            }
        });
    </script>
@endsection

@push('head')
    <style>
        /* Clean Checkbox List Styling */
        .form-check {
            padding-left: 1.5rem;
        }
        .form-check-input {
            margin-top: 0.25rem;
            margin-left: -1.5rem;
        }
        .form-check-label {
            cursor: pointer;
            user-select: none;
        }
        .form-check.d-none {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ITEMS_PER_PAGE = 30;
            let visibleCount = {
                categories: ITEMS_PER_PAGE,
                products: ITEMS_PER_PAGE,
                excludedCategories: ITEMS_PER_PAGE,
                excludedProducts: ITEMS_PER_PAGE
            };

            // Show more items function
            function showMoreItems(containerSelector, itemSelector, type) {
                const items = document.querySelectorAll(containerSelector + ' ' + itemSelector);
                const currentVisible = visibleCount[type];
                const nextVisible = currentVisible + ITEMS_PER_PAGE;
                
                items.forEach(function(item, index) {
                    if (index >= currentVisible && index < nextVisible) {
                        item.classList.remove('d-none');
                    }
                });
                
                visibleCount[type] = nextVisible;
                
                // Hide "Show More" button if all items are visible
                const buttonIdMap = {
                    'categories': 'showMoreCategories',
                    'products': 'showMoreProducts',
                    'excludedCategories': 'showMoreExcludedCategories',
                    'excludedProducts': 'showMoreExcludedProducts'
                };
                const showMoreBtn = document.getElementById(buttonIdMap[type]);
                if (showMoreBtn && nextVisible >= items.length) {
                    showMoreBtn.style.display = 'none';
                } else if (showMoreBtn) {
                    const remaining = items.length - nextVisible;
                    showMoreBtn.textContent = 'Show More (' + remaining + ' remaining)';
                }
            }

            // Select All / Deselect All functions
            function selectAll(containerSelector, itemSelector) {
                const items = document.querySelectorAll(containerSelector + ' ' + itemSelector);
                items.forEach(function(item) {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = true;
                });
            }

            function deselectAll(containerSelector, itemSelector) {
                const items = document.querySelectorAll(containerSelector + ' ' + itemSelector);
                items.forEach(function(item) {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                });
            }

            // Search functionality
            function setupSearch(searchInputId, containerSelector, itemSelector) {
                const searchInput = document.getElementById(searchInputId);
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const items = document.querySelectorAll(containerSelector + ' ' + itemSelector);
                        items.forEach(function(item) {
                            const name = item.getAttribute('data-name');
                            if (name.includes(searchTerm)) {
                                item.classList.remove('d-none');
                            } else {
                                item.classList.add('d-none');
                            }
                        });
                    });
                }
            }

            // Setup search for all fields
            setupSearch('searchCategories', '#categoriesList', '.category-item');
            setupSearch('searchProducts', '#productsList', '.product-item');
            setupSearch('searchExcludedCategories', '#excludedCategoriesList', '.excluded-category-item');
            setupSearch('searchExcludedProducts', '#excludedProductsList', '.excluded-product-item');

            // Setup Select All / Deselect All buttons
            document.getElementById('selectAllCategories')?.addEventListener('click', function() {
                selectAll('#categoriesList', '.category-item');
            });
            document.getElementById('deselectAllCategories')?.addEventListener('click', function() {
                deselectAll('#categoriesList', '.category-item');
            });

            document.getElementById('selectAllProducts')?.addEventListener('click', function() {
                selectAll('#productsList', '.product-item');
            });
            document.getElementById('deselectAllProducts')?.addEventListener('click', function() {
                deselectAll('#productsList', '.product-item');
            });

            document.getElementById('selectAllExcludedCategories')?.addEventListener('click', function() {
                selectAll('#excludedCategoriesList', '.excluded-category-item');
            });
            document.getElementById('deselectAllExcludedCategories')?.addEventListener('click', function() {
                deselectAll('#excludedCategoriesList', '.excluded-category-item');
            });

            document.getElementById('selectAllExcludedProducts')?.addEventListener('click', function() {
                selectAll('#excludedProductsList', '.excluded-product-item');
            });
            document.getElementById('deselectAllExcludedProducts')?.addEventListener('click', function() {
                deselectAll('#excludedProductsList', '.excluded-product-item');
            });

            // Setup Show More buttons
            document.getElementById('showMoreCategories')?.addEventListener('click', function() {
                showMoreItems('#categoriesList', '.category-item', 'categories');
            });
            document.getElementById('showMoreProducts')?.addEventListener('click', function() {
                showMoreItems('#productsList', '.product-item', 'products');
            });
            document.getElementById('showMoreExcludedCategories')?.addEventListener('click', function() {
                showMoreItems('#excludedCategoriesList', '.excluded-category-item', 'excludedCategories');
            });
            document.getElementById('showMoreExcludedProducts')?.addEventListener('click', function() {
                showMoreItems('#excludedProductsList', '.excluded-product-item', 'excludedProducts');
            });
        });
    </script>
@endpush

