<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'roles'], function () {
    Route::get('/', [RoleController::class, 'index'])->name('api.v1.roles.index');
    Route::post('/', [RoleController::class, 'store'])->name('api.v1.roles.store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('api.v1.roles.show');
    Route::put('/{role}', [RoleController::class, 'update'])->name('api.v1.roles.update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('api.v1.roles.destroy');
});
