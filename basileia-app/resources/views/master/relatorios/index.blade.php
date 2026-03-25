@extends('layouts.app')
@section('title', 'Relatórios')

@section('content')
<style>
    /* ===== Animações ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }
    .animate-in:nth-child(5) { animation-delay: 0.15s; }
    .animate-in:nth-child(6) { animation-delay: 0.18s; }
    .animate-in:nth-child(7) { animation-delay: 0.21s; }
    .animate-in:nth-child(8) { animation-delay: 0.24s; }

    /* ===== Skeleton ===== */
    .skeleton-block {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 400px 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 8px;
    }

    /* ===== Cabeçalho ===== */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .page-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .page-header .subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }
    .btn-export { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
    .btn-export:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }

    /* ===== Filtros ===== */
    .filters-bar { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px; }
    .filter-group label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .filter-group input, .filter-group select { padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.88rem; outline: none; background: white; transition: border-color 0.2s, box-shadow 0.2s; }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(88,28,135,0.1); }
    .btn-filter { background: var(--primary); color: white; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.88rem; transition: 0.2s; white-space: nowrap; }
    .btn-filter:hover { background: var(--primary-hover); }
    .btn-clear { background: white; border: 1px solid var(--border); padding: 9px 18px; border-radius: 8px; font-weight: 500; cursor: pointer; font-size: 0.88rem; color: var(--text-muted); text-decoration: none; white-space: nowrap; }
    .btn-clear:hover { background: #f8fafc; }

    /* ===== Cards ===== */
    .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; transition: all 0.3s ease; }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-3px); }
    .stat-card .icon { font-size: 1.5rem; margin-bottom: 8px; }
    .stat-card .value { font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px; }
    .stat-card .label { font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; font-weight: 600; }
    .stat-card.highlight { background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; border: none; }
    .stat-card.highlight .value { color: white; }
    .stat-card.highlight .label { color: rgba(255,255,255,0.8); }

    /* ===== Seções ===== */
    .section-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; margin-bottom: 24px; overflow: hidden; }
    .section-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .section-header h3 { font-size: 1rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
    .section-body { padding: 0; }

    /* ===== Tabelas (com scroll horizontal em mobile) ===== */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; min-width: 600px; }
    .report-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; font-size: 0.75rem; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .report-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: var(--text-main); }
    .report-table tr:last-child td { border-bottom: none; }
    .report-table tr:hover td { background: #f8fafc; }
    .report-table .text-right { text-align: right; }
    .report-table .text-center { text-align: center; }
    .report-table .font-bold { font-weight: 700; }

    /* ===== Barra de progresso ===== */
    .progress-bar { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; position: relative; }
    .progress-bar .fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .progress-bar .fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .progress-bar .fill.yellow { background: linear-gradient(90deg, #eab308, #ca8a04); }
    .progress-bar .fill.red { background: linear-gradient(90deg, #ef4444, #dc2626); }

    /* ===== Badge ===== */
    .badge-forma { display: inline-flex; padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-transform: capitalize; }
    .badge-forma.pix { background: #dbeafe; color: #1d4ed8; }
    .badge-forma.boleto { background: #fef3c7; color: #92400e; }
    .badge-forma.cartao { background: #f3e8ff; color: #6b21a8; }
    .badge-forma.recorrente { background: #d1fae5; color: #065f46; }

    /* ===== Empty states ===== */
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state .icon { font-size: 2.5rem; margin-bottom: 12px; }
    .empty-state h3 { color: var(--text-main); font-size: 1.1rem; margin-bottom: 6px; }
    .empty-state-box { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; }

    @media (max-width: 768px) {
        .filters-bar { flex-direction: column; }
        .filter-group { min-width: 100%; }
        .cards-grid { grid-template-columns: repeat(2, 1fr); }
        .report-table { font-size: 0.8rem; }
        .report-table th, .report-table td { padding: 8px 10px; }
    }
</style>

<!-- ===== Cabeçalho ===== -->
<div class="page-header">
    <div>
        <h2>📊 Relatórios</h2>
        <p class="subtitle">Análise consolidada da operação comercial e financeira</p>
    </div>
    <a href="{{ route('master.relatorios.exportar', request()->query()) }}" class="btn-export">📥 Exportar Relatório (CSV)</a>
</div>

<!-- ===== Filtros ===== -->
<form method="GET" action="{{ route('master.relatorios') }}">
<div class="filters-bar">
    <div class="filter-group">
        <label>Período Início</label>
        <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] }}">
    </div>
    <div class="filter-group">
        <label>Período Fim</label>
        <input type="date" name="data_fim" value="{{ $filtros['data_fim'] }}">
    </div>
    <div class="filter-group">
        <label>Vendedor</label>
        <select name="vendedor_id">
            <option value="">Todos</option>
            @foreach($vendedores as $v)
                <option value="{{ $v->id }}" {{ $filtros['vendedor_id'] == $v->id ? 'selected' : '' }}>{{ ($v->user->name ?? 'N/A') }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Status da Venda</label>
        <select name="status">
            <option value="">Todos</option>
            <option value="Aguardando pagamento" {{ $filtros['status'] == 'Aguardando pagamento' ? 'selected' : '' }}>Aguardando</option>
            <option value="Pago" {{ $filtros['status'] == 'Pago' ? 'selected' : '' }}>Pago</option>
            <option value="Cancelado" {{ $filtros['status'] == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
            <option value="Expirado" {{ $filtros['status'] == 'Expirado' ? 'selected' : '' }}>Expirado</option>
            <option value="Vencido" {{ $filtros['status'] == 'Vencido' ? 'selected' : '' }}>Vencido</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Forma Pagamento</label>
        <select name="forma_pagamento">
            <option value="">Todas</option>
            <option value="pix" {{ $filtros['forma_pagamento'] == 'pix' ? 'selected' : '' }}>PIX</option>
            <option value="boleto" {{ $filtros['forma_pagamento'] == 'boleto' ? 'selected' : '' }}>Boleto</option>
            <option value="cartao" {{ $filtros['forma_pagamento'] == 'cartao' ? 'selected' : '' }}>Cartão</option>
            <option value="recorrente" {{ $filtros['forma_pagamento'] == 'recorrente' ? 'selected' : '' }}>Recorrente</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Tipo Negociação</label>
        <select name="tipo_negociacao">
            <option value="">Todos</option>
            <option value="mensal" {{ $filtros['tipo_negociacao'] == 'mensal' ? 'selected' : '' }}>Mensal</option>
            <option value="anual" {{ $filtros['tipo_negociacao'] == 'anual' ? 'selected' : '' }}>Anual</option>
        </select>
    </div>
    <div class="filter-group">
        <label>Cliente</label>
        <select name="cliente_id">
            <option value="">Todos</option>
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ $filtros['cliente_id'] == $c->id ? 'selected' : '' }}>{{ ($c->nome_igreja ?? $c->nome ?? 'Cliente #'.$c->id) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Recorrência</label>
        <select name="recorrencia">
            <option value="">Todas</option>
            <option value="ativa" {{ $filtros['recorrencia'] == 'ativa' ? 'selected' : '' }}>Ativa</option>
            <option value="inativa" {{ $filtros['recorrencia'] == 'inativa' ? 'selected' : '' }}>Inativa</option>
        </select>
    </div>
    <div style="display: flex; gap: 8px; align-items: flex-end;">
        <button type="submit" class="btn-filter">🔍 Filtrar</button>
        <a href="{{ route('master.relatorios') }}" class="btn-clear">Limpar filtros</a>
    </div>
</div>
</form>

{{-- ===== Estado vazio global (sem dados no sistema) ===== --}}
@if(!$temDadosNoSistema)
<div class="empty-state empty-state-box" style="padding: 80px 20px;">
    <div class="icon">📊</div>
    <h3>Nenhum dado disponível</h3>
    <p>Os relatórios serão exibidos assim que houver movimentações registradas no sistema.</p>
</div>

{{-- ===== Dados existem, mas filtros não retornaram nada ===== --}}
@elseif(!$filtrosRetornaramDados)
<div class="empty-state empty-state-box" style="padding: 60px 20px;">
    <div class="icon">🔍</div>
    <h3>Nenhum relatório encontrado</h3>
    <p>Nenhum relatório encontrado para os filtros aplicados. Tente alterar os critérios de busca.</p>
</div>

@else

<!-- ===== SEÇÃO 1: Resumo Geral ===== -->
<div class="cards-grid">
    <div class="stat-card highlight animate-in">
        <div class="icon">💰</div>
        <div class="value">{{ $resumo['totalVendas'] }}</div>
        <div class="label">Total de Vendas</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">📈</div>
        <div class="value">R$ {{ number_format($resumo['valorVendido'], 2, ',', '.') }}</div>
        <div class="label">Valor Vendido</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">✅</div>
        <div class="value">R$ {{ number_format($resumo['valorRecebido'], 2, ',', '.') }}</div>
        <div class="label">Valor Recebido</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">🏷️</div>
        <div class="value">R$ {{ number_format($resumo['totalComissoes'], 2, ',', '.') }}</div>
        <div class="label">Comissão Gerada</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">👥</div>
        <div class="value">{{ $resumo['clientesAtivos'] }}</div>
        <div class="label">Clientes Ativos</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">🔄</div>
        <div class="value">{{ $resumo['renovacoes'] }}</div>
        <div class="label">Renovações</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">📉</div>
        <div class="value">{{ $resumo['churn'] }}</div>
        <div class="label">Churn</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">🚫</div>
        <div class="value">{{ $resumo['desistencia'] }}</div>
        <div class="label">Desistência</div>
    </div>
    <div class="stat-card animate-in">
        <div class="icon">🎯</div>
        <div class="value">R$ {{ number_format($resumo['ticketMedio'], 2, ',', '.') }}</div>
        <div class="label">Ticket Médio</div>
    </div>
</div>

<!-- ===== SEÇÃO 2: Vendas por Vendedor ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3>👤 Vendas por Vendedor</h3>
    </div>
    <div class="section-body">
        @if(count($vendasPorVendedor) > 0)
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th class="text-center">Vendas</th>
                    <th class="text-right">Valor Vendido</th>
                    <th class="text-right">Valor Recebido</th>
                    <th class="text-right">Comissão</th>
                    <th class="text-center">Clientes</th>
                    <th class="text-center">Churn</th>
                    <th class="text-center">Desistência</th>
                    <th class="text-right">Meta</th>
                    <th>% Meta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendasPorVendedor as $vv)
                <tr>
                    <td class="font-bold">{{ $vv['vendedor_nome'] }}</td>
                    <td class="text-center">{{ $vv['total_vendas'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_vendido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($vv['valor_recebido'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($vv['comissao'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $vv['clientes_ativos'] }}</td>
                    <td class="text-center">{{ $vv['churn'] }}</td>
                    <td class="text-center">{{ $vv['desistencia'] }}</td>
                    <td class="text-right">R$ {{ number_format($vv['meta'], 2, ',', '.') }}</td>
                    <td style="min-width: 120px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill {{ $vv['percentual_meta'] >= 100 ? 'green' : ($vv['percentual_meta'] >= 50 ? 'yellow' : 'red') }}" style="width: {{ min($vv['percentual_meta'], 100) }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 700; color: {{ $vv['percentual_meta'] >= 100 ? '#16a34a' : ($vv['percentual_meta'] >= 50 ? '#ca8a04' : '#dc2626') }};">{{ $vv['percentual_meta'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @else
        <div class="empty-state">
            <p>Nenhum relatório encontrado para os filtros aplicados.</p>
        </div>
        @endif
    </div>
</div>

<!-- ===== SEÇÃO 3: Recebimentos por Período ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3>💳 Recebimentos no Período</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="text-right">Quantidade / Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>📋 Total de Cobranças</td>
                    <td class="text-right font-bold">{{ $pagamentosPeriodo['total_pagamentos'] }}</td>
                </tr>
                <tr>
                    <td>✅ Total Pago</td>
                    <td class="text-right font-bold" style="color: #16a34a;">R$ {{ number_format($pagamentosPeriodo['total_pago'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>⏳ Total Pendente</td>
                    <td class="text-right font-bold" style="color: #ca8a04;">R$ {{ number_format($pagamentosPeriodo['total_pendente'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>❌ Total Vencido</td>
                    <td class="text-right font-bold" style="color: #dc2626;">R$ {{ number_format($pagamentosPeriodo['total_vencido'], 2, ',', '.') }}</td>
                </tr>
                <tr style="background: #f8fafc;">
                    <td class="font-bold">💰 Valor Total Recebido</td>
                    <td class="text-right font-bold" style="font-size: 1.1rem; color: var(--primary);">R$ {{ number_format($pagamentosPeriodo['valor_recebido'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- ===== SEÇÃO 4: Renovações e Churn ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3>🔄 Renovações e Churn</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>✅ Clientes Renovados / Pagos</td>
                    <td class="text-right font-bold" style="color: #16a34a;">{{ $churnRenovacoes['renovados'] }}</td>
                </tr>
                <tr>
                    <td>📉 Churn (Pós-pagamento)</td>
                    <td class="text-right font-bold" style="color: #dc2626;">{{ $churnRenovacoes['churn'] }}</td>
                </tr>
                <tr>
                    <td>🚫 Desistência (Pré-pagamento)</td>
                    <td class="text-right font-bold" style="color: #64748b;">{{ $churnRenovacoes['desistencias'] }}</td>
                </tr>
                <tr>
                    <td>📊 Taxa de Churn (%)</td>
                    <td class="text-right font-bold" style="color: {{ $churnRenovacoes['churn_percentual'] > 20 ? '#dc2626' : ($churnRenovacoes['churn_percentual'] > 10 ? '#ca8a04' : '#16a34a') }};">
                        {{ $churnRenovacoes['churn_percentual'] }}%
                    </td>
                </tr>
                <tr>
                    <td>🟢 Recorrência Ativa</td>
                    <td class="text-right font-bold">{{ $churnRenovacoes['ativos'] }}</td>
                </tr>
                <tr>
                    <td>🔴 Recorrência Inativa</td>
                    <td class="text-right font-bold">{{ $churnRenovacoes['inativos'] }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- ===== SEÇÃO 5: Formas de Pagamento ===== -->
<div class="section-card animate-in">
    <div class="section-header">
        <h3>💳 Formas de Pagamento</h3>
    </div>
    <div class="section-body">
        <div class="table-responsive">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Forma</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-right">Valor Total</th>
                    <th>% de Uso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($formasPagamento as $fp)
                <tr>
                    <td>
                        <span class="badge-forma {{ $fp['forma'] }}">
                            @if($fp['forma'] == 'pix') ⚡ PIX
                            @elseif($fp['forma'] == 'boleto') 📄 Boleto
                            @elseif($fp['forma'] == 'cartao') 💳 Cartão
                            @else 🔄 Recorrente
                            @endif
                        </span>
                    </td>
                    <td class="text-center font-bold">{{ $fp['quantidade'] }}</td>
                    <td class="text-right">R$ {{ number_format($fp['valor_total'], 2, ',', '.') }}</td>
                    <td style="min-width: 140px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="progress-bar" style="flex: 1;">
                                <div class="fill green" style="width: {{ $fp['percentual'] }}%;"></div>
                            </div>
                            <span style="font-size: 0.78rem; font-weight: 600;">{{ $fp['percentual'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

@endif

@endsection
