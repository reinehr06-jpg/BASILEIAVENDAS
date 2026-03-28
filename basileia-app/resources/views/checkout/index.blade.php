@extends('layouts.checkout')

@section('title', 'Finalizar Pagamento - Basileia Vendas')

@section('content')
<div class="checkout-main-grid">
    <!-- Lado Esquerdo: Valor e Diferenciais -->
    <div class="checkout-side-info">
        <div class="info-content">
            <h6 class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Você está assinando:</h6>
            <h3 class="font-weight-800 mb-0">
                {{ match($venda->tipo_negociacao ?? ($venda->plano ?? 'mensal')) {
                    'mensal'       => 'Plano Mensal',
                    'anual'        => 'Plano Anual Premium',
                    'anual_avista' => 'Plano Anual à Vista',
                    'anual_12x'    => 'Plano Anual em 12x',
                    default        => 'Assinatura Basileia'
                } }}
            </h3>
            
            <div class="display-price my-3 text-primary">
                R$ {{ number_format($venda->valor, 2, ',', '.') }}
                <small class="text-muted" style="font-size: 0.9rem; font-weight: 500;">/ {{ ($venda->tipo_negociacao ?? '') === 'mensal' ? 'mês' : 'ano' }}</small>
            </div>

            @php
                $isAnual = str_contains($venda->tipo_negociacao ?? '', 'anual');
            @endphp
            
            @if($isAnual)
            <div class="badge-discount mb-4">
                <i class="fas fa-magic mr-1"></i> ECONOMIA DE 20% APLICADA
            </div>
            @endif

            <ul class="premium-feature-list mt-4">
                <li class="p-feature">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Gestão com IA</strong>
                        <p class="small text-muted mb-0">IA aplicada para auxílio de solicitações da igreja.</p>
                    </div>
                </li>
                <li class="p-feature">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Lembretes Inteligentes</strong>
                        <p class="small text-muted mb-0">Lembretes de culto e células automáticos.</p>
                    </div>
                </li>
                <li class="p-feature">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Eventos & Cursos</strong>
                        <p class="small text-muted mb-0">Eventos e cursos automáticos e organizados.</p>
                    </div>
                </li>
            </ul>

            <div class="trust-footer mt-5 pt-3 border-top d-none d-lg-block">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <i class="fas fa-shield-halved fa-lg mr-2"></i>
                    <span>Pagamento Seguro<br>Processado pelo Basileia</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lado Direito: Formulário de Pagamento -->
    <div class="checkout-side-payment">
        <div class="payment-content">
            <h5 class="font-weight-700 mb-4">Finalize sua Inscrição</h5>
            
            <form action="{{ route('checkout.process', $venda->checkout_hash) }}" method="POST" id="checkout-form">
                @csrf

                @php
                    $metodoAtual = old('payment_method', $restritoMetodo ?? 'credit_card');
                    $exibirAbas = !isset($restritoMetodo);
                @endphp

                @if($exibirAbas)
                <div class="payment-method-toggle mb-4 d-flex">
                    <label class="p-toggle-item {{ $metodoAtual === 'credit_card' ? 'active' : '' }}" onclick="selectPayment('credit_card')">
                        <input type="radio" name="payment_method" value="credit_card" {{ $metodoAtual === 'credit_card' ? 'checked' : '' }}>
                        <i class="fas fa-credit-card"></i> Cartão
                    </label>
                    <label class="p-toggle-item {{ $metodoAtual === 'pix' ? 'active' : '' }}" onclick="selectPayment('pix')">
                        <input type="radio" name="payment_method" value="pix" {{ $metodoAtual === 'pix' ? 'checked' : '' }}>
                        <i class="fas fa-bolt"></i> PIX
                    </label>
                    <label class="p-toggle-item {{ $metodoAtual === 'boleto' ? 'active' : '' }}" onclick="selectPayment('boleto')">
                        <input type="radio" name="payment_method" value="boleto" {{ $metodoAtual === 'boleto' ? 'checked' : '' }}>
                        <i class="fas fa-barcode"></i> Boleto
                    </label>
                </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $restritoMetodo }}">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-4 rounded-lg">
                        <span class="small font-weight-bold text-muted text-uppercase">Pagando via</span>
                        <span class="text-primary font-weight-bold">
                            <i class="fas fa-{{ $restritoMetodo === 'credit_card' ? 'credit-card' : ($restritoMetodo === 'pix' ? 'bolt' : 'barcode') }} mr-1"></i>
                            {{ $restritoMetodo === 'credit_card' ? 'Cartão' : ($restritoMetodo === 'pix' ? 'PIX' : 'Boleto') }}
                        </span>
                    </div>
                @endif

                <div id="form-fields-container">
                    {{-- Grupo de E-mail --}}
                    <div class="form-group-premium mb-3">
                        <label>E-mail comercial</label>
                        <input type="email" class="form-control" value="{{ $venda->email_cliente ?? ($venda->cliente->email ?? '') }}" readonly>
                    </div>

                    {{-- Campos de Cartão --}}
                    <div id="secao-cartao" style="{{ $metodoAtual === 'credit_card' ? 'display:block' : 'display:none' }}">
                        <div class="form-group-premium mb-3">
                            <label>Número do Cartão</label>
                            <input type="text" name="numero_cartao" class="form-control" placeholder="0000 0000 0000 0000" oninput="formatarCartao(this)">
                        </div>
                        <div class="form-row">
                            <div class="col-7">
                                <div class="form-group-premium mb-3">
                                    <label>Validade</label>
                                    <input type="text" name="expiry" class="form-control" placeholder="MM/AA" oninput="formatarValidade(this)">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group-premium mb-3">
                                    <label>CVV</label>
                                    <input type="text" name="cvv" class="form-control" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                        <div class="form-group-premium mb-3">
                            <label>Nome no Cartão</label>
                            <input type="text" name="nome_cartao" class="form-control" placeholder="COMO NO CARTÃO">
                        </div>
                    </div>

                    {{-- Informativo PIX/Boleto --}}
                    <div id="secao-info" style="{{ $metodoAtual === 'credit_card' ? 'display:none' : 'display:block' }}">
                        <div class="alert alert-primary bg-light border-0 py-3 text-center rounded-lg">
                            <p class="small mb-0 text-muted" id="msg-pagamento">O link será gerado após clicar no botão abaixo.</p>
                        </div>
                    </div>

                    <div class="form-group-premium mb-4">
                        <label>CPF / CNPJ do Pagador</label>
                        <input type="text" name="cpf_titular" class="form-control" placeholder="000.000.000-00" oninput="formatarCpf(this)" required>
                    </div>
                </div>

                <button type="submit" class="btn-checkout-submit" id="main-submit-btn">
                    Confirmar Pagamento
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .checkout-main-grid { display: flex; flex-direction: row; min-height: 550px; }
    .checkout-side-info { width: 40%; background: #fbfbfc; padding: 45px 40px; border-right: 1px solid #f0f2f5; }
    .checkout-side-payment { width: 60%; padding: 45px 50px; background: #fff; }
    
    .font-weight-800 { font-weight: 800; }
    .display-price { font-size: 2.2rem; font-weight: 800; letter-spacing: -1.5px; }
    
    .badge-discount { 
        display: inline-block; background: #dcfce7; color: #166534; 
        font-size: 0.65rem; font-weight: 700; padding: 4px 10px; border-radius: 6px;
    }
    
    .premium-feature-list { list-style: none; padding: 0; }
    .p-feature { display: flex; gap: 12px; margin-bottom: 20px; align-items: flex-start; }
    .p-feature i { color: var(--primary); font-size: 1rem; margin-top: 3px; }
    .p-feature strong { font-size: 0.85rem; color: #111827; display: block; }
    .p-feature p { font-size: 0.8rem; line-height: 1.4; }

    .payment-method-toggle { background: #f4f5f7; padding: 4px; border-radius: 10px; gap: 2px; }
    .p-toggle-item { 
        flex: 1; text-align: center; padding: 8px 0; font-size: 0.8rem; font-weight: 700; 
        color: #6b7280; border-radius: 8px; cursor: pointer; transition: all 0.2s; margin-bottom: 0;
    }
    .p-toggle-item.active { background: #fff; color: var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .p-toggle-item i { margin-right: 5px; }
    .p-toggle-item input { display: none; }

    .form-group-premium label { display: block; font-size: 0.75rem; font-weight: 700; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; }
    .form-group-premium .form-control { 
        width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; 
        font-size: 0.95rem; font-weight: 500; transition: all 0.2s;
    }
    .form-group-premium .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(76, 29, 149, 0.08); outline: none; }

    .btn-checkout-submit {
        background: var(--primary-gradient); color: white; border: none; width: 100%; padding: 16px;
        border-radius: 12px; font-weight: 700; font-size: 1rem; box-shadow: 0 8px 16px rgba(76, 29, 149, 0.2);
        transition: all 0.2s;
    }
    .btn-checkout-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(76, 29, 149, 0.3); }

    @media (max-width: 900px) {
        .checkout-main-grid { flex-direction: column; }
        .checkout-side-info, .checkout-side-payment { width: 100%; border: none; }
        .checkout-side-info { order: 2; padding: 30px; }
        .checkout-side-payment { order: 1; padding: 30px; }
    }
</style>

<script>
function selectPayment(metodo) {
    document.querySelectorAll('.p-toggle-item').forEach(el => el.classList.remove('active'));
    const label = document.querySelector('input[value="'+metodo+'"]').parentElement;
    label.classList.add('active');
    
    document.getElementById('secao-cartao').style.display = metodo === 'credit_card' ? 'block' : 'none';
    document.getElementById('secao-info').style.display = metodo === 'credit_card' ? 'none' : 'block';
    
    const msg = document.getElementById('msg-pagamento');
    const btn = document.getElementById('main-submit-btn');
    if (metodo === 'pix') {
        msg.innerText = "Um QR Code Pix será gerado para pagamento imediato.";
        btn.innerText = "Gerar QR Code PIX";
    } else if (metodo === 'boleto') {
        msg.innerText = "Um boleto bancário será gerado para pagamento.";
        btn.innerText = "Gerar Boleto Bancário";
    } else {
        btn.innerText = "Confirmar Pagamento";
    }
}

function formatarCartao(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}
function formatarValidade(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2);
    input.value = v;
}
function formatarCpf(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) { v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'); }
    else { v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5'); }
    input.value = v;
}

document.getElementById('checkout-form').addEventListener('submit', function() {
    const btn = document.getElementById('main-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...';
});
</script>
@endsection
