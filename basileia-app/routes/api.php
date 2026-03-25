<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\VendaCobrancaController;

// ==========================================
// Rotas Públicas Integracoes
// ==========================================

// Asaas Webhook — Receber eventos de pagamento
Route::post('/asaas/webhook', [AsaasWebhookController::class, 'handle']);

// Endpoint de Criação de Cobrança (Wizard)
Route::post('/vendas/criar-cobranca', [VendaCobrancaController::class, 'createBilling']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
