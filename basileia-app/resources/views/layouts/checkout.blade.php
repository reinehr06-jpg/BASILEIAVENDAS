<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Check-out Seguro - Basileia Vendas')</title>
    
    <!-- Design System: Basileia Vendas Minimal Elite -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #7C3AED;
            --primary-dark: #6D28D9;
            --bg: #F9FAFB;
            --radius: 16px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            background-color: var(--bg);
            -webkit-font-smoothing: antialiased;
        }
        
        .checkout-main-wrapper {
            width: 100%;
            max-width: 1060px;
            background: #ffffff;
            border-radius: var(--radius);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #F3F4F6;
            animation: appEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes appEntrance {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 992px) {
            body { padding: 0; }
            .checkout-main-wrapper { border-radius: 0; border: none; }
        }
    </style>
</head>
<body>

    <div class="checkout-main-wrapper">
        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
