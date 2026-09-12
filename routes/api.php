<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ModerationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
    });
    Route::middleware('throttle:120,1')->group(function () {
        Route::get('carts', [CartController::class, 'index']);
        Route::get('carts/{cart}', [CartController::class, 'show']);
        Route::get('carts/{cart}/updates', [CartController::class, 'updates']);
    });
    Route::middleware(['auth:sanctum', 'role:customer,owner,admin', 'throttle:120,1'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::patch('me', [AccountController::class, 'profile']);
        Route::post('reports', [AccountController::class, 'report'])->middleware('throttle:6,1');
        Route::patch('me/notifications/read-all', [AccountController::class, 'readAll']);
        Route::get('me', [AccountController::class, 'me']);
        Route::get('me/settings', [AccountController::class, 'settings']);
        Route::patch('me/settings', [AccountController::class, 'saveSettings']);
        Route::put('me/location', [AccountController::class, 'location']);
        Route::delete('me/location', [AccountController::class, 'forgetLocation']);
        Route::get('me/following', [AccountController::class, 'following']);
        Route::get('me/updates', [AccountController::class, 'updates']);
        Route::post('me/devices', [AccountController::class, 'device']);
        Route::delete('me/devices/{device}', [AccountController::class, 'deleteDevice']);
        Route::get('me/notifications', [AccountController::class, 'notifications']);
        Route::patch('me/notifications/{notification}/read', [AccountController::class, 'read']);
        Route::put('carts/{cart}/follow', [CartController::class, 'follow']);
        Route::delete('carts/{cart}/follow', [CartController::class, 'unfollow']);
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('users', [ModerationController::class, 'users']);
            Route::patch('users/{user}', [ModerationController::class, 'user']);
            Route::get('carts', [ModerationController::class, 'carts']);
            Route::patch('carts/{cart}', [ModerationController::class, 'cart']);
            Route::get('reports', [ModerationController::class, 'reports']);
            Route::patch('reports/{report}', [ModerationController::class, 'report']);
        });
        Route::prefix('owner')->middleware('role:owner,admin')->group(function () {
            Route::get('carts', [CartController::class, 'mine']);
            Route::post('carts', [CartController::class, 'store']);
            Route::patch('carts/{cart}', [CartController::class, 'update']);
            Route::delete('carts/{cart}', [CartController::class, 'destroy']);
            Route::put('carts/{cart}/location', [CartController::class, 'location']);
            Route::post('carts/{cart}/updates', [CartController::class, 'postUpdate']);
            Route::put('carts/{cart}/schedules', [CartController::class, 'schedule']);
            Route::delete('carts/{cart}/schedules/{schedule}', [CartController::class, 'deleteSchedule']);
            Route::post('carts/{cart}/photos', [CartController::class, 'photo'])->middleware('throttle:10,1');
            Route::delete('carts/{cart}/photos/{photo}', [CartController::class, 'deletePhoto']);
        });
    });
});
