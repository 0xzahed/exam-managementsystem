<?php

use Illuminate\Support\Facades\Route;

// Test route to check Google OAuth configuration
Route::get('/test-google-config', function () {
    return response()->json([
        'client_id' => config('services.google.client_id'),
        'redirect_url' => config('services.google.redirect'),
        'expected_callback' => route('auth.google.callback'),
    ]);
});
