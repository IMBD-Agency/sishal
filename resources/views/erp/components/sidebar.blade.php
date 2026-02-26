<div class="sidebar" id="sidebar">
    <!-- Brand -->
    @php $logoUrl = $general_settings && $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/default-logo.webp'); @endphp
    <div class="sidebar-brand">
        <a href="{{ route('erp.dashboard') }}" 
           class="brand-logo-link"
           style="background-image: url('{{ $logoUrl }}');"
           aria-label="Home">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- GENERAL -->
        <!-- <span class="sidebar-category-title">General</span> -->
        
        <div class="nav-item">
            <a href="{{ route('erp.dashboard') }}" class="nav-link {{ request()->is('erp/dashboard*') ? ' active' : '' }}">
                <i class="fas fa-home nav-icon"></i>
                <span>Dashboard</span>
            </a>
        </div>

        @can('view branches')
        <div class="nav-item">
            <a href="{{ route('branches.index') }}" class="nav-link {{ request()->is('erp/branches*') ? ' active' : '' }}">
                <i class="fas fa-code-branch nav-icon text-info"></i>
                <span>Branches</span>
            </a>
        </div>
        @endcan

        @can('view warehouses')
        <div class="nav-item">
            <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->is('erp/warehouses*') ? ' active' : '' }}">
                <i class="fas fa-warehouse nav-icon text-warning"></i>
                <span>Warehouses</span>
            </a>
        </div>
        @endcan
       
        <!-- PRODUCTS -->
        <!-- <span class="sidebar-category-title">Products & Stock</span> -->

        @can('view products')
        <div class="nav-item">
            <a href="{{ route('product.list') }}" class="nav-link {{ request()->is('erp/products*') ? ' active' : '' }}">
                <i class="fas fa-box-open nav-icon text-primary"></i>
                <span>Products</span>
            </a>
        </div>
        @endcan

        @can('adjust stock')
        <div class="nav-item">
            <a href="{{ route('stock.adjustment.list') }}" class="nav-link {{ request()->is('erp/stock/adjustment-list*') ? ' active' : '' }}">
                <i class="fas fa-adjust nav-icon text-danger"></i>
                <span>Stock Adjust</span>
            </a>
        </div>
        @endcan

        <!-- PROCUREMENT -->
        <!-- <span class="sidebar-category-title">Purchases</span> -->

        @can('view purchases')
        <div class="nav-item">
            <a href="{{ route('purchase.list') }}" class="nav-link {{ request()->is('erp/purchases*') ? ' active' : '' }}">
                <i class="fas fa-shopping-cart nav-icon text-success"></i>
                <span>Purchase</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('purchaseReturn.list') }}" class="nav-link {{ request()->is('erp/purchase-return*') ? ' active' : '' }}">
                <i class="fas fa-history nav-icon text-danger"></i>
                <span>Purchase Return</span>
            </a>
        </div>
        @endcan

        @can('view suppliers')
        <div class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->is('erp/suppliers*') ? ' active' : '' }}">
                <i class="fas fa-truck nav-icon text-primary"></i>
                <span>Suppliers</span>
            </a>
        </div>
        @endcan

        @can('manage suppliers')
        <div class="nav-item">
            <a href="{{ route('supplier-payments.index') }}" class="nav-link {{ Route::is('supplier-payments.*') ? ' active' : '' }}">
                <i class="fas fa-hand-holding-usd nav-icon text-warning"></i>
                <span>Supplier Pay</span>
            </a>
        </div>
        @endcan
       
        <!-- <span class="sidebar-category-title">Sales & Retail</span> -->

        @can('use pos')
        <div class="nav-item">
            <a href="{{ route('pos.add') }}" class="nav-link {{ request()->is('erp/pos/create') ? ' active' : '' }}">
                <i class="fas fa-cash-register nav-icon text-success"></i>
                <span>POS</span>
            </a>
        </div>
        @endcan

        @can('view sales')
        <div class="nav-item">
            <a href="{{ route('pos.list') }}" class="nav-link {{ request()->is('erp/pos') ? ' active' : '' }}">
                <i class="fas fa-book nav-icon text-info"></i>
                <span>Sales</span>
            </a>
        </div>
        @endcan

        @can('view returns')
        <div class="nav-item">
            <a href="{{ route('saleReturn.list') }}" class="nav-link {{ request()->is('erp/sale-return*') ? ' active' : '' }}">
                <i class="fas fa-undo nav-icon text-danger"></i>
                <span>Sale Return</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('exchange.list') }}" class="nav-link {{ request()->is('erp/exchange*') ? ' active' : '' }}">
                <i class="fas fa-sync nav-icon text-warning"></i>
                <span>Exchange</span>
            </a>
        </div>
        @endcan

        @can('view money receipts')
        <div class="nav-item">
            <a href="{{ route('money-receipt.index') }}" class="nav-link {{ request()->routeIs('money-receipt.*') ? ' active' : '' }}">
                <i class="fas fa-file-invoice-dollar nav-icon text-primary"></i>
                <span>Money Receipt</span>
            </a>
        </div>
        @endcan
             
        @can('view transfers')
        <div class="nav-item">
            <a href="{{ route('stocktransfer.list') }}" class="nav-link {{ request()->is('erp/stock-transfer*') ? ' active' : '' }}">
                <i class="fas fa-exchange-alt nav-icon text-info"></i>
                <span>Stock Transfer</span>
            </a>
        </div>
        @endcan

        @can('view vouchers')
        <div class="nav-item">
            <a href="{{ route('vouchers.index') }}" class="nav-link {{ request()->is('erp/double-entry/vouchers*') ? ' active' : '' }}">
                <i class="fas fa-file-invoice-dollar nav-icon text-primary"></i>
                <span>Vouchers</span>
            </a>
        </div>
        @endcan

        @can('view ledger')
        <div class="nav-item">
            <a href="{{ route('ledger.index') }}" class="nav-link {{ request()->is('erp/double-entry/ledger*') ? ' active' : '' }}">
                <i class="fas fa-book-open nav-icon text-info"></i>
                <span>General Ledger</span>
            </a>
        </div>
        @endcan

        <!-- ACCOUNTING -->
        <!-- <span class="sidebar-category-title">Accounting & Reports</span> -->

        @can('view accounts')
        <div class="nav-item">
            <a href="{{ route('financial-accounts.index') }}" class="nav-link {{ request()->is('erp/financial-accounts*') ? ' active' : '' }}">
                <i class="fas fa-university nav-icon text-primary"></i>
                <span>Financial Accounts</span>
            </a>
        </div>
        @endcan

        @can('view salary')
        <div class="nav-item">
            <a href="{{ route('salary.index') }}" class="nav-link {{ request()->is('erp/salary*') ? ' active' : '' }}">
                <i class="fas fa-money-check-alt nav-icon text-success"></i>
                <span>Salary</span>
            </a>
        </div>
        @endcan

        @can('view reports')
        <div class="nav-item">
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->is('erp/reports') ? ' active' : '' }}">
                <i class="fas fa-chart-pie nav-icon text-danger"></i>
                <span>Reports Center</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('productstock.list') }}" class="nav-link {{ request()->is('erp/product-stock*') ? ' active' : '' }}">
                <i class="fas fa-layer-group nav-icon text-success"></i>
                <span>Stock Report</span>
            </a>
        </div>

