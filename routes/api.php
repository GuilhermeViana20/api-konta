<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PagamentoController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('pagamentos', PagamentoController::class)->except(['show']);

    Route::patch(
        'pagamentos/{pagamento}/pagar',
        [PagamentoController::class, 'marcarPaga']
    );
});

Schedule::command('contas:gerar-recorrentes')->monthlyOn(1, '03:00');
