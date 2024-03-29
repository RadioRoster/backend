<?php

use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('api.v1.permissions.index');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('api.v1.permissions.show');
});
