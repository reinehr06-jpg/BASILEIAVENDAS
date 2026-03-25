@extends('layouts.app')

@section('title', 'Configurações de Integrações')

@section('content')
<div class="integracoes-container">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card settings-card">
        <div class="card-header">
            <h2>Asaas - Gateway de Pagamento</h2>
            <p class="text-muted">Configure as credenciais de acesso para a API do Asaas e o ambiente de execução.</p>
        </div>

        <form action="{{ route('master.configuracoes.integracoes.update') }}" method="POST" class="settings-form">
            @csrf

            <div class="form-group">
                <label for="asaas_environment">Ambiente de Execução <span class="required">*</span></label>
                <select name="asaas_environment" id="asaas_environment" class="form-control" required>
                    <option value="sandbox" {{ $asaasEnvironment === 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                    <option value="production" {{ $asaasEnvironment === 'production' ? 'selected' : '' }}>Produção (Real)</option>
                </select>
                <small class="help-text">Define para qual URL as requisições financeiras serão enviadas.</small>
            </div>

            <div class="form-group">
                <label for="asaas_api_key">API Key (Asaas) <span class="required">*</span></label>
                <input type="password" name="asaas_api_key" id="asaas_api_key" class="form-control" value="{{ $asaasApiKey }}" required placeholder="Ex: $aact_YTU5YTE0M2M...">
                <small class="help-text">A chave de acesso gerada no painel do Asaas (Configurações > Integrações > Gerar API Key).</small>
            </div>

            <div class="form-group">
                <label for="asaas_webhook_token">Webhook Token (Asaas) <span class="required">*</span></label>
                <input type="password" name="asaas_webhook_token" id="asaas_webhook_token" class="form-control" value="{{ $asaasWebhookToken }}" required placeholder="Ex: 5b4c48...">
                <small class="help-text">O token de segurança usado para validar eventos de pagamento recebidos pelo webhook.</small>
            </div>

            <div class="form-actions border-top">
                <button type="submit" class="btn btn-primary">Salvar Configurações</button>
            </div>
        </form>
    </div>
</div>

<style>
    .integracoes-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .settings-card {
        padding: 0;
        overflow: hidden;
    }

    .card-header {
        background: #fafafa;
        padding: 24px 30px;
        border-bottom: 1px solid var(--border);
    }
    
    .card-header h2 {
        font-size: 1.25rem;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .settings-form {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(88, 28, 135, 0.1);
    }

    .help-text {
        display: block;
        margin-top: 6px;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .form-actions {
        margin-top: 32px;
        padding-top: 24px;
        display: flex;
        justify-content: flex-end;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: 0.2s;
        font-size: 1rem;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-weight: 500;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    
    .border-top {
        border-top: 1px solid var(--border);
    }
</style>
@endsection
