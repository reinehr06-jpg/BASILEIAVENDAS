<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pagamento;
use App\Models\Venda;

class PagamentoController extends Controller
{
    // ==========================================
    // VENDEDOR: Pagamentos das minhas vendas
    // ==========================================
    public function indexVendedor()
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return redirect()->route('vendedor.dashboard')
                ->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        // Pagamentos ativos (exclui cancelados de vendas canceladas/expiradas)
        $pagamentos = Pagamento::where('vendedor_id', $vendedor->id)
            ->whereNotIn('status', ['cancelado'])
            ->with(['venda', 'cliente'])
            ->orderByDesc('created_at')
            ->get();

        // Cobranças de vendas ativas (excluir vendas canceladas/expiradas)
        $vendasComCobrancas = Venda::where('vendedor_id', $vendedor->id)
            ->whereNotIn('status', ['Cancelado', 'Expirado'])
            ->with(['cliente', 'cobrancas'])
            ->whereHas('cobrancas')
            ->orderByDesc('created_at')
            ->get();

        return view('vendedor.pagamentos.index', compact('pagamentos', 'vendasComCobrancas'));
    }

    // ==========================================
    // MASTER: Todos os pagamentos
    // ==========================================
    public function indexMaster()
    {
        // Pagamentos ativos (exclui cancelados)
        $pagamentos = Pagamento::whereNotIn('status', ['cancelado'])
            ->with(['venda', 'cliente', 'vendedor.user'])
            ->orderByDesc('created_at')
            ->get();

        // Cobranças de vendas ativas
        $vendasComCobrancas = Venda::whereNotIn('status', ['Cancelado', 'Expirado'])
            ->with(['cliente', 'vendedor.user', 'cobrancas'])
            ->whereHas('cobrancas')
            ->orderByDesc('created_at')
            ->get();

        return view('master.pagamentos.index', compact('pagamentos', 'vendasComCobrancas'));
    }
}
