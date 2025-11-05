<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                {{ $general_settings->top_text }}
            </div>
        </div>
    </div>
</div>

<!-- Header Section -->
<header class="modern-header">
    <div class="container">
        <div class="row align-items-center py-3">
            <!-- Logo -->
            <div class="col-lg-3 col-md-4">
                <a class="navbar-brand d-flex align-items-center" href="/">
                    <img src="{{ $general_settings && $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/default-logo.webp') }}" alt="{{ $general_settings->site_title ?? 'alicom' }}" class="logo-img" width="180" height="48" decoding="async" loading="eager">
                </a>
            </div>

            <!-- Search Bar -->
            <div class="col-lg-6 col-md-8">
                <form class="search-form" action="{{ route('search') }}" method="get">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Search product" name="search">
                        <button class="search-btn" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side Action Buttons -->
            <div class="col-lg-3 col-md-12">
                <div class="header-actions">
                    <!-- Mobile search toggle -->
                    <button class="action-btn mobile-search-toggle d-lg-none me-1" id="mobileSearchToggle" aria-controls="mobileSearchBar" aria-expanded="false" aria-label="Toggle search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                            <path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" fill="currentColor"/>
                        </svg>
                    </button>
                    <!-- Mobile menu toggle -->
                    <button class="action-btn mobile-menu-toggle d-lg-none" id="mobileMenuToggle" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                            <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <a href="{{ route('wishlist.index') }}" class="action-btn" title="Wishlist">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                            <path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Zm-3.585,18.4a2.973,2.973,0,0,1-3.83,0C4.947,16.006,2,11.87,2,8.967a4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,11,8.967a1,1,0,0,0,2,0,4.8,4.8,0,0,1,4.5-5.05A4.8,4.8,0,0,1,22,8.967C22,11.87,19.053,16.006,13.915,20.313Z" fill="currentColor"/>
                        </svg>
                        <span class="badge nav-wishlist-count">0</span>
                    </a>
                    
                    <a href="#" class="action-btn" onclick="openOffcanvasCart(); return false;" title="Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                            <path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z" fill="currentColor"/>
                            <circle cx="7" cy="22" r="2" fill="currentColor"/>
                            <circle cx="17" cy="22" r="2" fill="currentColor"/>
                        </svg>
                        <span class="badge nav-cart-count">0</span>
                    </a>
                    
                    <div class="dropdown">
                        <button class="action-btn login-btn" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                                <path d="M12.006,12.309c3.611-.021,5.555-1.971,5.622-5.671-.062-3.56-2.111-5.614-5.634-5.637-3.561,.022-5.622,2.17-5.622,5.637,0,3.571,2.062,5.651,5.634,5.672Zm-.012-9.309c2.437,.016,3.591,1.183,3.634,3.636-.047,2.559-1.133,3.657-3.622,3.672-2.495-.015-3.582-1.108-3.634-3.654,.05-2.511,1.171-3.639,3.622-3.654Z" fill="currentColor"/>
                                <path d="M11.994,13.661c-5.328,.034-8.195,2.911-8.291,8.322-.01,.552,.43,1.008,.982,1.018,.516-.019,1.007-.43,1.018-.982,.076-4.311,2.08-6.331,6.291-6.357,4.168,.027,6.23,2.106,6.304,6.356,.01,.546,.456,.983,1,.983h.018c.552-.01,.992-.465,.983-1.017-.092-5.333-3.036-8.288-8.304-8.322Z" fill="currentColor"/>
                            </svg>
                            <span class="login-text">
                                @auth
                                    {{ Auth::user()->first_name ?? 'User' }}
                                @else
                                    Login
                                @endauth
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            @guest
                                <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                                <li><a class="dropdown-item" href="{{ route('register') }}">Register</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Logout</button>
                                    </form>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main Navigation -->
