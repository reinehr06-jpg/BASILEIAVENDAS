<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finalizar Pagamento - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas Card Edition -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #4C1D95;
            --primary-gradient: linear-gradient(135deg, #4C1D95 0%, #6366F1 100%);
            --bg: #f8f9fa;
            --surface: #ffffff;
            --radius-xl: 16px;
            --shadow-premium: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: #4a4a6a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .checkout-card-container {
            width: 100%;
            max-width: 960px;
            background: var(--surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-premium);
            overflow: hidden;
            border: 1px solid #edf0f5;
            animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes fadeInScale {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .logo-header {
            text-align: center;
            padding: 40px 0 20px;
        }
        
        .logo-official {
            max-width: 220px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }

        .btn-back-minimal {
            position: absolute;
            top: 25px;
            left: 25px;
            color: #adb5bd;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back-minimal:hover { color: var(--primary); text-decoration: none; }

        @media (max-width: 768px) {
            body { padding: 0; align-items: flex-start; background: #fff; }
            .checkout-card-container { border-radius: 0; border: none; box-shadow: none; }
            .btn-back-minimal { top: 15px; left: 15px; }
        }
    </style>
</head>
<body>

    <a href="javascript:history.back()" class="btn-back-minimal">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>

    <div class="checkout-card-container">
        <div class="logo-header">
            <!-- Logo oficial do sistema via asset local após deploy -->
            <img src="/assets/img/logo_oficial.png" alt="Basiléia Vendas" class="logo-official" 
                 onerror="this.src='https://i.imgur.com/uRjE87c.png'; this.style.opacity='0.5';">
        </div>

        @yield('content')
        
        <div class="text-center py-4 border-top bg-lightest">
            <p class="small text-muted mb-0">&copy; {{ date('Y') }} Basiléia Vendas - Checkout Seguro</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
