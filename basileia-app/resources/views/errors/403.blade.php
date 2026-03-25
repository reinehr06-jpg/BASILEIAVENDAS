@extends('layouts.app')

@section('title', 'Acesso Negado')

@section('content')
    <div class="card" style="text-align: center; padding: 60px 20px; max-width: 600px; margin: 0 auto; border-top: 4px solid var(--error);">
        
        <div style="background: rgba(239, 68, 68, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--error);">
             <svg style="width: 40px; height: 40px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>

        <h2 style="margin-bottom: 12px; color: var(--error); font-size: 1.5rem;">Acesso não autorizado</h2>
        <p style="color: var(--text-main); margin-bottom: 30px; font-weight: 500; font-size: 1.05rem;">Você não possui permissão para acessar esta área.</p>
        
        <a href="{{ url('/') }}" style="display: inline-block; padding: 10px 24px; background: var(--primary); color: white; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background 0.2s;">
            Voltar ao Início
        </a>
    </div>
@endsection
