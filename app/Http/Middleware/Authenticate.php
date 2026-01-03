<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        Log::info('Authenticate middleware BEFORE', [
            'host' => $request->getHost(),
            'path' => $request->path(),
            'guards' => $guards,
            'web_check' => auth('web')->check(),
            'driver_check' => auth('driver')->check(),
            'driver_id' => auth('driver')->id(),
            'session_id' => session()->getId(),
        ]);

        return parent::handle($request, $next, ...$guards);
    }

 protected function redirectTo($request): ?string
{
    if ($request->expectsJson()) return null;

    return '/login';
}
}