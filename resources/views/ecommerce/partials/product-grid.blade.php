@if($products->count() > 0)
    @foreach($products as $product)
        <div class="col-lg-3 col-md-6 col-6 mt-0 mb-3 mb-md-4">
            <div class="product-card position-relative mb-0 h-100" 
                data-href="{{ route('product.details', $product->slug) }}"
                data-gtm-id="{{ $product->id }}"
                data-gtm-name="{{ $product->name }}"
                data-gtm-price="{{ $product->discount ?? $product->price }}"
                data-gtm-category="{{ $product->category->name ?? '' }}">
                <!-- Top Wishlist Button -->
                <button class="product-wishlist-top {{$product->is_wishlisted ? ' active' : ''}}"
                    data-product-id="{{ $product->id }}"
                    onclick="event.stopPropagation(); toggleWishlist({{ $product->id }});"
                    title="Add to Wishlist">
                    <i class="{{ $product->is_wishlisted ? 'fas' : 'far' }} fa-heart"></i>
                </button>
                
                <!-- Original Wishlist Button (keeping for compatibility) -->
                <button class="wishlist-btn {{$product->is_wishlisted ? ' active' : ''}}"
                    data-product-id="{{ $product->id }}"
                    onclick="event.stopPropagation();">
                    <i class="{{ $product->is_wishlisted ? 'fas text-danger' : 'far' }} fa-heart"></i>
                </button>
                <div class="product-image-container">
                    <img src="{{$product->image ? asset($product->image) : asset('static/default-product.jpg')}}"
                        class="product-image"
                        alt="{{ $product->name }}"
                        loading="lazy"
                        onerror="this.onerror=null; this.src='{{ asset('static/default-product.jpg') }}';">
                </div>
                <div class="product-info">
                    <a href="{{ route('product.details', $product->slug) }}" class="product-title"
                        style="text-decoration: none;">{{ $product->name }} <small class="text-muted" style="font-size: 0.8em; font-weight: normal;">#{{ $product->style_number ?? $product->sku }}</small></a>
                    <div class="product-description">{!! $product->short_desc ? $product->short_desc : '' !!}</div>
                    <div class="product-meta" style="margin-top:6px;">
                        @php
                            $avgRating = $product->avg_rating ?? 0;
                            $totalReviews = $product->total_reviews ?? 0;
                        @endphp
                        <div class="stars" aria-label="{{ $avgRating }} out of 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa{{ $i <= $avgRating ? 's' : 'r' }} fa-star"></i>
                            @endfor
                        </div>
                        <div class="rating-text" style="font-size: 12px; color: #666; margin-top: 2px;">
                            ({{ $totalReviews }} review{{ $totalReviews !== 1 ? 's' : '' }})
                        </div>
                    </div>

                    <div class="price">
                        @php
                            $effectivePrice = $product->effective_price;
                            $originalPrice = $product->original_price;
                            $hasDiscount = $product->hasDiscount();
                        @endphp
                        @if($hasDiscount && $effectivePrice < $originalPrice)
                            <span class="fw-bold text-primary">
                                {{ number_format($effectivePrice, 2) }}৳
                            </span>
                            <span class="text-muted text-decoration-line-through ms-2">
                                {{ number_format($originalPrice, 2) }}৳
                            </span>
                        @else
                            <span class="fw-bold text-primary">
                                {{ number_format($originalPrice, 2) }}৳
                            </span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        @php
                            $hasStock = $product->has_stock ?? false;
                        @endphp
                        <a href="{{ route('product.details', $product->slug) }}" class="btn-add-cart" style="text-decoration: none; display: inline-flex; justify-content: center; align-items: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" fill="#fff" width="14" height="14">
                                <path
                                    d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z">
                                </path>
                                <circle cx="7" cy="22" r="2"></circle>
                                <circle cx="17" cy="22" r="2"></circle>
                            </svg> View Product</a>
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
            <h3 class="no-products-title">No Products Found</h3>
            <p class="no-products-message">We couldn't find any products matching your current filters.</p>
            <div class="no-products-suggestion">
                <i class="fas fa-lightbulb"></i>
                <span>Try adjusting your filters to see more products</span>
            </div>
        </div>
    </div>
@endif

@if($products->hasPages() && !isset($hidePagination))
    <div class="col-12">
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
@endif
