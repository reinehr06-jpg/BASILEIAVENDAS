<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\Assinatura;
use App\Models\IntegracaoAsaasLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class CriarCobrancaService
{
    protected AsaasService $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    public function execute(array $payload): array
    {
        try {
            DB::beginTransaction();

            $clientePayload = $payload['cliente'];
            $vendaPayload = $payload['venda'];
            $cobrancaPayload = $payload['cobranca'];
            
            // 1. Localizar ou criar cliente local
            $cliente = $this->findOrCreateClienteLocal($clientePayload);

            // 2. Localizar ou criar cliente no Asaas
            if (!$cliente->asaas_customer_id) {
                // Remove non-numeric from cpf_cnpj and telefone
                $doc = preg_replace('/\D/', '', $clientePayload['cpf_cnpj']);
                $tel = preg_replace('/\D/', '', $clientePayload['telefone']);
                
                $customerData = $this->asaasService->createCustomer(
                    $clientePayload['nome_igreja'],
                    $doc,
                    $tel,
                    $clientePayload['email'] ?? null
                );
                
                $cliente->asaas_customer_id = $customerData['id'];
                $cliente->save();
            }

            // 3. Criar venda local
            $venda = $this->createVendaLocal($vendaPayload, $cliente->id, $cobrancaPayload['modo_cobranca']);

            // 4. Decidir entre cobrança avulsa, parcelada ou assinatura
            $modo = $cobrancaPayload['modo_cobranca'];
            $asaasResponse = null;
            
            if ($modo === 'AVULSA') {
                $asaasResponse = $this->createAvulsa($cliente, $venda, $cobrancaPayload);
            } elseif ($modo === 'PARCELADA') {
                $asaasResponse = $this->createParcelada($cliente, $venda, $cobrancaPayload);
            } elseif ($modo === 'ASSINATURA') {
                $asaasResponse = $this->createSubscription($cliente, $venda, $cobrancaPayload);
            } else {
                throw new Exception("Modo de cobrança não suportado: {$modo}");
            }

            // 5. Salvar IDs externos e atualizar status
            $this->persistirRetornoAsaas($venda, $cliente, $asaasResponse, $modo, $cobrancaPayload);

            // 6. Registrar log
            $this->logIntegracao('venda', $venda->id, "CREATE_{$modo}", $payload, $asaasResponse, 200, 'SUCCESS');

            DB::commit();

            return $this->buildSuccessResponse($venda, $cliente, $asaasResponse, $modo);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao CriarCobrancaService', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return [
                'success' => false,
                'message' => 'Não foi possível criar a cobrança no Asaas.',
                'error_code' => 'ASAAS_INTEGRATION_ERROR',
                'details' => [
                    'step' => 'CREATE_PAYMENT',
                    'provider' => 'ASAAS',
                    'error' => $e->getMessage()
                ],
                'http_status' => 502
            ];
        }
    }

    private function findOrCreateClienteLocal(array $p): Cliente
    {
        $doc = preg_replace('/\D/', '', $p['cpf_cnpj']);
        // Verify if client already exists
        $cliente = Cliente::where('documento', $doc)->first();
        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->documento = $doc;
            $cliente->status = 'ATIVO';
        }

        $cliente->nome = $p['nome_igreja'];
        $cliente->nome_igreja = $p['nome_igreja'];
        $cliente->nome_pastor = $p['nome_responsavel'];
        $cliente->nome_responsavel = $p['nome_responsavel'];
        $cliente->telefone = $p['telefone'];
        $cliente->whatsapp = $p['telefone'];
        $cliente->contato = $p['telefone'];
        $cliente->email = $p['email'] ?? null;
        if (isset($p['localidade'])) $cliente->localidade = $p['localidade'];
        $cliente->moeda = $p['moeda'] ?? 'BRL';
        $cliente->quantidade_membros = $p['quantidade_membros'] ?? 1;
        $cliente->save();

        return $cliente;
    }

    private function createVendaLocal(array $p, int $clienteId, string $modo): Venda
    {
        $venda = new Venda();
        $venda->cliente_id = $clienteId;
        $venda->vendedor_id = $p['vendedor_id'];
        $venda->plano_id = $p['plano_id'];
        $venda->tipo_negociacao = $p['tipo_negociacao'];
        $venda->modo_cobranca = $modo;
        $venda->valor_original = $p['valor_original'];
        $venda->percentual_desconto = $p['percentual_desconto'] ?? 0;
        $venda->valor_desconto = $p['valor_desconto'] ?? 0;
        $venda->valor_final = $p['valor_final'];
        $venda->valor = $p['valor_final']; // Default local compatibility
        $venda->observacao_interna = $p['observacao_interna'] ?? null;
        $venda->origem = $p['origem'];
        $venda->status = 'PROCESSANDO_INTEGRACAO';
        $venda->data_venda = now();
        $venda->save();

        return $venda;
    }

    private function buildGeneralPayload(Venda $venda, array $c): array
    {
        $payload = [
            'value' => $venda->valor_final,
            'description' => "Venda #{$venda->id} - {$venda->tipo_negociacao}",
            'externalReference' => "venda_{$venda->id}",
        ];
        
        $billingType = $c['forma_pagamento'] === 'CLIENTE_ESCOLHE' ? 'UNDEFINED' : $c['forma_pagamento'];
        // Translate BOLETO_PIX if necessary, Asaas BOLETO automatically enables PIX in their system.
        if ($billingType === 'BOLETO_PIX') $billingType = 'BOLETO';
        
        $payload['billingType'] = $billingType;

        if (!empty($c['juros_percentual_mes'])) {
            $payload['interest'] = ['value' => $c['juros_percentual_mes']];
        }
        if (!empty($c['multa_percentual'])) {
            $payload['fine'] = ['value' => $c['multa_percentual']];
        }
        if (!empty($c['desconto_antecipado_percentual'])) {
            $payload['discount'] = [
                'value' => $c['desconto_antecipado_percentual'],
                'dueDateLimitDays' => $c['dias_antes_desconto'] ?? 0
            ];
        }

        return $payload;
    }

    private function createAvulsa(Cliente $cliente, Venda $venda, array $c): array
    {
        $payload = array_merge($this->buildGeneralPayload($venda, $c), [
            'customer' => $cliente->asaas_customer_id,
            'dueDate' => $c['vencimento'],
        ]);

        return $this->asaasService->requestAsaas('POST', '/payments', $payload);
    }

    private function createParcelada(Cliente $cliente, Venda $venda, array $c): array
    {
        $payload = array_merge($this->buildGeneralPayload($venda, $c), [
            'customer' => $cliente->asaas_customer_id,
            'totalValue' => $venda->valor_final,
            'installmentCount' => $c['parcelas'],
            'dueDate' => $c['vencimento'],
        ]);
        unset($payload['value']); // Must use totalValue for installments

        return $this->asaasService->requestAsaas('POST', '/payments', $payload);
    }

    private function createSubscription(Cliente $cliente, Venda $venda, array $c): array
    {
        $payload = array_merge($this->buildGeneralPayload($venda, $c), [
            'customer' => $cliente->asaas_customer_id,
            'nextDueDate' => $c['vencimento'],
            'cycle' => $c['frequencia'],
        ]);

        return $this->asaasService->requestAsaas('POST', '/subscriptions', $payload);
    }

    private function persistirRetornoAsaas(Venda $venda, Cliente $cliente, array $res, string $modo, array $c)
    {
        $venda->status = 'AGUARDANDO_PAGAMENTO';
        $venda->save();

        if ($modo === 'ASSINATURA') {
            Assinatura::create([
                'venda_id' => $venda->id,
                'asaas_subscription_id' => $res['id'],
                'cycle' => $c['frequencia'],
                'next_due_date' => $c['vencimento'],
                'status' => 'ACTIVE'
            ]);
            
            // Optionally, create the first Pagamento entry to track visually, or wait for Asaas Webhook.
            Pagamento::create([
                'venda_id' => $venda->id,
                'cliente_id' => $cliente->id,
                'vendedor_id' => $venda->vendedor_id,
                'asaas_subscription_id' => $res['id'], // Added if missing from model to track
                // Asaas doesn't return payment ID immediately on subscription unless first one is generated
                'asaas_payment_id' => null,
                'valor' => $venda->valor_final,
                'billing_type' => $c['forma_pagamento'],
                'forma_pagamento' => $c['forma_pagamento'],
                'status' => 'PENDING',
                'data_vencimento' => $c['vencimento'],
            ]);

        } else {
            // Avulsa / Parcelada
            // Se for parcelada, o response tem 'installment' e pode não voltar as details de cara.
            Pagamento::create([
                'venda_id' => $venda->id,
                'cliente_id' => $cliente->id,
                'vendedor_id' => $venda->vendedor_id,
                'asaas_payment_id' => $res['id'],
                'valor' => $venda->valor_final,
                'billing_type' => $res['billingType'] ?? $c['forma_pagamento'],
                'forma_pagamento' => $c['forma_pagamento'],
                'status' => $res['status'] ?? 'PENDING',
                'data_vencimento' => $c['vencimento'],
                'invoice_url' => $res['invoiceUrl'] ?? null,
                'bank_slip_url' => $res['bankSlipUrl'] ?? null,
            ]);
        }
    }

    private function logIntegracao($entidade, $entidadeId, $acao, $req, $res, $statusHttp, $statusInt)
    {
        IntegracaoAsaasLog::create([
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'acao' => $acao,
            'request_payload' => $req,
            'response_payload' => $res,
            'status_http' => $statusHttp,
            'status_integracao' => $statusInt
        ]);
    }

    private function buildSuccessResponse(Venda $venda, Cliente $cliente, array $res, string $modo): array
    {
        return [
            'success' => true,
            'message' => $modo === 'ASSINATURA' ? 'Assinatura criada com sucesso.' : 'Cobrança criada com sucesso.',
            'data' => [
                'venda_id' => $venda->id,
                'cliente_id' => $cliente->id,
                'tipo_registro' => $modo,
                'status_interno' => 'AGUARDANDO_PAGAMENTO',
                'externos' => [
                    'asaas_customer_id' => $cliente->asaas_customer_id,
                    'asaas_payment_id' => $modo !== 'ASSINATURA' ? $res['id'] : null,
                    'asaas_subscription_id' => $modo === 'ASSINATURA' ? $res['id'] : null,
                ],
                'links' => [
                    'invoice_url' => $res['invoiceUrl'] ?? null,
                    'bank_slip_url' => $res['bankSlipUrl'] ?? null,
                    'pix_qrcode' => null, // will fetch async or from webhook
                ],
            ]
        ];
    }
}
