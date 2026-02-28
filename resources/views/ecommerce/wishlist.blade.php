@extends('ecommerce.master')

@php
    use Illuminate\Support\Str;
@endphp

@section('main-section')
    <div class="wishlist-page">
        <div class="container py-4">

            {{-- Page Header --}}
            <div class="wl-page-header">
                <div class="wl-header-left">
                    <h1 class="wl-title">My Wishlist</h1>
                    <p class="wl-subtitle">Your saved favourite items</p>
                </div>
                <div class="wl-header-right">
                    <span class="wl-badge">
                        <i class="fas fa-heart"></i>
                        {{ $wishlists->count() }} {{ $wishlists->count() == 1 ? 'Item' : 'Items' }}
                    </span>
                    @if($wishlists->count() > 0)
                        <form action="{{ route('wishlist.removeAll') }}" method="post" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="wl-clear-btn">
                                <i class="fas fa-trash-alt"></i> Clear All
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Wishlist Grid --}}
            <div class="row g-3">
                @forelse ($wishlists as $wishlist)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="wl-card" data-href="{{ route('product.details', $wishlist->product->slug) }}">
                            {{-- Remove button --}}
                            <button class="wl-remove-btn product-wishlist-top active"
                                data-product-id="{{ $wishlist->product->id }}"
                                onclick="event.stopPropagation();"
                                title="Remove from Wishlist">
                                <i class="fas fa-heart"></i>
                            </button>
                            {{-- Hidden compat button --}}
                            <button class="wishlist-btn active" data-product-id="{{ $wishlist->product->id }}"
                                onclick="event.stopPropagation();" style="display:none;">
                                <i class="fas fa-heart"></i>
                            </button>

                            {{-- Image --}}
                            <div class="wl-img-wrap">
                                <img src="{{ $wishlist->product->image ? asset($wishlist->product->image) : asset('static/default-product.jpg') }}"
                                    class="wl-img" alt="{{ $wishlist->product->name }}">
                            </div>

                            {{-- Info --}}
                            <div class="wl-info">
                                <a href="{{ route('product.details', $wishlist->product->slug) }}" class="wl-name">
                                    {{ $wishlist->product->name }}
                                </a>

                                {{-- Stars --}}
                                @php
                                    $avgRating = $wishlist->product->averageRating();
                                @endphp
                                <div class="wl-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fa{{ $i <= $avgRating ? 's' : 'r' }} fa-star"></i>
                                    @endfor
                                </div>

                                {{-- Price --}}
                                @php
                                    $effectivePrice = $wishlist->product->effective_price;
                                    $originalPrice  = $wishlist->product->original_price;
                                    $hasDiscount    = $wishlist->product->hasDiscount();
                                @endphp
                                <div class="wl-price">
                                    @if($hasDiscount && $effectivePrice < $originalPrice)
                                        <span class="wl-price-current">{{ number_format($effectivePrice, 0) }}৳</span>
                                        <span class="wl-price-old">{{ number_format($originalPrice, 0) }}৳</span>
                                    @else
                                        <span class="wl-price-current">{{ number_format($originalPrice, 0) }}৳</span>
                                    @endif
                                </div>

                                {{-- View Button --}}
                                <a href="{{ route('product.details', $wishlist->product->slug) }}" class="wl-view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="wl-empty">
                            <i class="fas fa-heart wl-empty-icon"></i>
                            <h4>Your Wishlist is Empty</h4>
                            <p>Browse products and save your favourites here.</p>
                            <a href="{{ route('product.archive') }}" class="wl-shop-btn">
                                <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Footer nav --}}
            <div class="mt-4">
                <a href="{{ route('product.archive') }}" class="wl-back-btn">
                    <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>

    <div id="toast-container"
        style="position: fixed; top: 24px; right: 24px; z-index: 16000; display: flex; flex-direction: column; gap: 10px;">
    </div>

    <style>
        /* =====================
           WISHLIST PAGE — scoped
           ===================== */
        .wishlist-page {
            background: #f5f7fa;
            min-height: 60vh;
        }

        /* Header row */
        .wl-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .wl-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 2px;
        }
        .wl-subtitle {
            font-size: 13px;
            color: #888;
            margin: 0;
        }
        .wl-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .wl-badge {
            font-size: 12px;
            padding: 5px 12px;
            background: #e8f0fe;
            color: #1a73e8;
            border-radius: 999px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .wl-clear-btn {
            background: rgba(220,38,38,0.08);
            color: #dc2626;
            border: 1px solid rgba(220,38,38,0.2);
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .wl-clear-btn:hover { background: rgba(220,38,38,0.15); }

        /* Card */
        .wl-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            position: relative;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }
        .wl-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        /* Remove heart button */
        .wl-remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            z-index: 2;
            transition: all 0.2s;
        }
        .wl-remove-btn:hover { background: #fef2f2; transform: scale(1.1); }

        /* Image */
        .wl-img-wrap {
            width: 100%;
            aspect-ratio: 1/1;
            overflow: hidden;
            background: #f9f9f9;
        }
        .wl-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }
        .wl-card:hover .wl-img { transform: scale(1.05); }

        /* Info section */
        .wl-info {
            padding: 10px 12px 12px;
        }
        .wl-name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
            text-decoration: none;
            margin-bottom: 4px;
            line-height: 1.3;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .wl-stars {
            font-size: 10px;
            color: #f59e0b;
            margin-bottom: 5px;
        }
        .wl-price {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-bottom: 8px;
        }
        .wl-price-current {
            font-size: 14px;
            font-weight: 700;
            color: #00512c;
        }
        .wl-price-old {
            font-size: 12px;
            color: #aaa;
            text-decoration: line-through;
        }
        .wl-view-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 7px 0;
            background: #00512c;
            color: #fff !important;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none !important;
            transition: background 0.2s;
        }
        .wl-view-btn:hover { background: #003d20; }

        /* Empty state */
        .wl-empty {
            text-align: center;
            padding: 60px 20px;
        }
        .wl-empty-icon {
            font-size: 50px;
            color: #e5e7eb;
            display: block;
            margin-bottom: 16px;
        }
        .wl-empty h4 { color: #6b7280; font-size: 18px; margin-bottom: 8px; }
        .wl-empty p { color: #9ca3af; font-size: 14px; margin-bottom: 20px; }
        .wl-shop-btn {
            display: inline-flex;
            align-items: center;
            background: #00512c;
            color: #fff !important;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .wl-shop-btn:hover { background: #003d20; }

        /* Back button */
        .wl-back-btn {
            display: inline-flex;
            align-items: center;
            border: 1.5px solid #00512c;
            color: #00512c !important;
            padding: 9px 20px;
            border-radius: 10px;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }
        .wl-back-btn:hover { background: #00512c; color: #fff !important; }

        /* Mobile responsive */
        @media (max-width: 576px) {
            .wl-title { font-size: 18px; }
            .wl-info { padding: 8px 10px 10px; }
            .wl-name { font-size: 12px; }
            .wl-price-current { font-size: 13px; }
            .wl-view-btn { font-size: 11px; padding: 6px 0; }
        }
    </style>

    @push('scripts')
        <script>
            // Toast function
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
                setTimeout(() => {
                    toast.querySelector('.toast-progress').style.width = '0%';
                }, 10);
                setTimeout(() => {
                    toast.classList.add('hide');
                    setTimeout(() => toast.remove(), 400);
                }, 2500);
            }
            // Cart functionality is now handled by global cart handler in master.blade.php
            // No need for duplicate event listeners here

            // Wishlist card removal – uses the onSuccess callback from global toggleWishlist
            document.querySelectorAll('.product-wishlist-top, .wishlist-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var productId = this.getAttribute('data-product-id');
                    var card = this.closest('[class*="col-"]');

                    if (typeof window.toggleWishlist === 'function') {
                        window.toggleWishlist(parseInt(productId), btn, function(nowActive) {
                            if (!nowActive && card) {
                                card.style.transition = 'opacity 0.3s, transform 0.3s';
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.95)';
                                setTimeout(function() {
                                    card.remove();
                                    var remaining = document.querySelectorAll('.wl-card').length;
                                    var badge = document.querySelector('.wl-badge');
                                    if (badge) badge.innerHTML = '<i class="fas fa-heart"></i> ' + remaining + ' ' + (remaining === 1 ? 'Item' : 'Items');
                                    if (remaining === 0) window.location.reload();
                                }, 300);
                            }
                        });
                    }
                });
            });


            // Card click navigation
            document.querySelectorAll('.wl-card[data-href]').forEach(function(card) {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('button') || e.target.closest('a')) return;
                    window.location.href = this.getAttribute('data-href');
                });
            });

            // No client-side clear-all binding; handled by form submit
        </script>
        <style>
            .custom-toast {
                min-width: 220px;
                max-width: 340px;
                background: #fff;
                color: #222;
                padding: 0;
                border-radius: 10px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.18);
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
            .custom-toast.error { border-left-color: #e53935; }
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
            .custom-toast .toast-close:hover { color: #e53935; }
            .custom-toast .toast-progress {
                position: absolute;
                left: 0; bottom: 0;
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
            </style>
    @endpush
@endsection