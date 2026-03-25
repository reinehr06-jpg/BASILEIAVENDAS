<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Vendedor;
use App\Services\ClienteStatusService;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    /**
     * Tela de listagem de clientes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isMaster = $user->perfil === 'master';
        
        $query = Cliente::query();
        
        // Se for vendedor, filtrar apenas os clientes que tem vendas com ele
        if (!$isMaster) {
            $query->whereHas('vendas', function ($q) use ($user) {
                $q->whereHas('vendedor', function ($v) use ($user) {
                    $v->where('user_id', $user->id);
                });
            });
        }
        
        // Filtros
        if ($request->filled('busca')) {
            $busca = $request->get('busca');
            $query->where(function($q) use ($busca) {
                $q->where('nome_igreja', 'like', "%{$busca}%")
                  ->orWhere('nome_pastor', 'like', "%{$busca}%")
                  ->orWhere('documento', 'like', "%{$busca}%");
            });
        }
        
        // Default: mostrar apenas clientes ativos
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        } else {
            $query->where('status', 'ativo');
        }
        
        // Sincronizar status dos clientes antes de listar
        $clientesParaSync = (clone $query)->with('vendas.pagamentos')->get();
        ClienteStatusService::sincronizarStatus($clientesParaSync);

        // Re-carregar com status atualizado
        $clientes = Cliente::query();
        if (!$isMaster) {
            $clientes->whereHas('vendas', function ($q) use ($user) {
                $q->whereHas('vendedor', function ($v) use ($user) {
                    $v->where('user_id', $user->id);
                });
            });
        }
        if ($request->filled('busca')) {
            $busca = $request->get('busca');
            $clientes->where(function($q) use ($busca) {
                $q->where('nome_igreja', 'like', "%{$busca}%")
                  ->orWhere('nome_pastor', 'like', "%{$busca}%")
                  ->orWhere('documento', 'like', "%{$busca}%");
            });
        }
        if ($request->filled('status')) {
            $clientes->where('status', $request->get('status'));
        } else {
            $clientes->where('status', 'ativo');
        }
        $clientes = $clientes->with('vendas')->orderBy('created_at', 'desc')->paginate(15);

        // Cards de Resumo (baseado em TODOS os clientes, não apenas os filtrados)
        $allClientes = Cliente::query();
        if (!$isMaster) {
            $allClientes->whereHas('vendas', function ($q) use ($user) {
                $q->whereHas('vendedor', function ($v) use ($user) {
                    $v->where('user_id', $user->id);
                });
            });
        }
        $cards = [
            'total' => (clone $allClientes)->count(),
            'ativos' => (clone $allClientes)->where('status', 'ativo')->count(),
            'pendentes' => (clone $allClientes)->where('status', 'pendente')->count(),
            'inadimplentes' => (clone $allClientes)->where('status', 'inadimplente')->count(),
            'churn' => (clone $allClientes)->where('status', 'churn')->count(),
            'cancelados' => (clone $allClientes)->where('status', 'cancelado')->count(),
        ];
        
        $viewPath = $isMaster ? 'master.clientes.index' : 'vendedor.clientes.index';
        
        return view($viewPath, compact('clientes', 'cards', 'isMaster'));
    }

    /**
     * Tela de Detalhes do Cliente
     */
    public function show($id)
    {
        $user = Auth::user();
        $isMaster = $user->perfil === 'master';
        
        $cliente = Cliente::with(['vendas.vendedor.user', 'vendas.pagamentos'])->findOrFail($id);
        
        // Sincronizar status deste cliente
        ClienteStatusService::atualizarCliente($cliente);
        $cliente->refresh();
        
        // Se for vendedor, garantir que ele tem acesso a este cliente
        if (!$isMaster) {
            $temAcesso = $cliente->vendas()->whereHas('vendedor', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->exists();
            
            if (!$temAcesso) {
                abort(403, 'Acesso não autorizado a este cliente.');
            }
        }
        
        // Buscar histórico de vendas e pagamentos do cliente
        $vendas = $cliente->vendas()->orderBy('created_at', 'desc')->get();
        
        // Obter pagamentos através das vendas
        $pagamentos = collect();
        foreach ($vendas as $venda) {
            foreach ($venda->pagamentos as $pagamento) {
                $pagamentos->push($pagamento);
            }
        }
        $pagamentos = $pagamentos->sortByDesc('data_vencimento')->values();
        
        $viewPath = $isMaster ? 'master.clientes.show' : 'vendedor.clientes.show';
        
        return view($viewPath, compact('cliente', 'vendas', 'pagamentos', 'isMaster'));
    }

    /**
     * Atualizar Status do Cliente via API/AJAX
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:ativo,pendente,inadimplente,cancelado,churn'
        ]);
        
        $cliente = Cliente::findOrFail($id);
        $cliente->status = $request->status;
        $cliente->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Status do cliente atualizado com sucesso.',
            'status' => $cliente->status
        ]);
    }
}
