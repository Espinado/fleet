<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\DriverApp\Login;
use App\Livewire\DriverApp\Dashboard;
use App\Livewire\DriverApp\TripDetails;
use App\Livewire\DriverApp\Profile;
use App\Livewire\DriverApp\DriverStepDocumentUploader;
use App\Livewire\DriverApp\ViewDocument;


/*
|--------------------------------------------------------------------------
| STATIC FILES FOR PWA (must be before any /driver/{...} routes)
|--------------------------------------------------------------------------
*/

Route::get('/driver/manifest.webmanifest', function () {
    return response()->file(public_path('driver/manifest.webmanifest'), [
        'Content-Type' => 'application/manifest+json'
    ]);
});

Route::get('/driver/icons/{filename}', function ($filename) {
    $path = public_path("driver/icons/{$filename}");
    if (!file_exists($path)) abort(404);
    return response()->file($path);
});


/*
|--------------------------------------------------------------------------
| LOGIN (public)
|--------------------------------------------------------------------------
*/
Route::get('/driver/login', Login::class)->name('driver.login');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED DRIVER APP
|--------------------------------------------------------------------------
*/
Route::middleware(['driver'])->group(function () {

    Route::get('/driver/dashboard', Dashboard::class)
        ->name('driver.dashboard');

    Route::get('/driver/trip/{trip}', TripDetails::class)
        ->name('driver.trip');

    Route::get('/driver/document/{document}', ViewDocument::class)
        ->name('driver.documents.view');

    Route::post('/driver/logout', function () {
        Auth::logout();
        return redirect()->route('driver.login');
    })->name('driver.logout');
});
