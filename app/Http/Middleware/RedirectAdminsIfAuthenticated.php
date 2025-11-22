<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectAdminsIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // НЕ залогинен — всё ок, пускаем
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | ЛОГИКА ДЛЯ ВОДИТЕЛЯ
        |--------------------------------------------------------------------------
        */
        if ($user->driver) {

            // Водителю нельзя показывать админский login/register
            if ($request->is('login') || $request->is('register') || $request->is('password/*')) {
                return redirect()->route('driver.dashboard');
            }

            // Водитель может открывать свои маршруты
            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | ЛОГИКА ДЛЯ АДМИНА/МЕНЕДЖЕРА
        |--------------------------------------------------------------------------
        */
        // Админу нельзя открывать водительские URL
        if ($request->is('driver/*')) {
            return redirect()->route('dashboard');
        }

        // Админу нельзя смотреть login/register будучи залогиненным
        if ($request->is('login') || $request->is('register') || $request->is('password/*')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
