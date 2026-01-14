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

            <div class="row g-4">
                <!-- Procurement Reports -->
                <div class="col-12 mt-5">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                        <i class="fas fa-truck-loading me-2 text-primary"></i> Procurement & Inventory
                    </h5>
                </div>
                
                <div class="col-md-4">
                    <a href="{{ route('reports.purchase') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                            <div class="card-body p-4">
                                <div class="avatar-md bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-file-invoice-dollar fs-4 text-primary"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Itemized Purchase Report</h5>
                                <p class="text-muted small mb-0">Detailed list of all items purchased from suppliers with filtering options.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('simple-accounting.stock-value') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                            <div class="card-body p-4">
                                <div class="avatar-md bg-info-subtle rounded-3 d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-boxes fs-4 text-info"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Stock Value Report</h5>
                                <p class="text-muted small mb-0">Inventory valuation and current stock levels across all branches.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('suppliers.index') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                            <div class="card-body p-4">
                                <div class="avatar-md bg-warning-subtle rounded-3 d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-users-cog fs-4 text-warning"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Supplier Ledgers</h5>
                                <p class="text-muted small mb-0">Detailed transaction history and balances for each supplier.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Sales & Performance -->
                <div class="col-12 mt-5">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
                        <i class="fas fa-chart-line me-2 text-success"></i> Sales & Performance
                    </h5>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('reports.sale') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                            <div class="card-body p-4">
                                <div class="avatar-md bg-success-subtle rounded-3 d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-chart-line fs-4 text-success"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Detailed Sale Report</h5>
                                <p class="text-muted small mb-0">High-detail sales analysis with itemized variations, customers, and employee tracking.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('simple-accounting.sales-report') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                            <div class="card-body p-4">
                                <div class="avatar-md bg-indigo-subtle rounded-3 d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-list-ul fs-4 text-indigo text-primary"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Sales Summary</h5>
                                <p class="text-muted small mb-0">Aggregate sales trends daily, weekly, and monthly.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('simple-accounting.top-products') }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 transition-up">
                            <div class="card-body p-4">
                                <div class="avatar-md bg-danger-subtle rounded-3 d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-fire fs-4 text-danger"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Top Selling Products</h5>
                                <p class="text-muted small mb-0">Identify your best-performing products by volume and revenue.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .transition-up {
            transition: transform 0.3s ease, shadow 0.3s ease;
        }
        .transition-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .avatar-md {
            width: 48px;
            height: 48px;
        }
    </style>
@endsection
