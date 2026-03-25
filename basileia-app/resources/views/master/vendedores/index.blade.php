@extends('layouts.app')
@section('title', 'Vendedores')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .btn-primary { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(88, 28, 135, 0.2); }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
    
    .filters-bar { background: var(--surface); padding: 16px 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 24px; display: flex; gap: 16px; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    .search-input { flex-grow: 1; padding: 10px 16px; border: 1px solid var(--border); border-radius: 6px; outline: none; font-size: 0.95rem; }
    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.1); }
    .status-filter { padding: 10px 16px; border: 1px solid var(--border); border-radius: 6px; outline: none; min-width: 180px; background: white; font-size: 0.95rem; }
    
    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th, td { padding: 16px 20px; border-bottom: 1px solid var(--border); }
    th { background: #f8fafc; font-weight: 600; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background: #f8fafc; }
    
    .status-badge { padding: 6px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;}
    .status-ativo { background: #dcfce7; color: #166534; }
    .status-inativo { background: #fef9c3; color: #854d0e; }
    .status-bloqueado { background: #fee2e2; color: #991b1b; }
    
    .action-btn { background: none; border: 1px solid var(--border); font-size: 0.85rem; cursor: pointer; padding: 6px 10px; margin-right: 4px; transition: 0.2s; border-radius: 6px; }
    .action-btn:hover { background: #e2e8f0; }
    .action-btn.danger { border-color: #fecaca; color: #991b1b; }
    .action-btn.danger:hover { background: #fee2e2; }
    .action-btn.success { border-color: #bbf7d0; color: #166534; }
    .action-btn.success:hover { background: #dcfce7; }
    
    /* Modal Styles */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); display: none; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(2px); }
    .modal { background: var(--surface); padding: 30px; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); animation: modalIn 0.3s ease-out forwards; }
    @keyframes modalIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
    .modal-header h2 { font-size: 1.25rem; font-weight: 700; color: var(--text-main); }
    .close-modal { background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 1.2rem; line-height: 1; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .close-modal:hover { background: #e2e8f0; color: #ef4444; }
    
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;}
    .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-size: 0.95rem; transition: 0.2s;}
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.1); }
    .form-row { display: flex; gap: 16px; }
    .form-row .form-group { flex: 1; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 16px; border-top: 1px solid var(--border); }
    .btn-secondary { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; color: var(--text-main); transition: 0.2s;}
    .btn-secondary:hover { background: #f8fafc; }

    /* Detail Modal */
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .detail-item label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px; }
    .detail-item .value { font-size: 1rem; font-weight: 600; color: var(--text-main); margin-top: 4px; }
</style>

<div class="page-header">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">Gestão de Vendedores</h2>
    <button class="btn-primary" onclick="openModal('create')">+ Novo Vendedor</button>
</div>

@if(session('success'))
<div style="background: #dcfce7; color: #166534; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; border-left: 4px solid #22c55e;">
    ✔️ {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; border-left: 4px solid #ef4444;">
    ❌ {{ $errors->first() }}
</div>
@endif

<div class="filters-bar">
    <div style="flex-grow: 1; position: relative;">
        <span style="position: absolute; left: 14px; top: 12px; opacity: 0.5;">🔍</span>
        <input type="text" class="search-input" id="searchInput" style="padding-left: 40px;" placeholder="Buscar por nome, e-mail ou telefone..." oninput="filterTable()">
    </div>
    <select class="status-filter" id="statusFilter" onchange="filterTable()">
        <option value="">Status: Todos</option>
        <option value="ativo">Ativo</option>
        <option value="inativo">Inativo</option>
        <option value="bloqueado">Bloqueado</option>
    </select>
</div>

<div class="table-container">
    @if(isset($vendedores) && count($vendedores) > 0)
    <table id="vendedoresTable">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Comissão</th>
                <th>Meta Mensal</th>
                <th>Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendedores as $vendedor)
            <tr class="vendedor-row" 
                data-name="{{ strtolower($vendedor->name) }}" 
                data-email="{{ strtolower($vendedor->email) }}" 
                data-telefone="{{ strtolower($vendedor->vendedor?->telefone ?? '') }}"
                data-status="{{ $vendedor->status }}">
                <td>
                    <div style="font-weight: 600; color: var(--text-main);">{{ $vendedor->name }}</div>
                </td>
                <td style="color: var(--text-muted); font-size: 0.9rem;">{{ $vendedor->email }}</td>
                <td style="font-size: 0.9rem;">{{ $vendedor->vendedor?->telefone ?? 'Não informado' }}</td>
                <td><span style="background: rgba(88, 28, 135, 0.1); color: var(--primary); padding: 4px 8px; border-radius: 6px; font-weight: 700;">{{ $vendedor->vendedor?->comissao ?? '0' }}%</span></td>
                <td style="font-weight: 600; color: var(--text-muted);">R$ {{ number_format($vendedor->vendedor?->meta_mensal ?? 0, 2, ',', '.') }}</td>
                <td>
                    <span class="status-badge status-{{ $vendedor->status }}">{{ ucfirst($vendedor->status) }}</span>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    <button class="action-btn" title="Visualizar" onclick="openModal('view', {{ json_encode([
                        'name' => $vendedor->name,
                        'email' => $vendedor->email,
                        'telefone' => $vendedor->vendedor?->telefone ?? 'Não informado',
                        'comissao' => $vendedor->vendedor?->comissao ?? 0,
                        'meta_mensal' => $vendedor->vendedor?->meta_mensal ?? 0,
                        'status' => $vendedor->status,
                        'created_at' => $vendedor->created_at->format('d/m/Y H:i'),
                    ]) }})">👁️</button>
                    <button class="action-btn" title="Editar" onclick="openModal('edit', {{ json_encode([
                        'id' => $vendedor->id,
                        'name' => $vendedor->name,
                        'email' => $vendedor->email,
                        'telefone' => $vendedor->vendedor?->telefone ?? '',
                        'comissao' => $vendedor->vendedor?->comissao ?? 0,
                        'meta_mensal' => $vendedor->vendedor?->meta_mensal ?? 0,
                        'status' => $vendedor->status,
                    ]) }})">✏️</button>
                    <form method="POST" action="{{ route('master.vendedores.toggle', $vendedor->id) }}" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        @if($vendedor->status === 'ativo')
                            <button type="submit" class="action-btn danger" title="Inativar" onclick="return confirm('Deseja realmente inativar este vendedor?')">🚫</button>
                        @else
                            <button type="submit" class="action-btn success" title="Reativar" onclick="return confirm('Deseja reativar este vendedor?')">✅</button>
                        @endif
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding: 80px 20px; text-align: center;">
        <div style="background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <svg style="width: 40px; height: 40px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <h3 style="color: var(--text-main); font-size: 1.25rem; font-weight: 600; margin-bottom: 8px;">Nenhum vendedor encontrado</h3>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Nenhum vendedor cadastrado até o momento.</p>
    </div>
    @endif
