<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basiléia Vendas - @yield('title')</title>
    <style>
        :root {
            --primary: #581c87;
            --primary-hover: #4c1d95;
            --background: #f1f5f9;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, sans-serif; }
        body { background: var(--background); color: var(--text-main); display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 50; }
        .sidebar-brand { padding: 20px; font-size: 1.5rem; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-user { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-user h3 { font-size: 1rem; margin-bottom: 5px; }
        .sidebar-user span { font-size: 0.75rem; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 12px; font-weight: 600; text-transform: uppercase;}
        
        .sidebar-menu { padding: 20px 0; flex-grow: 1; overflow-y: auto; }
        .menu-item { display: block; padding: 12px 24px; color: rgba(255,255,255,0.8); text-decoration: none; transition: background 0.2s; font-weight: 500; font-size: 0.95rem; }
        .menu-item:hover, .menu-item.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid white; padding-left: 20px;}
        
        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .btn-logout { width: 100%; padding: 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-align: center; display: block; text-decoration: none; transition: 0.2s; }
        .btn-logout:hover { background: #dc2626; }

        /* Main Content */
        .main-content { margin-left: 260px; flex-grow: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: var(--surface); padding: 20px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 40; }
        .topbar h1 { font-size: 1.5rem; font-weight: 600; color: var(--text-main); }
        .content-area { padding: 30px; flex-grow: 1; }
        
        /* Card utility */
        .card { background: var(--surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">Basiléia Vendas</div>
        <div class="sidebar-user">
            <h3>{{ Auth::user()->name }}</h3>
            <span>{{ Auth::user()->perfil }}</span>
        </div>
        <nav class="sidebar-menu">
            @if(Auth::user()->perfil === 'master')
                <a href="{{ route('master.dashboard') }}" class="menu-item {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('master.vendedores') }}" class="menu-item {{ request()->routeIs('master.vendedores') ? 'active' : '' }}">Vendedores</a>
                <a href="{{ route('master.vendas') }}" class="menu-item {{ request()->routeIs('master.vendas') ? 'active' : '' }}">Vendas</a>
                <a href="{{ route('master.pagamentos') }}" class="menu-item {{ request()->routeIs('master.pagamentos') ? 'active' : '' }}">Pagamentos</a>
                <a href="{{ route('master.relatorios') }}" class="menu-item {{ request()->routeIs('master.relatorios') ? 'active' : '' }}">Relatórios</a>
                <a href="{{ route('master.metas') }}" class="menu-item {{ request()->routeIs('master.metas') ? 'active' : '' }}">Metas</a>
                <a href="{{ route('master.clientes') }}" class="menu-item {{ request()->routeIs('master.clientes') ? 'active' : '' }}">Clientes</a>
                <a href="{{ route('master.configuracoes') }}" class="menu-item {{ request()->routeIs('master.configuracoes*') ? 'active' : '' }}">Configurações</a>
            @else
                <a href="{{ route('vendedor.dashboard') }}" class="menu-item {{ request()->routeIs('vendedor.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('vendedor.vendas') }}" class="menu-item {{ request()->routeIs('vendedor.vendas') ? 'active' : '' }}">Vendas</a>
                <a href="{{ route('vendedor.pagamentos') }}" class="menu-item {{ request()->routeIs('vendedor.pagamentos') ? 'active' : '' }}">Pagamentos</a>
                <a href="{{ route('vendedor.clientes') }}" class="menu-item {{ request()->routeIs('vendedor.clientes') ? 'active' : '' }}">Clientes</a>
                <a href="{{ route('vendedor.comissao') }}" class="menu-item {{ request()->routeIs('vendedor.comissao') ? 'active' : '' }}">Comissão</a>
            @endif
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sair do Sistema</button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>@yield('title')</h1>
            <!-- Pode adicionar sininho de notificações ou dropdown do usuário aqui no futuro -->
        </header>
        <section class="content-area">
            @yield('content')
        </section>
    </main>
</body>
</html>
