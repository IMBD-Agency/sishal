@if($products->count() > 0)
    @foreach($products as $product)
        <div class="col-6 col-md-4 col-lg-3 mb-3 px-2 px-md-3">
            <div class="product-card position-relative mb-0 h-100" 
                style="border: 1px solid #e5e7eb !important; box-shadow: none !important; border-radius: 4px !important; background: #fff !important; transition: border-color 0.3s ease !important;"
                data-href="{{ route('product.details', $product->slug) }}"
                data-gtm-id="{{ $product->id }}"
                data-gtm-name="{{ $product->name }}"
                data-gtm-price="{{ $product->effective_price }}"
                data-gtm-category="{{ $product->category->name ?? '' }}">
                
                @php
                    $effectivePrice = $product->effective_price;
                    $originalPrice = $product->original_price;
                    $hasDiscount = $product->hasDiscount() && $effectivePrice < $originalPrice;
                    $discountPercentage = $hasDiscount ? round((($originalPrice - $effectivePrice) / $originalPrice) * 100) : 0;
                    $saveAmount = $hasDiscount ? ($originalPrice - $effectivePrice) : 0;
                @endphp

                <!-- Top Discount Badge -->
                @if($hasDiscount)
                    <div class="product-discount-label">-{{ $discountPercentage }}%</div>
                @endif

                <!-- Top Wishlist Button -->
                <button class="product-wishlist-top {{$product->is_wishlisted ? ' active' : ''}}"
                    data-product-id="{{ $product->id }}"
                    onclick="event.stopPropagation(); toggleWishlist({{ $product->id }});"
                    title="Add to Wishlist">
                    <i class="{{ $product->is_wishlisted ? 'fas' : 'far' }} fa-heart"></i>
                </button>

                <div class="product-image-container">
                    <img src="{{$product->image ? asset($product->image) : asset('static/default-product.jpg')}}"
                        class="product-image"
                        alt="{{ $product->name }}"
                        loading="lazy"
                        onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                </div>
                
                <div class="product-info p-3">
                    <a href="{{ route('product.details', $product->slug) }}" 
                       class="product-title stretched-link" 
                       style="text-decoration: none; font-weight: 500; color: #374151; display: block; line-height: 1.4; margin-bottom: 2px;"
                       title="{{ $product->name }}">
                        {{ $product->name }}
                    </a>
                    
                    @if($hasDiscount)
                        <div class="save-badge mt-1" style="font-size: 9px; padding: 2px 6px; position: relative; z-index: 2;">Save ৳ {{ number_format($saveAmount) }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <div class="price-container" style="position: relative; z-index: 2;">
                            <div class="product-price-current fw-bold" style="color: #1a1a1a; font-size: 1.1rem;">
                                ৳ {{ number_format($effectivePrice) }}
                            </div>
                            @if($hasDiscount)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="product-price-old text-muted text-decoration-line-through" style="font-size: 0.8rem;">
                                        ৳ {{ number_format($originalPrice) }}
                                    </span>
                                    <span class="product-discount-text" style="color: #ef4444; font-size: 0.8rem; font-weight: 600;">
                                        -{{ $discountPercentage }}%
                                    </span>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('product.details', $product->slug) }}" 
                           class="floating-cart-btn" 
                           onclick="event.stopPropagation();" 
                           title="Add to Cart"
                           style="position: relative; z-index: 3;">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="col-12">
        <div class="no-products-container">
            <div class="no-products-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="no-products-title">No Best Deals Found</h3>
            <p class="no-products-message">We couldn't find any products in the best deal section at the moment.</p>
        </div>
    </div>
@endif
