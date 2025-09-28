<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'NeibrPay Laravel API is running',
        'status' => 'ok',
        'timestamp' => now(),
        'version' => app()->version()
    ]);
});
