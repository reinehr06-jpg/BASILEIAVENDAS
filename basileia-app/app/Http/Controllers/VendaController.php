<?php

namespace App\Http\Controllers;

use App\Models\AprovacaoVenda;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Comissao;
use App\Models\LogEvento;
use App\Models\Notificacao;
use App\Models\Pagamento;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\AsaasService;
use App\Services\ChurchProvisioningService;
use App\Services\PagamentoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VendaController extends Controller
{
    // ==========================================
    // Planos Disponíveis (Configuração central)
    // ==========================================
    private static $planos = [
        ['nome' => 'Start',       'min_membros' => 1,    'max_membros' => 100,  'valor_mensal' => 197.00,  'valor_anual' => 1548.00, 'desconto_anual' => 34.5],
        ['nome' => 'Basic',       'min_membros' => 101,  'max_membros' => 300,  'valor_mensal' => 297.00,  'valor_anual' => 2748.00, 'desconto_anual' => 22.9],
        ['nome' => 'Plus',        'min_membros' => 301,  'max_membros' => 500,  'valor_mensal' => 397.00,  'valor_anual' => 3948.00, 'desconto_anual' => 17.1],
        ['nome' => 'Performance', 'min_membros' => 501,  'max_membros' => 99999, 'valor_mensal' => 0,        'valor_anual' => 0,       'desconto_anual' => 0,    'consulte' => true],
    ];

    private const MAX_DESCONTO = 100.00; // Desconto máximo permitido em % (até 100%, acima de 5% vai para aprovação)

    private const LIMITE_DESCONTO_SEM_APROVACAO = 5.00; // Limite de desconto sem necessidade de aprovação

    // ==========================================
    // VENDEDOR: Lista de vendas
    // ==========================================
    public function index()
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (! $vendedor) {
            return redirect()->route('vendedor.dashboard')
                ->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        // Auto-expirar vendas com mais de 72h sem pagamento
        self::expirarVendasAntigas();

        // Sincronizar proativamente vendas pendentes com Asaas
        self::syncPendentes($vendedor->id);

        // Vendas ativas (não canceladas, não expiradas)
        $vendas = Venda::where('vendedor_id', $vendedor->id)
            ->whereNotIn('status', ['Expirado', 'Cancelado'])
            ->with(['cliente', 'cobrancas', 'pagamentos'])
            ->orderByDesc('created_at')
            ->get();

        // Vendas canceladas (aba separada)
        $vendasCanceladas = Venda::where('vendedor_id', $vendedor->id)
            ->where('status', 'Cancelado')
            ->with(['cliente'])
            ->orderByDesc('created_at')
            ->get();

        $vendasExpiradas = Venda::where('vendedor_id', $vendedor->id)
            ->where('status', 'Expirado')
            ->with(['cliente'])
            ->orderByDesc('created_at')
            ->get();

        return view('vendedor.vendas.index', compact('vendas', 'vendasCanceladas', 'vendasExpiradas'));
    }

    // ==========================================
    // VENDEDOR: Histórico de vendas canceladas/expiradas
    // ==========================================
    public function canceladas()
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (! $vendedor) {
            return redirect()->route('vendedor.dashboard')
                ->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        $vendasCanceladas = Venda::where('vendedor_id', $vendedor->id)
            ->where('status', 'Cancelado')
            ->with(['cliente'])
            ->orderByDesc('created_at')
            ->get();

        $vendasExpiradas = Venda::where('vendedor_id', $vendedor->id)
            ->where('status', 'Expirado')
            ->with(['cliente'])
            ->orderByDesc('created_at')
            ->get();

        return view('vendedor.vendas.canceladas', compact('vendasCanceladas', 'vendasExpiradas'));
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
            $pagamentoService = new PagamentoService;
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
            'nome_igreja' => 'required|string|max:255',
            'nome_pastor' => 'required|string|max:255',
            'localidade' => 'required|string|max:255',
            'moeda' => 'required|string|max:10',
            'quantidade_membros' => 'required|integer|min:1',
            'documento' => 'required|string|max:18',
            'whatsapp' => 'required|string|max:20',
            'email_cliente' => 'required|email|max:255',
            'plano' => 'required|string',
            'forma_pagamento' => 'required|in:PIX,BOLETO,CREDIT_CARD',
            'tipo_negociacao' => 'required|in:mensal,anual',
            'desconto' => 'nullable|numeric|min:0|max:'.self::MAX_DESCONTO,
            'valor_performance' => 'nullable|numeric|min:0.01',
            'observacao' => 'nullable|string|max:1000',
            'parcelas' => 'nullable|integer|min:1|max:12',
        ], [
            'documento.required' => 'Informe um CPF ou CNPJ válido.',
            'desconto.max' => 'O desconto informado ultrapassa o limite permitido ('.self::MAX_DESCONTO.'%).',
            'quantidade_membros.min' => 'Digite a quantidade de membros para sugerir os planos disponíveis.',
            'parcelas.max' => 'O número máximo de parcelas é 12.',
            'valor_performance.required_if' => 'O valor combinado é obrigatório para o plano Performance.',
            'valor_performance.min' => 'O valor combinado deve ser maior que zero.',
        ]);

        // Validação de CPF/CNPJ básica
        $documento = preg_replace('/[^0-9]/', '', $request->documento);
        if (strlen($documento) !== 11 && strlen($documento) !== 14) {
            return back()->withErrors(['documento' => 'Informe um CPF ou CNPJ válido.'])->withInput();
        }

        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (! $vendedor) {
            return back()->withErrors(['error' => 'Perfil de vendedor não configurado.'])->withInput();
        }

        // Verificar se já existe cliente com mesmo documento (CPF/CNPJ) e venda ativa
        $clienteExistente = Cliente::where('documento', $documento)->first();
        if ($clienteExistente) {
            $vendaAtiva = Venda::where('cliente_id', $clienteExistente->id)
                ->whereNotIn('status', ['Cancelado', 'Expirado'])
                ->exists();

            if ($vendaAtiva) {
                return back()->withErrors([
                    'documento' => 'Este cliente já possui uma venda ativa no sistema. Não é possível criar outra venda para o mesmo CNPJ/CPF.',
                ])->withInput();
            }
        }

        // Calcular valor do plano
        $planoSelecionado = collect(self::$planos)->firstWhere('nome', $request->plano);
        if (! $planoSelecionado) {
            return back()->withErrors(['plano' => 'Plano selecionado inválido.'])->withInput();
        }

        $isPerformance = ! empty($planoSelecionado['consulte']);

        // Plano Performance: usar valor combinado
        if ($isPerformance) {
            $valorFinal = floatval($request->valor_performance);
            $valorBase = $valorFinal;
            $desconto = 0;

            if ($valorFinal <= 0) {
                return back()->withErrors(['valor_performance' => 'O valor combinado é obrigatório para o plano Performance.'])->withInput();
            }
        } else {
            $valorBase = $request->tipo_negociacao === 'anual'
                ? $planoSelecionado['valor_anual']
                : $planoSelecionado['valor_mensal'];

            $desconto = floatval($request->desconto ?? 0);
            $valorFinal = $valorBase - ($valorBase * ($desconto / 100));

            // Validar que o valor final é maior que zero
            if ($valorFinal <= 0) {
                return back()->withErrors(['desconto' => 'O desconto não pode ser 100%. O valor final deve ser maior que zero.'])->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Criar ou atualizar cliente
            $cliente = Cliente::updateOrCreate(
                ['documento' => $documento],
                [
                    'nome' => $request->nome_igreja,
                    'nome_igreja' => $request->nome_igreja,
                    'nome_pastor' => $request->nome_pastor,
                    'localidade' => $request->localidade,
                    'moeda' => $request->moeda,
                    'quantidade_membros' => $request->quantidade_membros,
                    'whatsapp' => $request->whatsapp,
                    'contato' => $request->whatsapp,
                    'email' => $request->email_cliente,
                ]
            );

            // Criar venda
            $venda = Venda::create([
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'valor' => $valorFinal,
                'valor_original' => $valorBase,
                'valor_final' => $valorFinal,
                'percentual_desconto' => $desconto,
                'comissao_gerada' => 0, // Comissão só é gerada após pagamento confirmado
                'status' => 'Aguardando pagamento',
                'plano' => $request->plano,
                'forma_pagamento' => $request->forma_pagamento,
                'tipo_negociacao' => $request->tipo_negociacao,
                'parcelas' => $request->parcelas ?? 1,
                'desconto' => $desconto,
                'observacao' => $request->observacao,
                'origem' => 'manual',
                'data_venda' => Carbon::now(),
                'checkout_hash' => Str::random(32),
                'checkout_status' => 'PENDENTE',
            ]);

            // Verificar se requer aprovação
            // Performance: sempre requer aprovação
            // Outros planos: requer aprovação se desconto > 5%
            $requerAprovacao = $isPerformance || $desconto > self::LIMITE_DESCONTO_SEM_APROVACAO;

            if ($requerAprovacao) {
                // Criar registro de aprovação
                AprovacaoVenda::create([
                    'venda_id' => $venda->id,
                    'tipo_aprovacao' => $isPerformance ? 'VALOR_PERFORMANCE' : 'DESCONTO',
                    'percentual_solicitado' => $isPerformance ? 0 : $desconto,
                    'valor_solicitado' => $isPerformance ? $valorFinal : null,
                    'limite_regra' => self::LIMITE_DESCONTO_SEM_APROVACAO,
                    'status' => 'PENDENTE',
                    'solicitado_por' => Auth::id(),
                ]);

                // Atualizar status da venda
                $venda->update([
                    'requer_aprovacao' => true,
                    'status_aprovacao' => 'pendente',
                    'status' => 'Aguardando aprovação',
                ]);

                // Notificar masters
                $mensagemNotificacao = $isPerformance
                    ? "A venda #{$venda->id} de {$cliente->nome_igreja} precisa de aprovação (plano Performance - valor R$ ".number_format($valorFinal, 2, ',', '.').')'
                    : "A venda #{$venda->id} de {$cliente->nome_igreja} precisa de aprovação (desconto de {$desconto}%)";

                Notificacao::notificarMasters(
                    'venda_aprovacao',
                    'Venda pendente de aprovação',
                    $mensagemNotificacao,
                    ['venda_id' => $venda->id, 'desconto' => $desconto, 'is_performance' => $isPerformance]
                );

                DB::commit();

                $mensagemSucesso = $isPerformance
                    ? 'Venda criada com sucesso! Como o plano Performance requer negociação, a venda precisa de aprovação do administrador antes de gerar a cobrança.'
                    : 'Venda criada com sucesso! Como o desconto é superior a 5%, a venda precisa de aprovação do administrador antes de gerar a cobrança.';

                return redirect()->route('vendedor.vendas')
                    ->with('success', $mensagemSucesso);
            }

            // 9.3 — Integrar com Asaas (apenas se não requer aprovação)
            $asaasId = null;
            $linkPagamento = null;
            $statusCobranca = 'PENDING';
            $linhaDigitavel = null;
            $paymentData = [];
            $boletoUrlFromSub = null;
            $dataVencimento = Carbon::now()->addDays(3)->format('Y-m-d');

            try {
                $asaas = new AsaasService;

                // 9.3.1 — Verificar/criar cliente no Asaas
                $customerData = $asaas->createCustomer(
                    $request->nome_igreja,
                    $documento,
                    $request->whatsapp,
                    $request->email_cliente
                );

                // 9.3.2 — Criar cobrança ou assinatura com referência externa
                $descricaoCobranca = "Basiléia - Plano {$request->plano} ({$request->tipo_negociacao})";

                // Determinar split se aplicável
                $split = [];
                if ($vendedor->isAptoSplit()) {
                    $split = $asaas->buildSplitArray($vendedor, $valorFinal, 'inicial');
                }

                // Determinar modo de cobrança baseado na combinação plano + forma de pagamento
                // Monthly + Boleto → SUBSCRIPTION (recorrente mensal)
                // Monthly + Credit Card → SUBSCRIPTION (recorrente mensal, à vista por mês)
                // Monthly + PIX → PAYMENT (avulsa)
                // Annual + Credit Card → INSTALLMENT (até 12x)
                // Annual + Boleto/PIX → PAYMENT (avulsa)

                $isBoleto = $request->forma_pagamento === 'BOLETO';
                $isCartao = $request->forma_pagamento === 'CREDIT_CARD';
                $isMensal = $request->tipo_negociacao === 'mensal';
                $isAnual = $request->tipo_negociacao === 'anual';

                if ($isMensal && ($isBoleto || $isCartao)) {
                    // Recorrência: Boleto ou Cartão Mensal cria assinatura
                    $subscriptionPayload = [
                        'customer' => $customerData['id'],
                        'billingType' => $request->forma_pagamento,
                        'value' => $valorFinal,
                        'nextDueDate' => $dataVencimento,
                        'cycle' => 'MONTHLY',
                        'description' => $descricaoCobranca,
                        'externalReference' => "venda_{$venda->id}",
                    ];

                    if (! empty($split)) {
                        $subscriptionPayload['split'] = $split;
                    }

                    $paymentData = $asaas->requestAsaas('POST', '/subscriptions', $subscriptionPayload);
                    $asaasSubscriptionId = $paymentData['id'] ?? null;

                    // Salvar ID da assinatura
                    $venda->update([
                        'modo_cobranca_asaas' => 'SUBSCRIPTION',
                        'asaas_subscription_id' => $asaasSubscriptionId,
                    ]);

                    // Buscar o primeiro pagamento da assinatura para pegar o link do boleto
                    if ($asaasSubscriptionId) {
                        try {
                            sleep(2); // Aguarda o Asaas gerar o pagamento
                            $paymentsResponse = $asaas->requestAsaas('GET', "/subscriptions/{$asaasSubscriptionId}/payments");
                            if (! empty($paymentsResponse['data']) && count($paymentsResponse['data']) > 0) {
                                $firstPayment = $paymentsResponse['data'][0];
                                $asaasId = $firstPayment['id'] ?? $asaasSubscriptionId;
                                $linkPagamento = $firstPayment['invoiceUrl'] ?? ($firstPayment['bankSlipUrl'] ?? null);
                                $boletoUrlFromSub = $firstPayment['bankSlipUrl'] ?? null;
                                $statusCobranca = $firstPayment['status'] ?? 'PENDING';
                                $linhaDigitavel = $firstPayment['identificationField'] ?? null;

                                // Atualiza o pagamento com os dados do primeiro pagamento da assinatura
                                Log::info('Asaas: primeiro pagamento da assinatura encontrado', [
                                    'subscription_id' => $asaasSubscriptionId,
                                    'payment_id' => $asaasId,
                                    'bankSlipUrl' => $boletoUrlFromSub,
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::warning('Asaas: falha ao buscar pagamentos da assinatura', ['error' => $e->getMessage()]);
                        }
                    }
                } else {
                    // Criar cobrança avulsa ou parcelada
                    $paymentPayload = [
                        'customer' => $customerData['id'],
                        'billingType' => $request->forma_pagamento,
                        'value' => $valorFinal,
                        'dueDate' => $dataVencimento,
                        'description' => $descricaoCobranca,
                        'externalReference' => "venda_{$venda->id}",
                    ];

                    // Se for parcelado (Cartão de Crédito)
                    if ($request->forma_pagamento === 'CREDIT_CARD' && $request->parcelas > 1) {
                        $paymentPayload['totalValue'] = $valorFinal;
                        $paymentPayload['installmentCount'] = $request->parcelas;
                        unset($paymentPayload['value']);
                        $venda->update(['modo_cobranca_asaas' => 'INSTALLMENT']);
                    } else {
                        $venda->update(['modo_cobranca_asaas' => 'PAYMENT']);
                    }

                    if (! empty($split)) {
                        $paymentPayload['split'] = $split;
                    }

                    $paymentData = $asaas->requestAsaas('POST', '/payments', $paymentPayload);

                    // Se for parcelado, salvar o installment ID para cancelar tudo depois
                    if ($request->forma_pagamento === 'CREDIT_CARD' && $request->parcelas > 1 && ! empty($paymentData['installment'])) {
                        $venda->update(['asaas_installment_id' => $paymentData['installment']]);
                        Log::info('Asaas: installment ID salvo', ['installment_id' => $paymentData['installment']]);
                    }
                }

                // 9.3.3 — Salvar dados retornados
                $asaasId = $paymentData['id'] ?? null;
                $linkPagamento = $paymentData['invoiceUrl'] ?? ($paymentData['bankSlipUrl'] ?? null);
                $statusCobranca = $paymentData['status'] ?? 'PENDING';

                // Se for subscription, usar os dados do primeiro pagamento (já setados acima)
                if (! empty($boletoUrlFromSub)) {
                    $linkPagamento = $linkPagamento ?? $boletoUrlFromSub;
                }

                if (! empty($paymentData['dueDate'])) {
                    $dataVencimento = $paymentData['dueDate'];
                }

                // 9.3.4 — Buscar linha digitável do boleto
                if ($request->forma_pagamento === 'BOLETO' && $asaasId && ! $linhaDigitavel) {
                    $linhaDigitavel = $asaas->getIdentificationField($asaasId);
                }

            } catch (\Exception $e) {
                Log::warning('Asaas integration failed, sale saved locally.', [
                    'venda_id' => $venda->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Salvar valor_original e valor_final na venda
            $venda->update([
                'valor_original' => $valorBase,
                'valor_final' => $valorFinal,
            ]);

            // Criar registro da cobrança
            Cobranca::create([
                'venda_id' => $venda->id,
                'asaas_id' => $asaasId,
                'status' => $statusCobranca,
                'link' => $linkPagamento,
            ]);

            // Criar registro de pagamento (Etapa 6 + 9.3)
            $formaMap = ['PIX' => 'pix', 'BOLETO' => 'boleto', 'CREDIT_CARD' => 'cartao'];

            // Para subscription, o asaId é o ID do primeiro pagamento da assinatura
            $paymentIdSalvar = $asaasId;
            $invoiceUrlSalvar = $paymentData['invoiceUrl'] ?? null;
            $bankSlipSalvar = $paymentData['bankSlipUrl'] ?? null;

            // Se for subscription, usar dados do primeiro pagamento
            if (! empty($boletoUrlFromSub)) {
                $bankSlipSalvar = $bankSlipSalvar ?? $boletoUrlFromSub;
            }
            if (! empty($linkPagamento)) {
                $invoiceUrlSalvar = $invoiceUrlSalvar ?? $linkPagamento;
            }

            Pagamento::create([
                'venda_id' => $venda->id,
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'asaas_payment_id' => $paymentIdSalvar,
                'valor' => $valorFinal,
                'forma_pagamento' => $formaMap[$request->forma_pagamento] ?? 'pix',
                'status' => 'pendente',
                'data_vencimento' => $dataVencimento,
                'link_pagamento' => $linkPagamento,
                'invoice_url' => $invoiceUrlSalvar,
                'bank_slip_url' => $bankSlipSalvar,
                'linha_digitavel' => $linhaDigitavel,
                'nota_fiscal_status' => 'pendente',
            ]);

            DB::commit();

            $paymentMethod = $formaMap[$request->forma_pagamento] ?? 'pix';
            return redirect()->route('checkout.show', ['venda_hash' => $venda->checkout_hash, 'method' => $paymentMethod])
                ->with('success', 'Venda registrada com sucesso! Você foi redirecionado para o checkout para baixar o boleto ou realizar o pagamento.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar venda', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->withErrors(['error' => 'Falha ao registrar venda: '.$e->getMessage()])->withInput();
        }
    }

    // ==========================================
    // API: Buscar planos compatíveis por membros
    // ==========================================
    public function buscarPlanos(Request $request)
    {
        $membros = intval($request->query('membros', 0));

        // Encontra o plano ideal (onde membros se encaixa)
        $planoIdeal = null;
        foreach (self::$planos as $p) {
            if ($membros >= $p['min_membros'] && $membros <= $p['max_membros']) {
                $planoIdeal = $p;
                break;
            }
        }

        // Retorna o plano ideal + todos os planos mais premium (acima dele)
        $planosCompativeis = collect(self::$planos)->filter(function ($p) use ($planoIdeal) {
            if (! $planoIdeal) {
                return true;
            }

            return $p['min_membros'] >= $planoIdeal['min_membros'];
        })->values();

        return response()->json($planosCompativeis);
    }

    // ==========================================
    // API: Verificar se documento (CPF/CNPJ) já possui venda ativa
    // ==========================================
    public function verificarDocumento(Request $request)
    {
        $documento = preg_replace('/\D/', '', $request->input('documento', ''));

        if (strlen($documento) < 11) {
            return response()->json(['exists' => false]);
        }

        // Buscar cliente pelo documento
        $cliente = Cliente::where('documento', $documento)->first();

        if (! $cliente) {
            return response()->json(['exists' => false]);
        }

        // Verificar se tem venda ativa (não cancelada, não expirada)
        $vendaAtiva = Venda::where('cliente_id', $cliente->id)
            ->whereNotIn('status', ['Cancelado', 'Expirado'])
            ->orderByDesc('created_at')
            ->first();

        if (! $vendaAtiva) {
            return response()->json([
                'exists' => true,
                'has_active_sale' => false,
                'cliente' => [
                    'nome_igreja' => $cliente->nome_igreja,
                    'nome_pastor' => $cliente->nome_pastor,
                    'email' => $cliente->email,
                ],
            ]);
        }

        $statusLabel = match (strtoupper($vendaAtiva->status)) {
            'PAGO' => 'Pago',
            'AGUARDANDO PAGAMENTO', 'AGUARDANDO APROVAÇÃO' => 'Aguardando pagamento',
            'VENCIDO' => 'Vencido',
            default => $vendaAtiva->status,
        };

        return response()->json([
            'exists' => true,
            'has_active_sale' => true,
            'cliente' => [
                'nome_igreja' => $cliente->nome_igreja,
                'nome_pastor' => $cliente->nome_pastor,
                'email' => $cliente->email,
            ],
            'venda' => [
                'id' => $vendaAtiva->id,
                'plano' => $vendaAtiva->plano,
                'status' => $statusLabel,
                'valor' => number_format($vendaAtiva->valor, 2, ',', '.'),
                'data' => $vendaAtiva->created_at->format('d/m/Y'),
            ],
        ]);
    }

    // ==========================================
    // MASTER: Lista global de vendas
    // ==========================================
    public function indexMaster()
    {
        // Auto-expirar vendas com mais de 72h sem pagamento
        self::expirarVendasAntigas();

        // Sincronizar proativamente todas as vendas pendentes com Asaas
        self::syncPendentes();

        // Vendas ativas (não canceladas, não expiradas)
        $vendas = Venda::whereNotIn('status', ['Expirado', 'Cancelado'])
            ->with(['cliente', 'vendedor.user', 'cobrancas', 'pagamentos'])
            ->orderByDesc('created_at')
            ->get();

        // Vendas canceladas (aba separada)
        $vendasCanceladas = Venda::where('status', 'Cancelado')
            ->with(['cliente', 'vendedor.user'])
            ->orderByDesc('created_at')
            ->get();

        $vendasExpiradas = Venda::where('status', 'Expirado')
            ->with(['cliente', 'vendedor.user'])
            ->orderByDesc('created_at')
            ->get();

        return view('master.vendas.index', compact('vendas', 'vendasCanceladas', 'vendasExpiradas'));
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

        // Cancelar no Asaas apenas se a venda foi aprovada (tem cobrança gerada)
        if ($venda->status !== 'Aguardando aprovação') {
            $this->cancelarNoAsaas($venda);
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

        // Cancelar no Asaas apenas se a venda foi aprovada (tem cobrança gerada)
        if ($venda->status !== 'Aguardando aprovação') {
            $this->cancelarNoAsaas($venda);
        }

        $venda->update(['status' => 'Cancelado']);
        Pagamento::where('venda_id', $venda->id)->update(['status' => 'cancelado']);

        return redirect()->route('master.vendas')
            ->with('success', 'Venda cancelada com sucesso.');
    }

    // ==========================================
    // MASTER: Estornar/Cancelar venda paga
    // ==========================================
    public function estornarMaster(Request $request, $id)
    {
        if (Auth::user()->perfil !== 'master') {
            return back()->withErrors(['error' => 'Apenas o administrador pode estornar vendas.']);
        }

        $venda = Venda::with(['pagamentos', 'cliente'])->findOrFail($id);

        if (! in_array(strtoupper($venda->status), ['PAGO', 'PAGO_ASAAS', 'AGUARDANDO PAGAMENTO', 'PENDING'])) {
            return back()->withErrors(['error' => 'Apenas vendas pagas ou pendentes podem ser estornadas. Status atual: '.$venda->status]);
        }

        $motivo = $request->input('motivo', 'Estorno solicitado pelo administrador');
        $asaas = new AsaasService;
        $acaoRealizada = [];
        $parcelasCanceladas = 0;
        $parcelasPagas = 0;

        try {
            $modoCobranca = $venda->modo_cobranca_asaas;

            // ══════════════════════════════════════════════════════════════
            // DESCOBERTA DE IDS (Caso estejam faltando no banco local)
            // ══════════════════════════════════════════════════════════════
            $installmentId = $venda->asaas_installment_id;
            $subscriptionId = $venda->asaas_subscription_id;

            // Se for parcelado mas não temos o ID do grupo, tentamos descobrir via primeiro pagamento
            if ($modoCobranca === 'INSTALLMENT' && ! $installmentId) {
                $pag = $venda->pagamentos->whereNotNull('asaas_payment_id')->first();
                if ($pag) {
                    try {
                        $info = $asaas->getPayment($pag->asaas_payment_id);
                        $installmentId = $info['installment'] ?? null;
                        if ($installmentId) {
                            $venda->update(['asaas_installment_id' => $installmentId]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Discovery: Falha ao buscar installment ID', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Se for assinatura mas não temos o ID, tentamos descobrir
            if ($modoCobranca === 'SUBSCRIPTION' && ! $subscriptionId) {
                $pag = $venda->pagamentos->whereNotNull('asaas_payment_id')->first();
                if ($pag) {
                    try {
                        $info = $asaas->getPayment($pag->asaas_payment_id);
                        $subscriptionId = $info['subscription'] ?? null;
                        if ($subscriptionId) {
                            $venda->update(['asaas_subscription_id' => $subscriptionId]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Discovery: Falha ao buscar subscription ID', ['error' => $e->getMessage()]);
                    }
                }
            }

            // ══════════════════════════════════════════════════════════════
            // EXECUÇÃO NO ASAAS (Modo Estrito: Se falhar aqui, não atualiza o DB local)
            // ══════════════════════════════════════════════════════════════

            // CASO 1: INSTALLMENT
            if ($modoCobranca === 'INSTALLMENT' && $installmentId) {
                try {
                    $asaas->requestAsaas('DELETE', "/installments/{$installmentId}");
                    $acaoRealizada[] = 'Parcelas futuras/pendentes canceladas no Asaas';
                } catch (\Exception $e) {
                    // Se o erro for que já está cancelado, ignoramos e seguimos
                    if (! str_contains($e->getMessage(), 'already') && ! str_contains($e->getMessage(), 'not found')) {
                        throw new \Exception('Falha ao cancelar parcelamento no Asaas: '.$e->getMessage());
                    }
                }

                foreach ($venda->pagamentos as $pag) {
                    if (! in_array(strtoupper($pag->status), ['RECEIVED', 'CONFIRMED', 'PAGO', 'PAGO_ASAAS'])) {
                        $pag->update(['status' => 'CANCELED']);
                        $parcelasCanceladas++;
                    } else {
                        $parcelasPagas++;
                    }
                }
            }
            // CASO 2: SUBSCRIPTION
            elseif ($modoCobranca === 'SUBSCRIPTION' && $subscriptionId) {
                try {
                    $asaas->requestAsaas('DELETE', "/subscriptions/{$subscriptionId}");
                    $acaoRealizada[] = 'Assinatura cancelada no Asaas';
                } catch (\Exception $e) {
                    if (! str_contains($e->getMessage(), 'already') && ! str_contains($e->getMessage(), 'not found')) {
                        throw new \Exception('Falha ao cancelar assinatura no Asaas: '.$e->getMessage());
                    }
                }

                $venda->pagamentos()
                    ->whereNotIn('status', ['RECEIVED', 'CONFIRMED', 'PAGO', 'PAGO_ASAAS'])
                    ->update(['status' => 'CANCELED']);
            }
            // CASO 3: PAGAMENTO ÚNICO OU FALLBACK
            else {
                foreach ($venda->pagamentos as $pagamento) {
                    if (! $pagamento->asaas_payment_id) {
                        continue;
                    }

                    $isPaid = in_array(strtoupper($pagamento->status), ['RECEIVED', 'CONFIRMED', 'PAGO', 'PAGO_ASAAS']);

                    if ($isPaid) {
                        try {
                            $asaas->refundPayment($pagamento->asaas_payment_id);
                            $pagamento->update(['status' => 'REFUNDED']);
                            $acaoRealizada[] = "Pagamento #{$pagamento->id} estornado integralmente";
                        } catch (\Exception $e) {
                            // Se for boleto, o Asaas pode não permitir refund via API
                            $forma = strtolower($pagamento->forma_pagamento ?? '');
                            if ($forma === 'boleto' || str_contains($e->getMessage(), 'manual')) {
                                $acaoRealizada[] = 'Boleto pago requer estorno manual no painel do Asaas';
                            } else {
                                throw new \Exception("Falha ao estornar pagamento #{$pagamento->id}: ".$e->getMessage());
                            }
                        }
                    } else {
                        try {
                            $asaas->requestAsaas('POST', "/payments/{$pagamento->asaas_payment_id}/cancel");
                            $pagamento->update(['status' => 'CANCELED']);
                            $acaoRealizada[] = 'Pagamento pendente cancelado no Asaas';
                        } catch (\Exception $e) {
                            if (! str_contains($e->getMessage(), 'already')) {
                                Log::warning('Falha ao cancelar pagamento individual', ['id' => $pagamento->asaas_payment_id, 'err' => $e->getMessage()]);
                            }
                        }
                    }
                }
            }

            // ══════════════════════════════════════════════════════════════
            // ATUALIZAÇÃO LOCAL (Só ocorre se não houve exceção acima)
            // ══════════════════════════════════════════════════════════════

            $venda->update(['status' => 'Estornado']);
            $venda->cobrancas()->update(['status' => 'CANCELADO']);

            Comissao::where('venda_id', $venda->id)
                ->whereIn('status', ['pendente', 'confirmada'])
                ->update(['status' => 'estornada']);

            $acaoRealizada[] = 'Comissões revertidas localmente';

            // Suspender no Church
            if ($venda->cliente && $venda->cliente->church_user_id) {
                try {
                    $church = new ChurchProvisioningService;
                    $church->suspenderConta($venda->cliente);
                    $acaoRealizada[] = 'Conta suspensa no Church';
                } catch (\Exception $e) {
                    Log::warning('Church: Falha ao suspender', ['error' => $e->getMessage()]);
                }
            }

            LogEvento::create([
                'usuario_id' => Auth::id(),
                'entidade' => 'Venda',
                'entidade_id' => $venda->id,
                'acao' => 'Estorno_Asaas_Sincronizado',
                'descricao' => "Motivo: {$motivo}. Ações: ".implode('; ', $acaoRealizada),
            ]);

            return redirect()->route('master.vendas')
                ->with('success', 'Estorno processado com sucesso: '.implode(' | ', $acaoRealizada));

        } catch (\Exception $e) {
            Log::error('[Estorno_Sincronizado] Falha crítica', ['venda_id' => $id, 'error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'O Asaas recusou a operação ou houve um erro técnico: '.$e->getMessage()]);
        }
    }

    /**
     * Cancelar cobrança/assinatura/parcelamento no Asaas
     * Tenta cancelar mas não falha se o Asaas retornar erro
     */
    private function cancelarNoAsaas(Venda $venda): void
    {
        try {
            $asaas = new AsaasService;

            // 1. Se for parcelado (installment), cancelar TODAS as parcelas de uma vez
            $installmentId = $venda->asaas_installment_id;

            if (! $installmentId && $venda->modo_cobranca_asaas === 'INSTALLMENT') {
                $primeiroPagamento = $venda->pagamentos->first();
                if ($primeiroPagamento && $primeiroPagamento->asaas_payment_id) {
                    try {
                        $paymentInfo = $asaas->getPayment($primeiroPagamento->asaas_payment_id);
                        if (! empty($paymentInfo['installment'])) {
                            $installmentId = $paymentInfo['installment'];
                            $venda->update(['asaas_installment_id' => $installmentId]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Falha ao buscar installment ID no Asaas', ['error' => $e->getMessage()]);
                    }
                }
            }

            if ($installmentId) {
                try {
                    $asaas->requestAsaas('DELETE', "/installments/{$installmentId}");
                    Log::info('Parcelas canceladas no Asaas', ['installment_id' => $installmentId]);
                } catch (\Exception $e) {
                    Log::warning('Falha ao cancelar installment no Asaas', ['error' => $e->getMessage()]);
                }
            }

            // 2. Se for assinatura (SUBSCRIPTION)
            if ($venda->asaas_subscription_id) {
                try {
                    $asaas->requestAsaas('DELETE', "/subscriptions/{$venda->asaas_subscription_id}");
                } catch (\Exception $e) {
                    Log::warning('Falha ao cancelar assinatura no Asaas', ['error' => $e->getMessage()]);
                }
            }

            // 3. Cancelar cobranças individuais (fallback)
            if (! $installmentId && ! $venda->asaas_subscription_id) {
                foreach ($venda->pagamentos as $pagamento) {
                    if ($pagamento->asaas_payment_id && ! in_array(strtoupper($pagamento->status), ['RECEIVED', 'CONFIRMED'])) {
                        try {
                            $asaas->requestAsaas('POST', "/payments/{$pagamento->asaas_payment_id}/cancel");
                        } catch (\Exception $e) {
                            Log::warning('Falha ao cancelar pagamento avulso no Asaas', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Erro no método cancelarNoAsaas', ['venda_id' => $venda->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Sincronizar pagamento manualmente com Asaas
     */
    public function syncPagamento($id)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (! $vendedor) {
            return back()->withErrors(['error' => 'Perfil de vendedor não encontrado.']);
        }

        $venda = Venda::where('vendedor_id', $vendedor->id)
            ->with(['pagamentos'])
            ->findOrFail($id);

        $pagamento = $venda->pagamentos->first();

        if (! $pagamento) {
            return back()->withErrors(['error' => 'Nenhum pagamento encontrado para esta venda.']);
        }

        $pagamentoService = new PagamentoService;
        $foiPago = $pagamentoService->sync($pagamento);

        if ($foiPago) {
            return back()->with('success', 'Pagamento confirmado! O status foi atualizado.');
        }

        // Verifica se o status mudou
        $venda->refresh();
        if ($venda->status === 'Pago') {
            return back()->with('success', 'Pagamento já estava confirmado.');
        }

        return back()->with('success', 'Sincronização concluída. Status atual: '.$venda->status);
    }

    // ==========================================
    // Sincronizar proativamente vendas pendentes com Asaas
    // ==========================================
    private static function syncPendentes(?int $vendedorId = null): void
    {
        try {
            $statusesPendentes = ['Aguardando pagamento', 'PENDING', 'Vencido', 'OVERDUE', 'AGUARDANDO_PAGAMENTO'];

            $query = Venda::whereIn('status', $statusesPendentes)
                ->where('created_at', '>', now()->subDays(7))
                ->whereHas('pagamentos', function ($q) {
                    $q->whereNotNull('asaas_payment_id');
                })
                ->with(['pagamentos', 'cobrancas'])
                ->take(10);

            if ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            }

            $vendasPendentes = $query->get();

            if ($vendasPendentes->isEmpty()) {
                return;
            }

            $pagamentoService = new PagamentoService;

            foreach ($vendasPendentes as $venda) {
                $pagamento = $venda->pagamentos->first();
                if ($pagamento && $pagamento->asaas_payment_id) {
                    $pagamentoService->sync($pagamento);
                }
            }
        } catch (\Exception $e) {
            Log::warning('VendaController: Erro ao sincronizar pendentes', [
                'error' => $e->getMessage(),
            ]);
        }
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

    // ==========================================
    // Checkout - Gerar Link de Pagamento
    // ==========================================
    public function gerarLinkCheckout(Venda $venda)
    {
        // Verificar se a venda pertence ao vendedor atual ou se é master
        $user = Auth::user();
        if ($user->perfil !== 'master' && $venda->vendedor_id !== $user->vendedor->id) {
            return response()->json(['error' => 'Você não tem permissão para gerar link desta venda.'], 403);
        }

        // Se já tem hash, retorna o existente
        if ($venda->checkout_hash) {
            $url = url('/checkout/'.$venda->checkout_hash);

            return response()->json([
                'success' => true,
                'url' => $url,
                'hash' => $venda->checkout_hash,
                'message' => 'Link de pagamento copiado!',
            ]);
        }

        // Gerar novo hash
        $hash = Str::random(32);
        $venda->update([
            'checkout_hash' => $hash,
            'checkout_status' => 'PENDENTE',
        ]);

        $url = url('/checkout/'.$hash);

        return response()->json([
            'success' => true,
            'url' => $url,
            'hash' => $hash,
            'message' => 'Link de pagamento gerado!',
        ]);
    }
}
