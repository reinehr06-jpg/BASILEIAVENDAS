<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Pagamento;
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
        $mesPassado = Carbon::now()->subMonth()->month;
        $anoPassado = Carbon::now()->subMonth()->year;

        // Definir o escopo de vendedores baseado no perfil
        $vendedorIds = null;
        $isPersonal = false;
        
        if ($user->perfil === 'vendedor') {
            $vendedorIds = [$user->vendedor->id ?? 0];
            $isPersonal = true;
        } elseif ($user->perfil === 'gestor') {
            $vendedorIds = Vendedor::where('gestor_id', $user->id)
                ->orWhere('usuario_id', $user->id) // O gestor também vê suas próprias vendas
                ->pluck('id')
                ->toArray();
        }

        // --- Queries Escopadas ---
        
        // Vendas Ativas (PAGO)
        $queryVendasAtivas = Venda::whereIn(DB::raw('UPPER(status)'), ['PAGO', 'PAGO_ASAAS']);
        if ($vendedorIds) $queryVendasAtivas->whereIn('vendedor_id', $vendedorIds);
        $vendasAtivas = $queryVendasAtivas->count();

        // Tendência de vendas (vs mês passado)
        $queryVendasPassado = Venda::whereIn(DB::raw('UPPER(status)'), ['PAGO', 'PAGO_ASAAS'])
            ->whereMonth('updated_at', $mesPassado)
            ->whereYear('updated_at', $anoPassado);
        if ($vendedorIds) $queryVendasPassado->whereIn('vendedor_id', $vendedorIds);
        $vendasMesPassado = $queryVendasPassado->count();
        $vendasTrend = $vendasMesPassado > 0 ? (($vendasAtivas - $vendasMesPassado) / $vendasMesPassado) * 100 : 0;

        // Vendedores Ativos (apenas para Master e Gestor)
        $vendedoresAtivos = 0;
        if ($user->perfil === 'master') {
            $vendedoresAtivos = User::where('perfil', 'vendedor')->whereRaw('UPPER(status) = ?', ['ATIVO'])->count();
        } elseif ($user->perfil === 'gestor') {
            $vendedoresAtivos = Vendedor::where('gestor_id', $user->id)->count();
        }

        // Comissões Pendentes (Vendas pagas que ainda não tiveram comissão quitada)
        $queryComisPend = Venda::whereIn(DB::raw('UPPER(status)'), ['PAGO', 'PAGO_ASAAS']);
        if ($vendedorIds) $queryComisPend->whereIn('vendedor_id', $vendedorIds);
        $comissoesPendentes = $queryComisPend->sum('comissao_gerada');
        // Clone the query builder to apply additional conditions for contagemPendentes
        $queryContagemPendentes = clone $queryComisPend;
        $contagemPendentes = $queryContagemPendentes->where('comissao_gerada', '>', 0)->count();

        // Faturamento (MTD) - Soma do valor real de cada parcela paga/recebida
        $queryPagamentos = Pagamento::whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
            ->whereMonth('pagamentos.updated_at', $mesAtual)
            ->whereYear('pagamentos.updated_at', $anoAtual)
            ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
            ->whereNotIn(DB::raw('UPPER(vendas.status)'), ['ESTORNADO', 'CANCELADO', 'EXPIRADO']);
        if ($vendedorIds) $queryPagamentos->whereIn('vendas.vendedor_id', $vendedorIds);
        $totalRecebido = $queryPagamentos->sum('pagamentos.valor');

        // Tendência de faturamento (vs mês passado)
        $queryPagamentosPassado = Pagamento::whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
            ->whereMonth('pagamentos.updated_at', $mesPassado)
            ->whereYear('pagamentos.updated_at', $anoPassado)
            ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
            ->whereNotIn(DB::raw('UPPER(vendas.status)'), ['ESTORNADO', 'CANCELADO', 'EXPIRADO']);
        if ($vendedorIds) $queryPagamentosPassado->whereIn('vendas.vendedor_id', $vendedorIds);
        $recebidoMesPassado = $queryPagamentosPassado->sum('pagamentos.valor');
        $recebidoTrend = $recebidoMesPassado > 0 ? (($totalRecebido - $recebidoMesPassado) / $recebidoMesPassado) * 100 : 0;

        // Clientes Ativos (Apenas quem já teve pelo menos um pagamento confirmado)
        $queryClientes = Cliente::whereHas('vendas.pagamentos', function($q) use ($vendedorIds) {
            $q->whereIn('status', ['RECEIVED', 'CONFIRMED', 'pago', 'PAGO']);
            if ($vendedorIds) $q->whereIn('vendas.vendedor_id', $vendedorIds);
        });
        $clientesAtivos = $queryClientes->count();

        // Churn do mês
        $queryChurn = Venda::whereIn('status', ['Estornado', 'Cancelado', 'Expirado', 'Vencido'])
            ->where('comissao_gerada', '>', 0)
            ->whereMonth('updated_at', $mesAtual)
            ->whereYear('updated_at', $anoAtual);
        if ($vendedorIds) $queryChurn->whereIn('vendedor_id', $vendedorIds);
        $churnMes = $queryChurn->count();

        // Renovações do mês
        $queryRenov = Cobranca::whereIn('cobrancas.status', ['RECEIVED', 'pago', 'PAGO'])
            ->whereMonth('cobrancas.created_at', $mesAtual)
            ->join('vendas', 'cobrancas.venda_id', '=', 'vendas.id');
        if ($vendedorIds) $queryRenov->whereIn('vendas.vendedor_id', $vendedorIds);
        $renovacoesMes = $queryRenov->count();

        // Gráfico Semanal
        $driver = DB::getDriverName();
        $weekFormat = $driver === 'pgsql' ? "TO_CHAR(pagamentos.updated_at, 'IW')" : "strftime('%W', pagamentos.updated_at)";
        
        $queryGrafico = Pagamento::selectRaw("$weekFormat as semana, sum(pagamentos.valor) as total")
            ->join('vendas', 'pagamentos.venda_id', '=', 'vendas.id')
            ->whereIn('pagamentos.status', ['RECEIVED', 'pago', 'PAGO', 'CONFIRMED'])
            ->whereNotIn(DB::raw('UPPER(vendas.status)'), ['ESTORNADO', 'CANCELADO', 'EXPIRADO'])
            ->whereYear('pagamentos.updated_at', $anoAtual);
        if ($vendedorIds) $queryGrafico->whereIn('vendas.vendedor_id', $vendedorIds);
        $faturamentoSemanal = $queryGrafico->groupBy('semana')->orderBy('semana')->limit(4)->get();

        // Melhor faixa
        $dayFormat = $driver === 'pgsql' ? "TO_CHAR(cobrancas.updated_at, 'DD')" : "strftime('%d', cobrancas.updated_at)";
        
        $queryFaixa = Cobranca::selectRaw("$dayFormat as dia, count(*) as total")
            ->join('vendas', 'cobrancas.venda_id', '=', 'vendas.id')
            ->where('cobrancas.status', 'RECEIVED');
        if ($vendedorIds) $queryFaixa->whereIn('vendas.vendedor_id', $vendedorIds);
        $historicoDias = $queryFaixa->groupBy('dia')->orderByDesc('total')->first();
        
        $melhorFaixa = "Sem dados";
        if ($historicoDias && $historicoDias->dia) {
            $dia = (int)$historicoDias->dia;
            if ($dia <= 10) $melhorFaixa = "Dias 01 a 10";
            elseif ($dia <= 20) $melhorFaixa = "Dias 11 a 20";
            else $melhorFaixa = "Dias 21 a 31";
        }

        $tituloSessao = match($user->perfil) {
            'master' => 'Visão Global da Operação',
            'gestor' => 'Performance da Equipe',
            default => 'Minha Performance Individual'
        };

        return view('dashboard', compact(
            'vendasAtivas', 'vendedoresAtivos', 'comissoesPendentes', 
            'totalRecebido', 'clientesAtivos', 'churnMes',
            'melhorFaixa', 'renovacoesMes', 'vendasTrend', 'recebidoTrend',
            'contagemPendentes', 'faturamentoSemanal', 'tituloSessao', 'isPersonal'
        ));
    }
}