</div>

<!-- ========== MODAL: Criar Vendedor ========== -->
<div class="modal-overlay" id="createModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Cadastrar Novo Vendedor</h2>
            <button class="close-modal" onclick="closeAllModals()">&times;</button>
        </div>
        <form action="{{ route('master.vendedores.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="name" required placeholder="Ex: João da Silva">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>E-mail (Acesso)</label>
                    <input type="email" name="email" required placeholder="vendedor@basileiavendas.com">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" placeholder="(11) 99999-9999">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Senha Provisória</label>
                    <input type="text" name="password" required value="Basileia@123">
                </div>
                <div class="form-group">
                    <label>Status Inicial</label>
                    <select name="status">
                        <option value="ativo" selected>Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>
            <div class="form-row" style="background: rgba(88, 28, 135, 0.03); padding: 16px; border-radius: 8px; border: 1px dashed rgba(88, 28, 135, 0.2); margin-top: 8px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--primary);">Comissão Padrão (%)</label>
                    <input type="number" step="0.1" name="comissao" required placeholder="10.0">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--primary);">Meta Mensal (R$)</label>
                    <input type="number" step="100" name="meta_mensal" placeholder="15000.00">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAllModals()">Cancelar</button>
                <button type="submit" class="btn-primary">Registrar Vendedor</button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL: Editar Vendedor ========== -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Editar Vendedor</h2>
            <button class="close-modal" onclick="closeAllModals()">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="name" id="editName" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>E-mail</label>
                    <input type="email" name="email" id="editEmail" required>
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" id="editTelefone">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nova Senha (deixe vazio para manter)</label>
                    <input type="text" name="password" placeholder="Deixe vazio para não alterar">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
            </div>
            <div class="form-row" style="background: rgba(88, 28, 135, 0.03); padding: 16px; border-radius: 8px; border: 1px dashed rgba(88, 28, 135, 0.2); margin-top: 8px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--primary);">Comissão (%)</label>
                    <input type="number" step="0.1" name="comissao" id="editComissao" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="color: var(--primary);">Meta Mensal (R$)</label>
                    <input type="number" step="100" name="meta_mensal" id="editMeta">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAllModals()">Cancelar</button>
                <button type="submit" class="btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL: Visualizar Vendedor ========== -->
