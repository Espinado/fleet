<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->post('/push/subscribe', function (Request $request) {
    $user = $request->user();

    $data = $request->validate([
        'endpoint' => 'required|string',
        'public_key' => 'required|string',
        'auth_token' => 'required|string',
        'content_encoding' => 'required|string',
    ]);

    $user->updatePushSubscription(
        $data['endpoint'],
        $data['public_key'],
        $data['auth_token'],
        $data['content_encoding']
    );

    return ['status' => 'subscribed'];
});
