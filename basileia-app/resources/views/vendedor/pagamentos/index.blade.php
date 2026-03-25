@extends('layouts.app')
@section('title', 'Pagamentos dos Clientes')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }

    .stats-bar { display: flex; gap: 16px; margin-bottom: 24px; }
    .stat-mini { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 16px 20px; flex: 1; text-align: center; }
    .stat-mini .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--primary); }
    .stat-mini .stat-label { font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-top: 4px; }

    .filters-bar { background: var(--surface); padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 24px; display: flex; gap: 14px; align-items: center; }
    .filter-select { padding: 9px 14px; border: 1px solid var(--border); border-radius: 6px; outline: none; background: white; font-size: 0.9rem; min-width: 160px; }
    .search-input { flex-grow: 1; padding: 9px 14px; border: 1px solid var(--border); border-radius: 6px; outline: none; font-size: 0.9rem; }
    .search-input:focus, .filter-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.1); }

    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 13px 16px; border-bottom: 1px solid var(--border); }
    th { background: #f8fafc; font-weight: 600; color: var(--text-muted); font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    .status-badge { padding: 5px 11px; border-radius: 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .status-pendente { background: #fef9c3; color: #854d0e; }
    .status-pago { background: #dcfce7; color: #166534; }
    .status-vencido { background: #fee2e2; color: #991b1b; }
    .status-cancelado { background: #f1f5f9; color: #64748b; }
    .status-estornado { background: #fce7f3; color: #9d174d; }
    .status-inadimplente { background: #fee2e2; color: #7f1d1d; }

    .forma-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; background: rgba(88,28,135,0.08); color: var(--primary); }

    .link-pagamento { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.82rem; }
    .link-pagamento:hover { text-decoration: underline; }

    .empty-state { padding: 80px 20px; text-align: center; }
    .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
    .empty-state h3 { font-size: 1.2rem; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
    .empty-state p { color: var(--text-muted); font-size: 0.9rem; }

    .nf-badge { font-size: 0.72rem; padding: 3px 8px; border-radius: 6px; font-weight: 600; }
    .nf-pendente { background: #fef9c3; color: #854d0e; }
    .nf-emitida { background: #dcfce7; color: #166534; }
    .nf-erro { background: #fee2e2; color: #991b1b; }
</style>

<div class="page-header">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Pagamentos dos Clientes</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">Acompanhe as cobranças e pagamentos das suas vendas.</p>
    </div>
</div>

@php
    // Unificar dados de pagamentos + cobranças
    $todosPagamentos = collect();

    foreach ($pagamentos as $p) {
        $statusNormalized = strtolower($p->status) === 'received' ? 'pago' : strtolower($p->status);
        $todosPagamentos->push((object)[
            'igreja' => $p->cliente->nome_igreja ?? $p->cliente->nome ?? '—',
            'pastor' => $p->cliente->nome_pastor ?? '',
            'valor' => $p->valor,
            'forma' => $p->forma_pagamento,
            'status' => $statusNormalized,
            'vencimento' => $p->data_vencimento,
            'pagamento_data' => $p->data_pagamento,
            'link' => $p->link_pagamento,
            'linha_digitavel' => $p->linha_digitavel,
            'nf_status' => $p->nota_fiscal_status,
            'nf_url' => $p->nota_fiscal_url,
            'recorrencia' => $p->recorrencia_status,
            'created_at' => $p->created_at,
        ]);
    }

    foreach ($vendasComCobrancas as $v) {
        foreach ($v->cobrancas as $c) {
            $statusNormalized = strtolower($c->status) === 'received' ? 'pago' : (strtolower($c->status) === 'pending' ? 'pendente' : strtolower($c->status));
            $todosPagamentos->push((object)[
                'igreja' => $v->cliente->nome_igreja ?? $v->cliente->nome ?? '—',
                'pastor' => $v->cliente->nome_pastor ?? '',
                'valor' => $v->valor,
                'forma' => $v->forma_pagamento ?? 'pix',
                'status' => $statusNormalized,
                'vencimento' => null,
                'pagamento_data' => strtolower($c->status) === 'received' ? $c->updated_at : null,
                'link' => $c->link,
                'linha_digitavel' => null,
                'nf_status' => 'pendente',
                'nf_url' => null,
                'recorrencia' => null,
                'created_at' => $c->created_at,
            ]);
        }
    }

    $todosPagamentos = $todosPagamentos->sortByDesc('created_at')->unique(fn($p) => ($p->igreja ?? '') . ($p->valor ?? 0) . ($p->status ?? ''));
@endphp

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-mini">
        <div class="stat-value">{{ $todosPagamentos->count() }}</div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-mini">
        <div class="stat-value">{{ $todosPagamentos->where('status', 'pago')->count() }}</div>
        <div class="stat-label">Pagos</div>
    </div>
    <div class="stat-mini">
        <div class="stat-value">{{ $todosPagamentos->where('status', 'pendente')->count() }}</div>
        <div class="stat-label">Pendentes</div>
    </div>
    <div class="stat-mini">
        <div class="stat-value">R$ {{ number_format($todosPagamentos->where('status', 'pago')->sum('valor'), 2, ',', '.') }}</div>
        <div class="stat-label">Recebido</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <input type="text" class="search-input" id="searchPag" placeholder="🔍 Buscar por igreja ou pastor..." oninput="filterPag()">
    <select class="filter-select" id="statusFilter" onchange="filterPag()">
        <option value="">Status: Todos</option>
        <option value="pendente">Pendente</option>
        <option value="pago">Pago</option>
        <option value="vencido">Vencido</option>
        <option value="cancelado">Cancelado</option>
        <option value="estornado">Estornado</option>
        <option value="inadimplente">Inadimplente</option>
    </select>
    <select class="filter-select" id="formaFilter" onchange="filterPag()">
        <option value="">Forma: Todas</option>
        <option value="pix">PIX</option>
        <option value="boleto">Boleto</option>
        <option value="cartao">Cartão</option>
        <option value="credit_card">Cartão</option>
        <option value="recorrente">Recorrente</option>
    </select>
</div>

<div class="table-container">
    @if($todosPagamentos->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Igreja / Pastor</th>
                <th>Valor</th>
                <th>Forma</th>
                <th>Status</th>
                <th>Vencimento</th>
                <th>Pagamento</th>
                <th>NF</th>
                <th>Link</th>
            </tr>
        </thead>
        <tbody id="pagTableBody">
            @foreach($todosPagamentos as $pag)
            <tr class="pag-row"
                data-igreja="{{ strtolower($pag->igreja) }}"
                data-pastor="{{ strtolower($pag->pastor) }}"
                data-status="{{ $pag->status }}"
                data-forma="{{ strtolower($pag->forma) }}">
                <td>
                    <div style="font-weight: 600; color: var(--text-main);">{{ $pag->igreja }}</div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">{{ $pag->pastor }}</div>
                </td>
                <td style="font-weight: 700;">R$ {{ number_format($pag->valor, 2, ',', '.') }}</td>
                <td><span class="forma-badge">{{ strtoupper($pag->forma) }}</span></td>
                <td><span class="status-badge status-{{ $pag->status }}">{{ ucfirst($pag->status) }}</span></td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $pag->vencimento ? \Carbon\Carbon::parse($pag->vencimento)->format('d/m/Y') : '—' }}</td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $pag->pagamento_data ? \Carbon\Carbon::parse($pag->pagamento_data)->format('d/m/Y') : '—' }}</td>
                <td>
                    @if($pag->nf_url)
                        <a href="{{ $pag->nf_url }}" target="_blank" class="nf-badge nf-emitida">📄 Ver</a>
                    @else
                        <span class="nf-badge nf-{{ $pag->nf_status }}">{{ ucfirst($pag->nf_status) }}</span>
                    @endif
                </td>
                <td>
                    @if($pag->link)
                        <a href="{{ $pag->link }}" target="_blank" style="font-size: 0.82rem; color: var(--primary); font-weight: 600; text-decoration: none;">📄 Abrir Boleto</a>
                    @else
                        <span style="font-size: 0.82rem; color: var(--text-muted);">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="icon">💳</div>
        <h3>Nenhum pagamento registrado</h3>
        <p>Os pagamentos aparecerão aqui conforme vendas forem realizadas.</p>
    </div>
    @endif
</div>

<script>
function filterPag() {
    const search = document.getElementById('searchPag').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const forma = document.getElementById('formaFilter').value;
    document.querySelectorAll('.pag-row').forEach(row => {
        const matchSearch = !search || row.dataset.igreja.includes(search) || row.dataset.pastor.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        const matchForma = !forma || row.dataset.forma.includes(forma);
        row.style.display = (matchSearch && matchStatus && matchForma) ? '' : 'none';
    });
}
</script>
@endsection
