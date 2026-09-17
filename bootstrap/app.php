<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->redirectGuestsTo(fn ($request) => (
            ($request->is('erp') || $request->is('erp/*')) 
                ? route('erp.login') 
                : route('login')
        ));
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            return back()->withErrors(['image' => 'The uploaded file is too large. Please upload a smaller file. (Max limit usually around 2-8MB depending on server config)']);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
            if (config('app.debug')) {
                return null; // Allows Laravel Ignition to show full debug info in local development
            }
            return response()->view('errors.405', ['exception' => $e], 405);
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Process queued jobs every minute
        $schedule->command('queue:work --stop-when-empty --tries=3')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Prune expired database cache entries to prevent bloat
        $schedule->command('cache:prune')->hourly();
    })->create();




