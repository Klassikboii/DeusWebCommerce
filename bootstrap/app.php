<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->routeIs('store.*')) {
                return route('store.login');
            }
            return route('login');
            
        });
        // --- DAFTARKAN ALIAS DI SINI ---
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'feature' => \App\Http\Middleware\CheckPackageFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
