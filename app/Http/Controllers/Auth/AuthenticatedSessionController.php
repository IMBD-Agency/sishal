<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $pageTitle = 'Login';
        return view('auth.login', compact('pageTitle'));
    }

    /**
     * Display the ERP login view.
     */
    public function erpLogin(): View
    {
        return view('erp.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // If explicitly coming from ERP login, go to the best available page based on permissions
        if ($request->login_source === 'erp') {
            $user = Auth::user();
            
            // Define prioritized landing pages based on permissions
            $landingPages = [
                'view dashboard' => 'erp.dashboard',
                'view products' => 'product.list',
                'use pos' => 'pos.add',
                'view sales' => 'pos.list',
                'view purchases' => 'purchase.list',
                'view online orders' => 'order.list',
                'view branches' => 'branches.index',
            ];

            foreach ($landingPages as $permission => $routeName) {
                if ($user->hasPermissionTo($permission)) {
                    return redirect()->intended(route($routeName, absolute: false));
                }
            }

            // Ultimate fallback for ERP users
            return redirect()->intended(route('erp.profile', absolute: false));
        }

        // Otherwise, go to Ecommerce Home (even for admins)
        return redirect()->intended(route('ecommerce.home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $redirectUrl = route('login');
        if (str_contains(url()->previous(), '/erp')) {
            $redirectUrl = route('erp.login');
        }

        return redirect($redirectUrl);
    }
}
