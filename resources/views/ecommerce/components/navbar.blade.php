<!-- Top Bar -->
<div class="top-bar">
    <div class="marquee-wrapper">
        <div class="marquee-content">
            <span class="top-bar-text">{{ $general_settings->top_text }}</span>
            <span class="top-bar-text">{{ $general_settings->top_text }}</span>
            <span class="top-bar-text">{{ $general_settings->top_text }}</span>
            <span class="top-bar-text">{{ $general_settings->top_text }}</span>
            <span class="top-bar-text">{{ $general_settings->top_text }}</span>
            <span class="top-bar-text">{{ $general_settings->top_text }}</span>
        </div>
    </div>
</div>

<!-- Header Section -->
<header class="modern-header">
    <div class="container">
        <div class="header-wrapper d-flex align-items-center justify-content-between py-2">
            <!-- Logo -->
            <div class="logo-section">
                <a class="navbar-brand" href="/">
                    <img src="{{ $general_settings && $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/default-logo.webp') }}" alt="{{ $general_settings->site_title ?? 'alicom' }}" class="logo-img" width="140" height="55" decoding="sync" loading="eager">
                </a>
            </div>

            <!-- Search Bar -->
            <div class="search-section flex-grow-1 mx-lg-5 mx-md-3 d-none d-md-block">
                <form class="search-form" action="{{ route('search') }}" method="get">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="What are you looking for?" name="search">
                        <button class="search-btn" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side Action Buttons -->
            <div class="actions-section">
                <div class="header-actions">
                    <!-- Mobile search toggle -->
                    <button class="action-btn mobile-search-toggle d-md-none" id="mobileSearchToggle" aria-controls="mobileSearchBar" aria-expanded="false" aria-label="Toggle search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    
                    <!-- Wishlist -->
                    <a href="{{ route('wishlist.index') }}" class="action-btn" title="Wishlist">
                        <i class="fa-regular fa-heart"></i>
                        <span class="badge nav-wishlist-count">0</span>
                    </a>
                    
                    <!-- Cart -->
                    <a href="#" class="action-btn" onclick="openOffcanvasCart(); return false;" title="Cart">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="badge nav-cart-count">0</span>
                    </a>
                    
                    <!-- Account -->
                    <div class="dropdown">
                        <button class="action-btn account-toggle" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                            <i class="fa-regular fa-user"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            @guest
                                <li><a class="dropdown-item" href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket me-2"></i> Login</a></li>
                                <li><a class="dropdown-item" href="{{ route('register') }}"><i class="fa-solid fa-user-plus me-2"></i> Register</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-id-card me-2"></i> Profile</a></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</button>
                                    </form>
                                </li>
                            @endguest
                        </ul>
                    </div>

                    <!-- Mobile menu toggle -->
                    <button class="action-btn mobile-menu-toggle d-lg-none" id="mobileMenuToggle" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main Navigation -->
