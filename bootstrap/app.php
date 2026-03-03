<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Mail;
use App\Mail\ErrorReport;

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
    ->withExceptions(function (Exceptions $exceptions) {
        // Отправка всех ошибок на rvr@arguss.lv, включая ошибки валидации
        $exceptions->reportable(function (\Throwable $e) {
            try {
                Mail::to('rvr@arguss.lv')->send(new ErrorReport($e));
            } catch (\Throwable $mailException) {
                report($mailException);
            }
        });

        // ValidationException по умолчанию не вызывают report() — принудительно отправить в отчёт (и на email)
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e) {
            report($e);
        });
    })
    ->create();
