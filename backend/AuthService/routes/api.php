<?php

use App\Http\Middleware\JwtMiddleware; 
use App\Http\Controllers\AuthController; 
use Illuminate\Http\Request;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
});
