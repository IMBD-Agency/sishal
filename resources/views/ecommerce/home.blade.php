@extends('ecommerce.master')

@section('main-section')
    <!-- Home: Left Category + Right Banner Slider -->
    <section class="home-hero py-4">
        <div class="container">
            <div class="row g-3 align-items-stretch">
                <!-- Left Category Menu -->
                <div class="col-lg-3">
                    <div class="category-menu">
                        <div class="menu-header">Category Menu</div>
                        <ul class="menu-list">
                            @foreach(($categories ?? []) as $category)
                            <li class="menu-item">
                                <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="menu-link">
                                    <span class="menu-icon menu-thumb">
                                        @if(!empty($category->image))
                                            <img src="{{ asset($category->image) }}" alt="{{ $category->name }}">
                                        @else
                                            <img src="https://via.placeholder.com/36x36?text=\u00A0" alt="placeholder">
                                        @endif
                                    </span>
                                    <span class="menu-text">{{ $category->name }}</span>
                                    @php $children = $category->children ?? ($category->subcategories ?? collect()); @endphp
                                    @if(!empty($children) && count($children))
                                        <span class="arrow">›</span>
                                    @endif
                                </a>
                                @if(!empty($children) && count($children))
                                <div class="submenu">
                                    @foreach($children as $child)
                                    <a href="{{ route('product.archive') }}?category={{ $child->slug }}" class="submenu-link">
                                        <span class="submenu-thumb">
                                            @if(!empty($child->image))
                                                <img src="{{ asset($child->image) }}" alt="{{ $child->name }}">
                                            @else
                                                <img src="https://via.placeholder.com/28x28?text=\u00A0" alt="placeholder">
                                            @endif
                                        </span>
                                        <span class="submenu-text">{{ $child->name }}</span>
                                    </a>
                                    @endforeach
                                </div>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Right Banner Slider (admin managed) -->
                <div class="col-lg-9">
                    <div id="heroSplide" class="splide splide-hero" aria-label="Hero Banners">
                        <div class="splide__track">
                            <ul class="splide__list">
                                @if(!empty($banners) && count($banners) > 0)
                                    @foreach($banners as $banner)
                                    <li class="splide__slide">
                                        @if($banner->link_url)
                                            <a href="{{ $banner->link_url }}" target="_blank" class="d-block w-100 h-100">
                                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="hero-slide-img">
                                            </a>
                                        @else
                                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="hero-slide-img">
                                        @endif
                                        @if($banner->title || $banner->description)
                                        <div class="hero-caption d-none d-md-block">
                                            @if($banner->title)
                                            <h5>{{ $banner->title }}</h5>
                                            @endif
                                            @if($banner->description)
                                            <p>{{ $banner->description }}</p>
                                            @endif
                                            @if($banner->link_url && $banner->link_text)
                                            <a href="{{ $banner->link_url }}" target="_blank" class="btn btn-primary btn-sm">{{ $banner->link_text }}</a>
                                            @endif
                                        </div>
                                        @endif
                                    </li>
                                    @endforeach
                                @else
                                    <li class="splide__slide"><img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1800&auto=format&fit=crop" alt="fallback-1" class="hero-slide-img"></li>
                                    <li class="splide__slide"><img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=1800&auto=format&fit=crop" alt="fallback-2" class="hero-slide-img"></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Categories (Splide carousel) -->
    <section class="popular-categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Categories</h2>
                <a href="{{ route('categories') }}" class="view-more-btn">View More</a>
            </div>

            <div id="categorySplide" class="splide" aria-label="Category Carousel">
                <div class="splide__track">
                    <ul class="splide__list" role="listbox">
                        @foreach ($featuredCategories as $category)
                        <li class="splide__slide" role="option">
                            <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="category-chip d-block">
                                <div class="chip-thumb full-bg">
                                    <img src="{{ asset($category->image) }}" alt="{{ $category->name }}">
                                </div>
                                <div class="chip-title badge-title">{{ strtoupper($category->name) }}</div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Selling Products -->
    <section class="top-products">
        <div class="container">
            <div class="d-flex justify-content-between mb-4">
                <div class="">
                    <h2 class="section-title mb-0 text-start mb-2">Top Selling Products</h2>
                    
                </div>
                <a href="{{ route('product.archive') }}" class="btn btn-outline-custom">View All Products</a>
            </div>

            <div id="mostSoldSplide" class="splide" aria-label="Top Selling Products" style="visibility:hidden;">
                <div class="splide__track">
                    <ul class="splide__list" id="mostSoldSplideList">
                        <!-- Slides will be injected here -->
                    </ul>
                </div>
            </div>
            <div id="mostSoldFallback" class="row product-grid">
                <div class="col-12 text-center text-muted">Loading top selling products...</div>
            </div>
        </div>
    </section>

    <!-- New Arrivals Products (same style as Top Selling) -->
    <section class="top-products">
        <div class="container">
            <div class="d-flex justify-content-between mb-4">
                <div class="">
                    <h2 class="section-title mb-0 text-start mb-2">New Arrivals</h2>
                </div>
                <a href="{{ route('product.archive', ['sort' => 'newest']) }}" class="btn btn-outline-custom">View All New</a>
            </div>

            <div class="row product-grid" id="newArrivalsProductsContainer">
                <!-- Products will be loaded here by jQuery -->
            </div>
        </div>
    </section>

	<!-- Latest Vlogs Carousel -->
	<section class="home-vlogs">
		<div class="container">
			<div class="d-flex justify-content-between mb-5">
				<div>
					<h2 class="section-title mb-0 text-start">Latest Vlogs</h2>
					<p class="section-subtitle text-start mb-0">Watch our latest tips and installs</p>
				</div>
				<a href="{{ route('vlogs') }}" class="btn btn-outline-custom">View All Vlogs</a>
			</div>

			@if(!empty($vlogs) && count($vlogs))
				<div class="vlog-carousel position-relative">
					<button class="vlog-nav prev" id="vlogPrev" type="button" aria-label="Previous">
						<i class="fa fa-chevron-left"></i>
					</button>
					<div class="vlog-scroller" id="vlogScroller">
						@foreach($vlogs as $vlog)
							<div class="vlog-item">
								<div class="card h-100 border-0 shadow-sm vlog-card">
									<div class="ratio ratio-16x9">
										{!! $vlog->frame_code !!}
									</div>
								</div>
							</div>
						@endforeach
					</div>
					<button class="vlog-nav next" id="vlogNext" type="button" aria-label="Next">
						<i class="fa fa-chevron-right"></i>
					</button>
				</div>
			@else
				<div class="text-center text-muted">No vlogs available.</div>
			@endif
		</div>
	</section>


    

	<!-- Vlogs -->


    <div id="toast-container"
        style="position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;">
    </div>

    <!-- Video Modal -->
    <!-- <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width:90vw;">
            <div class="modal-content" style="height:90vh;">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 d-flex justify-content-center align-items-center"
                    style="height:calc(90vh - 56px);">
                    <div class="ratio ratio-16x9 w-100 h-100">
                        <iframe id="youtubeVideo" width="100%" height="100%" src="" title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <style>
        /* Categories - Splide adjustments */
        .popular-categories #categorySplide .splide__list { list-style: none !important; margin: 0; padding: 0; }
        .popular-categories #categorySplide .splide__slide { list-style: none !important; padding: 0 8px; }
        /* Ensure Splide arrows render above content */
        .popular-categories #categorySplide .splide__arrows { z-index: 3; }
        /* Hero splide */
        .splide-hero .splide__list, .splide-hero .splide__slide { height: 100%; }
        .splide-hero { border-radius: 16px; overflow: hidden; }
        .hero-slide-img { width: 100%; height: 460px; object-fit: cover; display: block; }
        @media (max-width: 767.98px) { .hero-slide-img { height: 300px; } }
        .splide-hero .splide__arrow { background: rgba(255,255,255,0.9); width: 40px; height: 40px; box-shadow: 0 4px 18px rgba(0,0,0,0.12); }
        .splide-hero .splide__pagination__page.is-active { background: #111827; }
        .hero-caption { position: absolute; left: 24px; bottom: 24px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        /* Top selling carousel spacing */
        #mostSoldSplide .splide__slide { padding: 0 4px; }
        /* Make top selling cards a bit smaller and ensure icons/ratings show */
        #mostSoldSplide .product-card { padding: 0 !important; border-radius: 14px; background: #fff; box-shadow: 0 3px 14px rgba(0,0,0,0.06); }
        #mostSoldSplide .product-image-container { position: relative; height: 300px; border-radius: 12px; overflow: hidden; }
        #mostSoldSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; }
        #mostSoldSplide .rating-badge { position: absolute; left: 10px; bottom: 10px; background: rgba(255,255,255,0.95); border-radius: 8px; padding: 4px 8px; font-size: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); display: flex; align-items: center; gap: 6px; }
        #mostSoldSplide .rating-badge .star { color: #f59e0b; }
        #mostSoldSplide .product-title { font-size: 16px; line-height: 1.35; margin-top: 10px; margin-bottom: 4px; color: #4b5563; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        #mostSoldSplide .price { font-size: 15px; font-weight: 700; margin-top: 2px; }
        #mostSoldSplide .price .old { font-size: 12px; color: #6b7280; text-decoration: line-through; margin-left: 8px; font-weight: 500; }
        #mostSoldSplide .wishlist-btn { position: absolute; top: 10px; right: 10px; z-index: 2; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        #mostSoldSplide .wishlist-btn i { pointer-events: none; font-size: 16px; }
        #mostSoldSplide .product-meta .stars i { color: #fbbf24; margin-right: 2px; font-size: 12px; }
        #mostSoldSplide .product-actions { margin-top: 8px; }
        #mostSoldSplide .btn-add-cart { padding: 6px 10px; font-size: 12px; }
        @media (max-width: 991.98px) { #mostSoldSplide .product-image-container { height: 180px; } }
        /* remove hover border if any framework styles apply */
        #mostSoldSplide .product-card.no-hover-border:hover { border-color: transparent !important; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
        /* Category card styles - full poster with floating badge title */
        .popular-categories #categorySplide .splide__slide > a.category-chip { position: relative; display: block; width: 100%; height: 250px; border-radius: 14px; overflow: hidden; text-decoration: none; border: none; }
        .popular-categories .chip-thumb.full-bg { position: absolute; inset: 0; width: 100% !important; height: 100% !important; border-radius: inherit !important; overflow: hidden; display: block; }
        .popular-categories .chip-thumb.full-bg img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block; transform: scale(1.01); transition: transform .35s ease; }
        .popular-categories #categorySplide .splide__slide > a.category-chip:hover .chip-thumb.full-bg img { transform: scale(1.05); }
        .popular-categories .badge-title { position: absolute; left: 50%; transform: translateX(-50%); bottom: 14px; background: #fff; color: #111827; font-weight: 800; letter-spacing: .08em; font-size: 14px; padding: 10px 16px; border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,0.16); border: 1px solid rgba(17,24,39,0.06); display: inline-block; white-space: nowrap; max-width: 90%; }
        @media (max-width: 767.98px) { .popular-categories #categorySplide .splide__slide > a.category-chip { height: 180px; } .popular-categories .badge-title { font-size: 12px; padding: 8px 12px; left: 50%; transform: translateX(-50%); bottom: 12px; max-width: 92%; } }
        .popular-categories #categorySplide .splide__list { align-items: stretch; }
        .popular-categories .category-chip { text-align: center; }
		/* Scope styles to vlogs section only */
		@media (min-width: 1200px) {
			.home-vlogs > .container {
				max-width: none;
				width: 80%;
			}
		}

		.home-vlogs { padding: 40px 0; }

		.vlog-scroller {
			display: grid;
			grid-auto-flow: column;
			grid-auto-columns: calc(50% - 12px);
			gap: 24px;
			overflow-x: hidden;
			overflow-y: hidden;
			scroll-snap-type: x mandatory;
			scroll-behavior: smooth;
			padding-bottom: 4px;
			-webkit-overflow-scrolling: touch;
			touch-action: none;
		}
		/* Hide native scrollbar */
		.vlog-scroller { scrollbar-width: none; }
		.vlog-scroller::-webkit-scrollbar { display: none; height: 0; }
		.vlog-item { scroll-snap-align: start; }
		/* While dragging, let scroll take precedence over iframes */
		.vlog-scroller.dragging iframe { pointer-events: none !important; }
		@media (max-width: 991.98px) {
			.vlog-scroller { grid-auto-columns: 100%; gap: 16px; }
			.vlog-nav.prev { left: 0; }
			.vlog-nav.next { right: 0; }
		}
		/* Allow interacting with YouTube controls */
		.home-vlogs .ratio iframe { pointer-events: auto; }
		.vlog-carousel .vlog-nav {
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			width: 40px; height: 40px;
			border-radius: 50%;
			border: none;
			background: rgba(255,255,255,0.95);
			box-shadow: 0 4px 16px rgba(0,0,0,0.12);
			color: #333;
			display: flex; align-items: center; justify-content: center;
			cursor: pointer;
			z-index: 2;
		}
		.vlog-carousel .vlog-nav.prev { left: -20px; }
		.vlog-carousel .vlog-nav.next { right: -20px; }
		@media (max-width: 991.98px) {
			.vlog-carousel .vlog-nav.prev { left: 0; }
			.vlog-carousel .vlog-nav.next { right: 0; }
		}
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
            z-index: 9999;
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

        .wishlist-btn i.fa-heart.active {
            color: #e53935 !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Categories carousel now uses Splide (initialized in app.js)
        // Arrow-only navigation: scroll by viewport width (2 items desktop, 1 mobile)
        (function(){
            const scroller = document.getElementById('vlogScroller');
            const prev = document.getElementById('vlogPrev');
            const next = document.getElementById('vlogNext');
            if (!scroller || !prev || !next) return;

            const step = () => {
                const style = getComputedStyle(scroller);
                const gap = parseFloat(style.gap) || 0;
                const first = scroller.querySelector('.vlog-item');
                if (!first) return 0;
                const itemWidth = first.getBoundingClientRect().width + gap;
                const perView = window.innerWidth >= 992 ? 2 : 1;
                return itemWidth * perView;
            };

            const updateButtons = () => {
                // enable when there is hidden content on either side
                prev.disabled = scroller.scrollLeft <= 0;
                const max = scroller.scrollWidth - scroller.clientWidth - 1;
                next.disabled = scroller.scrollLeft >= max;
            };

            prev.addEventListener('click', () => { scroller.scrollBy({ left: -step(), behavior: 'smooth' }); setTimeout(updateButtons, 350); });
            next.addEventListener('click', () => { scroller.scrollBy({ left: step(), behavior: 'smooth' }); setTimeout(updateButtons, 350); });

            window.addEventListener('resize', updateButtons);
            window.addEventListener('load', updateButtons);
            updateButtons();
        })();
        window.showToast = function (message, type = 'success') {
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
        $(function () {
            $.get('/api/products/most-sold', function (products) {
                var container = $('#mostSoldProductsContainer');
                container.empty();
                if (!products.length) {
                    container.append('<div class="col-12 text-center text-muted">No products found.</div>');
                    return;
                }
                products.forEach(function (product) {
                    const rating = product.avg_rating ?? product.rating ?? 0;
                    const price = parseFloat(product.price || 0).toFixed(2);
                    const image = product.image ? product.image : '/default-product.png';
                    container.append(`
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="product-card position-relative" data-href="/product/${product.slug}">
                                    <button class="wishlist-btn${product.is_wishlisted ? ' active' : ''}" data-product-id="${product.id}">
                                        <i class="${product.is_wishlisted ? 'fas text-danger' : 'far'} fa-heart"></i>
                                    </button>
                                    <div class="product-image-container">
                                        <img src="${image}" class="product-image" alt="${product.name}">
                                    </div>
                                    <div class="product-info">
                                        <a href="/product/${product.slug}" style="text-decoration: none" class="product-title">${product.name}</a>
                                        <div class=\"product-meta\">
                                            <div class=\"stars\" aria-label=\"${rating} out of 5\">${Array.from({length:5}).map((_,i)=>`<i class=\\\"fa${i < Math.round(rating) ? 's' : 'r'} fa-star\\\"></i>`).join('')}</div>
                                        </div>
                                        <div class="price">${price}৳</div>
                                        <div class="d-flex justify-content-between align-items-center gap-2 product-actions">
                                            <button class="btn-add-cart" data-product-id="${product.id}" data-product-name="${product.name}" data-has-stock="${product.has_stock ? 'true' : 'false'}" ${!product.has_stock ? 'disabled' : ''}><svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" fill="#fff" width="14" height="14">
                                <path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z"></path>
                                <circle cx="7" cy="22" r="2"></circle>
                                <circle cx="17" cy="22" r="2"></circle>
                            </svg> ${product.has_stock ? 'Add to Cart' : 'Out of Stock'}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                });
            }).fail(function () {
                $('#mostSoldProductsContainer').html('<div class="col-12 text-center text-danger">Failed to load products.</div>');
            });

            // Load New Arrivals
            $.get('/api/products/new-arrivals', function (products) {
                var container = $('#newArrivalsProductsContainer');
                container.empty();
                if (!products.length) {
                    container.append('<div class="col-12 text-center text-muted">No products found.</div>');
                    return;
                }
                products.forEach(function (product) {
                    const rating = product.avg_rating ?? product.rating ?? 0;
                    const price = parseFloat(product.price || 0).toFixed(2);
                    const image = product.image ? product.image : '/default-product.png';
                    container.append(`
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="product-card position-relative" data-href="/product/${product.slug}">
                                    <button class="wishlist-btn${product.is_wishlisted ? ' active' : ''}" data-product-id="${product.id}">
                                        <i class="${product.is_wishlisted ? 'fas text-danger' : 'far'} fa-heart"></i>
                                    </button>
                                    <div class="product-image-container">
                                        <img src="${image}" class="product-image" alt="${product.name}">
                                    </div>
                                    <div class="product-info">
                                        <a href="/product/${product.slug}" style="text-decoration: none" class="product-title">${product.name}</a>
                                        <div class=\"product-meta\">
                                            <div class=\"stars\" aria-label=\"${rating} out of 5\">${Array.from({length:5}).map((_,i)=>`<i class=\\\"fa${i < Math.round(rating) ? 's' : 'r'} fa-star\\\"></i>`).join('')}</div>
                                        </div>
                                        <div class="price">${price}৳</div>
                                        <div class="d-flex justify-content-between align-items-center gap-2 product-actions">
                                            <button class="btn-add-cart" data-product-id="${product.id}" data-product-name="${product.name}" data-has-stock="${product.has_stock ? 'true' : 'false'}" ${!product.has_stock ? 'disabled' : ''}><svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" fill="#fff" width="14" height="14">
                                <path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z"></path>
                                <circle cx="7" cy="22" r="2"></circle>
                                <circle cx="17" cy="22" r="2"></circle>
                            </svg> ${product.has_stock ? 'Add to Cart' : 'Out of Stock'}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                });
            }).fail(function () {
                $('#newArrivalsProductsContainer').html('<div class="col-12 text-center text-danger">Failed to load products.</div>');
            });

            // Make product-card clickable but ignore clicks from interactive children
            $(document).on('click', '.product-card', function (e) {
                const interactive = ['A', 'BUTTON', 'SVG', 'PATH', 'FORM', 'INPUT', 'SELECT', 'TEXTAREA', 'LABEL'];
                if (interactive.includes(e.target.tagName)) return;
                const href = $(this).data('href');
                if (href) window.location.href = href;
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
                            showToast(response.message, 'success');
                        }
                    }
                });
            });
        });

        // Safe modal initialization utility
        function safeModalInit(modalId, options = {}) {
            const modalElement = document.getElementById(modalId);
            if (!modalElement) {
                console.warn(`Modal element with id '${modalId}' not found`);
                return null;
            }
            
            try {
                return new bootstrap.Modal(modalElement, options);
            } catch (error) {
                console.error(`Failed to initialize modal '${modalId}':`, error);
                return null;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var playBtn = document.getElementById('playVideoBtn');
            var videoModalElement = document.getElementById('videoModal');
            var youtubeVideo = document.getElementById('youtubeVideo');
            var YOUTUBE_URL = 'https://www.youtube.com/embed/np0FD080?autoplay=1'; // Replace YOUR_VIDEO_ID

            // Only initialize modal if the elements exist
            if (playBtn && videoModalElement && youtubeVideo) {
                var videoModal = safeModalInit('videoModal');

                if (videoModal) {
                    playBtn.addEventListener('click', function () {
                        youtubeVideo.src = YOUTUBE_URL;
                        videoModal.show();
                    });
                    
                    videoModalElement.addEventListener('hidden.bs.modal', function () {
                        youtubeVideo.src = '';
                    });
                }
            } else {
                console.log('Video modal elements not found - modal functionality disabled');
            }
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
            z-index: 9999;
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

        .wishlist-btn i.fa-heart.active {
            color: #e53935 !important;
        }
    </style>
@endpush