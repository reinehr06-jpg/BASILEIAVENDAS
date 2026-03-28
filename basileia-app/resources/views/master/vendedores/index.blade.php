@extends('layouts.app')
@section('title', 'Vendedores')

@section('content')
<style>
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
        color: var(--text-secondary);
        margin-left: 4px;
    }
    .action-btn:hover { background: var(--bg); color: var(--primary); border-color: var(--primary); }
    .action-btn.danger { color: var(--danger); border-color: var(--danger-light); }
    .action-btn.danger:hover { background: var(--danger-light); }
    .action-btn.success { color: var(--success); border-color: var(--success-light); }
    .action-btn.success:hover { background: var(--success-light); }

    .form-section {
        background: var(--bg);
        border-radius: var(--radius-md);
        padding: 18px 20px;
        margin-bottom: 16px;
        border-left: 3px solid var(--primary);
    }
    .form-section-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--primary);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .form-section-title i {
        font-size: 0.9rem;
    }

    .commission-highlight {
        background: linear-gradient(135deg, rgba(76,29,149,0.04), rgba(76,29,149,0.08));
        border: 1px dashed rgba(76,29,149,0.2);
        border-radius: var(--radius-md);
        padding: 16px 18px;
        margin-top: 8px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .info-item {
        padding: 10px 14px;
        background: var(--bg);
        border-radius: var(--radius-sm);
    }
    .info-item label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .info-item .value {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .vendedor-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="page-header">
    <div>
        <h2><i class="fas fa-users" style="margin-right: 8px;"></i>Gestão de Vendedores</h2>
        <p>Cadastre e gerencie sua equipe de vendas.</p>
    </div>
    <button class="btn btn-primary" onclick="BasileiaModal.open('createModal')">
        <i class="fas fa-plus"></i> Novo Vendedor
    </button>
</div>

<div class="filters-bar">
    <div style="position: relative; flex-grow: 1;">
        <i class="fas fa-magnifying-glass" style="position: absolute; left: 14px; top: 11px; color: var(--text-muted);"></i>
        <input type="text" class="search-input" id="searchInput" style="padding-left: 40px;" placeholder="Buscar por nome, e-mail ou telefone..." oninput="filterTable()">
    </div>
    <select class="filter-select" id="statusFilter" onchange="filterTable()">
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
                <th>Vendedor</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Comissão</th>
                <th>Split</th>
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
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="vendedor-avatar">{{ strtoupper(substr($vendedor->name, 0, 1)) }}</div>
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $vendedor->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $vendedor->vendedor?->telefone ?? 'Sem telefone' }}</div>
                        </div>
                    </div>
                </td>
                <td style="color: var(--text-secondary); font-size: 0.875rem;">{{ $vendedor->email }}</td>
                <td>
                    <span class="badge {{ $vendedor->perfil === 'gestor' ? 'badge-info' : 'badge-secondary' }}">
                        {{ $vendedor->perfil === 'gestor' ? 'Gestor' : 'Vendedor' }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-primary">{{ $vendedor->vendedor?->comissao ?? '0' }}%</span>
                </td>
                <td>
                    @if($vendedor->vendedor?->split_ativo)
                        @php $walletStatus = $vendedor->vendedor?->wallet_status ?? 'pendente'; @endphp
                        @if($walletStatus === 'validado')
                            <span class="badge badge-success">Validado</span>
                        @elseif($walletStatus === 'erro')
                            <span class="badge badge-danger">Erro</span>
                        @else
                            <span class="badge badge-warning">Pendente</span>
                        @endif
                    @else
                        <span class="badge badge-secondary">Inativo</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $vendedor->status === 'ativo' ? 'success' : ($vendedor->status === 'bloqueado' ? 'danger' : 'warning') }}">{{ ucfirst($vendedor->status) }}</span>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    <button class="action-btn" title="Visualizar" onclick='openViewModal({{ json_encode([
                        'id' => $vendedor->id,
                        'name' => $vendedor->name,
                        'email' => $vendedor->email,
                        'telefone' => $vendedor->vendedor?->telefone ?? 'Não informado',
                        'perfil' => $vendedor->perfil,
                        'comissao' => $vendedor->vendedor?->comissao ?? 0,
                        'comissao_inicial' => $vendedor->vendedor?->comissao_inicial ?? 0,
                        'comissao_recorrencia' => $vendedor->vendedor?->comissao_recorrencia ?? 0,
                        'meta_mensal' => $vendedor->vendedor?->meta_mensal ?? 0,
                        'status' => $vendedor->status,
                        'created_at' => $vendedor->created_at->format('d/m/Y H:i'),
                        'split_ativo' => $vendedor->vendedor?->split_ativo ?? false,
                        'wallet_status' => $vendedor->vendedor?->wallet_status ?? 'pendente',
                        'gestor_nome' => $vendedor->vendedor?->gestor?->name ?? 'Nenhum',
                    ]) }})'>
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn" title="Editar" onclick='openEditModal({{ json_encode([
                        'id' => $vendedor->id,
                        'name' => $vendedor->name,
                        'email' => $vendedor->email,
                        'telefone' => $vendedor->vendedor?->telefone ?? '',
                        'perfil' => $vendedor->perfil,
                        'comissao' => $vendedor->vendedor?->comissao ?? 0,
                        'comissao_inicial' => $vendedor->vendedor?->comissao_inicial ?? $vendedor->vendedor?->comissao ?? 0,
                        'comissao_recorrencia' => $vendedor->vendedor?->comissao_recorrencia ?? $vendedor->vendedor?->comissao ?? 0,
                        'meta_mensal' => $vendedor->vendedor?->meta_mensal ?? 0,
                        'status' => $vendedor->status,
                        'gestor_id' => $vendedor->vendedor?->gestor_id ?? '',
                        'comissao_gestor_primeira' => $vendedor->vendedor?->comissao_gestor_primeira ?? 0,
                        'comissao_gestor_recorrencia' => $vendedor->vendedor?->comissao_gestor_recorrencia ?? 0,
                        'split_ativo' => $vendedor->vendedor?->split_ativo ?? false,
                        'asaas_wallet_id' => $vendedor->vendedor?->asaas_wallet_id ?? '',
                        'tipo_split' => $vendedor->vendedor?->tipo_split ?? 'percentual',
                        'valor_split_inicial' => $vendedor->vendedor?->valor_split_inicial ?? 0,
                        'valor_split_recorrencia' => $vendedor->vendedor?->valor_split_recorrencia ?? 0,
                    ]) }})'>
                        <i class="fas fa-pen"></i>
                    </button>
                    @if($vendedor->status === 'ativo')
                    <form method="POST" action="{{ route('master.vendedores.toggle', $vendedor->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Inativar Vendedor', message: 'Deseja realmente inativar este vendedor?', type: 'warning', confirmText: 'Inativar', onConfirm: () => this.submit()});">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="action-btn danger" title="Inativar">
                            <i class="fas fa-ban"></i>
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('master.vendedores.toggle', $vendedor->id) }}" style="display: inline;" onsubmit="event.preventDefault(); BasileiaConfirm.show({title: 'Reativar Vendedor', message: 'Deseja reativar este vendedor?', type: 'success', confirmText: 'Reativar', onConfirm: () => this.submit()});">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="action-btn success" title="Reativar">
                            <i class="fas fa-circle-check"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-users"></i></div>
        <h3>Nenhum vendedor encontrado</h3>
        <p>Nenhum vendedor cadastrado até o momento.</p>
    </div>
    @endif
