@extends('layouts.app')
@section('title', 'Nova Venda')

@section('content')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .btn-back { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-back:hover { background: #f8fafc; }
    .btn-primary { background: var(--primary); color: white; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(88, 28, 135, 0.2); font-size: 1rem; }
    .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
    .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .form-section-title { font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid rgba(88,28,135,0.1); display: flex; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-section-title span { font-size: 1.2rem; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.4px; }
    .form-group label .required { color: #ef4444; margin-left: 2px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 11px 14px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-size: 0.93rem; transition: 0.2s; background: white; color: var(--text-main); }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(88,28,135,0.1); }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-group .field-hint { font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; }
    .form-group .field-error { font-size: 0.8rem; color: #ef4444; margin-top: 4px; font-weight: 500; }
    .form-row { display: flex; gap: 16px; }
    .form-row .form-group { flex: 1; }

    .auto-data-bar { background: linear-gradient(135deg, rgba(88,28,135,0.06), rgba(88,28,135,0.02)); border: 1px dashed rgba(88,28,135,0.2); border-radius: 12px; padding: 16px 20px; display: flex; flex-wrap: wrap; gap: 24px; margin-bottom: 24px; }
    .auto-data-item { display: flex; flex-direction: column; gap: 2px; }
    .auto-data-item .label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; }
    .auto-data-item .value { font-size: 0.95rem; font-weight: 700; color: var(--primary); }

    /* Planos Cards */
    .planos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; margin-top: 12px; }
    .plano-card { border: 2px solid var(--border); border-radius: 12px; padding: 16px; text-align: center; cursor: pointer; transition: all 0.25s ease; position: relative; background: white; }
    .plano-card:hover { border-color: rgba(88,28,135,0.3); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(88,28,135,0.1); }
    .plano-card.selected { border-color: var(--primary); background: rgba(88,28,135,0.04); box-shadow: 0 0 0 3px rgba(88,28,135,0.15); }
    .plano-card.selected::after { content: '✓'; position: absolute; top: 8px; right: 10px; background: var(--primary); color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
    .plano-card .plano-name { font-weight: 700; font-size: 1rem; color: var(--primary); margin-bottom: 4px; }
    .plano-card .plano-range { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px; }
    .plano-card .plano-price { font-weight: 800; font-size: 1.15rem; color: var(--text-main); }
    .plano-card .plano-price-label { font-size: 0.7rem; color: var(--text-muted); }
    .plano-card.disabled { opacity: 0.35; cursor: not-allowed; pointer-events: none; }

    .valor-resumo { background: linear-gradient(135deg, var(--primary), #7c3aed); border-radius: 12px; color: white; padding: 20px 24px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
    .valor-resumo .label { font-size: 0.9rem; opacity: 0.85; }
    .valor-resumo .valor { font-size: 1.8rem; font-weight: 800; }

    .error-box { background: #fee2e2; color: #991b1b; padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; border-left: 4px solid #ef4444; }
    .footer-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; padding-top: 24px; border-top: 1px solid var(--border); }

    @media (max-width: 768px) {
        .form-row { flex-direction: column; gap: 0; }
        .planos-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="page-header">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">+ Nova Venda</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Preencha todos os campos para registrar uma nova venda e gerar a cobrança.</p>
    </div>
    <a href="{{ route('vendedor.vendas') }}" class="btn-back">← Voltar</a>
</div>

@if($errors->any())
<div class="error-box">
    @foreach($errors->all() as $error)
        <div>❌ {{ $error }}</div>
    @endforeach
</div>
@endif

<!-- Dados automáticos -->
<div class="auto-data-bar">
    <div class="auto-data-item">
        <span class="label">Vendedor Responsável</span>
        <span class="value">{{ Auth::user()->name }}</span>
    </div>
    <div class="auto-data-item">
        <span class="label">Data da Venda</span>
        <span class="value">{{ now()->format('d/m/Y') }}</span>
    </div>
    <div class="auto-data-item">
        <span class="label">Status Inicial</span>
        <span class="value">Aguardando pagamento</span>
    </div>
    <div class="auto-data-item">
        <span class="label">Origem</span>
        <span class="value">Manual</span>
    </div>
</div>

<form action="{{ route('vendedor.vendas.store') }}" method="POST" id="formNovaVenda">
    @csrf

    <!-- ===== BLOCO 1: Identificação do Cliente ===== -->
    <div class="form-card">
        <div class="form-section-title"><span>⛪</span> Identificação do Cliente</div>

        <div class="form-row">
            <div class="form-group">
                <label>Nome da Igreja <span class="required">*</span></label>
                <input type="text" name="nome_igreja" value="{{ old('nome_igreja') }}" required placeholder="Digite o nome completo da igreja">
                @error('nome_igreja') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Nome do Pastor <span class="required">*</span></label>
                <input type="text" name="nome_pastor" value="{{ old('nome_pastor') }}" required placeholder="Digite o nome do pastor responsável">
                @error('nome_pastor') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Localidade <span class="required">*</span></label>
                <input type="text" name="localidade" value="{{ old('localidade') }}" required placeholder="Cidade, estado ou país">
            </div>
            <div class="form-group">
                <label>Moeda <span class="required">*</span></label>
                <select name="moeda">
                    <option value="BRL" {{ old('moeda') == 'BRL' ? 'selected' : '' }}>🇧🇷 BRL - Real</option>
                    <option value="USD" {{ old('moeda') == 'USD' ? 'selected' : '' }}>🇺🇸 USD - Dólar</option>
                    <option value="EUR" {{ old('moeda') == 'EUR' ? 'selected' : '' }}>🇪🇺 EUR - Euro</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantidade de Membros <span class="required">*</span></label>
                <input type="number" name="quantidade_membros" id="inputMembros" value="{{ old('quantidade_membros') }}" required min="1" placeholder="Informe o número de membros para sugerir o plano">
                @error('quantidade_membros') <div class="field-error">{{ $message }}</div> @enderror
                <div class="field-hint">O sistema sugere planos automaticamente com base na quantidade.</div>
            </div>
            <div class="form-group">
                <label>CNPJ da Igreja ou CPF do Pastor <span class="required">*</span></label>
                <input type="text" name="documento" id="inputDocumento" value="{{ old('documento') }}" required placeholder="Digite o CNPJ da igreja ou CPF do pastor" maxlength="18">
                @error('documento') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 0.5;">
                <label>WhatsApp de Contato <span class="required">*</span></label>
                <input type="text" name="whatsapp" id="inputWhatsapp" value="{{ old('whatsapp') }}" required placeholder="Digite o número com DDD" maxlength="15">
            </div>
            <div class="form-group" style="flex: 0.5;">
                <label>E-mail do Cliente <span class="required">*</span></label>
                <input type="email" name="email_cliente" value="{{ old('email_cliente') }}" required placeholder="email@igreja.com">
                @error('email_cliente') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <!-- ===== BLOCO 2: Dados Comerciais ===== -->
    <div class="form-card">
        <div class="form-section-title"><span>💼</span> Dados Comerciais</div>

        <!-- Grid de Planos Dinâmico -->
        <div class="form-group">
            <label>Plano Sugerido <span class="required">*</span></label>
            <input type="hidden" name="plano" id="inputPlano" value="{{ old('plano') }}">
            <div class="planos-grid" id="planosGrid">
                @foreach($planos as $p)
                <div class="plano-card {{ old('plano') == $p['nome'] ? 'selected' : '' }}"
                     data-nome="{{ $p['nome'] }}"
                     data-min="{{ $p['min_membros'] }}"
                     data-max="{{ $p['max_membros'] }}"
                     data-mensal="{{ $p['valor_mensal'] }}"
                     data-anual="{{ $p['valor_anual'] }}">
                    <div class="plano-name">{{ $p['nome'] }}</div>
                    <div class="plano-range">{{ $p['min_membros'] }} - {{ $p['max_membros'] == 99999 ? '∞' : $p['max_membros'] }} membros</div>
                    <div class="plano-price" data-mensal="{{ $p['valor_mensal'] }}" data-anual="{{ $p['valor_anual'] }}">
                        R$ {{ number_format($p['valor_mensal'], 2, ',', '.') }}
                    </div>
                    <div class="plano-price-label">por mês</div>
                </div>
                @endforeach
            </div>
            @error('plano') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-row" style="margin-top: 20px;">
            <div class="form-group">
                <label>Forma de Pagamento <span class="required">*</span></label>
                <select name="forma_pagamento" id="selectFormaPagamento">
                    <option value="" disabled {{ old('forma_pagamento') ? '' : 'selected' }}>Selecione a forma de pagamento</option>
                    <option value="PIX" {{ old('forma_pagamento') == 'PIX' ? 'selected' : '' }}>⚡ PIX</option>
                    <option value="BOLETO" {{ old('forma_pagamento') == 'BOLETO' ? 'selected' : '' }}>📄 Boleto Bancário</option>
                    <option value="CREDIT_CARD" {{ old('forma_pagamento') == 'CREDIT_CARD' ? 'selected' : '' }}>💳 Cartão de Crédito</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tipo de Negociação <span class="required">*</span></label>
                <select name="tipo_negociacao" id="selectTipoNegociacao">
                    <option value="mensal" {{ old('tipo_negociacao', 'mensal') == 'mensal' ? 'selected' : '' }}>📅 Mensal</option>
                    <option value="anual" {{ old('tipo_negociacao') == 'anual' ? 'selected' : '' }}>📆 Anual</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 0.4;">
                <label>Desconto (%)</label>
                <input type="number" step="0.1" name="desconto" id="inputDesconto" value="{{ old('desconto', 0) }}" min="0" max="{{ $maxDesconto }}" placeholder="Informe o percentual de desconto">
                @error('desconto') <div class="field-error">{{ $message }}</div> @enderror
                <div class="field-hint">Máximo permitido: {{ $maxDesconto }}%</div>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Observação Interna</label>
                <textarea name="observacao" placeholder="Digite observações internas, se necessário">{{ old('observacao') }}</textarea>
            </div>
        </div>

        <!-- Resumo do Valor Final -->
        <div class="valor-resumo" id="valorResumo" style="display: none;">
            <div>
                <div class="label">Valor Final da Cobrança</div>
                <div style="font-size: 0.8rem; opacity: 0.7;" id="resumoDetalhes"></div>
            </div>
            <div class="valor" id="valorFinal">R$ 0,00</div>
        </div>
    </div>

    <!-- ===== Rodapé / Ações ===== -->
    <div class="footer-actions">
        <a href="{{ route('vendedor.vendas') }}" class="btn-back">Cancelar</a>
        <button type="submit" class="btn-primary" id="btnSalvar">💰 Gerar Cobrança e Salvar Venda</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputMembros = document.getElementById('inputMembros');
    const inputPlano = document.getElementById('inputPlano');
    const selectTipo = document.getElementById('selectTipoNegociacao');
    const inputDesconto = document.getElementById('inputDesconto');
    const inputDocumento = document.getElementById('inputDocumento');
    const inputWhatsapp = document.getElementById('inputWhatsapp');
    const valorResumo = document.getElementById('valorResumo');
    const valorFinal = document.getElementById('valorFinal');
    const resumoDetalhes = document.getElementById('resumoDetalhes');
    const cards = document.querySelectorAll('.plano-card');

    // Selecionar plano ao clicar no card
    cards.forEach(card => {
        card.addEventListener('click', function() {
            if (this.classList.contains('disabled')) return;
            cards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            inputPlano.value = this.dataset.nome;
            calcularValor();
        });
    });

    // Atualizar planos compatíveis quando membros mudam
    inputMembros.addEventListener('input', function() {
        const membros = parseInt(this.value) || 0;
        let planoAutoSelecionado = false;

        cards.forEach(card => {
            const min = parseInt(card.dataset.min);
            const max = parseInt(card.dataset.max);
            if (membros >= min && membros <= max) {
                card.classList.remove('disabled');
                if (!planoAutoSelecionado) {
                    cards.forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    inputPlano.value = card.dataset.nome;
                    planoAutoSelecionado = true;
                }
            } else {
                card.classList.add('disabled');
                card.classList.remove('selected');
            }
        });

        if (!planoAutoSelecionado) {
            inputPlano.value = '';
            valorResumo.style.display = 'none';
        }
        calcularValor();
    });

    // Recalcular quando tipo ou desconto mudam
    selectTipo.addEventListener('change', function() {
        updatePriceLabels();
        calcularValor();
    });
    inputDesconto.addEventListener('input', calcularValor);

    function updatePriceLabels() {
        const tipo = selectTipo.value;
        cards.forEach(card => {
            const priceEl = card.querySelector('.plano-price');
            const labelEl = card.querySelector('.plano-price-label');
            const mensal = parseFloat(priceEl.dataset.mensal);
            const anual = parseFloat(priceEl.dataset.anual);
            if (tipo === 'anual') {
                priceEl.textContent = 'R$ ' + anual.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                labelEl.textContent = 'por ano';
            } else {
                priceEl.textContent = 'R$ ' + mensal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                labelEl.textContent = 'por mês';
            }
        });
    }

    function calcularValor() {
        const selected = document.querySelector('.plano-card.selected');
        if (!selected) {
            valorResumo.style.display = 'none';
            return;
        }

        const tipo = selectTipo.value;
        const base = tipo === 'anual' ? parseFloat(selected.dataset.anual) : parseFloat(selected.dataset.mensal);
        const desconto = parseFloat(inputDesconto.value) || 0;
        const final_ = base - (base * (desconto / 100));

        valorResumo.style.display = 'flex';
        valorFinal.textContent = 'R$ ' + final_.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        let detalhes = `Plano ${selected.dataset.nome} (${tipo})`;
        if (desconto > 0) {
            detalhes += ` • ${desconto}% de desconto aplicado`;
        }
        resumoDetalhes.textContent = detalhes;
    }

    // Máscara simples de documento (CPF/CNPJ)
    inputDocumento.addEventListener('input', function(e) {
        let v = this.value.replace(/\D/g, '');
        if (v.length <= 11) {
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            v = v.substring(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
        }
        this.value = v;
    });

    // Máscara simples de WhatsApp
    inputWhatsapp.addEventListener('input', function(e) {
        let v = this.value.replace(/\D/g, '');
        v = v.substring(0, 11);
        if (v.length > 6) {
            v = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
        } else if (v.length > 2) {
            v = '(' + v.substring(0,2) + ') ' + v.substring(2);
        } else if (v.length > 0) {
            v = '(' + v;
        }
        this.value = v;
    });

    // Init
    if (inputMembros.value) inputMembros.dispatchEvent(new Event('input'));
    updatePriceLabels();
    calcularValor();
});
</script>
@endsection
