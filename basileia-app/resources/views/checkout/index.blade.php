<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $currency }} Checkout - {{ $venda->plano->nome ?? $venda->plano }}</title>
    <meta name="description" content="{{ __('checkout.meta_description') }}">
    
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --success: #10b981;
            --success-light: #ecfdf5;
            --danger: #ef4444;
            --danger-light: #fef2f2;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.85);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, #e0e7ff 0, transparent 40%), 
                radial-gradient(at 100% 0%, #f1f5f9 0, transparent 40%),
                radial-gradient(at 100% 100%, #e0e7ff 0, transparent 40%),
                radial-gradient(at 0% 100%, #f1f5f9 0, transparent 40%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--slate-800);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Header Branding */
        .top-nav {
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--slate-900);
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .brand-logo i {
            color: var(--primary);
            font-size: 1.8rem;
        }

        .brand-logo span {
            color: var(--primary);
        }

        /* Language Selector */
        .language-selector {
            position: relative;
        }

        .language-selector-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--glass);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 50px;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--slate-700);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .language-selector-toggle:hover {
            background: var(--white);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .language-selector-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 320px;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            display: none;
            overflow: hidden;
            z-index: 1000;
            border: 1px solid var(--slate-100);
        }

        .language-selector-dropdown.active {
            display: block;
            animation: slideIn 0.25s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .language-search {
            padding: 12px;
            border-bottom: 1px solid var(--slate-100);
        }

        .language-search input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            font-size: 0.85rem;
            outline: none;
        }

        .language-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .language-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .language-item:hover {
            background: var(--slate-50);
        }

        .language-item.selected {
            background: var(--primary-light);
        }

        /* Urgency Stripe */
        .urgency-stripe {
            background: var(--slate-900);
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .urgency-stripe span.highlight {
            color: #fbbf24;
            font-weight: 700;
        }

        .urgency-stripe .timer {
            background: rgba(255,255,255,0.1);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
        }

        /* Layout Container */
        .main-checkout-grid {
            max-width: 1200px;
            margin: 20px auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .main-checkout-grid {
                grid-template-columns: 1fr;
                margin-top: 10px;
            }
        }

        /* Glass Cards */
        .checkout-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            padding: 40px;
        }

        @media (max-width: 640px) {
            .checkout-card {
                padding: 24px 20px;
            }
        }

        /* Step Navigation Flow */
        .step-nav {
            display: flex;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--slate-200);
            gap: 32px;
        }

        .step-item {
            padding-bottom: 16px;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--slate-400);
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .step-item.active {
            color: var(--primary);
        }

        .step-item.done {
            color: var(--success);
        }

        .step-item.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
        }

        .step-badge {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: var(--slate-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .step-item.active .step-badge {
            background: var(--primary-light);
            color: var(--primary);
        }

        .step-item.done .step-badge {
            background: var(--success-light);
            color: var(--success);
        }

        /* Form Styling */
        .section-header {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .section-desc {
            color: var(--slate-500);
            font-size: 1rem;
        }

        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group {
            margin-bottom: 24px;
        }

        .input-label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--slate-700);
            margin-bottom: 8px;
        }

        .modern-input {
            width: 100%;
            padding: 14px 16px;
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            font-size: 1rem;
            color: var(--slate-900);
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .modern-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .modern-input.error {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px var(--danger-light);
        }

        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 640px) {
            .input-row {
                grid-template-columns: 1fr;
            }
        }

        /* Payment Methods Modern */
        .payment-pill-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 32px;
        }

        @media (max-width: 480px) {
            .payment-pill-group {
                grid-template-columns: 1fr;
            }
        }

        .payment-pill {
            padding: 20px;
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            text-align: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .payment-pill.active {
            border-color: var(--primary);
            background: var(--primary-light);
            box-shadow: 0 0 0 1px var(--primary);
        }

        .payment-pill i {
            font-size: 1.6rem;
            color: var(--slate-400);
        }

        .payment-pill.active i {
            color: var(--primary);
        }

        .payment-pill span {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--slate-600);
        }

        .payment-pill.active span {
            color: var(--primary);
        }

        .payment-panel {
            display: none;
        }

        .payment-panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        /* Sidebar Summary */
        .summary-card-enterprise {
            background: var(--slate-900);
            border-radius: var(--radius-xl);
            padding: 40px;
            color: white;
            position: sticky;
            top: 40px;
            box-shadow: var(--shadow-xl);
        }

        .plan-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 24px;
            margin-bottom: 24px;
        }

        .plan-tag {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .summary-total {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .benefit-list {
            margin-top: 32px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .benefit-item-modern {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.95rem;
            color: var(--slate-300);
        }

        .benefit-item-modern i {
            color: var(--success);
            margin-top: 4px;
        }

        /* Buttons Enterprise */
        .btn-enterprise {
            width: 100%;
            padding: 18px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .btn-enterprise:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.5);
        }

        .btn-enterprise.loading {
            opacity: 0.8;
            pointer-events: none;
        }

        .btn-back {
            background: transparent;
            color: var(--slate-500);
            font-weight: 600;
            border: none;
            width: 100%;
            padding: 16px;
            cursor: pointer;
            transition: color 0.2s;
            margin-top: 12px;
        }

        .btn-back:hover {
            color: var(--slate-900);
        }

        /* Social Proof Modern */
        .trust-hero {
            margin-top: 40px;
            text-align: center;
            padding: 24px;
            background: rgba(255,255,255,0.4);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255,255,255,0.4);
        }

        .stars {
            color: #f59e0b;
            margin-bottom: 8px;
        }

        .trust-text {
            font-size: 0.85rem;
            color: var(--slate-500);
            font-weight: 500;
        }

        /* Success & Status states */
        .badge-secure {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--success);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Modal / Overlays */
        .pix-modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .pix-modal-content {
            background: white;
            padding: 40px;
            border-radius: var(--radius-xl);
            max-width: 440px;
            width: 90%;
            text-align: center;
            box-shadow: var(--shadow-xl);
        }
    </style>
</head>
<body>
    <!-- Urgency Stripe -->
    <div class="urgency-stripe">
        <i class="fas fa-rocket"></i>
        <span>{{ __('checkout.urgency_text') }} <span class="highlight" id="countdown">10:00</span></span>
        <span class="highlight">•</span>
        <span><i class="fas fa-users"></i> {{ __('checkout.urgency_slots') }}</span>
    </div>

    <!-- Navigation / Brand -->
    <nav class="top-nav">
        <a href="/" class="brand-logo">
            <i class="fas fa-cube"></i>
            Basiléia<span>Vendas</span>
        </a>

        <div class="language-selector">
            <button class="language-selector-toggle" onclick="toggleLanguageDropdown()">
                <span style="font-size: 1.2rem;">{{ $currentLanguage['flag'] }}</span>
                <span>{{ $currentLanguage['native_name'] }}</span>
                <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 4px;"></i>
            </button>
            <div class="language-selector-dropdown" id="language-dropdown">
                <div class="language-search">
                    <input type="text" id="language-search-input" placeholder="{{ __('checkout.search_language') }}" oninput="filterLanguages(this.value)">
                </div>
                <div class="language-list" id="language-list">
                    @foreach($availableLanguages as $lang)
                    <div class="language-item {{ $lang['code'] === $language ? 'selected' : '' }}" 
                         onclick="changeLanguage('{{ $lang['code'] }}', '{{ $lang['currency'] }}')"
                         data-name="{{ strtolower($lang['name'] . ' ' . $lang['native_name']) }}">
                        <span style="font-size: 1.4rem;">{{ $lang['flag'] }}</span>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--slate-900);">{{ $lang['native_name'] }}</div>
                            <div style="font-size: 0.75rem; color: var(--slate-500);">{{ $lang['currency'] }} • {{ $lang['name'] }}</div>
                        </div>
                        @if($lang['code'] === $language)
                        <i class="fas fa-check-circle" style="color: var(--primary); font-size: 0.9rem;"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    <div class="main-checkout-grid">
        <!-- Main Form Column -->
        <div class="checkout-card">
            <!-- Step Indicators -->
            <div class="step-nav">
                <div class="step-item active" id="step-1-indicator">
                    <div class="step-badge">1</div>
                    {{ __('checkout.step_identification') }}
                </div>
                <div class="step-item" id="step-2-indicator">
                    <div class="step-badge">2</div>
                    {{ __('checkout.step_payment') }}
                </div>
            </div>

            <!-- Step 1: Identification -->
            <div class="step-panel active" id="step-1">
                <div class="section-header">
                    <h1 class="section-title">{{ __('checkout.your_data') }}</h1>
                    <p class="section-desc">{{ __('checkout.your_data_subtitle') }}</p>
                </div>

                <form id="identification-form">
                    <div class="input-group">
                        <label class="input-label">{{ __('checkout.full_name') }} *</label>
                        <input type="text" class="modern-input" id="nome" name="nome" placeholder="{{ __('checkout.full_name_placeholder') }}" value="{{ $venda->cliente->nome ?? '' }}" required>
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ __('checkout.email') }} *</label>
                        <input type="email" class="modern-input" id="email" name="email" placeholder="{{ __('checkout.email_placeholder') }}" value="{{ $venda->cliente->email ?? '' }}" required>
                    </div>

                    <div class="input-row">
                        <div class="input-group">
                            <label class="input-label">{{ __('checkout.document') }} *</label>
                            <input type="text" class="modern-input" id="cpf" name="documento" placeholder="{{ $currency === 'BRL' ? '000.000.000-00' : 'ID / Document' }}" value="{{ $venda->cliente->documento ?? '' }}" required>
                        </div>
                        <div class="input-group">
                            <label class="input-label">{{ __('checkout.phone') }}</label>
                            <input type="text" class="modern-input" id="telefone" name="telefone" placeholder="(00) 00000-0000" value="{{ $venda->cliente->contato ?? $venda->cliente->whatsapp ?? '' }}">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ __('checkout.church_name') }}</label>
                        <input type="text" class="modern-input" id="nome_igreja" name="nome_igreja" placeholder="{{ __('checkout.church_name_placeholder') }}" value="{{ $venda->cliente->nome_igreja ?? '' }}">
                    </div>

                    <div class="input-group" style="margin-bottom: 40px;">
                        <label class="input-label">{{ __('checkout.members_count') }}</label>
                        <input type="number" class="modern-input" id="quantidade_membros" name="quantidade_membros" value="{{ $venda->cliente->quantidade_membros ?? 1 }}" min="1">
                    </div>

                    <button type="button" class="btn-enterprise" onclick="goToStep(2)">
                        {{ __('checkout.continue_to_payment') }}
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div style="margin-top: 32px; display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--slate-400); font-size: 0.85rem; font-weight: 500;">
                    <i class="fas fa-shield-check" style="color: var(--success); font-size: 1.1rem;"></i>
                    {{ __('checkout.security_message') }}
                </div>
            </div>

            <!-- Step 2: Payment -->
            <div class="step-panel" id="step-2">
                <div class="section-header">
                    <h1 class="section-title">{{ __('checkout.payment') }}</h1>
                    <p class="section-desc">{{ __('checkout.payment_subtitle') }}</p>
                </div>

                <!-- Currency Alert -->
                <div style="background: var(--primary-light); color: var(--primary); padding: 14px; border-radius: var(--radius); font-size: 0.85rem; font-weight: 600; margin-bottom: 32px; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(79, 70, 229, 0.1);">
                    <i class="fas fa-circle-info"></i>
                    {{ __('checkout.currency_info', ['currency' => $currency, 'symbol' => $currencyInfo['symbol']]) }}
                </div>
                <!-- Payment Tabs Pill -->
                <div class="payment-pill-group" id="payment-pill-group" style="grid-template-columns: 1fr;">
                    <div class="payment-pill active" id="tab-cartao" onclick="selectPaymentMethod('cartao')">
                        <i class="fas fa-credit-card"></i>
                        <span>{{ __('checkout.card') }}</span>
                    </div>
                </div>

                @php
                    $ultimoPagamento = $venda->pagamentos->where('status', 'pendente')->last() ?? $venda->pagamentos->last();
                @endphp

                @if($ultimoPagamento && in_array($ultimoPagamento->forma_pagamento, ['boleto', 'pix']))
                    <div style="background: white; border-radius: var(--radius); padding: 24px; border: 2px solid var(--primary); margin-bottom: 32px; box-shadow: var(--shadow-md);">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 48px; height: 48px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                <i class="fas {{ $ultimoPagamento->forma_pagamento === 'boleto' ? 'fa-barcode' : 'fa-qrcode' }}"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--slate-900);">Pagamento Disponível!</h3>
                                <p style="font-size: 0.85rem; color: var(--slate-500);">Seu pedido já possui uma cobrança ativa.</p>
                            </div>
                        </div>

                        @if($ultimoPagamento->forma_pagamento === 'boleto')
                            <div style="text-align: center; padding: 20px; background: var(--slate-50); border-radius: 12px; border: 1px dashed var(--slate-200);">
                                <p style="font-size: 0.9rem; color: var(--slate-600); margin-bottom: 16px;">Clique no botão abaixo para baixar seu boleto.</p>
                                <a href="{{ $ultimoPagamento->bank_slip_url ?? $ultimoPagamento->invoice_url }}" target="_blank" class="btn-enterprise" style="max-width: 300px; margin: 0 auto; background: var(--success); box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);">
                                    <i class="fas fa-download"></i> Baixar Boleto
                                </a>
                            </div>
                        @else
                            <div style="text-align: center; padding: 20px; background: var(--slate-50); border-radius: 12px; border: 1px dashed var(--slate-200);">
                                <p style="font-size: 0.9rem; color: var(--slate-600); margin-bottom: 16px;">Utilize o QR Code PIX para realizar o pagamento.</p>
                                <a href="{{ $ultimoPagamento->invoice_url }}" target="_blank" class="btn-enterprise" style="max-width: 300px; margin: 0 auto; background: var(--primary);">
                                    <i class="fas fa-qrcode"></i> Ver QR Code PIX
                                </a>
                            </div>
                        @endif
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <p style="font-size: 0.8rem; color: var(--slate-400);">Ou utilize os dados do cartão de crédito abaixo:</p>
                        </div>
                    </div>
                @endif

                <!-- Card Form (Default active) -->
                <div class="payment-panel active" id="cartao-panel">
                    <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                        <i class="fab fa-cc-visa" style="font-size: 2.2rem; color: #1a1f71;"></i>
                        <i class="fab fa-cc-mastercard" style="font-size: 2.2rem; color: #eb001b;"></i>
                        <i class="fab fa-cc-amex" style="font-size: 2.2rem; color: #006fcf;"></i>
                        <i class="fas fa-credit-card" style="font-size: 2.2rem; color: var(--slate-400);"></i>
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ __('checkout.card_number') }}</label>
                        <input type="text" class="modern-input" id="numero_cartao" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>

                    <div class="input-group">
                        <label class="input-label">{{ __('checkout.card_name') }}</label>
                        <input type="text" class="modern-input" id="nome_cartao" placeholder="{{ __('checkout.card_name_placeholder') }}">
                    </div>

                    <div class="input-row">
                        <div class="input-group">
                            <label class="input-label">{{ __('checkout.expiry') }}</label>
                            <input type="text" class="modern-input" id="validade_cartao" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="input-group">
                            <label class="input-label">CVV</label>
                            <input type="text" class="modern-input" id="cvv_cartao" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 40px;">
                    <button type="button" class="btn-enterprise" id="pay-button" onclick="processarPagamento()">
                        <i class="fas fa-lock"></i>
                        {{ __('checkout.pay_button') }}
                    </button>
                    <button type="button" class="btn-back" onclick="goToStep(1)">
                        <i class="fas fa-chevron-left" style="font-size: 0.7rem;"></i> {{ __('checkout.back') }}
                    </button>
                </div>

                <div style="margin-top: 24px; text-align: center; font-size: 0.75rem; color: var(--slate-400); font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-shield-halved" style="color: var(--slate-300);"></i>
                    {{ __('checkout.asaas_security') }}
                </div>
            </div>
        </div>

        <!-- Sidebar Summary -->
        <aside>
            <div class="summary-card-enterprise">
                <div class="plan-header">
                    <span class="plan-tag" id="summary-plan-tag">{{ __('checkout.plan') }}</span>
                    <h2 id="summary-plan-name" style="font-size: 1.8rem; margin-bottom: 8px; font-weight: 800;">{{ $venda->plano->nome ?? $venda->plano }}</h2>
                    <p style="color: var(--slate-400); font-size: 0.95rem;">{{ __('checkout.meta_description') }}</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--slate-400);">
                        <span>{{ __('checkout.original_price') }}</span>
                        <span id="summary-original-price" style="text-decoration: line-through;">{{ $currencyInfo['symbol'] }} {{ number_format($valorOriginalConvertido, 2, $currencyInfo['decimal_separator'], $currencyInfo['thousand_separator']) }}</span>
                    </div>
                    @if(($venda->valor_desconto ?? 0) > 0)
                    <div style="display: flex; justify-content: space-between; font-size: 1rem; color: var(--success); font-weight: 700;">
                        <span>{{ __('checkout.discount') }}</span>
                        <span id="summary-discount">- {{ $currencyInfo['symbol'] }} {{ number_format($this->exchangeRateService->convert($venda->valor_desconto, 'BRL', $currency), 2, $currencyInfo['decimal_separator'], $currencyInfo['thousand_separator']) }}</span>
                    </div>
                    @endif
                </div>

                <div style="display: flex; justify-content: space-between; align-items: baseline; padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <span style="font-weight: 700; font-size: 1.1rem; color: white;">{{ __('checkout.total') }}</span>
                    <div id="summary-total" class="summary-total">{{ $currencyInfo['symbol'] }} {{ number_format($valorConvertido, 2, $currencyInfo['decimal_separator'], $currencyInfo['thousand_separator']) }}</div>
                </div>

                <div class="benefit-list">
                    <div class="benefit-item-modern">
                        <i class="fas fa-circle-check"></i>
                        <span>{{ __('checkout.benefit_immediate') }}</span>
                    </div>
                    <div class="benefit-item-modern">
                        <i class="fas fa-circle-check"></i>
                        <span>{{ __('checkout.benefit_support') }}</span>
                    </div>
                    <div class="benefit-item-modern">
                        <i class="fas fa-circle-check"></i>
                        <span>{{ __('checkout.benefit_updates') }}</span>
                    </div>
                    <div class="benefit-item-modern">
                        <i class="fas fa-circle-check"></i>
                        <span>{{ __('checkout.benefit_guarantee') }}</span>
                    </div>
                </div>
            </div>

            <!-- Trust Hero -->
            <div class="trust-hero">
                <div class="stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <div style="font-weight: 800; color: var(--slate-900); margin-bottom: 4px; font-size: 1rem;">{{ __('checkout.social_proof_title') }}</div>
                <div class="trust-text">4.9/5 estrelas • {{ __('checkout.trust_secure') }}</div>
            </div>
            
            <div style="margin-top: 24px; display: flex; justify-content: center; gap: 20px; opacity: 0.4;">
                <i class="fab fa-cc-visa" style="font-size: 1.75rem;"></i>
                <i class="fab fa-cc-mastercard" style="font-size: 1.75rem;"></i>
                <i class="fab fa-cc-amex" style="font-size: 1.75rem;"></i>
                <i class="fas fa-lock" style="font-size: 1.25rem;"></i>
            </div>
        </aside>
    </div>

    <!-- Hidden input for current plan ID -->
    <input type="hidden" id="selected_plan_id" value="{{ $venda->plano_id }}">

    <script>
        const vendaHash = '{{ $venda->checkout_hash }}';
        const currentCurrency = '{{ $currency }}';
        const currentLanguage = '{{ $language }}';
        const exchangeRate = {{ $taxa ?? 1 }};
        const planosArr = @json($planos);
        const currencySymbol = '{{ $currencyInfo['symbol'] }}';
        const decimalSep = '{{ $currencyInfo['decimal_separator'] }}';
        const thousandSep = '{{ $currencyInfo['thousand_separator'] }}';
        
        // Pre-selected method logic
        const preSelectedMethod = '{{ $preSelectedMethod }}';
        let selectedPayment = preSelectedMethod || 'cartao';
        let currentStep = 1;

        // Auto advance to step 2 if method is pre-selected (Panel Redirect)
        if (preSelectedMethod) {
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => goToStep(2), 300);
            });
        }

        // Dynamic Plan Suggestion logic
        const inputMembros = document.getElementById('quantidade_membros');
        if (inputMembros) {
            inputMembros.addEventListener('input', updatePlanSuggestion);
            // Initial call
            setTimeout(updatePlanSuggestion, 500);
        }

        function updatePlanSuggestion() {
            const count = parseInt(inputMembros.value) || 1;
            let currentPlan = planosArr[0];
            let nextPlan = null;

            for (let i = 0; i < planosArr.length; i++) {
                if (count >= planosArr[i].faixa_min_membros && count <= planosArr[i].faixa_max_membros) {
                    currentPlan = planosArr[i];
                    nextPlan = planosArr[i + 1] || null;
                    break;
                }
            }

            // Update UI
            document.getElementById('summary-plan-name').innerText = currentPlan.nome;
            document.getElementById('selected_plan_id').value = currentPlan.id;
            
            const totalVal = currentPlan.valor_mensal * exchangeRate;
            const originalVal = (currentPlan.valor_mensal * 1.5) * exchangeRate; // Simplified original price logic

            document.getElementById('summary-total').innerText = currencySymbol + ' ' + totalVal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('summary-original-price').innerText = currencySymbol + ' ' + originalVal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Suggestion text
            let suggestionBox = document.getElementById('plan-suggestion-box');
            if (!suggestionBox) {
                suggestionBox = document.createElement('div');
                suggestionBox.id = 'plan-suggestion-box';
                suggestionBox.style.marginTop = '12px';
                suggestionBox.style.fontSize = '0.85rem';
                suggestionBox.style.fontWeight = '600';
                inputMembros.parentNode.appendChild(suggestionBox);
            }

            if (nextPlan) {
                const diff = currentPlan.faixa_max_membros - count;
                if (diff <= 10) {
                    suggestionBox.innerHTML = `<i class="fas fa-arrow-trend-up" style="color: var(--primary);"></i> Você está próximo do limite! O plano <strong>${nextPlan.nome}</strong> atende a partir de ${nextPlan.faixa_min_membros} membros.`;
                    suggestionBox.style.color = 'var(--primary)';
                } else {
                    suggestionBox.innerHTML = `<i class="fas fa-check-circle" style="color: var(--success);"></i> O plano <strong>${currentPlan.nome}</strong> é o ideal para você.`;
                    suggestionBox.style.color = 'var(--slate-500)';
                }
            } else {
                suggestionBox.innerHTML = `<i class="fas fa-star" style="color: #f59e0b;"></i> Você está no plano <strong>${currentPlan.nome}</strong> (Enterprise).`;
                suggestionBox.style.color = 'var(--slate-500)';
            }
        }

        // Timer (10m)
        let timeLeft = 10 * 60;
        const countdownEl = document.getElementById('countdown');
        function updateCountdown() {
            if (!countdownEl) return;
            const m = Math.floor(timeLeft / 60);
            const s = timeLeft % 60;
            countdownEl.textContent = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
            if (timeLeft <= 0) countdownEl.textContent = "EXPIRADO";
            timeLeft--;
        }
        setInterval(updateCountdown, 1000);

        // Masks
        function maskID(input) {
            let v = input.value.replace(/\D/g, '');
            if (currentCurrency === 'BRL') {
                if (v.length > 11) v = v.slice(0, 11);
                if (v.length > 9) v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{3})/, "$1.$2.$3");
                else if (v.length > 3) v = v.replace(/(\d{3})(\d{3})/, "$1.$2");
            }
            input.value = v;
        }
        document.getElementById('cpf').oninput = e => maskID(e.target);

        // Navigation
        function goToStep(step) {
            if (step === 2) {
                const n = document.getElementById('nome').value.trim();
                const e = document.getElementById('email').value.trim();
                const d = document.getElementById('cpf').value.trim();
                if (!n || !e || !d) {
                    alert("{{ __('checkout.fill_required') }}");
                    return;
                }
            }
            document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(`step-${step}`).classList.add('active');
            
            document.querySelectorAll('.step-item').forEach(i => i.classList.remove('active'));
            document.querySelectorAll('.step-item')[step-1].classList.add('active');
            currentStep = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function selectPayment(type, el) {
            // This function is no longer needed as only card is available.
            // Keeping it as a placeholder if payment methods are re-introduced.
        }

        // Language Logic
        function toggleLanguageDropdown() {
            document.getElementById('language-dropdown').classList.toggle('active');
        }
        function changeLanguage(lang, curr) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            url.searchParams.set('moeda', curr);
            window.location.href = url.toString();
        }
        function filterLanguages(q) {
            q = q.toLowerCase();
            document.querySelectorAll('.language-item').forEach(i => {
                i.style.display = i.dataset.name.includes(q) ? 'flex' : 'none';
            });
        }
        document.onclick = e => {
            if (!e.target.closest('.language-selector')) document.getElementById('language-dropdown').classList.remove('active');
        };

        // Process
        async function processarPagamento() {
            const btn = document.getElementById('pay-button');
            btn.classList.add('loading');
            btn.disabled = true;

            const data = {
                _token: '{{ csrf_token() }}',
                nome: document.getElementById('nome').value,
                email: document.getElementById('email').value,
                documento: document.getElementById('cpf').value,
                telefone: document.getElementById('telefone').value,
                nome_igreja: document.getElementById('nome_igreja').value,
                quantidade_membros: document.getElementById('quantidade_membros').value,
                plano_id: document.getElementById('selected_plan_id').value,
                payment_method: 'cartao',
                card_number: document.getElementById('numero_cartao').value,
                card_name: document.getElementById('nome_cartao').value,
                card_expiry: document.getElementById('validade_cartao').value,
                card_cvv: document.getElementById('cvv_cartao').value
            };

            try {
                const r = await fetch(`/checkout/${vendaHash}/processar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                const d = await r.json();
                if (d.success) {
                    window.location.href = d.redirect_url || `/checkout/${vendaHash}/sucesso`;
                } else {
                    alert(d.message || "{{ __('checkout.payment_error') }}");
                    btn.classList.remove('loading'); btn.disabled = false;
                }
            } catch (e) {
                alert("{{ __('checkout.connection_error') }}");
                btn.classList.remove('loading'); btn.disabled = false;
            }
        }

        function showPix(d) {
            // This function is no longer needed as Pix is removed.
        }

        function showBoleto(d) {
            // This function is no longer needed as Boleto is removed.
        }
    </script>
</body>
</html>
