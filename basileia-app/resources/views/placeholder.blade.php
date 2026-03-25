@extends('layouts.app')

@section('title', $titulo ?? 'Módulo em Construção')

@section('content')
    <div class="card" style="text-align: center; padding: 60px 20px; max-width: 600px; margin: 0 auto;">
        
        <div style="background: rgba(88, 28, 135, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: var(--primary);">
             <svg style="width: 40px; height: 40px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>

        <h2 style="margin-bottom: 12px; color: var(--text-main); font-size: 1.5rem;">{{ $titulo ?? 'Funcionalidade' }}</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px; line-height: 1.5;">Esta área está em desenvolvimento e será disponibilizada em breve. Nossa equipe está construindo para você.</p>
        
        <div style="padding: 40px; border: 2px dashed var(--border); border-radius: 12px; background: #fafafa; color: #a1a1aa; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <svg style="width: 32px; height: 32px; margin-bottom: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
            <p style="font-weight: 500;">nenhum dado disponível ainda</p>
        </div>
    </div>
@endsection
