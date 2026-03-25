<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meta;
use App\Models\Vendedor;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetaController extends Controller
{
    /**
     * Listar metas (Web)
     */
    public function index(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');

        $query = Meta::with('vendedor.user')->where('mes_referencia', $mes);
        if ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        }

        $metas = $query->get()->map(function ($meta) {
            return $this->carregarDadosPerformance($meta);
        });

        $vendedores = Vendedor::with('user')->get();
        $resumo = $this->getResumoDados($mes, $vendedorId);

        return view('master.metas.index', compact('metas', 'vendedores', 'resumo', 'mes', 'vendedorId'));
    }

    /**
     * Criar nova meta
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendedor_id' => 'required|exists:vendedores,id',
            'mes_referencia' => 'required|date_format:Y-m',
            'valor_meta' => 'required|numeric|min:0',
            'observacao' => 'nullable|string',
            'status' => 'required|in:não iniciada,em andamento,atingida,não atingida,superada',
        ]);

        // Verificar se já existe meta para este vendedor/mês
        $exists = Meta::where('vendedor_id', $request->vendedor_id)
                      ->where('mes_referencia', $request->mes_referencia)
                      ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Este vendedor já possui uma meta cadastrada para este mês.'])->withInput();
        }

        Meta::create($request->all());

        return redirect()->route('master.metas')->with('success', 'Meta criada com sucesso!');
    }

    /**
     * Atualizar meta
     */
    public function update(Request $request, $id)
    {
        $meta = Meta::findOrFail($id);

        $request->validate([
            'valor_meta' => 'required|numeric|min:0',
            'observacao' => 'nullable|string',
            'status' => 'required|in:não iniciada,em andamento,atingida,não atingida,superada',
        ]);

        $meta->update($request->only(['valor_meta', 'observacao', 'status']));

        return redirect()->route('master.metas')->with('success', 'Meta atualizada com sucesso!');
    }

    /**
     * Excluir meta
     */
    public function destroy($id)
    {
        $meta = Meta::findOrFail($id);
        $meta->delete();

        return redirect()->route('master.metas')->with('success', 'Meta excluída com sucesso!');
    }

    /**
     * API: Resumo das metas
     */
    public function apiResumo(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        return response()->json($this->getResumoDados($mes));
    }

    /**
     * API: Listar metas
     */
    public function apiListar(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->format('Y-m'));
        $vendedorId = $request->get('vendedor_id');

        $query = Meta::with('vendedor.user')->where('mes_referencia', $mes);
        if ($vendedorId) $query->where('vendedor_id', $vendedorId);

        $metas = $query->get()->map(function($m) {
            $perf = $this->carregarDadosPerformance($m);
            return [
                'id' => $m->id,
                'vendedor_id' => $m->vendedor_id,
                'vendedor_nome' => $m->vendedor->user->name ?? 'N/A',
                'mes_referencia' => $m->mes_referencia,
                'valor_meta' => $m->valor_meta,
                'valor_vendido' => $perf->valor_vendido,
                'valor_recebido' => $perf->valor_recebido,
                'percentual_atingido' => $perf->percentual,
                'status_meta' => $m->status,
            ];
        });

        return response()->json($metas);
    }

    /**
     * Helper: Carregar dados de performance para uma meta
     */
    private function carregarDadosPerformance($meta)
    {
        $dataInicio = Carbon::parse($meta->mes_referencia . '-01')->startOfMonth();
        $dataFim    = (clone $dataInicio)->endOfMonth();

        // Valor Vendido (Vendas não canceladas/expiradas)
        $meta->valor_vendido = Venda::where('vendedor_id', $meta->vendedor_id)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->whereNotIn('status', ['Cancelado', 'Expirado'])
            ->sum('valor');

        // Valor Recebido (Pagamentos pagos e não estornados)
        $meta->valor_recebido = Pagamento::where('vendedor_id', $meta->vendedor_id)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->where('status', 'pago')
            ->sum('valor');

        // Clientes Ativos (com pagamento no mês)
        $meta->clientes_ativos = Pagamento::where('vendedor_id', $meta->vendedor_id)
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->where('status', 'pago')
            ->distinct('cliente_id')
            ->count('cliente_id');

        // Percentual (Baseado no Valor Recebido conforme recomendação financeira)
        $meta->percentual = $meta->valor_meta > 0 
            ? round(($meta->valor_recebido / $meta->valor_meta) * 100, 1) 
            : 0;

        return $meta;
    }

    /**
     * Helper: Obter resumo consolidado para os cards
     */
    private function getResumoDados($mes, $vendedorId = null)
    {
        $query = Meta::where('mes_referencia', $mes);
        if ($vendedorId) $query->where('vendedor_id', $vendedorId);
        
        $metas = $query->get();
        $totalMetas = $metas->count();
        
        $valorTotalMeta = $metas->sum('valor_meta');
        $valorTotalRealizado = 0;
        $metasBatidas = 0;
        $metasAbaixo = 0;
        $totalPercentage = 0;

        foreach ($metas as $m) {
            $perf = $this->carregarDadosPerformance($m);
            $valorTotalRealizado += $perf->valor_recebido;
            
            if ($perf->percentual >= 100) {
                $metasBatidas++;
            } else {
                $metasAbaixo++;
            }
            $totalPercentage += $perf->percentual;
        }

        return [
            'total_metas' => $totalMetas,
            'metas_batidas' => $metasBatidas,
            'metas_abaixo' => $metasAbaixo,
            'valor_total_meta' => $valorTotalMeta,
            'valor_total_realizado' => $valorTotalRealizado,
            'percentual_medio' => $totalMetas > 0 ? round($totalPercentage / $totalMetas, 1) : 0,
            
            // Status counts for API
            'metas_atingidas' => $metas->where('status', 'atingida')->count(),
            'metas_em_andamento' => $metas->where('status', 'em andamento')->count(),
            'metas_nao_atingidas' => $metas->where('status', 'não atingida')->count(),
        ];
    }
}
