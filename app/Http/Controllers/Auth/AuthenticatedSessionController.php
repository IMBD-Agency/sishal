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

        // If explicitly coming from ERP login, go to Dashboard
        if ($request->login_source === 'erp') {
            return redirect()->intended(route('erp.dashboard', absolute: false));
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
