<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADMIN AUTH ROUTES (Breeze) — ONLY fleet.test
|--------------------------------------------------------------------------
*/

Route::domain('fleet.test')->group(function () {
    require __DIR__ . '/auth.php';
});
