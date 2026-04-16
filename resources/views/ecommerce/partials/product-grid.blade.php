@if($products->count() > 0)
    @foreach($products as $product)
        <div class="col-lg-3 col-md-6 col-6 mt-0 mb-3 mb-md-4">
            <div class="product-card position-relative" 
                data-href="{{ route('product.details', $product->slug) }}">
                
                @php
                    $effectivePrice = $product->effective_price;
                    $originalPrice = $product->original_price;
                    $hasDiscount = $product->hasDiscount() && $effectivePrice < $originalPrice;
                @endphp

                <div class="product-card-image">
                    <img src="{{$product->image ? asset($product->image) : asset('static/default-product.jpg')}}"
                        alt="{{ $product->name }}"
                        loading="lazy">
                    
                    <button class="product-wishlist-top {{$product->is_wishlisted ? ' active' : ''}}"
                        data-product-id="{{ $product->id }}"
                        onclick="event.stopPropagation(); toggleWishlist({{ $product->id }});"
                        style="position: absolute; top: 12px; right: 12px; z-index: 10; border: none; background: transparent; color: #9ca3af; transition: all 0.3s;">
                        <i class="{{ $product->is_wishlisted ? 'fas text-danger' : 'far' }} fa-heart" style="font-size: 1.1rem;"></i>
                    </button>

                    <button class="floating-cart-btn" 
                            title="Select Options"
                            onclick="event.stopPropagation(); window.location.href='{{ route('product.details', $product->slug) }}'"
                            style="z-index: 11; position: relative;">
                        <i class="fas fa-shopping-basket"></i>
                    </button>
                </div>

                <div class="product-card-info">
                    <h3 class="product-title">
                        <a href="{{ route('product.details', $product->slug) }}" class="stretched-link" style="text-decoration: none; color: inherit;">
                            {{ $product->name }}
                        </a>
                    </h3>
                    <div class="product-card-price">
                        <span class="current">TK. {{ number_format($effectivePrice, 0) }}</span>
                        @if($hasDiscount)
                            <span class="original">TK. {{ number_format($originalPrice, 0) }}</span>
                        @endif
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
