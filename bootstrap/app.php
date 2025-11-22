<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/driver.php',
        ],
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'guest'    => \App\Http\Middleware\RedirectAdminsIfAuthenticated::class,
            'driver'   => \App\Http\Middleware\EnsureDriver::class,
        ]);
    })
    ->withProviders([
        NotificationChannels\WebPush\WebPushServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {})
    ->create();
