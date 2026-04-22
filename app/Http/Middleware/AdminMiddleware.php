<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        if (Auth::user()->is_admin != 1 && Auth::user()->roles->isEmpty() && !Auth::user()->employee) {
            return redirect('login')->with('error', 'Access denied. You do not have ERP access privileges.');
        }

        return $next($request);
    }
}
