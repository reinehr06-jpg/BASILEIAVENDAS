@extends('layouts.app')
@section('title', 'Metas Comerciais')

@section('content')
<style>
    /* ===== Animações ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: fadeInUp 0.45s ease-out both; }
    .animate-in:nth-child(1) { animation-delay: 0.03s; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.09s; }
    .animate-in:nth-child(4) { animation-delay: 0.12s; }
    .animate-in:nth-child(5) { animation-delay: 0.15s; }
    .animate-in:nth-child(6) { animation-delay: 0.18s; }

    /* ===== Cabeçalho ===== */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .page-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }
    .page-header .subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 4px; }
    .btn-primary { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(88, 28, 135, 0.2); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }

    /* ===== Filtros ===== */
    .filters-bar { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px; }
    .filter-group label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .filter-group input, .filter-group select { padding: 9px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.88rem; outline: none; background: white; transition: 0.2s; }
    .filter-group input:focus, .filter-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(88,28,135,0.1); }
    .btn-filter { background: var(--primary); color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.88rem; transition: 0.2s; }
    .btn-filter:hover { background: var(--primary-hover); }

    /* ===== Cards ===== */
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
    .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; text-align: center; transition: 0.3s; position: relative; overflow: hidden; }
    .card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -10px rgba(0,0,0,0.08); border-color: var(--primary); }
    .card .icon { font-size: 1.5rem; margin-bottom: 12px; display: block; }
    .card .value { font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: block; }
    .card .label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
    .card.highlight { background: var(--primary); border-color: var(--primary); }
    .card.highlight .value, .card.highlight .label, .card.highlight .icon { color: white; }

    /* ===== Tabela ===== */
    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: #f8fafc; padding: 14px 20px; font-weight: 700; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
    td { padding: 16px 20px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }

    /* ===== Progress Bar ===== */
    .progress-wrapper { width: 100%; min-width: 120px; }
    .progress-info { display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 6px; font-weight: 700; }
    .progress-bar-bg { background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
    .bg-danger { background: #ef4444; }
    .bg-warning { background: #f59e0b; }
    .bg-success { background: #10b981; }
    .bg-info { background: #0ea5e9; }

    /* ===== Badges ===== */
    .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
    .badge-nao-iniciada { background: #f1f5f9; color: #475569; }
    .badge-em-andamento { background: #e0f2fe; color: #0369a1; }
    .badge-atingida { background: #dcfce7; color: #15803d; }
    .badge-não-atingida { background: #fee2e2; color: #b91c1c; }
    .badge-superada { background: #faf5ff; color: #7e22ce; border: 1px solid #d8b4fe; }

    /* ===== Modal ===== */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); display: none; align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(4px); padding: 20px; }
    .modal-content { background: white; border-radius: 16px; width: 100%; max-width: 550px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: modalIn 0.3s ease-out; overflow: hidden; }
    @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .modal-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { font-size: 1.25rem; font-weight: 700; color: var(--text-main); }
    .close-modal { background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .close-modal:hover { background: #fee2e2; color: #ef4444; }
    .modal-body { padding: 24px; }
    .modal-footer { padding: 20px 24px; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 12px; }
    
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
    .form-group input, .form-group select, .form-group text area { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 10px; font-size: 0.95rem; outline: none; transition: 0.2s; }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.1); }
</style>

<div class="page-header animate-in">
    <div>
        <h2>Metas da Operação</h2>
        <div class="subtitle">Acompanhamento de objetivos e performance por vendedor</div>
    </div>
    <button class="btn-primary" onclick="openModal('create')">
        <span>+</span> Nova Meta
    </button>
</div>

@if(session('success'))
<div class="animate-in" style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; border-left: 4px solid #22c55e; display: flex; align-items: center; gap: 12px;">
    <span>✔️</span> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="animate-in" style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; border-left: 4px solid #ef4444; display: flex; align-items: center; gap: 12px;">
    <span>❌</span> {{ $errors->first() }}
</div>
@endif

<!-- ===== Filtros ===== -->
<div class="filters-bar animate-in">
    <form action="{{ route('master.metas') }}" method="GET" class="filters-bar" style="width: 100%; margin: 0; padding: 0; border: none; background: transparent;">
        <div class="filter-group">
            <label>Mês de Referência</label>
            <input type="month" name="mes" value="{{ $mes }}" onchange="this.form.submit()">
        </div>
        <div class="filter-group">
            <label>Vendedor</label>
            <select name="vendedor_id" onchange="this.form.submit()">
                <option value="">Todos os Vendedores</option>
                @foreach($vendedores as $v)
                    <option value="{{ $v->id }}" {{ $vendedorId == $v->id ? 'selected' : '' }}>{{ $v->user->name ?? 'N/A' }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-filter">Aplicar Filtros</button>
        <a href="{{ route('master.metas') }}" style="text-decoration: none; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin-bottom: 12px;">Limpar</a>
    </form>
</div>

<!-- ===== Cards de Resumo ===== -->
<div class="summary-grid">
    <div class="card animate-in">
        <span class="icon">🎯</span>
        <span class="value">{{ $resumo['total_metas'] }}</span>
        <span class="label">Total de Metas</span>
    </div>
    <div class="card animate-in">
        <span class="icon">🏆</span>
        <span class="value" style="color: #10b981;">{{ $resumo['metas_batidas'] }}</span>
        <span class="label">Metas Batidas</span>
    </div>
    <div class="card animate-in">
        <span class="icon">📉</span>
        <span class="value" style="color: #ef4444;">{{ $resumo['metas_abaixo'] }}</span>
        <span class="label">Abaixo da Meta</span>
    </div>
    <div class="card animate-in highlight">
        <span class="icon">💰</span>
        <span class="value">R$ {{ number_format($resumo['valor_total_meta'], 0, ',', '.') }}</span>
        <span class="label">Volume Esperado</span>
    </div>
    <div class="card animate-in">
        <span class="icon">✅</span>
        <span class="value">R$ {{ number_format($resumo['valor_total_realizado'], 0, ',', '.') }}</span>
        <span class="label">Volume Realizado</span>
    </div>
    <div class="card animate-in">
        <span class="icon">📊</span>
        <span class="value">{{ $resumo['percentual_medio'] }}%</span>
        <span class="label">Atingimento Médio</span>
    </div>
</div>

<!-- ===== Tabela de Metas ===== -->
<div class="table-container animate-in">
    @if($metas->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Mês Ref.</th>
                <th>Meta Definida</th>
                <th>Vendido (Comercial)</th>
                <th>Recebido (Real)</th>
                <th>Clientes Ativos</th>
                <th>Atingimento</th>
                <th>Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($metas as $m)
            <tr>
                <td>
                    <div style="font-weight: 700; color: var(--text-main);">{{ $m->vendedor->user->name ?? 'N/A' }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $m->vendedor->email }}</div>
                </td>
                <td style="font-weight: 600;">{{ \Carbon\Carbon::parse($m->mes_referencia)->translatedFormat('M/Y') }}</td>
                <td style="font-weight: 700;">R$ {{ number_format($m->valor_meta, 2, ',', '.') }}</td>
                <td style="color: var(--text-muted);">R$ {{ number_format($m->valor_vendido, 2, ',', '.') }}</td>
                <td style="font-weight: 700; color: var(--primary);">R$ {{ number_format($m->valor_recebido, 2, ',', '.') }}</td>
                <td style="text-align: center; font-weight: 700;">{{ $m->clientes_ativos }}</td>
                <td style="width: 180px;">
                    <div class="progress-wrapper">
                        <div class="progress-info">
                            <span>{{ $m->percentual }}%</span>
                            <span>{{ $m->percentual >= 100 ? 'META BATIDA' : 'EM CURSO' }}</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill {{ $m->percentual >= 100 ? 'bg-success' : ($m->percentual >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                 style="width: {{ min($m->percentual, 100) }}%"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-{{ str_replace(' ', '-', $m->status) }}">{{ $m->status }}</span>
                </td>
                <td style="text-align: right;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button class="btn-filter" style="padding: 6px 10px; background: white; border: 1px solid var(--border); color: var(--text-main);" 
                                onclick="openModal('edit', {{ json_encode($m) }})">✏️</button>
                        <form action="{{ route('master.metas.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Excluir esta meta?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-filter" style="padding: 6px 10px; background: white; border: 1px solid #fee2e2; color: #ef4444;">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding: 80px 20px; text-align: center;">
        <div style="background: #f1f5f9; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <span style="font-size: 2rem;">📂</span>
        </div>
        <h3 style="color: var(--text-main); font-weight: 700; margin-bottom: 8px;">Nenhuma meta encontrada</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Não há metas cadastradas para os critérios selecionados.</p>
        @if($vendedorId || $mes)
            <a href="{{ route('master.metas') }}" style="display: inline-block; margin-top: 16px; color: var(--primary); font-weight: 700; text-decoration: none;">Limpar Filtros</a>
        @endif
    </div>
    @endif
</div>

<!-- ===== MODAL: Criar/Editar Meta ===== -->
<div class="modal-overlay" id="metaModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Cadastrar Meta</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form id="metaForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-body">
                <div class="form-group" id="vendedorGroup">
                    <label>Vendedor</label>
                    <select name="vendedor_id" id="formVendedor" required>
                        <option value="">Selecione o Vendedor</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}">{{ $v->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" id="mesGroup">
                        <label>Mês de Referência</label>
                        <input type="month" name="mes_referencia" id="formMes" required>
                    </div>
                    <div class="form-group">
                        <label>Valor da Meta (R$)</label>
                        <input type="number" step="0.01" name="valor_meta" id="formValor" required placeholder="0,00">
                    </div>
                </div>
                <div class="form-group">
                    <label>Status da Meta</label>
                    <select name="status" id="formStatus" required>
                        <option value="não iniciada">Não iniciada</option>
                        <option value="em andamento">Em andamento</option>
                        <option value="atingida">Atingida</option>
                        <option value="não atingida">Não atingida</option>
                        <option value="superada">Superada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Observação Interna</label>
                    <textarea name="observacao" id="formObs" rows="3" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 10px; font-size: 0.95rem; outline: none;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary" id="btnSubmit">Salvar Meta</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('metaModal');
    const form = document.getElementById('metaForm');

    function openModal(mode, data = null) {
        if (mode === 'create') {
            document.getElementById('modalTitle').textContent = 'Cadastrar Meta';
            document.getElementById('formMethod').value = 'POST';
            form.action = "{{ route('master.metas.store') }}";
            form.reset();
            document.getElementById('vendedorGroup').style.display = 'block';
            document.getElementById('mesGroup').style.display = 'block';
        } else {
            document.getElementById('modalTitle').textContent = 'Editar Meta';
            document.getElementById('formMethod').value = 'PUT';
            form.action = `/master/metas/${data.id}`;
            
            document.getElementById('formVendedor').value = data.vendedor_id;
            document.getElementById('formMes').value = data.mes_referencia;
            document.getElementById('formValor').value = data.valor_meta;
            document.getElementById('formStatus').value = data.status;
            document.getElementById('formObs').value = data.observacao || '';
            
            // Não permitir trocar vendedor/mês na edição para manter integridade
            document.getElementById('vendedorGroup').style.display = 'none';
            document.getElementById('mesGroup').style.display = 'none';
        }
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) closeModal();
    }
</script>

@endsection
