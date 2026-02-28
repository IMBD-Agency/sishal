@extends('ecommerce.master')

@section('main-section')
    <section class="featured-categories pb-2 pb-md-3 pt-3 pt-md-5">
        <div class="container container-80 featured-plain">
            <h2 class="section-title text-start mb-0">Our Products</h2>
            
        </div>
    </section>

    <div class="container container-80 py-2 py-md-4">
        <div class="row">
            <!-- Sidebar Filters (hidden on mobile) -->
            <div id="filterFormDesktop" class="col-md-3 mb-4 d-none d-md-block">
                <div class="filter-card">
                    <div class="filter-header">
                        <h5 class="filter-title">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                        <button type="button" class="btn-clear-filters" id="clearFilters">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="filter-section">
                        <div class="filter-section-header" data-bs-toggle="collapse" data-bs-target="#categoryFilter" aria-expanded="true">
                            <h6 class="filter-section-title">
                                <i class="fas fa-tags me-2"></i>Category
                            </h6>
                            <i class="fas fa-chevron-down filter-chevron"></i>
                        </div>
                        <div class="collapse show" id="categoryFilter">
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input class="filter-checkbox" type="checkbox" name="categories[]" id="catAll" value="all" {{ empty($selectedCategories) ? 'checked' : '' }}>
                                    <label class="filter-label" for="catAll">
                                        <span class="checkmark"></span>
                                        <span class="label-text">All Categories</span>
                                    </label>
                                </div>
                                @foreach ($categories as $category)
                                    <div class="category-parent-wrapper">
                                        <div class="filter-option category-parent">
                                            <input class="filter-checkbox" type="checkbox" name="categories[]" id="{{ $category->slug }}"
                                                value="{{ $category->slug }}" {{ in_array($category->slug, $selectedCategories ?? []) ? 'checked' : '' }}>
                                            <label class="filter-label" for="{{ $category->slug }}">
                                                <span class="checkmark"></span>
                                                <span class="label-text">{{ $category->name }}</span>
                                            </label>
                                            @if($category->children->count() > 0)
                                                <div class="category-expand-area" data-target="subcat-{{ $category->id }}" role="button" tabindex="0">
                                                    <span class="category-count-badge">{{ $category->children->count() }}</span>
                                                    <button type="button" class="category-toggle-btn" data-target="subcat-{{ $category->id }}" aria-label="Toggle subcategories">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        @if($category->children->count() > 0)
                                            <div class="category-children collapse" id="subcat-{{ $category->id }}">
                                                @foreach ($category->children as $subcategory)
                                                    <div class="filter-option category-child">
                                                        <input class="filter-checkbox" type="checkbox" name="categories[]" id="{{ $subcategory->slug }}"
                                                            value="{{ $subcategory->slug }}" {{ in_array($subcategory->slug, $selectedCategories ?? []) ? 'checked' : '' }}>
                                                        <label class="filter-label" for="{{ $subcategory->slug }}">
                                                            <span class="checkmark"></span>
                                                            <span class="label-text">{{ $subcategory->name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-section">
                        <div class="filter-section-header" data-bs-toggle="collapse" data-bs-target="#priceFilter" aria-expanded="true">
                            <h6 class="filter-section-title">
                                <i class="fas fa-dollar-sign me-2"></i>Price Range
                            </h6>
                            <i class="fas fa-chevron-down filter-chevron"></i>
                        </div>
                        <div class="collapse show" id="priceFilter">
                            <div class="price-filter-container">
                                <div class="price-inputs">
                                    <div class="price-input-group">
                                        <label class="price-label">Min</label>
                                        <input type="number" class="price-input" id="priceMinInput" 
                                               value="{{ $priceMin }}" min="0" max="{{ $maxProductPrice }}">
                                    </div>
                                    <div class="price-separator">-</div>
                                    <div class="price-input-group">
                                        <label class="price-label">Max</label>
                                        <input type="number" class="price-input" id="priceMaxInput" 
                                               value="{{ $priceMax }}" min="0" max="{{ $maxProductPrice }}">
                                    </div>
                                </div>
                                <div id="price-slider" class="price-slider"></div>
                                <input type="hidden" name="price_min" id="price_min" value="{{ $priceMin }}">
                                <input type="hidden" name="price_max" id="price_max" value="{{ $priceMax }}">
                                <div class="price-display">
                                    <span id="priceMinValue">{{ number_format($priceMin) }}৳</span>
                                    <span class="price-separator">to</span>
                                    <span id="priceMaxValue">{{ number_format($priceMax) }}৳</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div class="filter-section">
                        <div class="filter-section-header" data-bs-toggle="collapse" data-bs-target="#ratingFilter" aria-expanded="true">
                            <h6 class="filter-section-title">
                                <i class="fas fa-star me-2"></i>Customer Rating
                            </h6>
                            <i class="fas fa-chevron-down filter-chevron"></i>
                        </div>
                        <div class="collapse show" id="ratingFilter">
                            <div class="filter-options">
                                @for ($i = 5; $i >= 1; $i--)
                                    <div class="filter-option">
                                        <input class="filter-checkbox" type="checkbox" name="rating[]" id="rating{{ $i }}" value="{{ $i }}" {{ in_array($i, $selectedRatings ?? []) ? 'checked' : '' }}>
                                        <label class="filter-label rating-label" for="rating{{ $i }}">
                                            <span class="checkmark"></span>
                                            <div class="rating-stars">
                                                @for ($j = 1; $j <= 5; $j++)
                                                    <i class="fa{{ $j <= $i ? 's' : 'r' }} fa-star {{ $j <= $i ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                            </div>
                                            <span class="label-text">& up</span>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Product Grid -->
            <div class="col-md-9 col-12">
                <!-- Sticky filter/sort bar for mobile -->
                <div class="sticky-filter-bar d-md-none">
                    <div class="d-flex align-items-center gap-2 w-100">
                        <!-- Mobile filter toggle -->
                        <button class="btn btn-outline-secondary flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <select class="form-select form-select-sm flex-grow-1" name="sort" id="sortSelectMobile">
                            <option value="">Sort By</option>
                            <option value="newest" {{ $selectedSort == 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="lowToHigh" {{ $selectedSort == 'lowToHigh' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="highToLow" {{ $selectedSort == 'highToLow' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                </div>
                <!-- Desktop filter/sort bar -->
                <div class="d-flex flex-column flex-md-row justify-content-end align-items-start align-items-md-center mb-3 gap-2 d-none d-md-flex">
                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                        <select class="form-select form-select-sm flex-grow-1 flex-md-grow-0" style="width:auto;display:inline-block;min-width:140px;" name="sort"
                            id="sortSelect">
                            <option value="">Sort By</option>
                            <option value="newest" {{ $selectedSort == 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="lowToHigh" {{ $selectedSort == 'lowToHigh' ? 'selected' : '' }}>Price: Low to
                                High</option>
                            <option value="highToLow" {{ $selectedSort == 'highToLow' ? 'selected' : '' }}>Price: High to
                                Low</option>
                        </select>
                    </div>
                </div>
                <div id="products-container" class="row g-2 g-md-4 mt-2 mt-md-4" 
                     data-has-more="{{ $products->hasMorePages() ? 'true' : 'false' }}"
                     data-current-page="{{ $products->currentPage() }}">
                    @include('ecommerce.partials.product-grid', ['products' => $products, 'hidePagination' => true])
                </div>
                
                <!-- Load More button for products -->
                <div id="products-load-more-btn" class="text-center py-4" style="display: {{ $products->hasMorePages() ? 'block' : 'none' }};">
                    <button class="load-more-btn" onclick="loadMoreProducts()">
                        <span class="load-more-icon">
                            <i class="fas fa-arrow-down"></i>
                        </span>
                        <span class="load-more-text">Load More Products</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Offcanvas Filters -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filtersOffcanvasLabel"><i class="fas fa-filter me-2"></i>Filters</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div id="filterForm" class="">
                <div class="filter-card">
                    <div class="filter-header">
                        <h5 class="filter-title">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                        <button type="button" class="btn-clear-filters" id="clearFilters">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="filter-section">
                        <div class="filter-section-header" data-bs-toggle="collapse" data-bs-target="#categoryFilterMobile" aria-expanded="true">
                            <h6 class="filter-section-title">
                                <i class="fas fa-tags me-2"></i>Category
                            </h6>
                            <i class="fas fa-chevron-down filter-chevron"></i>
                        </div>
                        <div class="collapse show" id="categoryFilterMobile">
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input class="filter-checkbox" type="checkbox" name="categories[]" id="catAllMobile" value="all" {{ empty($selectedCategories) ? 'checked' : '' }}>
                                    <label class="filter-label" for="catAllMobile">
                                        <span class="checkmark"></span>
                                        <span class="label-text">All Categories</span>
                                    </label>
                                </div>
                                @foreach ($categories as $category)
                                    <div class="category-parent-wrapper">
                                        <div class="filter-option category-parent">
                                            <input class="filter-checkbox" type="checkbox" name="categories[]" id="{{ $category->slug }}-m"
                                                value="{{ $category->slug }}" {{ in_array($category->slug, $selectedCategories ?? []) ? 'checked' : '' }}>
                                            <label class="filter-label" for="{{ $category->slug }}-m">
                                                <span class="checkmark"></span>
                                                <span class="label-text">{{ $category->name }}</span>
                                            </label>
                                            @if($category->children->count() > 0)
                                                <div class="category-expand-area" data-target="subcat-{{ $category->id }}-m" role="button" tabindex="0">
                                                    <span class="category-count-badge">{{ $category->children->count() }}</span>
                                                    <button type="button" class="category-toggle-btn" data-target="subcat-{{ $category->id }}-m" aria-label="Toggle subcategories">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        @if($category->children->count() > 0)
                                            <div class="category-children collapse" id="subcat-{{ $category->id }}-m">
                                                @foreach ($category->children as $subcategory)
                                                    <div class="filter-option category-child">
                                                        <input class="filter-checkbox" type="checkbox" name="categories[]" id="{{ $subcategory->slug }}-m"
                                                            value="{{ $subcategory->slug }}" {{ in_array($subcategory->slug, $selectedCategories ?? []) ? 'checked' : '' }}>
                                                        <label class="filter-label" for="{{ $subcategory->slug }}-m">
                                                            <span class="checkmark"></span>
                                                            <span class="label-text">{{ $subcategory->name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-section">
                        <div class="filter-section-header" data-bs-toggle="collapse" data-bs-target="#priceFilterMobile" aria-expanded="true">
                            <h6 class="filter-section-title">
                                <i class="fas fa-dollar-sign me-2"></i>Price Range
                            </h6>
                            <i class="fas fa-chevron-down filter-chevron"></i>
                        </div>
                        <div class="collapse show" id="priceFilterMobile">
                            <div class="price-filter-container">
                                <div class="price-inputs">
                                    <div class="price-input-group">
                                        <label class="price-label">Min</label>
                                        <input type="number" class="price-input" id="priceMinInputMobile" 
                                               value="{{ $priceMin }}" min="0" max="{{ $maxProductPrice }}">
                                    </div>
                                    <div class="price-separator">-</div>
                                    <div class="price-input-group">
                                        <label class="price-label">Max</label>
                                        <input type="number" class="price-input" id="priceMaxInputMobile" 
                                               value="{{ $priceMax }}" min="0" max="{{ $maxProductPrice }}">
                                    </div>
                                </div>
                                <div id="price-slider-mobile" class="price-slider"></div>
                                <input type="hidden" name="price_min" id="price_min_mobile" value="{{ $priceMin }}">
                                <input type="hidden" name="price_max" id="price_max_mobile" value="{{ $priceMax }}">
                                <div class="price-display">
                                    <span id="priceMinValueMobile">{{ number_format($priceMin) }}৳</span>
                                    <span class="price-separator">to</span>
                                    <span id="priceMaxValueMobile">{{ number_format($priceMax) }}৳</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Filter -->
                    <div class="filter-section">
                        <div class="filter-section-header" data-bs-toggle="collapse" data-bs-target="#ratingFilterMobile" aria-expanded="true">
                            <h6 class="filter-section-title">
                                <i class="fas fa-star me-2"></i>Customer Rating
                            </h6>
                            <i class="fas fa-chevron-down filter-chevron"></i>
                        </div>
                        <div class="collapse show" id="ratingFilterMobile">
                            <div class="filter-options">
                                @for ($i = 5; $i >= 1; $i--)
                                    <div class="filter-option">
                                        <input class="filter-checkbox" type="checkbox" name="rating[]" id="ratingM{{ $i }}" value="{{ $i }}" {{ in_array($i, $selectedRatings ?? []) ? 'checked' : '' }}>
                                        <label class="filter-label rating-label" for="ratingM{{ $i }}">
                                            <span class="checkmark"></span>
                                            <div class="rating-stars">
                                                @for ($j = 1; $j <= 5; $j++)
                                                    <i class="fa{{ $j <= $i ? 's' : 'r' }} fa-star {{ $j <= $i ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                            </div>
                                            <span class="label-text">& up</span>
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>


        // AJAX Filtering Function
        function getActiveFilterRoot(){
            var desktop = document.getElementById('filterFormDesktop');
            var mobile = document.getElementById('filterForm');
            // Prefer the visible container
            if (desktop && desktop.offsetParent !== null) return desktop;
            if (mobile && mobile.offsetParent !== null) return mobile;
            return desktop || mobile;
        }

        // Infinite scroll state
        var infiniteScrollState = {
            currentPage: 1,
            isLoading: false,
            hasMore: true,
            loadingElement: null
        };

        // Create loading indicator element
        function createLoadingIndicator() {
            if (infiniteScrollState.loadingElement) {
                return infiniteScrollState.loadingElement;
            }
            var loadingDiv = document.createElement('div');
            loadingDiv.className = 'col-12 text-center py-4 infinite-scroll-loader';
            loadingDiv.style.display = 'none';
            loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Loading more products...</p>';
            infiniteScrollState.loadingElement = loadingDiv;
            return loadingDiv;
        }

        // Get filter data as URLSearchParams (simpler, works like pagination)
        function getFilterFormData(page) {
            var params = new URLSearchParams();
            var filterRoot = getActiveFilterRoot();
            
            // Get selected categories from checkboxes FIRST (user's active filter choice)
            var selectedCategories = [];
            var allCategorySelected = false;
            
            if (filterRoot) {
                // Check if "All Categories" is selected
                var allCategoryCheckbox = filterRoot.querySelector('#catAll, #catAllMobile');
                if (allCategoryCheckbox && allCategoryCheckbox.checked) {
                    allCategorySelected = true;
                }
                
                // Get other selected categories (excluding "all")
                if (!allCategorySelected) {
                    filterRoot.querySelectorAll('input[type=checkbox][name="categories[]"]:checked').forEach(function(cb) {
                        if (cb.value !== 'all') {
                            selectedCategories.push(cb.value);
                        }
                    });
                }
            }
            
            // Priority logic:
            // 1. If "All Categories" is selected → Don't send any category filter (show all products)
            // 2. If specific categories are selected → Use those
            // 3. Otherwise → Use URL category (from navigation links)
            if (allCategorySelected) {
                // "All Categories" selected - don't send any category filter
                // This will show all products regardless of category
            } else if (selectedCategories.length > 0) {
                // User selected specific categories via checkboxes - use those
                selectedCategories.forEach(function(cat) {
                    params.append('categories[]', cat);
                });
            } else {
                // No checkboxes selected - use URL category if present (from nav links)
                var urlParams = new URLSearchParams(window.location.search);
                var urlCategory = urlParams.get('category');
                if (urlCategory && urlCategory !== 'null' && urlCategory !== '') {
                    params.append('category', urlCategory);
                }
            }
            
            // Get price range
            var priceMinEl = filterRoot ? (filterRoot.querySelector('#price_min') || filterRoot.querySelector('#price_min_mobile')) : null;
            var priceMaxEl = filterRoot ? (filterRoot.querySelector('#price_max') || filterRoot.querySelector('#price_max_mobile')) : null;
            var priceMin = priceMinEl ? priceMinEl.value : '';
            var priceMax = priceMaxEl ? priceMaxEl.value : '';
            if (priceMin) params.append('price_min', priceMin);
            if (priceMax) params.append('price_max', priceMax);
            
            // Get selected ratings
            if (filterRoot) {
                filterRoot.querySelectorAll('input[type=checkbox][name="rating[]"]:checked').forEach(function(cb) {
                    params.append('rating[]', cb.value);
                });
            }
            
            // Get sort option (check both desktop and mobile)
            var sortSelect = document.getElementById('sortSelect');
            var sortSelectMobile = document.getElementById('sortSelectMobile');
            var sortValue = (sortSelect && sortSelect.value) ? sortSelect.value : 
                           (sortSelectMobile && sortSelectMobile.value) ? sortSelectMobile.value : null;
            if (sortValue) {
                params.append('sort', sortValue);
            }
            
            // Add page and infinite scroll
            if (page) params.append('page', page);
            params.append('infinite_scroll', 'true');
            
            return params;
        }

        // Load more products for infinite scroll
        function loadMoreProducts() {
            if (infiniteScrollState.isLoading || !infiniteScrollState.hasMore) {
                console.log('Skipping load - isLoading:', infiniteScrollState.isLoading, 'hasMore:', infiniteScrollState.hasMore);
                return;
            }
            
            infiniteScrollState.isLoading = true;
            var container = document.getElementById('products-container');
            if (!container) {
                console.error('Container not found!');
                infiniteScrollState.isLoading = false;
                return;
            }
            
            // Show loading indicator
            var loadingIndicator = createLoadingIndicator();
            if (!container.contains(loadingIndicator)) {
                container.appendChild(loadingIndicator);
            }
            loadingIndicator.style.display = 'block';
            
            // Show load more button with loading state
            var loadMoreBtn = document.getElementById('products-load-more-btn');
            var loadMoreButton = loadMoreBtn ? loadMoreBtn.querySelector('.load-more-btn') : null;
            
            // Add loading state to button
            if (loadMoreButton) {
                loadMoreButton.classList.add('loading');
                loadMoreButton.disabled = true;
            }
            
            if (loadMoreBtn) {
                loadMoreBtn.style.display = 'block';
            }
            
            // Get next page
            var nextPage = infiniteScrollState.currentPage + 1;
            var params = getFilterFormData(nextPage);
            
            // Try fetch first, fallback to XMLHttpRequest if blocked
            var fetchPromise = fetch('{{ route("products.filter") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                credentials: 'same-origin',
                body: params.toString()
            }).catch(function(error) {
                // If fetch is blocked, try XMLHttpRequest
                console.warn('Fetch blocked, trying XMLHttpRequest fallback:', error);
                return new Promise(function(resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("products.filter") }}', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            resolve({
                                ok: true,
                                status: xhr.status,
                                json: function() { return Promise.resolve(JSON.parse(xhr.responseText)); },
                                text: function() { return Promise.resolve(xhr.responseText); }
                            });
                        } else {
                            reject(new Error('HTTP ' + xhr.status + ': ' + xhr.statusText));
                        }
                    };
                    xhr.onerror = function() {
                        reject(new Error('Network error'));
                    };
                    xhr.send(params.toString());
                });
            });
            
            fetchPromise
            .then(function(response) {
                console.log('Response received, status:', response.status);
                // Check if response is ok (status 200-299)
                if (!response.ok) {
                    return response.json().then(function(err) {
                        throw new Error(err.message || 'An error occurred while loading products');
                    }).catch(function() {
                        throw new Error('Server error. Please try again.');
                    });
                }
                // Try to parse as JSON first
                return response.json().catch(function(error) {
                    // If JSON parsing fails, try to get text to see what we got
                    console.error('Failed to parse JSON, trying text:', error);
                    return response.text().then(function(text) {
                        console.error('Response is not JSON, got:', text.substring(0, 200));
                        // Try to parse as JSON manually if it looks like JSON
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Server returned non-JSON response');
                        }
                    });
                });
            })
            .then(function(data) {
                console.log('Response data received:', {
                    success: data.success,
                    hasHtml: !!data.html,
                    htmlLength: data.html ? data.html.length : 0,
                    count: data.count,
                    total: data.total,
                    hasMore: data.hasMore,
                    currentPage: data.currentPage
                });
                
                loadingIndicator.style.display = 'none';
                infiniteScrollState.isLoading = false;
                
                // Remove loading state from button
                var loadMoreButton = loadMoreBtn ? loadMoreBtn.querySelector('.load-more-btn') : null;
                if (loadMoreButton) {
                    loadMoreButton.classList.remove('loading');
                    loadMoreButton.disabled = false;
                }
                
                // Show/hide load more button based on hasMore
                if (loadMoreBtn && data.hasMore) {
                    loadMoreBtn.style.display = 'block';
                } else if (loadMoreBtn) {
                    loadMoreBtn.style.display = 'none';
                }
                
                if (data.success && data.html) {
                    // Extract product cards from the response HTML
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    // Get all col-* divs that contain product cards (exclude no-products-container)
                    var productContainers = Array.from(tempDiv.querySelectorAll('.col-lg-3, .col-md-6, .col-6, [class*="col-"]'))
                        .filter(function(col) {
                            // Only include if it contains a product-card, not no-products-container
                            return col.querySelector('.product-card') && !col.querySelector('.no-products-container');
                        });
                    
                    if (productContainers.length > 0) {
                        // Get existing product IDs to avoid duplicates
                        var existingProductIds = new Set();
                        container.querySelectorAll('.col-lg-3, .col-md-6, .col-6, [class*="col-"]').forEach(function(col) {
                            var productId = col.querySelector('.wishlist-btn[data-product-id]')?.getAttribute('data-product-id') ||
                                          col.querySelector('.btn-add-cart[data-product-id]')?.getAttribute('data-product-id');
                            if (productId) {
                                existingProductIds.add(productId);
                            }
                        });
                        
                        // Append new products to container (avoid duplicates)
                        var addedCount = 0;
                        productContainers.forEach(function(cardContainer) {
                            var productId = cardContainer.querySelector('.wishlist-btn[data-product-id]')?.getAttribute('data-product-id') ||
                                          cardContainer.querySelector('.btn-add-cart[data-product-id]')?.getAttribute('data-product-id');
                            if (!productId || !existingProductIds.has(productId)) {
                                container.insertBefore(cardContainer, loadingIndicator);
                                if (productId) {
                                    existingProductIds.add(productId);
                                }
                                addedCount++;
                            }
                        });
                        
                        // If no products were added (all duplicates), mark as no more
                        if (addedCount === 0) {
                            infiniteScrollState.hasMore = false;
                            if (container) {
                                container.setAttribute('data-has-more', 'false');
                            }
                            if (loadMoreBtn) {
                                loadMoreBtn.style.display = 'none';
                            }
                            console.log('All products were duplicates, no more to load');
                            return;
                        }
                        
                        // Update state and container data attributes
                        infiniteScrollState.currentPage = nextPage;
                        infiniteScrollState.hasMore = data.hasMore !== undefined ? data.hasMore : false;
                        
                        // Also check if we got fewer products than expected (might be last page)
                        if (productContainers.length < 20) {
                            infiniteScrollState.hasMore = false;
                        }
                        
                        if (container) {
                            container.setAttribute('data-has-more', infiniteScrollState.hasMore ? 'true' : 'false');
                            container.setAttribute('data-current-page', nextPage);
                        }
                        
                        // Show/hide load more button
                        if (loadMoreBtn) {
                            if (infiniteScrollState.hasMore) {
                                loadMoreBtn.style.display = 'block';
                            } else {
                                loadMoreBtn.style.display = 'none';
                            }
                        }
                        
                        console.log('Loaded ' + addedCount + ' new products (total in response: ' + productContainers.length + '). Has more: ' + infiniteScrollState.hasMore);
                    } else {
                        // No products found in response
                        infiniteScrollState.hasMore = false;
                        if (container) {
                            container.setAttribute('data-has-more', 'false');
                        }
                        if (loadMoreBtn) {
                            loadMoreBtn.style.display = 'none';
                        }
                        console.log('No products found in response');
                    }
                } else {
                    // Response was not successful or missing HTML
                    infiniteScrollState.hasMore = false;
                    if (container) {
                        container.setAttribute('data-has-more', 'false');
                    }
                    if (loadMoreBtn) {
                        loadMoreBtn.style.display = 'none';
                    }
                    console.error('Invalid response:', data);
                }
            })
            .catch(function(error) {
                console.error('Load more products error:', error);
                console.error('Error details:', {
                    name: error.name,
                    message: error.message
                });
                
                loadingIndicator.style.display = 'none';
                infiniteScrollState.isLoading = false;
                
                // Remove loading state from button
                var loadMoreBtn = document.getElementById('products-load-more-btn');
                var loadMoreButton = loadMoreBtn ? loadMoreBtn.querySelector('.load-more-btn') : null;
                if (loadMoreButton) {
                    loadMoreButton.classList.remove('loading');
                    loadMoreButton.disabled = false;
                }
                
                // Handle specific error types
                if (error.message && (error.message.includes('404') || error.message.includes('Blocked'))) {
                    infiniteScrollState.hasMore = false;
                    if (container) {
                        container.setAttribute('data-has-more', 'false');
                    }
                    if (loadMoreBtn) {
                        loadMoreBtn.style.display = 'none';
                    }
                    console.log('No more pages available or request blocked');
                } else if (error.message && error.message.includes('Network error')) {
                    // Network error - don't disable, user can retry by scrolling
                    console.warn('Network error, will retry on next scroll');
                } else {
                    // Other errors - disable to prevent infinite retries
                    infiniteScrollState.hasMore = false;
                    if (container) {
                        container.setAttribute('data-has-more', 'false');
                    }
                }
            });
        }

        // Initialize infinite scroll
        function initInfiniteScroll() {
            var container = document.getElementById('products-container');
            if (!container) return;
            
            // Get initial state from data attributes if available
            var hasMoreAttr = container.getAttribute('data-has-more');
            var currentPageAttr = container.getAttribute('data-current-page');
            
            // Reset state
            if (currentPageAttr) {
                infiniteScrollState.currentPage = parseInt(currentPageAttr) || 1;
            } else {
                infiniteScrollState.currentPage = 1;
            }
            infiniteScrollState.isLoading = false;
            
            // Set hasMore from data attribute if available, otherwise check product count
            if (hasMoreAttr !== null) {
                infiniteScrollState.hasMore = hasMoreAttr === 'true';
            } else {
                // Fallback: check if there are pagination links or if we have 20 products
                var hasPagination = container.querySelector('.pagination') !== null;
                var productCount = container.querySelectorAll('.product-card').length;
                infiniteScrollState.hasMore = hasPagination || productCount >= 20;
            }
            
            // Show/hide load more button based on initial state
            var loadMoreBtn = document.getElementById('products-load-more-btn');
            if (loadMoreBtn) {
                if (infiniteScrollState.hasMore) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            }
            
            // Remove existing scroll listener
            if (window.productsScrollHandler) {
                window.removeEventListener('scroll', window.productsScrollHandler);
                window.removeEventListener('touchmove', window.productsScrollHandler);
            }
            
            // Create scroll handler
            window.productsScrollHandler = function() {
                // Only proceed if we have more products and not currently loading
                if (infiniteScrollState.isLoading || !infiniteScrollState.hasMore) {
                    return;
                }
                
                // Check if we're near the bottom of the page
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                var windowHeight = window.innerHeight || document.documentElement.clientHeight;
                var documentHeight = Math.max(
                    document.body.scrollHeight,
                    document.body.offsetHeight,
                    document.documentElement.clientHeight,
                    document.documentElement.scrollHeight,
                    document.documentElement.offsetHeight
                );
                
                // Load more when user is 300px from bottom
                var distanceFromBottom = documentHeight - (scrollTop + windowHeight);
                
                if (distanceFromBottom < 300) {
                    console.log('Triggering load more products - distance from bottom:', distanceFromBottom);
                    loadMoreProducts();
                }
            };
            
            // Add scroll listeners with throttling to improve performance
            var scrollTimeout;
            var lastScrollTop = 0;
            var isScrolling = false;
            var throttledScrollHandler = function() {
                var currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Only process if user scrolled down (not up)
                if (currentScrollTop < lastScrollTop) {
                    lastScrollTop = currentScrollTop;
                    return;
                }
                lastScrollTop = currentScrollTop;
                
                if (scrollTimeout || isScrolling) {
                    return;
                }
                isScrolling = true;
                scrollTimeout = requestAnimationFrame(function() {
                    window.productsScrollHandler();
                    scrollTimeout = null;
                    isScrolling = false;
                });
            };
            
            window.addEventListener('scroll', throttledScrollHandler, { passive: true });
            window.addEventListener('touchmove', throttledScrollHandler, { passive: true });
            
            // Also listen to scrollend if available (better for performance)
            if ('onscrollend' in window) {
                window.addEventListener('scrollend', function() {
                    if (!scrollTimeout) {
                        window.productsScrollHandler();
                    }
                }, { passive: true });
            }
        }

        function applyFilters() {
            var params = getFilterFormData(1);
            var filterRoot = getActiveFilterRoot();
            if (!filterRoot) return;
            
            // Reset infinite scroll state
            infiniteScrollState.currentPage = 1;
            infiniteScrollState.isLoading = false;
            infiniteScrollState.hasMore = true;
            
            // Show loading state
            var container = document.getElementById('products-container');
            if (container) {
                container.innerHTML = '<div class="col-12 text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading products...</p></div>';
            }
            
            // Make AJAX request
            fetch('{{ route("products.filter") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                credentials: 'same-origin',
                body: params.toString()
            })
            .then(response => {
                // Check if response is ok (status 200-299)
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'An error occurred while filtering products');
                    }).catch(() => {
                        throw new Error('Server error. Please try again.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (container) {
                        container.innerHTML = data.html;
                        // Update container data attributes for infinite scroll
                        container.setAttribute('data-has-more', data.hasMore ? 'true' : 'false');
                        container.setAttribute('data-current-page', data.currentPage || 1);
                        
                        
                        // Update infinite scroll state
                        infiniteScrollState.currentPage = data.currentPage || 1;
                        infiniteScrollState.hasMore = data.hasMore || false;
                        // Re-initialize infinite scroll after content update (use requestAnimationFrame for better performance)
                        requestAnimationFrame(function() {
                            initInfiniteScroll();
                        });
                    }
                    
                } else {
                    if (container) {
                        container.innerHTML = '<div class="col-12"><div class="no-products-container"><div class="no-products-icon"><i class="fas fa-search"></i></div><h3 class="no-products-title">No Products Found</h3><p class="no-products-message">We couldn\'t find any products matching your current filters.</p><div class="no-products-suggestion"><i class="fas fa-lightbulb"></i><span>Try adjusting your filters to see more products</span></div></div></div>';
                        container.setAttribute('data-has-more', 'false');
                        container.setAttribute('data-current-page', '1');
                        infiniteScrollState.hasMore = false;
                        infiniteScrollState.currentPage = 1;
                    }
                }
            })
            .catch(error => {
                console.error('Filter error:', error);
                if (container) {
                    container.innerHTML = '<div class="col-12"><div class="no-products-container"><div class="no-products-icon"><i class="fas fa-exclamation-triangle"></i></div><h3 class="no-products-title">Error Loading Products</h3><p class="no-products-message">' + (error.message || 'An error occurred while filtering products. Please try again.') + '</p><div class="no-products-suggestion"><i class="fas fa-lightbulb"></i><span>Please refresh the page or try adjusting your filters</span></div></div></div>';
                    infiniteScrollState.hasMore = false;
                }
            });
        }

        // Enhanced filter functionality
        window.initProductsPage = function() {
            try {
                function initFilterRoot(filterRoot){
                    if (!filterRoot) return;
                    // Initialize price slider - check for both desktop and mobile IDs
                    var priceSlider = filterRoot.querySelector('#price-slider') || filterRoot.querySelector('#price-slider-mobile');
                    if (priceSlider && !priceSlider.noUiSlider && window.noUiSlider) {
                        var minValue = filterRoot.querySelector('#priceMinValue') || filterRoot.querySelector('#priceMinValueMobile');
                        var maxValue = filterRoot.querySelector('#priceMaxValue') || filterRoot.querySelector('#priceMaxValueMobile');
                        var priceMinInput = filterRoot.querySelector('#price_min') || filterRoot.querySelector('#price_min_mobile');
                        var priceMaxInput = filterRoot.querySelector('#price_max') || filterRoot.querySelector('#price_max_mobile');
                        var priceMinDirectInput = filterRoot.querySelector('#priceMinInput') || filterRoot.querySelector('#priceMinInputMobile');
                        var priceMaxDirectInput = filterRoot.querySelector('#priceMaxInput') || filterRoot.querySelector('#priceMaxInputMobile');
                    var maxProductPrice = {{ $maxProductPrice }};
                    
                    window.noUiSlider.create(priceSlider, {
                        start: [parseInt(priceMinInput.value), parseInt(priceMaxInput.value)],
                        connect: true,
                        step: 1,
                        range: { 'min': 0, 'max': maxProductPrice },
                        format: { 
                            to: function(v){ return Math.round(v); }, 
                            from: function(v){ return Number(v); } 
                        }
                    });
                    
                    priceSlider.noUiSlider.on('update', function (values) {
                        var minVal = Math.round(values[0]);
                        var maxVal = Math.round(values[1]);
                        
                        if (minValue) minValue.textContent = `${minVal.toLocaleString()}৳`;
                        if (maxValue) maxValue.textContent = `${maxVal.toLocaleString()}৳`;
                        if (priceMinInput) priceMinInput.value = minVal;
                        if (priceMaxInput) priceMaxInput.value = maxVal;
                        if (priceMinDirectInput) priceMinDirectInput.value = minVal;
                        if (priceMaxDirectInput) priceMaxDirectInput.value = maxVal;
                    });
                    
                    priceSlider.noUiSlider.on('change', function () {
                        // Auto-apply filters on slider change (reduced delay for better performance)
                        setTimeout(function() {
                            applyFilters();
                        }, 200);
                    });
                    }

                // Price input synchronization
                var priceMinDirectInput = filterRoot.querySelector('#priceMinInput') || filterRoot.querySelector('#priceMinInputMobile');
                var priceMaxDirectInput = filterRoot.querySelector('#priceMaxInput') || filterRoot.querySelector('#priceMaxInputMobile');
                
                if (priceMinDirectInput) {
                    priceMinDirectInput.addEventListener('change', function() {
                        var value = Math.max(0, Math.min(parseInt(this.value) || 0, {{ $maxProductPrice }}));
                        this.value = value;
                        var slider = filterRoot.querySelector('#price-slider') || filterRoot.querySelector('#price-slider-mobile');
                        if (slider && slider.noUiSlider) {
                            var currentValues = slider.noUiSlider.get();
                            slider.noUiSlider.set([value, currentValues[1]]);
                        }
                    });
                }
                
                if (priceMaxDirectInput) {
                    priceMaxDirectInput.addEventListener('change', function() {
                        var value = Math.max(0, Math.min(parseInt(this.value) || 0, {{ $maxProductPrice }}));
                        this.value = value;
                        var slider = filterRoot.querySelector('#price-slider') || filterRoot.querySelector('#price-slider-mobile');
                        if (slider && slider.noUiSlider) {
                            var currentValues = slider.noUiSlider.get();
                            slider.noUiSlider.set([currentValues[0], value]);
                        }
                    });
                }

                // Enhanced category checkboxes with "All" logic
                var categoryCheckboxes = filterRoot.querySelectorAll('input[type=checkbox][name="categories[]"]');
                var allCategoryCheckbox = filterRoot.querySelector('#catAll, #catAllMobile');
                
                categoryCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        if (this === allCategoryCheckbox && this.checked) {
                            // Uncheck all other category checkboxes
                            categoryCheckboxes.forEach(function(cb) {
                                if (cb !== allCategoryCheckbox) {
                                    cb.checked = false;
                                }
                            });
                        } else if (this !== allCategoryCheckbox && this.checked) {
                            // Uncheck "All" if a specific category is selected
                            if (allCategoryCheckbox) {
                                allCategoryCheckbox.checked = false;
                            }
                        }
                        
                        // Check if no categories are selected, then check "All"
                        var hasSelectedCategory = Array.from(categoryCheckboxes).some(function(cb) {
                            return cb !== allCategoryCheckbox && cb.checked;
                        });
                        
                        if (!hasSelectedCategory && allCategoryCheckbox) {
                            allCategoryCheckbox.checked = true;
                        }
                        
                        // Auto-apply filters on category change (reduced delay for better performance)
                        setTimeout(function() {
                            applyFilters();
                        }, 150);
                    });
                });

                // Rating checkboxes
                var ratingCheckboxes = filterRoot.querySelectorAll('input[type=checkbox][name="rating[]"]');
                ratingCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        // Auto-apply filters on rating change (reduced delay for better performance)
                        setTimeout(function() {
                            applyFilters();
                        }, 150);
                    });
                });

                // Clear filters functionality
                var clearFiltersBtns = filterRoot.querySelectorAll('#clearFilters, .btn-clear-filters');
                clearFiltersBtns.forEach(function(clearFiltersBtn){
                    clearFiltersBtn.addEventListener('click', function() {
                        // Uncheck all checkboxes
                        filterRoot.querySelectorAll('input[type=checkbox]').forEach(function(cb) {
                            cb.checked = false;
                        });
                        
                        // Check "All" category
                        if (allCategoryCheckbox) {
                            allCategoryCheckbox.checked = true;
                        }
                        
                        // Reset price range
                        var maxProductPrice = {{ $maxProductPrice }};
                        var slider = filterRoot.querySelector('#price-slider') || filterRoot.querySelector('#price-slider-mobile');
                        if (slider && slider.noUiSlider) {
                            slider.noUiSlider.set([0, maxProductPrice]);
                        }
                        
                        // Apply filters with cleared values
                        applyFilters();
                    });
                });

                // Sort select
                var sortSelect = document.getElementById('sortSelect');
                if (sortSelect) {
                    sortSelect.addEventListener('change', function () {
                        applyFilters();
                    });
                    
                    // Also sync mobile sort select
                    var sortSelectMobile = document.getElementById('sortSelectMobile');
                    if (sortSelectMobile) {
                        sortSelectMobile.addEventListener('change', function () {
                            // Sync with desktop select
                            if (sortSelect) {
                                sortSelect.value = this.value;
                            }
                            applyFilters();
                        });
                        
                        // Sync desktop to mobile
                        if (sortSelect) {
                            sortSelect.addEventListener('change', function () {
                                sortSelectMobile.value = this.value;
                            });
                        }
                    }
                }

                // Collapsible filter sections
                var filterHeaders = filterRoot.querySelectorAll('.filter-section-header');
                filterHeaders.forEach(function(header) {
                    header.addEventListener('click', function() {
                        var chevron = this.querySelector('.filter-chevron');
                        if (chevron) {
                            // Use CSS classes instead of direct transform override
                            if (this.getAttribute('aria-expanded') === 'true') {
                                chevron.classList.remove('rotated');
                            } else {
                                chevron.classList.add('rotated');
                            }
                        }
                    });
                });
                
                // Category toggle functionality - make entire expand area clickable
                function toggleCategory(expandArea) {
                    var targetId = expandArea.getAttribute('data-target');
                    var targetElement = filterRoot.querySelector('#' + targetId);
                    var toggleBtn = expandArea.querySelector('.category-toggle-btn');
                    var icon = toggleBtn ? toggleBtn.querySelector('i') : null;
                    
                    if (targetElement) {
                        var isExpanded = !targetElement.classList.contains('collapse');
                        if (isExpanded) {
                            // Collapse
                            targetElement.style.maxHeight = targetElement.scrollHeight + 'px';
                            // Force reflow
                            targetElement.offsetHeight;
                            targetElement.classList.add('collapse');
                            targetElement.style.maxHeight = '0';
                            if (icon) {
                                icon.classList.remove('fa-chevron-down');
                                icon.classList.add('fa-chevron-right');
                            }
                            expandArea.classList.remove('expanded');
                        } else {
                            // Expand
                            targetElement.classList.remove('collapse');
                            // Set initial height to 0, then animate to full height
                            targetElement.style.maxHeight = '0';
                            // Force reflow
                            targetElement.offsetHeight;
                            // Set to full height for animation
                            targetElement.style.maxHeight = targetElement.scrollHeight + 'px';
                            if (icon) {
                                icon.classList.remove('fa-chevron-right');
                                icon.classList.add('fa-chevron-down');
                            }
                            expandArea.classList.add('expanded');
                            
                            // After animation completes, set to auto for dynamic content
                            setTimeout(function() {
                                if (!targetElement.classList.contains('collapse')) {
                                    targetElement.style.maxHeight = 'none';
                                }
                            }, 300);
                        }
                    }
                }
                
                var categoryExpandAreas = filterRoot.querySelectorAll('.category-expand-area');
                categoryExpandAreas.forEach(function(expandArea) {
                    // Remove existing listeners by cloning
                    var newExpandArea = expandArea.cloneNode(true);
                    expandArea.parentNode.replaceChild(newExpandArea, expandArea);
                    
                    // Show toggle button immediately to prevent flash
                    var toggleBtn = newExpandArea.querySelector('.category-toggle-btn');
                    if (toggleBtn) {
                        toggleBtn.classList.add('initialized');
                    }
                    
                    // Click handler for expand area
                    newExpandArea.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleCategory(this);
                    });
                    
                    // Keyboard support
                    newExpandArea.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            e.stopPropagation();
                            toggleCategory(this);
                        }
                    });
                });
                
                // Auto-expand parent categories if any of their children are selected
                var childCheckboxes = filterRoot.querySelectorAll('.category-child input[type=checkbox]');
                var hasExpandedAny = false;
                childCheckboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        var parentWrapper = checkbox.closest('.category-parent-wrapper');
                        if (parentWrapper) {
                            var childrenDiv = parentWrapper.querySelector('.category-children');
                            var expandArea = parentWrapper.querySelector('.category-expand-area');
                            if (childrenDiv && expandArea) {
                                childrenDiv.classList.remove('collapse');
                                // Set max-height immediately without setTimeout to prevent flash
                                requestAnimationFrame(function() {
                                    childrenDiv.style.maxHeight = childrenDiv.scrollHeight + 'px';
                                });
                                var toggleBtn = expandArea.querySelector('.category-toggle-btn');
                                var icon = toggleBtn ? toggleBtn.querySelector('i') : null;
                                if (icon) {
                                    icon.classList.remove('fa-chevron-right');
                                    icon.classList.add('fa-chevron-down');
                                }
                                expandArea.classList.add('expanded');
                                hasExpandedAny = true;
                            }
                        }
                    }
                });
                
                // Initialize max-height for all non-collapsed children (for smooth animations)
                // Use requestAnimationFrame instead of setTimeout for better performance
                requestAnimationFrame(function() {
                    var allChildrenDivs = filterRoot.querySelectorAll('.category-children:not(.collapse)');
                    allChildrenDivs.forEach(function(childrenDiv) {
                        if (!childrenDiv.style.maxHeight || childrenDiv.style.maxHeight === '0px') {
                            childrenDiv.style.maxHeight = childrenDiv.scrollHeight + 'px';
                        }
                    });
                    // Show all category toggle buttons after initialization to prevent flash
                    var allToggleBtns = filterRoot.querySelectorAll('.category-toggle-btn');
                    allToggleBtns.forEach(function(btn) {
                        btn.classList.add('initialized');
                    });
                });
                
                // Highlight parent categories that have selected children
                function updateParentCategoryHighlight() {
                    var parentWrappers = filterRoot.querySelectorAll('.category-parent-wrapper');
                    parentWrappers.forEach(function(wrapper) {
                        var childCheckboxes = wrapper.querySelectorAll('.category-child input[type=checkbox]');
                        var hasSelectedChild = Array.from(childCheckboxes).some(function(cb) { return cb.checked; });
                        var parentOption = wrapper.querySelector('.category-parent');
                        if (hasSelectedChild) {
                            parentOption.classList.add('has-selected-child');
                        } else {
                            parentOption.classList.remove('has-selected-child');
                        }
                    });
                }
                
                // Update highlight on checkbox changes
                var allCategoryCheckboxes = filterRoot.querySelectorAll('.category-parent-wrapper input[type=checkbox]');
                allCategoryCheckboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        setTimeout(updateParentCategoryHighlight, 100);
                    });
                });
                
                // Initial highlight update
                updateParentCategoryHighlight();
                }
                // Initialize both desktop and mobile filter containers (if present)
                initFilterRoot(document.getElementById('filterFormDesktop'));
                initFilterRoot(document.getElementById('filterForm'));
                
                
                // Initialize infinite scroll instead of pagination
                initInfiniteScroll();
                
                // Also check if page is already scrolled near bottom (for short pages)
                // Use requestAnimationFrame for better performance
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                        var windowHeight = window.innerHeight || document.documentElement.clientHeight;
                        var documentHeight = Math.max(
                            document.body.scrollHeight,
                            document.body.offsetHeight,
                            document.documentElement.clientHeight,
                            document.documentElement.scrollHeight,
                            document.documentElement.offsetHeight
                        );
                        var distanceFromBottom = documentHeight - (scrollTop + windowHeight);
                        
                        // If page is short and we're near bottom, try loading more
                        if (distanceFromBottom < 500 && infiniteScrollState.hasMore && !infiniteScrollState.isLoading) {
                            console.log('Products page is short, checking if we need to load more products');
                            loadMoreProducts();
                        }
                    });
                });
                
                // Also trigger a check after a short delay to catch any initial scroll position
                // Reduced delay for better performance
                setTimeout(function() {
                    if (window.productsScrollHandler) {
                        window.productsScrollHandler();
                    }
                }, 500);
                
                // Minimal: no extra sync logic necessary
            } catch(error) {
                console.error('Error initializing products page:', error);
            }
        };

        // Initialize on first load and after AJAX injections
        document.addEventListener('DOMContentLoaded', function(){ if (typeof window.initProductsPage === 'function') window.initProductsPage(); });
        window.addEventListener('pageshow', function(){ if (typeof window.initProductsPage === 'function') window.initProductsPage(); });

        // Remove any existing cart event listeners to prevent duplicates
        if (window.__productsCartEventListener) {
            document.removeEventListener('click', window.__productsCartEventListener);
        }

        // Cart functionality is now handled by global cart handler in master.blade.php
        // No need for duplicate event listeners here
        document.addEventListener('click', function(e){
            var btn = e.target && e.target.closest('.wishlist-btn');
            if (!btn) return;
            e.preventDefault();
            var productId = btn.getAttribute('data-product-id');
            if (!window.jQuery) return;
            window.jQuery.ajax({
                url: '/add-remove-wishlist/' + productId,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                success: function (response) {
                    if (response.success) {
                        btn.classList.toggle('active');
                        showToast(response.message, 'success');
                    }
                }
            });
        });
    </script>
    <style>
        .custom-toast {
            min-width: 220px;
            max-width: 340px;
            background: #fff;
            color: #222;
            padding: 0;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            font-size: 16px;
            opacity: 1;
            transition: opacity 0.4s, transform 0.4s;
            margin-left: auto;
            margin-right: 0;
            pointer-events: auto;
            z-index: 16000;
            overflow: hidden;
            border-left: 5px solid #2196F3;
            position: relative;
        }

        .custom-toast.error {
            border-left-color: #e53935;
        }

        .custom-toast .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px 14px 16px;
        }

        .custom-toast .toast-icon {
            font-size: 22px;
            flex-shrink: 0;
        }

        .custom-toast .toast-message {
            flex: 1;
            font-weight: 500;
        }

        .custom-toast .toast-close {
            background: none;
            border: none;
            color: #888;
            font-size: 22px;
            cursor: pointer;
            margin-left: 8px;
            transition: color 0.2s;
        }

        .custom-toast .toast-close:hover {
            color: #e53935;
        }

        .custom-toast .toast-progress {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 100%;
            background: linear-gradient(90deg, #2196F3, #21cbf3);
            transition: width 2.3s linear;
        }

        .custom-toast.error .toast-progress {
            background: linear-gradient(90deg, #e53935, #ffb199);
        }

        .custom-toast.hide {
            opacity: 0;
            transform: translateY(-20px) scale(0.98);
        }

        /* Load more button - Green theme, transparent, with loading vibe */
        .load-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 24px;
            border-radius: 25px;
            border: 2px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
            font-weight: 500;
            font-size: 14px;
            background: rgba(34, 197, 94, 0.08);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .load-more-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .load-more-btn:hover {
            background: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }

        .load-more-btn:hover::before {
            left: 100%;
        }

        .load-more-btn:active {
            transform: translateY(0);
        }

        .load-more-btn.loading {
            pointer-events: none;
        }

        .load-more-btn.loading .load-more-icon {
            animation: spin 1s linear infinite;
        }

        .load-more-btn.loading .load-more-text::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        .load-more-btn .load-more-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .load-more-btn .load-more-text {
            position: relative;
        }

        .load-more-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Filter chevron rotation */
        .filter-chevron {
            transition: transform 0.3s ease;
        }
        .filter-chevron.rotated {
            transform: rotate(180deg);
        }

        /* Sticky filter/sort bar for mobile */
        .sticky-filter-bar {
            position: sticky;
            top: 72px; /* keep below fixed header */
            z-index: 100;
            background: #fff;
            padding: 12px 0;
            margin-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
            left: 0;
            right: 0;
        }

        @media (max-width: 767.98px) {
            .sticky-filter-bar {
                margin-left: -15px;
                margin-right: -15px;
                padding-left: 15px;
                padding-right: 15px;
            }
        }

        /* No Products Found Styles */
        .no-products-container {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #F3F0FF 0%, #E3F2FD 100%);
            border-radius: 16px;
            margin: 20px 0;
            border: 1px solid #E3F2FD;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.1);
        }

        .no-products-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8B5CF6 0%, #00512C 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
        }

        .no-products-icon i {
            font-size: 32px;
            color: white;
        }

        .no-products-title {
            font-size: 28px;
            font-weight: 700;
            color: #00512C;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .no-products-message {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 24px;
            line-height: 1.5;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .no-products-suggestion {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 12px 20px;
            border-radius: 25px;
            border: 1px solid #E3F2FD;
            box-shadow: 0 2px 10px rgba(0, 81, 44, 0.1);
            font-size: 14px;
            color: #00512C;
            font-weight: 500;
        }

        .no-products-suggestion i {
            color: #FCD34D;
            font-size: 16px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .no-products-container {
                padding: 40px 15px;
                margin: 15px 0;
            }

            .no-products-icon {
                width: 60px;
                height: 60px;
                margin-bottom: 20px;
            }

            .no-products-icon i {
                font-size: 24px;
            }

            .no-products-title {
                font-size: 24px;
                margin-bottom: 12px;
            }

            .no-products-message {
                font-size: 14px;
                margin-bottom: 20px;
            }

            .no-products-suggestion {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
        
        /* Offcanvas width - leave space for backdrop */
        #filtersOffcanvas.offcanvas-start {
            width: 85%;
            max-width: 320px;
        }
        
        @media (max-width: 576px) {
            #filtersOffcanvas.offcanvas-start {
                width: 80%;
                max-width: 300px;
            }
        }
        
        /* Category Hierarchy Styles */
        .category-parent-wrapper {
            margin-bottom: 6px;
        }
        
        .category-parent {
            position: relative;
            display: flex;
            align-items: center;
            transition: background-color 0.2s ease;
            border-radius: 6px;
            padding: 2px 0;
        }
        
        .category-parent:hover {
            background-color: rgba(0, 81, 44, 0.03);
        }
        
        .category-parent.has-selected-child {
            background-color: rgba(0, 81, 44, 0.08);
        }
        
        .category-parent.has-selected-child .label-text {
            font-weight: 600;
            color: #00512C;
        }
        
        .category-expand-area {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
            user-select: none;
            -webkit-user-select: none;
        }
        
        .category-expand-area:hover {
            background-color: rgba(0, 81, 44, 0.1);
        }
        
        .category-expand-area:focus {
            outline: 2px solid #00512C;
            outline-offset: 2px;
        }
        
        .category-expand-area.expanded {
            background-color: rgba(0, 81, 44, 0.08);
        }
        
        .category-count-badge {
            background: linear-gradient(135deg, #00512C 0%, #008751 100%);
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            line-height: 1.4;
        }
        
        .category-toggle-btn {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s, transform 0.2s, opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            opacity: 0;
            visibility: hidden;
        }
        
        /* Show toggle button after initialization to prevent flash */
        .category-toggle-btn.initialized {
            opacity: 1;
            visibility: visible;
        }
        
        .category-toggle-btn:hover {
            color: #00512C;
        }
        
        .category-toggle-btn:focus {
            outline: none;
        }
        
        .category-toggle-btn i {
            font-size: 11px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .category-toggle-btn i.fa-chevron-down {
            transform: rotate(90deg);
        }
        
        .category-children {
            margin-left: 24px;
            margin-top: 6px;
            padding-left: 12px;
            border-left: 2px solid #e0e7ef;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), margin-top 0.3s ease, padding-top 0.3s ease, padding-bottom 0.3s ease;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        .category-children.collapse {
            max-height: 0 !important;
            margin-top: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        .category-children:not(.collapse) {
            padding-top: 4px;
            padding-bottom: 4px;
        }
        
        .category-child {
            margin-bottom: 4px;
            padding-left: 4px;
            transition: background-color 0.2s ease;
            border-radius: 4px;
        }
        
        .category-child:hover {
            background-color: rgba(0, 81, 44, 0.04);
        }
        
        .category-child .label-text {
            font-size: 14px;
            color: #495057;
            font-weight: 400;
        }
        
        .category-child input[type=checkbox]:checked + .filter-label .label-text {
            color: #00512C;
            font-weight: 500;
        }
        
        /* Smooth animation for expanding/collapsing */
        @media (prefers-reduced-motion: no-preference) {
            .category-children {
                transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                           margin-top 0.3s ease, 
                           padding-top 0.3s ease, 
                           padding-bottom 0.3s ease,
                           opacity 0.2s ease;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            .category-children {
                transition: none;
            }
        }
        
    </style>
@endpush