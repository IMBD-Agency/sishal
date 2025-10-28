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
        {{-- Simple Accounting Menu --}}
        <div class="nav-item">
            <a href="#simpleAccountingSubmenu" class="nav-link {{ (request()->is('erp/simple-accounting*')) ? ' active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ (request()->is('erp/simple-accounting*')) ? 'true' : 'false' }}" aria-controls="simpleAccountingSubmenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-calculator nav-icon"></i>
                    <span>Accounting</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/simple-accounting*')) ? ' show' : '' }}" id="simpleAccountingSubmenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item"><a href="{{ route('simple-accounting.sales-summary') }}" class="nav-link {{ request()->is('erp/simple-accounting/sales-summary*') ? ' active' : '' }}">Sales Summary</a></li>
                    <li class="nav-item"><a href="{{ route('simple-accounting.profit-report') }}" class="nav-link {{ request()->is('erp/simple-accounting/profit-report*') ? ' active' : '' }}">Profit Report</a></li>
                    <li class="nav-item"><a href="{{ route('simple-accounting.top-products') }}" class="nav-link {{ request()->is('erp/simple-accounting/top-products*') ? ' active' : '' }}">Top Products</a></li>
                    <li class="nav-item"><a href="{{ route('simple-accounting.stock-value') }}" class="nav-link {{ request()->is('erp/simple-accounting/stock-value*') ? ' active' : '' }}">Stock Value</a></li>
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
                        <a href="{{ route('userRole.index') }}" class="nav-link {{ request()->is('erp/user-role*') ? ' active' : '' }}">User Role</a>
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
                        <a href="{{ route('category.list') }}" class="nav-link {{ request()->is('erp/categories*') ? ' active' : '' }}">Category</a>
                    </li>
                    @endcanany
                    @canany(['view subcategory list'])
                    <li class="nav-item">
                        <a href="{{ route('subcategory.list') }}" class="nav-link {{ request()->is('erp/subcategories*') ? ' active' : '' }}">Sub Categories</a>
                    </li>
                    @endcanany
                    @canany(['view products list'])
                    <li class="nav-item">
                        <a href="{{ route('product.list') }}" class="nav-link {{ request()->is('erp/products*') ? ' active' : '' }}">Products</a>
                    </li>
                    @endcanany
                    @canany(['view attribute list'])
                        <li class="nav-item">
                            <a href="{{ route('attribute.list') }}" class="nav-link {{ request()->is('erp/attributes*') ? ' active' : '' }}">Attributes</a>
                        </li>
                    @endcanany
                    @canany(['view review list'])
                    <li class="nav-item">
                        <a href="{{ route('reviews.index') }}" class="nav-link {{ request()->is('erp/reviews*') ? ' active' : '' }}">Reviews</a>
                    </li>
                    @endcanany
                    @canany(['view product stock list'])
                    <li class="nav-item">
                        <a href="{{ route('productstock.list') }}" class="nav-link {{ request()->is('erp/product-stock*') ? ' active' : '' }}">Product Stock</a>
                    </li>
                    @endcanany
                    @canany(['view variation list'])
                    <li class="nav-item">
                        <a href="{{ route('erp.variation-attributes.index') }}" class="nav-link {{ request()->is('erp/variation-attributes*') ? ' active' : '' }}">Variation Attributes</a>
                    </li>
                    @endcanany
                </ul>
            </div>
        </div>
        @endcanany
     {{--
        <div class="nav-item">
            <a href="#posSubmenu" class="nav-link {{ (request()->is('erp/stock-transfer*') || request()->is('erp/purchases*') || request()->is('erp/purchase-return*') || request()->is('erp/pos*') || request()->is('erp/sale-return*')) ? ' active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ (request()->is('erp/stock-transfer*') || request()->is('erp/purchases*') || request()->is('erp/purchase-return*') || request()->is('erp/pos*') || request()->is('erp/sale-return*')) ? 'true' : 'false' }}" aria-controls="posSubmenu">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cash-register nav-icon"></i>
                    <span>POS System</span>
                </div>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse{{ (request()->is('erp/stock-transfer*') || request()->is('erp/purchases*') || request()->is('erp/purchase-return*') || request()->is('erp/pos*') || request()->is('erp/sale-return*')) ? ' show' : '' }}" id="posSubmenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="{{ route('pos.add') }}" class="nav-link {{ request()->is('erp/pos/create') ? ' active' : '' }}">Add POS</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.list') }}" class="nav-link {{ request()->is('erp/pos') ? ' active' : '' }}">POS</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('saleReturn.list') }}" class="nav-link {{ request()->is('erp/sale-return*') ? ' active' : '' }}">Sale Return</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('purchaseReturn.list')}}" class="nav-link {{ request()->is('erp/purchase-return*') ? ' active' : '' }}">Purchase Return</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stocktransfer.list') }}" class="nav-link {{ request()->is('erp/stock-transfer*') ? ' active' : '' }}">Transfer</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('purchase.list') }}" class="nav-link {{ request()->is('erp/purchases*') ? ' active' : '' }}">Purchase</a>
                    </li>
                </ul>
            </div>
        </div>
        --}}
        
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
                            <span>Sales</span>
                            <i class="fas fa-chevron-down ms-auto"></i>
                        </a>
                        <div class="collapse{{ (request()->is('erp/customers*') || request()->is('erp/invoices*') || request()->is('erp/invoice-templates*')) ? ' show' : '' }}" id="salesSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item"><a href="{{ route('customers.list') }}" class="nav-link {{ request()->is('erp/customers*') ? ' active' : '' }}">Customer</a></li>
                                <li class="nav-item"><a href="{{ route('invoice.list') }}" class="nav-link {{ request()->is('erp/invoices*') ? ' active' : '' }}">Invoice</a></li>
                                <li class="nav-item"><a href="{{ route('invoice.template.list') }}" class="nav-link {{ request()->is('erp/invoice-templates*') ? ' active' : '' }}">Invoice Template</a></li>
                            </ul>
                        </div>
                    </li>
                    @endcanany
                    @can('view order list')
                    <li class="nav-item">
                        <a href="{{ route('order.list') }}" class="nav-link {{ request()->is('erp/order-list*') ? ' active' : '' }}">Order</a>
                    </li>
                    @endcan
                    @can('view order return list')
                    <li class="nav-item">
                        <a href="{{ route('orderReturn.list') }}" class="nav-link {{ request()->is('erp/order-return*') ? ' active' : '' }}">Order Return</a>
                    </li>
                    @endcan
                </ul>
            </div>
        </div>
        @endcanany
        @canany(['view list vlog'])
        <div class="nav-item">
            <a href="{{ route('vlogging.index') }}" class="nav-link {{ request()->is('erp/vlogging*') ? ' active' : '' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-video nav-icon"></i>
                    <span>Vlogs</span>
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