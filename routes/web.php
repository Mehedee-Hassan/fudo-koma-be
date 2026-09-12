<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::view('/login', 'admin.login')->name('login');
Route::post('/login', [AdminController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [AdminController::class, 'logout'])->middleware('auth');
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/configuration', [AdminController::class, 'settings']);
    Route::post('/configuration', [AdminController::class, 'saveSettings']);
    Route::post('/carts/{cart}/photos', [AdminController::class, 'upload']);
    Route::post('/deliveries/{delivery}/retry', [AdminController::class, 'retry']);
    Route::get('/{resource}', [AdminController::class, 'index']);
    Route::get('/{resource}/create', [AdminController::class, 'create']);
    Route::post('/{resource}', [AdminController::class, 'save']);
    Route::get('/{resource}/{id}/edit', [AdminController::class, 'edit'])->whereNumber('id');
    Route::put('/{resource}/{id}', [AdminController::class, 'save'])->whereNumber('id');
    Route::delete('/{resource}/{id}', [AdminController::class, 'delete'])->whereNumber('id');
});
