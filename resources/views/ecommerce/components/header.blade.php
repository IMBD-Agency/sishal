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

    <!-- Security Monitor: Detect Malicious Redirects -->
    <script>
        (function() {
            const blockedDomain = 'ushort.observer';
            
            // Monitor for suspicious navigation
            window.addEventListener('beforeunload', function(e) {
                if (document.activeElement && document.activeElement.href && document.activeElement.href.includes(blockedDomain)) {
                    console.error('CRITICAL: Malicious redirect detected to:', document.activeElement.href);
                }
            });

            // Intercept dynamic script injections
            const originalCreateElement = document.createElement;
            document.createElement = function(tagName) {
                const element = originalCreateElement.call(document, tagName);
                if (tagName.toLowerCase() === 'script') {
                    const originalSetAttribute = element.setAttribute;
                    element.setAttribute = function(name, value) {
                        if (name === 'src' && value.includes(blockedDomain)) {
                            console.error('BLOCKED: Malicious script injection attempt from:', value);
                            console.trace(); // This will show exactly where the malicious code is hidden
                            return;
                        }
                        return originalSetAttribute.apply(this, arguments);
                    };
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