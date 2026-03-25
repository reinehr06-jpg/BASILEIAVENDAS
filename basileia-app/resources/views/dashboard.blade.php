@extends('layouts.app')

@section('title', 'Visão Geral da Operação')

@section('content')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
        <div class="card">
            <h3 style="font-size: 0.95rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Vendas Ativas</h3>
            <div style="font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: var(--primary);">{{ $vendasAtivas ?? 0 }}</div>
        </div>
        <div class="card">
            <h3 style="font-size: 0.95rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Vendedores Ativos</h3>
            <div style="font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: var(--text-main);">{{ $vendedores ?? 0 }}</div>
        </div>
        <div class="card">
            <h3 style="font-size: 0.95rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Comissões Pendentes</h3>
            <div style="font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: #10b981;">R$ {{ number_format($comissoesPendentes ?? 0, 2, ',', '.') }}</div>
        </div>
        @if(Auth::user()->perfil === 'master')
        <div class="card">
            <h3 style="font-size: 0.95rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Churn (Mês)</h3>
            <div style="font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: #ef4444;">{{ $churnMes ?? 0 }}</div>
        </div>
        <div class="card">
            <h3 style="font-size: 0.95rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Desistência (Mês)</h3>
            <div style="font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: #64748b;">{{ $desistenciasMes ?? 0 }}</div>
        </div>
        @endif
    </div>

    <!-- Espaço para gráficos ou mais conteúdo no futuro -->
    <div class="card" style="margin-top: 30px; min-height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
        <p>Acompanhamento de gráficos de receita e churn em construção.</p>
    </div>
@endsection