@endcan

        @can('view financial reports')


        <div class="nav-item">
            <a href="{{ route('profitLoss.index') }}" class="nav-link {{ request()->is('erp/double-entry/profit-loss*') ? ' active' : '' }}">
                <i class="fas fa-file-contract nav-icon text-success"></i>
                <span>Profit & Loss</span>
            </a>
        </div>
        @endcan

        @can('view executive reports')
        <div class="nav-item">
            <a href="{{ route('reports.executive') }}" class="nav-link {{ request()->is('erp/reports/executive*') ? ' active' : '' }}">
                <i class="fas fa-chart-line nav-icon text-primary"></i>
                <span>Executive Report</span>
            </a>
        </div>
        @endcan

        <!-- ECOMMERCE -->
        <span class="sidebar-category-title">Ecommerce</span>

        @can('view online orders')
        <div class="nav-item">
            <a href="{{ route('order.list') }}" class="nav-link {{ request()->is('erp/order-list*') ? ' active' : '' }}">
                <i class="fas fa-shopping-bag nav-icon text-primary"></i>
                <span>Online Orders</span>
            </a>
        </div>
        @endcan

        @can('manage online orders')
        <div class="nav-item">
            <a href="{{ route('orderReturn.list') }}" class="nav-link {{ request()->is('erp/order-return*') ? ' active' : '' }}">
                <i class="fas fa-reply nav-icon text-danger"></i>
                <span>Order Returns</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('orderExchange.list') }}" class="nav-link {{ request()->is('erp/order-exchange*') ? ' active' : '' }}">
                <i class="fas fa-sync nav-icon text-success"></i>
                <span>Order Exchanges</span>
            </a>
        </div>
        @endcan

        @can('view customers')
        <div class="nav-item">
            <a href="{{ route('customers.list') }}" class="nav-link {{ request()->is('erp/customers*') ? ' active' : '' }}">
                <i class="fas fa-users nav-icon text-info"></i>
                <span>Customers</span>
            </a>
        </div>
        @endcan

        @can('view internal invoices')
        <div class="nav-item">
            <a href="{{ route('invoice.list') }}" class="nav-link {{ request()->is('erp/invoices*') ? ' active' : '' }}">
                <i class="fas fa-file-invoice nav-icon text-secondary"></i>
                <span>Invoice List</span>
            </a>
        </div>
        @endcan

        <!-- PROMOTIONS -->
        <!-- <span class="sidebar-category-title">Marketing</span> -->

        @can('manage coupons')
        <div class="nav-item">
            <a href="{{ route('coupons.index') }}" class="nav-link {{ request()->is('erp/coupons*') ? ' active' : '' }}">
                <i class="fas fa-ticket-alt nav-icon text-warning"></i>
                <span>Coupons</span>
            </a>
        </div>
        @endcan
        
        @can('manage bulk discounts')
        <div class="nav-item">
            <a href="{{ route('bulk-discounts.index') }}" class="nav-link {{ request()->is('erp/bulk-discounts*') ? ' active' : '' }}">
                <i class="fas fa-percent nav-icon text-danger"></i>
                <span>Bulk Discounts</span>
            </a>
        </div>
        @endcan

        @can('manage vlogs')
        <div class="nav-item">
            <a href="{{ route('vlogging.index') }}" class="nav-link {{ request()->is('erp/vlogging*') ? ' active' : '' }}">
                <i class="fas fa-video nav-icon text-primary"></i>
                <span>Visual Stories</span>
            </a>
        </div> 
        @endcan

        @can('manage banners')
        <div class="nav-item">
            <a href="{{ route('banners.index') }}" class="nav-link {{ request()->is('erp/banners*') ? ' active' : '' }}">
                <i class="fas fa-image nav-icon text-success"></i>
                <span>Banners</span>
            </a>
        </div>
        @endcan

        <!-- CONFIG -->
        <span class="sidebar-category-title">Setup</span>
        
        @can('view employees')
        <div class="nav-item">
            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->is('erp/employees*') ? ' active' : '' }}">
                <i class="fas fa-users-cog nav-icon text-primary"></i>
                <span>Employees</span>
            </a>
        </div>
        @endcan

        @can('manage settings')
        <div class="nav-item">
            <a href="{{ route('master.settings') }}" class="nav-link {{ request()->is('erp/master-settings*') ? ' active' : '' }}">
                <i class="fas fa-tools nav-icon text-secondary"></i>
                <span>Master Settings</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->is('erp/settings*') ? ' active' : '' }}">
                <i class="fas fa-cog nav-icon text-secondary"></i>
                <span>App Settings</span>
            </a>
        </div>
        @endcan

        @can('manage roles')
        <div class="nav-item">
            <a href="{{ route('userRole.index') }}" class="nav-link {{ request()->is('erp/user-role*') ? ' active' : '' }}">
                <i class="fas fa-user-shield nav-icon text-danger"></i>
                <span>User Roles</span>
            </a>
        </div>
        @endcan

        @can('manage shipping')
        <div class="nav-item">
            <a href="{{ route('shipping-methods.index') }}" class="nav-link {{ request()->is('erp/shipping-methods*') ? ' active' : '' }}">
                <i class="fas fa-shipping-fast nav-icon text-info"></i>
                <span>Shipping</span>
            </a>
        </div>
        @endcan

        @can('manage pages')
        <div class="nav-item">
            <a href="{{ route('additionalPages.index') }}" class="nav-link {{ request()->is('erp/additional-pages*') ? ' active' : '' }}">
                <i class="fas fa-file-alt nav-icon text-warning"></i>
                <span>Custom Pages</span>
            </a>
        </div>
        @endcan
    </nav>

    <script>
        (function() {
            var nav = document.querySelector('.sidebar-nav');
            if (!nav) return;

            // 1. Restore scroll position from session
            var scrollPos = sessionStorage.getItem('sidebarScroll');
            if (scrollPos) {
                nav.scrollTop = scrollPos;
            }

            // 2. Automatically scroll active item into view (if not visible)
            var activeLink = nav.querySelector('.nav-link.active');
            if (activeLink) {
                activeLink.scrollIntoView({ behavior: 'auto', block: 'nearest' });
            }

            // 3. Save scroll position on scroll
            nav.addEventListener('scroll', function() {
                sessionStorage.setItem('sidebarScroll', nav.scrollTop);
            }, { passive: true });
        })();
    </script>
</div>