<div class="modal-overlay" id="viewModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Detalhes do Vendedor</h2>
            <button class="close-modal" onclick="closeAllModals()">&times;</button>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><label>Nome</label><div class="value" id="viewName"></div></div>
            <div class="detail-item"><label>E-mail</label><div class="value" id="viewEmail"></div></div>
            <div class="detail-item"><label>Telefone</label><div class="value" id="viewTelefone"></div></div>
            <div class="detail-item"><label>Status</label><div class="value" id="viewStatus"></div></div>
            <div class="detail-item"><label>Comissão</label><div class="value" id="viewComissao"></div></div>
            <div class="detail-item"><label>Meta Mensal</label><div class="value" id="viewMeta"></div></div>
            <div class="detail-item"><label>Cadastrado em</label><div class="value" id="viewCreated"></div></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeAllModals()">Fechar</button>
        </div>
    </div>
</div>

<script>
    function closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    }

    function openModal(type, data = null) {
        closeAllModals();

        if (type === 'create') {
            document.getElementById('createModal').style.display = 'flex';
        }

        if (type === 'edit' && data) {
            const form = document.getElementById('editForm');
            form.action = '/master/vendedores/' + data.id;
            document.getElementById('editName').value = data.name;
            document.getElementById('editEmail').value = data.email;
            document.getElementById('editTelefone').value = data.telefone;
            document.getElementById('editComissao').value = data.comissao;
            document.getElementById('editMeta').value = data.meta_mensal;
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editModal').style.display = 'flex';
        }

        if (type === 'view' && data) {
            document.getElementById('viewName').textContent = data.name;
            document.getElementById('viewEmail').textContent = data.email;
            document.getElementById('viewTelefone').textContent = data.telefone;
            document.getElementById('viewStatus').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            document.getElementById('viewComissao').textContent = data.comissao + '%';
            document.getElementById('viewMeta').textContent = 'R$ ' + parseFloat(data.meta_mensal).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('viewCreated').textContent = data.created_at;
            document.getElementById('viewModal').style.display = 'flex';
        }
    }

    // Fechar modais ao clicar fora ou pressionar Esc
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeAllModals();
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // Filtro de busca e status
    function filterTable() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('.vendedor-row');

        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const telefone = row.dataset.telefone;
            const rowStatus = row.dataset.status;

            const matchSearch = !search || name.includes(search) || email.includes(search) || telefone.includes(search);
            const matchStatus = !status || rowStatus === status;

            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }
</script>
@endsection
