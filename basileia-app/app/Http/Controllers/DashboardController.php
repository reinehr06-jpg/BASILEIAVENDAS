<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $mesAtual = Carbon::now()->month;
        $anoAtual = Carbon::now()->year;
        
        if ($user->perfil === 'master') {
            // 3.1: 8 Cards Analíticos para o Painel Master
            $vendasAtivas = Venda::whereRaw('UPPER(status) = ?', ['PAGO'])->count();
            
            $vendedoresAtivos = User::where('perfil', 'vendedor')
                ->whereRaw('UPPER(status) = ?', ['ATIVO'])
                ->count();
            
            $comissoesPendentes = Venda::whereRaw('UPPER(status) = ?', ['PAGO'])->sum('comissao_gerada');
            
            $totalRecebido = Cobranca::whereIn('cobrancas.status', ['RECEIVED', 'pago', 'PAGO'])
                ->whereMonth('cobrancas.updated_at', $mesAtual)
                ->whereYear('cobrancas.updated_at', $anoAtual)
                ->join('vendas', 'cobrancas.venda_id', '=', 'vendas.id')
                ->sum('vendas.valor');
            
            $clientesAtivos = Cliente::whereHas('vendas', function($q) {
                $q->where('status', 'Pago');
            })->count();

            // Churn do mês (Novo critério: Vendas que foram pagas e depois canceladas)
            $churnMes = Venda::whereIn('status', ['Estornado', 'Cancelado', 'Expirado', 'Vencido'])
                ->where('comissao_gerada', '>', 0)
                ->whereMonth('updated_at', $mesAtual)
                ->whereYear('updated_at', $anoAtual)
                ->count();
            
            // Desistências do mês (Vendas canceladas que nunca foram pagas)
            $desistenciasMes = Venda::whereIn('status', ['Cancelado', 'Expirado', 'Vencido'])
                ->where('comissao_gerada', '<=', 0)
                ->whereMonth('updated_at', $mesAtual)
                ->whereYear('updated_at', $anoAtual)
                ->count();

            // Renovações do mês
            $renovacoesMes = Cobranca::whereIn('status', ['RECEIVED', 'pago', 'PAGO'])
                ->whereMonth('created_at', $mesAtual)
                ->count();

            // Melhor faixa de recebimento simulado (baseado no dia do mês em que mais se completam cobranças)
            $melhorFaixa = "Sem dados suficientes no período";
            
            // Tratamento genérico SQL para obter o dia (Compatibilidade com SQLite)
            $historicoDias = Cobranca::selectRaw("strftime('%d', updated_at) as dia, count(*) as total")
                ->where('status', 'RECEIVED')
                ->groupBy('dia')
                ->orderByDesc('total')
                ->first();
                
            if ($historicoDias && $historicoDias->dia) {
                $dia = (int)$historicoDias->dia;
                if ($dia <= 10) $melhorFaixa = "Dias 01 a 10";
                elseif ($dia <= 20) $melhorFaixa = "Dias 11 a 20";
                else $melhorFaixa = "Dias 21 a 31";
            }
            
            return view('dashboard', compact(
                'vendasAtivas', 'vendedoresAtivos', 'comissoesPendentes', 
                'totalRecebido', 'clientesAtivos', 'churnMes', 'desistenciasMes',
                'melhorFaixa', 'renovacoesMes'
            ));
        }

        // Vendedor Dashboard
        $vendedorId = $user->vendedor->id ?? 0;
        
        $vendasAtivas = Venda::where('vendedor_id', $vendedorId)
            ->whereRaw('UPPER(status) = ?', ['PAGO'])
            ->count();
        $comissoesPendentes = Venda::where('vendedor_id', $vendedorId)
            ->whereRaw('UPPER(status) = ?', ['PAGO'])
            ->sum('comissao_gerada');
        
        return view('dashboard', [
            'vendasAtivas' => $vendasAtivas,
            'vendedoresAtivos' => 1,
            'comissoesPendentes' => $comissoesPendentes,
            'totalRecebido' => 0, 'clientesAtivos' => 0, 'churnMes' => 0, 'melhorFaixa' => 'N/A', 'renovacoesMes' => 0
        ]);
    }
}
