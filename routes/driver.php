<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\DriverApp\Login;
use App\Livewire\DriverApp\Dashboard;
use App\Livewire\DriverApp\TripDetails;
use App\Livewire\DriverApp\Profile;

use App\Livewire\DriverApp\DriverStepDocumentUploader;
use App\Livewire\DriverApp\ViewDocument;

// ==========================
// ЛОГИН БЕЗ MIDDLEWARE
// ==========================
Route::get('/driver/login', Login::class)
    ->name('driver.login');

// ==========================
// ВСЁ ДЛЯ АВТОРИЗОВАННЫХ ВОДИТЕЛЕЙ
// ==========================
Route::middleware(['driver'])->group(function () {

    // Dashboard
    Route::get('/driver/dashboard', Dashboard::class)
        ->name('driver.dashboard');

    // Trip details
    Route::get('/driver/trip/{trip}', TripDetails::class)
        ->name('driver.trip');

    // Единая загрузка документа
   
    // Просмотр документа
    Route::get('/driver/document/{document}', ViewDocument::class)
        ->name('driver.documents.view');

    // Logout
    Route::post('/driver/logout', function () {
        Auth::logout();
        return redirect()->route('driver.login');
    })->name('driver.logout');
});
