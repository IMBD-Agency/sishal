@extends('ecommerce.master')

@section('main-section')
<div class="ssl-form-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="ssl-form-card">
                    <div class="form-header">
                        <h2><i class="fas fa-credit-card me-2"></i>{{ $pageTitle ?? 'Redirecting to Payment Gateway' }}</h2>
                        <p class="text-muted">Please wait while we redirect you to the secure payment gateway...</p>
                    </div>

                    @if(isset($error))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
                        </div>
                    @elseif(isset($formData))
                        <div class="form-body">
                            <div class="loading-spinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Redirecting to SSL Commerce...</p>
                            </div>

                            <!-- Hidden form for SSL Commerce -->
                            <form id="sslCommerceForm" method="POST" action="{{ config('ssl_commerce.api_url') }}" style="display: none;">
                                @foreach($formData as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                            </form>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>No payment data available. Please try again.
                        </div>
                    @endif

                    <div class="form-footer">
                        <a href="{{ route('checkout') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ssl-form-container {
    background: #f8fafc;
    min-height: 100vh;
}

.ssl-form-card {
    background: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.form-header h2 {
    color: #1f2937;
    margin-bottom: 1rem;
    font-weight: 700;
}

.form-header p {
    color: #6b7280;
    margin-bottom: 2rem;
}

.loading-spinner {
    padding: 2rem 0;
}

.loading-spinner .spinner-border {
    width: 3rem;
    height: 3rem;
}

.loading-spinner p {
    color: #6b7280;
    margin-top: 1rem;
}

.form-footer {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.alert {
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
}

.alert-danger {
    background-color: #fee2e2;
    border-color: #fecaca;
    color: #dc2626;
}

.alert-warning {
    background-color: #fef3c7;
    border-color: #fde68a;
    color: #d97706;
}

@media (max-width: 768px) {
    .ssl-form-card {
        padding: 2rem 1rem;
    }
}
</style>

@if(isset($formData) && !isset($error))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit the form after a short delay
    setTimeout(function() {
        document.getElementById('sslCommerceForm').submit();
    }, 2000);
});
</script>
@endif
@endsection