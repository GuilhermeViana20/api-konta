<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Schedule::command('contas:gerar-recorrentes')->monthlyOn(1, '03:00');
