<?php

use App\Http\Responses\ApiErrorResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return new ApiErrorResponse('Resource not found', status: Response::HTTP_NOT_FOUND);
});