<nav class="main-nav">
    <div class="container">
        <div class="row align-items-center g-0">
            <!-- Navigation Links -->
            <div class="col-12">
                <ul class="nav-links">
                    <li class="nav-item {{ request()->is('/') ? 'active' : '' }}"><a href="/" class="nav-link">HOME</a></li>
                    
                    <!-- Categories with Simple Dropdowns -->
                    @if(isset($nav_categories) && $nav_categories->count() > 0)
                        @foreach($nav_categories as $category)
                        <li class="nav-item nav-item-dropdown">
                            <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="nav-link">
                                {{ strtoupper($category->name) }}
                                @php $children = $category->children ?? ($category->subcategories ?? collect()); @endphp
                                @if($children->count() > 0)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="currentColor" style="margin-left: 4px; vertical-align: middle;"><path d="M12 15.5l-5-5h10z"/></svg>
                                @endif
                            </a>
                            @php $children = $category->children ?? ($category->subcategories ?? collect()); @endphp
                            @if($children->count() > 0)
                            <ul class="dropdown-menu simple-dropdown">
                                @foreach($children as $child)
                                <li>
                                    <a href="{{ route('product.archive') }}?category={{ $child->slug }}" class="subcategory-link">{{ strtoupper($child->name) }}</a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    @endif
                    
                    <li class="nav-item {{ request()->is('best-deal') ? 'active' : '' }}"><a href="{{ route('best.deal') }}" class="nav-link">BEST DEAL</a></li>
                    <li class="nav-item {{ request()->is('contact*') ? 'active' : '' }}"><a href="{{ route('contact') }}" class="nav-link">CONTACT</a></li>
                    @foreach($additional_pages as $page)
                    @if($page->positioned_at == 'navbar')
                    <li class="nav-item {{ request()->is('page/' . $page->slug) ? 'active' : '' }}"><a href="{{ route('additionalPage.show', $page->slug) }}" class="nav-link">{{ strtoupper($page->title) }}</a></li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>
        <!-- Mobile overlay & side drawer -->
        <div class="mobile-overlay d-lg-none" id="mobileOverlay" hidden></div>
        <aside class="mobile-drawer d-lg-none" id="mobileNav" aria-hidden="true" hidden>
            <div class="drawer-header">
                <span>Menu</span>
                <button class="drawer-close" id="mobileMenuClose" aria-label="Close menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="drawer-content">
                <a href="{{ auth()->check() ? route('profile.edit') : route('login') }}" class="drawer-login">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z" fill="currentColor"/></svg>
                    </span>
                    <span class="text">
                        @auth
                            {{ Auth::user()->first_name ?? 'User' }}
                        @else
                            Login
                        @endauth
                    </span>
                    <span class="chev">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path d="M9 18l6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </a>

                <div class="drawer-quick">
                    <a href="{{ route('wishlist.index') }}" class="quick-item">
                        <span class="qi-left">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path d="M17.5,1.917a6.4,6.4,0,0,0-5.5,3.3,6.4,6.4,0,0,0-5.5-3.3A6.8,6.8,0,0,0,0,8.967c0,4.547,4.786,9.513,8.8,12.88a4.974,4.974,0,0,0,6.4,0C19.214,18.48,24,13.514,24,8.967A6.8,6.8,0,0,0,17.5,1.917Z" fill="currentColor"/></svg>
                            <span>Wishlist</span>
                        </span>
                        <span class="qi-badge nav-wishlist-count">0</span>
                    </a>
                    <a href="#" onclick="openOffcanvasCart(); return false;" class="quick-item">
                        <span class="qi-left">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077Z" fill="currentColor"/></svg>
                            <span>My Cart</span>
                        </span>
                        <span class="qi-badge nav-cart-count">0</span>
                    </a>
                </div>

                <nav class="drawer-links">
                    <a href="/" class="drawer-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
                    
                    @if(isset($nav_categories) && $nav_categories->count() > 0)
                    <div class="drawer-categories">
                        <div class="drawer-category-header">Categories</div>
                        @foreach($nav_categories as $idx => $category)
                        @php $children = $category->children ?? ($category->subcategories ?? collect()); $hasChildren = $children->count() > 0; @endphp
                        <div class="drawer-cat{{ $hasChildren ? ' has-children' : '' }}">
                            <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="drawer-link drawer-category-link">
                                <span class="dc-text">{{ strtoupper($category->name) }}</span>
                            </a>
                            @if($hasChildren)
                            <button class="drawer-toggle" type="button" aria-label="Toggle subcategories" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <div class="drawer-subcategories" hidden>
                                @foreach($children as $child)
                                <a href="{{ route('product.archive') }}?category={{ $child->slug }}" class="drawer-link drawer-subcategory-link">
                                    {{ strtoupper($child->name) }}
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <!-- Products link removed for mobile drawer -->
                    <!-- Best Deal and Contact hidden on mobile drawer as requested -->
                        @foreach($additional_pages as $page)
                            @php $titleLower = strtolower($page->title ?? ''); @endphp
                            @if($page->positioned_at == 'navbar' && $titleLower !== 'products' && ($page->slug ?? '') !== 'products')
                            <a href="{{ route('additionalPage.show', $page->slug) }}" class="drawer-link {{ request()->is('page/' . $page->slug) ? 'active' : '' }}">{{ $page->title }}</a>
                            @endif
                        @endforeach
                </nav>
            </div>
                <!-- Slide-in subcategory panel -->
                <div class="mobile-subpanel" id="mobileSubpanel" hidden aria-hidden="true">
                    <div class="subpanel-header">
                        <button class="subpanel-back" id="mobileSubpanelBack" aria-label="Back">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="subpanel-title" id="mobileSubpanelTitle">Categories</div>
                    </div>
                    <div class="subpanel-body" id="mobileSubpanelBody"></div>
                </div>
            </aside>
    </div>
