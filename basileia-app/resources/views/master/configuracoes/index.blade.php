@extends('layouts.app')
@section('title', 'Configurações do Sistema')

@section('content')
<style>
    /* ===== Layout de Abas ===== */
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .settings-wrapper { animation: fadeIn 0.4s ease-out; }
    
    .settings-container { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; display: flex; min-height: 600px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    /* Menu Lateral de Configurações */
    .settings-nav { width: 220px; background: #f8fafc; border-right: 1px solid var(--border); padding: 20px 0; }
    .settings-nav-item { display: flex; align-items: center; gap: 12px; padding: 14px 24px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 0.92rem; transition: 0.2s; border-left: 3px solid transparent; }
    .settings-nav-item:hover { color: var(--primary); background: white; }
    .settings-nav-item.active { color: var(--primary); background: white; border-left-color: var(--primary); }
    .settings-nav-item .icon { font-size: 1.1rem; }

    /* Área de Conteúdo */
    .settings-content { flex: 1; padding: 40px; }
    .tab-pane { display: none; max-width: 650px; }
    .tab-pane.active { display: block; animation: fadeIn 0.3s ease-in; }

    /* Elementos de Formulário */
    .section-title { font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-bottom: 24px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 11px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: white; outline: none; transition: 0.2s; }
    .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.08); }
    
    .btn-save { background: var(--primary); color: white; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s; margin-top: 12px; }
    .btn-save:hover { background: var(--primary-hover); transform: translateY(-1px); }

    /* Alert */
    .alert { padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 600; font-size: 0.9rem; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }

    /* Header info */
    .tab-header-info { margin-bottom: 30px; }
    .tab-header-info h3 { font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px; }
    .tab-header-info p { color: var(--text-muted); font-size: 0.9rem; }
</style>

<div class="settings-wrapper">
    <div class="page-header" style="margin-bottom: 24px;">
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">🛡️ Administrador</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="settings-container">
        <!-- Menu de Abas -->
        <nav class="settings-nav">
            <a href="#" class="settings-nav-item active" onclick="switchTab('geral', this)">
                <span class="icon">⚙️</span> Geral
            </a>
            <a href="#" class="settings-nav-item" onclick="switchTab('integracoes', this)">
                <span class="icon">🔌</span> Integrações
            </a>
            <a href="#" class="settings-nav-item" onclick="switchTab('seguranca', this)">
                <span class="icon">🔒</span> Segurança
            </a>
        </nav>

        <!-- Conteúdo das Abas -->
        <div class="settings-content">
            
            <!-- ABA GERAL -->
            <div id="tab-geral" class="tab-pane active">
                <div class="tab-header-info">
                    <h3>Configurações Gerais</h3>
                    <p>Gerencie as preferências globais da plataforma Basiléia.</p>
                </div>
                
                <form action="#" method="POST">
                    <div class="form-group">
                        <label>Nome da Operação</label>
                        <input type="text" value="Basiléia Vendas" disabled>
                    </div>
                    <div class="form-group">
                        <label>E-mail de Suporte</label>
                        <input type="email" value="suporte@basileia.com.br">
                    </div>
                    <div class="form-group">
                        <label>Fuso Horário</label>
                        <select>
                            <option value="America/Sao_Paulo">Brasília (GMT-3)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-save" disabled>Salvar Alterações</button>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">* Algumas configurações gerais estão travadas no momento.</p>
                </form>
            </div>

            <!-- ABA INTEGRAÇÕES (ASAAS) -->
            <div id="tab-integracoes" class="tab-pane">
                <div class="tab-header-info">
                    <h3>Integrações Externas</h3>
                    <p>Configure gateways de pagamento e serviços de terceiros.</p>
                </div>

                <form action="{{ route('master.configuracoes.integracoes.update') }}" method="POST">
                    @csrf
                    <div style="background: #f8fafc; border: 1px solid var(--border); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
                        <div style="font-weight: 800; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <img src="https://asaas.com/favicon.ico" width="20" height="20" style="border-radius: 4px;"> Asaas - Gateway de Pagamento
                        </div>

                        <div class="form-group">
                            <label>Ambiente</label>
                            <select name="asaas_environment">
                                <option value="sandbox" {{ (\App\Models\Setting::get('asaas_environment') == 'sandbox') ? 'selected' : '' }}>🧪 Sandbox (Testes)</option>
                                <option value="production" {{ (\App\Models\Setting::get('asaas_environment') == 'production') ? 'selected' : '' }}>🚀 Produção (Real)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>API Key (Access Token)</label>
                            <input type="password" name="asaas_api_key" value="{{ \App\Models\Setting::get('asaas_api_key') }}" placeholder="Insira seu token do Asaas">
                        </div>

                        <div class="form-group">
                            <label>Webhook Token</label>
                            <input type="password" name="asaas_webhook_token" value="{{ \App\Models\Setting::get('asaas_webhook_token') }}" placeholder="Token de segurança do webhook">
                            <small style="color: var(--text-muted); font-size: 0.78rem;">Configure seu webhook no Asaas para: <code>{{ url('/api/asaas/webhook') }}</code></small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-save">💾 Salvar Integração</button>
                </form>
            </div>

            <!-- ABA SEGURANÇA -->
            <div id="tab-seguranca" class="tab-pane">
                <div class="tab-header-info">
                    <h3>Segurança e Acesso</h3>
                    <p>Controle de autenticação e logs do sistema.</p>
                </div>
                <div style="padding: 40px; border: 2px dashed var(--border); border-radius: 12px; text-align: center; color: var(--text-muted);">
                    As configurações de segurança avançadas estarão disponíveis na próxima versão.
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function switchTab(tabId, el) {
        // Remover active de todos
        document.querySelectorAll('.settings-nav-item').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        // Ativar selecionado
        el.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
        
        // Prevenir scroll top no clique do '#'
        event.preventDefault();
    }

    // Auto-switch se vier da rota de sucesso das integrações
    @if(session('success') && str_contains(session('success'), 'integração'))
        document.addEventListener('DOMContentLoaded', function() {
            switchTab('integracoes', document.querySelector('[onclick*="integracoes"]'));
        });
    @endif
</script>

@endsection
