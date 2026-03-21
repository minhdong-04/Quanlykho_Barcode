<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// API-style auth endpoints for SPA (use web middleware so session/CSRF work)
Route::post('/api/v1/auth/login', [AuthController::class, 'login']);
Route::post('/api/v1/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/', function () {
    return view('welcome');
});
