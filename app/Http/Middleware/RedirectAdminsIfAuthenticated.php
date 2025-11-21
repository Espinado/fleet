<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectAdminsIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::check()) {

            // Если водитель заходит на свои маршруты — ничего ему не мешаем
            if ($request->is('driver/*')) {
                return $next($request);
            }

            // Админа/менеджера перенаправляем на dashboard
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
