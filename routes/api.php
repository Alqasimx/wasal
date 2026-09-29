<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/otp/request', [AuthController::class, 'requestOtp']);

        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login/otp', [AuthController::class, 'loginOtp']);

        Route::post('/login/password', [AuthController::class, 'loginPassword']);

        Route::post('/password/reset', [AuthController::class, 'resetPassword']);

        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});