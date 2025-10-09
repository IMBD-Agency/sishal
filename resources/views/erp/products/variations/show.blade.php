@extends('erp.master')

@section('title', 'Variation Details')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-light min-vh-100" id="mainContent">
        @include('erp.components.header')
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-eye me-2"></i>
                                Variation Details - {{ $product->name }}
                            </h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('erp.products.variations.index', $product->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Variations
                                </a>
                                <a href="{{ route('erp.products.variations.edit', [$product->id, $variation->id]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('erp.products.variations.stock', [$product->id, $variation->id]) }}" class="btn btn-warning">
                                    <i class="fas fa-boxes me-1"></i> Manage Stock
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header"><h5 class="mb-0">Basic Info</h5></div>
                                        <div class="card-body">
                                            <p class="mb-2"><strong>SKU:</strong> {{ $variation->sku }}</p>
                                            <p class="mb-2"><strong>Name:</strong> {{ $variation->name }}</p>
                                            <p class="mb-2"><strong>Status:</strong> 
                                                <span class="badge bg-{{ $variation->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($variation->status) }}</span>
                                            </p>
                                            <p class="mb-2"><strong>Default:</strong> {{ $variation->is_default ? 'Yes' : 'No' }}</p>
                                            <p class="mb-2"><strong>Price:</strong> 
                                                {{ $variation->price ? number_format($variation->price, 2) : number_format($product->price, 2) }}
                                            </p>
                                            @if(!is_null($variation->discount) || !is_null($product->discount))
                                                <p class="mb-2"><strong>Discount:</strong> {{ number_format($variation->discount ?? $product->discount ?? 0, 2) }}</p>
                                                <p class="mb-0"><strong>Final:</strong> {{ number_format($variation->final_price, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header"><h5 class="mb-0">Attributes</h5></div>
                                        <div class="card-body">
                                            @forelse($variation->combinations as $combo)
                                                <p class="mb-2">
                                                    <strong>{{ $combo->attribute->name }}:</strong>
                                                    {{ $combo->attributeValue->value }}
                                                    @if($combo->attribute->is_color && $combo->attributeValue->color_code)
                                                        <span title="{{ $combo->attributeValue->color_code }}"
                                                              style="display:inline-block;width:14px;height:14px;border-radius:50%;vertical-align:middle;margin-left:6px;background-color: {{ $combo->attributeValue->color_code }}"></span>
                                                    @endif
                                                </p>
                                            @empty
                                                <p class="text-muted mb-0">No attributes linked.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header"><h5 class="mb-0">Stock</h5></div>
                                        <div class="card-body">
                                            <p class="mb-2"><strong>Total:</strong> {{ $variation->total_stock }}</p>
                                            <p class="mb-0"><strong>Available:</strong> {{ $variation->available_stock }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0">Gallery</h5>
                                        </div>
                                        <div class="card-body">
                                            @if($variation->galleries && $variation->galleries->count())
                                                <div class="d-flex flex-wrap gap-3">
                                                    @foreach($variation->galleries as $img)
                                                        <div class="border rounded p-1 bg-white">
                                                            <img src="/{{ $img->image }}" alt="Variation image" style="height:100px;width:auto;object-fit:cover">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted mb-0">No gallery images.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


