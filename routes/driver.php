<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Middleware\LogDriverRequests;

use App\Livewire\DriverApp\Login;
use App\Livewire\DriverApp\Dashboard;
use App\Livewire\DriverApp\TripDetails;
use App\Livewire\DriverApp\ViewDocument;

/*
|--------------------------------------------------------------------------
| STATIC FILES FOR PWA
|--------------------------------------------------------------------------
| Must be before any /driver/{...} routes
*/
Route::get('/driver/manifest.webmanifest', function () {
    return response()->file(public_path('driver/manifest.webmanifest'), [
        'Content-Type' => 'application/manifest+json',
    ]);
});

Route::get('/driver/icons/{filename}', function ($filename) {
    $path = public_path("driver/icons/{$filename}");
    abort_unless(is_file($path), 404);
    return response()->file($path);
})->where('filename', '^[A-Za-z0-9._-]+\.(png|svg|ico)$');

/*
|--------------------------------------------------------------------------
| LOGIN (public) - only for guests of DRIVER guard
|--------------------------------------------------------------------------
*/
Route::get('/driver/login', Login::class)
    ->middleware('guest:driver')
    ->name('driver.login');

/*
|--------------------------------------------------------------------------
| OFFLINE (public)
|--------------------------------------------------------------------------
*/
Route::view('/driver/offline', 'driver-app.offline')
    ->name('driver.offline');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED DRIVER APP (driver guard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:driver', LogDriverRequests::class])->group(function () {

    Route::get('/driver/dashboard', Dashboard::class)
        ->name('driver.dashboard');

    Route::get('/driver/trip/{trip}', TripDetails::class)
        ->name('driver.trip');

    Route::get('/driver/document/{document}', ViewDocument::class)
        ->name('driver.documents.view');

    Route::post('/driver/logout', function () {
        Auth::guard('driver')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/driver/login');
    })->name('driver.logout');
});
