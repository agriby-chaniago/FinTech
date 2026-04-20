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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.key' => \App\Http\Middleware\ValidateApiKey::class,
            'hybrid.api_auth' => \App\Http\Middleware\HybridApiAuthMiddleware::class,
            'hybrid.web_auth' => \App\Http\Middleware\HybridWebAuthMiddleware::class,
            'auth.principal' => \App\Http\Middleware\RequireAuthenticatedUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
