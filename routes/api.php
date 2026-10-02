<?php

use App\Http\Controllers\Api\V1\PropertyFeatureController;
use App\Http\Controllers\Api\V1\PropertyListingController;
use App\Http\Controllers\Api\V1\PropertyTypeController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/property-types', [PropertyTypeController::class, 'index']);
    Route::get('/property-features', [PropertyFeatureController::class, 'index']);
    Route::get('/property-listings', [PropertyListingController::class, 'index']);
    Route::get('/property-listings/{listing}', [PropertyListingController::class, 'show']);
    Route::get('/shared-offers/{token}', [PropertyListingController::class, 'shared']);

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
