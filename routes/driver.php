<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\DriverApp\Login;
use App\Livewire\DriverApp\Dashboard;
use App\Livewire\DriverApp\TripDetails;
use App\Livewire\DriverApp\ViewDocument;

Route::domain('driver.fleet.test')->group(function () {

    Route::get('/ping', function () {
    \Log::info('PING HIT', [
        'host' => request()->getHost(),
        'path' => request()->path(),
        'session' => session()->getId(),
    ]);
    return 'pong';
});

    // =========================
    // Debug (local only)
    // =========================
    Route::get('/_whoami', function () {
        abort_unless(app()->isLocal() || config('app.debug'), 404);

        return [
            'web' => auth('web')->check(),
            'driver' => auth('driver')->check(),
            'driver_id' => auth('driver')->id(),
            'session' => session()->getId(),
            'host' => request()->getHost(),
        ];
    });

    // =========================
    // PWA static (root)
    // =========================
    Route::get('/serviceworker.js', function () {
        return response()->file(public_path('driver/serviceworker.js'), [
            'Content-Type'  => 'application/javascript',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    });

    Route::get('/manifest.webmanifest', function () {
        return response()->file(public_path('driver/manifest.webmanifest'), [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    });

    // --- PUBLIC ---
    Route::get('/login', Login::class)->name('driver.login');
    Route::view('/offline', 'driver-app.offline')->name('driver.offline');

    // --- AUTH ---

    Route::get('/dashboard-test', function () {
    \Log::info('DASHBOARD-TEST HIT', [
        'host' => request()->getHost(),
        'session' => session()->getId(),
        'driver' => auth('driver')->check(),
        'driver_id' => auth('driver')->id(),
    ]);
    return 'dashboard-test';
});
   Route::middleware(['auth:driver', 'driver'])->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('driver.dashboard');
        Route::get('/trip/{trip}', TripDetails::class)->name('driver.trip');
        Route::get('/document/{document}', ViewDocument::class)->name('driver.documents.view');

        Route::post('/logout', function () {
            Auth::guard('driver')->logout();

            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('driver.login');
        })->name('driver.logout');
    });
});
