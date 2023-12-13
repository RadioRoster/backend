<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/users', [UserController::class, 'index'])->name('api.v1.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('api.v1.users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('api.v1.users.show');
    Route::patch('/users/{id}', [UserController::class, 'update'])->name('api.v1.users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('api.v1.users.destroy');
});
