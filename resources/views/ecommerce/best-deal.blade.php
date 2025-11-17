@extends('ecommerce.master')

@section('main-section')
    <style></style>
    <section class="featured-categories featured-plain pb-3 pt-5">
        <div class="container container-80">
            <h2 class="section-title text-start">Best Deal</h2>
        </div>
    </section>

    <div class="container container-80 py-4">
        <div class="row g-4 grid-5" id="best-deal-container" data-has-more="{{ $products->hasMorePages() ? 'true' : 'false' }}" data-current-page="{{ $products->currentPage() }}">
            @include('ecommerce.partials.best-deal-grid', ['products' => $products, 'hidePagination' => true])
        </div>
    </div>
    
    <!-- Loading indicator (outside container for proper positioning) -->
    <div class="container container-80">
        <div id="best-deal-loading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading more products...</p>
        </div>
    </div>

    <div id="toast-container"
        style="position: fixed; top: 24px; right: 24px; z-index: 16000; display: flex; flex-direction: column; gap: 10px;">
    </div>
@endsection

@push('scripts')
    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'custom-toast ' + type;
            toast.innerHTML = `
                <div class="toast-content">
                    <span class="toast-icon">${type === 'error' ? '❌' : ''}</span>
                    <span class="toast-message">${message}</span>
                    <button class="toast-close" onclick="this.parentElement.parentElement.classList.add('hide'); setTimeout(()=>this.parentElement.parentElement.remove(), 400);">&times;</button>
                </div>
                <div class="toast-progress"></div>
            `;
            document.getElementById('toast-container').appendChild(toast);
            // Animate progress bar
            setTimeout(() => {
                toast.querySelector('.toast-progress').style.width = '0%';
            }, 10);
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }, 2500);
        }

        // Infinite scroll state
        const bestDealScrollState = {
            currentPage: 1,
            isLoading: false,
            hasMore: true
        };

        // Initialize infinite scroll state
        function initBestDealInfiniteScroll() {
            const container = document.getElementById('best-deal-container');
            if (!container) return;

            const hasMoreAttr = container.getAttribute('data-has-more');
            const currentPageAttr = container.getAttribute('data-current-page');

            if (currentPageAttr) {
                bestDealScrollState.currentPage = parseInt(currentPageAttr) || 1;
            }
            bestDealScrollState.isLoading = false;
            bestDealScrollState.hasMore = hasMoreAttr === 'true';

            // Remove existing scroll listener
            if (window.bestDealScrollHandler) {
                window.removeEventListener('scroll', window.bestDealScrollHandler);
                window.removeEventListener('touchmove', window.bestDealScrollHandler);
            }

            // Create scroll handler
            window.bestDealScrollHandler = function() {
                // Only proceed if we have more products and not currently loading
                if (bestDealScrollState.isLoading || !bestDealScrollState.hasMore) {
                    return;
                }
                
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;

                // Load more when user is 300px from bottom
                const distanceFromBottom = documentHeight - (scrollTop + windowHeight);
                if (distanceFromBottom < 300) {
                    loadMoreBestDealProducts();
                }
            };

            // Add scroll listeners
            window.addEventListener('scroll', window.bestDealScrollHandler, { passive: true });
            window.addEventListener('touchmove', window.bestDealScrollHandler, { passive: true });
        }

        // Load more products for infinite scroll
        function loadMoreBestDealProducts() {
            if (bestDealScrollState.isLoading || !bestDealScrollState.hasMore) {
                return;
            }

            bestDealScrollState.isLoading = true;
            const container = document.getElementById('best-deal-container');
            const loadingIndicator = document.getElementById('best-deal-loading');
            
            if (!container) {
                bestDealScrollState.isLoading = false;
                return;
            }

            // Show loading indicator
            if (loadingIndicator) {
                loadingIndicator.style.display = 'block';
            }

            // Get next page
            const nextPage = bestDealScrollState.currentPage + 1;

            // Make AJAX request
            fetch('{{ route("best.deal") }}?page=' + nextPage + '&infinite_scroll=true', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                // Check if response is ok (status 200-299)
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'An error occurred while loading products');
                    }).catch(() => {
                        throw new Error('Server error. Please try again.');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                bestDealScrollState.isLoading = false;

                if (data.success && data.html) {
                    // Extract product cards from the response HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    // Get all col-* divs that contain product cards
                    // Try multiple selectors to ensure we catch all product containers
                    const productContainers = Array.from(tempDiv.querySelectorAll('.col-lg-3, .col-md-4, .col-sm-6, [class*="col-"]'))
                        .filter(function(col) {
                            // Only include if it contains a product-card, not no-products-container
                            return col.querySelector('.product-card') && !col.querySelector('.no-products-container');
                        });

                    if (productContainers.length > 0) {
                        // Get existing product IDs to avoid duplicates
                        const existingProductIds = new Set();
                        container.querySelectorAll('.col-lg-3, .col-md-4, .col-sm-6').forEach(function(col) {
                            const productId = col.querySelector('.wishlist-btn[data-product-id]')?.getAttribute('data-product-id') ||
                                            col.querySelector('.btn-add-cart[data-product-id]')?.getAttribute('data-product-id');
                            if (productId) {
                                existingProductIds.add(productId);
                            }
                        });
                        
                        // Append new products to container (avoid duplicates)
                        let addedCount = 0;
                        productContainers.forEach(function(cardContainer) {
                            const productId = cardContainer.querySelector('.wishlist-btn[data-product-id]')?.getAttribute('data-product-id') ||
                                            cardContainer.querySelector('.btn-add-cart[data-product-id]')?.getAttribute('data-product-id');
                            if (!productId || !existingProductIds.has(productId)) {
                                container.appendChild(cardContainer);
                                if (productId) {
                                    existingProductIds.add(productId);
                                }
                                addedCount++;
                            }
                        });
                        
                        // If no products were added (all duplicates), mark as no more
                        if (addedCount === 0) {
                            bestDealScrollState.hasMore = false;
                            if (container) {
                                container.setAttribute('data-has-more', 'false');
                            }
                            console.log('All products were duplicates, no more to load');
                            return;
                        }

                        // Update state and container data attributes
                        bestDealScrollState.currentPage = nextPage;
                        bestDealScrollState.hasMore = data.hasMore !== undefined ? data.hasMore : false;
                        
                        // Also check if we got fewer products than expected (might be last page)
                        if (productContainers.length < 20) {
                            bestDealScrollState.hasMore = false;
                        }
                        
                        if (container) {
                            container.setAttribute('data-has-more', bestDealScrollState.hasMore ? 'true' : 'false');
                            container.setAttribute('data-current-page', nextPage);
                        }
                        
                        console.log('Loaded ' + addedCount + ' new products (total in response: ' + productContainers.length + '). Has more: ' + bestDealScrollState.hasMore);
                    } else {
                        // No products found in response
                        bestDealScrollState.hasMore = false;
                        if (container) {
                            container.setAttribute('data-has-more', 'false');
                        }
                        console.log('No products found in response');
                    }
                } else {
                    // Response was not successful or missing HTML
                    bestDealScrollState.hasMore = false;
                    if (container) {
                        container.setAttribute('data-has-more', 'false');
                    }
                    console.error('Invalid response:', data);
                }
            })
            .catch(error => {
                console.error('Load more best deal products error:', error);
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                bestDealScrollState.isLoading = false;
                // Don't set hasMore to false on error - might be temporary network issue
                // Only set to false if we get a 404 or similar error indicating no more pages
                if (error.message && error.message.includes('404')) {
                    bestDealScrollState.hasMore = false;
                    if (container) {
                        container.setAttribute('data-has-more', 'false');
                    }
                }
                // Show error toast if function exists
                if (typeof showToast === 'function') {
                    showToast('Failed to load more products. Please try again.', 'error');
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initBestDealInfiniteScroll();
        });

        // Cart functionality is now handled by global cart handler in master.blade.php
        // No need for duplicate event listeners here

        $(document).on('click', '.wishlist-btn', function (e) {
            e.preventDefault();
            var btn = $(this);
            var icon = btn.find('i.fa-heart');
            var productId = btn.data('product-id');
            $.ajax({
                url: '/add-remove-wishlist/' + productId,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        icon.toggleClass('active');
                        icon.toggleClass('fas far');
                        if (typeof showToast === 'function') showToast(response.message, 'success');
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
    </style>
@endpush


