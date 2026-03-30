@extends('ecommerce.master')

@section('title', $pageTitle)

@section('main-section')
<!-- Modern Hero Banner -->
<section class="modern-hero-banner">
    <div class="container-fluid">
        <div class="row">
            <!-- Category Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="category-sidebar">
                    <div class="sidebar-header">
                        <h3 class="sidebar-title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                <path d="M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z"/>
                            </svg>
                            Category Menu
                        </h3>
                    </div>
                    <div class="category-list">
                        @foreach($categories as $category)
                        <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="category-item">
                            <div class="category-icon">
                                @if($category->image)
                                    <img src="{{ asset($category->image) }}" width="24" height="24" style="object-fit: contain;">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                        <path d="M4,6H20V8H4V6M4,11H20V13H4V11M4,16H20V18H4V16Z"/>
                                    </svg>
                                @endif
                            </div>
                            <span class="category-text">{{ $category->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor" class="arrow-icon">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Main Hero Content -->
            <div class="col-lg-9 col-md-8">
                <div class="hero-banner">
                    @if(!empty($banners) && count($banners))
                        @php $firstBanner = $banners[0]; @endphp
                        <div class="banner-content">
                            @if($firstBanner->title)
                                <h1 class="banner-title text-uppercase">{{ $firstBanner->title }}</h1>
                            @endif
                            @if($firstBanner->description)
                                <p class="banner-description">{{ $firstBanner->description }}</p>
                            @endif
                            <div class="banner-actions mt-4">
                                @if($firstBanner->link_url)
                                    <a href="{{ $firstBanner->link_url }}" class="btn btn-primary px-4 py-2">{{ $firstBanner->link_text ?? 'SHOP NOW' }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="banner-image">
                            <img src="{{ $firstBanner->image_url }}" alt="{{ $firstBanner->title }}" class="product-image" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    @else
                        <div class="banner-content">
                            <h1 class="banner-title">OUR CATEGORIES</h1>
                            <p>Explore our wide range of products</p>
                        </div>
                        <div class="banner-image">
                            <div class="d-flex align-items-center justify-content-center bg-light h-100" style="min-height: 300px;">
                                <i class="fas fa-images fa-4x text-muted opacity-25"></i>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Grid Section -->
<section class="categories-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="categories-grid">
                    @forelse($categories as $category)
                    <a href="{{ route('product.archive') }}?category={{ $category->slug }}" class="category-card">
                        <div class="category-image">
                            @if($category->image)
                                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}" class="img-fluid">
                            @else
                                <div class="placeholder-image">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="60" height="60" fill="currentColor">
                                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                                    </svg>
                                </div>
                            @endif
                        <div class="category-overlay">
                            <h3 class="category-name">{{ $category->name }}</h3>
                        </div>
                        </div>
                    </a>
                    @empty
                    <div class="col-12 text-center">
                        <div class="no-categories">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="80" height="80" fill="currentColor">
                                <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                            </svg>
                            <h3>No Categories Available</h3>
                            <p>Check back later for new categories!</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
