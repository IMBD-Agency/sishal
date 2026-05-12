{{-- Combo Product Card --}}
<div class="product-card combo-card position-relative">
    {{-- Combo Badge --}}
    <span class="badge bg-warning position-absolute top-0 start-0 m-2">
        <i class="fas fa-gift"></i> COMBO
    </span>

    {{-- Discount Badge --}}
    @if($product->combo_original_price > $product->price)
        <span class="badge bg-danger position-absolute top-0 end-0 m-2">
            -{{ round((($product->combo_original_price - $product->price) / $product->combo_original_price) * 100) }}%
        </span>
    @endif

    <a href="{{ route('combo.details', $product->slug) }}">
        <img src="{{ asset($product->image ?? 'static/default-product.jpg') }}" class="card-img-top" alt="{{ $product->name }}">
    </a>

    <div class="card-body">
        <h5 class="card-title">{{ $product->name }}</h5>
        <p class="text-muted small">{{ $product->comboItems->count() }} items in combo</p>

        {{-- Price --}}
        <div class="d-flex align-items-center gap-2">
            <span class="h5 text-primary mb-0">৳{{ number_format($product->price, 2) }}</span>
            @if($product->combo_original_price > $product->price)
                <small class="text-decoration-line-through text-muted">৳{{ number_format($product->combo_original_price, 2) }}</small>
            @endif
        </div>

        {{-- Quick Add to Cart --}}
        <form action="{{ route('cart.add') }}" method="POST" class="mt-2">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                <i class="fas fa-cart-plus"></i> Add Combo
            </button>
        </form>
    </div>
</div>
