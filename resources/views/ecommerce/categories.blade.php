@extends('ecommerce.master')

@section('title', $pageTitle)

@push('styles')
<style>
    .categories-section {
        background-color: #f8f9fa;
    }
    .category-card-simple {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid #eee;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
    }
    .category-card-simple:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: var(--primary-color, #7fad39);
    }
    .category-icon-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #fdfdfd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        overflow: hidden;
    }
    .category-icon-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .category-card-simple:hover .category-icon-wrapper img {
        transform: scale(1.1);
    }
    .category-name-simple {
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
        margin-top: 10px;
    }
    .category-product-count {
        font-size: 0.85rem;
        color: #888;
        margin-top: 5px;
    }
    .page-header-simple {
        padding: 80px 0;
        background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), 
                    url('https://www.transparenttextures.com/patterns/cubes.png'),
                    linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-bottom: 1px solid #e1e8ed;
        margin-bottom: 40px;
        box-shadow: inset 0 -10px 20px rgba(0,0,0,0.02);
    }
    .page-title-simple {
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 15px;
        font-size: 2.8rem;
        letter-spacing: -1px;
    }
    .breadcrumb-item a {
        color: var(--primary-color, #7fad39);
        font-weight: 500;
        text-decoration: none;
        transition: color 0.2s;
    }
    .breadcrumb-item a:hover {
        color: #5d822b;
        text-decoration: underline;
    }
    .breadcrumb-item.active {
        color: #718096;
        font-weight: 400;
    }
    .breadcrumb-divider {
        color: #cbd5e0;
        margin: 0 10px;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .page-header-simple {
            padding: 40px 15px;
            margin-bottom: 25px;
        }
        .page-title-simple {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }
        .category-icon-wrapper {
            width: 80px;
            height: 80px;
        }
        .category-name-simple {
            font-size: 0.95rem;
        }
    }
</style>
@endpush

@section('main-section')
<div class="page-header-simple">
    <div class="container text-center">
        <h1 class="page-title-simple text-uppercase">Shop By Category</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="{{ route('ecommerce.home') }}">Home</a></li>
                <li class="breadcrumb-divider">/</li>
                <li class="breadcrumb-item active" aria-current="page">All Categories</li>
            </ol>
        </nav>
    </div>
</div>

<section class="categories-section pb-5">
    <div class="container">
        <div class="row g-4">
            @forelse($categories as $category)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="category-card-simple">
                        <div class="category-icon-wrapper shadow-sm">
                            @if($category->image)
                                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}">
                            @else
                                <div class="bg-light w-100 h-100 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-tags fa-2x text-muted opacity-50"></i>
                                </div>
                            @endif
                        </div>
                        <h3 class="category-name-simple">{{ $category->name }}</h3>
                        @if($category->products_count > 0)
                            <span class="category-product-count">{{ $category->products_count }} Products</span>
                        @endif
                    </a>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No categories found</h3>
                </div>
            @endforelse
        </div>
    </div>
</section>

<div style="height: 50px;"></div>
@endsection
