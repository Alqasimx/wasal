<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('auth')->group(function () {
    Route::post('/otp/request', [AuthController::class, 'requestOtp']);

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login/otp', [AuthController::class, 'loginOtp']);

    Route::post('/login/password', [AuthController::class, 'loginPassword']);

    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth');
});