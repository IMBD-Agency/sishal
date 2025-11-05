@extends('erp.master')

@section('title', 'Edit Shipping Method')

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
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-medium">
                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>Available Cities
                                        </label>
                                        <small class="d-block text-muted mb-2">Select cities where this shipping method is available. Leave empty to make it available for all cities.</small>
                                        
                                        <!-- City Search Box -->
                                        <div class="mb-3">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="city_search_filter" 
                                                   placeholder="Search cities by name, state, or country...">
                                            <div class="d-flex gap-2 mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCities()">
                                                    <i class="fas fa-check-square"></i> Select All Visible
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllCities()">
                                                    <i class="fas fa-square"></i> Deselect All
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="city-selection-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem;">
                                            @foreach($cities as $city)
                                            <div class="form-check mb-2 city-item" data-city-name="{{ strtolower($city->name) }}" data-city-state="{{ strtolower($city->state ?? '') }}" data-city-country="{{ strtolower($city->country ?? '') }}">
                                                <input class="form-check-input city-checkbox" type="checkbox" name="cities[]" 
                                                       value="{{ $city->id }}" id="city_{{ $city->id }}"
                                                       {{ in_array($city->id, old('cities', $shippingMethod->cities->pluck('id')->toArray())) ? 'checked' : '' }}
                                                       onchange="toggleCityCost({{ $city->id }})">
                                                <label class="form-check-label w-100" for="city_{{ $city->id }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>{{ $city->display_name }}</span>
                                                        <div class="city-cost-input-wrapper" id="cost_wrapper_{{ $city->id }}" style="display: none; width: 150px;">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" 
                                                                       class="form-control city-cost-input" 
                                                                       name="city_costs[{{ $city->id }}]" 
                                                                       id="city_cost_{{ $city->id }}"
                                                                       placeholder="Override cost" 
                                                                       step="0.01" 
                                                                       min="0"
                                                                       value="{{ old("city_costs.{$city->id}", $shippingMethod->cities->where('id', $city->id)->first()->pivot->cost_override ?? '') }}">
                                                                <span class="input-group-text">৳</span>
                                                            </div>
                                                            <small class="text-muted">Leave empty to use default cost</small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle"></i> 
                                            <span id="selected_cities_count">0</span> city(s) selected. 
                                            <span id="visible_cities_count">{{ count($cities) }}</span> visible.
                                        </small>
                                        @error('cities')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
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

    <script>
        function toggleCityCost(cityId) {
            const checkbox = document.getElementById('city_' + cityId);
            const costWrapper = document.getElementById('cost_wrapper_' + cityId);
            if (checkbox.checked) {
                costWrapper.style.display = 'block';
            } else {
                costWrapper.style.display = 'none';
                document.getElementById('city_cost_' + cityId).value = '';
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selected = document.querySelectorAll('.city-checkbox:checked').length;
            const visible = document.querySelectorAll('.city-item:not([style*="display: none"])').length;
            document.getElementById('selected_cities_count').textContent = selected;
            document.getElementById('visible_cities_count').textContent = visible;
        }

        function selectAllCities() {
            document.querySelectorAll('.city-item:not([style*="display: none"]) .city-checkbox').forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    toggleCityCost(checkbox.value);
                }
            });
        }

        function deselectAllCities() {
            document.querySelectorAll('.city-checkbox').forEach(checkbox => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    toggleCityCost(checkbox.value);
                }
            });
        }

        // City search filter
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('city_search_filter');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    const cityItems = document.querySelectorAll('.city-item');
                    
                    cityItems.forEach(item => {
                        const cityName = item.getAttribute('data-city-name');
                        const cityState = item.getAttribute('data-city-state');
                        const cityCountry = item.getAttribute('data-city-country');
                        
                        if (searchTerm === '' || 
                            cityName.includes(searchTerm) || 
                            cityState.includes(searchTerm) || 
                            cityCountry.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    updateSelectedCount();
                });
            }

            // Initialize on page load
            document.querySelectorAll('.city-checkbox').forEach(checkbox => {
                const cityId = checkbox.value;
                toggleCityCost(cityId);
            });
            
            updateSelectedCount();
        });
    </script>
@endsection
