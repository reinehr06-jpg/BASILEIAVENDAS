<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Cobranca;
use App\Models\Pagamento;
use App\Models\Vendedor;
use App\Services\AsaasService;
use Carbon\Carbon;

class VendaController extends Controller
{
    // ==========================================
    // Planos Disponíveis (Configuração central)
    // ==========================================
    private static $planos = [
        ['nome' => 'Base',    'min_membros' => 1,    'max_membros' => 50,   'valor_mensal' => 97.00,   'valor_anual' => 970.00],
        ['nome' => 'Start',   'min_membros' => 51,   'max_membros' => 100,  'valor_mensal' => 147.00,  'valor_anual' => 1470.00],
        ['nome' => 'Basic',   'min_membros' => 101,  'max_membros' => 200,  'valor_mensal' => 197.00,  'valor_anual' => 1970.00],
        ['nome' => 'Core',    'min_membros' => 201,  'max_membros' => 500,  'valor_mensal' => 297.00,  'valor_anual' => 2970.00],
        ['nome' => 'Pro',     'min_membros' => 501,  'max_membros' => 1000, 'valor_mensal' => 497.00,  'valor_anual' => 4970.00],
        ['nome' => 'Premium', 'min_membros' => 1001, 'max_membros' => 99999,'valor_mensal' => 797.00,  'valor_anual' => 7970.00],
    ];

    private const MAX_DESCONTO = 15.00; // Desconto máximo permitido em %

    // ==========================================
    // VENDEDOR: Lista de vendas
    // ==========================================
    public function index()
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return redirect()->route('vendedor.dashboard')
                ->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        // Auto-expirar vendas com mais de 72h sem pagamento
        self::expirarVendasAntigas();

        $vendas = Venda::where('vendedor_id', $vendedor->id)
            ->whereNotIn('status', ['Expirado'])
            ->with(['cliente', 'cobrancas', 'pagamentos'])
            ->orderByDesc('created_at')
            ->get();

        $vendasExpiradas = Venda::where('vendedor_id', $vendedor->id)
            ->where('status', 'Expirado')
            ->with(['cliente'])
            ->orderByDesc('created_at')
            ->get();

