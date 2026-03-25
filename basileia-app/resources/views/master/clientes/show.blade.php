@extends('layouts.app')
@section('title', 'Detalhes do Cliente')

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
    
    /* ===== Seleção de Status ===== */
    .status-select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-weight: 600; font-size: 0.9rem; background: #f8fafc; color: var(--text-main); outline: none; margin-top: 16px; transition: 0.2s; cursor: pointer; }
    .status-select:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(88,28,135,0.1); }
    .status-select.ativo { color: #15803d; border-color: #bbf7d0; background: #f0fdf4; }
    .status-select.inativo { color: #475569; border-color: #cbd5e1; background: #f8fafc; }
    .status-select.churn { color: #b91c1c; border-color: #fecaca; background: #fef2f2; }
    .status-select.inadimplente { color: #b45309; border-color: #fde68a; background: #fffbeb; }

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

    /* Toast */
    #toastMessage { position: fixed; bottom: 20px; right: 20px; background: #1f2937; color: white; padding: 12px 24px; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s; z-index: 9999; }
    #toastMessage.show { transform: translateY(0); opacity: 1; }
</style>

<div class="page-header animate-in">
    <div>
        <a href="{{ route('master.clientes') }}" style="text-decoration: none; color: var(--primary); font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px;">
            ⬅ Voltar para a lista
        </a>
        <h2 style="margin: 0;">{{ $cliente->nome_igreja ?? $cliente->nome }}</h2>
        <div class="subtitle">Visão 360º — Dados cadastrais, vendas e pagamentos</div>
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

        <select id="clienteStatus" class="status-select {{ $cliente->status ?? 'ativo' }}" onchange="updateStatus(this.value)">
            <option value="ativo" {{ ($cliente->status ?? 'ativo') == 'ativo' ? 'selected' : '' }}>🟢 Ativo</option>
            <option value="inativo" {{ $cliente->status == 'inativo' ? 'selected' : '' }}>⚪ Inativo</option>
            <option value="inadimplente" {{ $cliente->status == 'inadimplente' ? 'selected' : '' }}>🟠 Inadimplente</option>
            <option value="churn" {{ $cliente->status == 'churn' ? 'selected' : '' }}>🔴 Churn / Cancelado</option>
        </select>

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
                <span class="info-label">Cadastrado em</span>
                <span class="info-value">{{ $cliente->created_at->format('d/m/Y') }}</span>
            </div>
            @if($cliente->asaas_customer_id)
            <div class="info-item" style="margin-top: 16px; background: #f8fafc; padding: 12px; border-radius: 8px;">
                <span class="info-label">Integração Asaas</span>
                <span class="info-value" style="font-family: monospace; font-size: 0.8rem; word-break: break-all;">{{ $cliente->asaas_customer_id }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- ===== Main Panel (Históricos) ===== -->
    <div class="main-card animate-in">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('vendas', this)">
                🛍️ Histórico de Vendas ({{ $vendas->count() }})
            </button>
            <button class="tab-btn" onclick="switchTab('pagamentos', this)">
                💳 Faturas e Pagamentos ({{ $pagamentos->count() }})
            </button>
        </div>

        <!-- TAB: VENDAS -->
        <div id="tab-vendas" class="tab-content active">
            @if($vendas->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Venda</th>
                            <th>Data</th>
                            <th>Plano</th>
                            <th>Vendedor</th>
                            <th>Recorrência</th>
                            <th style="text-align: right;">Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendas as $v)
                        <tr>
                            <td style="font-weight: 700;">#{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td style="color: var(--text-muted);">{{ $v->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-weight: 600;">{{ $v->plano ?? 'Personalizado' }}</td>
                            <td>{{ $v->vendedor->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($v->tipo_negociacao ?? 'Mensal') }}</td>
                            <td style="text-align: right; font-weight: 700;">R$ {{ number_format($v->valor, 2, ',', '.') }}</td>
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
                <p>Este cliente ainda não possui histórico de vendas na plataforma.</p>
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
                            <th>Referência Venda</th>
                            <th style="text-align: right;">Valor</th>
                            <th>Status</th>
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
                            <td>Venda #{{ $p->venda_id }}</td>
                            <td style="text-align: right; font-weight: 700; color: var(--primary);">R$ {{ number_format($p->valor, 2, ',', '.') }}</td>
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
                <p>O cliente não possui faturas geradas associadas às suas vendas.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<div id="toastMessage">Status atualizado com sucesso!</div>

<script>
    // Sistema de abas
    function switchTab(tabId, btnElement) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        btnElement.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // Requisição AJAX para mudança de status do Cliente
    function updateStatus(newStatus) {
        const selectEl = document.getElementById('clienteStatus');
        
        fetch('{{ route('master.clientes.updateStatus', $cliente->id) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar classes visuais do Select
                selectEl.className = `status-select ${newStatus}`;
                
                // Mostrar Toast de Sucesso
                const toast = document.getElementById('toastMessage');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 3000);
            }
        })
        .catch(err => {
            alert('Erro ao atualizar o status do cliente.');
            console.error(err);
        });
    }
</script>

@endsection
