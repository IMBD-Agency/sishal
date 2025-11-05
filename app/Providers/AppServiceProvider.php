<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\AdditionalPage;
use App\Models\ProductServiceCategory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $generalSettings = GeneralSetting::first() ?? new GeneralSetting([
            'site_title' => 'Your Store',
            'site_description' => 'Welcome to our online store. Find the best products at great prices.',
            'site_keywords' => 'online store, shopping, products, deals',
            'site_logo' => null,
            'site_favicon' => null
        ]);

        $additionalPages = AdditionalPage::where('is_active', 1)->select('id', 'title', 'slug', 'positioned_at')->get();
        
        // Share categories globally for navigation menu
        $navCategories = ProductServiceCategory::whereNull('parent_id')
            ->where('status', 'active')
            ->with(['children' => function($q) {
                $q->where('status', 'active')->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
        
        View::share('general_settings', $generalSettings);
        View::share('additional_pages', $additionalPages);
        View::share('nav_categories', $navCategories);
        // Blade directives for roles and permissions
        \Blade::directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });
        
        \Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });
        
        \Blade::directive('permission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermissionTo({$permission})): ?>";
        });
        
        \Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