        return view('vendedor.vendas.index', compact('vendas', 'vendasExpiradas'));
    }

    // ==========================================
    // VENDEDOR: Formulário de nova venda
    // ==========================================
    public function create()
    {
        $planos = self::$planos;
        $maxDesconto = self::MAX_DESCONTO;
        return view('vendedor.vendas.create', compact('planos', 'maxDesconto'));
    }

    // ==========================================
    // VENDEDOR: Ver detalhes da cobrança
    // ==========================================
    public function cobranca($id)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        $venda = Venda::where('vendedor_id', $vendedor->id)
            ->with(['cliente', 'cobrancas', 'pagamentos'])
            ->findOrFail($id);

        // Sincronizar status proativamente ao visualizar detalhes
        $pagamento = $venda->pagamentos->first();
        if ($pagamento) {
            $pagamentoService = new \App\Services\PagamentoService();
            $pagamentoService->sync($pagamento);
            // Recarregar venda após sync
            $venda->load(['cliente', 'cobrancas', 'pagamentos']);
        }

        return view('vendedor.vendas.cobranca', compact('venda'));
    }


    // ==========================================
    // VENDEDOR: Salvar venda
    // ==========================================
    public function store(Request $request)
    {
        $request->validate([
            'nome_igreja'        => 'required|string|max:255',
            'nome_pastor'        => 'required|string|max:255',
            'localidade'         => 'required|string|max:255',
            'moeda'              => 'required|string|max:10',
            'quantidade_membros' => 'required|integer|min:1',
            'documento'          => 'required|string|max:20',
            'whatsapp'           => 'required|string|max:20',
            'email_cliente'      => 'required|email|max:255',
            'plano'              => 'required|string',
            'forma_pagamento'    => 'required|in:PIX,BOLETO,CREDIT_CARD',
            'tipo_negociacao'    => 'required|in:mensal,anual',
            'desconto'           => 'nullable|numeric|min:0|max:' . self::MAX_DESCONTO,
            'observacao'         => 'nullable|string|max:1000',
        ], [
            'documento.required' => 'Informe um CPF ou CNPJ válido.',
            'desconto.max'       => 'O desconto informado ultrapassa o limite permitido (' . self::MAX_DESCONTO . '%).',
            'quantidade_membros.min' => 'Digite a quantidade de membros para sugerir os planos disponíveis.',
        ]);

        // Validação de CPF/CNPJ básica
        $documento = preg_replace('/[^0-9]/', '', $request->documento);
        if (strlen($documento) !== 11 && strlen($documento) !== 14) {
            return back()->withErrors(['documento' => 'Informe um CPF ou CNPJ válido.'])->withInput();
        }

        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return back()->withErrors(['error' => 'Perfil de vendedor não configurado.'])->withInput();
        }

        // Verificar se já existe cliente com mesmo documento (CPF/CNPJ) e cobrança em aberto
        $clienteExistente = Cliente::where('documento', $documento)->first();
        if ($clienteExistente && $clienteExistente->temCobrancaAberta()) {
            return back()->withErrors(['documento' => 'Já existe uma cobrança em aberto para este CPF/CNPJ.'])->withInput();
        }

        // Calcular valor do plano
        $planoSelecionado = collect(self::$planos)->firstWhere('nome', $request->plano);
        if (!$planoSelecionado) {
            return back()->withErrors(['plano' => 'Plano selecionado inválido.'])->withInput();
        }

        $valorBase = $request->tipo_negociacao === 'anual'
            ? $planoSelecionado['valor_anual']
            : $planoSelecionado['valor_mensal'];

        $desconto = floatval($request->desconto ?? 0);
        $valorFinal = $valorBase - ($valorBase * ($desconto / 100));

        try {
            DB::beginTransaction();

            // Criar ou atualizar cliente
            $cliente = Cliente::updateOrCreate(
                ['documento' => $documento],
                [
                    'nome'                => $request->nome_igreja,
                    'nome_igreja'         => $request->nome_igreja,
                    'nome_pastor'         => $request->nome_pastor,
                    'localidade'          => $request->localidade,
                    'moeda'               => $request->moeda,
                    'quantidade_membros'  => $request->quantidade_membros,
                    'whatsapp'            => $request->whatsapp,
                    'contato'             => $request->whatsapp,
                    'email'               => $request->email_cliente,
                ]
            );

            // Criar venda
            $venda = Venda::create([
                'cliente_id'       => $cliente->id,
                'vendedor_id'      => $vendedor->id,
                'valor'            => $valorFinal,
                'comissao_gerada'  => 0, // Comissão só é gerada após pagamento confirmado
                'status'           => 'Aguardando pagamento',
                'plano'            => $request->plano,
                'forma_pagamento'  => $request->forma_pagamento,
                'tipo_negociacao'  => $request->tipo_negociacao,
                'desconto'         => $desconto,
                'observacao'       => $request->observacao,
                'origem'           => 'manual',
                'data_venda'       => Carbon::now(),
            ]);

            // 9.3 — Integrar com Asaas
            $asaasId = null;
            $linkPagamento = null;
            $statusCobranca = 'PENDING';
            $linhaDigitavel = null;
            $paymentData = [];
            $dataVencimento = Carbon::now()->addDays(3)->format('Y-m-d');

            try {
                $asaas = new AsaasService();

                // 9.3.1 — Verificar/criar cliente no Asaas
                $customerData = $asaas->createCustomer(
                    $request->nome_igreja,
                    $documento,
                    $request->whatsapp
                );

                // 9.3.2 — Criar cobrança com referência externa
                $descricaoCobranca = "Basiléia - Plano {$request->plano} ({$request->tipo_negociacao})";

                $paymentData = $asaas->createPayment(
                    $customerData['id'],
                    $valorFinal,
                    $dataVencimento,
                    $request->forma_pagamento,
                    $descricaoCobranca,
                    "venda_{$venda->id}" // externalReference
                );

                // 9.3.3 — Salvar dados retornados
                $asaasId        = $paymentData['id'] ?? null;
                $linkPagamento  = $paymentData['invoiceUrl'] ?? ($paymentData['bankSlipUrl'] ?? null);
                $statusCobranca = $paymentData['status'] ?? 'PENDING';

                if (!empty($paymentData['dueDate'])) {
                    $dataVencimento = $paymentData['dueDate'];
                }

                // 9.3.4 — Buscar linha digitável do boleto
                if ($request->forma_pagamento === 'BOLETO' && $asaasId) {
                    $linhaDigitavel = $asaas->getIdentificationField($asaasId);
                }

            } catch (\Exception $e) {
                Log::warning('Asaas integration failed, sale saved locally.', [
                    'venda_id' => $venda->id,
                    'error'    => $e->getMessage()
                ]);
            }

            // Salvar valor_original e valor_final na venda
            $venda->update([
                'valor_original' => $valorBase,
                'valor_final'    => $valorFinal,
            ]);

            // Criar registro da cobrança
            Cobranca::create([
                'venda_id' => $venda->id,
                'asaas_id' => $asaasId,
                'status'   => $statusCobranca,
                'link'     => $linkPagamento,
            ]);

            // Criar registro de pagamento (Etapa 6 + 9.3)
            $formaMap = ['PIX' => 'pix', 'BOLETO' => 'boleto', 'CREDIT_CARD' => 'cartao'];
            Pagamento::create([
                'venda_id'           => $venda->id,
                'cliente_id'         => $cliente->id,
                'vendedor_id'        => $vendedor->id,
                'asaas_payment_id'   => $asaasId,
                'valor'              => $valorFinal,
                'forma_pagamento'    => $formaMap[$request->forma_pagamento] ?? 'pix',
                'status'             => 'pendente',
                'data_vencimento'    => $dataVencimento,
                'link_pagamento'     => $linkPagamento,
                'invoice_url'        => $paymentData['invoiceUrl'] ?? null,
                'bank_slip_url'      => $paymentData['bankSlipUrl'] ?? null,
                'linha_digitavel'    => $linhaDigitavel,
                'nota_fiscal_status' => 'pendente',
            ]);

            DB::commit();

            return redirect()->route('vendedor.vendas')
                ->with('success', 'Venda registrada com sucesso. A cobrança foi gerada e está aguardando pagamento.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar venda', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Falha ao registrar venda: ' . $e->getMessage()])->withInput();
        }
    }

    // ==========================================
    // API: Buscar planos compatíveis por membros
    // ==========================================
    public function buscarPlanos(Request $request)
    {
        $membros = intval($request->query('membros', 0));
        $planosCompativeis = collect(self::$planos)->filter(function ($p) use ($membros) {
            return $membros >= $p['min_membros'] && $membros <= $p['max_membros'];
        })->values();

        return response()->json($planosCompativeis);
    }

    // ==========================================
    // MASTER: Lista global de vendas
    // ==========================================
    public function indexMaster()
    {
        // Auto-expirar vendas com mais de 72h sem pagamento
        self::expirarVendasAntigas();

        $vendas = Venda::whereNotIn('status', ['Expirado'])
            ->with(['cliente', 'vendedor.user', 'cobrancas'])
            ->orderByDesc('created_at')
            ->get();

        $vendasExpiradas = Venda::where('status', 'Expirado')
            ->with(['cliente', 'vendedor.user'])
            ->orderByDesc('created_at')
            ->get();

        return view('master.vendas.index', compact('vendas', 'vendasExpiradas'));
    }

    // ==========================================
    // VENDEDOR: Cancelar (excluir) venda
    // ==========================================
    public function cancelar($id)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        $venda = Venda::where('vendedor_id', $vendedor->id)->findOrFail($id);

        // Só permite cancelar vendas que ainda não foram pagas
        if ($venda->status === 'Pago') {
            return back()->withErrors(['error' => 'Não é possível cancelar uma venda já paga.']);
        }

        $venda->update(['status' => 'Cancelado']);

        // Atualizar pagamento vinculado
        Pagamento::where('venda_id', $venda->id)->update(['status' => 'cancelado']);

        return redirect()->route('vendedor.vendas')
            ->with('success', 'Venda cancelada com sucesso. O registro foi mantido no histórico.');
    }

    // ==========================================
    // MASTER: Cancelar qualquer venda
    // ==========================================
    public function cancelarMaster($id)
    {
        $venda = Venda::findOrFail($id);

        if ($venda->status === 'Pago') {
            return back()->withErrors(['error' => 'Não é possível cancelar uma venda já paga.']);
        }

        $venda->update(['status' => 'Cancelado']);
        Pagamento::where('venda_id', $venda->id)->update(['status' => 'cancelado']);

        return redirect()->route('master.vendas')
            ->with('success', 'Venda cancelada com sucesso.');
    }

    // ==========================================
    // Auto-expirar vendas com mais de 72h
    // ==========================================
    private static function expirarVendasAntigas()
    {
        $limite = Carbon::now()->subHours(72);

        // Buscar vendas "Aguardando pagamento" criadas há mais de 72h
        $vendasExpiradas = Venda::where('status', 'Aguardando pagamento')
            ->where('created_at', '<', $limite)
            ->get();

        foreach ($vendasExpiradas as $venda) {
            $venda->update(['status' => 'Expirado']);

            // Atualizar pagamento vinculado
            Pagamento::where('venda_id', $venda->id)
                ->where('status', 'pendente')
                ->update(['status' => 'vencido']);

            Log::info("Venda #{$venda->id} expirada automaticamente após 72h sem pagamento.");
        }
    }
}
