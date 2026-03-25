@extends('layouts.app')
@section('title', 'Minhas Comissões')

@section('content')
<style>
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }
    .animate-in:nth-child(5) { animation-delay: 0.15s; }

    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .page-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .page-header .subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }

    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; transition: 0.3s; }
    .card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -10px rgba(0,0,0,0.08); border-color: var(--primary); }
    .card .icon { font-size: 1.5rem; margin-bottom: 12px; display: block; }
    .card .value { font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: block; }
    .card .label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
    .card.highlight { background: var(--primary); border-color: var(--primary); }
    .card.highlight .value, .card.highlight .label, .card.highlight .icon { color: white; }

    .filters-bar { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px; }
    .filter-group label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .filter-group input, .filter-group select { padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.88rem; outline: none; background: white; transition: 0.2s; }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(88,28,135,0.1); }
    .btn-filter { background: var(--primary); color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.88rem; transition: 0.2s; }
    .btn-filter:hover { background: var(--primary-hover); }
    .btn-export { background: #059669; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.88rem; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
    .btn-export:hover { background: #047857; }

    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    table { width: 100%; border-collapse: collapse; text-align: left; min-width: 900px; }
    th { background: #f8fafc; padding: 14px 16px; font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
    td { padding: 14px 16px; border-bottom: 1px solid var(--border); font-size: 0.88rem; color: var(--text-main); }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
    .badge-pendente { background: #fef9c3; color: #854d0e; }
    .badge-confirmada { background: #dcfce7; color: #15803d; }
    .badge-paga { background: #dbeafe; color: #1d4ed8; }
    .badge-inicial { background: #e0f2fe; color: #0369a1; }
    .badge-recorrencia { background: #faf5ff; color: #7e22ce; }
</style>

<div class="page-header animate-in">
    <div>
        <h2>💰 Minhas Comissões</h2>
        <div class="subtitle">Extrato de comissões por período</div>
    </div>
    <a href="{{ route('vendedor.comissoes.exportar', ['mes' => $mes]) }}" class="btn-export">
        📥 Exportar Excel
    </a>
</div>

@if(session('success'))
<div class="animate-in" style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; border-left: 4px solid #22c55e;">
    ✔️ {{ session('success') }}
</div>
@endif

<!-- Cards -->
<div class="summary-grid">
    <div class="card animate-in">
        <span class="icon">⏳</span>
        <span class="value" style="color: #f59e0b;">R$ {{ number_format($resumo['pendente'], 2, ',', '.') }}</span>
        <span class="label">Pendente</span>
    </div>
    <div class="card animate-in">
        <span class="icon">✅</span>
        <span class="value" style="color: #10b981;">R$ {{ number_format($resumo['confirmada'], 2, ',', '.') }}</span>
        <span class="label">Confirmada</span>
    </div>
    <div class="card animate-in">
        <span class="icon">💵</span>
        <span class="value" style="color: #3b82f6;">R$ {{ number_format($resumo['paga'], 2, ',', '.') }}</span>
        <span class="label">Paga</span>
    </div>
    <div class="card animate-in">
        <span class="icon">🔄</span>
        <span class="value">{{ $resumo['recorrencias'] }}</span>
        <span class="label">Recorrências</span>
    </div>
    <div class="card animate-in highlight">
        <span class="icon">💰</span>
        <span class="value">R$ {{ number_format($resumo['total'], 2, ',', '.') }}</span>
        <span class="label">Total do Mês</span>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="{{ route('vendedor.comissoes') }}">
<div class="filters-bar animate-in">
    <div class="filter-group">
        <label>Mês</label>
        <input type="month" name="mes" value="{{ $mes }}" onchange="this.form.submit()">
    </div>
    <div class="filter-group">
        <label>Tipo</label>
        <select name="tipo" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="inicial" {{ $tipo == 'inicial' ? 'selected' : '' }}>Inicial</option>
            <option value="recorrencia" {{ $tipo == 'recorrencia' ? 'selected' : '' }}>Recorrência</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Status</label>
        <select name="status" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="pendente" {{ $status == 'pendente' ? 'selected' : '' }}>Pendente</option>
            <option value="confirmada" {{ $status == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
            <option value="paga" {{ $status == 'paga' ? 'selected' : '' }}>Paga</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn-filter">🔍 Filtrar</button>
        <a href="{{ route('vendedor.comissoes') }}" style="text-decoration: none; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; padding: 10px;">Limpar</a>
    </div>
</div>
</form>

<!-- Tabela -->
<div class="table-container animate-in">
    @if($comissoes->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Cliente (Igreja)</th>
                <th>CPF/CNPJ</th>
                <th>Venda</th>
                <th>%</th>
                <th>Comissão</th>
                <th>Tipo</th>
                <th>Data Pag.</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comissoes as $c)
            <tr>
                <td>
                    <div style="font-weight: 700;">{{ $c->cliente->nome_igreja ?? $c->cliente->nome ?? 'N/A' }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $c->cliente->nome_pastor ?? $c->cliente->nome_responsavel ?? '' }}</div>
                </td>
                <td style="font-family: monospace; color: var(--text-muted);">{{ $c->cliente->documento ?? '-' }}</td>
                <td style="font-weight: 600; text-align: center;">#{{ $c->venda_id }}</td>
                <td style="text-align: center; font-weight: 700;">{{ $c->percentual_aplicado }}%</td>
                <td style="font-weight: 700; color: var(--primary);">R$ {{ number_format($c->valor_comissao, 2, ',', '.') }}</td>
                <td><span class="badge badge-{{ $c->tipo_comissao }}">{{ ucfirst($c->tipo_comissao) }}</span></td>
                <td>{{ $c->data_pagamento ? $c->data_pagamento->format('d/m/Y') : '-' }}</td>
                <td><span class="badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.85rem; color: var(--text-muted);">Mostrando {{ $comissoes->firstItem() ?? 0 }} a {{ $comissoes->lastItem() ?? 0 }} de {{ $comissoes->total() }} registros</span>
        <div>{{ $comissoes->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
    </div>
    @else
    <div style="padding: 80px 20px; text-align: center;">
        <div style="background: #f1f5f9; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <span style="font-size: 2rem;">💰</span>
        </div>
        <h3 style="color: var(--text-main); font-weight: 700; margin-bottom: 8px;">Nenhuma comissão encontrada</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem;">As comissões aparecerão aqui quando seus pagamentos forem confirmados.</p>
    </div>
    @endif
</div>

@endsection
