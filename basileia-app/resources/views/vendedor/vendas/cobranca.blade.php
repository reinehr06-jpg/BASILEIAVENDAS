@extends('layouts.app')
@section('title', 'Detalhes da Cobrança')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .btn-back { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-back:hover { background: #f8fafc; }

    .cobranca-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 900px) { .cobranca-grid { grid-template-columns: 1fr; } }

    .info-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .info-card h3 { font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid rgba(88,28,135,0.08); display: flex; align-items: center; gap: 8px; }

    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 0.82rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .info-value { font-weight: 700; color: var(--text-main); font-size: 0.95rem; text-align: right; }

    .status-badge { padding: 5px 14px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-pendente { background: #fef9c3; color: #854d0e; }
    .status-pago { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }

    .actions-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 28px; grid-column: 1 / -1; }
    .actions-card h3 { font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid rgba(88,28,135,0.08); display: flex; align-items: center; gap: 8px; }

    .actions-grid { display: flex; gap: 12px; flex-wrap: wrap; }
    .action-btn { display: inline-flex; align-items: center; gap: 10px; padding: 14px 24px; border-radius: 12px; font-weight: 700; font-size: 0.92rem; cursor: pointer; transition: 0.2s; text-decoration: none; border: none; }
    .action-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
    .action-btn-boleto { background: linear-gradient(135deg, #581c87, #7c3aed); color: white; }
    .action-btn-copy { background: white; border: 2px solid var(--primary); color: var(--primary); }
    .action-btn-copy:hover { background: rgba(88,28,135,0.04); }
    .action-btn-disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; pointer-events: none; }

    .iframe-container { grid-column: 1 / -1; background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
    .iframe-container h3 { font-size: 1rem; font-weight: 700; color: var(--primary); padding: 20px 28px; margin: 0; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }
    .iframe-container iframe { width: 100%; height: 600px; border: none; }

    .toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #166534; color: white; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; }
    .toast.show { display: block; animation: slideUp 0.3s ease; }
    @keyframes slideUp { from { opacity: 0; transform: translate(-50%, 20px); } to { opacity: 1; transform: translate(-50%, 0); } }

    .valor-destaque { font-size: 2rem; font-weight: 800; color: var(--primary); text-align: center; padding: 20px; background: linear-gradient(135deg, rgba(88,28,135,0.04), rgba(124,58,237,0.04)); border-radius: 12px; margin-bottom: 20px; }
</style>

<div class="page-header">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">📋 Detalhes da Cobrança</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 4px;">Venda #{{ $venda->id }} — {{ $venda->cliente->nome_igreja ?? $venda->cliente->nome ?? 'Cliente' }}</p>
    </div>
    <a href="{{ route('vendedor.vendas') }}" class="btn-back">← Voltar para Vendas</a>
</div>

@php
    $pagamento = $venda->pagamentos->first();
    $cobranca = $venda->cobrancas->first();
    $boletoUrl = $pagamento->bank_slip_url ?? ($cobranca->link ?? null);
    $invoiceUrl = $pagamento->invoice_url ?? ($pagamento->link_pagamento ?? ($cobranca->link ?? null));
    $statusClass = match(strtoupper($pagamento->status ?? 'pending')) {
        'PENDENTE', 'PENDING' => 'status-pendente',
        'PAGO', 'RECEIVED', 'CONFIRMED' => 'status-pago',
        'VENCIDO', 'OVERDUE' => 'status-vencido',
        'CANCELADO', 'CANCELED' => 'status-cancelado',
        default => 'status-pendente',
    };
@endphp

<div class="cobranca-grid">
    <!-- Card: Resumo do Valor -->
    <div class="info-card">
        <h3>💰 Valor da Cobrança</h3>
        <div class="valor-destaque">R$ {{ number_format($venda->valor, 2, ',', '.') }}</div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="status-badge {{ $statusClass }}">{{ strtoupper($pagamento->status ?? 'Pendente') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Forma de Pagamento</span>
            <span class="info-value">{{ $venda->forma_pagamento }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo</span>
            <span class="info-value">{{ ucfirst($venda->tipo_negociacao ?? 'Mensal') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Plano</span>
            <span class="info-value" style="color: var(--primary);">{{ $venda->plano }}</span>
        </div>
    </div>

    <!-- Card: Dados do Cliente -->
    <div class="info-card">
        <h3>⛪ Cliente</h3>
        <div class="info-row">
            <span class="info-label">Igreja</span>
            <span class="info-value">{{ $venda->cliente->nome_igreja ?? $venda->cliente->nome }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Pastor</span>
            <span class="info-value">{{ $venda->cliente->nome_pastor ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Documento</span>
            <span class="info-value">{{ $venda->cliente->documento ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vencimento</span>
            <span class="info-value">{{ $pagamento->data_vencimento ? $pagamento->data_vencimento->format('d/m/Y') : '—' }}</span>
        </div>
        @if($pagamento->data_pagamento)
        <div class="info-row">
            <span class="info-label">Data de Pagamento</span>
            <span class="info-value" style="color: #166534;">{{ $pagamento->data_pagamento->format('d/m/Y') }}</span>
        </div>
        @endif
        @if($pagamento->linha_digitavel)
        <div class="info-row">
            <span class="info-label">Linha Digitável</span>
            <span class="info-value" style="font-size: 0.78rem; word-break: break-all;">{{ $pagamento->linha_digitavel }}</span>
        </div>
        @endif
    </div>

    <!-- Card: Ações -->
    <div class="actions-card">
        <h3>⚡ Ações Rápidas</h3>
        <div class="actions-grid">
            @if($venda->forma_pagamento === 'BOLETO')
                <button type="button" id="btn-download-boleto" onclick="downloadBoleto({{ $venda->id }})" class="action-btn action-btn-boleto">
                    <span id="boleto-icon">📄</span> <span id="boleto-text">Baixar Boleto</span>
                </button>
            @else
                <span class="action-btn action-btn-disabled">📄 Baixar Boleto (indisponível)</span>
            @endif

            @if($invoiceUrl)
                <button onclick="copyUrl('{{ $invoiceUrl }}')" class="action-btn action-btn-copy">🔗 Copiar URL de Compra</button>
            @else
                <span class="action-btn action-btn-disabled">🔗 Copiar URL (indisponível)</span>
            @endif

            @if($pagamento->linha_digitavel)
                <button onclick="copyUrl('{{ $pagamento->linha_digitavel }}')" class="action-btn action-btn-copy" id="btn-copy-linha">📋 Copiar Linha Digitável</button>
            @endif
        </div>
    </div>

    <!-- Removido Iframe pois o Asaas bloqueia embeds por segurança (X-Frame-Options) -->
</div>

<div class="toast" id="toast">✅ Copiado para a área de transferência!</div>
<div class="toast" id="toast-error" style="background: #991b1b;">❌ Erro ao baixar boleto</div>

<script>
function copyUrl(text) {
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }).catch(() => {
        prompt('Copie o conteúdo abaixo:', text);
    });
}

function showError(message) {
    const toast = document.getElementById('toast-error');
    toast.textContent = '❌ ' + message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

async function downloadBoleto(vendaId) {
    const btn = document.getElementById('btn-download-boleto');
    const icon = document.getElementById('boleto-icon');
    const text = document.getElementById('boleto-text');
    
    // ABRE A JANELA IMEDIATAMENTE antes do await (evita bloqueador de popup do navegador)
    const newWindow = window.open('about:blank', '_blank');
    if (newWindow) {
        newWindow.document.write('<body style="font-family:sans-serif; text-align:center; padding-top:50px;"><h2>Carregando boleto, aguarde...</h2></body>');
    }
    
    // Loading state
    btn.disabled = true;
    btn.style.opacity = '0.7';
    icon.innerHTML = '⏳';
    text.innerHTML = 'Buscando boleto...';

    try {
        const response = await fetch(`/vendedor/vendas/${vendaId}/boleto`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            if (newWindow) newWindow.close(); // Fecha se houver erro
            throw new Error(data.message || 'Falha ao buscar o boleto.');
        }

        // Sucesso: Redireciona a janela aberta para o URL do PDF
        if (newWindow) {
            newWindow.location.href = data.url;
        } else {
            // Se o popup blocker barra de TODO JEITO, forçamos um redirect na aba atual ou tentamos via href
            window.location.href = data.url;
        }

        // Restaura botão
        icon.innerHTML = '✅';
        text.innerHTML = 'Concluído';
        setTimeout(() => {
            if(icon && text) {
                icon.innerHTML = '📄';
                text.innerHTML = 'Baixar Boleto';
            }
        }, 3000);

    } catch (error) {
        showError(error.message);
        icon.innerHTML = '⚠️';
        text.innerHTML = 'Erro';
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
    }
}
</script>

@endsection
