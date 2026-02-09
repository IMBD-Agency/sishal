@extends('erp.master')

@section('title', 'Reports Dashboard')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1">Reports Dashboard</h2>
                <p class="text-muted mb-0">Centralized hub for all your business analytics and reports</p>
            </div>

            <!-- Procurement & Inventory Section -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                        <i class="fas fa-truck-loading text-primary fs-5"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Procurement & Inventory</h5>
                </div>
                
                <div class="row g-4">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('reports.supplier-summary') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md bg-warning bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-users-cog fs-4 text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Supplier Report</h6>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 lh-sm">Detailed list of suppliers, total purchases, and dues analysis.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sales & Performance Section -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 p-2 rounded-3 me-2">
                        <i class="fas fa-chart-line text-success fs-5"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Sales & Performance</h5>
                </div>
                
                <div class="row g-4">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('simple-accounting.sales-summary') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-chart-line fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Sales Analytics</h6>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 lh-sm">Comprehensive sales analysis, trends, and profit margins.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('simple-accounting.top-products') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md bg-danger bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-fire fs-4 text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Top Selling Products</h6>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 lh-sm">Identify your best-performing products by volume and revenue.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Financial & Accounts Section -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-2">
                        <i class="fas fa-coins text-warning fs-5"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Financial & Accounts</h5>
                </div>
                
                <div class="row g-4">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('reports.profit-loss') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md bg-warning bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-balance-scale fs-4 text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Profit & Loss</h6>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 lh-sm">Comprehensive income statement with revenue vs. costs.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('reports.customer') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md bg-info bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-users fs-4 text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Customer Summary</h6>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 lh-sm">Customer sales history, revenue contribution, and balances.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a href="{{ route('reports.customer.ledger') }}" class="text-decoration-none">
                            <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-book-open fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Customer Ledger</h6>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 lh-sm">Detailed transaction history, opening balances, and collection statements.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .transition-up {
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .transition-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
            border-color: rgba(0,0,0,0.05);
        }
        .avatar-md {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
        }
    </style>
@endsection
