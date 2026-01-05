<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureDriver
{
    public function handle($request, Closure $next)
    {
      $user = Auth::guard('driver')->user();

$user = Auth::guard('driver')->user();

if (!$user || !$user->driver) {
    return redirect('/driver/login');
}
        return $next($request);
    }
}
