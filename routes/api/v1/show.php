<?php

use App\Http\Controllers\ShowController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'shows', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [ShowController::class, 'index'])->name('api.v1.shows.index');
    Route::post('/', [ShowController::class, 'store'])->name('api.v1.shows.store');
    Route::get('/{show}', [ShowController::class, 'show'])->name('api.v1.shows.show');
    Route::put('/{show}', [ShowController::class, 'update'])->name('api.v1.shows.update');
    Route::delete('/{show}', [ShowController::class, 'destroy'])->name('api.v1.shows.destroy');
});
