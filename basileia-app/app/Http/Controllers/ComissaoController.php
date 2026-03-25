<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comissao;
use App\Models\Vendedor;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ComissaoController extends Controller
{
    /**
     * Tela de comissões do vendedor
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return redirect()->route('vendedor.dashboard')
                ->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $tipo = $request->get('tipo');
        $status = $request->get('status');

        $query = Comissao::where('vendedor_id', $vendedor->id)
            ->where('competencia', $mes)
            ->with(['cliente', 'venda']);

        if ($tipo) $query->where('tipo_comissao', $tipo);
        if ($status) $query->where('status', $status);

        $comissoes = $query->orderByDesc('created_at')->paginate(20);

        // Cards de resumo
        $todas = Comissao::where('vendedor_id', $vendedor->id)->where('competencia', $mes)->get();
        $resumo = [
            'pendente' => $todas->where('status', 'pendente')->sum('valor_comissao'),
            'confirmada' => $todas->where('status', 'confirmada')->sum('valor_comissao'),
            'paga' => $todas->where('status', 'paga')->sum('valor_comissao'),
            'recorrencias' => $todas->where('tipo_comissao', 'recorrencia')->count(),
            'total' => $todas->sum('valor_comissao'),
        ];

        return view('vendedor.comissoes.index', compact('comissoes', 'resumo', 'mes', 'tipo', 'status', 'vendedor'));
    }

    /**
     * Tela de comissões do master (visão global)
     */
    public function indexMaster(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');
        $tipo = $request->get('tipo');
        $status = $request->get('status');

        $query = Comissao::where('competencia', $mes)
            ->with(['cliente', 'venda', 'vendedor.user']);

        if ($vendedorId) $query->where('vendedor_id', $vendedorId);
        if ($tipo) $query->where('tipo_comissao', $tipo);
        if ($status) $query->where('status', $status);

        $comissoes = $query->orderByDesc('created_at')->paginate(20);

        // Cards de resumo (global)
        $todasQuery = Comissao::where('competencia', $mes);
        if ($vendedorId) $todasQuery->where('vendedor_id', $vendedorId);
        $todas = $todasQuery->get();

        $resumo = [
            'pendente' => $todas->where('status', 'pendente')->sum('valor_comissao'),
            'confirmada' => $todas->where('status', 'confirmada')->sum('valor_comissao'),
            'paga' => $todas->where('status', 'paga')->sum('valor_comissao'),
            'recorrencias' => $todas->where('tipo_comissao', 'recorrencia')->count(),
            'total' => $todas->sum('valor_comissao'),
        ];

        $vendedores = Vendedor::with('user')->get();

        return view('master.comissoes.index', compact('comissoes', 'resumo', 'mes', 'vendedorId', 'tipo', 'status', 'vendedores'));
    }

    /**
     * API: Listar comissões
     */
    public function apiListar(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');
        $tipo = $request->get('tipo');
        $status = $request->get('status');

        $query = Comissao::where('competencia', $mes)
            ->with(['cliente', 'venda', 'vendedor.user']);

        if ($vendedorId) $query->where('vendedor_id', $vendedorId);
        if ($tipo) $query->where('tipo_comissao', $tipo);
        if ($status) $query->where('status', $status);

        $comissoes = $query->orderByDesc('created_at')->get();

        return response()->json($comissoes->map(fn ($c) => [
            'id' => $c->id,
            'vendedor' => $c->vendedor->user->name ?? 'N/A',
            'cliente' => $c->cliente->nome_igreja ?? $c->cliente->nome,
            'documento' => $c->cliente->documento,
            'venda_id' => $c->venda_id,
            'valor_venda' => $c->valor_venda,
            'percentual' => $c->percentual_aplicado,
            'valor_comissao' => $c->valor_comissao,
            'tipo' => $c->tipo_comissao,
            'data_pagamento' => $c->data_pagamento?->format('Y-m-d'),
            'competencia' => $c->competencia,
            'status' => $c->status,
        ]));
    }

    /**
     * API: Resumo das comissões
     */
    public function apiResumo(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');

        $query = Comissao::where('competencia', $mes);
        if ($vendedorId) $query->where('vendedor_id', $vendedorId);

        $todas = $query->get();

        return response()->json([
            'mes' => $mes,
            'total_comissao' => round($todas->sum('valor_comissao'), 2),
            'pendente' => round($todas->where('status', 'pendente')->sum('valor_comissao'), 2),
            'confirmada' => round($todas->where('status', 'confirmada')->sum('valor_comissao'), 2),
            'paga' => round($todas->where('status', 'paga')->sum('valor_comissao'), 2),
            'recorrencias' => $todas->where('tipo_comissao', 'recorrencia')->count(),
            'iniciais' => $todas->where('tipo_comissao', 'inicial')->count(),
            'total_registros' => $todas->count(),
        ]);
    }

    /**
     * Exportar comissões em CSV (compatível com Excel)
     */
    public function exportar(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');
        $user = Auth::user();

        $query = Comissao::where('competencia', $mes)
            ->with(['cliente', 'venda', 'vendedor.user']);

        // Se for vendedor, só exporta as próprias comissões
        if ($user->perfil === 'vendedor' && $user->vendedor) {
            $query->where('vendedor_id', $user->vendedor->id);
        } elseif ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        $comissoes = $query->orderBy('vendedor_id')->orderBy('created_at')->get();

        $nomeArquivo = "comissoes_{$mes}";
        if ($vendedorId) {
            $vendedor = Vendedor::with('user')->find($vendedorId);
            $nomeArquivo .= '_' . str_replace(' ', '_', strtolower($vendedor->user->name ?? 'vendedor'));
        }
        $nomeArquivo .= '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
        ];

        $callback = function () use ($comissoes) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8 (Excel reconhece acentos)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalho
            fputcsv($file, [
                'Vendedor',
                'Cliente (Igreja)',
                'Responsável',
                'CPF/CNPJ',
                'ID Venda',
                'Valor da Venda (R$)',
                '% Comissão',
                'Valor Comissão (R$)',
                'Tipo',
                'Status',
                'Data Pagamento',
                'Competência',
            ]);

            foreach ($comissoes as $c) {
                fputcsv($file, [
                    $c->vendedor->user->name ?? 'N/A',
                    $c->cliente->nome_igreja ?? $c->cliente->nome ?? 'N/A',
                    $c->cliente->nome_pastor ?? $c->cliente->nome_responsavel ?? 'N/A',
                    $c->cliente->documento ?? 'N/A',
                    $c->venda_id,
                    number_format($c->valor_venda, 2, ',', '.'),
                    $c->percentual_aplicado . '%',
                    number_format($c->valor_comissao, 2, ',', '.'),
                    ucfirst($c->tipo_comissao),
                    ucfirst($c->status),
                    $c->data_pagamento ? $c->data_pagamento->format('d/m/Y') : '-',
                    $c->competencia,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
