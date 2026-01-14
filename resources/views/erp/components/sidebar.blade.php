<div class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="d-flex align-items-center">
            <a href="{{ route('erp.dashboard') }}">
                <img src="{{ $general_settings && $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/default-logo.webp') }}" alt="" class="img-fluid">
            </a>
        </div>
    </div>
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="{{ route('erp.dashboard') }}" class="nav-link {{ request()->is('erp/dashboard*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-home nav-icon"></i>
                    <span>Dashboard</span>
                </div>
            </a>
        </div>
        @canany(['view branch list'])
        <div class="nav-item">
            @can('view branch list')
            <a href="{{ route('branches.index') }}" class="nav-link {{ request()->is('erp/branches*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-code-branch nav-icon"></i>
                    <span>Branches</span>
                </div>
            </a>
            @endcan
        </div>
        @endcanany
        <div class="nav-item">
            <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->is('erp/warehouses*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-warehouse nav-icon"></i>
                    <span>Warehouses</span>
                </div>
            </a>
        </div>
        @canany(['view employee list'])
        <div class="nav-item">
            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->is('erp/employees*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users nav-icon"></i>
                    <span>Employee Setup</span>
                </div>
            </a>
        </div>
        @endcanany

        {{-- Master Settings Dashboard --}}
        <div class="nav-item">
            <a href="{{ route('master.settings') }}" class="nav-link {{ request()->is('erp/master-settings*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tools nav-icon"></i>
                    <span>Master Settings</span>
                </div>
            </a>
        </div>
        {{-- Reports & Analytics --}}
        <div class="nav-item">
            <a href="#reportsSubmenu" class="nav-link {{ (request()->is('erp/reports*') || request()->is('erp/simple-accounting*')) ? ' active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ (request()->is('erp/reports*') || request()->is('erp/simple-accounting*')) ? 'true' : 'false' }}" aria-controls="reportsSubmenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-chart-pie nav-icon"></i>
                    <span>Analytics & Reports</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/reports*') || request()->is('erp/simple-accounting*')) ? ' show' : '' }}" id="reportsSubmenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->is('erp/reports') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-th-large nav-icon-sub"></i>
                                <span>Reports Dashboard</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.sale') }}" class="nav-link {{ request()->is('erp/reports/sales*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-invoice-dollar nav-icon-sub"></i>
                                <span>Detailed Sales</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.purchase') }}" class="nav-link {{ request()->is('erp/reports/purchases*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-truck-loading nav-icon-sub"></i>
                                <span>Detailed Purchase</span>
                            </div>
                        </a>
                    </li>
                    @can('view sales summary')
                    <li class="nav-item">
                        <a href="{{ route('simple-accounting.sales-summary') }}" class="nav-link {{ request()->is('erp/simple-accounting/sales-summary*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-bar nav-icon-sub"></i>
                                <span>Sales Trends</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('view sales report')
                    <li class="nav-item">
                        <a href="{{ route('simple-accounting.sales-report') }}" class="nav-link {{ request()->is('erp/simple-accounting/sales-report*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-list-ul nav-icon-sub"></i>
                                <span>Itemized Sales</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('view stock value')
                    <li class="nav-item">
                        <a href="{{ route('simple-accounting.stock-value') }}" class="nav-link {{ request()->is('erp/simple-accounting/stock-value*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-boxes nav-icon-sub"></i>
                                <span>Stock Levels</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>
        @canany(['view list user role'])
        <div class="nav-item">
            <a href="#" class="nav-link {{ (request()->is('erp/user-role*')) ? ' active' : '' }}" data-bs-toggle="collapse" data-bs-target="#userManagementSubMenu" aria-expanded="{{ (request()->is('erp/user-role*')) ? 'true' : 'false' }}" aria-controls="userManagementSubMenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-check nav-icon"></i>
                    <span>User Management</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/user-role*')) ? ' show' : '' }}" id="userManagementSubMenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="{{ route('userRole.index') }}" class="nav-link {{ request()->is('erp/user-role*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-shield me-2 small"></i>
                                <span>User Roles</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
      @endcanany
        @canany(['view products list', 'view product category list', 'view subcategory list', 'view product stock list', 'view variation list', 'view attribute list', 'view review list'])
        <div class="nav-item">
            <a href="#" class="nav-link {{ (request()->is('erp/categories*') || request()->is('erp/products*') || request()->is('erp/product-stock*') || request()->is('erp/attributes*') || request()->is('erp/subcategories*') || request()->is('erp/reviews*') || request()->is('erp/variation-attributes*')) ? ' active' : '' }}" data-bs-toggle="collapse" data-bs-target="#productsSubMenu" aria-expanded="{{ (request()->is('erp/categories*') || request()->is('erp/products*') || request()->is('erp/product-stock*') || request()->is('erp/attributes*') || request()->is('erp/subcategories*') || request()->is('erp/reviews*') || request()->is('erp/variation-attributes*')) ? 'true' : 'false' }}" aria-controls="productsSubMenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-box nav-icon"></i>
                    <span>Products System</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/categories*') || request()->is('erp/products*') || request()->is('erp/product-stock*') || request()->is('erp/attributes*') || request()->is('erp/subcategories*') || request()->is('erp/reviews*') || request()->is('erp/variation-attributes*')) ? ' show' : '' }}" id="productsSubMenu">
                <ul class="nav flex-column ms-4">
                    @canany(['view category list'])
                    <li class="nav-item">
                        <a href="{{ route('category.list') }}" class="nav-link {{ request()->is('erp/categories*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tags nav-icon-sub"></i>
                                <span>Category</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                    @canany(['view subcategory list'])
                    <li class="nav-item">
                        <a href="{{ route('subcategory.list') }}" class="nav-link {{ request()->is('erp/subcategories*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tag nav-icon-sub"></i>
                                <span>Sub Categories</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                    @canany(['view products list'])
                    <li class="nav-item">
                        <a href="{{ route('product.list') }}" class="nav-link {{ request()->is('erp/products*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-box-open nav-icon-sub"></i>
                                <span>Products</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                    @canany(['view attribute list'])
                    <li class="nav-item">
                        <a href="{{ route('attribute.list') }}" class="nav-link {{ request()->is('erp/attributes*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-sliders-h nav-icon-sub"></i>
                                <span>Attributes</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                    @canany(['view review list'])
                    <li class="nav-item">
                        <a href="{{ route('reviews.index') }}" class="nav-link {{ request()->is('erp/reviews*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-star nav-icon-sub"></i>
                                <span>Reviews</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                    @canany(['view product stock list'])
                    <li class="nav-item">
                        <a href="{{ route('productstock.list') }}" class="nav-link {{ request()->is('erp/product-stock*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-layer-group nav-icon-sub"></i>
                                <span>Product Stock</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                    @canany(['view variation list'])
                    <li class="nav-item">
                        <a href="{{ route('erp.variation-attributes.index') }}" class="nav-link {{ request()->is('erp/variation-attributes*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-cogs nav-icon-sub"></i>
                                <span>Variation Attributes</span>
                            </div>
                        </a>
                    </li>
                    @endcanany
                </ul>
            </div>
        </div>
        @endcanany
        @canany(['pos', 'pos list', 'pos return', 'branch stock transfer', 'pos assign list'])
        <div class="nav-item">
            <a href="#posSubmenu" class="nav-link {{ (request()->is('erp/stock-transfer*') || request()->is('erp/purchases*') || request()->is('erp/purchase-return*') || request()->is('erp/pos*') || request()->is('erp/sale-return*') || request()->is('erp/suppliers*') || request()->is('erp/supplier-payments*')) ? ' active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ (request()->is('erp/stock-transfer*') || request()->is('erp/purchases*') || request()->is('erp/purchase-return*') || request()->is('erp/pos*') || request()->is('erp/sale-return*') || request()->is('erp/suppliers*') || request()->is('erp/supplier-payments*')) ? 'true' : 'false' }}" aria-controls="posSubmenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cash-register nav-icon"></i>
                    <span>Purchase & Sales</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/stock-transfer*') || request()->is('erp/purchases*') || request()->is('erp/purchase-return*') || request()->is('erp/pos*') || request()->is('erp/sale-return*') || request()->is('erp/suppliers*') || request()->is('erp/supplier-payments*')) ? ' show' : '' }}" id="posSubmenu">
                <ul class="nav flex-column ms-4">
                    @can('pos')
                    <li class="nav-item">
                        <a href="{{ route('pos.add') }}" class="nav-link {{ request()->is('erp/pos/create') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-plus-circle nav-icon-sub"></i>
                                <span>POS (Direct Sale)</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.manual.create') }}" class="nav-link {{ request()->is('erp/pos/store/manual') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-edit nav-icon-sub"></i>
                                <span>Manual Sale Entry</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('pos list')
                    <li class="nav-item">
                        <a href="{{ route('pos.list') }}" class="nav-link {{ request()->is('erp/pos') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-history nav-icon-sub"></i>
                                <span>Sales History</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('pos return')
                    <li class="nav-item">
                        <a href="{{ route('saleReturn.list') }}" class="nav-link {{ request()->is('erp/sale-return*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-undo nav-icon-sub"></i>
                                <span>Sale Return</span>
                            </div>
                        </a>
                    </li>
                    @endcan  
                    @can('pos assign list')
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->is('erp/suppliers*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-tie nav-icon-sub"></i>
                                <span>Suppliers</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('supplier-payments.index') }}" class="nav-link {{ Route::is('supplier-payments.*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-money-check-alt nav-icon-sub"></i>
                                <span>Supplier Payments</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('purchase.list') }}" class="nav-link {{ request()->is('erp/purchases*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-list nav-icon-sub"></i>
                                <span>Purchase List</span>
                            </div>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="{{ route('purchase.create') }}" class="nav-link {{ request()->is('erp/purchases/create') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart nav-icon-sub"></i>
                                <span>New Purchase</span>
                            </div>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a href="{{ route('purchaseReturn.list') }}" class="nav-link {{ request()->is('erp/purchase-return*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-undo-alt nav-icon-sub"></i>
                                <span>Purchase Return</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('branch stock transper')
                    <li class="nav-item">
                        <a href="{{ route('stocktransfer.list') }}" class="nav-link {{ request()->is('erp/stock-transfer*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exchange-alt nav-icon-sub"></i>
                                <span>Stock Transfer</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>
        @endcanany
        
        {{-- Service functionality disabled - commented out
        <div class="nav-item">
            <a href="#" class="nav-link {{ request()->is('erp/customer-services*') ? ' active' : '' }}" data-bs-toggle="collapse" data-bs-target="#serviceSubMenu" aria-expanded="{{ request()->is('erp/customer-services*') ? 'true' : 'false' }}" aria-controls="productsSubMenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-headset nav-icon"></i>
                    <span>Customer Services</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ request()->is('erp/customer-services*') ? ' show' : '' }}" id="serviceSubMenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="{{ route('customerService.list') }}" class="nav-link {{ request()->is('erp/customer-services*') ? ' active' : '' }}">Service</a>
                    </li>
                </ul>
            </div>
        </div>
        --}}
        @canany(['view order list', 'view order return list', 'view customer list', 'view invoice list', 'view invoice template list'])
        <div class="nav-item">
            <a href="#" class="nav-link {{ (request()->is('erp/order-list*') || request()->is('erp/order-return*') || request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? ' active' : '' }}" data-bs-toggle="collapse" data-bs-target="#ecommerceSubMenu" aria-expanded="{{ (request()->is('erp/order-list*') || request()->is('erp/order-return*') || request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? 'true' : 'false' }}" aria-controls="ecommerceSubMenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    <span>Ecommerce</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/order-list*') || request()->is('erp/order-return*') || request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? ' show' : '' }}" id="ecommerceSubMenu">
                <ul class="nav flex-column ms-4">
                    @canany(['view customer list', 'view invoice list', 'view invoice template list'])
                    <li class="nav-item">
                        <a href="#salesSubmenu" class="nav-link {{ (request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? ' active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ (request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? 'true' : 'false' }}" aria-controls="salesSubmenu">
                            <i class="fas fa-hand-holding-usd me-2 small"></i>
                            <span>Sales Ops</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse{{ (request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? ' show' : '' }}" id="salesSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a href="{{ route('customers.list') }}" class="nav-link {{ request()->is('erp/customers*') ? ' active' : '' }}">
                                        <i class="fas fa-users me-2 extra-small"></i> Customer
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('invoice.list') }}" class="nav-link {{ request()->is('erp/invoices*') ? ' active' : '' }}">
                                        <i class="fas fa-file-invoice me-2 extra-small"></i> Invoices
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('invoice.template.list') }}" class="nav-link {{ request()->is('erp/invoice-templates*') ? ' active' : '' }}">
                                        <i class="fas fa-file-code me-2 extra-small"></i> Templates
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endcanany
                    @can('view order list')
                    <li class="nav-item">
                        <a href="{{ route('order.list') }}" class="nav-link {{ request()->is('erp/order-list*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-basket me-2 small"></i>
                                <span>Order List</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('view order return list')
                    <li class="nav-item">
                        <a href="{{ route('orderReturn.list') }}" class="nav-link {{ request()->is('erp/order-return*') ? ' active' : '' }}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-reply me-2 small"></i>
                                <span>Order Returns</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>
        @endcanany
        
        {{-- Coupons - No permission check, accessible to all admins --}}
        <div class="nav-item">
            <a href="{{ route('coupons.index') }}" class="nav-link {{ request()->is('erp/coupons*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-ticket-alt nav-icon"></i>
                    <span>Coupons</span>
                </div>
            </a>
        </div>
        
        {{-- Bulk Discounts - No permission check, accessible to all admins --}}
        <div class="nav-item">
            <a href="{{ route('bulk-discounts.index') }}" class="nav-link {{ request()->is('erp/bulk-discounts*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-percent nav-icon"></i>
                    <span>Bulk Discounts</span>
                </div>
            </a>
        </div>
        @canany(['view list vlog'])
        <div class="nav-item">
            <a href="{{ route('vlogging.index') }}" class="nav-link {{ request()->is('erp/vlogging*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-video nav-icon"></i>
                    <span>Visual Stories</span>
                </div>
            </a>
        </div> 
        @endcanany
        @canany(['view additional page list'])
        <div class="nav-item">
            <a href="{{ route('additionalPages.index') }}" class="nav-link {{ request()->is('erp/additional-pages*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-alt nav-icon"></i>
                    <span>Additional Pages</span>
                </div>
            </a>
        </div>
        @endcanany
        @canany(['view banner list'])
        <div class="nav-item">
            <a href="{{ route('banners.index') }}" class="nav-link {{ request()->is('erp/banners*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-image nav-icon"></i>
                    <span>Banner Management</span>
                </div>
            </a>
        </div>
        @endcanany
        @canany(['setting manage'])
        <div class="nav-item">
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->is('erp/settings*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cog nav-icon"></i>
                    <span>Settings</span>
                </div>
            </a>
        </div>
        @endcanany
        @canany(['view shipping list'])
        <div class="nav-item">
            <a href="{{ route('shipping-methods.index') }}" class="nav-link {{ request()->is('erp/shipping-methods*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shipping-fast nav-icon"></i>
                    <span>Shipping Methods</span>
                </div>
            </a>
        </div>
        @endcanany
    </nav>
</div> 





