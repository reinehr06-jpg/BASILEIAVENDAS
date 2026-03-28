@extends('layouts.checkout')

@section('title', 'Finalizar Registro - Basileia Vendas')

@section('content')
<div class="checkout-elite-grid">
    <!-- Lado Esquerdo: Imersão Basiléia (Roxo) -->
    <div class="left-pane-elite">
        <div class="pane-inner">
            <div class="elite-logo-wrapper">
                <img src="/assets/img/logo_oficial.png" alt="Basiléia" style="max-width: 170px; filter: brightness(0) invert(1);">
            </div>
            
            <div class="elite-badge mt-4">
                PLANO PROFISSIONAL ATIVADO
            </div>

            <h1 class="plan-name-elite mt-3">Plano Mensal</h1>
            
            <div class="price-row-elite mt-1">
                <span class="currency">R$</span>
                <span class="value">{{ number_format($venda->valor, 2, ',', '.') }}</span>
                <span class="cycle ml-2">COBRANÇA MENSAL</span>
            </div>

            <ul class="feature-list-elite mt-5">
                <li class="f-item-elite">
                    <div class="f-check"><i class="fas fa-check"></i></div>
                    <div class="f-text">
                        <strong>Gestão com IA Integrada</strong>
                        <p>Aplicação para solicitações da igreja.</p>
                    </div>
                </li>
                <li class="f-item-elite">
                    <div class="f-check"><i class="fas fa-check"></i></div>
                    <div class="f-text">
                        <strong>Automação de Cultos</strong>
                        <p>Lembretes e avisos 100% automáticos.</p>
                    </div>
                </li>
                <li class="f-item-elite">
                    <div class="f-check"><i class="fas fa-check"></i></div>
                    <div class="f-text">
                        <strong>Células e Eventos</strong>
                        <p>Controle total de presença, cursos e células.</p>
                    </div>
                </li>
            </ul>

            <div class="security-banner-elite mt-5">
                <div class="s-icon"><i class="fas fa-shield-check"></i></div>
                <div class="s-text">
                    <strong>Pagamento 100% Seguro</strong>
                    <p>Seus dados são protegidos por criptografia SSL.</p>
                </div>
            </div>

            <div class="brand-footer-elite mt-auto pt-4">
                <div class="d-flex align-items-center opacity-75">
                    <img src="https://logodownload.org/wp-content/uploads/2016/10/visa-logo-1.png" style="height: 10px; margin-right: 15px;">
                    <img src="https://logodownload.org/wp-content/uploads/2014/07/mastercard-logo-1.png" style="height: 18px; margin-right: 15px;">
                    <img src="https://logodownload.org/wp-content/uploads/2015/05/amex-logo-american-express-1.png" style="height: 14px; margin-right: 15px;">
                    <img src="https://logodownload.org/wp-content/uploads/2015/02/elo-logo-1.png" style="height: 14px;">
                </div>
                <div class="mt-4 small opacity-50">&copy; {{ date('Y') }} Basileia Vendas - Enterprise Cloud Operations</div>
            </div>
        </div>
    </div>

    <!-- Lado Direito: Formulário Minimalista (Branco) -->
    <div class="right-pane-elite">
        <div class="pane-inner">
            <h2 class="form-title-elite">Pagamento Seguro</h2>
            
            <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
                @csrf

                @php
                    $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
                @endphp

                <div class="d-flex align-items-center mb-4 mt-2">
                    <span class="label-muted mr-3">PAGAMENTO VIA:</span>
                    <div class="method-pill-elite">
                        <i class="fas fa-credit-card mr-1"></i> CARTÃO DE CRÉDITO
                    </div>
                </div>

                {{-- Só exibe se não houver restrição no banco ou URL --}}
                @if(!isset($restritoMetodo))
                <div class="payment-tabs-minimal mb-4">
                    <label class="t-btn {{ $metodoAtual === 'credit_card' ? 'active' : '' }}" onclick="updateMode('credit_card', this)">
                        <input type="radio" name="payment_method" value="credit_card" checked> CARTÃO
                    </label>
                    <label class="t-btn {{ $metodoAtual === 'pix' ? 'active' : '' }}" onclick="updateMode('pix', this)">
                        <input type="radio" name="payment_method" value="pix"> PIX
                    </label>
                    <label class="t-btn {{ $metodoAtual === 'boleto' ? 'active' : '' }}" onclick="updateMode('boleto', this)">
                        <input type="radio" name="payment_method" value="boleto"> BOLETO
                    </label>
                </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
                @endif

                <div class="form-body-elite">
                    <div class="group-lite">
                        <label>E-MAIL DE ACESSO AO PAINEL</label>
                        <input type="email" class="control-lite" value="{{ $venda->email_cliente ?? ($venda->cliente->email ?? '') }}" disabled>
                        <span class="hint-lite">Este e-mail receberá suas credenciais de login.</span>
                    </div>

                    <div id="box-card-elite" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
                        <div class="group-lite">
                            <label>NÚMERO DO CARTÃO</label>
                            <div class="input-icon-elite">
                                <input type="text" name="numero_cartao" class="control-lite" placeholder="0000 0000 0000 0000" oninput="formatCard(this)">
                                <i class="fas fa-credit-card text-muted"></i>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-7">
                                <div class="group-lite">
                                    <label>EXPIRAÇÃO</label>
                                    <input type="text" name="expiry" class="control-lite text-center" placeholder="01 / 26" oninput="formatExp(this)">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="group-lite">
                                    <label>CVC</label>
                                    <input type="text" name="cvv" class="control-lite text-center" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div class="group-lite">
                            <label>NOME COMPLETO (ESCRITO NO CARTÃO)</label>
                            <input type="text" name="nome_cartao" class="control-lite" placeholder="EDSON REINEHR">
                        </div>
                    </div>

                    <div id="box-info-elite" style="{{ $metodoAtual === 'credit_card' ? 'display:none' : 'display:block' }}">
                        <div class="alert alert-light border border-dashed text-center py-4 rounded-lg mb-3">
                            <i class="fas fa-clock fa-spin mr-2 text-primary"></i>
                            <span class="small font-weight-bold text-muted">Sessão de pagamento segura ativa...</span>
                        </div>
                    </div>

                    <div class="group-lite mb-4">
                        <label>DOCUMENTO DO PAGADOR (CPF ou CNPJ)</label>
                        <input type="text" name="cpf_titular" class="control-lite" placeholder="000.000.000-00" oninput="formatDoc(this)" required>
                    </div>
                </div>

                <button type="submit" class="btn-elite-action" id="btn-finalizar-elite">
                    Assinar Plano por R$ {{ number_format($venda->valor, 2, ',', '.') }}
                </button>

                <div class="text-center mt-4">
                    <span class="small font-weight-bold text-success">
                        <i class="fas fa-lock mr-1"></i> Pagamento 100% Seguro
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .checkout-elite-grid { display: flex; flex-direction: row; min-height: 750px; }
    
    /* Painel Esquerdo: Identidade */
    .left-pane-elite { width: 42%; background: #7C3AED; padding: 60px 45px; color: white; display: flex; flex-direction: column; }
    .elite-badge { display: inline-block; background: rgba(255,255,255,0.15); font-size: 0.65rem; font-weight: 800; padding: 5px 15px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.25); letter-spacing: 0.5px; }
    .plan-name-elite { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }
    .price-row-elite { display: flex; align-items: baseline; }
    .price-row-elite .currency { font-size: 1.1rem; font-weight: 700; opacity: 0.85; }
    .price-row-elite .value { font-size: 3.5rem; font-weight: 900; letter-spacing: -2px; margin: 0 4px; }
    .price-row-elite .cycle { font-size: 0.7rem; font-weight: 700; opacity: 0.7; border-left: 1.5px solid rgba(255,255,255,0.3); padding-left: 10px; }

    .feature-list-elite { list-style: none; padding: 0; }
    .f-item-elite { display: flex; gap: 14px; margin-bottom: 24px; align-items: flex-start; }
    .f-check { width: 24px; height: 24px; background: #10B981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0; transform: translateY(2px); }
    .f-text strong { font-size: 0.95rem; font-weight: 800; display: block; }
    .f-text p { font-size: 0.8rem; opacity: 0.8; margin-top: 1px; }

    .security-banner-elite { display: flex; gap: 15px; background: rgba(255,255,255,0.1); padding: 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15); align-items: center; }
    .s-icon { width: 34px; height: 34px; background: #10B981; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .s-text strong { font-size: 0.9rem; font-weight: 800; display: block; }
    .s-text p { font-size: 0.75rem; opacity: 0.8; margin: 0; }

    /* Painel Direito: Pagamento */
    .right-pane-elite { width: 58%; background: #ffffff; padding: 60px 80px; display: flex; flex-direction: column; }
    .form-title-elite { font-size: 1.6rem; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 25px; }
    .label-muted { font-size: 0.65rem; font-weight: 800; color: #6b7280; letter-spacing: 0.75px; }
    .method-pill-elite { background: #7C3AED; color: white; padding: 6px 15px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25); }

    .group-lite { margin-bottom: 22px; }
    .group-lite label { display: block; font-size: 0.65rem; font-weight: 800; color: #4B5563; margin-bottom: 8px; letter-spacing: 0.5px; }
    .control-lite { width: 100%; border: 1.25px solid #E5E7EB; border-radius: 8px; padding: 12px 16px; font-size: 0.95rem; font-weight: 500; transition: 0.2s; background: #F9FAFB; }
    .control-lite:focus { border-color: #7C3AED; background: #fff; outline: none; }
    .hint-lite { font-size: 0.75rem; color: #9CA3AF; margin-top: 5px; display: block; }
    
    .input-icon-elite { position: relative; }
    .input-icon-elite i { position: absolute; right: 15px; top: 15px; font-size: 1rem; }

    .btn-elite-action { background: #7C3AED; color: white; border: none; width: 100%; padding: 18px; border-radius: 12px; font-weight: 800; font-size: 1.05rem; box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3); transition: 0.3s; cursor: pointer; margin-top: 10px; }
    .btn-elite-action:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4); }

    .payment-tabs-minimal { display: flex; gap: 8px; }
    .t-btn { flex: 1; text-align: center; padding: 10px; border-radius: 8px; border: 1.5px solid #E5E7EB; font-size: 0.7rem; font-weight: 800; color: #6B7280; cursor: pointer; transition: 0.2s; }
    .t-btn.active { border-color: #7C3AED; color: #7C3AED; background: rgba(124, 58, 237, 0.03); }
    .t-btn input { display: none; }

    @media (max-width: 992px) {
        .checkout-elite-grid { flex-direction: column; }
        .left-pane-elite, .right-pane-elite { width: 100%; padding: 40px 25px; }
        .left-pane-elite { order: 2; border-top: 1px solid #eee; }
        .right-pane-elite { order: 1; padding-bottom: 50px; }
    }
</style>

<script>
function updateMode(metodo, element) {
    document.querySelectorAll('.t-btn').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    document.getElementById('box-card-elite').style.display = metodo === 'credit_card' ? 'block' : 'none';
    document.getElementById('box-info-elite').style.display = metodo === 'credit_card' ? 'none' : 'block';
    
    const btn = document.getElementById('btn-finalizar-elite');
    const valor = "R$ {{ number_format($venda->valor, 2, ',', '.') }}";
    if (metodo === 'pix') btn.innerText = "Gerar QR Code PIX - " + valor;
    else if (metodo === 'boleto') btn.innerText = "Gerar Boleto de " + valor;
    else btn.innerText = "Assinar Plano por " + valor;
}

function formatCard(input) { let v = input.value.replace(/\D/g, '').substring(0, 16); input.value = v.replace(/(.{4})/g, '$1 ').trim(); }
function formatExp(input) { let v = input.value.replace(/\D/g, '').substring(0, 4); if (v.length > 2) v = v.substring(0, 2) + ' / ' + v.substring(2); input.value = v; }
function formatDoc(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) { v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'); }
    else { v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5'); }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-finalizar-elite');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESSANDO...';
});
</script>
@endsection
