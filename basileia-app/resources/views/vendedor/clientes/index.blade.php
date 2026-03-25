@extends('layouts.app')
@section('title', 'Meus Clientes')

@section('content')
<style>
    /* ===== Animações ===== */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }

    /* ===== Cabeçalho ===== */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .page-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .page-header .subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }

    /* ===== Cards ===== */
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; transition: 0.3s; position: relative; overflow: hidden; }
    .card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -10px rgba(0,0,0,0.08); border-color: var(--primary); }
    .card .icon { font-size: 1.5rem; margin-bottom: 12px; display: block; }
    .card .value { font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: block; }
    .card .label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
    .card.highlight { background: var(--primary); border-color: var(--primary); }
    .card.highlight .value, .card.highlight .label, .card.highlight .icon { color: white; }

    /* ===== Filtros ===== */
    .filters-bar { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px; }
    .filter-group label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .filter-group input, .filter-group select { padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.88rem; outline: none; background: white; transition: 0.2s; }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(88,28,135,0.1); }
    .btn-filter { background: var(--primary); color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.88rem; transition: 0.2s; white-space: nowrap; }
    .btn-filter:hover { background: var(--primary-hover); }

    /* ===== Tabela ===== */
    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    table { width: 100%; border-collapse: collapse; text-align: left; min-width: 800px; }
    th { background: #f8fafc; padding: 14px 20px; font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
    td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    /* ===== Badges ===== */
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-ativo { background: #dcfce7; color: #15803d; }
    .badge-churn { background: #fee2e2; color: #b91c1c; }
    .badge-inadimplente { background: #fef3c7; color: #92400e; }
    .badge-inativo { background: #f1f5f9; color: #475569; }

    .action-btn { background: white; border: 1px solid var(--border); padding: 6px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; transition: 0.2s; }
    .action-btn:hover { border-color: var(--primary); background: #f8fafc; }
</style>

<div class="page-header animate-in">
    <div>
        <h2>🤝 Meus Clientes</h2>
        <div class="subtitle">Sua carteira comercial ativa</div>
    </div>
    <a href="{{ route('vendedor.vendas.create') }}" class="btn-filter" style="text-decoration: none;">+ Nova Venda</a>
</div>

<!-- ===== Cards ===== -->
<div class="summary-grid">
    <div class="card animate-in highlight">
        <span class="icon">🏛️</span>
        <span class="value">{{ $cards['total'] }}</span>
        <span class="label">Total na Carteira</span>
    </div>
    <div class="card animate-in">
        <span class="icon">🟢</span>
        <span class="value" style="color: #10b981;">{{ $cards['ativos'] }}</span>
        <span class="label">Em Dia (Ativos)</span>
    </div>
    <div class="card animate-in">
        <span class="icon">⚠️</span>
        <span class="value" style="color: #f59e0b;">{{ $cards['inadimplentes'] }}</span>
        <span class="label">Atenção (Atrasados)</span>
    </div>
</div>

<!-- ===== Filtros ===== -->
<form method="GET" action="{{ route('vendedor.clientes') }}">
<div class="filters-bar animate-in">
    <div class="filter-group" style="flex: 2;">
        <label>Buscar Minha Base</label>
        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome, igreja, CNPJ...">
    </div>
    <div class="filter-group">
        <label>Status</label>
        <select name="status">
            <option value="">Todos</option>
            <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
            <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
            <option value="inadimplente" {{ request('status') == 'inadimplente' ? 'selected' : '' }}>Inadimplente</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn-filter">🔍 Filtrar</button>
        <a href="{{ route('vendedor.clientes') }}" style="text-decoration: none; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; padding: 10px;">Limpar</a>
    </div>
</div>
</form>

<!-- ===== Tabela ===== -->
<div class="table-container animate-in">
    @if($clientes->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Igreja / Entidade</th>
                <th>Responsável / Pastor</th>
                <th>Membros</th>
                <th>Visão Financeira</th>
                <th>Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $c)
            <tr>
                <td>
                    <div style="font-weight: 700; color: var(--text-main);">{{ $c->nome_igreja ?? $c->nome }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $c->localidade ?? 'Localidade não informada' }}</div>
                </td>
                <td>
                    <div style="font-weight: 600;">{{ $c->nome_pastor ?? $c->nome_responsavel ?? 'Não informado' }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $c->contato ?? $c->telefone ?? '' }}</div>
                </td>
                <td style="font-weight: 700; text-align: center;">{{ $c->quantidade_membros ?? '-' }}</td>
                <td>
                    @if($c->temCobrancaAberta())
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; font-weight: 600; color: #b91c1c; background: #fee2e2; padding: 2px 8px; border-radius: 4px;">
                            <span style="width: 6px; height: 6px; background: #ef4444; border-radius: 50%;"></span>
                            Débito
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; font-weight: 600; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 4px;">
                            <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></span>
                            Em Dia
                        </span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $c->status ?? 'ativo' }}">{{ ucfirst($c->status ?? 'Ativo') }}</span>
                </td>
                <td style="text-align: right;">
                    <a href="{{ route('vendedor.clientes.show', $c->id) }}" class="action-btn">👁️ Histórico</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Paginação -->
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.85rem; color: var(--text-muted);">Mostrando {{ $clientes->firstItem() ?? 0 }} a {{ $clientes->lastItem() ?? 0 }} de {{ $clientes->total() }} clientes</span>
        <div>
            {{ $clientes->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @else
    <div style="padding: 80px 20px; text-align: center;">
        <div style="background: #f1f5f9; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <span style="font-size: 2rem;">🤝</span>
        </div>
        <h3 style="color: var(--text-main); font-weight: 700; margin-bottom: 8px;">Nenhum cliente na sua base</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Os clientes aparecerão aqui assim que você registrar a primeira venda para eles.</p>
    </div>
    @endif
</div>

@endsection
