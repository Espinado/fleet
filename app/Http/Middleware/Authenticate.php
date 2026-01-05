<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
   protected function redirectTo($request): ?string
{
    // если это driver-домен или driver-урлы — на driver login
    if ($request->is('driver/*') || str_starts_with($request->getHost(), 'driver.')) {
        return '/driver/login';
    }

    return '/login';
}
}
