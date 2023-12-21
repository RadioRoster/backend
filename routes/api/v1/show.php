<?php

use App\Http\Controllers\ShowController;
use Illuminate\Support\Facades\Route;

Route::get('/shows', [ShowController::class, 'index'])->name('api.v1.shows.index');
Route::post('/shows', [ShowController::class, 'store'])->name('api.v1.shows.store');
Route::get('/shows/{show}', [ShowController::class, 'show'])->name('api.v1.shows.show');
Route::put('/shows/{show}', [ShowController::class, 'update'])->name('api.v1.shows.update');
Route::delete('/shows/{show}', [ShowController::class, 'destroy'])->name('api.v1.shows.destroy');

