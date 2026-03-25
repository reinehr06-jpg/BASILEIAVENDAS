<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basiléia Vendas - Login</title>
    <style>
        :root {
            --primary: #581c87; /* Roxo profundo inspirado no logo Basiléia */
            --primary-hover: #4c1d95;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body {
            background-color: var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-main);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 600px;
            background: var(--surface);
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            overflow: hidden;
            margin: 20px;
        }

        .login-brand {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), #3b0764);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: white;
            text-align: center;
        }

        .login-brand h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin-bottom: 10px;
        }

        .login-brand p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 300px;
            line-height: 1.5;
        }

        .login-form-container {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 1.8rem;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
            background: var(--background);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(88, 28, 135, 0.1);
            background: white;
        }

        .error-message {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .logo-placeholder {
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 3rem;
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
                margin: 0;
                border-radius: 0;
            }
            .login-brand {
                padding: 40px 20px;
            }
            .login-form-container {
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-brand">
            <div class="logo-placeholder">B</div>
            <h1>Basiléia Vendas</h1>
            <p>Controle Comercial, Pagamentos Assinaturas e Gestão de Vendedores.</p>
        </div>
        
        <div class="login-form-container">
            <div class="login-header">
                <h2>Acesso Restrito</h2>
                <p>Insira suas credenciais Master para continuar.</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="email">E-mail de Acesso</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus placeholder="Digite seu e-mail de acesso">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Senha Master</label>
                    <input type="password" id="password" name="password" class="form-input" required placeholder="Digite sua senha">
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit">
                    Entrar no Sistema
                </button>
            </form>
        </div>
    </div>

</body>
</html>
