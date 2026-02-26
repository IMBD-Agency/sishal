@extends('erp.master')

@section('title', 'Reports Dashboard')

@section('body')
    @include('erp.components.sidebar')
    <div class="main-content bg-gray-50 min-vh-100" id="mainContent">
        @include('erp.components.header')
        
        <div class="container-fluid px-4 py-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Reports Dashboard</h2>
                    <p class="text-muted mb-0">Centralized hub for all your business analytics and reports</p>
                </div>
                <div class="d-none d-md-block">
                    <div class="input-group search-reports">
                        <span class="input-group-text border-0 bg-white shadow-sm"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 bg-white shadow-sm" id="reportSearch" placeholder="Search reports...">
                    </div>
                </div>
            </div>

            <div class="row g-4 h-100">
                <!-- Sidebar Category Nav (Mobile Only) -->
                <div class="col-12 d-md-none mb-3">
                    <div class="d-flex overflow-auto pb-2 gap-2">
                        <a href="#sales" class="btn btn-white shadow-sm rounded-pill text-nowrap px-3">Sales</a>
                        <a href="#inventory" class="btn btn-white shadow-sm rounded-pill text-nowrap px-3">Inventory</a>
                        <a href="#financial" class="btn btn-white shadow-sm rounded-pill text-nowrap px-3">Financial</a>
                        <a href="#insights" class="btn btn-white shadow-sm rounded-pill text-nowrap px-3">Insights</a>
                    </div>
                </div>

                <!-- Sales & Performance Section -->
                <div class="col-12 report-section" id="sales">
                    <div class="d-flex align-items-center mb-4 section-header">
                        <div class="section-icon bg-soft-primary text-primary me-3">
                            <i class="fas fa-chart-line fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Sales & Customers</h5>
                        <div class="flex-grow-1 ms-3 border-bottom border-light"></div>
                    </div>
                    
                    <div class="row g-4">
                        @php
                            $salesReports = [
                                [
                                    'title' => 'Sales Analytics',
                                    'desc' => 'Comprehensive sales analysis and profit margins.',
                                    'route' => 'simple-accounting.sales-summary',
                                    'icon' => 'fa-chart-pie',
                                    'color' => 'primary'
                                ],
                                [
                                    'title' => 'Daily Sales Report',
                                    'desc' => 'Detailed daily sales transactions and breakdowns.',
                                    'route' => 'reports.sale',
                                    'icon' => 'fa-receipt',
                                    'color' => 'success'
                                ],
                                [
                                    'title' => 'Top Selling Products',
                                    'desc' => 'Identify best-performing products by volume.',
                                    'route' => 'simple-accounting.top-products',
                                    'icon' => 'fa-fire',
                                    'color' => 'danger'
                                ],
                                [
                                    'title' => 'Customer Summary',
                                    'desc' => 'Sales history and revenue contribution.',
                                    'route' => 'reports.customer',
                                    'icon' => 'fa-users',
                                    'color' => 'info'
                                ],
                                [
                                    'title' => 'Customer Ledger',
                                    'desc' => 'Detailed transaction history and statements.',
                                    'route' => 'reports.customer.ledger',
                                    'icon' => 'fa-book-open',
                                    'color' => 'indigo'
                                ],
                            ];
                        @endphp

                        @foreach($salesReports as $report)
                        <div class="col-xl-3 col-lg-4 col-md-6 report-card-wrapper">
                            <a href="{{ route($report['route']) }}" class="text-decoration-none h-100 d-block">
                                <div class="card border-0 shadow-sm rounded-4 h-100 report-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box bg-soft-{{ $report['color'] }} text-{{ $report['color'] }} rounded-3">
                                                <i class="fas {{ $report['icon'] }} fs-4"></i>
                                            </div>
                                            <div class="badge-dot bg-{{ $report['color'] }}"></div>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2 report-title">{{ $report['title'] }}</h6>
                                        <p class="text-muted small mb-0 lh-base">{{ $report['desc'] }}</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0 text-end opacity-0 card-arrow">
                                        <i class="fas fa-arrow-right text-{{ $report['color'] }} small"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Procurement & Inventory Section -->
                <div class="col-12 report-section mt-5" id="inventory">
                    <div class="d-flex align-items-center mb-4 section-header">
                        <div class="section-icon bg-soft-warning text-warning me-3">
                            <i class="fas fa-warehouse fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Procurement & Inventory</h5>
                        <div class="flex-grow-1 ms-3 border-bottom border-light"></div>
                    </div>
                    
                    <div class="row g-4">
                        @php
                            $inventoryReports = [
                                [
                                    'title' => 'Purchase Report',
                                    'desc' => 'In-depth analysis of purchases and materials.',
                                    'route' => 'reports.purchase',
                                    'icon' => 'fa-shopping-cart',
                                    'color' => 'warning'
                                ],
                                [
                                    'title' => 'Supplier Summary',
                                    'desc' => 'Suppliers list, total purchases, and dues.',
                                    'route' => 'reports.supplier-summary',
                                    'icon' => 'fa-truck-loading',
                                    'color' => 'primary'
                                ],
                                [
                                    'title' => 'Supplier Ledger',
                                    'desc' => 'Full statements and payment histories.',
                                    'route' => 'reports.supplier.ledger',
                                    'icon' => 'fa-clipboard-list',
                                    'color' => 'info'
                                ],
                                [
                                    'title' => 'Stock Report',
                                    'desc' => 'Real-time inventory levels across all locations.',
                                    'route' => 'productstock.list',
                                    'icon' => 'fa-boxes',
                                    'color' => 'success'
                                ],
                            ];
                        @endphp

                        @foreach($inventoryReports as $report)
                        <div class="col-xl-3 col-lg-4 col-md-6 report-card-wrapper">
                            <a href="{{ route($report['route']) }}" class="text-decoration-none h-100 d-block">
                                <div class="card border-0 shadow-sm rounded-4 h-100 report-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box bg-soft-{{ $report['color'] }} text-{{ $report['color'] }} rounded-3">
                                                <i class="fas {{ $report['icon'] }} fs-4"></i>
                                            </div>
                                            <div class="badge-dot bg-{{ $report['color'] }}"></div>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2 report-title">{{ $report['title'] }}</h6>
                                        <p class="text-muted small mb-0 lh-base">{{ $report['desc'] }}</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0 text-end opacity-0 card-arrow">
                                        <i class="fas fa-arrow-right text-{{ $report['color'] }} small"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Financial & Accounts Section -->
                <div class="col-12 report-section mt-5" id="financial">
                    <div class="d-flex align-items-center mb-4 section-header">
                        <div class="section-icon bg-soft-danger text-danger me-3">
                            <i class="fas fa-coins fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Financial & Accounts</h5>
                        <div class="flex-grow-1 ms-3 border-bottom border-light"></div>
                    </div>
                    
                    <div class="row g-4">
                        @php
                            $financialReports = [
                                [
                                    'title' => 'Profit & Loss',
                                    'desc' => 'Comprehensive income vs. expenses statement.',
                                    'route' => 'reports.profit-loss',
                                    'icon' => 'fa-balance-scale',
                                    'color' => 'danger'
                                ],
                                [
                                    'title' => 'Cash Book',
                                    'desc' => 'Track all petty cash and daily cash flow.',
                                    'route' => 'reports.cash-book',
                                    'icon' => 'fa-coins',
                                    'color' => 'warning'
                                ],
                                [
                                    'title' => 'Bank Book',
                                    'desc' => 'Overview of bank statements and deposits.',
                                    'route' => 'reports.bank-book',
                                    'icon' => 'fa-university',
                                    'color' => 'info'
                                ],
                                [
                                    'title' => 'Mobile Book',
                                    'desc' => 'Mobile gateway accounts (bKash/Nagad).',
                                    'route' => 'reports.mobile-book',
                                    'icon' => 'fa-mobile-alt',
                                    'color' => 'success'
                                ],
                                [
                                    'title' => 'Expense Report',
                                    'desc' => 'Categorized business expenditures analysis.',
                                    'route' => 'reports.expenses',
                                    'icon' => 'fa-wallet',
                                    'color' => 'secondary'
                                ],
                            ];
                        @endphp

                        @foreach($financialReports as $report)
                        <div class="col-xl-3 col-lg-4 col-md-6 report-card-wrapper">
                            <a href="{{ route($report['route']) }}" class="text-decoration-none h-100 d-block">
                                <div class="card border-0 shadow-sm rounded-4 h-100 report-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box bg-soft-{{ $report['color'] }} text-{{ $report['color'] }} rounded-3">
                                                <i class="fas {{ $report['icon'] }} fs-4"></i>
                                            </div>
                                            <div class="badge-dot bg-{{ $report['color'] }}"></div>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2 report-title">{{ $report['title'] }}</h6>
                                        <p class="text-muted small mb-0 lh-base">{{ $report['desc'] }}</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0 text-end opacity-0 card-arrow">
                                        <i class="fas fa-arrow-right text-{{ $report['color'] }} small"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Insights & Growth Section -->
                <div class="col-12 report-section mt-5 mb-5" id="insights">
                    <div class="d-flex align-items-center mb-4 section-header">
                        <div class="section-icon bg-soft-info text-info me-3">
                            <i class="fas fa-lightbulb fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Insights & Growth</h5>
                        <div class="flex-grow-1 ms-3 border-bottom border-light"></div>
                    </div>
                    
                    <div class="row g-4">
                        @php
                            $insightReports = [
                                [
                                    'title' => 'Executive Report',
                                    'desc' => 'High-level business overview for management.',
                                    'route' => 'reports.executive',
                                    'icon' => 'fa-user-tie',
                                    'color' => 'primary'
                                ],
                            ];
                        @endphp

                        @foreach($insightReports as $report)
                        <div class="col-xl-3 col-lg-4 col-md-6 report-card-wrapper">
                            <a href="{{ route($report['route']) }}" class="text-decoration-none h-100 d-block">
                                <div class="card border-0 shadow-sm rounded-4 h-100 report-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-box bg-soft-{{ $report['color'] }} text-{{ $report['color'] }} rounded-3">
                                                <i class="fas {{ $report['icon'] }} fs-4"></i>
                                            </div>
                                            <div class="badge-dot bg-{{ $report['color'] }}"></div>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2 report-title">{{ $report['title'] }}</h6>
                                        <p class="text-muted small mb-0 lh-base">{{ $report['desc'] }}</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0 text-end opacity-0 card-arrow">
                                        <i class="fas fa-arrow-right text-{{ $report['color'] }} small"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-box {
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        
        .report-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.02) !important;
        }
        
        .report-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
            border-color: rgba(0,0,0,0.05) !important;
            cursor: pointer;
        }
        
        .report-card:hover .icon-box {
            transform: scale(1.1);
        }
        
        .report-card:hover .card-arrow {
            opacity: 1 !important;
            transform: translateX(0);
        }
        
        .card-arrow {
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }
        
        .bg-soft-primary { background-color: rgba(67, 97, 238, 0.1); }
        .text-primary { color: #4361ee !important; }
        .bg-soft-success { background-color: rgba(76, 175, 80, 0.1); }
        .text-success { color: #4caf50 !important; }
        .bg-soft-danger { background-color: rgba(244, 67, 54, 0.1); }
        .text-danger { color: #f44336 !important; }
        .bg-soft-warning { background-color: rgba(255, 152, 0, 0.1); }
        .text-warning { color: #ff9800 !important; }
        .bg-soft-info { background-color: rgba(0, 188, 212, 0.1); }
        .text-info { color: #00bcd4 !important; }
        .bg-soft-indigo { background-color: rgba(102, 16, 242, 0.1); }
        .text-indigo { color: #6610f2 !important; }
        .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
        
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .search-reports {
            width: 300px;
        }
        
        .search-reports .form-control:focus {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .section-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .report-section {
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        document.getElementById('reportSearch').addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            let cards = document.querySelectorAll('.report-card-wrapper');
            let sections = document.querySelectorAll('.report-section');

            cards.forEach(card => {
                let title = card.querySelector('.report-title').textContent.toLowerCase();
                let desc = card.querySelector('.text-muted').textContent.toLowerCase();
                
                if (title.indexOf(value) > -1 || desc.indexOf(value) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });

            // Hide sections if no visible cards
            sections.forEach(section => {
                let visibleCards = section.querySelectorAll('.report-card-wrapper[style=""]');
                if (visibleCards.length === 0 && value !== "") {
                    section.style.display = "none";
                } else {
                    section.style.display = "";
                }
            });
        });
    </script>
@endsection

