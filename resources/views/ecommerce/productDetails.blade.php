@extends('ecommerce.master')

@push('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('main-section')
    <div class="pd-scroll-indicator"></div>
    <!-- Breadcrumb Navigation -->
    <div class="pd-breadcrumb-wrapper">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="pd-breadcrumb">
                    <li class="pd-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="pd-breadcrumb-item"><a href="{{ route('product.archive') }}?category={{ $product->category->slug ?? '' }}">{{ $product->category->name ?? 'Category' }}</a></li>
                    <li class="pd-breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="product-details-page">
        <div class="container">
            <div class="pd-main-container" data-has-variations="{{ $product->has_variations ? 'true' : 'false' }}">
                <!-- Product Gallery -->
                <div class="pd-gallery-section">
                    <div class="pd-gallery-wrapper position-relative">
                        <!-- Floating Wishlist Button -->
                        <button type="button" class="pd-main-wishlist-btn {{ $product->is_wishlisted ? 'active' : '' }}" 
                                data-product-id="{{ $product->id }}" 
                                onclick="toggleWishlist({{ $product->id }}, this)">
                            <i class="{{ $product->is_wishlisted ? 'fas' : 'far' }} fa-heart"></i>
                        </button>
                        <!-- Main Swiper -->
                        <div class="swiper pd-main-swiper main-image-swiper">
                            <div class="swiper-wrapper">
                                <!-- Main product image -->
                                <div class="swiper-slide" data-image-type="product">
                                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="main-image" loading="eager" onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                                </div>
                                <!-- Product galleries -->
                                @foreach($product->galleries as $gallery)
                                    <div class="swiper-slide" data-image-type="gallery">
                                        <img src="{{ asset($gallery->image) }}" alt="{{ $product->name }}" class="main-image" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                                    </div>
                                @endforeach
                                <!-- Variation images (hidden by default) -->
                                @if($product->has_variations)
                                    @foreach($product->variations as $variation)
                                        @if($variation->image)
                                            <div class="swiper-slide variation-image-slide" data-variation-id="{{ $variation->id }}" data-image-type="variation" style="display: none;">
                                                <img src="{{ asset($variation->image) }}" alt="{{ $variation->name }}" class="main-image" loading="lazy">
                                            </div>
                                        @endif
                                        @foreach($variation->galleries as $gallery)
                                            <div class="swiper-slide variation-gallery-slide" data-variation-id="{{ $variation->id }}" data-image-type="variation-gallery" style="display: none;">
                                                <img src="{{ asset($gallery->image) }}" alt="{{ $variation->name }}" class="main-image" loading="lazy">
                                            </div>
                                        @endforeach
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        
                        <!-- Thumbnail Swiper -->
                        <div class="swiper pd-thumb-swiper thumb-swiper">
                            <div class="swiper-wrapper">
                                <!-- Main product image thumbnail -->
                                <div class="swiper-slide" data-image-type="product">
                                    <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                                </div>
                                <!-- Product gallery thumbnails -->
                                @foreach($product->galleries as $gallery)
                                    <div class="swiper-slide" data-image-type="gallery">
                                        <img src="{{ asset($gallery->image) }}" alt="{{ $product->name }}" onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                                    </div>
                                @endforeach
                                <!-- Variation image thumbnails (hidden by default) -->
                                @if($product->has_variations)
                                    @foreach($product->variations as $variation)
                                        @if($variation->image)
                                            <div class="swiper-slide variation-thumb-slide" data-variation-id="{{ $variation->id }}" data-image-type="variation" style="display: none;">
                                                <img src="{{ asset($variation->image) }}" alt="{{ $variation->name }}">
                                            </div>
                                        @endif
                                        @foreach($variation->galleries as $gallery)
                                            <div class="swiper-slide variation-gallery-thumb-slide" data-variation-id="{{ $variation->id }}" data-image-type="variation-gallery" style="display: none;">
                                                <img src="{{ asset($gallery->image) }}" alt="{{ $variation->name }}">
                                            </div>
                                        @endforeach
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Product Info -->
                <div class="pd-info-section">
                    <div class="pd-premium-badge"><i class="fas fa-crown me-2"></i>Premium Selection</div>
                    <h1 class="pd-title">{{ $product->name }} <small class="text-muted" style="font-size: 0.6em;">(#{{ $product->style_number ?? $product->sku }})</small></h1>
                    
                    <div class="pd-rating-row">
                        <div class="pd-stars">
                            @php
                                $avgRating = $product->averageRating() ?? 0;
                                $fullStars = floor($avgRating);
                                $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $fullStars)
                                    <i class="fas fa-star"></i>
                                @elseif($i == $fullStars + 1 && $hasHalfStar)
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="pd-rating-text">({{ number_format($avgRating, 1) }} / 5.0 Rating)</span>
                    </div>

                    <div class="pd-price-container">
                        @php
                            $effectivePrice = $product->effective_price;
                            $originalPrice = $product->original_price;
                            $hasDiscount = $product->hasDiscount();
                        @endphp
                        @if($hasDiscount && $effectivePrice < $originalPrice)
                            <span class="pd-current-price">
                                TK. {{ number_format($effectivePrice, 0) }}
                            </span>
                            <span class="pd-original-price">
                                TK. {{ number_format($originalPrice, 0) }}
                            </span>
                        @else
                            <span class="pd-current-price">
                                TK. {{ number_format($originalPrice, 0) }}
                            </span>
                        @endif
                    </div>

                    @if (!empty($product->short_desc))
                    <div class="pd-description-brief">
                        {!! $product->getCleanHtml('short_desc') !!}
                    </div>
                    @endif

                    @if($product->size_chart)
                    <div class="size-chart-section mt-3 mb-3">
                        <h4 class="size-chart-title" style="font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-ruler-combined" style="font-size: 0.75rem;"></i>Size Chart (inch)
                        </h4>
                        <div class="size-chart-image-container">
                            <img src="{{ asset($product->size_chart) }}" alt="Size Chart for {{ $product->name }}" class="size-chart-img" onclick="openImageModal(this.src)">
                        </div>
                    </div>
                    @endif

                    @if($product->has_variations)
                    @php
                        $attributeGroups = [];
                        foreach ($product->variations as $variation) {
                            foreach ($variation->combinations as $combination) {
                                $attr = $combination->attribute;
                                $val = $combination->attributeValue;
                                if (!$attr || !$val) continue;

                                if (!isset($attributeGroups[$attr->id])) {
                                    $attributeGroups[$attr->id] = [
                                        'name' => $attr->name,
                                        'values' => []
                                    ];
                                }
                                if (!isset($attributeGroups[$attr->id]['values'][$val->id])) {
                                    $attributeGroups[$attr->id]['values'][$val->id] = [
                                        'label' => $val->value,
                                        'color_code' => $val->color_code,
                                        'image' => $val->image
                                    ];
                                }
                            }
                        }
                    @endphp
                    <div class="pd-variation-section">
                        @foreach($attributeGroups as $attrId => $group)
                            <div class="pd-variation-group">
                                <div class="pd-variation-label">{{ strtoupper($group['name']) }}:</div>
                                <div class="pd-variation-options" data-attribute-id="{{ $attrId }}">
                                    @foreach($group['values'] as $valId => $val)
                                        @php
                                            $isColor = strtolower($group['name']) === 'color';
                                            $baseClass = $isColor ? 'pd-color-btn' : 'pd-size-btn';
                                            $label = is_array($val) ? ($val['label'] ?? (string)$val) : (string)$val;
                                            $imgPath = is_array($val) ? ($val['image'] ?? null) : null;
                                            $colorCode = is_array($val) ? ($val['color_code'] ?? null) : null;
                                        @endphp
                                        @if($isColor)
                                            <button type="button" class="{{ $baseClass }}" data-attr-id="{{ $attrId }}" data-value-id="{{ $valId }}" data-label="{{ $label }}" title="{{ $label }}">
                                                @if(!empty($imgPath))
                                                    <img src="{{ asset($imgPath) }}" alt="{{ $label }}">
                                                @elseif(!empty($colorCode))
                                                    <span class="color-preview" style="background-color: {{ $colorCode }};"></span>
                                                @else
                                                    <span class="color-text-fallback">{{ $label }}</span>
                                                @endif
                                            </button>
                                        @else
                                            <button type="button" class="{{ $baseClass }}" data-attr-id="{{ $attrId }}" data-value-id="{{ $valId }}" data-label="{{ $label }}">{{ $label }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <input type="hidden" id="selected-variation-id" value="">
                        <div id="selected-attribute-values" style="display:none;"></div>

                        <div class="pd-selected-info">
                            <div class="pd-info-grid">
                                <div class="pd-info-item">
                                    <span class="pd-info-label">Selected Variation</span>
                                    <span id="selected-variation-name" class="pd-info-value">Please select options above</span>
                                </div>
                                <div class="pd-info-item text-end">
                                    <span class="pd-info-label">Price</span>
                                    <span id="selected-variation-price" class="pd-info-value">—</span>
                                </div>
                            </div>
                            <div class="pd-stock-status mt-2" id="selected-variation-stock">
                                <i class="fas fa-info-circle me-1"></i> Select your preferences to see availability
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!$product->has_variations)
                    <div class="pd-selected-info">
                        <div class="pd-stock-status in-stock mt-2">
                            <i class="fas fa-check-circle text-success me-1"></i> In stock: {{ $product->total_variation_stock }}
                        </div>
                    </div>
                    @endif

                    <!-- Variation Scripts - Moved to component for AJAX compatibility and performance -->
                    @if($product->has_variations)
                        @include('ecommerce.components.product-variation-scripts')
                    @endif

                    <div class="pd-purchase-actions">
                        <div class="pd-quantity-selector">
                            <button class="pd-qty-btn" type="button" onclick="changeQuantity(-1)"><i class="fas fa-minus"></i></button>
                            <input type="text" inputmode="numeric" class="pd-qty-input" id="quantityInput" name="quantity" value="1" readonly>
                            <button class="pd-qty-btn" type="button" onclick="changeQuantity(1)"><i class="fas fa-plus"></i></button>
                        </div>

                        @php
                            $hasStock = $product->hasStock();
                        @endphp
                        <button class="pd-btn-cart" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-has-stock="{{ $hasStock ? 'true' : 'false' }}"
                                {{ (!$hasStock || $product->has_variations) ? 'disabled' : '' }}>
                            <i class="fas fa-shopping-basket me-2"></i> {{ $hasStock ? 'Add To Cart' : 'Out of Stock' }}
                        </button>

                        <form action="{{ url('/buy-now') }}/{{ $product->id }}" method="POST" style="display:contents;" id="buyNowForm">
                            @csrf
                            <input type="hidden" name="variation_id" id="buy-now-variation-id" value="">
                            <input type="hidden" name="qty" id="buy-now-qty" value="1">
                            <button type="submit" class="pd-btn-buy" id="buyNowBtn" {{ (!$hasStock || $product->has_variations) ? 'disabled' : '' }} data-has-variations="{{ $product->has_variations ? 'true' : 'false' }}">
                                <i class="fas fa-bolt me-2"></i> {{ $hasStock ? 'Buy Now' : 'Out of Stock' }}
                            </button>
                        </form>
                    </div>

                    <div class="pd-messenger-row">
                        @if(!empty($settings->whatsapp_url))
                            <a href="{{ str_starts_with($settings->whatsapp_url, 'http') ? $settings->whatsapp_url : 'https://' . $settings->whatsapp_url }}" target="_blank" class="pd-msg-btn whatsapp" title="Chat on WhatsApp">
                                <i class="fab fa-whatsapp"></i> <span class="d-none d-md-inline">WhatsApp Order</span>
                            </a>
                        @endif
                        
                        @if(!empty($settings->facebook_url))
                            <a href="javascript:void(0)" onclick="openFacebookMessenger()" class="pd-msg-btn messenger" title="Chat on Facebook Messenger">
                                <i class="fab fa-facebook-messenger"></i> <span class="d-none d-md-inline">Chat with Us</span>
                            </a>
                        @endif
                    </div>

                    <div class="pd-extra-actions mt-4 pt-4 border-top">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="pd-share-group d-flex align-items-center">
                                <span class="pd-share-label">Share:</span>
                                <div class="pd-share-icons d-flex gap-2">
                                    <a href="#" onclick="shareToFacebook()" class="pd-share-icon facebook" title="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" onclick="shareToTwitter()" class="pd-share-icon twitter" title="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#" onclick="shareToWhatsApp()" class="pd-share-icon whatsapp" title="Share on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div> <!-- Close pd-main-container -->
            </div> <!-- Close top container -->

            <!-- Product Tabs Section -->
            @php
                $reviews = $product->reviews()->where('is_approved', true)->latest()->get();
                $avgRating = $reviews->avg('rating') ?? 0;
                $totalReviews = $reviews->count();
                $recommendCount = $reviews->where('rating', '>=', 4)->count();
                $recommendPct = $totalReviews > 0 ? round(($recommendCount / $totalReviews) * 100) : 0;
                // Fetch top rated reviews first (>= 4 stars), limit to 2
                $topReviews = $reviews->where('rating', '>=', 4)->take(2);
            @endphp
            
            <div class="pd-tabs-section mt-5">
                <div class="container">
                    <div class="row">
                        <!-- Left Column: Tabs Content -->
                        <div class="col-lg-8">
                            <div class="pd-tabs-header text-start justify-content-start border-bottom mb-4">
                                <button class="pd-tab-btn active" data-tab="description" type="button">
                                    Description
                                </button>
                                <button class="pd-tab-btn" data-tab="specs" type="button">
                                    Additional info
                                </button>
                                <button class="pd-tab-btn" data-tab="reviews" type="button">
                                    Review
                                </button>
                            </div>

                            <div class="pd-tabs-body">
                                <!-- Description Tab -->
                                <div id="description" class="pd-tab-content active">
                                    <div class="pd-content-wrapper description-text">
                                        {!! $product->getCleanHtml('description') !!}
                                        
                                        @if($product->features)
                                            <div class="mt-4">
                                                <h4 class="mb-3">Highlights and Benefits</h4>
                                                {!! $product->getCleanHtml('features') !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Additional Info Tab (Specs) -->
                                <div id="specs" class="pd-tab-content">
                                    <h4 class="mb-3">Specification</h4>
                                    @if($product->productAttributes && $product->productAttributes->count() > 0)
                                        <ul class="pd-specs-list">
                                            @foreach($product->productAttributes as $attribute)
                                                <li>
                                                    <strong>{{ $attribute->name }}:</strong> {{ $attribute->pivot->value }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">No additional information available.</p>
                                    @endif
                                </div>

                                <!-- Reviews Tab (List Only) -->
                                <div id="reviews" class="pd-tab-content">
                                    <div class="pd-reviews-list-section">
                                    <div class="pd-review-form-container mb-5" style="background: #fff; border: 1px solid #f1f5f9; border-radius: 20px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.02);">
                                        @auth
                                            <div class="d-flex align-items-center gap-3 mb-4">
                                                <div class="pd-user-avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: 700;">
                                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h5 class="m-0 fw-bold">Write a Review</h5>
                                                    <p class="text-muted small m-0">Sharing as <span class="fw-bold text-dark">{{ Auth::user()->name }}</span></p>
                                                </div>
                                            </div>

                                            <form id="review-form">
                                                @csrf
                                                <div class="row align-items-center mb-4">
                                                    <div class="col-md-4">
                                                        <label class="d-block mb-2 fw-bold text-dark" style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Your Rating</label>
                                                        <div class="pd-stars-input d-flex align-items-center gap-1">
                                                            @for($i = 5; $i >= 1; $i--)
                                                                <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" class="pd-star-radio">
                                                                <label for="star{{ $i }}" class="pd-star-label" data-rating="{{ $i }}" style="font-size: 24px;">
                                                                    <i class="far fa-star"></i>
                                                                </label>
                                                            @endfor
                                                            <span id="rating-text" class="ms-3 badge bg-light text-dark fw-normal">Select Rating</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <label for="comment" class="d-block mb-2 fw-bold text-dark" style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Your Experience</label>
                                                    <textarea class="pd-form-control w-100 p-3" name="comment" id="comment" rows="4" required 
                                                        placeholder="What did you like or dislike? What did you use this product for?"
                                                        style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; resize: none; font-size: 15px; color: #334155; transition: all 0.3s;"></textarea>
                                                </div>

                                                <div class="text-end">
                                                    <button type="submit" class="pd-btn-buy w-auto px-5 rounded-pill shadow-sm" style="height: 50px; font-weight: 600; letter-spacing: 0.5px;">
                                                        Submit Review <i class="fas fa-paper-plane ms-2"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <div class="text-center py-5">
                                                <div class="mb-4">
                                                    <i class="fas fa-lock fa-3x text-muted opacity-25"></i>
                                                </div>
                                                <h4 class="fw-bold mb-3">Sign in to write a review</h4>
                                                <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">Share your experience with our community. It only takes a moment to log in or create an account.</p>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a href="{{ route('login') }}" class="pd-btn-buy w-auto px-5 rounded-pill text-decoration-none">
                                                        Log In
                                                    </a>
                                                    <a href="{{ route('register') }}" class="pd-btn-cart outline sm w-auto px-5 rounded-pill text-decoration-none d-flex align-items-center justify-content-center" style="height: 54px;">
                                                        Sign Up
                                                    </a>
                                                </div>
                                            </div>
                                        @endauth
                                    </div>

                                        <div class="pd-section-header mb-4 d-flex justify-content-between align-items-center">
                                            <h4 class="pd-section-subtitle m-0">Customer Reviews</h4>
                                        </div>
                                        
                                        <div id="reviews-list" class="pd-reviews-grid" data-product-id="{{ $product->id }}">
                                            <!-- Reviews loaded via JS -->
                                        </div>
                                        <div id="load-more-container" class="text-center mt-4" style="display: none;">
                                            <button class="pd-btn-cart outline w-auto px-5" id="load-more-reviews">Load More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Sidebar -->
                    <div class="col-lg-4">
                        <div class="pd-sidebar-sticky" style="top: 100px;">
                            
                            <!-- Top Stories (Featured Reviews) -->
                            <div class="pd-sidebar-card mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="pd-sidebar-title m-0">Top Stories</h4>
                                    <i class="fas fa-quote-right fa-2x text-light"></i>
                                </div>
                                
                                <div class="pd-top-stories-list">
                                    @forelse($topReviews as $review)
                                        <div class="pd-story-card mb-3">
                                            <div class="mb-2 text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                @endfor
                                            </div>
                                            <p class="mb-3 text-muted small">"{{ Str::limit($review->comment, 80) }}"</p>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="pd-story-avatar">{{ strtoupper(substr($review->user_name ?? 'A', 0, 1)) }}</div>
                                                <div>
                                                    <h6 class="m-0 text-dark" style="font-size: 13px; font-weight: 700;">{{ $review->user_name }}</h6>
                                                    <span class="text-muted" style="font-size: 11px;">Verified Buyer</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                            <i class="far fa-comment-alt text-muted fa-2x mb-3 opacity-50"></i>
                                            <p class="text-muted small m-0">No top stories yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Rating Summary Card -->
                            <div class="pd-sidebar-card text-center">
                                <div id="rating-summary-content" class="pd-rating-summary-card">
                                    <div class="pd-overall-score mb-2">
                                        <span class="pd-score-num display-4 fw-bold text-dark" id="overall-rating">{{ number_format($avgRating, 1) }}</span>
                                        <div class="pd-score-stars text-warning small" id="rating-stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                                            @endfor
                                        </div>
                                        <div class="text-muted small text-uppercase mt-1">Average Rating</div>
                                    </div>
                                    
                                    <div class="pd-progress-container my-3 px-4">
                                        <div class="progress" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $recommendPct }}%" aria-valuenow="{{ $recommendPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="text-muted mt-2" style="font-size: 11px;">{{ $recommendPct }}% Reviewers Recommend This</p>
                                    </div> <!-- Close rating-summary-content -->
                                </div> <!-- Close sidebar-card -->

                            </div> <!-- Close pd-sidebar-sticky -->
                        </div> <!-- Close col-lg-4 -->
                    </div> <!-- Close row -->
                </div> <!-- Close container -->
            </div> <!-- Close pd-tabs-section -->

        <!-- (Review Modal Removed) -->

    <!-- Related Products Section -->
    @if(isset($relatedProducts) && count($relatedProducts))
        @php
            $userId = Auth::id();
            $wishlistedIds = $userId ? \App\Models\Wishlist::where('user_id', $userId)->whereIn('product_id', $relatedProducts->pluck('id'))->pluck('product_id')->toArray() : [];
            $sourceType = $settings->ecommerce_source_type ?? null;
            $sourceId = $settings->ecommerce_source_id ?? null;
        @endphp
        <section class="pd-related-section mt-4 py-4 bg-light">
            <div class="container">
                <div class="pd-section-header text-center mb-4">
                    <h2 class="pd-section-title" style="font-size: 24px; font-weight: 800; margin-bottom: 5px;">You Might Also Like</h2>
                    <p class="text-muted small m-0">Handpicked selection of similar premium items</p>
                </div>

                <div id="relatedProductsSplide" class="splide pd-related-splide" aria-label="You Might Also Like">
                    <div class="splide__track">
                        <ul class="splide__list" id="relatedProductsSplideList">
                            @foreach($relatedProducts as $relatedProduct)
                                @php
                                    $isWishlisted = in_array($relatedProduct->id, $wishlistedIds);
                                    $effPrice = $relatedProduct->effective_price;
                                    $origPrice = $relatedProduct->original_price;
                                    $avgRating = $relatedProduct->reviews->avg('rating') ?? 0;
                                    
                                    // Stock logic for related
                                    $hasStock = false;
                                    if ($sourceType && $sourceId) {
                                        if ($relatedProduct->has_variations) {
                                            foreach ($relatedProduct->variations as $var) {
                                                $q = ($sourceType === 'branch') ? $var->stocks->where('branch_id', $sourceId)->sum('quantity') : $var->stocks->where('warehouse_id', $sourceId)->sum('quantity');
                                                if ($q > 0) { $hasStock = true; break; }
                                            }
                                        } else {
                                            $q = ($sourceType === 'branch') ? $relatedProduct->branchStock->where('branch_id', $sourceId)->sum('quantity') : $relatedProduct->warehouseStock->where('warehouse_id', $sourceId)->sum('quantity');
                                            $hasStock = $q > 0;
                                        }
                                    } else {
                                        $hasStock = $relatedProduct->total_variation_stock > 0;
                                    }
                                @endphp
                                <li class="splide__slide">
                                    <div class="pd-product-card" 
                                        data-href="{{ route('product.details', $relatedProduct->slug) }}"
                                        data-gtm-id="{{ $relatedProduct->id }}"
                                        data-gtm-name="{{ $relatedProduct->name }}"
                                        data-gtm-price="{{ $effPrice }}"
                                        data-gtm-category="{{ $relatedProduct->category->name ?? '' }}">
                                        
                                        <div class="pd-card-image">
                                            <img src="{{ asset($relatedProduct->image) }}" 
                                                 alt="{{ $relatedProduct->name }}" 
                                                 loading="lazy" 
                                                 onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                                            
                                            <button class="pd-card-wishlist{{ $isWishlisted ? ' active' : '' }}" 
                                                    data-product-id="{{ $relatedProduct->id }}" 
                                                    onclick="event.stopPropagation(); toggleWishlist({{ $relatedProduct->id }}, this)">
                                                <i class="{{ $isWishlisted ? 'fas text-danger' : 'far' }} fa-heart"></i>
                                            </button>

                                            @if(!$hasStock)
                                                <div class="pd-card-badge stock-out">Out of Stock</div>
                                            @elseif($effPrice < $origPrice)
                                                @php
                                                    $off = round((($origPrice - $effPrice) / $origPrice) * 100);
                                                @endphp
                                                <div class="pd-card-badge discount">-{{ $off }}%</div>
                                            @endif

                                            <div class="pd-card-overlay">
                                                <span class="pd-overlay-text">VIEW DETAILS</span>
                                            </div>
                                        </div>

                                        <div class="pd-card-info">
                                            <h3 class="pd-card-title">{{ $relatedProduct->name }}</h3>
                                            <div class="pd-card-price">
                                                @if($effPrice < $origPrice)
                                                    <span class="current">TK. {{ number_format($effPrice, 0) }}</span>
                                                    <span class="original">TK. {{ number_format($origPrice, 0) }}</span>
                                                @else
                                                    <span class="current">TK. {{ number_format($effPrice, 0) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

    <script>
        console.log('[PD] Product Details page script running');
        
        // Initial stock for simple products
        window.PD_STOCK = {{ $product->has_variations ? 0 : ($product->total_variation_stock ?? 0) }};

        // Global quantity control function
        window.changeQuantity = function(delta) {
            console.log('[QTY] changeQuantity called with delta:', delta);
            var input = document.getElementById('quantityInput');
            if (!input) {
                console.error('[QTY] Quantity input not found');
                return;
            }
            var value = parseInt(input.value) || 1;
            value += delta;
            
            if (value < 1) {
                value = 1;
            }

            // Cap by stock
            if (value > window.PD_STOCK && window.PD_STOCK > 0) {
                value = window.PD_STOCK;
                if (typeof showToast === 'function') {
                    showToast('Maximum available stock reached (' + window.PD_STOCK + ')', 'warning');
                }
            } else if (value > 100) { // Safety cap
                value = 100;
            }

            input.value = value;
            console.log('[QTY] Quantity updated to:', value);
            
            // Update buy-now quantity hidden field
            var buyNowQty = document.getElementById('buy-now-qty');
            if (buyNowQty) {
                buyNowQty.value = value;
                console.log('[QTY] Buy-now quantity synced to:', value);
            }
            // Trigger change event to ensure other listeners are notified
            if (input) {
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        // Quantity control is handled by inline onclick handlers on the buttons

        // Wishlist functionality handled by global toggleWishlist in master.blade.php

        // Social Media Sharing Functions
        function shareToFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('{{ $product->name }}');
            const description = encodeURIComponent('{{ strip_tags(substr($product->description, 0, 200)) }}...');
            const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${title}%20-%20${description}`;
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        function shareToTwitter() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('{{ $product->name }}');
            const shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        function shareToWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('{{ $product->name }}');
            const shareUrl = `https://wa.me/?text=${title}%20${url}`;
            window.open(shareUrl, '_blank');
        }

        // New Messenger System Functions

        function openFacebookMessenger() {
            const productName = encodeURIComponent('{{ $product->name }}');
            const productUrl = encodeURIComponent(window.location.href);
            const productPrice = '{{ $product->sale_price ? number_format($product->sale_price, 2) : number_format($product->price, 2) }}';
            const currency = '{{ $settings->currency ?? "USD" }}';
            
            let message = `Hi! I'm interested in this product: ${productName} (${currency} ${productPrice}). Could you provide more information? ${productUrl}`;
            const encodedMessage = encodeURIComponent(message);
            
            // For now, let's use a simple approach - open Messenger directly
            // You can replace 'your_page_username' with your actual Facebook page username
            const pageUsername = 'your_page_username'; // This should be set in your Facebook URL setting
            
            // Try to get page username from settings
            const facebookUrl = `{{ $settings->facebook_url ?? '' }}`;
            let extractedUsername = null;
            
            if (facebookUrl) {
                // Extract username from various Facebook URL formats
                if (facebookUrl.includes('facebook.com/')) {
                    const match = facebookUrl.match(/facebook\.com\/([^\/\?]+)/);
                    if (match && match[1]) {
                        extractedUsername = match[1];
                    }
                } else if (facebookUrl.includes('m.me/')) {
                    const match = facebookUrl.match(/m\.me\/([^\/\?]+)/);
                    if (match && match[1]) {
                        extractedUsername = match[1];
                    }
                }
            }
            
            const finalUsername = extractedUsername || pageUsername;
            
            if (finalUsername && finalUsername !== 'your_page_username') {
                // Open Messenger with the specific page
                window.open(`https://m.me/${finalUsername}?ref=${encodedMessage}`, '_blank');
            } else {
                // Fallback: open general Messenger with message
                window.open(`https://m.me/?text=${encodedMessage}`, '_blank');
            }
        }

        

        function shareToLinkedIn() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('{{ $product->name }}');
            const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }


        // Swiper image gallery initialization
        function initImageGallery() {
            console.log('[GALLERY] Starting Swiper initialization...');
            
            var gallery = document.querySelector('.product-gallery');
            if (!gallery) {
                console.log('[GALLERY] Gallery not found');
                return;
            }

            if (typeof Swiper === 'undefined') {
                console.log('[GALLERY] Swiper not loaded, retrying in 500ms...');
                setTimeout(initImageGallery, 500);
                return;
            }

            var thumbContainer = gallery.querySelector('.thumb-swiper');
            var mainContainer = gallery.querySelector('.main-swiper');
            
            if (!thumbContainer || !mainContainer) {
                console.error('[GALLERY] Swiper containers not found');
                return;
            }

            // Destroy existing swipers
            if (window.thumbSwiper) {
                try { window.thumbSwiper.destroy(true, true); } catch(e) {}
            }
            if (window.mainSwiper) {
                try { window.mainSwiper.destroy(true, true); } catch(e) {}
            }

            try {
                // Create thumb swiper
                window.thumbSwiper = new Swiper(thumbContainer, {
                    spaceBetween: 10,
                    slidesPerView: 'auto',
                    freeMode: true,
                    watchSlidesProgress: true,
                    touchRatio: 1,
                    touchAngle: 45,
                    grabCursor: true,
                    breakpoints: {
                        0: {
                            slidesPerView: 3,
                            spaceBetween: 8,
                            touchRatio: 1.5
                        },
                        480: {
                            slidesPerView: 4,
                            spaceBetween: 10,
                            touchRatio: 1.2
                        },
                        768: {
                            slidesPerView: 5,
                            spaceBetween: 10,
                            touchRatio: 1
                        }
                    }
                });

                // Create main swiper
                window.mainSwiper = new Swiper(mainContainer, {
                    spaceBetween: 10,
                    thumbs: { 
                        swiper: window.thumbSwiper 
                    },
                    zoom: {
                        maxRatio: 3,
                        minRatio: 1
                    },
                    lazy: {
                        loadPrevNext: true,
                        loadPrevNextAmount: 2
                    },
                    touchRatio: 1,
                    touchAngle: 45,
                    grabCursor: true,
                    resistance: true,
                    resistanceRatio: 0.85,
                    breakpoints: {
                        0: {
                            touchRatio: 1.5,
                            spaceBetween: 5
                        },
                        768: {
                            touchRatio: 1,
                            spaceBetween: 10
                        }
                    }
                });
                
                console.log('[GALLERY] Main gallery Swiper initialized successfully');
                
            } catch (error) {
                console.error('[GALLERY] Failed to initialize main gallery Swiper:', error);
            }

            // Initialize simple click to gallery modal
            initSimpleGallery();

            console.log('[GALLERY] Swiper initialization complete');
            
            // Initialize image zoom functionality
            initImageZoom();
        }

        // Image zoom functionality with mouse tracking
        window.initImageZoom = function() {
            const zoomLevel = 2.5; // Zoom multiplier
            const mainImages = document.querySelectorAll('.main-image');
            
            mainImages.forEach(function(img) {
                // Skip if already initialized (check for custom property)
                if (img._zoomInitialized === true) {
                    return;
                }
                
                // Find the container (swiper-slide or main-image wrapper)
                let imageContainer = img.closest('.swiper-slide');
                if (!imageContainer) {
                    imageContainer = img.parentElement;
                }
                
                if (!imageContainer) return;
                
                // Mark as initialized
                img._zoomInitialized = true;
                
                // Skip if image is not loaded
                if (!img.complete) {
                    img.addEventListener('load', function() {
                        setupZoom(imageContainer, img, zoomLevel);
                    }, { once: true });
                } else {
                    setupZoom(imageContainer, img, zoomLevel);
                }
            });
        }
        
        function setupZoom(imageContainer, img, zoomLevel) {
            let isZooming = false;
            
            // Ensure container has proper styling for zoom
            if (!imageContainer.classList.contains('zooming-container')) {
                imageContainer.style.position = 'relative';
                imageContainer.style.overflow = 'hidden';
                imageContainer.classList.add('zooming-container');
            }
            
            // Mouse enter - prepare for zoom
            imageContainer.addEventListener('mouseenter', function() {
                imageContainer.classList.add('zooming');
            });
            
            // Mouse move - calculate and apply zoom
            imageContainer.addEventListener('mousemove', function(e) {
                if (!isZooming) {
                    isZooming = true;
                }
                
                const rect = imageContainer.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Calculate percentage position
                const xPercent = (x / rect.width) * 100;
                const yPercent = (y / rect.height) * 100;
                
                // Set transform origin to mouse position
                img.style.transformOrigin = xPercent + '% ' + yPercent + '%';
                
                // Apply zoom
                img.style.transform = 'scale(' + zoomLevel + ')';
            });
            
            // Mouse leave - reset zoom
            imageContainer.addEventListener('mouseleave', function() {
                imageContainer.classList.remove('zooming');
                img.style.transform = 'scale(1)';
                img.style.transformOrigin = 'center center';
                isZooming = false;
            });
            
            // Touch support for mobile (optional)
            let touchStartDistance = 0;
            let touchStartScale = 1;
            
            imageContainer.addEventListener('touchstart', function(e) {
                if (e.touches.length === 2) {
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    touchStartDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    touchStartScale = parseFloat(img.style.transform.replace('scale(', '').replace(')', '')) || 1;
                }
            });
            
            imageContainer.addEventListener('touchmove', function(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    const currentDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    
                    const scale = touchStartScale * (currentDistance / touchStartDistance);
                    const clampedScale = Math.max(1, Math.min(zoomLevel, scale));
                    
                    const rect = imageContainer.getBoundingClientRect();
                    const centerX = (touch1.clientX + touch2.clientX) / 2 - rect.left;
                    const centerY = (touch1.clientY + touch2.clientY) / 2 - rect.top;
                    
                    const xPercent = (centerX / rect.width) * 100;
                    const yPercent = (centerY / rect.height) * 100;
                    
                    img.style.transformOrigin = xPercent + '% ' + yPercent + '%';
                    img.style.transform = 'scale(' + clampedScale + ')';
                }
            });
            
            imageContainer.addEventListener('touchend', function(e) {
                if (e.touches.length < 2) {
                    img.style.transform = 'scale(1)';
                    img.style.transformOrigin = 'center center';
                }
            });
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[PD] DOM ready - initializing...');
            initImageGallery();
        });

        // Toast notification system
        function showToast(message, type = 'success') {
            console.log('[TOAST] Showing toast:', message, type);
            const toast = document.createElement('div');
            toast.className = 'custom-toast ' + type;
            toast.innerHTML = `
                <div class="toast-content">
                    <span class="toast-icon"></span>
                    <span class="toast-message">${message}</span>
                    <button class="toast-close" onclick="var el=this.parentElement.parentElement; el.classList.add('hide'); requestAnimationFrame(function(){requestAnimationFrame(function(){el.remove();});});">&times;</button>
                </div>
                <div class="toast-progress"></div>
            `;
            
            // Ensure toast container exists
            var container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position: fixed; top: 24px; right: 24px; z-index: 16000; display: flex; flex-direction: column; gap: 10px;';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            
            // Animate progress bar - start at 100% and animate to 0%
            var progressBar = toast.querySelector('.toast-progress');
            progressBar.style.width = '100%';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    progressBar.style.width = '0%';
                });
            });
            
            // Auto remove after 2.5 seconds - use requestAnimationFrame with delay simulation
            var removeStart = performance.now();
            function autoRemove() {
                if (performance.now() - removeStart >= 2500) {
                    toast.classList.add('hide');
                    var hideStart = performance.now();
                    function finalRemove() {
                        if (performance.now() - hideStart >= 400) {
                            toast.remove();
                        } else {
                            requestAnimationFrame(finalRemove);
                        }
                    }
                    requestAnimationFrame(finalRemove);
                } else {
                    requestAnimationFrame(autoRemove);
                }
            }
            requestAnimationFrame(autoRemove);
        }

        // Make showToast globally available
        window.showToast = showToast;

        // Simple gallery functionality
        function initSimpleGallery() {
            console.log('[GALLERY] Initializing simple gallery functionality...');
            var mainImages = document.querySelectorAll('.main-swiper .main-image');
            console.log('[GALLERY] Found', mainImages.length, 'main images');
            
            mainImages.forEach(function(img, index) {
                // Click to open gallery modal
                img.addEventListener('click', function() {
                    console.log('[GALLERY] Click detected - opening gallery modal');
                    openGalleryModal(img.src, img.alt);
                });
            });
            
            // Initialize gallery modal
            initGalleryModal();
            
            console.log('[GALLERY] Simple gallery initialization complete');
        }

        // Gallery modal functionality
        function initGalleryModal() {
            var modal = document.getElementById('galleryModal');
            var closeBtn = document.querySelector('.gallery-close');
            var modalImage = document.getElementById('galleryModalImage');
            var imageContainer = document.querySelector('.gallery-modal-image-container');
            
            // Initialize zoom functionality
            initImageZoomInModal(modalImage, imageContainer);
            
            // Close modal
            closeBtn.addEventListener('click', closeGalleryModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeGalleryModal();
                }
            });
            
            // ESC key to close
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'block') {
                    closeGalleryModal();
                }
            });
        }

        // Image zoom functionality for gallery modal
        function initImageZoomInModal(image, container) {
            if (!image || !container) return;
            
            // Skip if already initialized
            if (image._zoomInitialized) {
                return;
            }
            image._zoomInitialized = true;
            
            let isZoomed = false;
            let currentScale = 1;
            let currentTranslateX = 0;
            let currentTranslateY = 0;
            let isDragging = false;
            let startX = 0;
            let startY = 0;
            let initialTranslateX = 0;
            let initialTranslateY = 0;
            const minScale = 1;
            const maxScale = 5;
            const zoomStep = 0.5;
            
            // Reset zoom when image changes
            function resetZoom() {
                isZoomed = false;
                currentScale = 1;
                currentTranslateX = 0;
                currentTranslateY = 0;
                container.classList.remove('zoomed');
                updateTransform();
            }
            
            // Update image transform
            function updateTransform() {
                image.style.transform = `translate(${currentTranslateX}px, ${currentTranslateY}px) scale(${currentScale})`;
            }
            
            // Zoom in/out
            function zoom(scale) {
                currentScale = Math.max(minScale, Math.min(maxScale, scale));
                isZoomed = currentScale > 1;
                container.classList.toggle('zoomed', isZoomed);
                
                // Center image if zooming out to 1x
                if (currentScale === 1) {
                    currentTranslateX = 0;
                    currentTranslateY = 0;
                }
                
                updateTransform();
            }
            
            // Click to zoom
            image.addEventListener('click', function(e) {
                if (isDragging) {
                    isDragging = false;
                    return;
                }
                
                if (!isZoomed) {
                    // Zoom in
                    const rect = container.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const xPercent = (x / rect.width) * 100;
                    const yPercent = (y / rect.height) * 100;
                    
                    currentScale = 2.5;
                    isZoomed = true;
                    container.classList.add('zoomed');
                    
                    // Adjust translate to zoom into click point
                    const imageRect = image.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    const offsetX = (x - containerRect.width / 2) * (currentScale - 1);
                    const offsetY = (y - containerRect.height / 2) * (currentScale - 1);
                    currentTranslateX = -offsetX;
                    currentTranslateY = -offsetY;
                    
                    updateTransform();
                } else {
                    // Zoom out
                    resetZoom();
                }
            });
            
            // Mouse wheel zoom
            container.addEventListener('wheel', function(e) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
                const newScale = currentScale + delta;
                zoom(newScale);
            }, { passive: false });
            
            // Drag to pan when zoomed
            image.addEventListener('mousedown', function(e) {
                if (isZoomed) {
                    isDragging = true;
                    startX = e.clientX - currentTranslateX;
                    startY = e.clientY - currentTranslateY;
                    initialTranslateX = currentTranslateX;
                    initialTranslateY = currentTranslateY;
                }
            });
            
            document.addEventListener('mousemove', function(e) {
                if (isDragging && isZoomed) {
                    currentTranslateX = e.clientX - startX;
                    currentTranslateY = e.clientY - startY;
                    
                    // Constrain panning to image bounds
                    const imageRect = image.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    const scaledWidth = imageRect.width / currentScale;
                    const scaledHeight = imageRect.height / currentScale;
                    
                    const maxX = (scaledWidth - containerRect.width) / 2;
                    const maxY = (scaledHeight - containerRect.height) / 2;
                    
                    currentTranslateX = Math.max(-maxX, Math.min(maxX, currentTranslateX));
                    currentTranslateY = Math.max(-maxY, Math.min(maxY, currentTranslateY));
                    
                    updateTransform();
                }
            });
            
            document.addEventListener('mouseup', function() {
                isDragging = false;
            });
            
            // Touch support for mobile
            let touchStartDistance = 0;
            let touchStartScale = 1;
            let touchStartX = 0;
            let touchStartY = 0;
            let lastTouchX = 0;
            let lastTouchY = 0;
            
            container.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1) {
                    // Single touch - pan
                    if (isZoomed) {
                        touchStartX = e.touches[0].clientX - currentTranslateX;
                        touchStartY = e.touches[0].clientY - currentTranslateY;
                        lastTouchX = e.touches[0].clientX;
                        lastTouchY = e.touches[0].clientY;
                    }
                } else if (e.touches.length === 2) {
                    // Pinch zoom
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    touchStartDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    touchStartScale = currentScale;
                }
            });
            
            container.addEventListener('touchmove', function(e) {
                e.preventDefault();
                
                if (e.touches.length === 1 && isZoomed) {
                    // Pan
                    const touch = e.touches[0];
                    currentTranslateX = touch.clientX - touchStartX;
                    currentTranslateY = touch.clientY - touchStartY;
                    
                    // Constrain panning
                    const imageRect = image.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    const scaledWidth = imageRect.width / currentScale;
                    const scaledHeight = imageRect.height / currentScale;
                    
                    const maxX = (scaledWidth - containerRect.width) / 2;
                    const maxY = (scaledHeight - containerRect.height) / 2;
                    
                    currentTranslateX = Math.max(-maxX, Math.min(maxX, currentTranslateX));
                    currentTranslateY = Math.max(-maxY, Math.min(maxY, currentTranslateY));
                    
                    updateTransform();
                } else if (e.touches.length === 2) {
                    // Pinch zoom
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    const currentDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    
                    const scale = touchStartScale * (currentDistance / touchStartDistance);
                    zoom(scale);
                }
            }, { passive: false });
            
            // Reset zoom when image src changes - with cleanup
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
                        resetZoom();
                    }
                });
            });
            
            observer.observe(image, { attributes: true });
            
            // Store reset function and observer for cleanup
            image._resetZoom = resetZoom;
            image._zoomObserver = observer;
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                if (image._zoomObserver) {
                    image._zoomObserver.disconnect();
                    image._zoomObserver = null;
                }
            });
        }

        // Open gallery modal
        function openGalleryModal(src, alt) {
            var modal = document.getElementById('galleryModal');
            var modalImage = document.getElementById('galleryModalImage');
            var imageContainer = document.querySelector('.gallery-modal-image-container');
            var thumbsContainer = document.querySelector('.gallery-modal-thumbs');
            
            // Initialize zoom if not already initialized
            if (modalImage && imageContainer && !modalImage._zoomInitialized) {
                initImageZoomInModal(modalImage, imageContainer);
            }
            
            // Reset zoom if image was previously zoomed
            if (modalImage && modalImage._resetZoom) {
                modalImage._resetZoom();
            }
            
            modalImage.src = src;
            modalImage.alt = alt;
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Get all images from the current product's gallery (both main image and gallery images)
            var allImages = [];
            
            // Add main product image
            var mainImage = document.querySelector('.main-swiper .swiper-slide[data-image-type="product"] img');
            if (mainImage) {
                allImages.push({
                    src: mainImage.src,
                    alt: mainImage.alt
                });
            }
            
            // Add gallery images
            var galleryImages = document.querySelectorAll('.main-swiper .swiper-slide[data-image-type="gallery"] img');
            galleryImages.forEach(function(img) {
                allImages.push({
                    src: img.src,
                    alt: img.alt
                });
            });
            
            // Clear existing thumbnails
            thumbsContainer.innerHTML = '';
            
            // Create new thumbnails
            allImages.forEach(function(imageData, index) {
                var thumbDiv = document.createElement('div');
                thumbDiv.className = 'gallery-modal-thumb';
                thumbDiv.setAttribute('data-src', imageData.src);
                
                if (imageData.src === src) {
                    thumbDiv.classList.add('active');
                }
                
                var thumbImg = document.createElement('img');
                thumbImg.src = imageData.src;
                thumbImg.alt = imageData.alt;
                
                thumbDiv.appendChild(thumbImg);
                thumbsContainer.appendChild(thumbDiv);
                
                // Add click event to thumbnail
                thumbDiv.addEventListener('click', function() {
                    // Reset zoom when switching images
                    if (modalImage._resetZoom) {
                        modalImage._resetZoom();
                    }
                    
                    modalImage.src = imageData.src;
                    modalImage.alt = imageData.alt;
                    
                    // Update active thumbnail
                    document.querySelectorAll('.gallery-modal-thumb').forEach(function(thumb) {
                        thumb.classList.remove('active');
                    });
                    thumbDiv.classList.add('active');
                });
            });
        }

        // Close gallery modal
        function closeGalleryModal() {
            var modal = document.getElementById('galleryModal');
            var modalImage = document.getElementById('galleryModalImage');
            
            // Reset zoom when closing modal
            if (modalImage && modalImage._resetZoom) {
                modalImage._resetZoom();
            }
            
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Thumbnail gallery functionality with cleanup
        (function() {
            let thumbClickHandlers = [];
            
            function initThumbnailGallery() {
                // Clean up existing handlers to prevent memory leaks
                thumbClickHandlers.forEach(function(handler) {
                    handler.element.removeEventListener('click', handler.fn);
                });
                thumbClickHandlers = [];
                
                const thumbItems = document.querySelectorAll('.thumb-item');
                const mainImages = document.querySelectorAll('.main-image');
                
                thumbItems.forEach(function(thumb, index) {
                    const handler = function() {
                        // Remove active class from all thumbs
                        thumbItems.forEach(t => t.classList.remove('active'));
                        // Add active class to clicked thumb
                        thumb.classList.add('active');
                        
                        // Hide all main images
                        mainImages.forEach(img => img.style.display = 'none');
                        
                        // Show corresponding main image
                        const imageType = thumb.getAttribute('data-image-type');
                        const mainImage = document.querySelector(`.main-image[data-image-type="${imageType}"]`);
                        if (mainImage) {
                            mainImage.style.display = 'block';
                        }
                    };
                    
                    thumb.addEventListener('click', handler);
                    thumbClickHandlers.push({ element: thumb, fn: handler });
                });
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initThumbnailGallery);
            } else {
                initThumbnailGallery();
            }
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                thumbClickHandlers.forEach(function(handler) {
                    handler.element.removeEventListener('click', handler.fn);
                });
                thumbClickHandlers = [];
            });
        })();

        // Test elements on page load - use requestAnimationFrame to prevent memory issues
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
            var qtyInput = document.getElementById('quantityInput');
            var qtyButtons = document.querySelectorAll('.quantity-btn');
            var gallery = document.querySelector('.product-gallery');
            var thumbSlides = document.querySelectorAll('.thumb-item');
            // Initialize inline stock for simple products (no variations)
            try {
                var stockInline = document.getElementById('size-stock-inline') || document.getElementById('inline-stock-display');
                if (stockInline && stockInline.getAttribute('data-has-variations') === '0') {
                    var initial = parseInt(stockInline.getAttribute('data-initial-stock') || '0', 10);
                    setInlineStock(initial);
                }
            } catch (e) { console.warn('Stock init failed', e); }
            
            console.log('[PD] Elements found:');
            console.log('[PD] - Quantity input:', !!qtyInput);
            console.log('[PD] - Quantity buttons:', qtyButtons.length);
            console.log('[PD] - Gallery:', !!gallery);
            console.log('[PD] - Thumb slides:', thumbSlides.length);
            console.log('[PD] - Swiper loaded:', typeof Swiper !== 'undefined');
            console.log('[PD] - showToast function:', typeof showToast === 'function');
            
            // Test quantity functionality
            if (qtyInput && qtyButtons.length > 0) {
                console.log('[PD] Testing quantity controls...');
                var originalValue = qtyInput.value;
                window.changeQuantity(1);
                if (qtyInput.value != originalValue) {
                    console.log('[PD] ✓ Quantity controls working');
                    qtyInput.value = originalValue; // Reset
                } else {
                    console.log('[PD] ✗ Quantity controls not working');
                }
            }
            
            // Test image gallery
            if (gallery && thumbSlides.length > 0) {
                console.log('[PD] Testing image gallery...');
                if (window.mainSwiper && window.thumbSwiper) {
                    console.log('[PD] ✓ Swiper gallery initialized with', thumbSlides.length, 'thumbnails');
                } else {
                    console.log('[PD] ✗ Swiper gallery not initialized');
                }
            }
            
            // Test wishlist functionality
            var wishlistBtn = document.querySelector('button[onclick="addToWishlist()"]');
            if (wishlistBtn) {
                console.log('[PD] ✓ Wishlist button found');
                console.log('[PD] ✓ addToWishlist function available:', typeof addToWishlist === 'function');
            } else {
                console.log('[PD] ✗ Wishlist button not found');
            }
            
            // Test toast functionality
            console.log('[PD] Testing toast notifications...');
            if (typeof showToast === 'function') {
                console.log('[PD] ✓ Toast system ready');
            } else {
                console.log('[PD] ✗ Toast system not available');
            }
            });
        });
        
        // Optimized retry with cleanup to prevent memory leaks
        (function retryInit(attempts){
            if (typeof window.initializePageSpecificScripts === 'function') {
                try {
                    window.initializePageSpecificScripts();
                    console.log('[PD] initializePageSpecificScripts invoked from section');
                } catch(e) {
                    console.error('[PD] init error', e);
                }
            } else if (attempts > 0) {
                // Use requestAnimationFrame instead of setTimeout to prevent memory buildup
                requestAnimationFrame(function(){ 
                    retryInit(attempts - 1); 
                });
            } else {
                console.warn('[PD] initializer not found after retries');
            }
        })(5); // Reduced from 30 to 5 to prevent memory issues
    </script>

    <!-- Toast Container -->
    <div id="toast-container"
        style="position: fixed; top: 24px; right: 24px; z-index: 16000; display: flex; flex-direction: column; gap: 10px;">
    </div>

    <!-- Gallery Modal -->
    <div id="galleryModal" class="gallery-modal">
        <div class="gallery-modal-content">
            <span class="gallery-close">&times;</span>
            <div class="gallery-modal-main">
                <div class="gallery-modal-image-container">
                    <img id="galleryModalImage" src="" alt="" class="gallery-modal-image">
                </div>
            </div>
            <div class="gallery-modal-thumbs">
                <!-- Thumbnails will be dynamically populated by JavaScript -->
            </div>
        </div>
    </div>

