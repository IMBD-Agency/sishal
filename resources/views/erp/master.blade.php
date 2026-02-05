<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{$general_settings->site_title ?? ''}}</title>
    
    <!-- Critical CSS: Prevents Layout Shift -->
    <style>
        :root { --sidebar-width: 320px; }
        html { scrollbar-gutter: stable; } /* FORCE scrollbar space always - prevents header jump */
        .sidebar { width: 320px; position: fixed; left: 0; top: 0; height: 100vh; background: #fff; z-index: 1050; contain: layout size; }
        .main-content { margin-left: 320px; padding-top: 70px; }
        .header { position: fixed; top: 0; right: 0; left: 320px; height: 70px; z-index: 1020; background: #fff; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 280px; }
            .header { left: 0; height: 64px; }
            .main-content { margin-left: 0; padding-top: 64px; }
        }
    </style>

    <!-- Preload Logo for instant feel -->
    @php $logoUrl = $general_settings && $general_settings->site_logo ? asset($general_settings->site_logo) : asset('static/default-logo.webp'); @endphp
    <link rel="preload" as="image" href="{{ $logoUrl }}" fetchpriority="high">
    
    <link rel="icon" href="{{ $general_settings && $general_settings->site_favicon ? asset($general_settings->site_favicon) : asset('static/default-site-icon.webp') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="{{ asset('erp.css') }}" rel="stylesheet">
    <link href="{{ asset('premium-theme.css') }}?v=1.0.3" rel="stylesheet">
    <link href="{{ asset('erp-style-fixes.css') }}?v=1.0.3" rel="stylesheet">
    
    @stack('css')
    @stack('head')
</head>
<body class="loaded">
    <!-- Slim Top Progress Bar (GitHub Style) -->
    <div id="top-progress-bar"></div>

    @yield('body')

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // High-Performance ERP Logic (Standard Page Reloads)
        function initializePageFunctions() {
            const body = document.body;
            const sidebar = document.getElementById('sidebar');
            
            // Remove loading screen (Turbo-Fast Mode)
            setTimeout(() => {
                body.classList.remove('loading');
                body.classList.add('loaded');
                if (sidebar) sidebar.classList.add('transition-enabled');
            }, 10); // Reduced delay for instant feel

            // Global Select2 Initializer
            $('.select2, .select2-simple, .select2-setup').each(function() {
                if (!$(this).hasClass("select2-hidden-accessible")) {
                    const placeholder = $(this).data('placeholder') || $(this).attr('placeholder') || 'Select an option';
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        allowClear: true,
                        placeholder: { id: '', text: placeholder }
                    });
                }
            });
        }

        // Run on Page Load
        document.addEventListener('DOMContentLoaded', initializePageFunctions);

        // Pure Browser-Based Fast Navigation Feedack
        window.addEventListener('beforeunload', () => {
            const bar = document.getElementById('top-progress-bar');
            if (bar) bar.style.width = '70%'; // Immediate visual start
        });

        // Clear on Page Show (Back button etc)
        window.addEventListener('pageshow', () => {
            const bar = document.getElementById('top-progress-bar');
            if (bar) bar.style.width = '0%';
        });

        // Global Select2 Auto-Focus Fix
        $(document).on('select2:open', (e) => {
            const container = $('.select2-container--open');
            if (container.length) {
                setTimeout(() => {
                    const searchField = container.find('.select2-search__field');
                    if (searchField.length) searchField[0].focus();
                }, 100);
            }
        });

        // Smart Prefetching (Makes clicks feel instant)
        document.addEventListener('mouseover', (e) => {
            const link = e.target.closest('.nav-link');
            if (link && link.href && !link.dataset.prefetched) {
                const prefetch = document.createElement('link');
                prefetch.rel = 'prefetch';
                prefetch.href = link.href;
                document.head.appendChild(prefetch);
                link.dataset.prefetched = 'true';
            }
        });

    </script>
    @stack('scripts')
</body>
</html>
