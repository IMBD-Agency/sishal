<div id="offcanvasCart" class="offcanvas-cart-overlay">
    <div class="offcanvas-cart-panel">
        <div class="offcanvas-cart-header">
            <h5 class="mb-0">
                <span class="cart-icon-wrap"><i class="fas fa-shopping-bag"></i></span>
                Your Cart
                <span id="cartCount" class="item-count">0</span>
            </h5>
            <button type="button" class="btn-close-modern" onclick="closeOffcanvasCart()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="offcanvas-cart-body">
            <div id="cartItems">
                
            </div>
            
            <div id="emptyCart" class="empty-cart" style="display: none;">
                <i class="fas fa-shopping-cart"></i>
                <h6>Your cart is empty</h6>
                <p class="text-muted">Add some items to get started!</p>
            </div>
        </div>
        
        <div class="offcanvas-cart-footer">
            <div class="subtotal-row">
                <span class="subtotal-label">Subtotal</span>
                <span id="subtotalAmount" class="subtotal-amount">0.00৳</span>
            </div>
            <a href="{{ route('checkout') }}" class="checkout-btn">
                <i class="fas fa-lock me-2"></i>
                Secure Checkout
            </a>
        </div>
    </div>
</div>

<style>
    /* ── Overlay ── */
    .offcanvas-cart-overlay {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(5px);
        z-index: 16000;
        display: none;
        justify-content: flex-end;
    }

    /* ── Panel ── */
    .offcanvas-cart-panel {
        width: 400px;
        max-width: 100vw;
        height: 100vh;
        background: #f6f7fb;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: -8px 0 40px rgba(0,0,0,0.18);
    }
    .offcanvas-cart-overlay.show .offcanvas-cart-panel {
        transform: translateX(0);
    }

    /* ── Header ── */
    .offcanvas-cart-header {
        background: linear-gradient(135deg, #00512c 0%, #0a7a44 100%);
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .offcanvas-cart-header h5 {
        color: #fff;
        font-weight: 700;
        font-size: 17px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .offcanvas-cart-header h5 .cart-icon-wrap {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.18);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }
    .item-count {
        background: #fff;
        color: #00512c;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 20px;
        margin-left: 6px;
        line-height: 1.6;
    }
    .btn-close-modern {
        background: rgba(255,255,255,0.15);
        border: none;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        transition: background 0.2s;
        cursor: pointer;
    }
    .btn-close-modern:hover { background: rgba(255,255,255,0.3); }

    /* ── Body ── */
    .offcanvas-cart-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        scrollbar-width: thin;
        scrollbar-color: #d0d4da transparent;
    }
    .offcanvas-cart-body::-webkit-scrollbar { width: 4px; }
    .offcanvas-cart-body::-webkit-scrollbar-track { background: transparent; }
    .offcanvas-cart-body::-webkit-scrollbar-thumb { background: #d0d4da; border-radius: 4px; }

    /* ── Cart Item Card ── */
    .cart-item {
        background: #fff;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border: 1px solid #eef0f4;
        transition: box-shadow 0.2s, transform 0.2s;
        position: relative;
    }
    .cart-item:hover {
        box-shadow: 0 4px 18px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }
    .cart-item-image {
        width: 72px;
        height: 72px;
        border-radius: 10px;
        object-fit: cover;
        margin-right: 12px;
        border: 1px solid #f0f1f5;
        flex-shrink: 0;
    }
    .cart-item-name {
        font-weight: 600;
        font-size: 14px;
        color: #1a1a2e;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .cart-item-price {
        font-weight: 700;
        font-size: 15px;
        color: #00512c;
        margin-bottom: 10px;
    }

    /* ── Quantity Controls ── */
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 0;
        background: #f4f5f8;
        border-radius: 50px;
        padding: 3px;
        width: fit-content;
        border: 0 !important;
    }
    .quantity-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        color: #333;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 12px;
    }
    .quantity-btn:hover:not(:disabled) {
        background: #00512c;
        color: #fff;
    }
    .quantity-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
    .quantity-display {
        min-width: 32px;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        color: #1a1a2e;
    }

    /* ── Delete Button ── */
    .delete-btn {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 13px;
    }
    .delete-btn:hover {
        background: #fef2f2;
        box-shadow: 0 0 0 3px rgba(239,68,68,0.15);
    }

    /* ── Empty State ── */
    .empty-cart {
        text-align: center;
        padding: 60px 24px;
        color: #9ca3af;
    }
    .empty-cart i {
        font-size: 52px;
        margin-bottom: 16px;
        opacity: 0.3;
        display: block;
    }
    .empty-cart h6 {
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .empty-cart p { font-size: 13px; margin: 0; }

    /* ── Footer ── */
    .offcanvas-cart-footer {
        background: #fff;
        padding: 16px 20px 20px;
        border-top: 1px solid #eef0f4;
        flex-shrink: 0;
    }
    .subtotal-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 2px dashed #eef0f4;
    }
    .subtotal-label {
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .subtotal-amount {
        font-weight: 800;
        font-size: 20px;
        color: #1a1a2e;
    }
    .checkout-btn {
        width: 100%;
        background: linear-gradient(135deg, #00512c 0%, #0a7a44 100%);
        color: #fff;
        border: none;
        padding: 14px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 15px;
        transition: all 0.25s;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        letter-spacing: 0.02em;
        box-shadow: 0 4px 15px rgba(0,81,44,0.3);
    }
    .checkout-btn:hover {
        background: linear-gradient(135deg, #003d20 0%, #006633 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,81,44,0.4);
        color: #fff;
    }
    .checkout-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ── Mobile ── */
    @media (max-width: 480px) {
        .offcanvas-cart-panel { width: 100vw; }
        .cart-item-image { width: 60px; height: 60px; }
        .cart-item { padding: 12px; }
    }

    /* ── Animations ── */
    .slide-out {
        animation: slideOut 0.3s ease-in-out forwards;
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to   { transform: translateX(60px); opacity: 0; }
    }
    .slide-in {
        animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(0) !important;
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 1; }
        to   { transform: translateX(0);    opacity: 1; }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var __cartBodyScrollY = 0;
    var __cartTouchMoveHandler = null;

    // In-memory cart state for optimistic updates
    var __cartState = {};

    function renderCart(cartData) {
        console.log('[CART] renderCart called with data:', cartData);
        
        var cartItemsDiv = $('#cartItems');
        var emptyCartDiv = $('#emptyCart');
        var subtotalAmount = $('#subtotalAmount');
        var cartCount = $('#cartCount');

        // Build new state map from server data
        __cartState = {};
        if (cartData.cart) {
            cartData.cart.forEach(function(item) {
                __cartState[item.cart_id] = {
                    qty: item.qty,
                    price: item.price,
                    max_stock: item.max_stock || 999
                };
            });
        }

        cartItemsDiv.empty();
        if (!cartData.cart || cartData.cart.length === 0) {
            cartItemsDiv.hide();
            emptyCartDiv.show();
            subtotalAmount.text('0.00৳');
            cartCount.text('0');
            return;
        }
        
        cartItemsDiv.show();
        emptyCartDiv.hide();
        var totalCount = 0;
        
        $.each(cartData.cart, function(i, item) {
            totalCount += item.qty;
            var atMax = item.qty >= (item.max_stock || 999);
            cartItemsDiv.append(`
                <div class="cart-item" data-cart-id="${item.cart_id}" data-product-id="${item.product_id}" data-price="${item.price}" data-max-stock="${item.max_stock || 999}">
                    <div class="d-flex align-items-start">
                        <img src="/${item.image ? item.image : 'https://via.placeholder.com/64'}" class="cart-item-image" alt="${item.name}">
                        <div class="cart-item-details flex-grow-1">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">${(item.price * item.qty).toFixed(2)}৳</div>
                            <div class="quantity-controls">
                                <button class="quantity-btn cart-qty-decrease" data-cart-id="${item.cart_id}" ${item.qty <= 1 ? 'disabled' : ''}>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity-display">${item.qty}</span>
                                <button class="quantity-btn cart-qty-increase" data-cart-id="${item.cart_id}" ${atMax ? 'disabled' : ''} title="${atMax ? 'Maximum stock reached' : ''}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            ${atMax ? '<small class="text-danger" style="font-size:0.72rem">Max stock reached</small>' : ''}
                        </div>
                        <button class="delete-btn cart-item-remove" data-cart-id="${item.cart_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `);
        });
        
        subtotalAmount.text(`${cartData.cart_total.toFixed(2)}৳`);
        cartCount.text(totalCount);
    }

    // Recalculate subtotal from current DOM
    function recalcSubtotal() {
        var total = 0;
        var count = 0;
        document.querySelectorAll('.cart-item').forEach(function(el) {
            var cartId = parseInt(el.getAttribute('data-cart-id'));
            var state = __cartState[cartId];
            if (state) {
                total += state.price * state.qty;
                count += state.qty;
            }
        });
        $('#subtotalAmount').text(total.toFixed(2) + '৳');
        $('#cartCount').text(count);
        // Also update navbar badges
        document.querySelectorAll('.nav-cart-count').forEach(function(el){ el.textContent = count; });
    }

    // Optimistic increase
    function increaseQuantity(cartId) {
        var state = __cartState[cartId];
        if (!state) return;

        // Client-side stock enforcement
        if (state.qty >= state.max_stock) {
            if (typeof showToast === 'function') showToast('Maximum available stock reached', 'error');
            return;
        }

        // Optimistic update
        state.qty += 1;
        var itemEl = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
        if (itemEl) {
            itemEl.querySelector('.quantity-display').textContent = state.qty;
            itemEl.querySelector('.cart-item-price').textContent = (state.price * state.qty).toFixed(2) + '৳';
            var decBtn = itemEl.querySelector('.cart-qty-decrease');
            var incBtn = itemEl.querySelector('.cart-qty-increase');
            if (decBtn) decBtn.disabled = state.qty <= 1;
            if (incBtn) {
                var atMax = state.qty >= state.max_stock;
                incBtn.disabled = atMax;
                incBtn.title = atMax ? 'Maximum stock reached' : '';
                // show/hide max stock note
                var maxNote = itemEl.querySelector('.text-danger');
                if (atMax && !maxNote) {
                    itemEl.querySelector('.cart-item-details').insertAdjacentHTML('beforeend', '<small class="text-danger" style="font-size:0.72rem">Max stock reached</small>');
                } else if (!atMax && maxNote) {
                    maxNote.remove();
                }
            }
        }
        recalcSubtotal();
        updateCartQtyBadge();

        // Sync with server
        $.ajax({
            url: '/cart/increase/' + cartId,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (!response.success) {
                    // Revert if server rejected
                    state.qty -= 1;
                    if (itemEl) {
                        itemEl.querySelector('.quantity-display').textContent = state.qty;
                        itemEl.querySelector('.cart-item-price').textContent = (state.price * state.qty).toFixed(2) + '৳';
                        var decBtn = itemEl.querySelector('.cart-qty-decrease');
                        if (decBtn) decBtn.disabled = state.qty <= 1;
                    }
                    recalcSubtotal();
                    updateCartQtyBadge();
                    if (typeof showToast === 'function') showToast(response.message || 'Could not update quantity', 'error');
                } else if (response.max_stock !== undefined) {
                    state.max_stock = response.max_stock;
                }
            },
            error: function() {
                // Revert on network error
                state.qty -= 1;
                if (itemEl) {
                    itemEl.querySelector('.quantity-display').textContent = state.qty;
                    itemEl.querySelector('.cart-item-price').textContent = (state.price * state.qty).toFixed(2) + '৳';
                }
                recalcSubtotal();
                updateCartQtyBadge();
            }
        });
    }

    // Optimistic decrease
    function decreaseQuantity(cartId) {
        var state = __cartState[cartId];
        if (!state || state.qty <= 1) return;

        // Optimistic update
        state.qty -= 1;
        var itemEl = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
        if (itemEl) {
            itemEl.querySelector('.quantity-display').textContent = state.qty;
            itemEl.querySelector('.cart-item-price').textContent = (state.price * state.qty).toFixed(2) + '৳';
            var decBtn = itemEl.querySelector('.cart-qty-decrease');
            var incBtn = itemEl.querySelector('.cart-qty-increase');
            if (decBtn) decBtn.disabled = state.qty <= 1;
            if (incBtn) {
                incBtn.disabled = false;
                incBtn.title = '';
                var maxNote = itemEl.querySelector('.text-danger');
                if (maxNote) maxNote.remove();
            }
        }
        recalcSubtotal();
        updateCartQtyBadge();

        // Sync with server
        $.ajax({
            url: '/cart/decrease/' + cartId,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (!response.success) {
                    // Revert
                    state.qty += 1;
                    if (itemEl) {
                        itemEl.querySelector('.quantity-display').textContent = state.qty;
                        itemEl.querySelector('.cart-item-price').textContent = (state.price * state.qty).toFixed(2) + '৳';
                        var decBtn = itemEl.querySelector('.cart-qty-decrease');
                        if (decBtn) decBtn.disabled = state.qty <= 1;
                    }
                    recalcSubtotal();
                    updateCartQtyBadge();
                }
            },
            error: function() {
                state.qty += 1;
                if (itemEl) {
                    itemEl.querySelector('.quantity-display').textContent = state.qty;
                    itemEl.querySelector('.cart-item-price').textContent = (state.price * state.qty).toFixed(2) + '৳';
                }
                recalcSubtotal();
                updateCartQtyBadge();
            }
        });
    }

    // Delegated event listeners on cartItems container for performance
    document.addEventListener('click', function(e) {
        var incBtn = e.target.closest('.cart-qty-increase');
        var decBtn = e.target.closest('.cart-qty-decrease');
        var removeBtn = e.target.closest('.cart-item-remove');

        if (incBtn) {
            var cartId = parseInt(incBtn.getAttribute('data-cart-id'));
            increaseQuantity(cartId);
        } else if (decBtn) {
            var cartId = parseInt(decBtn.getAttribute('data-cart-id'));
            decreaseQuantity(cartId);
        } else if (removeBtn) {
            var cartId = parseInt(removeBtn.getAttribute('data-cart-id'));
            removeItem(cartId);
        }
    });

    function removeItem(cartId) {
        var itemEl = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
        if (itemEl) {
            itemEl.style.opacity = '0.4';
            itemEl.style.pointerEvents = 'none';
        }
        $.ajax({
            url: '/cart/delete/' + cartId,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    delete __cartState[cartId];
                    if (itemEl) itemEl.remove();
                    recalcSubtotal();
                    updateCartQtyBadge();
                    if (Object.keys(__cartState).length === 0) {
                        $('#cartItems').hide();
                        $('#emptyCart').show();
                    }
                } else {
                    // Restore on failure
                    if (itemEl) { itemEl.style.opacity = ''; itemEl.style.pointerEvents = ''; }
                }
            },
            error: function() {
                if (itemEl) { itemEl.style.opacity = ''; itemEl.style.pointerEvents = ''; }
            }
        });
    }

    function fetchCartData() {
        var timestamp = new Date().getTime();
        $.get('/cart/list?t=' + timestamp, function(data) {
            renderCart(data);
        }).fail(function(xhr, status, error) {
            console.error('[CART] Error fetching cart data:', error);
        });
    }

    function openOffcanvasCart() {
        var overlay = document.getElementById('offcanvasCart');
        var panel = overlay.querySelector('.offcanvas-cart-panel');
        overlay.style.display = 'flex';
        overlay.classList.add('show');
        panel.classList.remove('slide-out');
        void panel.offsetWidth;
        panel.classList.add('slide-in');
        var scrollBarComp = window.innerWidth - document.documentElement.clientWidth;
        __cartBodyScrollY = window.scrollY || window.pageYOffset || 0;
        document.documentElement.style.overflow = 'hidden';
        document.documentElement.style.overscrollBehavior = 'contain';
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = '-' + __cartBodyScrollY + 'px';
        document.body.style.width = '100%';
        document.body.style.touchAction = 'none';
        if (scrollBarComp > 0) {
            document.body.style.paddingRight = scrollBarComp + 'px';
        }
        __cartTouchMoveHandler = function(e){
            var withinPanel = e.target.closest && e.target.closest('.offcanvas-cart-panel');
            if (!withinPanel) e.preventDefault();
        };
        document.addEventListener('touchmove', __cartTouchMoveHandler, { passive: false });
        fetchCartData();
    }

    function closeOffcanvasCart() {
        const overlay = document.getElementById('offcanvasCart');
        const panel = overlay.querySelector('.offcanvas-cart-panel');
        panel.classList.remove('slide-in');
        panel.classList.add('slide-out');
        overlay.classList.remove('show');
        setTimeout(() => {
            overlay.style.display = 'none';
            document.removeEventListener('touchmove', __cartTouchMoveHandler || function(){}, { passive: false });
            __cartTouchMoveHandler = null;
            document.documentElement.style.overflow = '';
            document.documentElement.style.overscrollBehavior = '';
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            document.body.style.touchAction = '';
            document.body.style.paddingRight = '';
            if (typeof __cartBodyScrollY === 'number') {
                window.scrollTo(0, __cartBodyScrollY);
            }
            panel.classList.remove('slide-out');
        }, 300);
    }

    function updateCartCount() {
        $.get('/cart/qty-sum', function(data) {
            if (data && data.qty_sum !== undefined) {
                var count = data.qty_sum;
                var cartCountEl = document.getElementById('cartCount');
                if (cartCountEl) cartCountEl.textContent = count;
                document.querySelectorAll('.nav-cart-count').forEach(function(el) { el.textContent = count; });
            }
        });
    }

    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Close on overlay click (desktop) + touch (mobile)
    document.addEventListener('DOMContentLoaded', function() {
        var cartOverlay = document.getElementById('offcanvasCart');
        if (cartOverlay && cartOverlay.parentElement !== document.body) {
            document.body.appendChild(cartOverlay);
        }
        const overlay = document.getElementById('offcanvasCart');

        // Desktop: click on backdrop
        overlay.addEventListener('click', function(e) {
            if (!e.target.closest('.offcanvas-cart-panel')) closeOffcanvasCart();
        });

        // Mobile: touchstart then touchend on backdrop (bypasses touchmove prevention)
        var _touchStartedOnBackdrop = false;
        overlay.addEventListener('touchstart', function(e) {
            _touchStartedOnBackdrop = !e.target.closest('.offcanvas-cart-panel');
        }, { passive: true });
        overlay.addEventListener('touchend', function(e) {
            if (_touchStartedOnBackdrop && !e.target.closest('.offcanvas-cart-panel')) {
                e.preventDefault();
                closeOffcanvasCart();
            }
            _touchStartedOnBackdrop = false;
        }, { passive: false });

        fetchCartData();
    });


    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeOffcanvasCart();
    });
</script>
