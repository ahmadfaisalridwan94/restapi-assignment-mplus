<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::group(['prefix' => 'v1'], function () {
    Route::post('/auth/google', [AuthController::class, 'google']);
    Route::get('/oauth/google/callback', [AuthController::class, 'google_callback']);

    Route::post('/auth/facebook', [AuthController::class, 'facebook']);
    Route::get('/oauth/facebook/callback', [AuthController::class, 'facebook_callback']);

    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/token/refresh', [AuthController::class, 'refresh']);
});

Route::group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () {
    Route::get('/users/profile', [UserController::class, 'profile']);
    Route::put('/users/profile', [UserController::class, 'update']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // group admin
    Route::group(['prefix' => 'admin', 'middleware' => 'role:admin'], function () {
        Route::get('/users', function (Request $request) {
            return "get users";
        });
    });
});
