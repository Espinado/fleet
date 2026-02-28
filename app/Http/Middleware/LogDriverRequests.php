<?php

// app/Http/Middleware/LogDriverRequests.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\DriverEventLogger;

class LogDriverRequests
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $ms = (int) round((microtime(true) - $start) * 1000);

        // исключаем шум
        $path = $request->path();
        if (
            str_starts_with($path, 'livewire') ||
            str_starts_with($path, 'storage') ||
            str_starts_with($path, 'build') ||
            str_contains($path, 'service-worker') ||
            str_contains($path, 'manifest')
        ) {
            return $response;
        }

        DriverEventLogger::log(
            channel: 'http',
            event: 'request',
            name: $request->route()?->getName(),
            meta: [
                'query' => $request->query(),
                // body логируем только для POST/PATCH и без файлов
                'input' => $request->isMethod('get') ? null : $request->except(['password','password_confirmation','_token','files','file']),
            ],
            tripId: null,
            statusCode: $response->getStatusCode(),
            durationMs: $ms
        );

        return $response;
    }
}
