@extends('erp.master')

@section('title', 'Point of Sale')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Section -->
        <div class="container-fluid px-4 py-3 bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-cash-register me-2"></i>Point of Sale
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-outline-primary me-2" onclick="clearCart()">
                        <i class="fas fa-trash-alt me-1"></i>Clear Cart
                    </button>
                    <button class="btn btn-success" onclick="processPayment()">
                        <i class="fas fa-credit-card me-1"></i>Process Payment
                    </button>
                </div>
            </div>
        </div>

        <!-- Main POS Content -->
        <div class="container-fluid px-4 py-4">
            <div class="row">
                <!-- Products Section -->
                <div class="col-lg-8">
                    <!-- Search and Filter -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white" style="padding: 10px;">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0"
                                            placeholder="Search products..." id="searchInput">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white" style="padding: 10px;">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" 
                                            placeholder="Scan barcode or enter Style No..." 
                                            id="barcodeInput" 
                                            autocomplete="off">
                                    </div>
                                    <div id="barcodeErrorMessage" class="mt-2" style="display: none;">
                                        <div class="alert alert-danger d-flex align-items-center mb-0 shadow-sm" style="border-radius: 8px; padding: 12px 16px;">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            <span id="barcodeErrorText"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <select class="form-select" id="categoryFilter">
                                                <option value="">Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-select" id="sortBy">
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="row" id="productsGrid">
                        <!-- Product Cards will be populated here -->
                    </div>
                </div>

                <!-- Cart Section -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 120px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="cart-items" id="cartItems" style="max-height: 400px; overflow-y: auto;">
                                <div class="text-center py-5 text-muted" id="emptyCart">
                                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                    <p>Your cart is empty</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold text-primary h5 mb-0" id="cartTotal">0.00৳</span>
                            </div>
                            <button class="btn btn-success w-100" id="openCheckoutDrawer">
                                <i class="fas fa-credit-card me-2"></i>Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="" alt="" class="img-fluid rounded" id="modalProductImage">
                        </div>
                        <div class="col-md-6">
                            <h4 id="modalProductName" class="mb-2"></h4>
                            <p class="text-muted mb-3" id="modalProductCategory"></p>
                            <div class="mb-3">
                                <h6 class="mb-2"><i class="fas fa-info-circle me-1"></i>Description:</h6>
                                <p id="modalProductDescription" class="border rounded p-3 bg-light"></p>
                            </div>
                            <div class="mb-3">
                                <h5 class="text-primary mb-0">
                                    <span id="modalDiscountPrice"></span>
                                    <span style="font-weight: 400; color: #6c757d; margin-left: 8px;">
                                        <del id="modalProductPrice"></del>
                                    </span>
                                </h5>
                            </div>
                            <div class="mb-3 d-none" id="variationWrapper">
                                <label class="form-label fw-semibold mb-2">
                                    <i class="fas fa-tags me-1"></i>Select Size/Variation
                                </label>
                                <div id="variationButtons" class="d-flex flex-wrap gap-2 mb-2">
                                    <!-- Variation buttons will be populated here -->
                                </div>
                                <div class="form-text mt-2" id="variationStockInfo"></div>
                            </div>
                            <div class="input-group mb-3" style="width: 150px;">
                                <button class="btn btn-outline-secondary" onclick="decrementQuantity()">-</button>
                                <input type="number" class="form-control text-center" value="1" min="1" id="modalQuantity">
                                <button class="btn btn-outline-secondary" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="modalAddToCartBtn" onclick="addToCartFromModal()">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('erp.pos.components.checkout-drawer')

    <style>
        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid #e0e0e0;
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .product-card.out-of-stock {
            opacity: 0.7;
            filter: grayscale(0.3);
        }

        .product-card.out-of-stock:hover {
            transform: translateY(-4px);
        }

        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .product-image {
            height: 220px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .discount-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .stock-badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 12px;
        }

        .out-of-stock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .out-of-stock-overlay i {
            font-size: 3rem;
            color: #fff;
        }

        .product-card .card-body {
            display: flex;
            flex-direction: column;
            padding: 1.25rem;
            flex-grow: 1;
        }

        .product-header {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .product-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2d3748;
            line-height: 1.4;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8rem;
        }

        .product-category-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-block;
            width: fit-content;
        }

        .product-description {
            color: #718096;
            font-size: 0.85rem;
            line-height: 1.5;
            margin: 0.5rem 0;
            height: 2.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-footer {
            margin-top: auto;
        }

        .price-container {
            display: flex;
            align-items: baseline;
            gap: 10px;
            flex-wrap: wrap;
        }

        .current-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #28a745;
            line-height: 1;
        }

        .original-price {
            font-size: 1rem;
            color: #a0aec0;
            text-decoration: line-through;
            font-weight: 500;
        }

        .product-meta {
            padding: 0.5rem 0;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .sku-text {
            font-size: 0.75rem;
            color: #718096;
            font-weight: 500;
        }

        .add-to-cart-btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 10px;
            transition: all 0.2s ease;
            border: none;
            background-color: #007bff;
            color: #fff;
        }

        .add-to-cart-btn:hover:not(:disabled) {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }

        .add-to-cart-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .add-to-cart-btn i {
            font-size: 0.9rem;
        }

        .add-to-cart-btn i {
            font-size: 0.9rem;
        }

        #modalProductDescription {
            color: #495057;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
        }

        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 15px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            background: #e9ecef;
        }

        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .select2-container--open {
            z-index: 3000 !important;
        }

        .select2-selection {
            border: 2px solid #e5e7eb;
            padding: 16px 16px 16px 48px;
            height: 100% !important;
            border-radius: 12px;
            font-size: 14px;
        }

        .variation-btn {
            min-width: 60px;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .variation-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .variation-btn.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Global variables
        let products = [];
        let cart = [];
        let selectedProduct = null;
        let currentBranchId = null;

        // Helper function to strip HTML tags and decode HTML entities
        function stripHtmlTags(html) {
            if (!html) return '';
            // Create a temporary div element
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            // Get text content which automatically strips HTML tags
            return tmp.textContent || tmp.innerText || '';
        }

        // Initialize the page
        $(document).ready(function () {
            // Clear cartItems from sessionStorage after successful POS sale (do this FIRST)
            if ($('.alert-success').length > 0) {
                sessionStorage.setItem('cartItems', '[]');
            }
            $('#drawerBranchSelect').val(currentBranchId);
            
            setupEventListeners();
            // Set initial branch ID from the first option
            const branchSelect = $('#sortBy');
            if (branchSelect.val()) {
                currentBranchId = branchSelect.val();
                loadProductsFromAPI();
                $('#drawerBranchSelect').val(currentBranchId);
            }
            const storedCart = sessionStorage.getItem('cartItems');
            if (storedCart) {
                try {
                    cart = JSON.parse(storedCart);
                    updateCartDisplay();
                } catch (e) {
                    cart = [];
                }
            }
            if ($('#productPagination').length === 0) {
                $('#productsGrid').after('<div id="productPagination"></div>');
            }
        });

        function setupEventListeners() {
            $('#searchInput').on('input', debounce(filterProducts, 300));
            $('#categoryFilter').on('change', filterProducts);
            $('#sortBy').on('change', function () {
                // Set cartItems in sessionStorage to an empty array when branch is changed
                sessionStorage.clear();
                currentBranchId = $(this).val();
                loadProductsFromAPI();
                clearCart()
            });
            
            // Barcode scanner handler
            let barcodeTimeout;
            let barcodeValue = '';
            
            $('#barcodeInput').on('keypress', function(e) {
                // Clear timeout on each keypress
                clearTimeout(barcodeTimeout);
                
                // If Enter key is pressed, process the barcode immediately
                if (e.which === 13) {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (barcode) {
                        scanBarcode(barcode);
                        $(this).val('');
                    }
                    return;
                }
                
                // Accumulate barcode value
                barcodeValue = $(this).val();
                
                // Set timeout - if no keypress for 800ms, assume barcode scan is complete
                // Only auto-trigger if barcode is 6+ characters (to avoid premature searches during manual typing)
                // Barcode scanners typically send data very quickly, so they'll trigger this
                barcodeTimeout = setTimeout(function() {
                    if (barcodeValue && barcodeValue.length >= 6) {
                        scanBarcode(barcodeValue);
                        $('#barcodeInput').val('');
                    }
                }, 800);
            });
            
            // Also handle paste events for manual entry
            $('#barcodeInput').on('paste', function(e) {
                setTimeout(function() {
                    const barcode = $('#barcodeInput').val().trim();
                    if (barcode) {
                        scanBarcode(barcode);
                        $('#barcodeInput').val('');
                    }
                }, 10);
            });
        }
        
        // Function to show barcode error message
        function showBarcodeError(message) {
            const $errorDiv = $('#barcodeErrorMessage');
            const $errorText = $('#barcodeErrorText');
            $errorText.text(message);
            $errorDiv.slideDown(300);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $errorDiv.slideUp(300);
            }, 5000);
        }
        
        // Function to hide barcode error message
        function hideBarcodeError() {
            $('#barcodeErrorMessage').slideUp(300);
        }
        
        // Function to scan barcode and add product to cart
        function scanBarcode(barcode) {
            if (!currentBranchId) {
                showToast('Please select a branch first', 'warning');
                return;
            }
            
            if (!barcode || barcode.length < 1) {
                return;
            }
            
            // Hide any previous error
            hideBarcodeError();
            
            // Show loading indicator
            const $barcodeInput = $('#barcodeInput');
            $barcodeInput.prop('disabled', true);
            $barcodeInput.css('background-color', '#f0f0f0');
            
            // Search for product by barcode/SKU
            $.ajax({
                url: `/erp/products/find-by-barcode/${currentBranchId}`,
                method: 'POST',
                data: {
                    barcode: barcode,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.product) {
                        const product = response.product;
                        
                        // If it's a variation product with selected variation
                        if (response.type === 'variation' && response.variation_id) {
                            // Load variations and select the specific one
                            loadProductVariationsForBarcode(product.id, response.variation_id, product);
                        } else {
                            // Regular product or variation product without specific variation
                            // Check if product has variations
                            if (product.has_variations) {
                                showToast('Product has variations. Please select a variation.', 'info');
                                // Show product modal to select variation
                                showProductModal(product.id);
                            } else {
                                // Add directly to cart
                                addToCart(product.id, 1);
                                showToast(`${product.name} added to cart`, 'success');
                            }
                        }
                    } else {
                        showBarcodeError(`Product not found with barcode/Style No: "${barcode}"`);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 404) {
                        showBarcodeError(`Product not found with barcode/Style No: "${barcode}"`);
                    } else {
                        showBarcodeError('Error scanning barcode. Please try again.');
                    }
                },
                complete: function() {
                    // Re-enable input
                    $barcodeInput.prop('disabled', false);
                    $barcodeInput.css('background-color', '');
                    $barcodeInput.focus();
                }
            });
        }
        
        // Load variations and auto-select the one from barcode
        function loadProductVariationsForBarcode(productId, variationId, product) {
            // First, get the variation stock information
            $.get(`/erp/pos/product/${productId}/branch/${currentBranchId}/stock/${variationId}`, function(stockResponse) {
                let stockForBranch = 0;
                
                if (stockResponse && stockResponse.success) {
                    stockForBranch = stockResponse.quantity || 0;
                }
                
                // Now get variation details
                $.get(`/erp/products/${productId}/variations-list`, function(variations) {
                    if (variations && variations.length > 0) {
                        // Find the variation that matches the barcode
                        const variation = variations.find(v => v.id === variationId);
                        if (variation) {
                            // Set selected variation with stock info
                            selectedProduct = { ...product, selectedVariation: null, variations: variations };
                            selectedProduct.selectedVariation = {
                                id: variation.id,
                                price: variation.price || product.price,
                                stockForBranch: stockForBranch,
                                display_name: variation.display_name || variation.name,
                            };
                            
                            // Add to cart with the specific variation
                            addToCart(productId, 1);
                            showToast(`${product.name} (${variation.display_name || variation.name}) added to cart`, 'success');
                        } else {
                            showToast('Variation not found', 'error');
                        }
                    } else {
                        showToast('Product variations not found', 'error');
                    }
                }).fail(function() {
                    showToast('Error loading product variations', 'error');
                });
            }).fail(function() {
                // If stock API fails, still try to add with 0 stock
                $.get(`/erp/products/${productId}/variations-list`, function(variations) {
                    if (variations && variations.length > 0) {
                        const variation = variations.find(v => v.id === variationId);
                        if (variation) {
                            selectedProduct = { ...product, selectedVariation: null, variations: variations };
                            selectedProduct.selectedVariation = {
                                id: variation.id,
                                price: variation.price || product.price,
                                stockForBranch: product.selected_variation ? product.selected_variation.stock : 0,
                                display_name: variation.display_name || variation.name,
                            };
                            addToCart(productId, 1);
                            showToast(`${product.name} (${variation.display_name || variation.name}) added to cart`, 'success');
                        }
                    }
                });
            });
        }

        // Load products from API based on selected branch
        function loadProductsFromAPI(page = 1) {
            if (!currentBranchId) return;

            const searchTerm = $('#searchInput').val();
            const categoryId = $('#categoryFilter').val();

            // Show loading state
            const $grid = $('#productsGrid');
            $grid.html('<div class="col-12 text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading products...</p></div>');

            // Build query parameters
            const params = new URLSearchParams();
            if (searchTerm) params.append('search', searchTerm);
            if (categoryId) params.append('category_id', categoryId);
            if (page) params.append('page', page);

            // Make API call using jQuery AJAX
            $.ajax({
                url: `/erp/products/search-with-filters/${currentBranchId}?${params.toString()}`,
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    // If paginated (search), data will have .data and meta
                    if (data.data && data.current_page) {
                        products = data.data;
                        renderProducts(products, {
                            current_page: data.current_page,
                            last_page: data.last_page
                        });
                    } else {
                        products = data;
                        renderProducts(products);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading products:', error);
                    $grid.html('<div class="col-12 text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-2x"></i><p class="mt-2">Error loading products</p></div>');
                }
            });
        }

        // Debounce function to limit API calls
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function renderProducts(productsToRender = products, pagination = null) {
            const $grid = $('#productsGrid');
            $grid.empty();

            productsToRender.forEach(product => {
                const $productCard = createProductCard(product);
                $grid.append($productCard);
            });

            // Render pagination controls if pagination meta is present
            $('#productPagination').remove();
            if (pagination) {
                const $pagination = $('<div id="productPagination" class="d-flex justify-content-center my-3"></div>');
                const prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
                const nextDisabled = pagination.current_page === pagination.last_page ? 'disabled' : '';
                $pagination.append(`<button class="btn btn-outline-primary mx-1" id="prevPageBtn" ${prevDisabled}>Prev</button>`);
                $pagination.append(`<span class="mx-2 align-self-center">Page ${pagination.current_page} of ${pagination.last_page}</span>`);
                $pagination.append(`<button class="btn btn-outline-primary mx-1" id="nextPageBtn" ${nextDisabled}>Next</button>`);
                $grid.after($pagination);

                // Pagination button handlers
                $('#prevPageBtn').off('click').on('click', function () {
                    if (pagination.current_page > 1) {
                        loadProductsFromAPI(pagination.current_page - 1);
                    }
                });
                $('#nextPageBtn').off('click').on('click', function () {
                    if (pagination.current_page < pagination.last_page) {
                        loadProductsFromAPI(pagination.current_page + 1);
                    }
                });
            }
        }

        function createProductCard(product) {
            const $col = $('<div>').addClass('col-md-6 col-lg-4 mb-4');

            // Handle image path
            const imageSrc = product.image ? `/${product.image}` : 'https://via.placeholder.com/300x200?text=No+Image';

            // Handle category name
            const categoryName = product.category ? product.category.name : 'Uncategorized';

            const isVariationProduct = !!product.has_variations;

            // Handle stock information with better styling and warnings
            const stockQuantity = product.branch_stock ? product.branch_stock.quantity : 0;
            const isInStock = stockQuantity > 0;
            let stockBadge = '';
            if (isInStock) {
                if (stockQuantity <= 5) {
                    stockBadge = `<span class="badge bg-warning stock-badge">
                        <i class="fas fa-exclamation-triangle me-1"></i>Low Stock (${stockQuantity})
                    </span>`;
                } else {
                    stockBadge = `<span class="badge bg-success stock-badge">
                        <i class="fas fa-check-circle me-1"></i>In Stock (${stockQuantity})
                    </span>`;
                }
            } else {
                stockBadge = `<span class="badge bg-danger stock-badge">
                    <i class="fas fa-times-circle me-1"></i>Out of Stock
                </span>`;
            }

            // Calculate discount percentage
            const hasDiscount = product.discount && product.discount < product.price;
            const discountPercent = hasDiscount ? Math.round(((product.price - product.discount) / product.price) * 100) : 0;
            const discountBadge = hasDiscount ? 
                `<span class="badge bg-danger discount-badge">
                    <i class="fas fa-tag me-1"></i>${discountPercent}% OFF
                </span>` : '';

            // Price display
            const currentPrice = parseFloat(product.discount ?? product.price).toFixed(2);
            const originalPrice = parseFloat(product.price).toFixed(2);
            const priceDisplay = hasDiscount ? 
                `<div class="price-container">
                    <span class="current-price">${currentPrice}৳</span>
                    <span class="original-price">${originalPrice}৳</span>
                </div>` :
                `<div class="price-container">
                    <span class="current-price">${currentPrice}৳</span>
                </div>`;

            // Strip HTML from description and limit to 1-2 lines
            const description = product.description ? stripHtmlTags(product.description) : 'No description available';
            const shortDescription = description.length > 100 ? description.substring(0, 100) + '...' : description;

            const buttonHtml = isVariationProduct
                ? `<div class="d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1 select-variation-btn" data-product-id="${product.id}" ${!isInStock ? 'disabled' : ''}>
                            <i class="fas fa-list me-1"></i>Select Variation
                        </button>
                   </div>`
                : `<div class="d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1 add-to-cart-btn" data-product-id="${product.id}" ${!isInStock ? 'disabled' : ''}>
                            <i class="fas fa-cart-plus me-1"></i>Add to Cart
                        </button>
                   </div>`;

            const cardHtml = `
                            <div class="card product-card fade-in ${!isInStock ? 'out-of-stock' : ''}" data-product-id="${product.id}">
                                <div class="product-image-wrapper">
                                    <img src="${imageSrc}" class="card-img-top product-image" alt="${product.name}" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                    ${discountBadge}
                                    ${!isInStock ? '<div class="out-of-stock-overlay"><i class="fas fa-ban"></i></div>' : ''}
                                </div>
                                <div class="card-body">
                                    <div class="product-header mb-2">
                                        <h6 class="card-title mb-1">${product.name}</h6>
                                        <span class="product-category-badge">${categoryName}</span>
                                    </div>
                                    <p class="product-description mb-2">${shortDescription}</p>
                                    <div class="product-footer">
                                        <div class="price-section mb-2">
                                            ${priceDisplay}
                                        </div>
                                        <div class="product-meta d-flex justify-content-between align-items-center mb-3">
                                            <div class="stock-info">
                                                ${stockBadge}
                                            </div>
                                        </div>
                                        ${buttonHtml}
                                    </div>
                                </div>
                            </div>
                        `;

            $col.html(cardHtml);

            // Add click event for product card (opens modal)
            $col.find('.product-card').on('click', function (e) {
                // Don't open modal if clicking the add to cart button
                if (!$(e.target).closest('.add-to-cart-btn, .select-variation-btn').length) {
                    const productId = $(this).data('product-id');
                    showProductModal(productId);
                }
            });

            // Add click event for add to cart button (simple products)
            $col.find('.add-to-cart-btn').on('click', function (e) {
                e.stopPropagation();
                const productId = $(this).data('product-id');
                addToCart(productId);
            });

            // Add click event for variation select button
            $col.find('.select-variation-btn').on('click', function (e) {
                e.stopPropagation();
                const productId = $(this).data('product-id');
                showProductModal(productId);
            });

            return $col;
        }

        function showProductModal(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return;

            // Store in session storage
            sessionStorage.setItem('lastViewedProduct', JSON.stringify(product));

            // Reset variation UI
            $('#variationWrapper').addClass('d-none');
            $('#variationButtons').empty();
            $('#variationStockInfo').text('').removeClass('text-danger text-success');
            
            // Reset Add to Cart button state
            const $addToCartBtn = $('#modalAddToCartBtn');
            if (product.has_variations) {
                // For variation products, disable until variation is selected
                $addToCartBtn.prop('disabled', true);
            } else {
                // For simple products, enable if in stock
                const isInStock = product.branch_stock && product.branch_stock.quantity > 0;
                $addToCartBtn.prop('disabled', !isInStock);
            }

            selectedProduct = { ...product, selectedVariation: null, variations: [] };

            // Handle image path
            const imageSrc = product.image ? `/${product.image}` : 'https://via.placeholder.com/300x200?text=No+Image';
            $('#modalProductImage').attr('src', imageSrc).on('error', function () {
                $(this).attr('src', 'https://via.placeholder.com/300x200?text=No+Image');
            });

            $('#modalProductName').text(product.name);
            $('#modalProductCategory').text(product.category ? product.category.name : 'Uncategorized');
            
            // Strip HTML tags from description and display as plain text
            const cleanDescription = stripHtmlTags(product.description || 'No description available');
            $('#modalProductDescription').text(cleanDescription);
            
            if (product.discount && product.discount < product.price) {
                $('#modalDiscountPrice').text(`${parseFloat(product.discount).toFixed(2)}৳`);
                $('#modalProductPrice').text(`${parseFloat(product.price).toFixed(2)}৳`).show();
            } else {
                $('#modalDiscountPrice').text(`${parseFloat(product.price).toFixed(2)}৳`);
                $('#modalProductPrice').hide();
            }
            $('#modalQuantity').val(1);

            // Add Style No information
            const styleNoInfo = `<p class="text-muted mb-2"><i class="fas fa-barcode me-1"></i>Style No: ${product.sku || 'N/A'}</p>`;

            // Insert Style No info after description
            $('#modalProductDescription').after(styleNoInfo);

            // If product has variations, load them for the current branch
            if (product.has_variations) {
                loadVariationsForProduct(product.id);
            }

            const $modal = $('#productModal');
            $modal.modal('show');

            // Clear additional info when modal is hidden
            $modal.off('hidden.bs.modal').on('hidden.bs.modal', function () {
                // Remove Style No info
                $modal.find('p.text-muted').each(function () {
                    const text = $(this).text();
                    if (text.includes('Style No:')) {
                        $(this).remove();
                    }
                });
            });
        }

        function loadMultiBranchStock(productId, variationId) {
            const url = variationId 
                ? `/erp/pos/product/${productId}/variation/${variationId}/stock`
                : `/erp/pos/product/${productId}/stock`;
            
            $.get(url, function(response) {
                if (response.success && response.data) {
                    const $wrapper = $('#multiBranchStockWrapper');
                    const $body = $('#multiBranchStockBody');
                    $body.empty();
                    
                    if (response.data.length > 0) {
                        response.data.forEach(function(stock) {
                            const stockClass = stock.quantity > 0 ? 'text-success' : 'text-danger';
                            const stockIcon = stock.quantity > 0 ? 'fa-check-circle' : 'fa-times-circle';
                            $body.append(`
                                <tr>
                                    <td>${stock.branch_name}</td>
                                    <td class="${stockClass}">
                                        <i class="fas ${stockIcon} me-1"></i>
                                        <strong>${stock.quantity}</strong>
                                    </td>
                                </tr>
                            `);
                        });
                        $wrapper.show();
                    } else {
                        $wrapper.hide();
                    }
                } else {
                    $('#multiBranchStockWrapper').hide();
                }
            }).fail(function() {
                $('#multiBranchStockWrapper').hide();
            });
        }

        function loadVariationsForProduct(productId) {
            if (!currentBranchId) return;

            $.get(`/erp/products/${productId}/variations-list`, function (vars) {
                if (!Array.isArray(vars) || vars.length === 0) {
                    return;
                }

                selectedProduct.variations = vars;
                const $wrapper = $('#variationWrapper');
                const $buttons = $('#variationButtons');
                const $stockInfo = $('#variationStockInfo');

                $wrapper.removeClass('d-none');
                $buttons.empty();

                vars.forEach(function (v) {
                    const label = v.display_name || v.name || `Variation #${v.id}`;
                    // Use variation price if set, otherwise use product price
                    const variationPrice = (v.price && v.price > 0) ? v.price : null;
                    const buttonClass = 'btn btn-outline-primary variation-btn';
                    
                    $buttons.append(
                        `<button type="button" class="${buttonClass}" data-variation-id="${v.id}" data-price="${variationPrice || ''}" data-label="${label}">
                            ${label}
                        </button>`
                    );
                });

                // Handle variation button clicks
                $buttons.off('click', '.variation-btn').on('click', '.variation-btn', function () {
                    const $btn = $(this);
                    const variationId = $btn.data('variation-id');
                    const variationPrice = $btn.data('price');
                    const $addToCartBtn = $('#modalAddToCartBtn');
                    const $stockInfo = $('#variationStockInfo');
                    
                    // If variation has no specific price, use product price
                    const productBasePrice = parseFloat(selectedProduct.price) || 0;
                    const price = (variationPrice && parseFloat(variationPrice) > 0) ? parseFloat(variationPrice) : productBasePrice;
                    const label = $btn.data('label');
                    
                    // Remove active class from all buttons
                    $buttons.find('.variation-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
                    // Add active class to clicked button
                    $btn.removeClass('btn-outline-primary').addClass('active btn-primary');

                    selectedProduct.selectedVariation = {
                        id: parseInt(variationId, 10),
                        price: price,
                        stockForBranch: null,
                    };

                    // Update price display in modal - use product's discount price if available
                    const productDiscount = parseFloat(selectedProduct.discount) || 0;
                    const hasDiscount = productDiscount > 0 && productDiscount < productBasePrice;
                    const displayPrice = hasDiscount ? productDiscount : price;
                    $('#modalDiscountPrice').text(`${parseFloat(displayPrice).toFixed(2)}৳`);
                    if (hasDiscount) {
                        $('#modalProductPrice').text(`${productBasePrice.toFixed(2)}৳`).show();
                    } else {
                        $('#modalProductPrice').hide();
                    }
                    
                    // Clear stock info and disable button while loading
                    $stockInfo.html('<i class="fas fa-spinner fa-spin me-1"></i>Loading stock...').removeClass('text-success text-danger text-warning');
                    $addToCartBtn.prop('disabled', true);

                    // Show stock per variation for the selected branch via API
                    $.get(`/erp/pos/product/${productId}/branch/${currentBranchId}/stock/${variationId}`, function (resp) {
                        let qty = 0;
                        
                        if (resp && resp.success) {
                            qty = resp.quantity || 0;
                        }
                        
                        selectedProduct.selectedVariation.stockForBranch = qty;
                        
                        // Update stock info display with color coding and warnings
                        if (qty > 0) {
                            let stockMessage = `<i class="fas fa-check-circle me-1"></i>Available stock: <strong>${qty}</strong>`;
                            if (qty <= 5) {
                                $stockInfo.removeClass('text-success text-danger').addClass('text-warning');
                                stockMessage = `<i class="fas fa-exclamation-triangle me-1"></i><strong>Low Stock!</strong> Only ${qty} available`;
                            } else {
                                $stockInfo.removeClass('text-danger text-warning').addClass('text-success');
                            }
                            $stockInfo.html(stockMessage);
                            $addToCartBtn.prop('disabled', false);
                        } else {
                            $stockInfo.removeClass('text-success text-warning').addClass('text-danger');
                            $stockInfo.html(`<i class="fas fa-times-circle me-1"></i><strong>Out of Stock</strong>`);
                            $addToCartBtn.prop('disabled', true);
                        }
                    }).fail(function (xhr, status, error) {
                        // API call failed - assume no stock
                        selectedProduct.selectedVariation.stockForBranch = 0;
                        $stockInfo.removeClass('text-success text-warning').addClass('text-danger');
                        $stockInfo.html(`<i class="fas fa-times-circle me-1"></i><strong>Out of Stock</strong>`);
                        $addToCartBtn.prop('disabled', true);
                    });
                });
            });
        }

        function addToCart(productId, quantity = 1) {
            const product = products.find(p => p.id === productId);
            if (!product) return;

            // Store in session storage
            sessionStorage.setItem('lastAddedProduct', JSON.stringify(product));

            // Check stock availability
            if (!product.branch_stock || product.branch_stock.quantity <= 0) {
                showToast('Product is out of stock!', 'warning');
                return;
            }

            const variationKey = selectedProduct && selectedProduct.selectedVariation
                ? selectedProduct.selectedVariation.id
                : null;

            const existingItem = cart.find(item =>
                item.id === productId &&
                (item.variation_id || null) === (variationKey || null)
            );

            // Determine available stock source
            let availableStock = product.branch_stock ? product.branch_stock.quantity : 0;
            if (selectedProduct && selectedProduct.selectedVariation && selectedProduct.selectedVariation.stockForBranch !== null && selectedProduct.selectedVariation.stockForBranch !== undefined) {
                availableStock = selectedProduct.selectedVariation.stockForBranch;
            }

            if (existingItem) {
                const newQuantity = existingItem.quantity + quantity;
                // Check if new quantity exceeds available stock
                if (newQuantity > availableStock) {
                    showToast(`Only ${availableStock} items available in stock for this selection!`, 'warning');
                    return;
                }
                existingItem.quantity = newQuantity;
                // Update variation info if present
                if (selectedProduct && selectedProduct.selectedVariation) {
                    const variation = selectedProduct.variations.find(v => v.id === selectedProduct.selectedVariation.id);
                    existingItem.variation_name = variation ? (variation.display_name || variation.name || `Variation #${variation.id}`) : null;
                    existingItem.variation_stock = selectedProduct.selectedVariation.stockForBranch;
                }
            } else {
                // Check if quantity exceeds available stock
                if (quantity > availableStock) {
                    showToast(`Only ${availableStock} items available in stock for this selection!`, 'warning');
                    return;
                }
                const baseItem = { ...product, quantity: quantity };
                if (selectedProduct && selectedProduct.selectedVariation) {
                    baseItem.variation_id = selectedProduct.selectedVariation.id;
                    baseItem.variation_price = selectedProduct.selectedVariation.price;
                    baseItem.price = selectedProduct.selectedVariation.price;
                    // Store variation display name for UI
                    const variation = selectedProduct.variations.find(v => v.id === selectedProduct.selectedVariation.id);
                    baseItem.variation_name = variation ? (variation.display_name || variation.name || `Variation #${variation.id}`) : null;
                    baseItem.variation_stock = selectedProduct.selectedVariation.stockForBranch;
                }
                cart.push(baseItem);
            }

            updateCartDisplay();

            // Show success message
            showToast('Product added to cart!', 'success');
        }

        function addToCartFromModal() {
            if (!selectedProduct) return;

            const quantity = parseInt($('#modalQuantity').val());

            // Validate quantity
            if (quantity <= 0) {
                showToast('Please enter a valid quantity!', 'warning');
                return;
            }

            // If product has variations, ensure one is selected and has stock
            if (selectedProduct.has_variations) {
                if (!selectedProduct.selectedVariation || !selectedProduct.selectedVariation.id) {
                    showToast('Please select a variation first.', 'warning');
                    return;
                }
                
                // Check if selected variation has stock
                const stock = selectedProduct.selectedVariation.stockForBranch;
                if (stock === null || stock === undefined || stock <= 0) {
                    showToast('Selected variation is out of stock!', 'warning');
                    return;
                }
                
                // Check if quantity exceeds available stock
                if (quantity > stock) {
                    showToast(`Only ${stock} items available in stock for this variation!`, 'warning');
                    // Update quantity input to max available
                    $('#modalQuantity').val(stock);
                    return;
                }
            }

            addToCart(selectedProduct.id, quantity);

            $('#productModal').modal('hide');
        }

        function updateCartDisplay() {
            const $cartItems = $('#cartItems');
            const $emptyCart = $('#emptyCart');
            const $cartTotal = $('#cartTotal');
            const $checkoutBtn = $('#checkoutBtn');

            // Always persist cart state, even if empty
            sessionStorage.setItem('cartItems', JSON.stringify(cart));

            if (cart.length === 0) {
                $emptyCart.show();
                $cartItems.html('<div class="text-center py-5 text-muted" id="emptyCart"><i class="fas fa-shopping-cart fa-3x mb-3"></i><p>Your cart is empty</p></div>');
                $cartTotal.text('0.00৳');
                $checkoutBtn.prop('disabled', true);
                
                // Also update the checkout drawer items if it's open
                if ($('#drawerCartItems').length) {
                    $('#drawerCartItems').html('<div class="text-center text-muted py-5">No items in cart</div>');
                    updateDrawerTotals();
                }
                return;
            }

            $emptyCart.hide();
            $checkoutBtn.prop('disabled', false);

            let total = 0;
            $cartItems.empty();

            cart.forEach(item => {
                // Use discount price if available and less than original price
                const useDiscount = item.discount && Number(item.discount) < Number(item.price);
                const displayPrice = useDiscount ? Number(item.discount) : Number(item.price);
                const originalPrice = Number(item.price);
                const itemTotal = displayPrice * item.quantity;
                total += itemTotal;

                // Show variation info if present
                const variationInfo = item.variation_name ? `<small class="text-info d-block"><i class="fas fa-tag me-1"></i>${item.variation_name}</small>` : '';
                
                // Check stock availability and show warning
                let availableStock = 0;
                if (item.variation_id && item.variation_stock !== undefined && item.variation_stock !== null) {
                    availableStock = item.variation_stock;
                } else {
                    const product = products.find(p => p.id === item.id);
                    availableStock = product && product.branch_stock ? product.branch_stock.quantity : 0;
                }
                const stockWarning = item.quantity > availableStock ? 
                    `<small class="text-danger d-block"><i class="fas fa-exclamation-triangle me-1"></i>Only ${availableStock} available in stock!</small>` : 
                    (availableStock <= 5 && availableStock > 0 ? `<small class="text-warning d-block"><i class="fas fa-exclamation-triangle me-1"></i>Low stock: ${availableStock} remaining</small>` : '');

                const $cartItem = $(`
                                <div class="cart-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${item.name}</h6>
                                            <p class="mb-2"><strong>Style No:</strong> ${item.sku || 'N/A'}</p>
                                            ${variationInfo}
                                            ${stockWarning}
                                            <small class="text-muted">
                                                ${displayPrice.toFixed(2)}৳ each
                                                ${useDiscount ? `<del style='color:rgb(179, 172, 172); margin-left:4px;'>${originalPrice.toFixed(2)}৳</del>` : ''}
                                            </small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="quantity-controls me-2">
                                                <button class="quantity-btn decrease-qty" data-product-id="${item.id}" data-variation-id="${item.variation_id || ''}">-</button>
                                                <span class="mx-2">${item.quantity}</span>
                                                <button class="quantity-btn increase-qty" data-product-id="${item.id}" data-variation-id="${item.variation_id || ''}">+</button>
                                            </div>
                                            <button class="btn btn-outline-danger btn-sm remove-item" data-product-id="${item.id}" data-variation-id="${item.variation_id || ''}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-end mt-2">
                                        <strong>${itemTotal.toFixed(2)}৳</strong>
                                    </div>
                                </div>
                            `);

                // Add event listeners for quantity controls
                $cartItem.find('.decrease-qty').on('click', function () {
                    const productId = $(this).data('product-id');
                    const variationId = $(this).data('variation-id') || null;
                    updateQuantity(productId, variationId, -1);
                });

                $cartItem.find('.increase-qty').on('click', function () {
                    const productId = $(this).data('product-id');
                    const variationId = $(this).data('variation-id') || null;
                    updateQuantity(productId, variationId, 1);
                });

                $cartItem.find('.remove-item').on('click', function () {
                    const productId = $(this).data('product-id');
                    const variationId = $(this).data('variation-id') || null;
                    removeFromCart(productId, variationId);
                });

                $cartItems.append($cartItem);
            });

            $cartTotal.text(`${total.toFixed(2)}৳`);
        }

        function updateQuantity(productId, variationId, change) {
            // Find item by both productId and variationId
            const item = cart.find(item => 
                item.id === productId && 
                (item.variation_id || null) === (variationId || null)
            );
            if (!item) return;

            const newQuantity = item.quantity + change;

            if (newQuantity <= 0) {
                removeFromCart(productId, variationId);
            } else {
                // Check if new quantity exceeds available stock
                let availableStock = 0;
                
                if (item.variation_id && item.variation_stock !== undefined && item.variation_stock !== null) {
                    // Use variation stock
                    availableStock = item.variation_stock;
                } else {
                    // Use product branch stock
                    const product = products.find(p => p.id === productId);
                    availableStock = product && product.branch_stock ? product.branch_stock.quantity : 0;
                }
                
                if (newQuantity > availableStock) {
                    showToast(`Only ${availableStock} items available in stock${item.variation_name ? ' for ' + item.variation_name : ''}!`, 'warning');
                    // Set quantity to max available
                    item.quantity = availableStock;
                    updateCartDisplay();
                    return;
                }

                item.quantity = newQuantity;
                updateCartDisplay();
            }
        }

        function removeFromCart(productId, variationId) {
            cart = cart.filter(item => 
                !(item.id === productId && (item.variation_id || null) === (variationId || null))
            );
            updateCartDisplay();
            showToast('Product removed from cart', 'info');
        }

        function clearCart() {
            cart = [];
            updateCartDisplay();
            showToast('Cart cleared', 'info');
        }

        function processPayment() {
            if (cart.length === 0) return;

            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Here you would integrate with your payment processing logic
            alert(`Processing payment for ${total.toFixed(2)}৳\n\nItems:\n${cart.map(item => `${item.name} x${item.quantity}`).join('\n')}`);

            clearCart();
            showToast('Payment processed successfully!', 'success');
        }

        function filterProducts() {
            // Reload products from API with current filters
            loadProductsFromAPI();
        }

        function sortProducts() {
            const sortBy = $('#sortBy').val();
            const $grid = $('#productsGrid');
            const $productCards = $grid.children();

            const sortedCards = $productCards.sort((a, b) => {
                const productAId = $(a).find('.product-card').data('product-id');
                const productBId = $(b).find('.product-card').data('product-id');

                const productA = products.find(p => p.id === productAId);
                const productB = products.find(p => p.id === productBId);

                switch (sortBy) {
                    case 'price':
                        return productA.price - productB.price;
                    case 'category':
                        return productA.category.localeCompare(productB.category);
                    default:
                        return productA.name.localeCompare(productB.name);
                }
            });

            $grid.empty().append(sortedCards);
        }

        function incrementQuantity() {
            const $input = $('#modalQuantity');
            $input.val(parseInt($input.val()) + 1);
        }

        function decrementQuantity() {
            const $input = $('#modalQuantity');
            const currentVal = parseInt($input.val());
            if (currentVal > 1) {
                $input.val(currentVal - 1);
            }
        }

        function showToast(message, type = 'info') {
            // Create toast notification
            const $toast = $(`
                            <div class="alert alert-${type} position-fixed" style="top: 20px; right: 20px; z-index: 16000; min-width: 250px;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-${getToastIcon(type)} me-2"></i>
                                    ${message}
                                </div>
                            </div>
                        `);

            $('body').append($toast);

            // Remove toast after 3 seconds
            setTimeout(() => {
                $toast.remove();
            }, 3000);
        }

        function getToastIcon(type) {
            switch (type) {
                case 'success': return 'check-circle';
                case 'warning': return 'exclamation-triangle';
                case 'danger': return 'times-circle';
                default: return 'info-circle';
            }
        }

        // Additional jQuery event handlers for modal buttons
        $(document).on('click', '#addToCartFromModal', addToCartFromModal);
        $(document).on('click', '#incrementQuantity', incrementQuantity);
        $(document).on('click', '#decrementQuantity', decrementQuantity);
        $(document).on('click', '#clearCart', clearCart);
        $(document).on('click', '#checkoutBtn', processPayment);

        // Drawer open/close logic
        $(document).on('click', '#openCheckoutDrawer', function () {
            renderDrawerCart();
            $('#checkoutDrawer').addClass('open').show();

            // Initialize or re-initialize Select2 for customer search
            if ($('#drawerCustomerSelect').length) {
                if ($('#drawerCustomerSelect').hasClass('select2-hidden-accessible')) {
                    $('#drawerCustomerSelect').select2('destroy');
                }
                $('#drawerCustomerSelect').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search or select customer',
                    minimumInputLength: 1,
                    dropdownParent: $('#checkoutDrawer'),
                    ajax: {
                        url: '/erp/customers/search',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(function (customer) {
                                    return {
                                        id: customer.id,
                                        text: customer.name + (customer.email ? ' (' + customer.email + (customer.phone ? ', ' + customer.phone : '') + ')' : (customer.phone ? ' (' + customer.phone + ')' : ''))
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    width: '100%'
                });
            }

            updateDrawerTotals();
        });
        $(document).on('click', '#closeCheckoutDrawer', function () {
            $('#checkoutDrawer').removeClass('open').hide();
        });

        function renderDrawerCart() {
            const $drawerCartItems = $('#drawerCartItems');
            $drawerCartItems.empty();
            let subtotal = 0;
            if (cart.length === 0) {
                $drawerCartItems.html('<div class="text-center text-muted py-5">No items in cart</div>');
            } else {
                cart.forEach(item => {
                    // Use variation_price if available, otherwise use regular price logic
                    let displayPrice, originalPrice;
                    if (item.variation_price) {
                        displayPrice = Number(item.variation_price);
                        originalPrice = Number(item.price);
                    } else {
                        const useDiscount = item.discount && Number(item.discount) < Number(item.price);
                        displayPrice = useDiscount ? Number(item.discount) : Number(item.price);
                        originalPrice = Number(item.price);
                    }
                    const itemTotal = displayPrice * item.quantity;
                    subtotal += itemTotal;
                    
                    // Show variation info if present
                    const variationInfo = item.variation_name ? `<div class='small text-info'><i class="fas fa-tag me-1"></i>${item.variation_name}</div>` : '';
                    const showDiscount = item.discount && Number(item.discount) < Number(item.price) && !item.variation_price;
                    
                    $drawerCartItems.append(`
                                    <div class='d-flex justify-content-between align-items-center mb-2'>
                                        <div>
                                            <div class='fw-bold'>${item.name}</div>
                                            ${variationInfo}
                                            <div class='small text-muted'>
                                                ${displayPrice.toFixed(2)}৳ x ${item.quantity}
                                                ${showDiscount ? `<del style='color:rgb(179, 172, 172); margin-left:4px;'>$${originalPrice.toFixed(2)}</del>` : ''}
                                            </div>
                                        </div>
                                        <div class='fw-bold'>${itemTotal.toFixed(2)}৳</div>
                                    </div>
                                `);
                });
            }
            $('#drawerCartSubtotal').text(`${subtotal.toFixed(2)}৳`);
        }

        function updateDrawerTotals() {
            // Get subtotal from cart
            let subtotal = 0;
            cart.forEach(item => {
                // Use variation_price if available, otherwise use regular price logic
                let displayPrice;
                if (item.variation_price) {
                    displayPrice = Number(item.variation_price);
                } else {
                    const useDiscount = item.discount && Number(item.discount) < Number(item.price);
                    displayPrice = useDiscount ? Number(item.discount) : Number(item.price);
                }
                subtotal += displayPrice * item.quantity;
            });

            // Get values from input fields
            const shipping = parseFloat($('#drawerShippingCharge').val()) || 0;
            const discount = parseFloat($('#drawerDiscountInput').val()) || 0;
            
            // Calculate totals
            const total = subtotal + shipping - discount;
            const paidAmount = parseFloat($('#drawerPaidAmountInput').val()) || 0;
            const dueAmount = total - paidAmount;
            
            // Update display
            $('#drawerCartSubtotal').text(`${subtotal.toFixed(2)}৳`);
            $('#drawerShippingTotal').text(`${shipping.toFixed(2)}৳`);
            $('#drawerDiscountTotal').text(`-${discount.toFixed(2)}৳`);
            $('#drawerCartTotal').text(`${total.toFixed(2)}৳`);
            $('#drawerPaidAmountTotal').text(`${paidAmount.toFixed(2)}৳`);
            $('#drawerDueAmountTotal').text(`${dueAmount.toFixed(2)}৳`);
            
            // Auto-fill paid amount if empty or 0
            if (!paidAmount || paidAmount === 0) {
                $('#drawerPaidAmountInput').val(total.toFixed(2));
                $('#drawerPaidAmountTotal').text(`${total.toFixed(2)}৳`);
                $('#drawerDueAmountTotal').text('0.00৳');
            }
        }

        // Attach event listeners to trigger calculation
        $(document).on('input', '#drawerShippingCharge, #drawerDiscountInput, #drawerPaidAmountInput', updateDrawerTotals);
        
        // Auto-fill paid amount when drawer opens
        $(document).on('click', '#openCheckoutDrawer', function() {
            setTimeout(function() {
                updateDrawerTotals();
            }, 100);
        });
    </script>
@endsection

@push('scripts')
<script>
    // Set branch id in hidden input when opening the checkout drawer
    $(document).on('click', '#openCheckoutDrawer', function() {
        if (typeof currentBranchId !== 'undefined' && currentBranchId) {
            $('#hiddenBranchId').val(currentBranchId);
        }
    });
</script>
@endpush