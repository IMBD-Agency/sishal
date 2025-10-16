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
            <div class="section-header section-header--fancy">
                <h2 class="section-title">Categories</h2>
                <a href="{{ route('categories') }}" class="section-see-all">View All</a>
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
            <div class="section-header section-header--fancy">
                <h2 class="section-title ">Top Selling Products</h2>
                <a href="{{ route('product.archive') }}" class="section-see-all">View All</a>
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
  
    <!-- New Arrivals Products (carousel like Top Selling) -->
    <section class="top-products">
        <div class="container">
            <div class="section-header section-header--fancy">
                <h2 class="section-title">New Arrivals</h2>
                <a href="{{ route('product.archive', ['sort' => 'newest']) }}" class="section-see-all">View All</a>
            </div>

            <div id="newArrivalsSplide" class="splide" aria-label="New Arrivals" style="visibility:hidden;">
                <div class="splide__track">
                    <ul class="splide__list" id="newArrivalsSplideList">
                        <!-- Slides will be injected here -->
                    </ul>
                </div>
            </div>
            <div id="newArrivalsFallback" class="row product-grid">
                <div class="col-12 text-center text-muted">Loading new arrivals...</div>
            </div>
        </div>
    </section>

	<!-- Latest Vlogs Carousel (Splide) -->
	<section class="home-vlogs">
		<div class="container">
			<div class="d-flex justify-content-between mb-5">
				<div>
					<h2 class="section-title mb-0 text-start">Collections </h2>
					<!-- <p class="section-subtitle text-start mb-0">Watch our latest Fashion & Lifestyle Vlogs</p> -->
				</div>
			</div>
			
			@if(!empty($vlogs) && count($vlogs))
				<div id="vlogSplide" class="splide" aria-label="Latest Vlogs">
					<div class="splide__track">
						<ul class="splide__list">
							@foreach($vlogs as $vlog)
								<li class="splide__slide">
									<div class="card h-100 border-0 shadow-sm vlog-card">
										<div class="ratio ratio-16x9">
											{!! $vlog->frame_code !!}
										</div>
									</div>
								</li>
							@endforeach
						</ul>
					</div>
				</div>
			@else
				<div class="text-center text-muted">No vlogs available.</div>
			@endif
		</div>
	</section>

    

	<!-- Vlogs -->

    <!-- Best Deals (carousel, under vlogs) -->
    @if(!empty($bestDealProducts) && count($bestDealProducts))
    <section class="top-products">
        <div class="container">
            <div class="section-header section-header--fancy">
                <h2 class="section-title">Best Deals</h2>
                <a href="{{ route('best.deal') }}" class="section-see-all">View All</a>
            </div>

            <div id="bestDealsSplide" class="splide" aria-label="Best Deals" style="visibility:hidden;">
                <div class="splide__track">
                    <ul class="splide__list" id="bestDealsSplideList">
                        @foreach($bestDealProducts as $product)
                        <li class="splide__slide">
                            <div class="product-card position-relative no-hover-border" data-href="{{ route('product.details', $product->slug) }}">
                                <button class="wishlist-btn {{ $product->is_wishlisted ? ' active' : '' }}" data-product-id="{{ $product->id }}" title="{{ $product->is_wishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}" type="button">
                                    <i class="{{ $product->is_wishlisted ? 'fas text-danger' : 'far' }} fa-heart"></i>
                                </button>
                                <div class="product-image-container">
                                    <img src="{{ $product->image ?: '/static/default-product.png' }}" class="product-image" alt="{{ $product->name }}">
                                    @php $avg = $product->averageRating(); $reviews = $product->totalReviews(); @endphp
                                    @if($avg > 0)
                                    <div class="rating-badge">
                                        <span>{{ number_format($avg,1) }}</span>
                                        <i class="fas fa-star star"></i>
                                        <span>| {{ $reviews }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="product-info">
                                    <a href="{{ route('product.details', $product->slug) }}" style="text-decoration: none" class="product-title" title="{{ $product->name }}">{{ $product->name }}</a>
                                    <div class="price">
                                        @php $price = (float)($product->price ?? 0); $discount = (float)($product->discount ?? 0); $final = $discount > 0 ? $discount : $price; @endphp
                                        @if($discount > 0 && $discount < $price)
                                            <span class="fw-bold text-primary">{{ number_format($final,2) }}৳</span>
                                            <span class="old">{{ number_format($price,2) }}৳</span>
                                        @else
                                            <span class="fw-bold text-primary">{{ number_format($final,2) }}৳</span>
                                        @endif
                                    </div>
                                    @php $hasStock = $product->hasStock(); @endphp
                                    <div class="d-flex justify-content-between align-items-center gap-2 product-actions">
                                        <button class="btn-add-cart {{ !$hasStock ? 'disabled' : '' }}" 
                                                data-product-id="{{ $product->id }}" 
                                                data-product-name="{{ $product->name }}" 
                                                data-has-stock="{{ $hasStock ? 'true' : 'false' }}" 
                                                {{ !$hasStock ? 'disabled title="Out of stock"' : 'title="Add to cart"' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" fill="#fff" width="14" height="14">
                                                <path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z"></path>
                                                <circle cx="7" cy="22" r="2"></circle>
                                                <circle cx="17" cy="22" r="2"></circle>
                                            </svg> 
                                            {{ $hasStock ? 'Add to Cart' : 'Out of Stock' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div id="bestDealsFallback" class="row product-grid">
                <div class="col-12 text-center text-muted">Loading best deals...</div>
            </div>
        </div>
    </section>
    @endif

    
    @if(!empty($vlogBottomBanners) && count($vlogBottomBanners))
    @php
        $b1 = $vlogBottomBanners[0] ?? null;
        $b2 = $vlogBottomBanners[1] ?? null;
        $b3 = $vlogBottomBanners[2] ?? null;
    @endphp
    <section class="vlog-banners py-4">
        <div class="container">
            <div class="row g-3 align-items-stretch">
                @if($b1)
                <div class="col-lg-8">
                    @if($b1->link_url)
                        <a href="{{ $b1->link_url }}" target="_blank" class="d-block h-100">
                            <img src="{{ $b1->image_url }}" alt="{{ $b1->title }}" class="img-fluid rounded w-100" style="height:100%;object-fit:cover;">
                        </a>
                    @else
                        <img src="{{ $b1->image_url }}" alt="{{ $b1->title }}" class="img-fluid rounded w-100" style="height:100%;object-fit:cover;">
                    @endif
                </div>
                @endif

                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-3 h-100">
                        @if($b2)
                        <div class="w-100">
                            @if($b2->link_url)
                                <a href="{{ $b2->link_url }}" target="_blank" class="d-block">
                                    <img src="{{ $b2->image_url }}" alt="{{ $b2->title }}" class="img-fluid rounded w-100">
                                </a>
                            @else
                                <img src="{{ $b2->image_url }}" alt="{{ $b2->title }}" class="img-fluid rounded w-100">
                            @endif
                        </div>
                        @endif
                        @if($b3)
                        <div class="w-100">
                            @if($b3->link_url)
                                <a href="{{ $b3->link_url }}" target="_blank" class="d-block">
                                    <img src="{{ $b3->image_url }}" alt="{{ $b3->title }}" class="img-fluid rounded w-100">
                                </a>
                            @else
                                <img src="{{ $b3->image_url }}" alt="{{ $b3->title }}" class="img-fluid rounded w-100">
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif


    <style>
        /* Best Deals carousel: mirror Top Selling/New Arrivals styles */
        #bestDealsSplide { background:rgb(253, 253, 255);  padding: 14px 0; }
        #bestDealsSplide .splide__slide { padding: 0px; position: relative; }
        #bestDealsSplide .product-card { padding: 0 !important; border-radius: 10px; background: #fff; box-shadow: 0 3px 14px rgba(0,0,0,0.06); border: none !important; }
        #bestDealsSplide .product-image-container { position: relative; height: 300px; border-radius: 12px 12px 0 0; overflow: hidden; }
        #bestDealsSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; }
        #bestDealsSplide .rating-badge { position: absolute; left: 10px; bottom: 10px; background: rgba(255,255,255,0.95); border-radius: 8px; padding: 4px 8px; font-size: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); display: flex; align-items: center; gap: 6px; }
        #bestDealsSplide .rating-badge .star { color: #f59e0b; }
        #bestDealsSplide .product-title { font-size: 18px; line-height: 1.4; margin-top: 10px; margin-bottom: 2px; color:rgb(25, 30, 39); font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        #bestDealsSplide .price { font-size: 18px; font-weight: 700; margin-top: 2px; }
        #bestDealsSplide .price .fw-bold { color: #059669; }
        #bestDealsSplide .price .old { font-size: 14px; color: #9ca3af !important; text-decoration: line-through !important; margin-left: 8px; font-weight: 500; }
        #bestDealsSplide .product-info { padding: 10px 12px 12px; }
        #bestDealsSplide .wishlist-btn { position: absolute; top: 10px; right: 10px; z-index: 10; pointer-events: auto; cursor: pointer; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        #bestDealsSplide .wishlist-btn i { pointer-events: none; font-size: 16px; }
        #bestDealsSplide .product-actions { margin-top: 6px; }
        #bestDealsSplide .btn-add-cart { padding: 6px 10px; font-size: 12px; }
        @media (max-width: 991.98px) { #bestDealsSplide .product-image-container { height: 180px; } }
    </style>


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
        @media (max-width: 768px) {
            .splide-hero { border-radius: 0 !important; }
        }
        .hero-slide-img { width: 100%; height: 460px; object-fit: cover; display: block; }
        @media (max-width: 767.98px) { .hero-slide-img { height: 300px; } }
        .splide-hero .splide__arrow { background: rgba(255,255,255,0.9); width: 40px; height: 40px; box-shadow: 0 4px 18px rgba(0,0,0,0.12); }
        @media (max-width: 768px) {
            .splide-hero .splide__arrows { display: none !important; }
        }
        .splide-hero .splide__pagination__page.is-active { background: #111827; }
        .hero-caption { position: absolute; left: 24px; bottom: 24px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        /* Top selling carousel spacing */
        #mostSoldSplide { background:rgb(253, 253, 253); padding: 20px 0px ; }
        #mostSoldSplide .splide__slide { padding: 0 0px; box-shadow: 2px 2px 10px rgba(0,0,0,0.06); position: relative; }
        /* Make top selling cards a bit smaller and ensure icons/ratings show */
        #mostSoldSplide .product-card { padding: 0 !important; border-radius: 10px; background: #fff; box-shadow: 0 3px 14px rgba(0,0,0,0.06); border: none !important; }
        #mostSoldSplide .product-image-container { position: relative; height: 300px; border-radius: 12px 12px 0 0; overflow: hidden; }
        #mostSoldSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; }
        #mostSoldSplide .rating-badge { position: absolute; left: 10px; bottom: 10px; background: rgba(255,255,255,0.95); border-radius: 8px; padding: 4px 8px; font-size: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); display: flex; align-items: center; gap: 6px; }
        #mostSoldSplide .rating-badge .star { color: #f59e0b; }
        #mostSoldSplide .product-title { font-size: 18px; line-height: 1.4; margin-top: 10px; margin-bottom: 2px; color:rgb(25, 30, 39); font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        #mostSoldSplide .price { font-size: 18px; font-weight: 700; margin-top: 2px; }
        #mostSoldSplide .price .fw-bold { color: #059669; }
        #mostSoldSplide .price .old { font-size: 14px; color: #9ca3af !important; text-decoration: line-through !important; margin-left: 8px; font-weight: 500; }
        #mostSoldSplide .product-info { padding: 10px 12px 12px; }
        #mostSoldSplide .wishlist-btn { position: absolute; top: 10px; right: 10px; z-index: 10; pointer-events: auto; cursor: pointer; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        #mostSoldSplide .wishlist-btn i { pointer-events: none; font-size: 16px; }
        #mostSoldSplide .product-meta .stars i { color: #fbbf24; margin-right: 2px; font-size: 12px; }
        #mostSoldSplide .product-actions { margin-top: 6px; }
        #mostSoldSplide .btn-add-cart { padding: 6px 10px; font-size: 12px; }
        
        
        @media (max-width: 991.98px) { #mostSoldSplide .product-image-container { height: 180px; } }
        /* remove hover border if any framework styles apply */
        #mostSoldSplide .product-card.no-hover-border:hover { border-color: transparent !important; border: none !important; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }

        /* New Arrivals carousel: mirror Top Selling styles */
        #newArrivalsSplide { background:rgb(253, 253, 253);  padding: 14px 0px; }
        #newArrivalsSplide .splide__slide { padding: 0px; position: relative; }
        #newArrivalsSplide .product-card { padding: 0 !important; border-radius: 10px; background: #fff; box-shadow: 0 3px 14px rgba(0,0,0,0.06); border: none !important; }
        #newArrivalsSplide .product-image-container { position: relative; height: 300px; border-radius: 12px 12px 0 0; overflow: hidden; }
        #newArrivalsSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; }
        #newArrivalsSplide .rating-badge { position: absolute; left: 10px; bottom: 10px; background: rgba(255,255,255,0.95); border-radius: 8px; padding: 4px 8px; font-size: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); display: flex; align-items: center; gap: 6px; }
        #newArrivalsSplide .rating-badge .star { color: #f59e0b; }
        #newArrivalsSplide .product-title { font-size: 18px; line-height: 1.4; margin-top: 10px; margin-bottom: 2px; color:rgb(25, 30, 39); font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        #newArrivalsSplide .price { font-size: 18px; font-weight: 700; margin-top: 2px; }
        #newArrivalsSplide .price .fw-bold { color: #059669; }
        #newArrivalsSplide .price .old { font-size: 14px; color: #9ca3af !important; text-decoration: line-through !important; margin-left: 8px; font-weight: 500; }
        #newArrivalsSplide .product-info { padding: 10px 12px 12px; }
        #newArrivalsSplide .wishlist-btn { position: absolute; top: 10px; right: 10px; z-index: 10; pointer-events: auto; cursor: pointer; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        #newArrivalsSplide .wishlist-btn i { pointer-events: none; font-size: 16px; }
        #newArrivalsSplide .product-actions { margin-top: 6px; }
        #newArrivalsSplide .btn-add-cart { padding: 6px 10px; font-size: 12px; }
        
        
        @media (max-width: 991.98px) { #newArrivalsSplide .product-image-container { height: 180px; } }
        /* Categories carousel container */
        #categorySplide { background:rgb(253, 253, 253);   }
        /* Category card styles - full poster with floating badge title */
        .popular-categories #categorySplide .splide__slide > a.category-chip { position: relative; display: block; width: 100%; height: 250px; border-radius: 14px; overflow: hidden; text-decoration: none; border: none; }
        .popular-categories .chip-thumb.full-bg { position: absolute; inset: 0; width: 100% !important; height: 100% !important; border-radius: inherit !important; overflow: hidden; display: block; }
        .popular-categories .chip-thumb.full-bg img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block; transform: scale(1.01); transition: transform .35s ease; }
        .popular-categories #categorySplide .splide__slide > a.category-chip:hover .chip-thumb.full-bg img { transform: scale(1.05); }
        .popular-categories .badge-title { position: absolute; left: 50%; transform: translateX(-50%); bottom: 14px; background: #fff; color: #111827; font-weight: 800; letter-spacing: .08em; font-size: 14px; padding: 10px 16px; border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,0.16); border: 1px solid rgba(17,24,39,0.06); display: inline-block; white-space: nowrap; max-width: 90%; }
        
        /* Enhanced mobile responsive for categories */
        @media (max-width: 768px) { 
            .popular-categories #categorySplide .splide__slide > a.category-chip { 
                height: 160px; 
                border-radius: 12px;
            } 
            .popular-categories .badge-title { 
                font-size: 11px; 
                padding: 6px 10px; 
                left: 50%; 
                transform: translateX(-50%); 
                bottom: 10px; 
                max-width: 90%; 
                border-radius: 8px;
            } 
        }
        
        @media (max-width: 576px) { 
            .popular-categories #categorySplide .splide__slide > a.category-chip { 
                height: 140px; 
                border-radius: 10px;
            } 
            .popular-categories .badge-title { 
                font-size: 10px; 
                padding: 5px 8px; 
                left: 50%; 
                transform: translateX(-50%); 
                bottom: 8px; 
                max-width: 88%; 
                border-radius: 6px;
            } 
        }
        
        .popular-categories #categorySplide .splide__list { align-items: stretch; }
        .popular-categories .category-chip { text-align: center; }
		/* Scope styles to vlogs section only */
		@media (min-width: 1200px) {
			.home-vlogs > .container {
				max-width: none;
				width: 80%;
			}
			.vlog-banners > .container {
				max-width: none;
				width: 80%;
                /* padding: 0 10px !important; */
			}
		}

		.home-vlogs { padding: 10px 0; }
		/* Allow interacting with YouTube controls */
		.home-vlogs .ratio iframe { pointer-events: auto; }
        /* Splide spacing for vlogs */
        #vlogSplide { background:rgb(253, 253, 253);  padding: 8px 0px; }
        #vlogSplide .splide__slide { padding: 0px; }
		#vlogSplide .splide__arrows { z-index: 2; }
		@media (max-width: 991.98px) { #vlogSplide .splide__slide { padding: 0 8px; } }
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
        // Old jQuery code removed - now using the new JavaScript implementation in app.js

        $(function () {
            // New Arrivals now rendered and initialized via Splide in resources/js/app.js
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