</nav>
<!-- end header-stack -->
<!-- Mobile search bar dropdown -->
<div class="mobile-searchbar d-lg-none" id="mobileSearchBar" hidden>
    <div class="container">
        <form class="mobile-search-form" action="{{ route('search') }}" method="get">
            <input type="text" class="mobile-search-input" placeholder="Search product" name="search">
            <button class="mobile-search-btn" type="submit" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" fill="currentColor"/></svg>
            </button>
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
        // Ensure overlay and drawer are top-level so they cover the whole site
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
            // Delay class application to next frame so transition plays smoothly
            requestAnimationFrame(function(){
                drawer.classList.add('open');
                overlay.classList.add('open');
                toggle && toggle.setAttribute('aria-expanded','true');
                document.documentElement.classList.add('mobile-lock');
            });
        }
        function closeDrawer(){
            if(!drawer || !overlay) return;
            drawer.classList.remove('open');
            overlay.classList.remove('open');
            toggle && toggle.setAttribute('aria-expanded','false');
            document.documentElement.classList.remove('mobile-lock');
            // hide after transition
            setTimeout(function(){
                drawer.setAttribute('hidden','');
                overlay.setAttribute('hidden','');
            }, 420);
        }
        if(toggle && drawer){
            toggle.addEventListener('click', function(){ openDrawer(); });
        }
        if(overlay){ overlay.addEventListener('click', function(){
            // If subpanel is open, close only the subpanel; otherwise close entire drawer
            var subPanelEl = document.getElementById('mobileSubpanel');
            if(subPanelEl && !subPanelEl.hasAttribute('hidden')){
                subPanelEl.classList.remove('open');
                setTimeout(function(){ subPanelEl.setAttribute('hidden',''); }, 300);
                return;
            }
            closeDrawer();
        }); }
        if(closeBtn){ closeBtn.addEventListener('click', function(){ closeDrawer(); }); }
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeDrawer(); } });

        // Close drawer slowly when any link or button inside is clicked
        if(drawer){
            drawer.addEventListener('click', function(e){
                // Back button inside subpanel should only close subpanel (not the drawer)
                var backBtn = e.target.closest('#mobileSubpanelBack');
                if(backBtn){
                    e.preventDefault();
                    e.stopPropagation();
                    var panelOnly = document.getElementById('mobileSubpanel');
                    if(panelOnly){
                        panelOnly.classList.remove('open');
                        setTimeout(function(){ panelOnly.setAttribute('hidden',''); }, 300);
                    }
                    return;
                }
                // Chevron should open the sliding subpanel (not expand inline)
                var toggleBtn = e.target.closest('.drawer-toggle');
                if(toggleBtn){
                    e.preventDefault();
                    var cat = toggleBtn.closest('.drawer-cat');
                    if(!cat) return;
                    var catLink = cat.querySelector('.drawer-category-link');
                    var title = catLink && catLink.querySelector('.dc-text') ? catLink.querySelector('.dc-text').textContent.trim() : (catLink ? catLink.textContent.trim() : '');
                    var sub = cat.querySelector('.drawer-subcategories');
                    var panel = document.getElementById('mobileSubpanel');
                    var panelBody = document.getElementById('mobileSubpanelBody');
                    var panelTitle = document.getElementById('mobileSubpanelTitle');
                    if(panel && panelBody && sub){
                        panelTitle && (panelTitle.textContent = title);
                        panelBody.innerHTML = sub.innerHTML;
                        panel.removeAttribute('hidden');
                        requestAnimationFrame(function(){ panel.classList.add('open'); });
                    }
                    return;
                }
                // Open subpanel when tapping a category that has children
                var catLink = e.target.closest('.drawer-category-link');
                if(catLink){
                    var catWrap = catLink.closest('.drawer-cat');
                    if(catWrap && catWrap.classList.contains('has-children')){
                        e.preventDefault();
                        var title = catLink.querySelector('.dc-text') ? catLink.querySelector('.dc-text').textContent.trim() : catLink.textContent.trim();
                        var sub = catWrap.querySelector('.drawer-subcategories');
                        var panel = document.getElementById('mobileSubpanel');
                        var panelBody = document.getElementById('mobileSubpanelBody');
                        var panelTitle = document.getElementById('mobileSubpanelTitle');
                        if(panel && panelBody && sub){
                            panelTitle && (panelTitle.textContent = title);
                            panelBody.innerHTML = sub.innerHTML;
                            panel.removeAttribute('hidden');
                            requestAnimationFrame(function(){ panel.classList.add('open'); });
                            return;
                        }
                    }
                }
                var interactive = e.target.closest('a, button');
                if(interactive){ closeDrawer(); }
            });
        }

        // Subpanel back handler
        var subBack = document.getElementById('mobileSubpanelBack');
        var subPanel = document.getElementById('mobileSubpanel');
        if(subBack && subPanel){
            subBack.addEventListener('click', function(){
                subPanel.classList.remove('open');
                setTimeout(function(){ subPanel.setAttribute('hidden',''); }, 300);
            });
        }

        // Close subpanel when clicking outside of it
        document.addEventListener('click', function(e){
            if(!subPanel || subPanel.hasAttribute('hidden')) return;
            if(e.target.closest('#mobileSubpanel')) return;
            // clicks on toggle or category link should be handled by drawer listener
            if(e.target.closest('.drawer-toggle') || e.target.closest('.drawer-category-link')) return;
            subPanel.classList.remove('open');
            setTimeout(function(){ subPanel.setAttribute('hidden',''); }, 300);
        });
        // Close on Escape as well
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape' && subPanel && !subPanel.hasAttribute('hidden')){
                subPanel.classList.remove('open');
                setTimeout(function(){ subPanel.setAttribute('hidden',''); }, 300);
            }
        });

        // Mobile search toggle with focus and accessibility
        function openSearchBar(){
            if(!searchBar) return;
            searchBar.removeAttribute('hidden');
            if(!searchBar.classList.contains('open')){
                searchBar.classList.add('open');
            }
            if(searchToggle){ searchToggle.setAttribute('aria-expanded','true'); }
            // Focus input on next frame to ensure visibility
            requestAnimationFrame(function(){
                var input = searchBar.querySelector('.mobile-search-input');
                if(input){ try { input.focus({ preventScroll: true }); } catch(_) { input.focus(); }
                    if(input.select) { input.select(); }
                }
            });
            // Keep header in view on small screens
            try { window.scrollTo({ top: 0, behavior: 'smooth' }); } catch(_) { window.scrollTo(0,0); }
        }
        function closeSearchBar(){
            if(!searchBar) return;
            searchBar.classList.remove('open');
            if(searchToggle){ searchToggle.setAttribute('aria-expanded','false'); }
            // Remove setTimeout to prevent layout shifts
            searchBar.setAttribute('hidden','');
        }
        if(searchToggle && searchBar){
            searchToggle.addEventListener('click', function(){
                if(searchBar.classList.contains('open')){ closeSearchBar(); } else { openSearchBar(); }
            });
        }
        // Close search when clicking outside
        document.addEventListener('click', function(e){
            if(!searchBar || !searchBar.classList.contains('open')) return;
            if(e.target.closest('#mobileSearchBar') || e.target.closest('#mobileSearchToggle')) return;
            closeSearchBar();
        });
        // Close on Escape
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeSearchBar(); } });
    })();
