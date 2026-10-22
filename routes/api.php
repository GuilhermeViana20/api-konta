<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaixinhaController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\TransacaoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/caixinhas', [CaixinhaController::class, 'index']);

    Route::get('/transacoes', [TransacaoController::class, 'index']);
    Route::post('/transacoes', [TransacaoController::class, 'store']);
    Route::patch('/transacoes/{transacao}/pagar', [TransacaoController::class, 'pagar']);
});

Schedule::command('contas:gerar-recorrentes')->monthlyOn(1, '03:00');
