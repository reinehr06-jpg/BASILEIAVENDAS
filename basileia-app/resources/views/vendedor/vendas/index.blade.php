@extends('layouts.app')
@section('title', 'Minhas Vendas')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .btn-primary { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(88, 28, 135, 0.2); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }

    .success-box { background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; border-left: 4px solid #22c55e; }
    .error-box { background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; border-left: 4px solid #ef4444; }

    .stats-bar { display: flex; gap: 16px; margin-bottom: 24px; }
    .stat-mini { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 16px 20px; flex: 1; text-align: center; }
    .stat-mini .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--primary); }
    .stat-mini .stat-label { font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-top: 4px; }

    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 14px 18px; border-bottom: 1px solid var(--border); }
    th { background: #f8fafc; font-weight: 600; color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    .status-badge { padding: 5px 12px; border-radius: 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .status-aguardando { background: #fef9c3; color: #854d0e; }
    .status-pago { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }
    .status-estornado { background: #fce7f3; color: #9d174d; }
    .status-expirado { background: #f1f5f9; color: #94a3b8; }

    .link-cobranca { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    .link-cobranca:hover { text-decoration: underline; }

    .btn-delete { background: none; border: 1px solid #fecaca; color: #991b1b; font-size: 0.8rem; cursor: pointer; padding: 5px 10px; border-radius: 6px; transition: 0.2s; }
    .btn-delete:hover { background: #fee2e2; }

    .empty-state { padding: 80px 20px; text-align: center; }
    .empty-state .icon { background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; }
    .empty-state h3 { color: var(--text-main); font-size: 1.25rem; font-weight: 600; margin-bottom: 8px; }
    .empty-state p { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px; }

    .expired-section { margin-top: 32px; }
    .expired-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; cursor: pointer; }
    .expired-header h3 { font-size: 1rem; color: var(--text-muted); font-weight: 600; }
    .expired-toggle { font-size: 0.85rem; color: var(--primary); font-weight: 600; cursor: pointer; background: none; border: none; }
    .expired-content { display: none; }
    .expired-content.show { display: block; }
    .expired-table tr { opacity: 0.6; }
    .expired-table tr:hover { opacity: 0.85; }

    .countdown-badge { font-size: 0.72rem; color: #dc2626; font-weight: 600; display: block; margin-top: 2px; }
</style>

<div class="page-header">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Minhas Vendas</h2>
    <a href="{{ route('vendedor.vendas.create') }}" class="btn-primary">+ Nova Venda</a>
</div>

@if(session('success'))
<div class="success-box">✅ {{ session('success') }}</div>
@endif

@if($errors->any())
<div class="error-box">❌ {{ $errors->first() }}</div>
@endif

<!-- Mini Stats -->
<div class="stats-bar">
    <div class="stat-mini">
        <div class="stat-value">{{ $vendas->count() }}</div>
        <div class="stat-label">Ativas</div>
    </div>
    <div class="stat-mini">
        <div class="stat-value">{{ $vendas->filter(fn($v) => in_array(trim(strtoupper($v->status)), ['PAGO', 'RECEIVED', 'CONFIRMED']))->count() }}</div>
        <div class="stat-label">Pagas</div>
    </div>
    <div class="stat-mini">
        <div class="stat-value">{{ $vendas->filter(fn($v) => in_array(trim(strtoupper($v->status)), ['AGUARDANDO PAGAMENTO', 'PENDING']))->count() }}</div>
        <div class="stat-label">Aguardando</div>
    </div>
    <div class="stat-mini">
        <div class="stat-value">R$ {{ number_format($vendas->filter(fn($v) => in_array(trim(strtoupper($v->status)), ['PAGO', 'RECEIVED', 'CONFIRMED']))->sum('valor'), 2, ',', '.') }}</div>
        <div class="stat-label">Receita</div>
    </div>
</div>

<div class="table-container">
    @if($vendas->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Igreja / Cliente</th>
                <th>Plano</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Cobrança</th>
                <th>Data</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-main);">{{ $venda->cliente?->nome_igreja ?? ($venda->cliente?->nome ?? '—') }}</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">{{ $venda->cliente?->nome_pastor ?? '' }}</div>
                </td>
                <td>
                    <span style="background: rgba(88,28,135,0.08); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.85rem;">{{ $venda->plano ?? 'N/A' }}</span>
                </td>
                <td style="font-weight: 700;">R$ {{ number_format($venda->valor, 2, ',', '.') }}</td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ ucfirst($venda->tipo_negociacao ?? 'mensal') }}</td>
                <td>
                    @php
                        $cleanStatus = trim(strtoupper($venda->status));
                        $statusClass = match($cleanStatus) {
                            'PAGO', 'RECEIVED', 'CONFIRMED' => 'status-pago',
                            'AGUARDANDO PAGAMENTO', 'PENDING' => 'status-aguardando',
                            'VENCIDO', 'OVERDUE' => 'status-vencido',
                            'CANCELADO', 'CANCELED' => 'status-cancelado',
                            'ESTORNADO', 'REFUNDED' => 'status-estornado',
                            'EXPIRADO' => 'status-expirado',
                            default => 'status-aguardando'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $venda->status }}</span>
                    @if($venda->status === 'Aguardando pagamento')
                        @php
                            $horasRestantes = max(0, 72 - now()->diffInHours($venda->created_at));
                        @endphp
                        @if($horasRestantes > 0)
                            <span class="countdown-badge">⏳ {{ $horasRestantes }}h restantes</span>
                        @endif
                    @endif
                </td>
                <td>
                    @php
                        $cobranca = $venda->cobrancas->first();
                        $pagamento = $venda->pagamentos->first();
                        $forma = strtolower($venda->forma_pagamento ?? $pagamento->forma_pagamento ?? '');
                        $hasCobranca = ($pagamento && ($pagamento->invoice_url || $pagamento->bank_slip_url || $pagamento->link_pagamento)) || ($cobranca && $cobranca->link);
                    @endphp
                    @if($hasCobranca)
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            @if($forma === 'boleto')
                                <a href="{{ route('vendedor.vendas.boleto.baixar', $venda->id) }}" class="btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0 14px; height: 34px; font-size: 0.78rem; background: #2563eb; width: fit-content; box-shadow: none; border-radius: 8px; text-decoration: none;">
                                    📄 Baixar Boleto
                                </a>
                            @else
                                @php
                                    $finalLink = $pagamento->link_pagamento ?? ($cobranca->link ?? null);
                                @endphp
                                @if($finalLink)
                                    <button onclick="copyToClipboard('{{ $finalLink }}')" class="btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0 14px; height: 34px; font-size: 0.78rem; background: #16a34a; width: fit-content; box-shadow: none; border-radius: 8px; border: none; cursor: pointer;">
                                        🔗 Copiar Link
                                    </button>
                                @else
                                    <a href="{{ route('vendedor.vendas.cobranca', $venda->id) }}" class="link-cobranca">📋 Ver Detalhes</a>
                                @endif
                            @endif
                        </div>
                    @else
                        <span style="font-size: 0.82rem; color: var(--text-muted);">—</span>
                    @endif
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $venda->data_venda ? $venda->data_venda->format('d/m/Y') : $venda->created_at->format('d/m/Y') }}</td>
                <td style="text-align: right;">
                    @if(!in_array(strtoupper($venda->status), ['PAGO', 'CANCELADO', 'EXPIRADO', 'ESTORNADO']))
                    <form method="POST" action="{{ route('vendedor.vendas.cancelar', $venda->id) }}" style="display: inline;" onsubmit="return confirm('Deseja cancelar esta venda? O registro será mantido no histórico.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete" title="Cancelar venda">🗑️ Cancelar</button>
                    </form>
                    @else
                        <span style="font-size: 0.78rem; color: var(--text-muted);">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="icon">📋</div>
        <h3>Nenhuma venda cadastrada até o momento.</h3>
        <p>Comece registrando sua primeira venda.</p>
        <a href="{{ route('vendedor.vendas.create') }}" class="btn-primary">+ Nova Venda</a>
    </div>
    @endif
</div>

<!-- Vendas Expiradas / Canceladas (histórico colapsável) -->
@if(isset($vendasExpiradas) && $vendasExpiradas->count() > 0)
<div class="expired-section">
    <div class="expired-header" onclick="toggleExpired()">
        <h3>📁 Histórico de Propostas Expiradas ({{ $vendasExpiradas->count() }})</h3>
        <button class="expired-toggle" id="toggleBtn">Expandir ▼</button>
    </div>
    <div class="expired-content" id="expiredContent">
        <div class="table-container">
            <table class="expired-table">
                <thead>
                    <tr>
                        <th>Igreja</th>
                        <th>Plano</th>
                        <th>Valor Proposto</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendasExpiradas as $vexp)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $vexp->cliente->nome_igreja ?? $vexp->cliente->nome ?? '—' }}</div>
                            <div style="font-size: 0.82rem; color: var(--text-muted);">{{ $vexp->cliente->nome_pastor ?? '' }}</div>
                        </td>
                        <td><span style="font-size: 0.85rem;">{{ $vexp->plano ?? 'N/A' }}</span></td>
                        <td style="font-weight: 600; color: var(--text-muted);">R$ {{ number_format($vexp->valor, 2, ',', '.') }}</td>
                        <td><span class="status-badge status-expirado">{{ $vexp->status }}</span></td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $vexp->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<script>
function toggleExpired() {
    const content = document.getElementById('expiredContent');
    const btn = document.getElementById('toggleBtn');
    content.classList.toggle('show');
    btn.textContent = content.classList.contains('show') ? 'Recolher ▲' : 'Expandir ▼';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Feedback visual
        const toast = document.createElement('div');
        toast.textContent = '✅ URL copiada para a área de transferência!';
        toast.style.cssText = 'position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #166534; color: white; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: fadeIn 0.3s ease;';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }).catch(() => {
        prompt('Copie a URL abaixo:', text);
    });
}
</script>
@endsection
