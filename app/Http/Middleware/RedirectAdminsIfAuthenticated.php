<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectAdminsIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        /*
        |--------------------------------------------------------------------------
        | DRIVER SUBDOMAIN — НИЧЕГО НЕ ТРОГАЕМ
        |--------------------------------------------------------------------------
        | driver.fleet.test живёт своей жизнью
        | никаких редиректов, никаких guest-ограничений
        */
        if ($host === 'driver.fleet.test') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN DOMAIN (fleet.test)
        |--------------------------------------------------------------------------
        */

        // Проверяем ТОЛЬКО web guard
        if (!Auth::guard('web')->check()) {
            return $next($request);
        }

        // Админ залогинен — нельзя смотреть login / register
        if (
            $request->is('login') ||
            $request->is('register') ||
            $request->is('password/*')
        ) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
