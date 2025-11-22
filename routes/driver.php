<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\DriverApp\Login;
use App\Livewire\DriverApp\Dashboard;
use App\Livewire\DriverApp\TripDetails as TripView;
use App\Livewire\DriverApp\Profile;

use App\Livewire\DriverApp\UploadDocument;
use App\Livewire\DriverApp\ViewDocument;

// === ЛОГИН ВОДИТЕЛЯ (без middleware) ===
Route::get('/driver/login', Login::class)
    ->name('driver.login');

// === ВСЁ ОСТАЛЬНОЕ — ТОЛЬКО ДЛЯ ВОДИТЕЛЕЙ ===
Route::middleware(['driver'])->group(function () {

    Route::get('/driver/dashboard', Dashboard::class)
        ->name('driver.dashboard');

    Route::get('/driver/trip/{trip}', TripView::class)
        ->name('driver.trip');

    Route::get('/driver/profile', Profile::class)
        ->name('driver.profile');

    // ===== ЗАГРУЗКА ДОКУМЕНТОВ =====
    Route::get('/driver/trip/{trip}/step/{step}/upload/{type}',
        UploadDocument::class
    )->name('driver.documents.upload');

    // ===== ПРОСМОТР ДОКУМЕНТА =====
    Route::get('/driver/document/{document}',
        ViewDocument::class
    )->name('driver.documents.view');

    // ===== LOGOUT =====
    Route::post('/driver/logout', function () {
        Auth::logout();
        return redirect()->route('driver.login');
    })->name('driver.logout');
});
