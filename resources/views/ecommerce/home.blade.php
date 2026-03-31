@extends('ecommerce.master')

@section('main-section')
    <!-- Home: Full Width Banner Slider -->
    <section class="home-hero">
        <div class="container-fluid px-0">
            <!-- Full Width Banner Slider (admin managed) -->
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
                            <li class="splide__slide">
                                <div class="d-flex align-items-center justify-content-center bg-light w-100" style="min-height: 400px; color: #999;">
                                    <div class="text-center">
                                        <i class="fas fa-image fa-3x mb-3"></i>
                                        <p class="mt-2 fw-medium">No Image Found</p>
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>  
    </section>

    <!-- Popular Categories (Splide carousel) -->
    <section class="popular-categories">
        <div class="container">
            <div class="section-header section-header--fancy">
                <h2 class="section-title"> CATEGORIES</h2>
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

  <!-- New Arrivals Products (carousel like Top Selling) -->
  <section class="top-products">
        <div class="container">
            <div class="section-header section-header--fancy">
                <h2 class="section-title">NEW ARRIVALS</h2>
                <a href="{{ route('product.archive', ['sort' => 'newest']) }}" class="section-see-all">View All</a>
            </div>

            <div id="newArrivalsSplide" class="splide" aria-label="New Arrivals" style="visibility:hidden;">
                <div class="splide__track">
                    <ul class="splide__list" id="newArrivalsSplideList">
                        <!-- Slides will be injected here -->
                    </ul>
                </div>
            </div>
            <div id="newArrivalsFallback" class="row product-grid g-3 px-2">
                <!-- Skeleton loaders will be injected here -->
            </div>
        </div>
    </section>

    <!-- Top Selling Products -->
    <section class="top-products">
        <div class="container">
            <div class="section-header section-header--fancy">
                <h2 class="section-title ">TOP SELLING PRODUCTS</h2>
                <a href="{{ route('product.archive') }}" class="section-see-all">View All</a>
            </div>

            <div id="mostSoldSplide" class="splide" aria-label="Top Selling Products" style="visibility:hidden;">
                <div class="splide__track">
                    <ul class="splide__list" id="mostSoldSplideList">
                        <!-- Slides will be injected here -->
                    </ul>
                </div>
            </div>
            <div id="mostSoldFallback" class="row product-grid g-3 px-2">
                <!-- Skeleton loaders will be injected here -->
            </div>
        </div>
    </section>
  
  

	<!-- Latest Vlogs Carousel (Splide) -->
	<section class="home-vlogs">
		<div class="container">
			<div class="d-flex justify-content-between mb-5">
				<div>
					<h2 class="section-title mb-0 text-start">COLLECTIONS </h2>
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
    <section class="top-products">
        <div class="container">
            <div class="section-header section-header--fancy">
                <h2 class="section-title">BEST DEALS</h2>
                <a href="{{ route('best.deal') }}" class="section-see-all">View All</a>
            </div>

            <div id="bestDealsSplide" class="splide" aria-label="Best Deals" style="visibility:hidden;">
                <div class="splide__track">
                    <ul class="splide__list" id="bestDealsSplideList">
                        <!-- Slides will be injected here -->
                    </ul>
                </div>
            </div>
            <div id="bestDealsFallback" class="row product-grid g-3 px-2">
                <!-- Skeleton loaders will be injected here -->
            </div>
        </div>
    </section>

    
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
        /* Best Deals carousel: mirror Top Selling/New Arrivals styles - prevent layout shifts */
        #bestDealsSplide { 
            background: transparent !important;  
            padding: 14px 0;
            contain: layout style;
            will-change: auto;
        }
        #bestDealsSplide .splide__slide { padding: 0px; position: relative; }
        #bestDealsSplide .product-image-container { position: relative; height: 380px; border-radius: 4px 4px 0 0; overflow: hidden; background: #fff; }
        #bestDealsSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s ease; }
        #bestDealsSplide .rating-badge { position: absolute; left: 10px; bottom: 10px; background: rgba(255,255,255,0.95); border-radius: 8px; padding: 4px 8px; font-size: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); display: flex; align-items: center; gap: 6px; }
        #bestDealsSplide .rating-badge .star { color: #f59e0b; }
        #bestDealsSplide .product-title { font-size: 18px; line-height: 1.3; margin-top: 8px; margin-bottom: 0px; color:rgb(25, 30, 39); font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        #bestDealsSplide .price { font-size: 18px; font-weight: 700; margin-top: 2px; }
        #bestDealsSplide .price .fw-bold { color: #059669; }
        #bestDealsSplide .price .old { font-size: 14px; color: #9ca3af !important; text-decoration: line-through !important; margin-left: 8px; font-weight: 500; }
        #bestDealsSplide .product-info { padding: 10px 12px 12px; }
        #bestDealsSplide .wishlist-btn { position: absolute; top: 10px; right: 10px; z-index: 10; pointer-events: auto; cursor: pointer; background: #fff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        #bestDealsSplide .wishlist-btn i { pointer-events: none; font-size: 16px; }
        #bestDealsSplide .product-actions { margin-top: 6px; }
        #bestDealsSplide .btn-add-cart { padding: 6px 10px; font-size: 12px; }
        @media (max-width: 991.98px) { 
            #bestDealsSplide .product-image-container, 
            #mostSoldSplide .product-image-container, 
            #newArrivalsSplide .product-image-container { height: 240px; } 
        }
        
        /* Skeleton Loader Styles */
        .product-skeleton {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 3px 14px rgba(0,0,0,0.06);
            overflow: hidden;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .skeleton-image {
            width: 100%;
            height: 380px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        
        .skeleton-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        
        .skeleton-content {
            padding: 10px 12px 12px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .skeleton-title {
            height: 20px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .skeleton-title:first-child {
            width: 85%;
        }
        
        .skeleton-title:last-child {
            width: 60%;
            margin-top: 4px;
        }
        
        .skeleton-price {
            height: 24px;
            width: 100px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
            margin-top: 8px;
        }
        
        .skeleton-button {
            height: 36px;
            width: 100%;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 6px;
            margin-top: auto;
        }
        
        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
        
        @media (max-width: 991.98px) {
            .skeleton-image {
                height: 240px;
            }
        }
        
        /* Skeleton for carousel slides */
        .splide__slide .product-skeleton {
            margin: 0;
        }
    </style>


    <div id="toast-container"
        style="position: fixed; top: 24px; right: 24px; z-index: 16000; display: flex; flex-direction: column; gap: 10px;">
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
        /* Hero splide - Full Width */
        .splide-hero .splide__list, .splide-hero .splide__slide { height: 100%; }
        .splide-hero { border-radius: 0; overflow: hidden; }
        .home-hero { padding: 0 !important; padding-top: 0 !important; }
        .home-hero .container-fluid { padding: 0 !important; }
        /* Hero slide image styles are now in ecommerce.css to avoid conflicts */
        .splide-hero .splide__arrow { display: none !important; }
        .splide-hero .splide__arrows { display: none !important; }
        .splide-hero .splide__pagination__page.is-active { background: #111827; }
        .hero-caption { position: absolute; left: 24px; bottom: 24px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,0.3); }
        /* Top selling carousel spacing - prevent layout shifts */
        #mostSoldSplide { 
            background: transparent !important; 
            padding: 20px 0px;
            contain: layout style;
            will-change: auto;
        }
        #mostSoldSplide .splide__slide { padding: 0 0px; position: relative; }
        /* Make top selling cards a bit smaller and ensure icons/ratings show */
        #mostSoldSplide .product-card { padding: 0 !important; }
        #mostSoldSplide .product-image-container { position: relative; height: 380px; border-radius: 4px 4px 0 0; overflow: hidden; background: #fff; }
        #mostSoldSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s ease; }
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
        
        
        @media (max-width: 991.98px) { #mostSoldSplide .product-image-container { height: 240px; } }
        /* remove hover border if any framework styles apply */
        #mostSoldSplide .product-card.no-hover-border:hover { border-color: transparent !important; border: none !important; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
        .product-card:hover .product-image { transform: scale(1.1); }

        /* New Arrivals carousel: mirror Top Selling styles - prevent layout shifts */
        #newArrivalsSplide { 
            background: transparent !important;  
            padding: 14px 0px;
            contain: layout style;
            will-change: auto;
        }
        #newArrivalsSplide .splide__slide { padding: 0px; position: relative; }
        #newArrivalsSplide .product-card { 
            padding: 0 !important; 
        }
        #newArrivalsSplide .product-image-container { position: relative; height: 380px; border-radius: 4px 4px 0 0; overflow: hidden; background: #fff; }
        #newArrivalsSplide .product-image { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s ease; }
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
        
        
        @media (max-width: 991.98px) { #newArrivalsSplide .product-image-container { height: 240px; } }
        /* Categories carousel container - prevent layout shifts */
        #categorySplide { 
            background:rgb(253, 253, 253);
            /* Prevent layout shifts from carousel initialization */
            contain: layout style;
            will-change: auto;
        }
        /* Category card styles - full poster with floating badge title */
        .popular-categories #categorySplide .splide__slide > a.category-chip { position: relative; display: block; width: 100%; height: 250px; border-radius: 8px; overflow: hidden; text-decoration: none; border: none; }
        .popular-categories .chip-thumb.full-bg { position: absolute; inset: 0; width: 100% !important; height: 100% !important; border-radius: inherit !important; overflow: hidden; display: block; }
        .popular-categories .chip-thumb.full-bg img { width: 100% !important; height: 100% !important; object-fit: cover !important; display: block; transform: scale(1.01); transition: transform .35s ease; }
        .popular-categories #categorySplide .splide__slide > a.category-chip:hover .chip-thumb.full-bg img { transform: scale(1.05); }
        .popular-categories .badge-title { position: absolute; left: 50%; transform: translateX(-50%); bottom: 14px; background: #fff; color: #111827; font-weight: 800; letter-spacing: .08em; font-size: 14px; padding: 10px 16px; border-radius: 6px; box-shadow: 0 6px 24px rgba(0,0,0,0.16); border: 1px solid rgba(17,24,39,0.06); display: inline-block; white-space: nowrap; max-width: 90%; }
        
        /* Enhanced mobile responsive for categories */
        @media (max-width: 768px) { 
            .popular-categories #categorySplide .splide__slide > a.category-chip { 
                height: 160px; 
                border-radius: 6px;
            } 
            .popular-categories .badge-title { 
                font-size: 11px; 
                padding: 6px 10px; 
                left: 50%; 
                transform: translateX(-50%); 
                bottom: 10px; 
                max-width: 90%; 
                border-radius: 6px;
            } 
        }
        
        @media (max-width: 576px) { 
            .popular-categories #categorySplide .splide__slide > a.category-chip { 
                height: 140px; 
                border-radius: 4px;
            } 
            .popular-categories .badge-title { 
                font-size: 10px; 
                padding: 5px 8px; 
                left: 50%; 
                transform: translateX(-50%); 
                bottom: 8px; 
                max-width: 88%; 
                border-radius: 4px;
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

        .wishlist-btn i.fa-heart.active {
            color: #e53935 !important;
        }

        /* Mobile specific font reductions for section headers */
        @media (max-width: 768px) {
            .splide__arrows {
                display: none !important;
            }
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

        .wishlist-btn i.fa-heart.active {
            color: #e53935 !important;
        }
        .product-card-price .current {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
        }
        .product-card-price .original {
            font-size: 0.9rem;
            text-decoration: line-through;
            color: #9ca3af;
            margin-left: 8px;
        }

        /* Premium Splide Arrows */
        .splide__arrow {
            background: #fff !important;
            border: 1px solid #e5e7eb !important;
            opacity: 1 !important;
            width: 40px !important;
            height: 40px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
            transition: all 0.3s ease !important;
        }
        .splide__arrow:hover {
            background: #00512C !important;
            border-color: #00512C !important;
            color: #fff !important;
        }
        .splide__arrow svg {
            fill: #374151 !important;
            transition: fill 0.3s ease !important;
        }
        .splide__arrow:hover svg {
            fill: #fff !important;
        }
        .splide__arrow--prev { left: -20px !important; }
        .splide__arrow--next { right: -20px !important; }

        @media (max-width: 991.98px) { 
            .product-card .product-image-container { height: 240px; } 
            .splide__arrow--prev { left: 5px !important; }
            .splide__arrow--next { right: 5px !important; }
        }

    </style>
@endpush