</script>
<style>
/* Navigation Styling - Clean and Simple Like Reference */
.main-nav {
    background: #fff;
}

.main-nav .container {
    padding: 0;
    max-width: 100%;
}

.main-nav .row {
    margin: 0;
}

.main-nav .nav-links {
    display: flex !important;
    align-items: center;
    justify-content: center !important;
    gap: 0 !important;
    list-style: none;
    margin: 0 !important;
    padding: 0 !important;
    width: 100%;
    flex-wrap: nowrap;
}

.main-nav .nav-item {
    margin: 0 !important;
    position: relative;
    flex-shrink: 0;
}

.main-nav .nav-link {
    display: flex !important;
    align-items: center;
    justify-content: center;
    padding: 16px 20px !important;
    text-decoration: none;
    color: #374151 !important;
    font-weight: 500;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.2s ease;
    white-space: nowrap;
    position: relative;
}

.main-nav .nav-link:hover {
    color: #059669 !important;
}

.main-nav .nav-item.active .nav-link {
    color: #059669 !important;
    border-bottom: none !important;
}

.main-nav .nav-item.active .nav-link::after,
.main-nav .nav-link::after {
    display: none !important;
    content: none !important;
    border-bottom: none !important;
    background: none !important;
    height: 0 !important;
}

.main-nav .nav-link svg {
    margin-left: 6px;
    transition: transform 0.2s ease;
    flex-shrink: 0;
}

