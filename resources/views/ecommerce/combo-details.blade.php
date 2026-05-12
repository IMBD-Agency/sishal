@extends('layouts.ecommerce')

@section('content')
<div class="container py-5">
    <div class="row">
        {{-- Combo Image --}}
        <div class="col-md-6">
            <img src="{{ asset($combo->image ?? 'static/default-product.jpg') }}" class="img-fluid rounded" alt="{{ $combo->name }}">
            
            {{-- Combo Items Gallery --}}
            <div class="row mt-3">
                @foreach($combo->comboItems as $item)
                    <div class="col-3">
                        <img src="{{ asset($item->product->image ?? 'static/default-product.jpg') }}" class="img-thumbnail" alt="{{ $item->product->name }}">
                        <small class="d-block text-center">{{ $item->product->name }}</small>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Combo Details --}}
        <div class="col-md-6">
            <h1>{{ $combo->name }}</h1>
            <p class="text-muted">{{ $combo->short_desc }}</p>

            {{-- Price Section --}}
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <h2 class="text-primary mb-0">৳{{ number_format($combo->price, 2) }}</h2>
                        @if($combo->combo_original_price > $combo->price)
                            <span class="text-decoration-line-through text-muted">৳{{ number_format($combo->combo_original_price, 2) }}</span>
                            <span class="badge bg-success">Save ৳{{ number_format($combo->combo_original_price - $combo->price, 2) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Combo Items List --}}
            <h5>What's in this Combo:</h5>
            <ul class="list-group mb-3">
                @foreach($combo->comboItems as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <img src="{{ asset($item->product->image ?? 'static/default-product.jpg') }}" width="40" class="rounded me-2">
                            {{ $item->product->name }} 
                            @if($item->variation)
                                ({{ $item->variation->name }})
                            @endif
                            × {{ $item->quantity }}
                        </span>
                        <span>৳{{ number_format(($item->combo_price ?? $item->product->price) * $item->quantity, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            {{-- Add to Cart --}}
            <form action="{{ route('cart.add') }}" method="POST" class="d-flex gap-3 align-items-center">
                @csrf
                <input type="hidden" name="product_id" value="{{ $combo->id }}">
                <div class="input-group" style="width: 150px;">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementQty()">-</button>
                    <input type="number" name="quantity" id="qty" value="1" min="1" class="form-control text-center">
                    <button type="button" class="btn btn-outline-secondary" onclick="incrementQty()">+</button>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-cart-plus"></i> Add Combo to Cart
                </button>
            </form>

            {{-- Stock Status --}}
            <p class="mt-3">
                <i class="fas fa-check-circle text-success"></i> 
                @if($combo->isInStock())
                    In Stock
                @else
                    <span class="text-danger">Out of Stock</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Description --}}
    <div class="row mt-5">
        <div class="col-12">
            <h4>Description</h4>
            <div class="border p-3 rounded">
                {!! $combo->description !!}
            </div>
        </div>
    </div>
</div>

<script>
function incrementQty() {
    document.getElementById('qty').value++;
}
function decrementQty() {
    let qty = document.getElementById('qty');
    if(qty.value > 1) qty.value--;
}
</script>
@endsection