@if(isset($product) && $product && ($general_settings->gtm_container_id ?? null))
<script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        'event': 'view_item',
        'ecommerce': {
            'currency': 'BDT',
            'value': {{ $product->discount ?? $product->price }},
            'items': [{
                'item_id': '{{ $product->id }}',
                'item_name': {!! json_encode($product->name) !!},
                'item_category': {!! json_encode($product->category->name ?? '') !!},
                'price': {{ $product->discount ?? $product->price }},
                'quantity': 1
            }]
        }
    });
</script>
@endif

@endsection

@push('scripts')
    <script>
        console.log('[PD] productDetails scripts executing');
        console.log('[PD] Script section loaded successfully');
        // Image modal removed per requirements; keep a no-op cleaner in case of legacy backdrops
        function removeStuckBackdrops(){
            try {
                document.querySelectorAll('.modal-backdrop').forEach(function(el){ el.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            } catch(_) {}
        }

        // Initialize Related Products Splide (matching New Arrivals)
        // Prevent multiple initializations with a flag
        let relatedProductsSplideInitialized = false;
        let relatedProductsSplideInstance = null;
        let splideRetryCount = 0;
        const maxRetries = 30; // Max 15 seconds wait
        
        function initRelatedProductsSplide(){
            // Prevent multiple initializations
            if (relatedProductsSplideInitialized) {
                console.log('[SPLIDE] Already initialized, skipping...');
                return;
            }
            
            const wrapper = document.getElementById('relatedProductsSplide');
            const listEl = document.getElementById('relatedProductsSplideList');

            if (!wrapper || !listEl) {
                // Retry if elements not found yet
                if (splideRetryCount < maxRetries) {
                    splideRetryCount++;
                    setTimeout(initRelatedProductsSplide, 200);
                    return;
                }
                console.log('[SPLIDE] Elements not found after retries');
                return;
            }

            // Check if already has a Splide instance
            if (wrapper.splide && wrapper.splide.Components) {
                console.log('[SPLIDE] Instance exists, refreshing...');
                try {
                    wrapper.splide.refresh();
                    relatedProductsSplideInitialized = true;
                    wrapper.style.visibility = 'visible';
                    wrapper.style.opacity = '1';
                    wrapper.style.display = 'block';
                    return;
                } catch(e) {
                    console.log('[SPLIDE] Refresh failed, will reinitialize');
                }
            }

            // Wait for Splide to be available
            const SplideClass = window.Splide || (typeof Splide !== 'undefined' ? Splide : null);
            
            if (!SplideClass) {
                splideRetryCount++;
                if (splideRetryCount >= maxRetries) {
                    console.error('[SPLIDE] Splide library failed to load');
                    wrapper.style.display = 'block';
                    wrapper.style.visibility = 'visible';
                    return;
                }
                setTimeout(initRelatedProductsSplide, 500);
                return;
            }

            const products = listEl.querySelectorAll('.splide__slide');
            
            if (products.length === 0) {
                console.warn('[SPLIDE] No products found');
                wrapper.style.display = 'none';
                return;
            }

            // Reset retry count on success
            splideRetryCount = 0;

            // Make visible immediately
            wrapper.style.visibility = 'visible';
            wrapper.style.opacity = '1';
            wrapper.style.display = 'block';
            
            // Use double requestAnimationFrame for better timing
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    const actualSlideCount = products.length;
                    const perPage = window.innerWidth >= 1200 ? 4 : window.innerWidth >= 992 ? 3 : 2;
                    const canLoop = actualSlideCount > perPage;
                    
                    try {
                        // Destroy existing instance if any
                        if (relatedProductsSplideInstance) {
                            try {
                                relatedProductsSplideInstance.destroy();
                            } catch(e) {}
                        }
                        
                        const rpSplide = new SplideClass(wrapper, {
                            type: canLoop ? 'loop' : 'slide',
                            perPage: perPage,
                            gap: '24px',
                            pagination: false,
                            arrows: products.length > perPage,
                            autoplay: canLoop,
                            interval: 4000,
                            pauseOnHover: true,
                            rewind: true,
                            breakpoints: { 
                                1199: { perPage: 3 }, 
                                991: { perPage: 2 }, 
                                575: { perPage: 2 } 
                            }
                        });
                        
                        rpSplide.mount();
                        relatedProductsSplideInstance = rpSplide;
                        relatedProductsSplideInitialized = true;
                        
                        // Ensure visibility
                        wrapper.style.visibility = 'visible';
                        wrapper.style.opacity = '1';
                        wrapper.style.display = 'block';
                        
                        // Function to ensure visible slides are shown
                        const ensureVisibleSlides = () => {
                            try {
                                // Get all slides that should be visible
                                const visibleSlides = wrapper.querySelectorAll('.splide__slide.is-visible, .splide__slide.is-active, .splide__slide.is-next, .splide__slide.is-prev');
                                visibleSlides.forEach(slide => {
                                    slide.style.opacity = '1';
                                    slide.style.visibility = 'visible';
                                    const card = slide.querySelector('.pd-product-card');
                                    if (card) {
                                        card.style.opacity = '1';
                                        card.style.visibility = 'visible';
                                    }
                                });
                            } catch(e) {}
                        };
                        
                        // Ensure visibility immediately and after delays
                        requestAnimationFrame(() => {
                            ensureVisibleSlides();
                        });
                        
                        // Use requestAnimationFrame with delay simulation
                        var delay1Start = performance.now();
                        function delayedRefresh() {
                            if (performance.now() - delay1Start >= 100) {
                                try {
                                    if (rpSplide.Components && rpSplide.Components.Slides) {
                                        rpSplide.refresh();
                                    }
                                    ensureVisibleSlides();
                                } catch(e) {}
                            } else {
                                requestAnimationFrame(delayedRefresh);
                            }
                        }
                        requestAnimationFrame(delayedRefresh);
                        
                        var delay2Start = performance.now();
                        function delayedVisibility() {
                            if (performance.now() - delay2Start >= 300) {
                                ensureVisibleSlides();
                            } else {
                                requestAnimationFrame(delayedVisibility);
                            }
                        }
                        requestAnimationFrame(delayedVisibility);
                        
                        // Ensure visibility on carousel events
                        if (rpSplide.on) {
                            rpSplide.on('moved', ensureVisibleSlides);
                            rpSplide.on('updated', ensureVisibleSlides);
                        }
                        
                        // Autoplay - use requestAnimationFrame
                        if (canLoop && rpSplide.Components && rpSplide.Components.Autoplay) {
                            var autoplayStart = performance.now();
                            function startAutoplay() {
                                if (performance.now() - autoplayStart >= 200) {
                                    try {
                                        if (rpSplide.Components && rpSplide.Components.Autoplay) {
                                            rpSplide.Components.Autoplay.play();
                                        }
                                    } catch(e) {}
                                } else {
                                    requestAnimationFrame(startAutoplay);
                                }
                            }
                            requestAnimationFrame(startAutoplay);
                        }
                        
                        console.log('[SPLIDE] Carousel initialized successfully');
                    } catch (error) {
                        console.error('[SPLIDE] Initialization error:', error);
                        wrapper.style.visibility = 'visible';
                        wrapper.style.opacity = '1';
                        wrapper.style.display = 'block';
                        relatedProductsSplideInitialized = false;
                    }
                });
            });
        }
        
        // Robust initialization with multiple checks
        function startRelatedProductsInit() {
            const checkAndInit = () => {
                const wrapper = document.getElementById('relatedProductsSplide');
                const listEl = document.getElementById('relatedProductsSplideList');
                const hasSplide = window.Splide || (typeof Splide !== 'undefined');
                
                if (wrapper && listEl && hasSplide && !relatedProductsSplideInitialized) {
                    initRelatedProductsSplide();
                } else if (!relatedProductsSplideInitialized) {
                    setTimeout(checkAndInit, 100);
                }
            };
            
            setTimeout(checkAndInit, 100);
        }
        
        // Multiple initialization triggers
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startRelatedProductsInit);
        } else {
            startRelatedProductsInit();
        }
        
        // Backup initialization on window load
        window.addEventListener('load', () => {
            // Use requestAnimationFrame with delay simulation to prevent memory buildup
            var startTime = performance.now();
            function checkInit() {
                if (performance.now() - startTime >= 500) {
                    if (!relatedProductsSplideInitialized) {
                        startRelatedProductsInit();
                    }
                } else {
                    requestAnimationFrame(checkInit);
                }
            }
            requestAnimationFrame(checkInit);
        });


        // Image preview functionality
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                    document.querySelector('.image-upload-area').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage() {
            document.getElementById('image').value = '';
            document.getElementById('image-preview').style.display = 'none';
            document.querySelector('.image-upload-area').style.display = 'block';
        }

		// Add event listener for image input (guard element existence on this page)
		var imageInputEl = document.getElementById('image');
		if (imageInputEl) {
			imageInputEl.addEventListener('change', function() {
				previewImage(this);
			});
		}

        // Image modal functionality
        function openImageModal(src) {
            var modal = document.getElementById('imageModal');
            var modalImg = document.getElementById('modalImage');
            modal.style.display = 'block';
            modalImg.src = src;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal when clicking outside the image
        window.onclick = function(event) {
            var modal = document.getElementById('imageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }


        // Expose page-specific initializer so master layout can re-run after AJAX navigation
        window.initializePageSpecificScripts = function(){
            // Reset the flag to allow re-initialization after AJAX navigation
            if (window.__productPageInitApplied && !window.__allowReinit) { 
                console.log('[PD] Page already initialized, skipping');
                return; 
            }
            window.__productPageInitApplied = true;
            
            // Initialize variation selection after AJAX navigation
            if (typeof window.initializeVariationSelection === 'function') {
                window.initializeVariationSelection();
            }

            // Tabs click handler
            document.querySelectorAll('.pd-tab-btn').forEach(btn => {
                btn.onclick = function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active from all buttons and contents
                    document.querySelectorAll('.pd-tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.pd-tab-content').forEach(c => c.classList.remove('active'));
                    
                    // Add active to current
                    this.classList.add('active');
                    const target = document.getElementById(tabId);
                    if (target) target.classList.add('active');
                };
            });

            // Related product card clicks
            document.querySelectorAll('.pd-product-card').forEach(card => {
                card.onclick = function(e) {
                    if (e.target.closest('.pd-card-wishlist')) return;
                    const href = this.getAttribute('data-href');
            if (href) window.location.href = href;
                };
            });

            // Scroll indicator listener
            const scrollIndicator = document.querySelector('.pd-scroll-indicator');
            if (scrollIndicator) {
                window.onscroll = function() {
                    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    const scrolled = (winScroll / height) * 100;
                    scrollIndicator.style.width = scrolled + "%";
                };
            }

            // Cart functionality is now handled by global cart handler in master.blade.php
            // No need for duplicate event listeners here
            
            // Keep variation-specific logic for product details page
            // DISABLED: Using global cart handler instead
            window.__cartEventListener = function(e){
                return; // Function disabled - using global cart handler
                var btnEl = e.target.closest ? e.target.closest('.btn-add-cart') : null;
                if (!btnEl) return;
                e.preventDefault();
                
                // Prevent multiple simultaneous requests
                if (btnEl.disabled || btnEl.getAttribute('data-processing') === 'true') {
                    console.log('[CART] Request already in progress, ignoring click');
                    return;
                }
                
                // Mark button as processing
                btnEl.setAttribute('data-processing', 'true');
                
                // Get product ID from data attribute or fallback to PHP variable
                var productId = btnEl.getAttribute('data-product-id') || {{ $product->id ?? 'null' }};
                var productName = btnEl.getAttribute('data-product-name') || '{{ $product->name ?? "Unknown Product" }}';
                
                // Validate product ID
                if (!productId || productId === 'null' || productId === '') {
                    console.error('[CART] Invalid product ID:', productId);
                    alert('Error: Invalid product ID. Please refresh the page and try again.');
                    btnEl.removeAttribute('data-processing');
                    return;
                }
                
                var qtyInput = document.getElementById('quantityInput');
                var qty = parseInt(qtyInput && qtyInput.value ? qtyInput.value : 1, 10) || 1;
                btnEl.disabled = true;
                var data = new URLSearchParams();
                data.append('qty', qty.toString());

                // Variation payload - use server flag as primary source, DOM as secondary check
                var hasVariations = @json($product->has_variations);
                
                // Only override server flag if DOM clearly shows variation elements exist
                // Limit query to prevent memory issues
                var variationElements = document.querySelectorAll('.color-option, .size-option, .color-image-btn, .variation-option');
                var hasVariationsDOM = variationElements.length > 0;
                
                // Trust the server flag primarily, but if DOM shows variations and server says no, use DOM
                if (hasVariationsDOM && !hasVariations) {
                    hasVariations = true;
                }
                
                // Clean up reference to prevent memory leak
                variationElements = null;

                var variationIdEl = document.getElementById('selected-variation-id');
                var variationId = variationIdEl ? variationIdEl.value : '';

                if (variationId) {
                    // Always send variation_id if present (even if server flag says no variations)
                    data.append('variation_id', variationId);
                } else if (hasVariations) {
                    // Require explicit selection
                    showToast('Please select Color and Size before adding to cart.', 'error');
                    btnEl.disabled = false;
                    btnEl.removeAttribute('data-processing');
                    variationIdEl = null; // Clean up
                    return;
                }
                
                // Clean up references
                variationIdEl = null;
                
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
                fetch('/cart/add-page/' + productId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: data.toString()
                }).then(function(res){
                    console.log('[CART] Response status:', res.status);
                    
                    // Handle authentication redirect (401 status)
                    if (res.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status + ': ' + res.statusText);
                    }
                    return res.json().catch(function(){
                        return { success:false, message:'Invalid JSON from server', status: res.status };
                    });
                }).then(function(response){
                    if (response && response.success) {
                        showToast((response.message || 'Product added to cart successfully!'), 'success');
                        if (typeof updateCartCount === 'function') { updateCartCount(); }
                        if (typeof updateCartQtyBadge === 'function') { updateCartQtyBadge(); }
                    } else if (response && response.redirect) {
                        // Check if response contains redirect URL (for authentication)
                        window.location.href = response.redirect;
                    }
                    // Clean up response reference
                    response = null;
                }).catch(function(error){
                    // Clean up error reference
                    error = null;
                }).finally(function(){
                    btnEl.disabled = false;
                    btnEl.removeAttribute('data-processing');
                    // Clean up references
                    data = null;
                    csrfMeta = null;
                    csrfToken = null;
                });
            };
            
            // Register the event listener - DISABLED: Using global cart handler instead
            // document.addEventListener('click', window.__cartEventListener);
            
            // Reset the re-init flag after initialization
            window.__allowReinit = false;
        };

        // Variation scripts moved to main content section for AJAX compatibility
        // Run initializer immediately for normal loads
        if (typeof window.initializePageSpecificScripts === 'function') {
            try { window.initializePageSpecificScripts(); } catch (e) { console.error('Init error', e); }
        }
    </script>

    <!-- NEW CLEAN REVIEW SYSTEM -->
    <script>
        $(document).ready(function() {
            // Function to get current product info dynamically
            function getCurrentProductInfo() {
                var productId = {{ $product->id }};
                var productName = '{{ $product->name }}';
                var productSlug = '{{ $product->slug }}';

                return {
                    id: productId,
                    name: productName,
                    slug: productSlug
                };
            }

        });

        // Global Configuration for Product Details
        window.PD_CONFIG = {
            productId: {{ $product->id }},
            productSlug: '{{ $product->slug }}',
            productName: {!! json_encode($product->name) !!}
        };

        // Review System JavaScript
        $(document).ready(function() {
            // Check if reviews container exists
            const reviewsContainer = $('#reviews-list');
            
            // source of truth: Global Config > Data Attribute > PHP injection
            let reviewProductId = window.PD_CONFIG.productId; 
            
            if (!reviewProductId && reviewsContainer.length > 0) {
                 reviewProductId = parseInt(reviewsContainer.data('product-id'));
            }
            const productSlug = reviewsContainer.data('product-slug');
            let currentPage = 1;
            let currentFilter = '';
            let isLoading = false;
            
            // Debug: Show product information
            console.log('=== REVIEW SYSTEM INITIALIZATION ===');
            console.log('Product ID from container:', reviewProductId);
            console.log('Product Slug from container:', productSlug);
            console.log('Product ID from PHP:', @json($product->id));
            console.log('Product Name from PHP:', @json($product->name));
            console.log('Product Slug from PHP:', @json($product->slug));
            console.log('Current URL:', window.location.href);
            console.log('Page Title:', document.title);
            console.log('Timestamp:', new Date().toISOString());
            
            // Additional debugging
            console.log('=== PHP DEBUG INFO ===');
            console.log('PHP Product ID:', {{ $product->id }});
            console.log('PHP Product Name:', '{{ $product->name }}');
            console.log('PHP Product Slug:', '{{ $product->slug }}');
            console.log('Container Product ID:', $('#reviews-list').data('product-id'));
            console.log('Container Product Slug:', $('#reviews-list').data('product-slug'));
            
            // Verify the product ID is correct
            if (reviewProductId !== @json($product->id)) {
                console.warn('⚠️ Product ID mismatch detected, using container product ID');
                // Don't return, just use the container's product ID
            }

            // Load reviews on page load
            loadReviews();

            // Initialize star rating interaction
            initializeStarRating();

            // Handle review form submission
            const reviewForm = $('#review-form');
            console.log('Review form found:', reviewForm.length > 0);
            if (reviewForm.length > 0) {
                reviewForm.on('submit', function(e) {
                    console.log('Form submit event triggered!');
                    e.preventDefault();
                    console.log('Prevented default, calling submitReview()');
                    submitReview();
                });
            } else {
                console.error('Review form not found!');
            }

            // Backup: Handle submit button click
            $('#review-form button[type="submit"]').on('click', function(e) {
                console.log('Submit button clicked!');
                e.preventDefault();
                submitReview();
            });

            // Handle review filter
            $('#reviews-filter').on('change', function() {
                currentFilter = $(this).val();
                currentPage = 1;
                loadReviews();
            });

            // Handle load more button
            $('#load-more-reviews').on('click', function() {
                loadMoreReviews();
            });

            // Load reviews function with retry logic
            let reviewRetryCount = 0;
            const maxReviewRetries = 2;
            
            function loadReviews(retryAttempt = 0) {
                if (isLoading) return;
                isLoading = true;
                
                // Show skeleton loaders on first load
                if (currentPage === 1) {
                    $('#reviews-skeleton').show();
                    $('#rating-summary-skeleton').show();
                    $('#rating-summary-content').hide();
                }

                const url = `/api/products/${reviewProductId}/reviews?page=${currentPage}&rating=${currentFilter}&_t=${Date.now()}`;
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            
                            // Reset retry count on success
                            reviewRetryCount = 0;
                            
                            // Validate reviews belong to correct product (silent check)
                            response.reviews.data.forEach((review) => {
                                if (review.product_id != reviewProductId) {
                                    // Silently skip mismatched reviews to prevent memory issues
                                    return;
                                }
                            });
                            
                            displayReviews(response.reviews.data);
                            updateRatingSummary(response.average_rating, response.total_reviews, response.rating_distribution);
                            updatePagination(response.pagination);
                            
                            // Hide skeleton loaders after displaying reviews
                            $('#reviews-skeleton').hide();
                            $('#rating-summary-skeleton').hide();
                            $('#rating-summary-content').show();
                            
                            // Hide any error messages
                            $('#reviews-error-message').remove();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading reviews:', xhr);
                        
                        // Auto-retry for transient network errors
                        const errorMessage = error || (xhr.responseJSON && xhr.responseJSON.message) || 'Network error';
                        if (retryAttempt < maxReviewRetries && (errorMessage.includes('Network') || errorMessage.includes('timeout') || status === 'timeout' || xhr.status === 0)) {
                            console.log(`Retrying reviews load (attempt ${retryAttempt + 1}/${maxReviewRetries})...`);
                            setTimeout(() => {
                                loadReviews(retryAttempt + 1);
                            }, 1000 * (retryAttempt + 1)); // Exponential backoff
                            return;
                        }
                        
                        // Show error with retry button instead of alert
                        showReviewsError('Failed to load reviews. Please try again.');
                        
                        // Hide skeleton loaders on error
                        $('#reviews-skeleton').hide();
                        $('#rating-summary-skeleton').hide();
                        $('#rating-summary-content').show();
                    },
                    complete: function() {
                        isLoading = false;
                    }
                });
            }

            // Load more reviews
            function loadMoreReviews() {
                currentPage++;
                loadReviews();
            }

            // Display reviews
            function displayReviews(reviews) {
                const reviewsList = $('#reviews-list');
                const containerProductId = reviewsList.data('product-id');
                
                // Verify we're displaying reviews for the correct product
                if (containerProductId != reviewProductId) {
                    return; // Don't display reviews if there's a mismatch
                }
                
                // Limit reviews to prevent memory issues
                if (reviews.length > 50) {
                    reviews = reviews.slice(0, 50);
                }
                
                if (currentPage === 1) {
                    // Hide skeleton and clear list
                    $('#reviews-skeleton').hide();
                    reviewsList.empty();
                }

                if (reviews.length === 0 && currentPage === 1) {
                    reviewsList.html(`
                        <div class="no-reviews py-5 text-center">
                            <i class="fas fa-comments mb-3" style="font-size: 3rem; color: #e2e8f0;"></i>
                            <h5 class="text-muted fw-bold">No Experiences Shared Yet</h5>
                            <p class="text-muted small">Be the first to share your experience with this premium selection.</p>
                        </div>
                    `);
                    return;
                }

                reviews.forEach(function(review) {
                    const reviewHtml = createReviewHtml(review);
                    reviewsList.append(reviewHtml);
                });
            }

            // Create review HTML
            function createReviewHtml(review) {
                const stars = generateStars(review.rating);
                const date = new Date(review.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
                const isFeatured = review.is_featured || false;
                const userInitial = review.user_name ? review.user_name.charAt(0).toUpperCase() : '?';
                
                return `
                    <div class="pd-review-item ${isFeatured ? 'featured' : ''}" data-review-id="${review.id}">
                        <div class="pd-review-user">
                            <div class="pd-user-avatar">${userInitial}</div>
                            <div class="pd-user-meta text-start">
                                <h5>${review.user_name} <span class="ms-2 text-muted fw-normal">${date}</span></h5>
                                <div class="pd-score-stars text-start mt-1" style="font-size: 11px;">${stars}</div>
                            </div>
                        </div>
                        <div class="pd-review-content">${review.comment}</div>
                    </div>
                `;
            }

            // Update rating summary
            function updateRatingSummary(averageRating, totalReviews, ratingDistribution) {
                $('#overall-rating').text(averageRating.toFixed(1));
                $('#rating-count').text(`${totalReviews} review${totalReviews !== 1 ? 's' : ''}`);
                
                // Update stars
                const stars = generateStars(Math.round(averageRating));
                $('#rating-stars').html(stars);

                // Update rating bars
                updateRatingBars(ratingDistribution, totalReviews);
            }

            // Update rating bars
            function updateRatingBars(distribution, totalReviews) {
                const barsContainer = $('#rating-bars');
                barsContainer.empty();

                for (let i = 5; i >= 1; i--) {
                    const count = distribution[i] || 0;
                    const percentage = totalReviews > 0 ? (count / totalReviews) * 100 : 0;
                    
                    barsContainer.append(`
                        <div class="pd-rating-row">
                            <span class="pd-rating-label">${i} Star</span>
                            <div class="pd-progress-wrapper">
                                <div class="pd-progress-bar" style="width: ${percentage}%"></div>
                            </div>
                            <span class="pd-rating-count-val">${count}</span>
                        </div>
                    `);
                }
            }

            // Update pagination
            function updatePagination(pagination) {
                const loadMoreContainer = $('#load-more-container');
                if (pagination.current_page < pagination.last_page) {
                    loadMoreContainer.show();
                } else {
                    loadMoreContainer.hide();
                }
            }

            // Initialize star rating
            function initializeStarRating() {
                $('.pd-star-label').on('click', function() {
                    const rating = $(this).data('rating');
                    // Radio button is handled by browser, but we can trigger state updates here
                });

                // The CSS handles most of the hover effects now with the row-reverse trick
                // but we keep the text hint update
                $('.pd-star-label').on('mouseenter', function() {
                    const rating = $(this).data('rating');
                    updateRatingHint(rating);
                });

                $('.pd-stars-input').on('mouseleave', function() {
                    const selectedRating = $('input[name="rating"]:checked').val();
                    updateRatingHint(selectedRating || 0);
                });
            }

            function updateRatingHint(rating) {
                const ratingTexts = {
                    1: 'Poor',
                    2: 'Fair',
                    3: 'Good',
                    4: 'Very Good',
                    5: 'Excellent'
                };
                $('#rating-text').text(ratingTexts[rating] || 'Select a rating');
            }

            // Submit review
            function submitReview() {
                console.log('submitReview() function called!');
                const rating = $('input[name="rating"]:checked').val();
                const comment = $('#comment').val();

                console.log('Review data:', { rating, comment });

                if (!rating) {
                    console.log('No rating selected');
                    alert('Please select a rating');
                    return;
                }

                if (!comment.trim() || comment.trim().length < 5) {
                    console.log('Comment too short or empty:', comment);
                    alert('Please write a review comment (at least 5 characters)');
                    return;
                }

                console.log('Validation passed, proceeding with submission');

                const formData = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    rating: rating,
                    comment: comment
                };
                
                
                // Ensure we have a valid product ID
                const targetProductId = reviewProductId || window.PD_CONFIG.productId;

                if (!targetProductId) {
                    console.error('Missing product ID for review submission');
                    alert('System Error: Product ID not found. Please refresh the page.');
                    return;
                }

                console.log('Submitting review for product ID:', targetProductId);

                $.ajax({
                    url: `/api/products/${targetProductId}/reviews`,
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('Sending review submission...');
                    },
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response.success) {
                            if (typeof showToast === 'function') {
                                showToast('Review submitted successfully!', 'success');
                            } else {
                                alert('Review submitted successfully!');
                            }
                            $('#review-form')[0].reset();
                            updateStarDisplay(0);
                            currentPage = 1;
                            loadReviews();
                        } else {
                            if (typeof showToast === 'function') {
                                showToast(response.message || 'Error submitting review', 'error');
                            } else {
                                alert(response.message || 'Error submitting review');
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Review submission failed:', {status, error, response: xhr.responseText});
                        
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            // Show validation errors
                            let errorMessage = 'Validation failed:\n';
                            for (const field in response.errors) {
                                errorMessage += `• ${response.errors[field][0]}\n`;
                            }
                            alert(errorMessage);
                        } else {
                            alert(response ? response.message : 'Error submitting review: ' + error);
                        }
                    }
                });
            }

            // Generate stars HTML
            function generateStars(rating) {
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    stars += `<i class="fa${i <= rating ? 's' : 'r'} fa-star"></i>`;
                }
                return stars;
            }

            // Show error message with retry option (replaces alert)
            // IMPORTANT: This function replaces the old showError() that used alert()
            function showReviewsError(message) {
                // Remove any existing error message
                $('#reviews-error-message').remove();
                
                // Create error message with retry button
                const errorHtml = `
                    <div id="reviews-error-message" class="col-12 text-center py-4" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin: 20px 0;">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3" style="color: #856404;"></i>
                        <p style="color: #856404; margin-bottom: 15px;">${message}</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="window.retryLoadReviews && window.retryLoadReviews()">
                            <i class="fas fa-refresh me-1"></i>Retry
                        </button>
                    </div>
                `;
                
                // Insert error message before reviews list
                const reviewsList = $('#reviews-list');
                if (reviewsList.length) {
                    reviewsList.before(errorHtml);
                } else {
                    // If reviews list doesn't exist, append to reviews container
                    const reviewsContainer = $('#reviews-container, .reviews-section, #product-reviews');
                    if (reviewsContainer.length) {
                        reviewsContainer.append(errorHtml);
                    } else {
                        // Fallback: use toast notification
                        if (typeof showToast === 'function') {
                            showToast(message, 'error');
                        } else {
                            console.error(message);
                        }
                    }
                }
            }
            
            // Override any old showError function to prevent alert dialogs
            // This ensures that even if cached code tries to call showError, it won't show alert
            // CRITICAL: This prevents the alert dialog from showing in production
            window.showError = function(message) {
                console.warn('Old showError() called - redirecting to showReviewsError() to prevent alert dialog');
                // Always use showReviewsError instead of alert
                if (typeof showReviewsError === 'function') {
                    showReviewsError(message);
                } else {
                    // Fallback: use toast or console, never alert
                    if (typeof showToast === 'function') {
                        showToast(message, 'error');
                    } else {
                        console.error('Review error:', message);
                    }
                }
            };
            
            // Make retry function globally available
            window.retryLoadReviews = function() {
                reviewRetryCount = 0;
                currentPage = 1;
                $('#reviews-list').empty();
                loadReviews(0);
            };
        });

    </script>
@endpush





  