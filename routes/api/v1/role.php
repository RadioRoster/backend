<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('api.v1.roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('api.v1.roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('api.v1.roles.show');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('api.v1.roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('api.v1.roles.destroy');
});
