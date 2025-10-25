@extends('ecommerce.master')

@section('main-section')
<div class="payment-result-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="payment-result-card">
                    <div class="result-icon cancelled">
                        <i class="fas fa-ban"></i>
                    </div>
                    <h2 class="result-title">Payment Cancelled</h2>
                    <p class="result-message">
                        Your payment has been cancelled. No charges have been made to your account. 
                        You can try again or choose a different payment method.
                    </p>
                    
                    @if(isset($tranId))
                    <div class="transaction-details">
                        <p><strong>Transaction ID:</strong> {{ $tranId }}</p>
                    </div>
                    @endif

                    <div class="result-actions">
                        <a href="{{ route('checkout') }}" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </a>
                        <a href="{{ route('checkout') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-cart me-2"></i>Back to Checkout
                        </a>
                    </div>

                    <div class="help-section">
                        <h5>Need Help?</h5>
                        <p>If you need assistance with your order, please contact us:</p>
                        <ul>
                            <li><i class="fas fa-phone"></i> +880-XXX-XXXXXXX</li>
                            <li><i class="fas fa-envelope"></i> support@yourstore.com</li>
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

.result-icon.cancelled {
    background: #fef3c7;
    color: #d97706;
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
    color: #3b82f6;
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