</div>

<!-- ========== MODAL: Criar Vendedor ========== -->
<div class="modal-overlay" id="createModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus" style="margin-right: 8px;"></i>Cadastrar Vendedor</h2>
            <button class="modal-close" onclick="BasileiaModal.close('createModal')">&times;</button>
        </div>
        <form action="{{ route('master.vendedores.store') }}" method="POST" class="modal-body">
            @csrf

            <!-- Seção 1: Dados Pessoais -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-user"></i> Dados Pessoais</div>
                <div class="form-group">
                    <label>Nome Completo <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: João da Silva">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>E-mail (Acesso) <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="vendedor@email.com">
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" class="form-control" placeholder="(11) 99999-9999">
                    </div>
                </div>
                <div class="form-group" style="padding: 10px; background: rgba(76,29,149,0.05); border: 1px dashed var(--primary); border-radius: var(--radius-sm);">
                    <label style="color: var(--primary); font-weight: 700;"><i class="fas fa-lock" style="margin-right: 5px;"></i> Senha Provisória</label>
                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin-top: 5px;">Basileia123</div>
                    <div class="field-hint" style="color: var(--text-secondary); margin-top: 5px;">O vendedor será <b>obrigado</b> a trocar esta senha no primeiro acesso.</div>
                </div>
            </div>

            <!-- Seção 2: Função e Equipe -->
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-user-tag"></i> Função e Equipe</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Perfil <span class="required">*</span></label>
                        <select name="perfil" id="createPerfil" class="form-control" onchange="toggleGestorFields()">
                            <option value="vendedor" selected>Vendedor</option>
                            <option value="gestor">Gestor de Equipe</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" id="createGestorRow">
                    <label>Gestor Responsável</label>
                    <select name="gestor_id" class="form-control">
                        <option value="">Nenhum (equipe do Admin)</option>
                        @foreach($gestores ?? [] as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Seção 3: Comissões -->
            <div class="form-section" id="vendedorComissaoSection">
                <div class="form-section-title"><i class="fas fa-hand-holding-dollar"></i> Comissões do Vendedor</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Comissão Inicial (%) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="comissao_inicial" class="form-control" required placeholder="10.00">
                        <div class="field-hint">% sobre o valor na primeira venda.</div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Comissão Recorrência (%) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="comissao_recorrencia" class="form-control" required placeholder="5.00">
                        <div class="field-hint">% sobre o valor em renovações.</div>
                    </div>
                </div>
            </div>

            <!-- Seção 3B: Comissões do Gestor (só para perfil gestor) -->
            <div class="form-section" id="gestorComissaoSection" style="display: none;">
                <div class="form-section-title"><i class="fas fa-user-tie"></i> Comissões do Gestor</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Comissão Gestor - 1ª Venda (%)</label>
                        <input type="number" step="0.01" name="comissao_gestor_primeira" class="form-control" placeholder="3.00">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Comissão Gestor - Recorrência (%)</label>
                        <input type="number" step="0.01" name="comissao_gestor_recorrencia" class="form-control" placeholder="1.00">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('createModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Cadastrar Vendedor</button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL: Editar Vendedor ========== -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-pen" style="margin-right: 8px;"></i>Editar Vendedor</h2>
            <button class="modal-close" onclick="BasileiaModal.close('editModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding-top: 0;">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('edit-tab-dados', this)">📋 Dados Gerais</button>
                <button class="tab-btn" onclick="switchTab('edit-tab-comissoes', this)">💰 Comissões</button>
                <button class="tab-btn" onclick="switchTab('edit-tab-split', this)">🔗 Split Asaas</button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <!-- Aba: Dados Gerais -->
                <div id="edit-tab-dados" class="tab-content active">
                    <div class="form-group">
                        <label>Nome Completo <span class="required">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>E-mail <span class="required">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="editTelefone" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nova Senha</label>
                            <input type="text" name="password" class="form-control" placeholder="Deixe vazio para manter a atual">
                            <div class="field-hint">Preencha apenas se deseja alterar.</div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="editStatus" class="form-control">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Perfil <span class="required">*</span></label>
                            <select name="perfil" id="editPerfil" class="form-control">
                                <option value="vendedor">Vendedor</option>
                                <option value="gestor">Gestor de Equipe</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Gestor Responsável</label>
                            <select name="gestor_id" id="editGestor" class="form-control">
                                <option value="">Nenhum (equipe do Admin)</option>
                                @foreach($gestores ?? [] as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Aba: Comissões -->
                <div id="edit-tab-comissoes" class="tab-content">
                    <div class="form-section" style="margin-top: 12px;">
                        <div class="form-section-title"><i class="fas fa-chart-bar"></i> Comissões do Vendedor</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Comissão Inicial (%) <span class="required">*</span></label>
                                <input type="number" step="0.01" name="comissao_inicial" id="editComissaoInicial" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Comissão Recorrência (%) <span class="required">*</span></label>
                                <input type="number" step="0.01" name="comissao_recorrencia" id="editComissaoRecorrencia" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="form-section-title"><i class="fas fa-user-tie"></i> Comissões do Gestor</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Gestor - 1ª Venda (R$)</label>
                                <input type="number" step="0.01" name="comissao_gestor_primeira" id="editComissaoGestorPrimeira" class="form-control">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Gestor - Recorrência (R$)</label>
                                <input type="number" step="0.01" name="comissao_gestor_recorrencia" id="editComissaoGestorRecorrencia" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aba: Split Asaas -->
                <div id="edit-tab-split" class="tab-content">
                    <div class="form-section" style="margin-top: 12px;">
                        <div class="form-section-title"><i class="fas fa-link"></i> Configuração de Split</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Wallet ID (Asaas)</label>
                                <input type="text" name="asaas_wallet_id" id="editWalletId" class="form-control" placeholder="ID da wallet no Asaas">
                            </div>
                            <div class="form-group">
                                <label>Tipo de Split</label>
                                <select name="tipo_split" id="editTipoSplit" class="form-control">
                                    <option value="percentual">Percentual (%)</option>
                                    <option value="fixo">Valor Fixo (R$)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Valor Split - Inicial</label>
                                <input type="number" step="0.01" name="valor_split_inicial" id="editSplitInicial" class="form-control" placeholder="0.00">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Valor Split - Recorrência</label>
                                <input type="number" step="0.01" name="valor_split_recorrencia" id="editSplitRecorrencia" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('editModal')">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== MODAL: Visualizar Vendedor ========== -->
<div class="modal-overlay" id="viewModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-user" style="margin-right: 8px;"></i>Detalhes do Vendedor</h2>
            <button class="modal-close" onclick="BasileiaModal.close('viewModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Nome</label>
                    <div class="value" id="viewName"></div>
                </div>
                <div class="info-item">
                    <label>E-mail</label>
                    <div class="value" id="viewEmail" style="font-weight: 600;"></div>
                </div>
                <div class="info-item">
                    <label>Telefone</label>
                    <div class="value" id="viewTelefone" style="font-weight: 600;"></div>
                </div>
                <div class="info-item">
                    <label>Perfil</label>
                    <div class="value" id="viewPerfil"></div>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <div id="viewStatus"></div>
                </div>
                <div class="info-item">
                    <label>Gestor</label>
                    <div class="value" id="viewGestor" style="font-weight: 600;"></div>
                </div>
                 <div class="info-item">
                     <label>Comissão Inicial (Venda)</label>
                     <div class="value text-primary" id="viewComissaoInicial"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Recorrência (Venda)</label>
                     <div class="value text-primary" id="viewComissaoRecorrencia"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Gestor (1ª Venda)</label>
                     <div class="value text-primary" id="viewComissaoGestorPrimeira"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Gestor (Recorrência)</label>
                     <div class="value text-primary" id="viewComissaoGestorRecorrencia"></div>
                 </div>
                 <div class="info-item">
                     <label>Meta Mensal</label>
                     <div class="value text-primary" id="viewMeta"></div>
                 </div>
                 <div class="info-item">
                     <label>Comissão Recorrência (Venda)</label>
                     <div class="value text-primary" id="viewComissaoRecorrencia"></div>
                 </div>
                 <div class="info-item" id="gestorComissoesContainer">
                     <label>Comissão Gestor (1ª Venda)</label>
                     <div class="value text-primary" id="viewComissaoGestorPrimeira"></div>
                 </div>
                 <div class="info-item" id="gestorComissoesContainer2">
                     <label>Comissão Gestor (Recorrência)</label>
                     <div class="value text-primary" id="viewComissaoGestorRecorrencia"></div>
                 </div>
                <div class="info-item">
                    <label>Meta Mensal</label>
                    <div class="value text-primary" id="viewMeta"></div>
                </div>
                <div class="info-item">
                    <label>Cadastrado em</label>
                    <div class="value" id="viewCreated" style="font-weight: 600; color: var(--text-secondary);"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="BasileiaModal.close('viewModal')">Fechar</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleGestorFields() {
        var perfil = document.getElementById('createPerfil').value;
        document.getElementById('createGestorRow').style.display = perfil === 'vendedor' ? 'block' : 'none';
        
        // Always show vendor commission section (both vendedor and gestor can earn sales commission)
        document.getElementById('vendedorComissaoSection').style.display = 'block';
        
        // Only show manager commission section when creating a manager
        document.getElementById('gestorComissaoSection').style.display = perfil === 'gestor' ? 'block' : 'none';
    }

    function openViewModal(data) {
        document.getElementById('viewName').textContent = data.name;
        document.getElementById('viewEmail').textContent = data.email;
        document.getElementById('viewTelefone').textContent = data.telefone;
        document.getElementById('viewPerfil').innerHTML = data.perfil === 'gestor'
            ? '<span class="badge badge-info">Gestor</span>'
            : '<span class="badge badge-secondary">Vendedor</span>';
        document.getElementById('viewStatus').innerHTML = '<span class="badge badge-' + (data.status === 'ativo' ? 'success' : (data.status === 'bloqueado' ? 'danger' : 'warning')) + '">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span>';
        document.getElementById('viewGestor').textContent = data.gestor_nome || 'Nenhum';
         document.getElementById('viewComissaoInicial').textContent = parseFloat(data.comissao_inicial || 0).toFixed(1) + '%';
         document.getElementById('viewComissaoRecorrencia').textContent = parseFloat(data.comissao_recorrencia || 0).toFixed(1) + '%';
         document.getElementById('viewComissaoGestorPrimeira').textContent = parseFloat(data.comissao_gestor_primeira || 0).toFixed(1) + '%';
         document.getElementById('viewComissaoGestorRecorrencia').textContent = parseFloat(data.comissao_gestor_recorrencia || 0).toFixed(1) + '%';
         document.getElementById('viewMeta').textContent = 'R$ ' + parseFloat(data.meta_mensal || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2});
         document.getElementById('viewCreated').textContent = data.created_at;
        BasileiaModal.open('viewModal');
    }

    function openEditModal(data) {
        var form = document.getElementById('editForm');
        form.action = '/master/vendedores/' + data.id;
        document.getElementById('editName').value = data.name;
        document.getElementById('editEmail').value = data.email;
        document.getElementById('editTelefone').value = data.telefone;
        document.getElementById('editStatus').value = data.status;
        document.getElementById('editPerfil').value = data.perfil || 'vendedor';
        document.getElementById('editGestor').value = data.gestor_id || '';
        document.getElementById('editComissaoInicial').value = data.comissao_inicial || data.comissao || 0;
        document.getElementById('editComissaoRecorrencia').value = data.comissao_recorrencia || data.comissao || 0;
        document.getElementById('editComissaoGestorPrimeira').value = data.comissao_gestor_primeira || 0;
        document.getElementById('editComissaoGestorRecorrencia').value = data.comissao_gestor_recorrencia || 0;
        document.getElementById('editWalletId').value = data.asaas_wallet_id || '';
        document.getElementById('editTipoSplit').value = data.tipo_split || 'percentual';
        document.getElementById('editSplitInicial').value = data.valor_split_inicial || 0;
        document.getElementById('editSplitRecorrencia').value = data.valor_split_recorrencia || 0;

        // Reset to first tab
        var firstTab = document.querySelector('#editModal .tab-btn');
        document.querySelectorAll('#editModal .tab-content').forEach(function(c) { c.classList.remove('active'); });
        document.querySelectorAll('#editModal .tab-btn').forEach(function(b) { b.classList.remove('active'); });
        firstTab.classList.add('active');
        document.getElementById('edit-tab-dados').classList.add('active');

        // Always show vendor commissions section (both vendedor and gestor can earn sales commission)
        document.getElementById('edit-tab-comissoes').style.display = 'block';

        BasileiaModal.open('editModal');
    }

    function filterTable() {
        var search = document.getElementById('searchInput').value.toLowerCase();
        var status = document.getElementById('statusFilter').value;
        document.querySelectorAll('.vendedor-row').forEach(function(row) {
            var matchSearch = !search || row.dataset.name.includes(search) || row.dataset.email.includes(search) || row.dataset.telefone.includes(search);
            var matchStatus = !status || row.dataset.status === status;
            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }
</script>
@endsection
