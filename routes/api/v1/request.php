<?php

use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;


Route::post('/requests', [RequestController::class, 'store'])->name('api.v1.requests.store');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/requests', [RequestController::class, 'index'])->name('api.v1.requests.index');
    Route::get('/requests/{request}', [RequestController::class, 'show'])->name('api.v1.requests.show');
    Route::delete('/requests/{request}', [RequestController::class, 'destroy'])->name('api.v1.requests.destroy');
});
