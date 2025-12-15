@extends('ecommerce.master')

@section('main-section')
    <div class="checkout-container">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <!-- Progress Steps -->
                    <div class="checkout-progress mb-5">
                        <div class="progress-container">
                            <div class="progress-step active">
                                <div class="step-circle">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <span class="step-label">Cart</span>
                            </div>
                            <div class="progress-line active"></div>
                            <div class="progress-step active">
                                <div class="step-circle">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <span class="step-label">Checkout</span>
                            </div>
                            <div class="progress-line"></div>
                            <div class="progress-step">
                                <div class="step-circle">
                                    <i class="fas fa-check"></i>
                                </div>
                                <span class="step-label">Complete</span>
                            </div>
                        </div>
                    </div>

                    {{-- Error Messages --}}
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form class="row g-4" id="checkoutForm" action="{{ route('order.make') }}" method="POST">
                        @csrf
                        <!-- Left Column - Forms -->
                        <div class="col-lg-8">
                            <!-- Shipping Information -->
                            <div class="checkout-section">
                                <div class="section-header">
                                    <h4><i class="fas fa-truck me-2"></i>Shipping Information</h4>
                                    <p class="text-muted">Please provide your shipping details</p>
                                </div>
                                <div class="section-body">
                                    <div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">First Name *</label>
                                                <input type="text" class="form-control modern-input" name="first_name" value="{{ optional(auth()->user())->first_name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control modern-input" name="last_name" value="{{ optional(auth()->user())->last_name }}">
                                            </div>
                                            <input type="hidden" name="email" value="{{ optional(auth()->user())->email }}">
                                            <div class="col-12">
                                                <label class="form-label">Phone Number *</label>
                                                <input type="tel" class="form-control modern-input" name="phone" value="{{ optional(optional(auth()->user())->customer)->phone }}" required>
                                            </div>
                                            <h5 class="mt-3"><i class="fas fa-file-invoice me-2"></i>Billing Address</h5>
                                            <div class="col-12">
                                                <label class="form-label">Address *</label>
                                                <input type="text" class="form-control modern-input" name="billing_address_1" value="{{ optional(optional(auth()->user())->customer)->address_1 }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">City *</label>
                                                <div class="city-select-wrapper">
                                                    <input type="text" 
                                                           class="form-control modern-input city-search-input" 
                                                           id="billing_city_search" 
                                                           placeholder="Search and select city..." 
                                                           autocomplete="off"
                                                           value="{{ optional(optional(auth()->user())->customer)->city }}"
                                                           required>
                                                    <input type="hidden" name="billing_city_id" id="billing_city_id" value="">
                                                    <input type="hidden" name="billing_city" id="billing_city" value="{{ optional(optional(auth()->user())->customer)->city }}">
                                                    <div class="city-dropdown" id="billing_city_dropdown" style="display: none;"></div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="billing_state" value="">
                                            <input type="hidden" name="billing_zip_code" value="">
                                            <input type="hidden" name="billing_address_2" value="">
                                            <input type="hidden" name="billing_country" value="">
                                            <input type="hidden" name="shipping_address_2" value="">
                                            <input type="hidden" name="shipping_country" value="">
                                        </div>
                                        <div class="billing-address-toggle mt-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="sameAsBilling" checked>
                                                <label class="form-check-label" for="sameAsBilling">
                                                    Billing address same as shipping address
                                                </label>
                                            </div>
                                        </div>
                                        <div id="billingAddressSection" style="display: none; margin-top: 1.5rem;">
                                            <h5 class="mb-3"><i class="fas fa-file-invoice me-2"></i>Shipping Address</h5>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">First Name *</label>
                                                    <input type="text" class="form-control modern-input" name="shipping_first_name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" class="form-control modern-input" name="shipping_last_name">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Phone Number *</label>
                                                    <input type="tel" class="form-control modern-input" name="shipping_phone">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Address *</label>
                                                    <input type="text" class="form-control modern-input" name="shipping_address_1" value="">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">City *</label>
                                                    <div class="city-select-wrapper">
                                                        <input type="text" 
                                                               class="form-control modern-input city-search-input" 
                                                               id="shipping_city_search" 
                                                               placeholder="Search and select city..." 
                                                               autocomplete="off"
                                                               value="">
                                                        <input type="hidden" name="shipping_city_id" id="shipping_city_id" value="">
                                                        <input type="hidden" name="shipping_city" id="shipping_city" value="">
                                                        <div class="city-dropdown" id="shipping_city_dropdown" style="display: none;"></div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="shipping_state" value="">
                                                <input type="hidden" name="shipping_zip_code" value="">
                                            </div>
                                        </div>
                                        {{-- removed hidden shipping method; using radio inputs below --}}
                                        {{-- <input type="hidden" name="payment_method" id="paymentMethodInput" value="cod"> --}}
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Method -->
                            <div class="checkout-section">
                                <div class="section-header">
                                    <h4><i class="fas fa-shipping-fast me-2"></i>Shipping Method</h4>
                                    <p class="text-muted">Choose your preferred delivery option</p>
                                </div>
                                <div class="section-body">
                                    @if($hasProductFreeDelivery)
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-truck me-2"></i><strong>Free Delivery!</strong> Your order qualifies for free delivery.
                                    </div>
                                    @endif
                                    <div class="shipping-options">
                                        @foreach($shippingMethods as $index => $method)
                                        <div class="shipping-option">
                                            <input type="radio" name="shipping_method" id="shipping_{{ $method->id }}" value="{{ $method->id }}" {{ $index === 0 ? 'checked' : '' }}>
                                            <label for="shipping_{{ $method->id }}" class="shipping-label">
                                                <div class="shipping-info">
                                                    <div class="shipping-name">{{ $method->name }}</div>
                                                    <div class="shipping-desc">{{ $method->delivery_time ?? $method->description }}</div>
                                                </div>
                                                <div class="shipping-price">
                                                    @if($hasProductFreeDelivery)
                                                        <span class="text-success"><i class="fas fa-truck me-1"></i>Free</span>
                                                    @else
                                                        {{ number_format($method->cost, 2) }}৳
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="checkout-section">
                                <div class="section-header">
                                    <h4><i class="fas fa-credit-card me-2"></i>Payment Method</h4>
                                    <p class="text-muted">Secure payment processing</p>
                                </div>
                                <div class="section-body">
                                    <div class="payment-options mb-4">
                                        <div class="payment-option">
                                            <input type="radio" name="payment_method" id="cod" value="cash" checked>
                                            <label for="cod" class="payment-label">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span>Cash on Delivery</span>
                                            </label>
                                        </div>
                                        {{-- Temporarily hidden: Online Payment option --}}
                                        {{--
                                        <div class="payment-option">
                                            <input type="radio" name="payment_method" id="online" value="online-payment">
                                            <label for="online" class="payment-label">
                                                <i class="fas fa-globe"></i>
                                                <span>Online Payment</span>
                                            </label>
                                        </div>
                                        --}}
                                    </div>
                                    {{-- Temporarily hidden: Online payment form --}}
                                    {{--
                                    <div class="credit-card-form" id="onlinePaymentForm" style="display: none;">
                                        <div class="ssl-commerce-payment">
                                            <div class="payment-info">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-shield-alt me-2"></i>
                                                    <strong>Secure Payment:</strong> You will be redirected to SSL Commerce secure payment page to complete your transaction.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    --}}
                                </div>
                            </div>

                    <!-- Order Note -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h4><i class="fas fa-sticky-note me-2"></i>Order Note (Optional)</h4>
                            <p class="text-muted">Any special instructions for your order</p>
                        </div>
                        <div class="section-body">
                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <textarea name="notes" class="form-control modern-input" rows="3" placeholder="e.g., Please call before delivery, leave at reception, etc.">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                        </div>

                        <!-- Right Column - Order Summary -->
                        <div class="col-lg-4">
                            <div class="order-summary">
                                <div class="summary-header">
                                    <h4><i class="fas fa-receipt me-2"></i>Order Summary</h4>
                                </div>
                                <div class="summary-body">
                                    <!-- Order Items -->
                                    <div class="order-items">
                                        @foreach ($carts as $cart)

                                            <div class="order-item">
                                                <img src="{{ asset(@$cart->product->image) }}"
                                                    alt="Product">
                                                <div class="item-details">
                                                    <div class="item-name">{{ @$cart->product->name }}</div>
                                                    <div class="item-quantity">Qty: {{$cart->qty}}</div>
                                                </div>
                                                <div class="item-price">{{ $cart->qty * ($cart->product->discount > 0 ? $cart->product->discount : $cart->product->price) }}৳</div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Promo Code -->
                                    <div class="promo-code-section">
                                        <div class="input-group">
                                            <input type="text" class="form-control modern-input" id="coupon_code"
                                                name="coupon_code" placeholder="Enter promo code" style="text-transform: uppercase;">
                                            <button class="btn btn-outline-primary" type="button" id="applyCouponBtn">Apply</button>
                                        </div>
                                        <div id="couponMessage" class="mt-2" style="display: none;"></div>
                                        <input type="hidden" id="applied_coupon_discount" name="applied_coupon_discount" value="0">
                                    </div>

                                    <!-- Price Breakdown -->
                                    <div class="price-breakdown">
                                        <div class="price-row">
                                            <span>Subtotal</span>
                                            <span>{{$cartTotal}}৳</span>
                                        </div>
                                        <div class="price-row">
                                            <span>Shipping</span>
                                            <span id="shippingDisplay" class="{{ $hasProductFreeDelivery ? 'text-success' : '' }}">
                                                @if($hasProductFreeDelivery)
                                                    <i class="fas fa-truck me-1"></i>Free Delivery
                                                @else
                                                    {{ number_format($initialShippingCost, 2) }}৳
                                                @endif
                                            </span>
                                        </div>
                                        <div class="price-row">
                                            <span>Tax</span>
                                            <span>{{ $cartTotal * $taxRate }}৳</span>
                                        </div>
                                        <div class="price-row discount-row" id="couponDiscountRow" style="display: none;">
                                            <span>Coupon Discount</span>
                                            <span class="text-success" id="couponDiscountAmount">-0.00৳</span>
                                        </div>
                                        <div class="price-row total-row">
                                            <span>Total</span>
                                            <span id="totalDisplay">{{ number_format($cartTotal + ($cartTotal * $taxRate) + $initialShippingCost, 2) }}৳</span>
                                        </div>
                                    </div>

                                    <!-- Place Order Button -->
                                    <button type="submit" class="btn btn-place-order w-100" style="background-color: var(--primary-blue); color: #fff;">
                                        <i class="fas fa-credit-card me-2"></i>
                                        <span id="placeOrderText">Place Order - {{ number_format($cartTotal + ($cartTotal * $taxRate) + $initialShippingCost, 2) }}৳</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay for Order Processing -->
    <div id="orderProcessingOverlay" class="order-processing-overlay" style="display: none;">
        <div class="processing-content">
            <div class="processing-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <h3 class="processing-title">Processing Your Order</h3>
            <p class="processing-message" id="processingMessage">Please wait while we process your order...</p>
            <div class="processing-steps">
                <div class="step-item" id="step1">
                    <i class="fas fa-check-circle step-icon"></i>
                    <span>Creating order</span>
                </div>
                <div class="step-item" id="step2">
                    <i class="fas fa-circle step-icon"></i>
                    <span>Generating invoice</span>
                </div>
                <div class="step-item" id="step3">
                    <i class="fas fa-circle step-icon"></i>
                    <span>Sending confirmation</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --bg-light: #f8fafc;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .checkout-container {
            background: var(--bg-light);
            min-height: 100vh;
            padding-top: 2rem;
        }

        /* Progress Steps */
        .checkout-progress {
            text-align: center;
        }

        .progress-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e2e8f0;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s;
            margin-bottom: 0.5rem;
        }

        .progress-step.active .step-circle {
            background: var(--primary-blue);
            color: white;
        }

        .step-label {
            font-size: 0.875rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .progress-step.active .step-label {
            color: var(--primary-blue);
            font-weight: 600;
        }

        .progress-line {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 1rem;
            margin-bottom: 2rem;
        }

        .progress-line.active {
            background: var(--primary-blue);
        }

        /* Checkout Sections */
        .checkout-section {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            /* Allow dropdowns to escape the section */
            overflow: visible;
        }

        .section-header {
            background: linear-gradient(135deg, var(--light-blue), white);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header h4 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .section-header p {
            margin: 0;
            font-size: 0.9rem;
        }

        .section-body {
            padding: 1.5rem;
        }

        /* Modern Input Styles */
        .modern-input {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .modern-input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(13, 162, 231, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        /* Shipping Options */
        .shipping-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .shipping-option {
            position: relative;
        }

        .shipping-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .shipping-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .shipping-option input[type="radio"]:checked+.shipping-label {
            border-color: var(--primary-blue);
            background: var(--light-blue);
        }

        .shipping-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .shipping-desc {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .shipping-price {
            font-weight: 700;
            color: var(--primary-blue);
            font-size: 1.1rem;
        }

        /* Payment Options */
        .payment-options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .payment-option {
            flex: 1;
            min-width: 150px;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            text-align: center;
        }

        .payment-option input[type="radio"]:checked+.payment-label {
            border-color: var(--primary-blue);
            background: var(--light-blue);
        }

        .payment-label i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }

        .payment-label span {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            position: sticky;
            top: 2rem;
            overflow: hidden;
        }

        .summary-header {
            background: linear-gradient(135deg, var(--light-blue), white);
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-header h4 {
            color: var(--text-dark);
            margin: 0;
            font-weight: 600;
        }

        .summary-body {
            padding: 1.5rem;
        }

        .order-items {
            margin-bottom: 1.5rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 1rem;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .item-quantity {
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .item-price {
            font-weight: 700;
            color: var(--primary-blue);
        }

        .promo-code-section {
            margin-bottom: 1.5rem;
        }

        .price-breakdown {
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-bottom: 1.5rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
        }

        .total-row {
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1rem;
            margin-bottom: 0;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--success-color);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 8px;
        }

        .btn-place-order {
            background: var(--primary-blue);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .btn-place-order:hover {
            background: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Order Processing Overlay */
        .order-processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            z-index: 999999 !important;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .processing-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease-out;
            position: relative;
            z-index: 999999 !important;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .processing-spinner {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
        }

        .spinner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: var(--primary-blue);
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
        }

        .spinner-ring:nth-child(2) {
            width: 70%;
            height: 70%;
            top: 15%;
            left: 15%;
            border-top-color: #00c6ff;
            animation-duration: 1s;
            animation-direction: reverse;
        }

        .spinner-ring:nth-child(3) {
            width: 50%;
            height: 50%;
            top: 25%;
            left: 25%;
            border-top-color: #0072ff;
            animation-duration: 0.8s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .processing-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .processing-message {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
            min-height: 24px;
        }

        .processing-steps {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .step-item.active {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-blue);
        }

        .step-item.completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .step-item.completed .step-icon {
            color: var(--success-color);
        }

        .step-item.active .step-icon {
            color: var(--primary-blue);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .step-icon {
            font-size: 1.2rem;
            color: #ccc;
        }

        /* SSL Commerce Payment Styles */
        .ssl-commerce-payment {
            margin-top: 1rem;
        }

        .payment-methods {
            margin-top: 1rem;
        }

        .payment-method-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-light);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }

        .payment-method-item:hover {
            background: var(--light-blue);
        }

        .payment-method-item i {
            font-size: 1.2rem;
            color: var(--primary-blue);
            margin-right: 0.75rem;
            width: 20px;
        }

        .payment-method-item span {
            font-weight: 500;
            color: var(--text-dark);
        }

        /* City Search Dropdown Styles */
        .city-select-wrapper {
            position: relative;
            /* Ensure new stacking context above section content */
            z-index: 2;
        }

        .city-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid var(--primary-blue);
            border-top: none;
            border-radius: 0 0 12px 12px;
            max-height: 300px;
            overflow-y: auto;
            /* Sit above sticky order summary and other elements */
            z-index: 9999;
            box-shadow: var(--shadow-lg);
            margin-top: -2px;
        }

        .city-dropdown-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid var(--border-color);
        }

        .city-dropdown-item:last-child {
            border-bottom: none;
        }

        .city-dropdown-item:hover {
            background: var(--light-blue);
        }

        .city-dropdown-item.active {
            background: var(--light-blue);
            font-weight: 600;
        }

        .city-dropdown-item .city-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .city-dropdown-item .city-location {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        .city-dropdown-empty {
            padding: 1rem;
            text-align: center;
            color: var(--text-light);
            font-style: italic;
        }

        .city-dropdown-loading {
            padding: 1rem;
            text-align: center;
            color: var(--primary-blue);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .progress-container {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .progress-line {
                display: none;
            }

            .payment-options {
                flex-direction: column;
            }

            .order-summary {
                position: static;
                margin-top: 2rem;
            }
        }
    </style>

    <script>
        // Immediate test - should show in console right away
        console.log('=== JAVASCRIPT TEST START ===');
        console.log('JavaScript is loading...');
        
        try {
            
            // Card number formatting
            document.addEventListener('DOMContentLoaded', function () {
                console.log('First DOMContentLoaded listener executed');
                const cardInput = document.querySelector('input[placeholder="1234 5678 9012 3456"]');
                const expiryInput = document.querySelector('input[placeholder="MM/YY"]');

                if (cardInput) {
                    cardInput.addEventListener('input', function (e) {
                        let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                        if (formattedValue.length > 19) formattedValue = formattedValue.substr(0, 19);
                        e.target.value = formattedValue;
                    });
                }

                if (expiryInput) {
                    expiryInput.addEventListener('input', function (e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length >= 2) {
                            value = value.substring(0, 2) + '/' + value.substring(2, 4);
                        }
                        e.target.value = value;
                    });
                }

                // City Search Functionality
                let citySearchTimeout;
                let selectedCityId = null;
                let isBillingCity = true; // Track which city field is active

                function initCitySearch(inputId, dropdownId, cityIdInputId, cityNameInputId, isBilling = true) {
                    const input = document.getElementById(inputId);
                    const dropdown = document.getElementById(dropdownId);
                    const cityIdInput = document.getElementById(cityIdInputId);
                    const cityNameInput = document.getElementById(cityNameInputId);

                    if (!input || !dropdown) return;

                    // Search cities on input
                    input.addEventListener('input', function() {
                        const query = this.value.trim();
                        
                        clearTimeout(citySearchTimeout);
                        
                        if (query.length < 2) {
                            dropdown.style.display = 'none';
                            cityIdInput.value = '';
                            selectedCityId = null;
                            if (isBilling) {
                                updateShippingMethods(null);
                            }
                            return;
                        }

                        dropdown.innerHTML = '<div class="city-dropdown-loading">Searching...</div>';
                        dropdown.style.display = 'block';

                        citySearchTimeout = setTimeout(() => {
                            fetch(`{{ route('api.cities.search') }}?q=${encodeURIComponent(query)}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.cities && data.cities.length > 0) {
                                        dropdown.innerHTML = data.cities.map(city => `
                                            <div class="city-dropdown-item" data-city-id="${city.id}" data-city-name="${city.display_name}">
                                                <div class="city-name">${city.name}</div>
                                                <div class="city-location">${city.display_name}</div>
                                            </div>
                                        `).join('');

                                        // Add click handlers
                                        dropdown.querySelectorAll('.city-dropdown-item').forEach(item => {
                                            item.addEventListener('click', function() {
                                                const cityId = this.getAttribute('data-city-id');
                                                const cityName = this.getAttribute('data-city-name');
                                                input.value = cityName;
                                                cityIdInput.value = cityId;
                                                cityNameInput.value = cityName.split(',')[0]; // Just city name
                                                dropdown.style.display = 'none';
                                                selectedCityId = cityId;
                                                
                                                // Update shipping methods when city is selected (use billing city, fallback to shipping city)
                                                if (isBilling) {
                                                    updateShippingMethods(cityId);
                                                } else {
                                                    // If shipping city is selected and billing city is not set, use shipping city
                                                    const billingCityId = document.getElementById('billing_city_id').value;
                                                    if (!billingCityId) {
                                                        updateShippingMethods(cityId);
                                                    }
                                                }
                                            });
                                        });
                                    } else {
                                        dropdown.innerHTML = '<div class="city-dropdown-empty">No cities found</div>';
                                    }
                                })
                                .catch(error => {
                                    console.error('City search error:', error);
                                    dropdown.innerHTML = '<div class="city-dropdown-empty">Error searching cities</div>';
                                });
                        }, 300);
                    });

                    // Hide dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });

                    // Focus handling
                    input.addEventListener('focus', function() {
                        if (this.value.trim().length >= 2 && dropdown.innerHTML && !dropdown.innerHTML.includes('Searching')) {
                            dropdown.style.display = 'block';
                        }
                    });
                }

                // Initialize city search for billing and shipping
                initCitySearch('billing_city_search', 'billing_city_dropdown', 'billing_city_id', 'billing_city', true);
                initCitySearch('shipping_city_search', 'shipping_city_dropdown', 'shipping_city_id', 'shipping_city', false);

                // Check if products have free delivery
                const hasProductFreeDelivery = {{ $hasProductFreeDelivery ? 'true' : 'false' }};
                
                // Update shipping methods based on city
                function updateShippingMethods(cityId) {
                    const url = cityId 
                        ? `{{ route('api.shipping.methods.city') }}?city_id=${cityId}`
                        : `{{ route('api.shipping.methods.city') }}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            // Use API response free delivery flag if available, otherwise use page-level flag
                            const apiFreeDelivery = data.has_product_free_delivery !== undefined 
                                ? data.has_product_free_delivery 
                                : hasProductFreeDelivery;
                            const shouldForceFreeDelivery = couponProvidesFreeDelivery || apiFreeDelivery;
                            
                            if (data.shipping_methods && data.shipping_methods.length > 0) {
                                // Update shipping options
                                const shippingOptionsContainer = document.querySelector('.shipping-options');
                                if (shippingOptionsContainer) {
                                    shippingOptionsContainer.innerHTML = data.shipping_methods.map((method, index) => `
                                        <div class="shipping-option">
                                            <input type="radio" name="shipping_method" id="shipping_${method.id}" value="${method.id}" ${index === 0 ? 'checked' : ''}>
                                            <label for="shipping_${method.id}" class="shipping-label">
                                                <div class="shipping-info">
                                                    <div class="shipping-name">${method.name}</div>
                                                    <div class="shipping-desc">${method.delivery_time || method.description || ''}</div>
                                                </div>
                                                <div class="shipping-price">
                                                    ${shouldForceFreeDelivery 
                                                        ? '<span class="text-success"><i class="fas fa-truck me-1"></i>Free</span>' 
                                                        : method.formatted_cost + '৳'}
                                                </div>
                                            </label>
                                        </div>
                                    `).join('');

                                    // Re-attach event listeners
                                    document.querySelectorAll('input[name="shipping_method"]').forEach(option => {
                                        option.addEventListener('change', function() {
                                            updateShippingPrice(this.value, data.summary);
                                        });
                                    });
                                }

                                // Update order summary
                                if (data.summary) {
                                    const preparedSummary = prepareSummaryWithCoupon(data.summary, shouldForceFreeDelivery);
                                    updateOrderSummary(preparedSummary, shouldForceFreeDelivery);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching shipping methods:', error);
                        });
                }

                // Update shipping method price
                function updateShippingPrice(methodId, summary = null) {
                    const freeDelivery = couponProvidesFreeDelivery || hasProductFreeDelivery;
                    if (summary) {
                        const preparedSummary = prepareSummaryWithCoupon(summary, freeDelivery);
                        updateOrderSummary(preparedSummary, freeDelivery);
                        return;
                    }

                    // Fallback: get from current shipping methods data
                    const selectedMethod = document.querySelector(`input[name="shipping_method"]:checked`);
                    if (selectedMethod) {
                        const subtotal = {{ $cartTotal }};
                        const tax = {{ $cartTotal * $taxRate }};
                        let shipping = parseFloat(selectedMethod.closest('.shipping-option').querySelector('.shipping-price').textContent.replace(/[৳,Free]/g, '')) || 0;
                        if (freeDelivery) {
                            shipping = 0;
                        }

                        const preparedSummary = prepareSummaryWithCoupon({
                            subtotal: subtotal,
                            tax: tax,
                            shipping: shipping,
                            formatted_subtotal: subtotal.toFixed(2),
                            formatted_tax: tax.toFixed(2),
                            formatted_shipping: shipping.toFixed(2)
                        }, freeDelivery);

                        updateOrderSummary(preparedSummary, freeDelivery);
                    }
                }


                // Shipping method price update (original - keep for compatibility)
                const shippingOptions = document.querySelectorAll('input[name="shipping_method"]');
                shippingOptions.forEach(option => {
                    option.addEventListener('change', function () {
                        updateShippingPrice(this.value);
                    });
                });

                // Payment method toggle
                const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
                const creditCardForm = document.querySelector('.credit-card-form');

                paymentOptions.forEach(option => {
                    option.addEventListener('change', function () {
                        if (this.value === 'online-payment') {
                            if (creditCardForm) creditCardForm.style.display = 'block';
                        } else {
                            if (creditCardForm) creditCardForm.style.display = 'none';
                        }
                    });
                });

                // Billing address toggle
                const sameAsBilling = document.getElementById('sameAsBilling');
                const billingAddressSection = document.getElementById('billingAddressSection');
                if (sameAsBilling && billingAddressSection) {
                    sameAsBilling.addEventListener('change', function () {
                        billingAddressSection.style.display = this.checked ? 'none' : '';
                        
                        // If checked, sync shipping city with billing city
                        if (this.checked) {
                            const billingCityId = document.getElementById('billing_city_id').value;
                            const billingCityName = document.getElementById('billing_city').value;
                            const billingCitySearch = document.getElementById('billing_city_search').value;
                            
                            if (billingCityId) {
                                document.getElementById('shipping_city_id').value = billingCityId;
                                document.getElementById('shipping_city').value = billingCityName;
                                document.getElementById('shipping_city_search').value = billingCitySearch;
                                // Update shipping methods based on billing city
                                updateShippingMethods(billingCityId);
                            }
                        }
                    });
                }
                // Payment method toggle (second listener)
                document.querySelectorAll('input[name="payment_method"]').forEach(function (option) {
                    option.addEventListener('change', function () {
                        const onlineForm = document.getElementById('onlinePaymentForm');
                        
                        if (onlineForm) {
                            onlineForm.style.display = (this.value === 'online-payment') ? '' : 'none';
                        }
                    });
                });
        });

        // Coupon validation and application
        const couponCodeInput = document.getElementById('coupon_code');
        const couponDiscountInput = document.getElementById('applied_coupon_discount');
        let appliedCouponDiscount = couponDiscountInput ? (parseFloat(couponDiscountInput.value) || 0) : 0;
        let couponProvidesFreeDelivery = false;
        
        // Check if products have free delivery (needed for updateOrderSummary)
        const hasProductFreeDelivery = {{ $hasProductFreeDelivery ? 'true' : 'false' }};

        function showCouponDiscountRow(amount) {
            const discountRow = document.getElementById('couponDiscountRow');
            const discountAmount = document.getElementById('couponDiscountAmount');
            
            if (!discountRow || !discountAmount) {
                return;
            }
            
            if (amount > 0) {
                discountRow.style.display = 'flex';
                discountAmount.textContent = `-${amount.toFixed(2)}৳`;
            } else {
                discountRow.style.display = 'none';
                discountAmount.textContent = '-0.00৳';
            }
        }

        function parseAmount(value) {
            const parsed = parseFloat(value);
            return isNaN(parsed) ? 0 : parsed;
        }

        function prepareSummaryWithCoupon(summary, forceFreeDelivery = false) {
            if (!summary) return summary;

            const normalized = { ...summary };
            const subtotal = parseAmount(normalized.subtotal);
            const tax = parseAmount(normalized.tax);
            let shipping = parseAmount(normalized.shipping);

            if (forceFreeDelivery) {
                shipping = 0;
                normalized.shipping = 0;
                normalized.formatted_shipping = '0.00';
            } else if (!normalized.formatted_shipping) {
                normalized.formatted_shipping = shipping.toFixed(2);
            }

            const totalBeforeDiscount = subtotal + tax + shipping;

            if (appliedCouponDiscount > 0) {
                const discountedTotal = Math.max(totalBeforeDiscount - appliedCouponDiscount, 0);
                normalized.total = discountedTotal;
                normalized.formatted_total = discountedTotal.toFixed(2);
                showCouponDiscountRow(appliedCouponDiscount);
            } else {
                normalized.total = totalBeforeDiscount;
                normalized.formatted_total = totalBeforeDiscount.toFixed(2);
                showCouponDiscountRow(0);
            }

            if (!normalized.formatted_subtotal) {
                normalized.formatted_subtotal = subtotal.toFixed(2);
            }

            if (!normalized.formatted_tax) {
                normalized.formatted_tax = tax.toFixed(2);
            }

            return normalized;
        }
        
        // Update order summary - must be in global scope for coupon functions
        function updateOrderSummary(summary, isFreeDelivery = null) {
            if (!summary) return;
            const freeDelivery = couponProvidesFreeDelivery || (isFreeDelivery !== null ? isFreeDelivery : hasProductFreeDelivery);
            const computedSummary = prepareSummaryWithCoupon(summary, freeDelivery);
            const shippingElement = document.querySelector('#shippingDisplay') || document.querySelector('.price-breakdown .price-row:nth-child(2) span:last-child');
            const totalElement = document.querySelector('#totalDisplay') || document.querySelector('.total-row span:last-child');
            const buttonElement = document.querySelector('#placeOrderText') || document.querySelector('.btn-place-order');
            
            if (shippingElement) {
                if (freeDelivery) {
                    shippingElement.innerHTML = '<i class="fas fa-truck me-1"></i>Free Delivery';
                    shippingElement.classList.add('text-success');
                } else {
                    shippingElement.textContent = `${computedSummary.formatted_shipping}৳`;
                    shippingElement.classList.remove('text-success');
                }
            }
            
            if (totalElement) {
                if (typeof totalElement === 'object' && totalElement.tagName) {
                    totalElement.textContent = `${computedSummary.formatted_total}৳`;
                }
            }
            
            if (buttonElement) {
                if (typeof buttonElement === 'object' && buttonElement.tagName === 'SPAN') {
                    buttonElement.textContent = `Place Order - ${computedSummary.formatted_total}৳`;
                } else if (buttonElement) {
                    buttonElement.innerHTML = `<i class="fas fa-credit-card me-2"></i>Place Order - ${computedSummary.formatted_total}৳`;
                }
            }
        }
        
        document.getElementById('applyCouponBtn').addEventListener('click', function() {
            const couponCode = document.getElementById('coupon_code').value.trim().toUpperCase();
            const messageDiv = document.getElementById('couponMessage');
            const applyBtn = this;
            
            if (!couponCode) {
                messageDiv.className = 'mt-2 alert alert-warning';
                messageDiv.textContent = 'Please enter a coupon code.';
                messageDiv.style.display = 'block';
                return;
            }
            
            // Get current shipping cost
            const selectedShipping = document.querySelector('input[name="shipping_method"]:checked');
            let shippingCost = 0;
            if (selectedShipping) {
                const shippingPriceEl = selectedShipping.closest('.shipping-option')?.querySelector('.shipping-price');
                if (shippingPriceEl) {
                    shippingCost = parseFloat(shippingPriceEl.textContent.replace(/[৳,]/g, '')) || 0;
                }
            }
            
            applyBtn.disabled = true;
            applyBtn.textContent = 'Validating...';
            
            fetch('{{ route("api.coupons.validate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    coupon_code: couponCode,
                    shipping_cost: shippingCost
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    appliedCouponDiscount = parseFloat(data.discount) || 0;
                    if (couponDiscountInput) {
                        couponDiscountInput.value = appliedCouponDiscount;
                    }
                    couponProvidesFreeDelivery = !!data.free_delivery;
                    showCouponDiscountRow(appliedCouponDiscount);
                    
                    // Update total
                    updateTotalWithCoupon(data);
                    
                    // Show success message
                    messageDiv.className = 'mt-2 alert alert-success';
                    messageDiv.textContent = data.message;
                    messageDiv.style.display = 'block';
                    
                    // Disable input and button
                    if (couponCodeInput) {
                        couponCodeInput.value = couponCode;
                        couponCodeInput.readOnly = true;
                        couponCodeInput.setAttribute('data-applied', 'true');
                    }
                    applyBtn.textContent = 'Applied';
                    applyBtn.disabled = true;
                } else {
                    appliedCouponDiscount = 0;
                    couponProvidesFreeDelivery = false;
                    if (couponDiscountInput) {
                        couponDiscountInput.value = 0;
                    }
                    showCouponDiscountRow(0);
                    
                    // Update total without coupon
                    updateTotalWithoutCoupon();
                    
                    if (couponCodeInput) {
                        couponCodeInput.readOnly = false;
                        couponCodeInput.removeAttribute('data-applied');
                    }
                    
                    // Show error message
                    messageDiv.className = 'mt-2 alert alert-danger';
                    messageDiv.textContent = data.message;
                    messageDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.className = 'mt-2 alert alert-danger';
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.style.display = 'block';
            })
            .finally(() => {
            if (appliedCouponDiscount === 0) {
                applyBtn.disabled = false;
                applyBtn.textContent = 'Apply';
            }
            });
        });
        
        // Uppercase coupon code on input
        document.getElementById('coupon_code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        function updateTotalWithCoupon(data) {
            couponProvidesFreeDelivery = !!data.free_delivery;
            const summary = prepareSummaryWithCoupon({
                subtotal: data.subtotal,
                tax: data.tax,
                shipping: data.shipping,
                total: data.total,
                formatted_subtotal: data.formatted_subtotal,
                formatted_tax: data.formatted_tax,
                formatted_shipping: data.formatted_shipping,
                formatted_total: data.formatted_total
            }, couponProvidesFreeDelivery);

            updateOrderSummary(summary, couponProvidesFreeDelivery);
        }
        
        function updateTotalWithoutCoupon() {
            couponProvidesFreeDelivery = false;
            showCouponDiscountRow(0);
            const subtotal = {{ $cartTotal }};
            const tax = {{ $cartTotal * $taxRate }};
            const selectedShipping = document.querySelector('input[name="shipping_method"]:checked');
            let shipping = 0;
            if (selectedShipping) {
                const shippingPriceEl = selectedShipping.closest('.shipping-option')?.querySelector('.shipping-price');
                if (shippingPriceEl) {
                    shipping = parseFloat(shippingPriceEl.textContent.replace(/[৳,]/g, '')) || 0;
                }
            }
            const total = subtotal + tax + shipping;
            
            const shippingElement = document.querySelector('.price-breakdown .price-row:nth-child(2) span:last-child');
            const totalElement = document.querySelector('.total-row span:last-child');
            const buttonElement = document.querySelector('.btn-place-order');
            
            if (shippingElement) {
                shippingElement.textContent = `${shipping.toFixed(2)}৳`;
                shippingElement.classList.remove('text-success');
            }
            if (totalElement) totalElement.textContent = `${total.toFixed(2)}৳`;
            if (buttonElement) buttonElement.innerHTML = `<i class="fas fa-credit-card me-2"></i>Place Order - ${total.toFixed(2)}৳`;
        }

        function updateShippingPrice(method) {
            const shippingMethods = {
                @foreach($shippingMethods as $method)
                '{{ $method->id }}': {{ $method->cost }},
                @endforeach
            };

            const subtotal = {{ $cartTotal }};
            const tax = {{ $cartTotal * $taxRate }};
            let shipping = shippingMethods[method] || 0; // Default to 0 if method not found

            const summary = prepareSummaryWithCoupon({
                subtotal: subtotal,
                tax: tax,
                shipping: couponProvidesFreeDelivery ? 0 : shipping,
                formatted_subtotal: subtotal.toFixed(2),
                formatted_tax: tax.toFixed(2),
                formatted_shipping: (couponProvidesFreeDelivery ? 0 : shipping).toFixed(2)
            }, couponProvidesFreeDelivery);

            const shippingElement = document.querySelector('.price-breakdown .price-row:nth-child(2) span:last-child');
            const totalElement = document.querySelector('.total-row span:last-child');
            const buttonElement = document.querySelector('.btn-place-order');
            
            if (shippingElement) {
                if (couponProvidesFreeDelivery) {
                    shippingElement.innerHTML = '<i class="fas fa-truck me-1"></i>Free Delivery';
                    shippingElement.classList.add('text-success');
                } else {
                    shippingElement.textContent = `${summary.formatted_shipping}৳`;
                    shippingElement.classList.remove('text-success');
                }
            }
            if (totalElement) totalElement.textContent = `${summary.formatted_total}৳`;
            if (buttonElement) buttonElement.innerHTML = `<i class="fas fa-credit-card me-2"></i>Place Order - ${summary.formatted_total}৳`;
        }

        // Handle form submission for SSL Commerce
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Second DOMContentLoaded listener executed - setting up form listener');
            
            const form = document.getElementById('checkoutForm');
            if (!form) {
                console.error('Checkout form not found!');
                return;
            }
            
            console.log('Form found, adding event listener');
            
            // Add click listener to submit button for debugging
            const submitBtn = document.querySelector('.btn-place-order');
            if (submitBtn) {
                console.log('Submit button found, adding click listener');
                submitBtn.addEventListener('click', function(e) {
                    console.log('Submit button clicked!');
                });
            } else {
                console.error('Submit button not found!');
            }
            
            // Check payment method radio buttons
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            console.log('Found payment method radio buttons:', paymentMethods.length);
            paymentMethods.forEach((radio, index) => {
                console.log(`Payment method ${index}:`, radio.value, 'checked:', radio.checked);
                radio.addEventListener('change', function() {
                    console.log('Payment method changed to:', this.value);
                });
            });
            
            form.addEventListener('submit', function(e) {
                console.log('Form submitted!');
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                
                if (!paymentMethod) {
                    console.error('No payment method selected!');
                    return;
                }
                
                console.log('Payment method:', paymentMethod.value);
                
                if (paymentMethod.value === 'online-payment') {
                    console.log('Online payment selected, preventing default and initializing payment');
                    e.preventDefault();
                    initializeSSLCommercePayment();
                } else {
                    // COD (Cash on Delivery) - Show processing overlay
                    console.log('COD payment method selected, showing processing overlay');
                    e.preventDefault();
                    showOrderProcessingOverlay();
                    
                    // Submit form after showing overlay
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                }
            });

            // Function to show order processing overlay
            function showOrderProcessingOverlay() {
                const overlay = document.getElementById('orderProcessingOverlay');
                const messageEl = document.getElementById('processingMessage');
                
                if (!overlay) return;
                
                // Show overlay
                overlay.style.display = 'flex';
                
                // Reset steps
                document.getElementById('step1').classList.remove('completed', 'active');
                document.getElementById('step2').classList.remove('completed', 'active');
                document.getElementById('step3').classList.remove('completed', 'active');
                
                // Step 1: Creating order
                setTimeout(() => {
                    const step1 = document.getElementById('step1');
                    step1.classList.add('active');
                    messageEl.textContent = 'Creating your order...';
                }, 500);
                
                // Step 2: Generating invoice (simulate after 2 seconds)
                setTimeout(() => {
                    const step1 = document.getElementById('step1');
                    const step2 = document.getElementById('step2');
                    step1.classList.remove('active');
                    step1.classList.add('completed');
                    step2.classList.add('active');
                    messageEl.textContent = 'Generating your invoice PDF...';
                }, 2000);
                
                // Step 3: Sending confirmation (simulate after 4 seconds)
                setTimeout(() => {
                    const step2 = document.getElementById('step2');
                    const step3 = document.getElementById('step3');
                    step2.classList.remove('active');
                    step2.classList.add('completed');
                    step3.classList.add('active');
                    messageEl.textContent = 'Sending confirmation email...';
                }, 4000);
                
                // Final message (after 6 seconds)
                setTimeout(() => {
                    const step3 = document.getElementById('step3');
                    step3.classList.remove('active');
                    step3.classList.add('completed');
                    messageEl.textContent = 'Almost done! Finalizing your order...';
                }, 6000);
            } 
        });

        function initializeSSLCommercePayment() {
            console.log('=== PAYMENT INITIALIZATION START ===');
            console.log('initializeSSLCommercePayment called');
            
            let formData;
            let submitBtn;
            let originalText;
            
            try {
                const form = document.getElementById('checkoutForm');
                if (!form) {
                    console.error('Form not found in initializeSSLCommercePayment');
                    return;
                }
                
                formData = new FormData(form);
                console.log('Form data prepared');
                
                // Show loading state
                submitBtn = document.querySelector('.btn-place-order');
                if (!submitBtn) {
                    console.error('Submit button not found');
                    return;
                }
                
                originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.disabled = true;
                console.log('Button state updated to loading');
            } catch (error) {
                console.error('Error in payment initialization setup:', error);
                return;
            }

            // First create the order
            console.log('Starting order creation request to:', '{{ route("order.make") }}');
            
            // Add timeout to fetch request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            fetch('{{ route("order.make") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId); // Clear timeout since we got a response
                console.log('Order creation response status:', response.status);
                console.log('Order creation response headers:', response.headers);
                console.log('Order creation response URL:', response.url);
                
                if (!response.ok) {
                    console.error('Order creation failed with status:', response.status);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                console.log('Order creation response content-type:', contentType);
                
                if (!contentType || !contentType.includes('application/json')) {
                    // Log the response text to see what we're getting
                    console.log('Response is not JSON, getting text...');
                    return response.text().then(text => {
                        console.log('Non-JSON response text:', text);
                        throw new Error('Response is not JSON');
                    });
                }
                console.log('Response is JSON, parsing...');
                return response.json();
            })
            .then(data => {
                console.log('Order creation data:', data);
                console.log('Order creation success:', data.success);
                
                if (data.success) {
                    console.log('Order created successfully, initializing payment...');
                    // Initialize SSL Commerce payment
                    return fetch('{{ route("payment.initialize") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            order_id: data.order_id,
                            amount: data.total_amount
                        })
                    });
                } else {
                    throw new Error(data.message || 'Order creation failed');
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                return response.json();
            })
            .then(paymentData => {
                console.log('Payment initialization data:', paymentData);
                console.log('Payment URL:', paymentData.payment_url);
                console.log('Success:', paymentData.success);
                
                if (paymentData.success) {
                    console.log('Payment successful, redirecting to:', paymentData.payment_url);
                    console.log('Payment data:', JSON.stringify(paymentData, null, 2));
                    
                    if (paymentData.local_development) {
                        // For local development, show success message and redirect
                        alert('Payment completed successfully! (Local Development Mode)');
                        window.location.href = paymentData.payment_url;
                    } else {
                        // Redirect directly to SSL Commerce gateway
                        console.log('Redirecting to SSL Commerce gateway...');
                        console.log('Gateway URL:', paymentData.payment_url);
                        
                        // Add a small delay to see the console messages
                        setTimeout(() => {
                            console.log('About to redirect to SSL Commerce gateway...');
                            window.location.replace(paymentData.payment_url);
                        }, 1000);
                    }
                } else {
                    console.error('Payment failed:', paymentData.message);
                    throw new Error(paymentData.message || 'Payment initialization failed');
                }
            })
            .catch(error => {
                clearTimeout(timeoutId); // Clear timeout in case of error
                console.error('Error:', error);
                console.error('Error details:', error);
                console.error('Error name:', error.name);
                console.error('Error message:', error.message);
                
                // Show more detailed error message
                let errorMessage = 'Payment initialization failed: ' + error.message;
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timed out. Please check your internet connection and try again.';
                } else if (error.message.includes('Response is not JSON')) {
                    errorMessage = 'Server returned an error page instead of payment data. Please check the server logs.';
                } else if (error.message.includes('HTTP error')) {
                    errorMessage = 'Server error occurred. Please try again or contact support.';
                }
                
                console.error('Payment error:', errorMessage);
                alert(errorMessage);
                
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        } catch (error) {
            console.error('JavaScript error in checkout page:', error);
        }
    </script>
    
    @if(isset($carts) && $carts->count() > 0 && ($general_settings->gtm_container_id ?? null))
    <script>
        
        window.dataLayer = window.dataLayer || [];
        
        // Prevent duplicate begin_checkout events on page reload using session storage
        var cartValue = {{ $cartTotal }};
        var cartItemsCount = {{ $carts->count() }};
        var checkoutKey = 'gtm_begin_checkout_' + cartValue + '_' + cartItemsCount;
        
        // Check if this checkout has already been tracked in this session
        if (!sessionStorage.getItem(checkoutKey)) {
            // Mark as tracked to prevent duplicate events on page reload
            sessionStorage.setItem(checkoutKey, 'true');
            
            window.dataLayer.push({
                'event': 'begin_checkout',
                'ecommerce': {
                    'currency': 'BDT',
                    'value': cartValue,
                    'items': [
                        @foreach($carts as $cart)
                        @if($cart->product)
                        {
                            'item_id': '{{ $cart->product_id }}',
                            'item_name': {!! json_encode($cart->product->name ?? '') !!},
                            'item_category': {!! json_encode($cart->product->category->name ?? '') !!},
                            'price': {{ $cart->product->discount ?? $cart->product->price }},
                            'quantity': {{ $cart->qty }}

                        }@if(!$loop->last),@endif
                        @endif
                        @endforeach
                    ]
                    
                }
            });
        }
    </script>
    @endif
@endsection