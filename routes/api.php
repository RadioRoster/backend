<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Defines the API routes for version 1.
 *
 * @group v1
 */
Route::group(['prefix' => 'v1'], function () {
    require __DIR__ . '/api/v1/auth.php';
    require __DIR__ . '/api/v1/user.php';
    require __DIR__ . '/api/v1/role.php';
    require __DIR__ . '/api/v1/show.php';
});