<nav class="main-nav">
    <div class="container">
        <div class="nav-container d-flex align-items-center justify-content-center">
            <ul class="nav-links d-flex align-items-center mb-0 list-unstyled">
                <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <a href="/" class="nav-link">Home</a>
                </li>
                    
                <!-- Categories -->
                @if(isset($nav_categories) && $nav_categories->count() > 0)
                    @foreach($nav_categories as $category)
                    <li class="nav-item nav-item-dropdown">
                        <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="nav-link">
                            {{ $category->name }}
                            @php $children = $category->children ?? ($category->subcategories ?? collect()); @endphp
                            @if($children->count() > 0)
                                <i class="fa-solid fa-chevron-down ms-1 small"></i>
                            @endif
                        </a>
                        @if($children->count() > 0)
                        <div class="dropdown-panel">
                            <ul class="dropdown-list">
                                @foreach($children as $child)
                                <li>
                                    <a href="{{ route('product.archive') }}?category={{ $child->slug }}" class="dropdown-link">
                                        {{ $child->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </li>
                    @endforeach
                @endif
                <li class="nav-item {{ request()->is('best-deal') ? 'active' : '' }}">
                    <a href="{{ route('best.deal') }}" class="nav-link">Best Deals</a>
                </li>
                <li class="nav-item {{ request()->is('contact*') ? 'active' : '' }}">
                    <a href="{{ route('contact') }}" class="nav-link">Contact</a>
                </li>
                @foreach($additional_pages as $page)
                    @if($page->positioned_at == 'navbar')
                    <li class="nav-item {{ request()->is('page/' . $page->slug) ? 'active' : '' }}">
                        <a href="{{ route('additionalPage.show', $page->slug) }}" class="nav-link">{{ $page->title }}</a>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</nav>
        <!-- Mobile overlay & side drawer -->
        <!-- Mobile overlay & side drawer -->
<div class="mobile-overlay" id="mobileOverlay" hidden></div>
<aside class="mobile-drawer" id="mobileNav" aria-hidden="true" hidden>
    <div class="drawer-header d-flex align-items-center justify-content-between p-3 border-bottom">
        <span class="fw-bold text-uppercase">Menu</span>
        <button class="drawer-close btn-close shadow-none" id="mobileMenuClose" aria-label="Close menu"></button>
    </div>
    <div class="drawer-content p-3">
        <div class="drawer-user-section mb-4">
            <a href="{{ auth()->check() ? route('profile.edit') : route('login') }}" class="d-flex align-items-center p-3 rounded-4 bg-light text-decoration-none">
                <div class="user-avatar bg-white shadow-sm d-flex align-items-center justify-content-center rounded-circle me-3" style="width: 45px; height: 45px;">
                    <i class="fa-regular fa-user text-dark"></i>
                </div>
                <div class="user-info">
                    <span class="d-block fw-bold text-dark">
                        @auth
                            {{ Auth::user()->first_name ?? 'User' }}
                        @else
                            Guest User
                        @endauth
                    </span>
                    <small class="text-muted">@auth View Profile @else Login / Register @endauth</small>
                </div>
                <i class="fa-solid fa-chevron-right ms-auto text-muted small"></i>
            </a>
        </div>

        <div class="drawer-quick-actions d-grid grid-2 gap-2 mb-4">
            <a href="{{ route('wishlist.index') }}" class="d-flex flex-column align-items-center p-3 rounded-4 bg-light text-decoration-none text-dark">
                <i class="fa-regular fa-heart mb-1 fs-5"></i>
                <span class="small fw-semibold">Wishlist</span>
            </a>
            <a href="#" onclick="openOffcanvasCart(); return false;" class="d-flex flex-column align-items-center p-3 rounded-4 bg-light text-decoration-none text-dark">
                <i class="fa-solid fa-cart-shopping mb-1 fs-5"></i>
                <span class="small fw-semibold">Cart</span>
            </a>
        </div>

        <nav class="drawer-links-nav">
            <div class="nav-section-label">Main Navigation</div>
            <ul class="list-unstyled">
                <li class="mb-1">
                    <a href="/" class="drawer-nav-link p-2 d-flex align-items-center rounded-3 {{ request()->is('/') ? 'active' : '' }}">
                        <i class="fa-solid fa-house me-3 text-muted"></i> Home
                    </a>
                </li>
                
                @if(isset($nav_categories) && $nav_categories->count() > 0)
                <li class="nav-section-label">Categories</li>
                @foreach($nav_categories as $idx => $category)
                @php $children = $category->children ?? ($category->subcategories ?? collect()); $hasChildren = $children->count() > 0; @endphp
                <li class="mb-1">
                    <div class="drawer-category-item rounded-3 {{ $hasChildren ? 'has-children' : '' }}">
                        <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="drawer-nav-link p-2 d-flex align-items-center justify-content-between">
                            <span>{{ $category->name }}</span>
                            @if($hasChildren)
                                <i class="fa-solid fa-chevron-right small text-muted category-arrow"></i>
                            @endif
                        </a>
                        @if($hasChildren)
                        <div class="drawer-sublinks ps-4 shadow-inner">
                            @foreach($children as $child)
                                <a href="{{ route('product.archive') }}?category={{ $child->slug }}" class="drawer-nav-link p-2 d-block small">
                                    {{ $child->name }}
                                </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </li>
                @endforeach
                @endif
                
                <li class="nav-section-label">Support</li>
                <li class="mb-1">
                    <a href="{{ route('contact') }}" class="drawer-nav-link p-2 d-flex align-items-center rounded-3">
                        <i class="fa-solid fa-headset me-3 text-muted"></i> Contact Us
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
    </div>
</nav>
<!-- end header-stack -->
<div class="mobile-searchbar" id="mobileSearchBar" hidden>
    <div class="container py-2">
        <form class="mobile-search-form" action="{{ route('search') }}" method="get">
            <div class="search-container">
                <input type="text" class="search-input py-2 px-4 rounded-pill border" placeholder="Search products..." name="search">
                <button class="search-btn bg-transparent border-0 text-dark position-absolute end-0 top-50 translate-middle-y pe-4" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    (function(){
        var toggle = document.getElementById('mobileMenuToggle');
        var drawer = document.getElementById('mobileNav');
        var overlay = document.getElementById('mobileOverlay');
        var closeBtn = document.getElementById('mobileMenuClose');
        var searchToggle = document.getElementById('mobileSearchToggle');
        var searchBar = document.getElementById('mobileSearchBar');

        if(overlay && overlay.parentElement !== document.body){
            document.body.appendChild(overlay);
        }
        if(drawer && drawer.parentElement !== document.body){
            document.body.appendChild(drawer);
        }

        function openDrawer(){
            if(!drawer || !overlay) return;
            drawer.removeAttribute('hidden');
            overlay.removeAttribute('hidden');
            // Ensure display is block before adding open classes
            setTimeout(function(){
                drawer.classList.add('open');
                overlay.classList.add('open');
                document.documentElement.style.overflow = 'hidden';
            }, 10);
        }

        function closeDrawer(){
            if(!drawer || !overlay) return;
            drawer.classList.remove('open');
            overlay.classList.remove('open');
            document.documentElement.style.overflow = '';
            setTimeout(function(){
                drawer.setAttribute('hidden','');
                overlay.setAttribute('hidden','');
            }, 400);
        }

        if(toggle) toggle.addEventListener('click', openDrawer);
        if(closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if(overlay) overlay.addEventListener('click', closeDrawer);

        // Handle category accordion in mobile drawer
        var categoryItems = document.querySelectorAll('.drawer-category-item.has-children');
        categoryItems.forEach(function(item) {
            var toggleLink = item.querySelector('a');
            var arrow = item.querySelector('.category-arrow');
            var sublinks = item.querySelector('.drawer-sublinks');

            function toggleSubmenu(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var isOpen = item.classList.contains('open');
                
                // Close other open submenus
                categoryItems.forEach(function(otherItem) {
                    if(otherItem !== item) {
                        otherItem.classList.remove('open');
                        var otherSub = otherItem.querySelector('.drawer-sublinks');
                        if(otherSub) otherSub.classList.remove('show');
                    }
                });

                item.classList.toggle('open');
                if (sublinks) sublinks.classList.toggle('show');
            }

            // Make the entire link toggle the submenu on mobile
            toggleLink.addEventListener('click', toggleSubmenu);
        });

        // Mobile search toggle
        if(searchToggle && searchBar) {
            searchToggle.addEventListener('click', function() {
                var isHidden = searchBar.hasAttribute('hidden');
                if(isHidden) {
                    searchBar.removeAttribute('hidden');
                    searchBar.querySelector('input').focus();
                } else {
                    searchBar.setAttribute('hidden', '');
                }
            });
        }
    })();
</script>

@include('ecommerce.components.offcanvas-cart')