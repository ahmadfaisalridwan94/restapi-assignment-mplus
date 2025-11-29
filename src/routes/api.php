<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

//ADMIN
use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'v1'], function () {
    Route::post('/auth/google', [AuthController::class, 'google']);
    Route::get('/auth/google/url', [AuthController::class, 'generateGoogleAuthUrl']);

    Route::post('/auth/facebook', [AuthController::class, 'facebook']);
    Route::get('/auth/facebook/url', [AuthController::class, 'generateFacebookAuthUrl']);

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
        Route::get('/users', [AdminUserController::class, 'getAllPaginated']);
    });
});
