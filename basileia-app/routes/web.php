<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterPanelController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PagamentoBoletoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\Master\IntegracaoController;
use App\Http\Middleware\CheckMaster;
use App\Http\Middleware\CheckVendedor;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/Login', function() { return redirect('/login'); });
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    
    // Fallback inteligente: Quem acessar apenas /dashboard será jogado para seu respectivo painel
    Route::get('/dashboard', function () {
        if (Auth::user()->perfil === 'master') {
            return redirect()->route('master.dashboard');
        }
        return redirect()->route('vendedor.dashboard');
    })->name('dashboard');

    // API interna: buscar planos por quantidade de membros
    Route::get('/api/planos', [VendaController::class, 'buscarPlanos'])->name('api.planos');

    // ==========================================
    // Módulo Master
    // ==========================================
    Route::middleware(CheckMaster::class)->prefix('master')->name('master.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/vendedores', [MasterPanelController::class, 'vendedores'])->name('vendedores');
        Route::post('/vendedores', [MasterPanelController::class, 'storeVendedor'])->name('vendedores.store');
        Route::put('/vendedores/{id}', [MasterPanelController::class, 'updateVendedor'])->name('vendedores.update');
        Route::patch('/vendedores/{id}/toggle', [MasterPanelController::class, 'toggleVendedor'])->name('vendedores.toggle');
        Route::get('/vendas', [VendaController::class, 'indexMaster'])->name('vendas');
        Route::delete('/vendas/{id}', [VendaController::class, 'cancelarMaster'])->name('vendas.cancelar');
        Route::get('/pagamentos', [PagamentoController::class, 'indexMaster'])->name('pagamentos');
        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios');
        Route::get('/relatorios/exportar', [RelatorioController::class, 'exportar'])->name('relatorios.exportar');

        // Endpoints de API para Relatórios
        Route::prefix('api/relatorios')->name('relatorios.api.')->group(function () {
            Route::get('/resumo', [RelatorioController::class, 'apiResumo'])->name('resumo');
            Route::get('/vendas-por-vendedor', [RelatorioController::class, 'apiVendasPorVendedor'])->name('vendas_vendedor');
            Route::get('/pagamentos', [RelatorioController::class, 'apiPagamentos'])->name('pagamentos');
            Route::get('/churn-renovacoes', [RelatorioController::class, 'apiChurnRenovacoes'])->name('churn_renovacoes');
            Route::get('/formas-pagamento', [RelatorioController::class, 'apiFormasPagamento'])->name('formas_pagamento');
        });

        Route::get('/metas', [MetaController::class, 'index'])->name('metas');
        Route::post('/metas', [MetaController::class, 'store'])->name('metas.store');
        Route::put('/metas/{id}', [MetaController::class, 'update'])->name('metas.update');
        Route::delete('/metas/{id}', [MetaController::class, 'destroy'])->name('metas.destroy');

        // Endpoints de API para Metas
        Route::prefix('api/metas')->name('metas.api.')->group(function () {
            Route::get('/', [MetaController::class, 'apiListar'])->name('index');
            Route::get('/resumo', [MetaController::class, 'apiResumo'])->name('resumo');
            Route::post('/', [MetaController::class, 'apiStore'])->name('store');
            Route::put('/{id}', [MetaController::class, 'apiUpdate'])->name('update');
        });

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::patch('/clientes/{id}/status', [ClienteController::class, 'updateStatus'])->name('clientes.updateStatus');

        Route::get('/comissoes', [ComissaoController::class, 'indexMaster'])->name('comissoes');
        Route::get('/comissoes/exportar', [ComissaoController::class, 'exportar'])->name('comissoes.exportar');
        Route::prefix('api/comissoes')->name('comissoes.api.')->group(function () {
            Route::get('/', [ComissaoController::class, 'apiListar'])->name('index');
            Route::get('/resumo', [ComissaoController::class, 'apiResumo'])->name('resumo');
        });

        Route::get('/configuracoes', [MasterPanelController::class, 'configuracoes'])->name('configuracoes');
        
        // Configurações de Integrações (Asaas)
        Route::get('/configuracoes/integracoes', [IntegracaoController::class, 'index'])->name('configuracoes.integracoes');
        Route::post('/configuracoes/integracoes', [IntegracaoController::class, 'update'])->name('configuracoes.integracoes.update');
    });

    // ==========================================
    // Módulo Vendedor
    // ==========================================
    Route::middleware(CheckVendedor::class)->prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/vendas', [VendaController::class, 'index'])->name('vendas');
        Route::get('/vendas/nova', [VendaController::class, 'create'])->name('vendas.create');
        Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');
        Route::get('/vendas/{id}/boleto', [PagamentoBoletoController::class, 'download'])->name('vendas.boleto');
        Route::get('/vendas/{id}/boleto/baixar', [PagamentoBoletoController::class, 'forceDownload'])->name('vendas.boleto.baixar');
        Route::get('/vendas/{id}/cobranca', [VendaController::class, 'cobranca'])->name('vendas.cobranca');
        Route::delete('/vendas/{id}', [VendaController::class, 'cancelar'])->name('vendas.cancelar');

        Route::get('/pagamentos', [PagamentoController::class, 'indexVendedor'])->name('pagamentos');
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');
        Route::get('/clientes/{id}', [ClienteController::class, 'show'])->name('clientes.show');
        Route::get('/comissoes', [ComissaoController::class, 'index'])->name('comissoes');
        Route::get('/comissoes/exportar', [ComissaoController::class, 'exportar'])->name('comissoes.exportar');
        Route::get('/comissao', function() { return redirect()->route('vendedor.comissoes'); })->name('comissao');
    });

});

Route::post('/webhook/saque', function () {
    return response()->json(['authorized' => true]);
});
