<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureDriver
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // водитель = пользователь, у которого есть driver-модель
        if (!$user || !$user->driver) {
            return redirect()->route('driver.login');
        }

        return $next($request);
    }
}
