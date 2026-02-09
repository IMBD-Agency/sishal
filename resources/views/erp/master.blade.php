<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{$general_settings->site_title ?? ''}}</title>
    
    <!-- Critical CSS: Prevents Layout Shift -->
    <style>
        :root { --sidebar-width: 320px; --primary-color: #198754; --primary-rgb: 25, 135, 84; }
        html { scrollbar-gutter: stable; }
        
        /* Critical Layout Styles */
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; opacity: 0; transition: opacity 0.3s ease; }
        body.loaded { opacity: 1; }
        .sidebar { width: var(--sidebar-width); position: fixed; left: 0; top: 0; height: 100vh; background: #fff; z-index: 1050; border-right: 1px solid #E5E7EB; overflow-y: auto; }
        .main-content { margin-left: var(--sidebar-width); padding-top: 70px; min-height: 100vh; }
        .header { position: fixed; top: 0; right: 0; left: var(--sidebar-width); height: 70px; z-index: 1020; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; padding: 0 1.5rem; }
        
        /* Sidebar Brand & Logo - Left Aligned */
        .sidebar-brand { padding: 0 1.5rem; background: #ffffff; height: 90px; display: flex; align-items: center; border-bottom: 2px solid #f8fafc; position: sticky; top: 0; z-index: 10; justify-content: flex-start; }
        .brand-logo-link { display: block; width: 100%; height: 60px; background-size: contain; background-repeat: no-repeat; background-position: left center; }
        
        /* Navigation Styles - Strictly Left Aligned */
        .sidebar-nav { padding-bottom: 3rem; }
        .sidebar-category-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em; padding: 1.5rem 1rem 0.5rem 1rem; display: block; text-align: left; }
        .nav-item { margin: 4px 12px; }
        .nav-link { color: #475569; padding: 12px 16px; border-radius: 8px; text-decoration: none; display: flex !important; align-items: center !important; justify-content: flex-start !important; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
        .nav-link span { text-align: left; margin-left: 0; }
        .nav-link:hover { background: #f1f5f9; color: var(--primary-color); }
        .nav-link.active { background: #ecfdf5; color: var(--primary-color); font-weight: 700; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .nav-icon { width: 20px; margin-right: 12px !important; font-size: 1.1rem; text-align: center; flex-shrink: 0; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 280px; transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
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
