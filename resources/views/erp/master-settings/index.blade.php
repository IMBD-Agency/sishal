@extends('erp.master')

@section('title', 'Master Settings Dashboard')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="mb-4">
                <h2 class="h3 fw-bold mb-1 text-dark">Master Settings</h2>
                <p class="text-muted mb-0">Manage all your product metadata and system configurations in one place.</p>
            </div>

            <div class="row g-4">
                <!-- Product Categories -->
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('category.list') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up overflow-hidden">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-th-large fa-2x"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Product Category</h5>
                                <p class="text-muted small mb-3">Organize products into groups</p>
                                <div class="badge bg-primary rounded-pill px-3">{{ $stats['categories'] }} Items</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Brands -->
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('brands.index') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up overflow-hidden">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-certificate fa-2x"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Product Brand</h5>
                                <p class="text-muted small mb-3">Manage product manufacturing brands</p>
                                <div class="badge bg-success rounded-pill px-3">{{ $stats['brands'] }} Items</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Units -->
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('units.index') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up overflow-hidden">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-balance-scale fa-2x"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Product Unit</h5>
                                <p class="text-muted small mb-3">Measurement units (Pcs, Kg, Yard)</p>
                                <div class="badge bg-info rounded-pill px-3">{{ $stats['units'] }} Items</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Seasons -->
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('seasons.index') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up overflow-hidden">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-cloud-sun fa-2x"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Product Season</h5>
                                <p class="text-muted small mb-3">Summer, Winter, Autumn, Spring</p>
                                <div class="badge bg-warning text-dark rounded-pill px-3">{{ $stats['seasons'] }} Items</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Genders -->
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('genders.index') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up overflow-hidden">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-venus-mars fa-2x"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Product Gender</h5>
                                <p class="text-muted small mb-3">Mens, Womens, Kids, Unisex</p>
                                <div class="badge bg-danger rounded-pill px-3">{{ $stats['genders'] }} Items</div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Attributes (Color/Size) -->
                <div class="col-md-4 col-xl-3">
                    <a href="{{ route('erp.variation-attributes.index') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up overflow-hidden">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box bg-secondary bg-opacity-10 text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                                    <i class="fas fa-sliders-h fa-2x"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Product Style/Size</h5>
                                <p class="text-muted small mb-3">Manage Colors, Sizes, and more</p>
                                <div class="badge bg-secondary rounded-pill px-3">{{ $stats['attributes'] }} Items</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .transition-up {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .transition-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
    </style>
@endsection
