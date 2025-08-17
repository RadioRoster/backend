<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'users'], function () {
    Route::get('/', [UserController::class, 'index'])->name('api.v1.users.index');
    Route::post('/', [UserController::class, 'store'])->name('api.v1.users.store');
    Route::get('/{user}', [UserController::class, 'show'])->name('api.v1.users.show');
    Route::put('/{user}', [UserController::class, 'update'])->name('api.v1.users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('api.v1.users.destroy');
});
