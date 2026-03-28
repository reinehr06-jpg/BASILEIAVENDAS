@extends('layouts.checkout')

@section('title', 'Finalizar Registro - Basileia Vendas')

@section('content')
<div class="checkout-enterprise-grid">
    <!-- Coluna Esquerda: O Plano e Valor (Foco em Valor Percebido) -->
    <div class="checkout-left-area">
        <div class="content-left-padding">
            <span class="badge-premium mb-3">PLANO PROFISSIONAL ATIVADO</span>
            <h1 class="plan-title-main">
                {{ match($venda->tipo_negociacao ?? ($venda->plano ?? 'mensal')) {
                    'mensal'       => 'Plano Mensal',
                    'anual'        => 'Plano Anual Premium',
                    'anual_avista' => 'Plano Anual à Vista',
                    'anual_12x'    => 'Anual em 12 Parcelas',
                    default        => 'Assinatura Basileia'
                } }}
            </h1>
            
            <div class="price-container-main my-4">
                <span class="currency-tag">R$</span>
                <span class="price-big">{{ number_format($venda->valor, 2, ',', '.') }}</span>
                <div class="billing-cycle-info text-uppercase">
                   <i class="fas fa-sync-alt mr-1"></i> Cobrança {{ ($venda->tipo_negociacao ?? '') === 'mensal' ? 'Mensal' : 'Anual' }}
                </div>
            </div>

            @php
                $isAnual = str_contains($venda->tipo_negociacao ?? '', 'anual');
            @endphp
            
            @if($isAnual)
            <div class="promo-seal mb-4 animate-up">
                <i class="fas fa-sparkles mr-1"></i> Economia Exclusiva de Plano Anual
            </div>
            @endif

            <ul class="feature-checklist-premium">
                <li class="f-item">
                    <div class="f-icon-circle"><i class="fas fa-check"></i></div>
                    <div class="f-text-group">
                        <strong class="f-title">Gestão com IA Integrada</strong>
                        <p class="f-desc">IA aplicada para auxílio de solicitações da igreja.</p>
                    </div>
                </li>
                <li class="f-item">
                    <div class="f-icon-circle"><i class="fas fa-check"></i></div>
                    <div class="f-text-group">
                        <strong class="f-title">Automação de Cultos</strong>
                        <p class="f-desc">Lembretes e avisos 100% automáticos.</p>
                    </div>
                </li>
                <li class="f-item">
                    <div class="f-icon-circle"><i class="fas fa-check"></i></div>
                    <div class="f-text-group">
                        <strong class="f-title">Células e Eventos</strong>
                        <p class="f-desc">Controle total de presença, cursos e células.</p>
                    </div>
                </li>
            </ul>

            <div class="safe-seal-footer mt-auto pt-4 border-top">
                <div class="d-flex align-items-center mb-1">
                    <i class="fas fa-shield-check text-success fa-lg mr-2"></i>
                    <strong class="small text-dark font-weight-800">Criptografia Segura (SSL)</strong>
                </div>
                <p class="small text-muted mb-0">Seus dados estão protegidos sob os mais altos padrões de segurança bancária.</p>
            </div>
        </div>
    </div>

    <!-- Coluna Direita: O Pagamento (Foco em Conversão) -->
    <div class="checkout-right-area">
        <div class="content-right-padding">
            <h3 class="payment-header-title">Pagamento Seguro</h3>
            
            <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
                @csrf

                @php
                    $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
                @endphp

                {{-- Só exibe as abas se não houver restrição --}}
                @if(!isset($restritoMetodo))
                <div class="payment-method-selector mb-4">
                    <label class="p-item {{ $metodoAtual === 'credit_card' ? 'active' : '' }}" onclick="switchMode('credit_card', this)">
                        <input type="radio" name="payment_method" value="credit_card" {{ $metodoAtual === 'credit_card' ? 'checked' : '' }}>
                        <i class="fas fa-credit-card"></i> Cartão
                    </label>
                    <label class="p-item {{ $metodoAtual === 'pix' ? 'active' : '' }}" onclick="switchMode('pix', this)">
                        <input type="radio" name="payment_method" value="pix" {{ $metodoAtual === 'pix' ? 'checked' : '' }}>
                        <i class="fas fa-bolt"></i> PIX
                    </label>
                    <label class="p-item {{ $metodoAtual === 'boleto' ? 'active' : '' }}" onclick="switchMode('boleto', this)">
                        <input type="radio" name="payment_method" value="boleto" {{ $metodoAtual === 'boleto' ? 'checked' : '' }}>
                        <i class="fas fa-barcode"></i> Boleto
                    </label>
                </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
                    <div class="method-locked-pill mb-4 d-flex justify-content-between align-items-center">
                        <span class="small font-weight-800 text-muted text-uppercase">Pagamento via:</span>
                        <span class="btn-sm btn-outline-primary border-0 rounded-pill px-3 py-1 font-weight-800" style="background: rgba(76, 29, 149, 0.05);">
                            <i class="fas fa-{{ $restritoMetodo === 'credit_card' ? 'credit-card' : ($restritoMetodo === 'pix' ? 'bolt' : 'barcode') }} mr-1"></i>
                            {{ $restritoMetodo === 'credit_card' ? 'CARTÃO DE CRÉDITO' : ($restritoMetodo === 'pix' ? 'PIX INSTANTÂNEO' : 'BOLETO BANCÁRIO') }}
                        </span>
                    </div>
                @endif

                <div class="form-body-enterprise">
                    <div class="form-group-enterprise">
                        <label>E-mail de acesso ao painel</label>
                        <input type="email" class="form-control-enterprise bg-light" value="{{ $venda->email_cliente ?? ($venda->cliente->email ?? '') }}" readonly>
                        <p class="small text-muted mt-1">Este e-mail receberá suas credenciais de login.</p>
                    </div>

                    {{-- Seção: Cartão --}}
                    <div id="section-card" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
                        <div class="form-group-enterprise">
                            <label>Número do Cartão</label>
                            <div class="input-with-icon">
                                <input type="text" name="numero_cartao" class="form-control-enterprise" placeholder="0000 0000 0000 0000" oninput="formatCard(this)">
                                <i class="fas fa-credit-card icon-inside text-muted"></i>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-7">
                                <div class="form-group-enterprise">
                                    <label>Expiração (MM/AA)</label>
                                    <input type="text" name="expiry" class="form-control-enterprise text-center" placeholder="MM / AA" oninput="formatExpiry(this)">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group-enterprise">
                                    <label>CVC</label>
                                    <input type="text" name="cvv" class="form-control-enterprise text-center" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div class="form-group-enterprise">
                            <label>Nome Completo (Escrito no Cartão)</label>
                            <input type="text" name="nome_cartao" class="form-control-enterprise text-uppercase" placeholder="EX: FULANO DE TAL">
                        </div>
                    </div>

                    {{-- Informativo Pix/Boleto --}}
                    <div id="section-status" style="{{ $metodoAtual === 'credit_card' ? 'display:none' : 'display:block' }}">
                        <div class="payment-info-box mb-4">
                            <i class="fas fa-clock fa-lg mr-2 text-primary"></i>
                            <span id="hint-text">Seu código para pagamento será gerado imediatamente.</span>
                        </div>
                    </div>

                    <div class="form-group-enterprise">
                        <label>Documento do Pagador (CPF ou CNPJ)</label>
                        <input type="text" name="cpf_titular" class="form-control-enterprise" placeholder="000.000.000-00" oninput="formatDoc(this)" required>
                    </div>
                </div>

                <button type="submit" class="btn-checkout-enterprise" id="btn-submit-main">
                    Confirmar Assinatura - R$ {{ number_format($venda->valor, 2, ',', '.') }}
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .checkout-enterprise-grid { display: flex; flex-direction: row; min-height: 650px; }
    .checkout-left-area { width: 44%; background: #fdfdfe; padding: 60px 50px; border-right: 1px solid #f2f3f5; display: flex; flex-direction: column; }
    .checkout-right-area { width: 56%; padding: 60px 70px; background: #fff; }
    
    .badge-premium { display: inline-block; background: rgba(76, 29, 149, 0.06); color: var(--primary); font-size: 0.65rem; font-weight: 800; padding: 6px 14px; border-radius: 6px; letter-spacing: 1px; }
    .plan-title-main { font-size: 2.25rem; font-weight: 800; color: #111827; letter-spacing: -1px; margin-top: 10px; }
    
    .price-container-main { display: flex; align-items: baseline; gap: 4px; }
    .currency-tag { font-size: 1.25rem; font-weight: 700; color: #6b7280; }
    .price-big { font-size: 3.5rem; font-weight: 900; letter-spacing: -3px; color: #4C1D95; line-height: 1; }
    .billing-cycle-info { display: block; font-size: 0.75rem; font-weight: 800; color: #9ca3af; letter-spacing: 0.5px; margin-top: 8px; }

    .promo-seal { display: inline-block; background: #ecfdf5; color: #065f46; font-size: 0.8rem; font-weight: 700; padding: 10px 18px; border-radius: 10px; border: 1.5px solid #d1fae5; }
    
    .feature-checklist-premium { list-style: none; padding: 0; margin: 0; }
    .f-item { display: flex; gap: 16px; margin-bottom: 25px; align-items: flex-start; }
    .f-icon-circle { width: 22px; height: 22px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; flex-shrink: 0; margin-top: 3px; }
    .f-title { display: block; font-size: 0.95rem; font-weight: 800; color: #111827; line-height: 1.2; }
    .f-desc { font-size: 0.85rem; color: #6b7280; line-height: 1.4; margin-top: 2px; }

    .payment-header-title { font-size: 1.5rem; font-weight: 800; color: #111827; letter-spacing: -0.5px; }

    .payment-method-selector { display: flex; background: #f3f4f6; padding: 5px; border-radius: 14px; gap: 5px; }
    .p-item { flex: 1; text-align: center; padding: 12px 0; font-size: 0.8rem; font-weight: 800; color: #6b7280; border-radius: 10px; cursor: pointer; transition: all 0.2s; margin-bottom: 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .p-item.active { background: #fff; color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
    .p-item i { margin-right: 6px; font-size: 0.95rem; }
    .p-item input { display: none; }

    .form-group-enterprise { margin-bottom: 22px; }
    .form-group-enterprise label { display: block; font-size: 0.75rem; font-weight: 800; color: #4b5563; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control-enterprise { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 12px 16px; font-size: 1rem; font-weight: 500; transition: all 0.3s; background: #fff; }
    .form-control-enterprise:focus { border-color: var(--primary); box-shadow: 0 0 0 5px rgba(76, 29, 149, 0.08); outline: none; }
    
    .input-with-icon { position: relative; }
    .icon-inside { position: absolute; right: 15px; top: 16px; font-size: 1.15rem; }

    .payment-info-box { background: rgba(76, 29, 149, 0.04); color: var(--primary); font-size: 0.9rem; font-weight: 700; padding: 15px 20px; border-radius: 12px; border: 1.5px solid rgba(76, 29, 149, 0.08); display: flex; align-items: center; }

    .btn-checkout-enterprise { background: var(--primary-gradient); color: white; border: none; width: 100%; padding: 20px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; box-shadow: 0 15px 40px rgba(76, 29, 149, 0.3); transition: all 0.3s; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; }
    .btn-checkout-enterprise:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(76, 29, 149, 0.4); }

    @media (max-width: 992px) {
        .checkout-enterprise-grid { flex-direction: column; }
        .checkout-left-area, .checkout-right-area { width: 100%; border: none; padding: 40px 25px; }
        .checkout-left-area { order: 2; border-top: 1px solid #f2f3f5; }
        .checkout-right-area { order: 1; }
        .price-big { font-size: 3rem; }
    }
</style>

<script>
function switchMode(metodo, element) {
    document.querySelectorAll('.p-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    document.getElementById('section-card').style.display = metodo === 'credit_card' ? 'block' : 'none';
    document.getElementById('section-status').style.display = metodo === 'credit_card' ? 'none' : 'block';
    
    const hint = document.getElementById('hint-text');
    const btn = document.getElementById('btn-submit-main');
    const valor = "R$ {{ number_format($venda->valor, 2, ',', '.') }}";
    
    if (metodo === 'pix') {
        hint.innerText = "Um QR Code Pix dinâmico será gerado para liberação instantânea.";
        btn.innerHTML = '<i class="fas fa-bolt mr-2 text-warning"></i> Gerar QR Code PIX - ' + valor;
    } else if (metodo === 'boleto') {
        hint.innerText = "Um boleto bancário será gerado e enviado para o seu e-mail.";
        btn.innerHTML = '<i class="fas fa-barcode mr-2"></i> Gerar Boleto Bancário - ' + valor;
    } else {
        btn.innerHTML = 'Confirmar Assinatura - ' + valor;
    }
}

function formatCard(input) { let v = input.value.replace(/\D/g, '').substring(0, 16); input.value = v.replace(/(.{4})/g, '$1 ').trim(); }
function formatExpiry(input) { let v = input.value.replace(/\D/g, '').substring(0, 4); if (v.length > 2) v = v.substring(0, 2) + ' / ' + v.substring(2); input.value = v; }
function formatDoc(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) { v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'); }
    else { v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5'); }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit-main');
    btn.disabled = true;
    btn.style.opacity = '0.8';
    btn.innerHTML = '<i class="fas fa-sync fa-spin mr-2"></i> PROCESSANDO TRANSAÇÃO SEGURA...';
});
</script>
@endsection
