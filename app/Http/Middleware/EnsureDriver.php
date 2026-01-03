<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureDriver
{
    public function handle($request, Closure $next)
    {
        // делаем driver guard дефолтным в рамках запроса
        Auth::shouldUse('driver');

        $user = Auth::user();

        Log::info('EnsureDriver middleware', [
            'host'        => $request->getHost(),
            'path'        => $request->path(),
            'driver_guard'=> Auth::guard('driver')->check(),
            'driver_id'   => Auth::guard('driver')->id(),
            'default_guard_check' => Auth::check(),
            'user_id'     => $user?->id,
            'has_driver'  => (bool) $user?->driver,
            'session_id'  => session()->getId(),
        ]);

        if (!$user || !$user->driver) {
    Log::warning('EnsureDriver redirect to login', [
        'reason' => 'no user or no driver relation',
    ]);

    return redirect('/login');
}

        return $next($request);
    }
}
