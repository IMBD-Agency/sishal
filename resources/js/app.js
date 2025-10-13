import './bootstrap';

import Alpine from 'alpinejs';
import Splide from '@splidejs/splide';
import '@splidejs/splide/css';

window.Alpine = Alpine;

Alpine.start();

// Custom Banner Carousel (works without Bootstrap)
function initCategorySplide() {
    try {
        const categoryEl = document.getElementById('categorySplide');
        if (categoryEl && !categoryEl.__splideMounted) {
            const splide = new Splide(categoryEl, {
                type: 'slide',
                perPage: 4,
                perMove: 1,
                gap: '16px',
                pagination: false,
                arrows: true,
                drag: true,
                flickPower: 300,
                releaseWheel: true,
                keyboard: 'focused',
                breakpoints: {
                    1199: { perPage: 3 },
                    767: { perPage: 2 }
                }
            });
            splide.mount();
            categoryEl.__splideMounted = true;
        }
    } catch (e) {
        console.error('Failed to mount category Splide:', e);
    }
}

// Try at different lifecycle moments to avoid timing issues
document.addEventListener('DOMContentLoaded', initCategorySplide);
window.addEventListener('load', initCategorySplide);
setTimeout(initCategorySplide, 0);

document.addEventListener('DOMContentLoaded', function() {
    // Build Top Selling Splide from API
    (function initMostSoldSplide(){
        const wrapper = document.getElementById('mostSoldSplide');
        const listEl = document.getElementById('mostSoldSplideList');
        const fallback = document.getElementById('mostSoldFallback');
        if (!wrapper || !listEl) return;
        fetch('/api/products/most-sold')
            .then(r => r.json())
            .then(products => {
                if (!Array.isArray(products) || products.length === 0) {
                    if (fallback) fallback.style.display = 'block';
                    wrapper.style.display = 'none';
                    return;
                }
                if (fallback) fallback.style.display = 'none';
                listEl.innerHTML = products.map(product => {
                    const rating = product.avg_rating ?? product.rating ?? 0;
                    const reviews = product.total_reviews ?? 0;
                    const price = Number(product.price || 0).toFixed(2);
                    const image = product.image ? product.image : '/default-product.png';
                    const discounted = Number(product.discount || 0);
                    return `
                        <li class="splide__slide">
                            <div class="product-card position-relative no-hover-border" data-href="/product/${product.slug}">
                                <button class="wishlist-btn${product.is_wishlisted ? ' active' : ''}" data-product-id="${product.id}"><i class="${product.is_wishlisted ? 'fas text-danger' : 'far'} fa-heart"></i></button>
                                <div class="product-image-container">
                                    <img src="${image}" class="product-image" alt="${product.name}">
                                    <div class="rating-badge"><span>${(Math.round(rating*10)/10).toFixed(1)}</span><i class="fas fa-star star"></i><span>| ${reviews}</span></div>
                                </div>
                                <div class="product-info">
                                    <a href="/product/${product.slug}" style="text-decoration: none" class="product-title">${product.name}</a>
                                    <div class="price">${discounted > 0 ? `<span class=\"fw-bold text-primary\">${discounted.toFixed(2)}৳</span><span class=\"old\">${price}৳</span>` : `<span class=\"fw-bold text-primary\">${price}৳</span>`}</div>
                                    <div class="d-flex justify-content-between align-items-center gap-2 product-actions">
                                        <button class="btn-add-cart" data-product-id="${product.id}" data-product-name="${product.name}" data-has-stock="${product.has_stock ? 'true' : 'false'}" ${!product.has_stock ? 'disabled' : ''}>
                                            <svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" fill="#fff" width="14" height="14"><path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z"></path><circle cx="7" cy="22" r="2"></circle><circle cx="17" cy="22" r="2"></circle></svg> ${product.has_stock ? 'Add to Cart' : 'Out of Stock'}</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;
                }).join('');
                wrapper.style.visibility = 'visible';
                const topSplide = new Splide(wrapper, {
                    perPage: 4,
                    gap: '16px',
                    pagination: false,
                    arrows: true,
                    breakpoints: { 1199: { perPage: 3 }, 991: { perPage: 2 }, 575: { perPage: 1 } }
                });
                topSplide.mount();
            })
            .catch(() => {
                if (fallback) {
                    fallback.innerHTML = '<div class="col-12 text-center text-danger">Failed to load products.</div>';
                    fallback.style.display = 'block';
                }
                if (wrapper) wrapper.style.display = 'none';
            });
    })();
    // Init Hero Splide
    try {
        const heroEl = document.getElementById('heroSplide');
        if (heroEl && !heroEl.__splideMounted) {
            const hero = new Splide(heroEl, {
                type: 'loop',
                autoplay: true,
                interval: 5000,
                pauseOnHover: true,
                arrows: true,
                pagination: true,
                drag: true,
                rewind: true,
            });
            hero.mount();
            heroEl.__splideMounted = true;
        }
    } catch (e) { console.error('Failed to mount hero Splide:', e); }
    
    const carousel = document.getElementById('homeHeroCarousel');
    // old markup removed; keep code for backward compatibility on other pages

    const items = carousel.querySelectorAll('.carousel-item');
    const indicators = carousel.querySelectorAll('.carousel-indicators button');
    const prevBtn = carousel.querySelector('.carousel-control-prev');
    const nextBtn = carousel.querySelector('.carousel-control-next');
    
    if (items.length === 0) return;

    let currentIndex = 0;
    let autoPlayInterval = null;
    let isHovering = false;

    // Show specific slide
    function showSlide(index) {
        items.forEach(item => item.classList.remove('active'));
        indicators.forEach(ind => ind.classList.remove('active'));
        
        currentIndex = (index + items.length) % items.length;
        items[currentIndex].classList.add('active');
        if (indicators[currentIndex]) {
            indicators[currentIndex].classList.add('active');
        }
    }

    // Next slide
    function nextSlide() {
        showSlide(currentIndex + 1);
    }

    // Previous slide
    function prevSlide() {
        showSlide(currentIndex - 1);
    }

    // Auto-play
    function startAutoPlay() {
        autoPlayInterval = setInterval(() => {
            if (!isHovering) nextSlide();
        }, 5000);
    }

    function stopAutoPlay() {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
            autoPlayInterval = null;
        }
    }

    // Event listeners for controls
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            prevSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }

    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', (e) => {
            e.preventDefault();
            showSlide(index);
            stopAutoPlay();
            startAutoPlay();
        });
    });

    // Hover pause
    carousel.addEventListener('mouseenter', () => {
        isHovering = true;
    });

    carousel.addEventListener('mouseleave', () => {
        isHovering = false;
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') prevSlide();
        if (e.key === 'ArrowRight') nextSlide();
    });

    // Mouse drag support with better detection (supports dragging on links)
    let isDown = false;
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    let didDrag = false;

    carousel.addEventListener('mousedown', (e) => {
        // Allow drag on everything, including anchors; we'll suppress click later if it was a drag
        isDown = true;
        startX = e.pageX - carousel.offsetLeft;
        carousel.style.cursor = 'grabbing';
        isDragging = false;
        didDrag = false;
    });

    carousel.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        currentX = e.pageX - carousel.offsetLeft;
        const walk = currentX - startX;
        
        if (Math.abs(walk) > 5) {
            isDragging = true;
            didDrag = true;
        }
    });

    carousel.addEventListener('mouseup', (e) => {
        if (!isDown) return;
        
        const deltaX = currentX - startX;
        isDown = false;
        carousel.style.cursor = 'grab';
        
        // Only change slide if dragged enough distance
        if (isDragging && Math.abs(deltaX) > 50) {
            if (deltaX < 0) {
                nextSlide();
            } else {
                prevSlide();
            }
            stopAutoPlay();
            startAutoPlay();
        }
        
        isDragging = false;
    });

    carousel.addEventListener('mouseleave', () => {
        if (isDown) {
            isDown = false;
            carousel.style.cursor = 'grab';
        }
    });

    // Prevent text selection while dragging
    carousel.addEventListener('dragstart', (e) => {
        e.preventDefault();
    });

    // Suppress anchor navigation if a drag happened, but ignore clicks on controls
    carousel.addEventListener('click', (e) => {
        if (!didDrag) return;
        if (e.target.closest('.carousel-control-prev') || e.target.closest('.carousel-control-next')) {
            didDrag = false; return;
        }
        const anchor = e.target.closest('a');
        if (anchor) {
            e.preventDefault();
            e.stopPropagation();
        }
        didDrag = false;
    }, true);

    // Touch support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    carousel.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    carousel.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        }
        if (touchEndX > touchStartX + 50) {
            prevSlide();
            stopAutoPlay();
            startAutoPlay();
        }
    }

    // Initialize safely (avoid duplicate mounts)
    if (!carousel.dataset.initialized) {
        carousel.dataset.initialized = 'true';
        showSlide(0);
        startAutoPlay();
        carousel.style.cursor = 'grab';
    }
});
