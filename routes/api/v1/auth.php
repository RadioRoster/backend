<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::group(['prefix' => 'reset_password'], function () {
    Route::post('/', [PasswordResetController::class, 'sendLink'])->name('api.v1.reset-password.email');
    Route::post('/{token}', [PasswordResetController::class, 'reset'])->name('api.v1.reset-password.reset');
});