.main-nav .nav-item-dropdown:hover .nav-link svg {
    transform: rotate(180deg);
}

@media (max-width: 992px) {
    .main-nav .nav-links, .main-nav .nav-category-label { display: none !important; }
}

/* Simple Category Dropdown Styles - Like Reference Image */
.main-nav .nav-item-dropdown {
    position: relative;
}

.main-nav .nav-item-dropdown:hover .nav-link {
    color: #059669 !important;
}

.main-nav .nav-item-dropdown .simple-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    background: #fff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 0;
    margin-top: 0;
    z-index: 1000;
    display: none;
    list-style: none;
}

.main-nav .nav-item-dropdown:hover .simple-dropdown {
    display: block;
}

.main-nav .nav-item-dropdown .simple-dropdown:hover {
    display: block;
}

.main-nav .nav-item-dropdown .simple-dropdown li {
    margin: 0;
    padding: 0;
    border-bottom: 1px dotted #e5e7eb;
}

.main-nav .nav-item-dropdown .simple-dropdown li:last-child {
    border-bottom: none;
}

.main-nav .nav-item-dropdown .simple-dropdown .subcategory-link {
    display: block;
    padding: 12px 16px;
    text-decoration: none;
    color: #374151;
    font-size: 13px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.2s ease;
}

.main-nav .nav-item-dropdown .simple-dropdown .subcategory-link:hover {
    background: #f9fafb;
    color: #059669;
}

/* Mobile Drawer Categories */
.drawer-categories {
    margin: 12px 0 4px 0;
}

.drawer-category-header {
    display: none; /* hide 'Categories' title on mobile */
}

.drawer-category-link {
    padding: 12px 16px 12px 20px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .3px;
}

.drawer-cat { position: relative; }
.drawer-cat.has-children .drawer-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; background: transparent; color: #9ca3af; border-radius: 6px;
}
.drawer-cat.open .drawer-toggle { color: #059669; }
.drawer-cat .drawer-toggle:hover { background: #f3f4f6; }
.drawer-cat.open .drawer-toggle svg { transform: rotate(90deg); transition: transform .2s ease; }
.drawer-subcategories { padding-left: 0; margin-left: 0; border-left: 2px solid #f3f4f6; }

.drawer-subcategories {
    padding-left: 6px;
    margin: 0 0 6px 0;
    display: none !important; /* never open inline on mobile */
}

.drawer-subcategory-link {
    padding: 10px 16px 10px 28px;
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .3px;
}

/* Drawer link visual improvements */
.drawer-links .drawer-link {
    display: block;
    padding: 12px 16px;
    color: #374151;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
    font-size: 13px;
}
.drawer-links .drawer-link:last-child { border-bottom: none; }
.drawer-links .drawer-link:hover { background: #f9fafb; color: #059669; }
.drawer-links .drawer-link.active { color: #059669; }

/* Subpanel styles */
.mobile-subpanel {
    position: fixed; inset: 0 0 0 auto; width: 100%; max-width: 88%; background: #fff; z-index: 17000;
    transform: translateX(100%); transition: transform .3s ease;
    box-shadow: -12px 0 30px rgba(0,0,0,.08);
}
.mobile-subpanel.open { transform: translateX(0); }
.mobile-subpanel .subpanel-header { display: flex; align-items: center; gap: 8px; padding: 12px 14px; border-bottom: 1px solid #e5e7eb; }
.mobile-subpanel .subpanel-title { font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: .3px; }
.mobile-subpanel .subpanel-body { padding: 6px 0; }
.mobile-subpanel .subpanel-body .drawer-link { border-bottom: none; font-size: 13px; }
.subpanel-back { border: none; background: transparent; padding: 6px; border-radius: 6px; color: #6b7280; }
.subpanel-back:hover { background: #f3f4f6; }

@media (max-width: 991.98px) {
    .main-nav .nav-item-dropdown .simple-dropdown {
        position: static;
        display: block;
        box-shadow: none;
        border: none;
        margin-top: 0;
    }
    
    .main-nav .nav-item-dropdown .simple-dropdown li {
        border-bottom: 1px solid #e5e7eb;
    }
}
</style>
@include('ecommerce.components.offcanvas-cart')