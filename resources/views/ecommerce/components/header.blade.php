@php
    use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    @php
        $gtmId = $general_settings->gtm_container_id ?? null;
    @endphp

    {{-- 
    @if($gtmId)
        <!-- Google Tag Manager -->
        <script>
            window.dataLayer = window.dataLayer || [];
        </script>
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || []; w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                }); var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{ $gtmId }}');</script>
        <!-- End Google Tag Manager -->
    @endif
    --}}

    <!-- Global Security Shield: Block Malicious Redirects & Unauthorized Event Listeners -->
    <script>
        (function() {
            const blockedDomain = 'ushort.observer';
            
            // 1. Intercept location changes
            const originalAssign = window.location.assign;
            const originalReplace = window.location.replace;
            
            const checkAndSecure = (url) => {
                if (url && (url.includes(blockedDomain) || (url.startsWith('http') && !url.includes(window.location.hostname)))) {
                    console.error('BLOCKED: Unauthorized redirect attempt to:', url);
                    console.trace();
                    return false;
                }
                return true;
            };

            window.location.assign = function(url) { if(checkAndSecure(url)) return originalAssign.apply(this, arguments); };
            window.location.replace = function(url) { if(checkAndSecure(url)) return originalReplace.apply(this, arguments); };

            // 2. Intercept dataLayer.push (The most common hook for this malware)
            window.dataLayer = window.dataLayer || [];
            const originalPush = window.dataLayer.push;
            window.dataLayer.push = function(data) {
                console.log('[SECURITY] Event Pushed:', data.event || 'unnamed');
                // If the malware is listening for a specific event, we can catch it here
                return originalPush.apply(this, arguments);
            };

            // 3. Prevent the malware from setting window.location.href directly
            // We monitor beforeunload to catch "last second" redirects
            window.addEventListener('beforeunload', function(e) {
                if (document.activeElement && document.activeElement.href && document.activeElement.href.includes(blockedDomain)) {
                    e.preventDefault();
                    console.error('STOPPED: Redirect to malicious domain caught at exit.');
                    return 'Malicious redirect detected. Stay on page?';
                }
            });

            // 4. Block dynamic script tags from the domain
            const originalCreateElement = document.createElement;
            document.createElement = function(tagName) {
                const element = originalCreateElement.call(document, tagName);
                if (tagName.toLowerCase() === 'script') {
                    const originalSetAttribute = element.setAttribute;
                    element.setAttribute = function(name, value) {
                        if (name === 'src' && value.includes(blockedDomain)) {
                            console.error('BLOCKED: Malicious script injection attempt from:', value);
                            console.trace();
                            return;
                        }
                        return originalSetAttribute.apply(this, arguments);
                    };
                    
                    // Also intercept .src directly
                    Object.defineProperty(element, 'src', {
                        set: function(value) {
                            if (value.includes(blockedDomain)) {
                                console.error('BLOCKED: Script .src setter matched malicious domain:', value);
                                return;
                            }
                            this.setAttribute('src', value);
                        },
                        get: function() { return this.getAttribute('src'); }
                    });
                }
                return element;
            };
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@if($pageTitle) {{ $pageTitle . ' | '}} @endif {{ $general_settings->site_title ?? '' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-check" content="{{ auth()->check() ? '1' : '0' }}">

    @php
        // Prefer explicit SEO product if provided
        $___seo = isset($seoProduct) && $seoProduct ? $seoProduct : (isset($product) ? $product : null);
    @endphp
    @if($___seo)
        {{-- Product-specific meta tags --}}
        {{-- Debug: Product ID {{ $___seo->id }}, Name: {{ $___seo->name }} --}}
        <!-- DEBUG: Product ID: {{ $___seo->id }}, Name: {{ $___seo->name }}, Meta Title: {{ $___seo->meta_title ?? 'NULL' }} -->
        <meta name="title" content="{{ $___seo->meta_title ?? $___seo->name }}">
        <meta name="description"
            content="{{ $___seo->meta_description ?? Str::limit(strip_tags($___seo->description ?? ''), 160) }}">
        @php
            $keywords = '';
            if ($___seo->meta_keywords) {
                if (is_array($___seo->meta_keywords)) {
                    $keywords = implode(', ', $___seo->meta_keywords);
                } else {
                    // It's a JSON string, decode it
                    $decoded = json_decode($___seo->meta_keywords, true);
                    if (is_array($decoded)) {
                        $keywords = implode(', ', $decoded);
                    } else {
                        $keywords = $___seo->meta_keywords;
                    }
                }
            }
        @endphp

        <meta name="keywords" content="{{ $keywords }}">

        <meta property="og:title" content="{{ $___seo->meta_title ?? $___seo->name }}">
        <meta property="og:description"
            content="{{ $___seo->meta_description ?? Str::limit(strip_tags($___seo->description ?? ''), 160) }}">
        <meta property="og:image"
            content="{{ $___seo->image ? asset($___seo->image) : asset('static/default-product.jpg') }}">
        <meta property="og:type" content="product">
        <meta property="og:url" content="{{ url()->current() }}">

    @else
        {{-- Default meta tags for general pages --}}
        <meta name="title" content="{{ $general_settings->site_title ?? 'Your Store' }}">
        <meta name="description"
            content="{{ $general_settings->site_description ?? 'Welcome to our online store. Find the best products at great prices.' }}">
        <meta name="keywords" content="{{ $general_settings->site_keywords ?? 'online store, shopping, products, deals' }}">

        {{-- Default Open Graph meta tags --}}
        <meta property="og:title" content="{{ $general_settings->site_title ?? 'Your Store' }}">
        <meta property="og:description"
            content="{{ $general_settings->site_description ?? 'Welcome to our online store. Find the best products at great prices.' }}">
        <meta property="og:image"
            content="{{ $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/default-site-logo.png') }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
    @endif
    <link rel="icon"
        href="{{ $general_settings && $general_settings->site_favicon ? asset($general_settings->site_favicon) : asset('static/default-site-icon.webp') }}"
        type="image/x-icon">
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <link href="{{ asset('ecommerce.css') }}?v={{ @filemtime(public_path('ecommerce.css')) }}" rel="stylesheet" />

    <!-- Fallback for non-JS browsers -->
    <noscript>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css" rel="stylesheet">
    </noscript>

    <!-- Removed Turbo CDN to fix JavaScript functionality issues -->
    @stack('styles')
    @vite(['resources/js/app.js'])
    <style>
        html,
        body {
            font-family: 'Segoe UI', 'SegoeUI', 'Helvetica Neue', Helvetica, Arial, 'Noto Sans', 'Liberation Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif;
        }
    </style>
</head>

<body>
    @if($gtmId ?? null)
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0"
                style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif

    @php
        // Initialize data layer with page info
        $pageType = 'other';
        if (isset($product)) {
            $pageType = 'product';
        } elseif (request()->routeIs('product.archive') || request()->routeIs('product.category')) {
            $pageType = 'product_list';
        } elseif (request()->routeIs('order.success')) {
            $pageType = 'purchase';
        } elseif (request()->routeIs('checkout')) {
            $pageType = 'checkout';
        } elseif (request()->routeIs('cart')) {
            $pageType = 'cart';
        }
    @endphp

    @if($gtmId ?? null)
        <script>
            // Initialize data layer with page info
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'pageType': '{{ $pageType }}',
                'pageTitle': '{{ $pageTitle ?? "" }}',
                'userId': '{{ auth()->id() ?? "" }}',
                @if(auth()->check())
                    'userEmail': '{{ auth()->user()->email ?? "" }}',
                @endif
            });
        </script>
    @endif