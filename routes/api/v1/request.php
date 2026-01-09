<?php

use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'requests'], function () {
    Route::post('/', [RequestController::class, 'store'])->name('api.v1.requests.store');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('/', [RequestController::class, 'index'])->name('api.v1.requests.index');
        Route::get('/{request}', [RequestController::class, 'show'])->name('api.v1.requests.show');
        Route::delete('/{request}', [RequestController::class, 'destroy'])->name('api.v1.requests.destroy');
    });
});
