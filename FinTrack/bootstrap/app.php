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
        $middleware->alias([
            'service.api_key' => \App\Http\Middleware\ValidateServiceApiKey::class,
            'hybrid.api_auth' => \App\Http\Middleware\HybridApiAuthMiddleware::class,
            'hybrid.web_auth' => \App\Http\Middleware\HybridWebAuthMiddleware::class,
            'auth.principal' => \App\Http\Middleware\RequireAuthenticatedUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
