<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Finalizar Registro - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas Enterprise Edition -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #4C1D95;
            --primary-light: #6D28D9;
            --surface: rgba(255, 255, 255, 0.96);
            --radius-enterprise: 20px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            /* Fundo Animado Enterprise Basiléia */
            background: linear-gradient(-45deg, #4B0082, #8A2BE2, #9400D3, #A020F0);
            background-size: 400% 400%;
            animation: gradientAnim 15s ease infinite;
            -webkit-font-smoothing: antialiased;
        }

        @keyframes gradientAnim {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .checkout-main-container {
            width: 100%;
            max-width: 1020px;
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-enterprise);
            box-shadow: 0 40px 100px rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.4);
            overflow: hidden;
            animation: containerEntrance 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes containerEntrance {
            from { transform: translateY(60px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .header-enterprise {
            text-align: center;
            padding: 45px 0 25px;
            background: rgba(255,255,255,0.5);
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }
        
        .logo-main {
            max-width: 260px;
            height: auto;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.1));
            transition: transform 0.3s;
        }
        .logo-main:hover { transform: scale(1.02); }

        .btn-back-link {
            position: absolute;
            top: 30px;
            left: 30px;
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
            text-decoration: none;
            transition: 0.3s;
            z-index: 100;
        }
        .btn-back-link:hover { color: #fff; transform: translateX(-5px); text-decoration: none; }

        @media (max-width: 992px) {
            body { padding: 0; background-attachment: fixed; }
            .checkout-main-container { border-radius: 0; box-shadow: none; border: none; }
            .btn-back-link { color: var(--primary); top: 15px; left: 15px; }
        }
    </style>
</head>
<body>

    <a href="javascript:history.back()" class="btn-back-link d-none d-lg-block">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>

    <div class="checkout-main-container">
        <div class="header-enterprise">
            <img src="/assets/img/logo_oficial.png" alt="Basiléia Vendas" class="logo-main" 
                 onerror="this.src='https://i.imgur.com/uRjE87c.png';">
        </div>

        @yield('content')
        
        <div class="text-center py-4 bg-light border-top" style="opacity: 0.8;">
            <p class="small text-muted mb-0 font-weight-600">&copy; {{ date('Y') }} Basiléia Vendas - Enterprise Cloud Operations</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
