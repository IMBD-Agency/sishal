@include('ecommerce.components.header')
@include('ecommerce.components.navbar')

<!-- Main Content Container for AJAX Loading -->
<div id="main-content-container">
    @yield('main-section')
    @stack('scripts')
</div>

@include('ecommerce.components.footer')

<!-- Scroll to Top Button -->
<button id="scrollToTopBtn" class="scroll-to-top-btn" title="Scroll to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Page loading optimization -->
<div id="page-loader"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 20000; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s ease-out; pointer-events: none;">
    <div style="text-align: center;">
        <div
            style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #2196F3; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;">
        </div>
        <p style="color: #666; font-size: 14px;">Loading...</p>
    </div>
</div>

<script>
    // Enhanced smooth navigation without Turbo CDN
    document.addEventListener('DOMContentLoaded', function () {
        // Optimized header management - prevent inconsistent behavior
        let headerElements = null;
        let headerVarsSet = false;

        function initializeHeaderElements() {
            if (!headerElements) {
                headerElements = {
                    topBar: document.querySelector('.top-bar'),
                    header: document.querySelector('header.modern-header'),
                    nav: document.querySelector('nav.main-nav'),
                    root: document.documentElement
                };
            }
            return headerElements;
        }

        function setHeaderVars(force = false) {
            // Completely disable dynamic header variable calculation
            // Use only CSS-defined values to prevent any shake
            return;
        }

        // Set initial values immediately
        setHeaderVars();

        // Completely disable resize-based header recalculation to prevent shake
        // let resizeTimeout;
        // window.addEventListener('resize', function() {
        //     // Disabled to prevent any dynamic calculations
        // });
        // CSS handles fixed layout using variables; just ensure vars stay updated
        // Hide page loader with subtle transition
        const pageLoader = document.getElementById('page-loader');
        if (pageLoader) {
            // Ensure loader is hidden immediately on page load
            pageLoader.style.opacity = '0';
            pageLoader.style.display = 'none';
        }

        // AJAX Navigation System Removed - Using standard page navigation for better SEO

        function showPageLoader() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.display = 'flex';
                loader.style.opacity = '1';
            }
        }

        function hidePageLoader() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.transition = 'opacity 0.05s ease-out';
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 50);
            }
        }

        // loadPageContent function removed - using standard page navigation

        function updateActiveNavigation(url) {
            // Remove active class from all nav items
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.classList.remove('active');
            });

            // Add active class to current page nav item
            const currentUrl = new URL(url);
            const currentPath = currentUrl.pathname;
            const currentSearch = currentUrl.search;

            console.log('Updating navigation for:', currentPath, currentSearch); // Debug log

            // Check for exact matches first
            navItems.forEach(item => {
                const link = item.querySelector('a');
                if (link) {
                    const linkUrl = new URL(link.href);
                    const linkPath = linkUrl.pathname;
                    const linkSearch = linkUrl.search;

                    console.log('Checking link:', linkPath, linkSearch); // Debug log

                    // Exact match (path and query parameters)
                    if (linkPath === currentPath && linkSearch === currentSearch) {
                        item.classList.add('active');
                        console.log('Exact match found:', link.textContent.trim());
                    }
                    // Home page match
                    else if (currentPath === '/' && linkPath === '/') {
                        item.classList.add('active');
                        console.log('Home match found');
                    }
                    // Products page - check for specific view parameter
                    else if (currentPath === '/products') {
                        // If current page has view=categories, only activate Category link
                        if (currentSearch.includes('view=categories')) {
                            if (linkSearch.includes('view=categories')) {
                                item.classList.add('active');
                                console.log('Category match found');
                            }
                        }
                        // If current page doesn't have view=categories, only activate Products link
                        else {
                            if (!linkSearch.includes('view=categories') && linkPath === '/products') {
                                item.classList.add('active');
                                console.log('Products match found');
                            }
                        }
                    }
                    // Contact page match
                    else if (currentPath === '/contact' && linkPath === '/contact') {
                        item.classList.add('active');
                        console.log('Contact match found');
                    }
                    // About page match
                    else if (currentPath === '/about' && linkPath === '/about') {
                        item.classList.add('active');
                        console.log('About match found');
                    }
                    // Additional pages match
                    else if (currentPath.startsWith('/pages/') && linkPath === currentPath) {
                        item.classList.add('active');
                        console.log('Additional page match found');
                    }
                }
            });
        }

        // reinitializePageScripts function removed - no longer needed

        function ensureStylesLoaded() {
            // Gentle style recalculation without forcing reflow
            const container = document.getElementById('main-content-container');
            if (container) {
                // Only trigger reflow if necessary
                container.offsetHeight;
            }
        }

        function scrollToTop() {
            // Simple, consistent scroll to top without multiple methods
            window.scrollTo(0, 0);
        }

        // AJAX navigation removed - using standard page navigation

        // Handle initial page load - only scroll if not already at top
        if (window.history.state === null && window.pageYOffset > 0) {
            scrollToTop();
        }

        // ensureNavLinksWork function removed - no longer needed

        // handleNavLinkClick function removed - no longer needed

        // AJAX navigation calls removed - using standard page navigation

        // ensureContactAboutWork function removed - no longer needed

        function initializeTabFunctionality() {
            // Initialize tab functionality for product pages
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            if (tabButtons.length > 0 && tabContents.length > 0) {
                console.log('Initializing tab functionality for', tabButtons.length, 'tabs');

                tabButtons.forEach(function (btn) {
                    // Remove any existing event listeners
                    btn.removeEventListener('click', handleTabClick);
                    // Add new event listener
                    btn.addEventListener('click', handleTabClick);
                });

                function handleTabClick(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    console.log('Tab clicked:', this.getAttribute('data-tab'));

                    // Remove active from all buttons
                    tabButtons.forEach(b => b.classList.remove('active'));
                    // Hide all tab contents
                    tabContents.forEach(tc => {
                        tc.classList.remove('active');
                        tc.style.display = 'none';
                    });

                    // Activate clicked button
                    this.classList.add('active');

                    // Show corresponding tab
                    const tabId = this.getAttribute('data-tab');
                    const tabContent = document.getElementById(tabId);
                    if (tabContent) {
                        tabContent.classList.add('active');
                        tabContent.style.display = 'block';
                        console.log('Showing tab:', tabId);
                    } else {
                        console.log('Tab content not found:', tabId);
                    }
                }
            }
        }

        // Contact and About handling removed - using standard navigation

        // Initialize tab functionality on page load
        initializeTabFunctionality();

        // Define and run loader for Most Sold Products (Home)
        window.loadMostSoldProducts = function () {
            var container = window.jQuery ? window.jQuery('#mostSoldProductsContainer') : null;
            if (!container || container.length === 0) return; // Not on home section

            // If already populated, do nothing
            if (container.children().length > 0) return;

            // Check if home page script has already loaded products with ratings
            if (container.find('.product-meta .stars').length > 0) return;

            // Disable delayed product loading to prevent layout shifts
            // if (window.location.pathname === '/' && container.length > 0) {
            //     // Disabled to prevent shake
            //     return;
            // }

            // Load products from master layout
            loadProductsFromMaster();
        };

        // Separate function to load products from master layout
        function loadProductsFromMaster() {
            var container = window.jQuery ? window.jQuery('#mostSoldProductsContainer') : null;
            if (!container || container.length === 0) return;

            if (window.jQuery) {
                window.jQuery.get('/api/products/most-sold', function (products) {
                    container.empty();
                    if (!products || !products.length) {
                        container.append('<div class="col-12 text-center text-muted">No products found.</div>');
                        return;
                    }
                    products.forEach(function (product) {
                        var rating = product.avg_rating ?? product.rating ?? 0;
                        var price = parseFloat(product.price || 0).toFixed(2);
                        var image = product.image ? product.image : '/static/default-product.jpg';
                        var category = product.category_name || product.category || '';
                        container.append('\
                    <div class="col-lg-3 col-md-6 mb-4">\
                        <div class="product-card position-relative" data-href="/product/' + product.slug + '" data-gtm-id="' + product.id + '" data-gtm-name="' + product.name + '" data-gtm-price="' + price + '" data-gtm-category="' + category + '">\
                            <button class="wishlist-btn' + (product.is_wishlisted ? ' active' : '') + '" data-product-id="' + product.id + '">\
                                <i class="' + (product.is_wishlisted ? 'fas text-danger' : 'far') + ' fa-heart"></i>\
                            </button>\
                            <div class="product-image-container">\
                                <img src="' + image + '" class="product-image" alt="' + product.name + '">\
                            </div>\
                            <div class="product-info">\
                                <a href="/product/' + product.slug + '" style="text-decoration: none" class="product-title">' + product.name + '</a>\
                                <div class="product-meta">\
                                    <div class="stars" aria-label="' + rating + ' out of 5">' + Array.from({ length: 5 }).map(function (_, i) { return '<i class="fa' + (i < Math.round(rating) ? 's' : 'r') + ' fa-star"></i>'; }).join('') + '</div>\
                                </div>\
                                <div class="price">' + price + '৳</div>\
                                <div class="d-flex justify-content-between align-items-center gap-2 product-actions">\
                                    <a href="/product/' + product.slug + '" class="btn-add-cart" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;"><svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" fill="#fff" width="14" height="14"><path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z"></path><circle cx="7" cy="22" r="2"></circle><circle cx="17" cy="22" r="2"></circle></svg> View Product</a>\
                                </div>\
                            </div>\
                        </div>\
                    </div>');
                    });
                }).fail(function () {
                    container.html('<div class="col-12 text-center text-danger">Failed to load products.</div>');
                });
            }
        };

        // Attempt to load products on initial page load as well
        loadMostSoldProducts();

        // Contact and About periodic handling removed

        // Initialize tab functionality once on page load
        initializeTabFunctionality();

        // Minimal image loading
        const images = document.querySelectorAll('img');
        if (images.length > 0) {
            images.forEach(img => {
                if (img) {
                    // Disable image loading animations to prevent layout shifts
                    img.style.opacity = '1';
                    // if (img.complete) {
                    //     img.style.opacity = '1';
                    // } else {
                    //     img.style.opacity = '0.995';
                    //     img.style.transition = 'opacity 0.03s ease-in-out';
                    //     img.addEventListener('load', function() {
                    //         this.style.opacity = '1';
                    //     });
                    // }
                }
            });
        }

        // Smooth scroll for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        if (anchorLinks.length > 0) {
            anchorLinks.forEach(link => {
                if (link) {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href').substring(1);
                        const targetElement = document.getElementById(targetId);
                        if (targetElement) {
                            targetElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });
                }
            });
        }

        // Add loading state for forms
        const forms = document.querySelectorAll('form');
        if (forms.length > 0) {
            forms.forEach(form => {
                if (form) {
                    form.addEventListener('submit', function () {
                        const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
                        if (submitBtn) {
                            submitBtn.style.opacity = '0.7';
                            submitBtn.disabled = true;
                        }
                    });
                }
            });
        }

        // Global cart count update function
        window.updateCartQtyBadge = function () {
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                // Fallback: fetch cart count directly
                fetch('/cart/qty-sum')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.qty_sum !== undefined) {
                            const count = data.qty_sum;

                            // Update cart count badges in navbar
                            const navCartCounts = document.querySelectorAll('.nav-cart-count');
                            navCartCounts.forEach(function (el) {
                                el.textContent = count;
                            });

                            // Update mobile cart count
                            const mobileCartCounts = document.querySelectorAll('.qi-badge.nav-cart-count');
                            mobileCartCounts.forEach(function (el) {
                                el.textContent = count;
                            });
                        }
                    })
                    .catch(function () {
                        // Silent fail
                    });
            }
        };

        // Global toast notification function
        window.showToast = function (message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'custom-toast ' + type;
            toast.innerHTML = `
                <div class="toast-content">
                    <span class="toast-icon">${type === 'error' ? '❌' : '✅'}</span>
                    <span class="toast-message">${message}</span>
                    <button class="toast-close">&times;</button>
                </div>
                <div class="toast-progress"></div>
            `;

            var container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }

            container.appendChild(toast);

            // Trigger animations
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                const progress = toast.querySelector('.toast-progress');
                if (progress) progress.style.transform = 'scaleX(0)';
            }, 10);

            const removeToast = () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            };

            // Auto-remove after 3 seconds
            const timeout = setTimeout(removeToast, 3000);

            // Close on click
            const closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.onclick = () => {
                    clearTimeout(timeout);
                    removeToast();
                };
            }
        };

        // Global wishlist toggle function
        window.toggleWishlist = function (productId) {
            console.log('[WISHLIST] Toggling wishlist for product:', productId);

            // Fast authentication check before sending request
            const isAuth = document.querySelector('meta[name="auth-check"]')?.getAttribute('content') === '1';
            if (!isAuth) {
                if (typeof showToast === 'function') {
                    showToast('Please login first to add products to your wishlist.', 'error');
                }
                return;
            }

            const button = document.querySelector(`[data-product-id="${productId}"].product-wishlist-top`);
            if (!button) {
                console.error('[WISHLIST] Button not found for product:', productId);
                return;
            }

            const isActive = button.classList.contains('active');
            const icon = button.querySelector('i');

            // Show loading state
            button.disabled = true;
            icon.className = 'fas fa-spinner fa-spin';

            fetch(`/add-remove-wishlist/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(async response => {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Invalid JSON response:', text.substring(0, 100));
                        throw new Error('Server returned an invalid response');
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Toggle button state
                        button.classList.toggle('active');
                        icon.className = isActive ? 'far fa-heart' : 'fas fa-heart';

                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast(isActive ? 'Removed from wishlist!' : 'Added to wishlist!', 'success');
                        } else {
                            alert(isActive ? 'Removed from wishlist!' : 'Added to wishlist!');
                        }

                        // Update wishlist count
                        if (typeof updateWishlistCount === 'function') {
                            updateWishlistCount();
                        }
                    } else {
                        // Show error message
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Failed to update wishlist', 'error');
                        } else {
                            alert(data.message || 'Failed to update wishlist');
                        }

                        // Reset button state
                        icon.className = isActive ? 'fas fa-heart' : 'far fa-heart';
                    }
                })
                .catch(error => {
                    console.error('Wishlist error:', error);
                    if (typeof showToast === 'function') {
                        showToast('Error updating wishlist', 'error');
                    } else {
                        alert('Error updating wishlist');
                    }

                    // Reset button state
                    icon.className = isActive ? 'fas fa-heart' : 'far fa-heart';
                })
                .finally(() => {
                    button.disabled = false;
                });
        };

        // Prevent layout shifts without excessive transforms
        const preventLayoutShifts = () => {
            // Ensure body doesn't have problematic transforms
            document.body.style.transform = 'none';
            document.body.style.willChange = 'auto';
            // Remove any existing transform classes that might conflict
            document.body.classList.remove('transform-gpu', 'will-change-transform');
        };

        // Apply layout shift prevention on page load
        preventLayoutShifts();

        // Re-apply on window resize to prevent layout shifts
        window.addEventListener('resize', preventLayoutShifts);

        // Ensure header stability without scroll interference
        function stabilizeHeader() {
            // Directly target elements without dynamic calculation
            const topBar = document.querySelector('.top-bar');
            const header = document.querySelector('header.modern-header');
            const nav = document.querySelector('nav.main-nav');

            // Remove conflicting transforms that cause shake
            if (topBar) topBar.style.transform = 'none';
            if (header) header.style.transform = 'none';
            if (nav) nav.style.transform = 'none';
        }

        // Stabilize header once on load
        stabilizeHeader();

        // Disable visibility change header recalculation to prevent shake
        // document.addEventListener('visibilitychange', function() {
        //     if (!document.hidden) {
        //         stabilizeHeader();
        //     }
        // });

        // Force header stability on window focus
        window.addEventListener('focus', function () {
            stabilizeHeader();
        });

        // Scroll to Top Button functionality
        const scrollToTopBtn = document.getElementById('scrollToTopBtn');

        // Show/hide scroll to top button based on scroll position
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });

        // Scroll to top when button is clicked
        scrollToTopBtn.addEventListener('click', function () {
            scrollToTop();
        });

        // Global cart event handler to prevent duplicate listeners
        window.globalCartHandler = function (e) {
            var btn = e.target.closest('.btn-add-cart');
            // If it's an anchor tag, it's a "View Product" link - let it proceed with navigation
            if (!btn || btn.tagName === 'A') return;

            // Check stock for products without variations
            var hasStock = btn.getAttribute('data-has-stock');
            if (hasStock === 'false') {
                e.preventDefault();
                e.stopPropagation();
                showToast('This product is out of stock!', 'warning');
                return;
            }

            // For dynamically generated buttons without stock info, we'll let the server handle the check
            // The server-side cart handler will check stock and return appropriate response

            e.preventDefault();
            e.stopPropagation();

            // Prevent multiple simultaneous requests
            if (btn.disabled || btn.getAttribute('data-processing') === 'true') {
                return;
            }

            // Mark button as processing
            btn.setAttribute('data-processing', 'true');
            btn.disabled = true;

            var productId = btn.getAttribute('data-product-id');
            var productName = btn.getAttribute('data-product-name') || 'Product';

            if (!productId) {
                btn.disabled = false;
                btn.removeAttribute('data-processing');
                if (typeof showToast === 'function') showToast('Error: Product ID not found', 'error');
                return;
            }

            // Get quantity if available
            var qtyInput = document.getElementById('quantityInput');
            var qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            // Prepare data
            var data = new URLSearchParams();
            data.append('qty', qty.toString());

            // Get CSRF token
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

            // Determine the correct endpoint based on current page
            var endpoint = window.location.pathname.includes('/product/') ?
                '/cart/add-page/' + productId :
                '/cart/add/' + productId;

            // Handle variations for product details page
            if (window.location.pathname.includes('/product/')) {
                // Check for variations - multiple ways to detect
                var hasVariations = document.querySelector('[data-has-variations="true"]') !== null ||
                    document.querySelector('[data-has-variations="1"]') !== null ||
                    document.querySelector('.color-option, .size-option, .variation-option').length > 0;

                if (hasVariations) {
                    var variationIdEl = document.getElementById('selected-variation-id');
                    var selectedVariationId = variationIdEl ? variationIdEl.value : null;

                    // Debug logging
                    console.log('[CART] Product has variations, checking selection:', {
                        hasVariations: hasVariations,
                        variationIdEl: variationIdEl,
                        selectedVariationId: selectedVariationId,
                        elementValue: variationIdEl ? variationIdEl.value : 'element not found'
                    });

                    if (!selectedVariationId || selectedVariationId === '' || selectedVariationId === '0') {
                        console.error('[CART] Variation not selected!', {
                            variationIdEl: variationIdEl,
                            value: variationIdEl ? variationIdEl.value : 'N/A'
                        });
                        if (typeof showToast === 'function') showToast('Please select product options (Color/Size) before adding to cart', 'error');
                        btn.disabled = false;
                        btn.removeAttribute('data-processing');
                        return;
                    }

                    data.append('variation_id', selectedVariationId);
                    console.log('[CART] Added variation_id to request:', selectedVariationId);
                } else {
                    console.log('[CART] Product has no variations, skipping variation_id');
                }
            }

            // Function to re-enable button
            var reEnableButton = function () {
                btn.disabled = false;
                btn.removeAttribute('data-processing');
            };

            // Backup timeout to ensure button gets re-enabled even if everything fails
            var backupTimeout = setTimeout(function () {
                reEnableButton();
            }, 5000); // 5 second backup timeout

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: data.toString()
            })
                .then(response => {
                    // Handle authentication redirect (401 status)
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        if (typeof showToast === 'function') showToast(data.message || 'Product added to cart successfully!', 'success');
                        if (typeof updateCartCount === 'function') updateCartCount();
                        if (typeof updateCartQtyBadge === 'function') updateCartQtyBadge();

                        // GTM add_to_cart event tracking
                        if (data.product && window.dataLayer) {
                            window.dataLayer.push({
                                'event': 'add_to_cart',
                                'ecommerce': {
                                    'currency': 'BDT',
                                    'value': data.product.price * (data.qty || 1),
                                    'items': [{
                                        'item_id': String(data.product.id),
                                        'item_name': data.product.name,
                                        'item_category': data.product.category || '',
                                        'price': data.product.price,
                                        'quantity': data.qty || 1
                                    }]
                                }
                            });
                        }
                    } else if (data && data.redirect) {
                        // Check if response contains redirect URL (for authentication)
                        window.location.href = data.redirect;
                    }
                    // No error popup needed - redirect handles authentication
                })
                .catch(error => {
                    // No error popup needed - redirect handles authentication
                })
                .finally(() => {
                    clearTimeout(backupTimeout);
                    reEnableButton();
                });
        };

        // Remove any existing global cart listeners and add the new one
        document.removeEventListener('click', window.globalCartHandler);
        document.addEventListener('click', window.globalCartHandler);

        // Clean up any stuck buttons on page load (but preserve variation logic)
        document.querySelectorAll('.btn-add-cart[data-processing="true"]').forEach(function (btn) {
            btn.disabled = false;
            btn.removeAttribute('data-processing');
        });

        // Disable delayed variation logic to prevent layout shifts
        // setTimeout(function() {
        //     // Disabled to prevent shake
        // }, 100);

        // Clean up stuck buttons on page visibility change instead of interval
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                var stuckButtons = document.querySelectorAll('.btn-add-cart[data-processing="true"]');
                if (stuckButtons.length > 0) {
                    stuckButtons.forEach(function (btn) {
                        btn.disabled = false;
                        btn.removeAttribute('data-processing');
                    });
                }
            }
        });

        // Reset button states when page becomes visible (navigation between pages)
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                document.querySelectorAll('.btn-add-cart').forEach(function (btn) {
                    btn.disabled = false;
                    btn.removeAttribute('data-processing');
                });

                // Disable delayed variation logic to prevent layout shifts
                // setTimeout(function() {
                //     // Disabled to prevent shake
                // }, 50);
            }
        });

        // Global product card click handler - make entire product card clickable
        document.addEventListener('click', function (e) {
            var productCard = e.target.closest('.product-card');
            if (!productCard) return;

            // Prevent navigation when clicking on wishlist/cart or other interactive UI inside the card
            if (
                e.target.closest('.wishlist-btn') ||
                e.target.closest('.product-wishlist-top') ||
                e.target.closest('.btn-add-cart') ||
                e.target.closest('button') ||
                e.target.closest('a')
            ) {
                return;
            }

            // Don't trigger if clicking on generic interactive elements
            var interactiveElements = ['A', 'BUTTON', 'SVG', 'PATH', 'FORM', 'INPUT', 'SELECT', 'TEXTAREA', 'LABEL'];
            if (interactiveElements.includes(e.target.tagName)) return;

            // Navigation Logic
            var href = productCard.getAttribute('data-href');

            // Fallback to title link if data-href is missing
            if (!href) {
                var titleLink = productCard.querySelector('.product-title');
                if (titleLink && titleLink.href) {
                    href = titleLink.href;
                }
            }

            if (href) {
                e.preventDefault();
                // Security: Ensure href is internal
                if (href.startsWith('http') && !href.includes(window.location.hostname)) {
                    console.error('BLOCKED: External redirect attempt to:', href);
                    return;
                }
                
                var separator = href.indexOf('?') !== -1 ? '&' : '?';
                // Append no_cache timestamp to satisfy Controller logic and bypass browser cache
                var newUrl = href + separator + 'no_cache=' + new Date().getTime();
                
                // Use a direct assignment to bypass any intercepted assign() method
                window.location.href = newUrl;
            }
        });
    });
</script>

</body>

</html>