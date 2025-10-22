<?php

use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'permissions'], function () {
    Route::get('/', [PermissionController::class, 'index'])->name('api.v1.permissions.index');
    Route::get('/{permission}', [PermissionController::class, 'show'])->name('api.v1.permissions.show');
});
