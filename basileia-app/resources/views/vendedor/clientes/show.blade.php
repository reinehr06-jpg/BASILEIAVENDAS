@extends('layouts.app')
@section('title', 'Histórico do Cliente')

@section('content')
<style>
    /* ===== Animações ===== */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }

    /* ===== Layout Grid Direita/Esquerda ===== */
    .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 24px; margin-top: 24px; align-items: start; }
    @media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } }

    /* ===== Card Lateral ===== */
    .sidebar-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 24px; text-align: center; }
    .profile-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #e0e7ff, #f3e8ff); color: var(--primary); font-size: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(88,28,135,0.1); }
    .sidebar-card h3 { font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
    .sidebar-card .sub-info { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; }
    .info-list { text-align: left; border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px; }
    .info-item { margin-bottom: 12px; display: flex; flex-direction: column; gap: 2px; }
    .info-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
    
    /* ===== Status Estático para Vendedor ===== */
    .status-badge-large { display: inline-block; width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-weight: 600; font-size: 0.9rem; margin-top: 16px; text-transform: uppercase; letter-spacing: 1px; }
    .status-badge-large.ativo { color: #15803d; border-color: #bbf7d0; background: #f0fdf4; }
    .status-badge-large.inativo { color: #475569; border-color: #cbd5e1; background: #f8fafc; }
    .status-badge-large.churn { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .status-badge-large.inadimplente { color: #b45309; border-color: #fde68a; background: #fffbeb; }

    /* ===== Main Área & Tabs ===== */
    .main-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
    .tabs-header { display: flex; background: #f8fafc; border-bottom: 1px solid var(--border); }
    .tab-btn { flex: 1; padding: 16px; background: none; border: none; border-bottom: 3px solid transparent; font-weight: 700; color: var(--text-muted); cursor: pointer; font-size: 0.95rem; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .tab-btn:hover { color: var(--primary); background: white; }
    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); background: white; }
    .tab-content { display: none; padding: 0; }
    .tab-content.active { display: block; animation: fadeInUp 0.3s ease-out both; }

    /* ===== Tabelas ===== */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: white; padding: 16px 20px; font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); white-space: nowrap; }
    td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-success { background: #dcfce7; color: #15803d; }
    .badge-warning { background: #fef3c7; color: #b45309; }
    .badge-danger { background: #fee2e2; color: #b91c1c; }
    .badge-neutral { background: #f1f5f9; color: #475569; }
</style>

<div class="page-header animate-in">
    <div>
        <a href="{{ route('vendedor.clientes') }}" style="text-decoration: none; color: var(--primary); font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px;">
            ⬅ Voltar para a base
        </a>
        <h2 style="margin: 0;">{{ $cliente->nome_igreja ?? $cliente->nome }}</h2>
        <div class="subtitle">Visão 360º — Histórico Comercial da Igreja/Entidade</div>
    </div>
</div>

<div class="profile-grid">
    <!-- ===== Sidebar (Dados Cadastrais) ===== -->
    <div class="sidebar-card animate-in">
        <div class="profile-icon">
            {{ strtoupper(substr($cliente->nome_igreja ?? $cliente->nome, 0, 1)) }}
        </div>
        <h3>{{ $cliente->nome_igreja ?? $cliente->nome }}</h3>
        <p class="sub-info">{{ $cliente->localidade ?? 'Localidade não informada' }}</p>

        <!-- Status travado (vendedor não muda status do cliente na unha) -->
        <div class="status-badge-large {{ $cliente->status ?? 'ativo' }}">
            {{ $cliente->status ?? 'ativo' }}
        </div>

        <div class="info-list">
            <div class="info-item">
                <span class="info-label">Pastor / Responsável</span>
                <span class="info-value">{{ $cliente->nome_pastor ?? $cliente->nome_responsavel ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento (CPF/CNPJ)</span>
                <span class="info-value">{{ $cliente->documento ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Contato / WhatsApp</span>
                <span class="info-value">{{ $cliente->contato ?? $cliente->whatsapp ?? $cliente->telefone ?? 'Não informado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Quantidade de Membros</span>
                <span class="info-value">{{ $cliente->quantidade_membros ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Na base desde</span>
                <span class="info-value">{{ $cliente->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- ===== Main Panel (Históricos) ===== -->
    <div class="main-card animate-in">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('vendas', this)">
                🛍️ Meu Histórico de Vendas ({{ $vendas->count() }})
            </button>
            <button class="tab-btn" onclick="switchTab('pagamentos', this)">
                💳 Faturas Associadas ({{ $pagamentos->count() }})
            </button>
        </div>

        <!-- TAB: VENDAS -->
        <div id="tab-vendas" class="tab-content active">
            @if($vendas->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Plano</th>
                            <th>Recorrência</th>
                            <th style="text-align: right;">Valor</th>
                            <th>Status Comercial</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendas as $v)
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 600;">{{ $v->created_at->format('d/m/Y') }}</td>
                            <td style="font-weight: 700;">{{ $v->plano ?? 'Personalizado' }}</td>
                            <td>{{ ucfirst($v->tipo_negociacao ?? 'Mensal') }}</td>
                            <td style="text-align: right; font-weight: 700; color: var(--primary);">R$ {{ number_format($v->valor, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $vStatus = strtolower($v->status);
                                    $badgeClass = 'badge-neutral';
                                    if ($vStatus == 'pago') $badgeClass = 'badge-success';
                                    if ($vStatus == 'aguardando pagamento') $badgeClass = 'badge-warning';
                                    if ($vStatus == 'cancelado' || $vStatus == 'vencido') $badgeClass = 'badge-danger';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $v->status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 10px;">🛍️</div>
                <h3 style="color: var(--text-main); font-size: 1.1rem;">Nenhuma venda registrada</h3>
                <p>Você ainda não fechou vendas com este cliente.</p>
            </div>
            @endif
        </div>

        <!-- TAB: PAGAMENTOS -->
        <div id="tab-pagamentos" class="tab-content">
            @if($pagamentos->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Vencimento</th>
                            <th>Forma de Pgto</th>
                            <th style="text-align: right;">Valor</th>
                            <th>Status do Pgto</th>
                            <th style="text-align: right;">Data Pgto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagamentos as $p)
                        <tr>
                            <td style="font-weight: 700;">{{ \Carbon\Carbon::parse($p->data_vencimento)->format('d/m/Y') }}</td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; text-transform: uppercase; font-size: 0.78rem;">
                                    @if(strtolower($p->forma_pagamento) == 'pix') ⚡ PIX
                                    @elseif(strtolower($p->forma_pagamento) == 'boleto') 📄 Boleto
                                    @elseif(strtolower($p->forma_pagamento) == 'cartão') 💳 Cartão
                                    @else 💳 {{ $p->forma_pagamento }}
                                    @endif
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--text-main);">R$ {{ number_format($p->valor, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $pStatus = strtolower($p->status);
                                    $pClass = 'badge-neutral';
                                    if ($pStatus == 'pago') $pClass = 'badge-success';
                                    if ($pStatus == 'pendente') $pClass = 'badge-warning';
                                    if ($pStatus == 'vencido' || $pStatus == 'estornado') $pClass = 'badge-danger';
                                @endphp
                                <span class="badge {{ $pClass }}">{{ $p->status }}</span>
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: var(--text-muted);">
                                {{ $p->data_pagamento ? \Carbon\Carbon::parse($p->data_pagamento)->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 10px;">💳</div>
                <h3 style="color: var(--text-main); font-size: 1.1rem;">Sem faturas</h3>
                <p>Nenhuma fatura foi gerada ainda para suas vendas com este cliente.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Sistema de abas simples
    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        btnElement.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
</script>

@endsection
