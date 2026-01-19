@extends('ecommerce.master')

@section('main-section')
    <section class="featured-categories pb-3 pt-5">
        <div class="container">
            <h2 class="section-title text-start">Search Result</h2>
            <p class="section-subtitle text-start">Search result for {{ $search }}</p>
        </div>
    </section>

    <div class="container py-4">
        <div class="row">

            <!-- Product Grid -->
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>Showing {{ $products->count() }} products</div>
                </div>
                <div class="row g-4 mt-4">
                    @include('ecommerce.partials.product-grid', ['products' => $products])
                </div>
            </div>
        </div>
    </div>
    <div id="toast-container"
        style="position: fixed; top: 24px; right: 24px; z-index: 16000; display: flex; flex-direction: column; gap: 10px;">
    </div>
@endsection

@push('scripts')
<style>
    .custom-toast {
        min-width: 220px;
        max-width: 340px;
        background: #fff;
        color: #222;
        padding: 0;
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
        font-size: 16px;
        opacity: 1;
        transition: opacity 0.4s, transform 0.4s;
        margin-left: auto;
        margin-right: 0;
        pointer-events: auto;
        z-index: 16000;
        overflow: hidden;
        border-left: 5px solid #2196F3;
        position: relative;
    }

    .custom-toast.error {
        border-left-color: #e53935;
    }

    .custom-toast .toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 18px 14px 16px;
    }

    .custom-toast .toast-icon {
        font-size: 22px;
        flex-shrink: 0;
    }

    .custom-toast .toast-message {
        flex: 1;
        font-weight: 500;
    }

    .custom-toast .toast-close {
        background: none;
        border: none;
        color: #888;
        font-size: 22px;
        cursor: pointer;
        margin-left: 8px;
        transition: color 0.2s;
    }

    .custom-toast .toast-close:hover {
        color: #e53935;
    }

    .custom-toast .toast-progress {
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 100%;
        background: linear-gradient(90deg, #2196F3, #21cbf3);
        transition: width 2.3s linear;
    }

    .custom-toast.error .toast-progress {
        background: linear-gradient(90deg, #e53935, #ffb199);
    }

    .custom-toast.hide {
        opacity: 0;
        transform: translateY(-20px) scale(0.98);
    }

    /* No Products Found Styles */
    .no-products-container {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #F3F0FF 0%, #E3F2FD 100%);
        border-radius: 16px;
        margin: 20px 0;
        border: 1px solid #E3F2FD;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.1);
    }

    .no-products-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #8B5CF6 0%, #00512C 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
    }

    .no-products-icon i {
        font-size: 32px;
        color: white;
    }

    .no-products-title {
        font-size: 28px;
        font-weight: 700;
        color: #00512C;
        margin-bottom: 16px;
        line-height: 1.2;
    }

    .no-products-message {
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 24px;
        line-height: 1.5;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .no-products-suggestion {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: white;
        padding: 12px 20px;
        border-radius: 25px;
        border: 1px solid #E3F2FD;
        box-shadow: 0 2px 10px rgba(0, 81, 44, 0.1);
        font-size: 14px;
        color: #00512C;
        font-weight: 500;
    }

    .no-products-suggestion i {
        color: #FCD34D;
        font-size: 16px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .no-products-container {
            padding: 40px 15px;
            margin: 15px 0;
        }

        .no-products-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
        }

        .no-products-icon i {
            font-size: 24px;
        }

        .no-products-title {
            font-size: 24px;
            margin-bottom: 12px;
        }

        .no-products-message {
            font-size: 14px;
            margin-bottom: 20px;
        }

        .no-products-suggestion {
            padding: 10px 16px;
            font-size: 13px;
        }
    }
</style>
<script>
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'custom-toast ' + type;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${type === 'error' ? '❌' : ''}</span>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.classList.add('hide'); setTimeout(()=>this.parentElement.parentElement.remove(), 400);">&times;</button>
            </div>
            <div class="toast-progress"></div>
        `;
        document.getElementById('toast-container').appendChild(toast);
        // Animate progress bar
        setTimeout(() => {
            toast.querySelector('.toast-progress').style.width = '0%';
        }, 10);
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 400);
        }, 2500);
    }

    function toggleWishlist(id) {
        if (!window.jQuery) return;
        $.ajax({
            url: "{{ url('/add-remove-wishlist') }}/" + id,
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message);
                    // Update icons
                    $('.wishlist-btn[data-product-id="' + id + '"]').toggleClass('active');
                    $('.product-wishlist-top[data-product-id="' + id + '"]').toggleClass('active');
                    
                    // Update icon classes for top button
                    var topBtn = $('.product-wishlist-top[data-product-id="' + id + '"]');
                    var topIcon = topBtn.find('i');
                    if (topBtn.hasClass('active')) {
                            topIcon.removeClass('far').addClass('fas');
                    } else {
                            topIcon.removeClass('fas').addClass('far');
                    }
                    
                    // Update icon classes for cart button
                    var cartBtn = $('.wishlist-btn[data-product-id="' + id + '"]');
                    var cartIcon = cartBtn.find('i');
                    if (cartBtn.hasClass('active')) {
                            cartIcon.removeClass('far').addClass('fas text-danger');
                    } else {
                            cartIcon.removeClass('fas text-danger').addClass('far');
                    }
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                     window.location.href = "{{ route('login') }}";
                } else {
                     showToast('Something went wrong', 'error');
                }
            }
        });
    }
</script>
@endpush