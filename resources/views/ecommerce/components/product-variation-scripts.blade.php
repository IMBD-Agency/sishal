@php
    // Optimize variation payload to reduce memory usage
    // Only include essential data, limit gallery images
    $__variationPayload = ($product->variations ?? collect())->map(function($v) use ($product) {
        return [
            'id' => $v->id,
            'name' => $v->name,
            'price' => (float) ($v->final_price ?? $v->price ?? $product->price),
            'image' => $v->image ? asset($v->image) : null,
            // Limit gallery images to first 3 to reduce memory
            'galleries' => $v->galleries->take(3)->map(function($g) {
                return asset($g->image);
            })->values()->all(),
            'available_stock' => (int) ($v->available_stock ?? 0),
            'attribute_value_ids' => $v->combinations->pluck('attribute_value_id')->values()->all(),
        ];
    })->values()->all();
@endphp

<script>
    console.log('[VARIATION] Variation script section reached');
    
    // Global error handler for querySelector errors
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('querySelector') && e.message.includes('not a valid selector')) {
            console.error('[VARIATION] querySelector Error Caught:', e.message);
            // Prevent the error from propagating
            e.preventDefault();
            e.stopPropagation();
            return true;
        }
    }, true);
    
    // Global querySelector wrapper to prevent invalid selector errors
    (function() {
        var originalQuerySelector = Document.prototype.querySelector;
        var originalQuerySelectorAll = Document.prototype.querySelectorAll;
        
        Document.prototype.querySelector = function(selector) {
            if (!selector || selector === '' || selector === '#' || selector === 'undefined' || selector === 'null') {
                return null;
            }
            try {
                return originalQuerySelector.call(this, selector);
            } catch (e) {
                return null;
            }
        };
        
        Document.prototype.querySelectorAll = function(selector) {
            if (!selector || selector === '' || selector === '#' || selector === 'undefined' || selector === 'null') {
                return [];
            }
            try {
                return originalQuerySelectorAll.call(this, selector);
            } catch (e) {
                return [];
            }
        };
        
        // Also wrap Element.prototype methods
        var originalElementQuerySelector = Element.prototype.querySelector;
        var originalElementQuerySelectorAll = Element.prototype.querySelectorAll;
        
        Element.prototype.querySelector = function(selector) {
            if (!selector || selector === '' || selector === '#' || selector === 'undefined' || selector === 'null') {
                return null;
            }
            try {
                return originalElementQuerySelector.call(this, selector);
            } catch (e) {
                return null;
            }
        };
        
        Element.prototype.querySelectorAll = function(selector) {
            if (!selector || selector === '' || selector === '#' || selector === 'undefined' || selector === 'null') {
                return [];
            }
            try {
                return originalElementQuerySelectorAll.call(this, selector);
            } catch (e) {
                return [];
            }
        };
    })();

    function initializeVariationSelection() {
        var hasVariations = @json($product->has_variations);
        if (!hasVariations) {
            return;
        }

        var productVariations = @json($__variationPayload);
        // Limit variations data to prevent memory issues
        if (Array.isArray(productVariations) && productVariations.length > 50) {
            console.warn('[VARIATION] Too many variations, limiting to 50 to prevent memory issues');
            productVariations = productVariations.slice(0, 50);
        }

        // Apply variation logic only if product has variations
        var initialAddBtn = document.querySelector('.btn-add-cart');
        if (initialAddBtn) {
            initialAddBtn.disabled = true;
        }

        function updateHiddenSelectedValues(selectedMap) {
            var container = document.getElementById('selected-attribute-values');
            if (!container) return;
            container.innerHTML = '';
            Object.keys(selectedMap).forEach(function(attrId) {
                if (!attrId || attrId === 'undefined' || attrId === 'null' || attrId === '' || attrId === '#') return;
                
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'attribute_value_ids[]';
                input.value = selectedMap[attrId];
                container.appendChild(input);
            });
        }

        function renderSelectionLabels(selectedMap) {
            Object.keys(selectedMap).forEach(function(attrId) {
                if (!attrId || attrId === 'undefined' || attrId === 'null' || attrId === '' || attrId === '#') return;
                
                var sanitizedAttrId = String(attrId).replace(/[^a-zA-Z0-9_-]/g, '');
                if (!sanitizedAttrId) return;
                
                try {
                    var labelEl = document.querySelector('[data-selected-label="attr-' + sanitizedAttrId + '"]');
                    if (labelEl) {
                        var btn = document.querySelector('.size-option.active[data-attr-id="' + sanitizedAttrId + '"], .color-option.active[data-attr-id="' + sanitizedAttrId + '"], .color-image-btn.active[data-attr-id="' + sanitizedAttrId + '"]');
                        var label = btn ? (btn.getAttribute('data-label') || btn.textContent) : '';
                        if (label) { labelEl.textContent = label; }
                    }
                } catch (e) {}
            });
        }

        function tryResolveVariation(selectedMap) {
            if (!productVariations || productVariations.length === 0) return null;
            var selectedIds = Object.values(selectedMap).filter(Boolean).map(function(v){ return parseInt(v, 10); }).sort(function(a,b){return a-b;});
            if (selectedIds.length === 0) return null;
            
            var resolved = null;
            var maxIterations = Math.min(productVariations.length, 100);
            
            for (var i = 0; i < maxIterations; i++) {
                var v = productVariations[i];
                if (!v || !v.attribute_value_ids) continue;
                var ids = v.attribute_value_ids.map(function(x){ return parseInt(x, 10); }).sort(function(a,b){return a-b;});
                if (ids.length && ids.length === selectedIds.length) {
                    var same = ids.every(function(x, idx){ return x === selectedIds[idx]; });
                    if (same) { resolved = v; break; }
                }
            }
            
            if (!resolved && selectedIds.length > 0) {
                for (var i = 0; i < maxIterations; i++) {
                    var v = productVariations[i];
                    if (!v || !v.attribute_value_ids) continue;
                    var ids = v.attribute_value_ids.map(function(x){ return parseInt(x, 10); });
                    var allSelectedMatch = selectedIds.every(function(selectedId) {
                        return ids.indexOf(selectedId) !== -1;
                    });
                    if (allSelectedMatch) { resolved = v; break; }
                }
            }
            return resolved;
        }

        function switchToVariationImages(variation) {
            console.log('[VARIATION] Switching to variation images');
            var allVariationSlides = document.querySelectorAll('.variation-image-slide, .variation-gallery-slide, .variation-thumb-slide, .variation-gallery-thumb-slide');
            allVariationSlides.forEach(function(slide) { slide.style.display = 'none'; });
            
            var productSlides = document.querySelectorAll('[data-image-type="product"], [data-image-type="gallery"]');
            productSlides.forEach(function(slide) { slide.style.display = 'block'; });
            
            if (variation && variation.image) {
                productSlides.forEach(function(slide) { slide.style.display = 'none'; });
                document.querySelectorAll('.variation-image-slide[data-variation-id="' + variation.id + '"]').forEach(function(slide) { slide.style.display = 'block'; });
                document.querySelectorAll('.variation-gallery-slide[data-variation-id="' + variation.id + '"]').forEach(function(slide) { slide.style.display = 'block'; });
                document.querySelectorAll('.variation-thumb-slide[data-variation-id="' + variation.id + '"]').forEach(function(slide) { slide.style.display = 'block'; });
                document.querySelectorAll('.variation-gallery-thumb-slide[data-variation-id="' + variation.id + '"]').forEach(function(slide) { slide.style.display = 'block'; });
                
                if (window.mainSwiper) window.mainSwiper.update();
                if (window.thumbSwiper) window.thumbSwiper.update();
                
                requestAnimationFrame(function() {
                    if (typeof window.initImageZoom === 'function') window.initImageZoom();
                });
            } else {
                if (window.mainSwiper) window.mainSwiper.update();
                if (window.thumbSwiper) window.thumbSwiper.update();
                requestAnimationFrame(function() {
                    if (typeof window.initImageZoom === 'function') window.initImageZoom();
                });
            }
        }

        var selectedMap = {};
        
        function handleVariationClick(btn, e) {
            e.preventDefault();
            e.stopPropagation();
            var attrId = btn.getAttribute('data-attr-id');
            var valId = btn.getAttribute('data-value-id');
            if (!attrId || !valId) return;

            var container = btn.closest('[data-attribute-id]');
            if (container) {
                container.querySelectorAll('.size-option, .color-option, .color-image-btn').forEach(function(b){ 
                    b.classList.remove('active', 'variation-active'); 
                });
            }
            
            if (selectedMap[String(attrId)] === String(valId)) {
                delete selectedMap[String(attrId)];
            } else {
                btn.classList.add('active', 'variation-active');
                selectedMap[String(attrId)] = String(valId);
            }
            
            updateHiddenSelectedValues(selectedMap);
            renderSelectionLabels(selectedMap);
            
            var resolved = tryResolveVariation(selectedMap);
            var isCompleteMatch = Object.keys(selectedMap).length === document.querySelectorAll('[data-attribute-id]').length;
            
            var varIdEl = document.getElementById('selected-variation-id');
            var nameEl = document.getElementById('selected-variation-name');
            var priceEl = document.getElementById('selected-variation-price');
            var stockEl = document.getElementById('selected-variation-stock');
            var addBtn = document.querySelector('.btn-add-cart');
            var buyNowBtn = document.querySelector('.btn-buy-now');
            
            if (resolved && isCompleteMatch) {
                if (varIdEl) varIdEl.value = resolved.id;
                var buyNowVariationId = document.getElementById('buy-now-variation-id');
                if (buyNowVariationId) buyNowVariationId.value = resolved.id;
                
                if (nameEl) nameEl.textContent = resolved.name || 'Selected';
                if (priceEl) priceEl.textContent = Number(resolved.price).toFixed(2) + '৳';
                if (stockEl) {
                    stockEl.textContent = resolved.available_stock > 0 ? 'In stock: ' + resolved.available_stock : 'Out of stock';
                    stockEl.style.color = resolved.available_stock > 0 ? '' : 'red';
                }
                setInlineStock(resolved.available_stock);
                if (addBtn) addBtn.disabled = resolved.available_stock <= 0;
                if (buyNowBtn) buyNowBtn.disabled = resolved.available_stock <= 0;
                switchToVariationImages(resolved);
            } else {
                if (varIdEl) varIdEl.value = '';
                var buyNowVariationId = document.getElementById('buy-now-variation-id');
                if (buyNowVariationId) buyNowVariationId.value = '';
                
                if (nameEl) nameEl.textContent = isCompleteMatch ? 'No variation found' : 'Please select all options';
                if (priceEl) priceEl.textContent = '—';
                if (stockEl) stockEl.textContent = 'Select options to see price';
                setInlineStock(null);
                if (addBtn) addBtn.disabled = true;
                if (buyNowBtn) buyNowBtn.disabled = true;
                switchToVariationImages(resolved);
            }
        }

        document.querySelectorAll('.color-option, .size-option, .color-image-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) { handleVariationClick(btn, e); });
        });
    }

    initializeVariationSelection();
</script>
