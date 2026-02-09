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
    
    <!-- Loading indicator and Load More button (outside container for proper positioning) -->
    <div class="container container-80">
        <div id="best-deal-loading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading more products...</p>
        </div>
        <div id="best-deal-load-more-btn" class="text-center py-4" style="display: none;">
            <button class="btn btn-primary" onclick="loadMoreBestDealProducts()">
                <i class="fas fa-arrow-down me-2"></i>Load More Products
            </button>
        </div>
    </div>


@endsection

@push('scripts')
    <script>


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
            
            // Show/hide load more button based on initial state
            const loadMoreBtn = document.getElementById('best-deal-load-more-btn');
            if (loadMoreBtn) {
                if (bestDealScrollState.hasMore) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            }

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
                const documentHeight = document.documentElement.scrollHeight || document.body.scrollHeight;

                // Load more when user is 300px from bottom
                const distanceFromBottom = documentHeight - (scrollTop + windowHeight);
                
                // Debug logging (only occasionally to avoid spam)
                if (Math.random() < 0.01) { // 1% chance to log
                    console.log('Scroll check:', {
                        scrollTop,
                        windowHeight,
                        documentHeight,
                        distanceFromBottom,
                        isLoading: bestDealScrollState.isLoading,
                        hasMore: bestDealScrollState.hasMore,
                        currentPage: bestDealScrollState.currentPage
                    });
                }
                
                if (distanceFromBottom < 300) {
                    console.log('Triggering load more - distance from bottom:', distanceFromBottom);
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
                console.log('Skipping load - isLoading:', bestDealScrollState.isLoading, 'hasMore:', bestDealScrollState.hasMore);
                return;
            }

            bestDealScrollState.isLoading = true;
            const container = document.getElementById('best-deal-container');
            const loadingIndicator = document.getElementById('best-deal-loading');
            
            if (!container) {
                console.error('Container not found!');
                bestDealScrollState.isLoading = false;
                return;
            }

            // Show loading indicator, hide load more button
            if (loadingIndicator) {
                loadingIndicator.style.display = 'block';
            }
            const loadMoreBtn = document.getElementById('best-deal-load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.style.display = 'none';
            }

            // Get next page
            const nextPage = bestDealScrollState.currentPage + 1;
            const url = '{{ route("best.deal") }}?page=' + nextPage + '&infinite_scroll=true';
            
            console.log('Loading page', nextPage, 'from URL:', url);

            // Try fetch first, fallback to XMLHttpRequest if blocked
            let fetchPromise = fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            }).catch(error => {
                // If fetch is blocked, try XMLHttpRequest
                console.warn('Fetch blocked, trying XMLHttpRequest fallback:', error);
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', url, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            // Create a Response-like object
                            resolve({
                                ok: true,
                                status: xhr.status,
                                json: () => Promise.resolve(JSON.parse(xhr.responseText)),
                                text: () => Promise.resolve(xhr.responseText)
                            });
                        } else {
                            reject(new Error('HTTP ' + xhr.status + ': ' + xhr.statusText));
                        }
                    };
                    xhr.onerror = function() {
                        reject(new Error('Network error'));
                    };
                    xhr.send();
                });
            });

            fetchPromise
            .then(response => {
                console.log('Response received, status:', response.status);
                // Check if response is ok (status 200-299)
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'An error occurred while loading products');
                    }).catch(() => {
                        throw new Error('Server error. Please try again.');
                    });
                }
                // Try to parse as JSON first
                return response.json().catch(error => {
                    // If JSON parsing fails, try to get text to see what we got
                    console.error('Failed to parse JSON, trying text:', error);
                    return response.text().then(text => {
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
            .then(data => {
                console.log('Response data received:', {
                    success: data.success,
                    hasHtml: !!data.html,
                    htmlLength: data.html ? data.html.length : 0,
                    count: data.count,
                    total: data.total,
                    hasMore: data.hasMore,
                    currentPage: data.currentPage
                });
                
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                bestDealScrollState.isLoading = false;
                
                // Show/hide load more button based on hasMore
                const loadMoreBtn = document.getElementById('best-deal-load-more-btn');
                if (loadMoreBtn && data.hasMore) {
                    loadMoreBtn.style.display = 'block';
                } else if (loadMoreBtn) {
                    loadMoreBtn.style.display = 'none';
                }

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
                        
                        // Show/hide load more button
                        const loadMoreBtn = document.getElementById('best-deal-load-more-btn');
                        if (loadMoreBtn) {
                            if (bestDealScrollState.hasMore) {
                                loadMoreBtn.style.display = 'block';
                            } else {
                                loadMoreBtn.style.display = 'none';
                            }
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
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'none';
                }
                bestDealScrollState.isLoading = false;
                
                // Handle specific error types
                if (error.message && (error.message.includes('404') || error.message.includes('Blocked'))) {
                    bestDealScrollState.hasMore = false;
                    if (container) {
                        container.setAttribute('data-has-more', 'false');
                    }
                    console.log('No more pages available or request blocked');
                } else if (error.message && error.message.includes('Network error')) {
                    // Network error - don't disable, user can retry by scrolling
                    console.warn('Network error, will retry on next scroll');
                }
                
                // Show error toast if function exists
                if (typeof showToast === 'function') {
                    let errorMsg = 'Failed to load more products. ';
                    if (error.message && error.message.includes('Blocked')) {
                        errorMsg += 'Request was blocked. Please disable ad blockers and try again.';
                    } else {
                        errorMsg += 'Please try again.';
                    }
                    showToast(errorMsg, 'error');
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing best deal infinite scroll');
            initBestDealInfiniteScroll();
            
            // Log initial state
            const container = document.getElementById('best-deal-container');
            if (container) {
                console.log('Initial state:', {
                    hasMore: container.getAttribute('data-has-more'),
                    currentPage: container.getAttribute('data-current-page'),
                    productCount: container.querySelectorAll('.product-card').length
                });
            }
            
            // Also check if page is already scrolled near bottom (for short pages)
            setTimeout(function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
                const distanceFromBottom = documentHeight - (scrollTop + windowHeight);
                
                // If page is short and we're near bottom, try loading more
                if (distanceFromBottom < 500 && bestDealScrollState.hasMore && !bestDealScrollState.isLoading) {
                    console.log('Page is short, checking if we need to load more products');
                    loadMoreBestDealProducts();
                }
            }, 500);
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
                    } else {
                        if (typeof showToast === 'function') showToast(response.message || 'Failed to update wishlist', 'error');
                    }
                },
                error: function() {
                    if (typeof showToast === 'function') showToast('Error updating wishlist', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });
    </script>

@endpush


