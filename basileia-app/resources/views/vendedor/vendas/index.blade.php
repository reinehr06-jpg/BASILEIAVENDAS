@extends('layouts.app')
@section('title', 'Minhas Vendas')

@section('content')
<style>
    .status-aguardando { background: #fef9c3; color: #854d0e; }
    .status-pago { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }
    .status-estornado { background: #fce7f3; color: #9d174d; }
    .status-expirado { background: #f1f5f9; color: #94a3b8; }
    .link-cobranca { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    .link-cobranca:hover { text-decoration: underline; }
    .countdown-badge { font-size: 0.7rem; color: var(--danger); font-weight: 600; display: block; margin-top: 2px; }
    .expired-section { margin-top: 32px; }
    .expired-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; cursor: pointer; }
    .expired-header h3 { font-size: 1rem; color: var(--text-muted); font-weight: 600; }
    .expired-toggle { font-size: 0.85rem; color: var(--primary); font-weight: 600; cursor: pointer; background: none; border: none; }
    .expired-content { display: none; }
    .expired-content.show { display: block; }
    .expired-table tr { opacity: 0.6; }
    .expired-table tr:hover { opacity: 0.85; }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-shopping-bag" style="margin-right: 8px;"></i>Minhas Vendas</h2>
        <p>Acompanhe suas vendas e cobranças.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('vendedor.vendas.canceladas') }}" class="btn btn-outline">
            <i class="fas fa-folder"></i> Canceladas ({{ $vendasCanceladas->count() + $vendasExpiradas->count() }})
        </a>
        <a href="{{ route('vendedor.vendas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Venda
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value">{{ $vendas->count() }}</div>
        <div class="stat-label">Vendas Ativas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color: var(--warning);">{{ $vendas->where('status', 'Aguardando pagamento')->count() }}</div>
        <div class="stat-label">Aguardando Pagto</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check"></i></div>
        <div class="stat-value" style="color: var(--success);">{{ $vendas->where('status', 'Pago')->count() }}</div>
        <div class="stat-label">Pagas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-xmark"></i></div>
        <div class="stat-value" style="color: var(--danger);">{{ $vendasCanceladas->count() }}</div>
        <div class="stat-label">Canceladas</div>
    </div>
</div>

<!-- Table -->
<div class="table-container">
    @if($vendas->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Plano</th>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Pagamento</th>
                <th>Data</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $venda->cliente?->nome_igreja ?? ($venda->cliente?->nome ?? '—') }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $venda->cliente?->nome_pastor ?? '' }}</div>
                </td>
                <td><span class="badge badge-primary">{{ $venda->plano ?? 'N/A' }}</span></td>
                <td style="font-weight: 700;">R$ {{ number_format($venda->valor, 2, ',', '.') }}</td>
                <td style="font-size: 0.85rem; color: var(--text-secondary);">{{ ucfirst($venda->tipo_negociacao ?? 'mensal') }}</td>
                <td>
                    @php
                        $statusExibido = $venda->getStatusEfetivo();
                        $cleanStatus = trim(strtoupper($statusExibido));
                        $statusClass = match(true) {
                            in_array($cleanStatus, ['PAGO', 'RECEIVED', 'CONFIRMED']) => 'status-pago',
                            in_array($cleanStatus, ['AGUARDANDO PAGAMENTO', 'PENDING', 'AGUARDANDO APROVAÇÃO', 'AGUARDANDO APROVACAO']) => 'status-aguardando',
                            in_array($cleanStatus, ['VENCIDO', 'OVERDUE', 'REJEITADO']) => 'status-vencido',
                            in_array($cleanStatus, ['CANCELADO', 'CANCELED']) => 'status-cancelado',
                            in_array($cleanStatus, ['ESTORNADO', 'REFUNDED']) => 'status-estornado',
                            $cleanStatus === 'EXPIRADO' => 'status-expirado',
                            default => 'status-aguardando'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusExibido }}</span>
                    @if($venda->isPagamentoParcelado() && $venda->getParcelaAtual() > 0)
                        <span style="font-size: 0.7rem; color: var(--success); font-weight: 600; display: block; margin-top: 2px;">{{ $venda->getProgressoParcelas() }}</span>
                    @endif
                    @if($venda->status === 'Aguardando pagamento')
                        @php $horasRestantes = max(0, 72 - floor(now()->diffInHours($venda->created_at))); @endphp
                        @if($horasRestantes > 0)
                            <span class="countdown-badge"><i class="fas fa-clock"></i> {{ $horasRestantes }}h restantes</span>
                        @endif
                    @endif
                </td>
                <td>
                    @php
                        $checkoutUrl = $venda->checkout_hash ? url('/checkout/' . $venda->checkout_hash) : null;
                        $boletoCheckoutUrl = $checkoutUrl ? $checkoutUrl . '?method=boleto' : null;
                    @endphp

                    @if(!in_array(strtoupper($venda->status), ['PAGO', 'CANCELADO', 'EXPIRADO', 'ESTORNADO']))
                        @if($checkoutUrl)
                            <div class="d-flex flex-column gap-1">
                                <button onclick="copiarLinkCheckout({{ $venda->id }})" class="btn btn-success btn-sm" style="font-size: 0.75rem; padding: 4px 8px; width: 100%; text-align: left;" title="Copiar Link de Checkout">
                                    <i class="fas fa-link"></i> Link
                                </button>
                                <a href="{{ $boletoCheckoutUrl }}" target="_blank" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 4px 8px; width: 100%; text-align: left;" title="Ver Boleto no Checkout">
                                    <i class="fas fa-barcode"></i> Boleto
                                </a>
                            </div>
                        @else
                            <span style="font-size: 0.8rem; color: var(--warning); font-weight: 600;"><i class="fas fa-clock"></i> Gerando...</span>
                        @endif
                    @else
                        <span style="font-size: 0.8rem; color: var(--text-muted);">—</span>
                    @endif
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $venda->data_venda ? $venda->data_venda->format('d/m/Y') : $venda->created_at->format('d/m/Y') }}</td>
                <td style="text-align: right;">
                    @if(!in_array(strtoupper($venda->status), ['PAGO', 'CANCELADO', 'EXPIRADO', 'ESTORNADO']))
                    <button type="button" class="btn btn-ghost btn-sm" style="color: var(--success);" title="Copiar Link de Pagamento" onclick="copiarLinkCheckout({{ $venda->id }})">
                        <i class="fas fa-link"></i>
                    </button>
                    <form method="POST" action="{{ route('vendedor.vendas.sync', $venda->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--primary);" title="Sincronizar com Asaas">
                            <i class="fas fa-rotate"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('vendedor.vendas.cancelar', $venda->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Cancelar Venda', message: 'Deseja cancelar esta venda? O registro será mantido no histórico.', type: 'warning', confirmText: 'Cancelar Venda', onConfirm: () => this.submit()});">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);"><i class="fas fa-trash"></i></button>
                    </form>
                    @else
                        <span style="font-size: 0.75rem; color: var(--text-muted);">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-shopping-bag"></i></div>
        <h3>Nenhuma venda cadastrada</h3>
        <p>Comece registrando sua primeira venda.</p>
        <a href="{{ route('vendedor.vendas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Venda
        </a>
    </div>
    @endif
</div>

<!-- Expired Section -->
@if($vendasExpiradas->count() > 0)
<div class="expired-section">
    <div class="expired-header" onclick="document.getElementById('expiredContent').classList.toggle('show')">
        <h3><i class="fas fa-clock"></i> Vendas Expiradas ({{ $vendasExpiradas->count() }})</h3>
        <button class="expired-toggle">Mostrar/Ocultar</button>
    </div>
    <div class="expired-content" id="expiredContent">
        <div class="table-container">
            <table class="expired-table">
                <thead>
                    <tr><th>Cliente</th><th>Plano</th><th>Valor</th><th>Data</th></tr>
                </thead>
                <tbody>
                    @foreach($vendasExpiradas as $venda)
                    <tr>
                        <td>{{ $venda->cliente?->nome_igreja ?? '—' }}</td>
                        <td><span class="badge badge-secondary">{{ $venda->plano }}</span></td>
                        <td>R$ {{ number_format($venda->valor, 2, ',', '.') }}</td>
                        <td style="color: var(--text-muted);">{{ $venda->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<script>
async function copiarLinkCheckout(vendaId) {
    try {
        const response = await fetch(`/vendedor/vendas/${vendaId}/checkout-link`);
        const data = await response.json();
        
        if (data.success) {
            const url = data.url;
            
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                alert('✅ Link copiado para a área de transferência!\n\n' + url);
            } else {
                // Fallback: usar textarea temporário
                const textArea = document.createElement("textarea");
                textArea.value = url;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    alert('✅ Link copiado para a área de transferência!\n\n' + url);
                } catch (err) {
                    console.error('Fallback copy failed', err);
                    alert('⚠️ Não foi possível copiar automaticamente. Use este link:\n\n' + url);
                }
                document.body.removeChild(textArea);
            }
        } else {
            alert('❌ ' + (data.error || 'Erro ao gerar link'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao buscar link do servidor. Tente novamente.');
    }
}
</script>

@endsection
