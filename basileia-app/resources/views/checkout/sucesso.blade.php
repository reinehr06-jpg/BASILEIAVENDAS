<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('checkout.order_received') }} - Basiléia Vendas</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #10b981;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.9);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius-xl: 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--slate-900);
            background-image: 
                radial-gradient(at 0% 0%, #4f46e5 0, transparent 40%), 
                radial-gradient(at 100% 100%, #10b981 0, transparent 40%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--slate-800);
        }

        .success-card {
            background: var(--glass);
            backdrop-filter: blur(16px);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            max-width: 560px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .card-header {
            padding: 60px 40px 40px;
            text-align: center;
        }

        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.5rem;
            animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
        }

        .status-icon.paid {
            background: var(--success);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
        }

        .status-icon.pending {
            background: #fbbf24;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(251, 191, 36, 0.4);
        }

        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -1px;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 1.1rem;
            color: var(--slate-500);
            font-weight: 500;
        }

        .card-body {
            padding: 0 40px 40px;
        }

        .order-summary {
            background: white;
            border: 1px solid var(--slate-100);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--slate-50);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            font-weight: 700;
            color: var(--slate-900);
        }

        .next-steps {
            margin-bottom: 32px;
        }

        .step-item {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            align-items: flex-start;
        }

        .step-number {
            width: 32px;
            height: 32px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .step-content {
            font-size: 0.95rem;
            color: var(--slate-600);
            line-height: 1.6;
        }

        .btn-access {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 18px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .btn-access:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.5);
        }

        .whatsapp-support {
            margin-top: 24px;
            text-align: center;
        }

        .whatsapp-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #25d366;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* Confetti */
        canvas#confetti {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 1001;
        }
    </style>
</head>
<body>
    <canvas id="confetti"></canvas>

    <div class="success-card">
        @php
            $ultimoPagamento = $venda->pagamentos->last();
            $status = $ultimoPagamento ? $ultimoPagamento->status : 'Aguardando pagamento';
            $isPago = in_array($status, ['Pago', 'Recebido', 'Confirmado']);
        @endphp

        <div class="card-header">
            <div class="status-icon {{ $isPago ? 'paid' : 'pending' }}">
                <i class="fas {{ $isPago ? 'fa-check' : 'fa-clock' }}"></i>
            </div>
            
            <h1 class="title">
                @if($isPago)
                    {{ __('checkout.success_paid_title') }}
                @else
                    {{ __('checkout.success_pending_title') }}
                @endif
            </h1>
            <p class="subtitle">
                @if($isPago)
                    {{ __('checkout.success_paid_subtitle') }}
                @else
                    {{ __('checkout.success_pending_subtitle') }}
                @endif
            </p>
        </div>

        <div class="card-body">
            <div class="order-summary">
                <div class="summary-row">
                    <span class="label">{{ __('checkout.order_id') }}</span>
                    <span class="value">#{{ str_pad($venda->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">{{ __('checkout.plan') }}</span>
                    <span class="value">{{ $venda->plano }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">{{ __('checkout.status') }}</span>
                    <span class="value" style="color: {{ $isPago ? 'var(--success)' : '#fbbf24' }}">{{ $status }}</span>
                </div>
                <div class="summary-row" style="border: none; padding-top: 16px;">
                    <span class="label" style="color: var(--slate-900); font-size: 1rem;">Total</span>
                    <span class="value" style="font-size: 1.25rem; color: var(--primary);">R$ {{ number_format($venda->valor_final ?? $venda->valor, 2, ',', '.') }}</span>
                </div>
            </div>

            <div class="next-steps">
                @if($isPago)
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">{{ __('checkout.step_paid_1') }}</div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">{{ __('checkout.step_paid_2') }}</div>
                    </div>
                @else
                    @if($ultimoPagamento && $ultimoPagamento->forma_pagamento === 'pix')
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">{{ __('checkout.step_pix_pending') }}</div>
                        </div>
                    @elseif($ultimoPagamento && $ultimoPagamento->forma_pagamento === 'boleto')
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">{{ __('checkout.step_boleto_pending') }}</div>
                        </div>
                        <div style="margin: 20px 0;">
                            <a href="{{ $ultimoPagamento->bank_slip_url ?? $ultimoPagamento->invoice_url }}" target="_blank" class="btn-access" style="background: var(--success); box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);">
                                <i class="fas fa-download"></i> {{ __('checkout.download_boleto') ?? 'Baixar Boleto' }}
                            </a>
                        </div>
                    @endif
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">{{ __('checkout.step_process_info') }}</div>
                    </div>
                @endif
            </div>

            <a href="https://basileia.global" class="btn-access">
                @if($isPago)
                    <i class="fas fa-rocket"></i> {{ __('checkout.access_now') }}
                @else
                    <i class="fas fa-home"></i> {{ __('checkout.back_to_site') }}
                @endif
            </a>

            <div class="whatsapp-support">
                <a href="https://wa.me/5551981048868?text=Olá! Preciso de ajuda com meu pedido #{{ $venda->id }}" class="whatsapp-link" target="_blank">
                    <i class="fab fa-whatsapp"></i> {{ __('checkout.talk_to_support') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts de confete -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        @if($isPago)
        const duration = 3 * 1000;
        const animationEnd = Date.now() + duration;
        const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

        function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
        }

        const interval = setInterval(function() {
            const timeLeft = animationEnd - Date.now();

            if (timeLeft <= 0) {
                return clearInterval(interval);
            }

            const particleCount = 50 * (timeLeft / duration);
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
        }, 250);
        @endif
    </script>
</body>
</html>
