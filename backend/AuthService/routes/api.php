<?php

use App\Http\Middleware\JwtMiddleware; 
use App\Http\Controllers\AuthController; 
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});
