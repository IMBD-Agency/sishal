@extends('ecommerce.master')

@section('main-section')
<div class="payment-result-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="payment-result-card">
                    <div class="result-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="result-title">Payment Successful!</h2>
                    <p class="result-message">
                        Thank you for your payment! Your order has been confirmed and will be processed shortly. 
                        You will receive an email confirmation with your order details.
                    </p>
                    
                    @if(isset($tranId))
                    <div class="transaction-details">
                        <p><strong>Transaction ID:</strong> {{ $tranId }}</p>
                        <p><strong>Payment Method:</strong> SSL Commerce</p>
                        <p><strong>Status:</strong> <span class="text-success">Completed</span></p>
                    </div>
                    @endif

                    <div class="result-actions">
                        @auth
                        <a href="{{ route('order.details', $orderNumber ?? '') }}" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>View Order Details
                        </a>
                        @endauth
                        <a href="{{ route('ecommerce.home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Continue Shopping
                        </a>
                    </div>

                    <div class="help-section">
                        <h5>What's Next?</h5>
                        <ul>
                            <li><i class="fas fa-envelope"></i> You'll receive an email confirmation</li>
                            <li><i class="fas fa-truck"></i> Your order will be processed within 24 hours</li>
                            <li><i class="fas fa-phone"></i> We'll contact you for delivery details</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-result-container {
    background: #f8fafc;
    min-height: 100vh;
}

.payment-result-card {
    background: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.result-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2.5rem;
}

.result-icon.success {
    background: #d1fae5;
    color: #059669;
}

.result-title {
    color: #1f2937;
    margin-bottom: 1rem;
    font-weight: 700;
}

.result-message {
    color: #6b7280;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.transaction-details {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: left;
}

.result-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.help-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 2rem;
    text-align: left;
}

.help-section h5 {
    color: #1f2937;
    margin-bottom: 1rem;
}

.help-section ul {
    list-style: none;
    padding: 0;
}

.help-section li {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    color: #6b7280;
}

.help-section li i {
    margin-right: 0.5rem;
    color: #059669;
}

@media (max-width: 768px) {
    .result-actions {
        flex-direction: column;
    }
    
    .result-actions .btn {
        width: 100%;
    }
}
</style>
@endsection
