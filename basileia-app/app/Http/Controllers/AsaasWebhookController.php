<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Pagamento;
use App\Models\Venda;
use App\Models\Assinatura;
use App\Models\LogEvento;
use App\Services\AsaasService;
use App\Mail\VendaConfirmadaMail;
use App\Services\PagamentoService;

class AsaasWebhookController extends Controller
{
    /**
     * Receber webhook do Asaas e sincronizar status internos
     */
    public function handle(Request $request)
    {
        // Validar origem
        $webhookToken = \App\Models\Setting::get('asaas_webhook_token', config('services.asaas.webhook_token', env('ASAAS_WEBHOOK_TOKEN', '')));
        if ($webhookToken) {
            $headerToken = $request->header('asaas-access-token');
            if ($headerToken !== $webhookToken) {
                Log::warning('Asaas Webhook: token inválido', ['received' => $headerToken]);
                return response()->json(['error' => 'Token inválido'], 403);
            }
        }

        $payload = $request->all();
        $event   = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;

        if (!$event || !$payment) {
            Log::warning('Asaas Webhook: payload incompleto', $payload);
            return response()->json(['error' => 'Payload incompleto'], 400);
        }

        $asaasPaymentId = $payment['id'] ?? null;

        Log::info("Asaas Webhook: evento recebido", [
            'event'      => $event,
            'payment_id' => $asaasPaymentId,
            'status'     => $payment['status'] ?? null,
        ]);

        // Localizar pagamento no banco
        $pagamento = Pagamento::where('asaas_payment_id', $asaasPaymentId)->first();

        // 1. Tentar encontrar por externalReference caso o asaas_payment_id não esteja batendo (ex: pagamento gerado mas id não salvo a tempo)
        if (!$pagamento && !empty($payment['externalReference'])) {
            $extRef = $payment['externalReference'];
            if (str_starts_with($extRef, 'venda_')) {
                $vendaId = str_replace('venda_', '', $extRef);
                $vendaParaVincular = Venda::find($vendaId);
                if ($vendaParaVincular) {
                    $pagamento = Pagamento::where('venda_id', $vendaId)->first();
                    if ($pagamento && !$pagamento->asaas_payment_id) {
                        $pagamento->asaas_payment_id = $asaasPaymentId;
                        $pagamento->save();
                        Log::info("Asaas Webhook: Pagamento vinculado via externalReference", ['venda_id' => $vendaId, 'payment_id' => $asaasPaymentId]);
                    }
                }
            }
        }

        // 2. Se o pagamento for de uma ASSINATURA e for "PAYMENT_CREATED", o banco local pode ainda não tê-lo.
        if (!$pagamento && !empty($payment['subscription'])) {
            $assinatura = Assinatura::where('asaas_subscription_id', $payment['subscription'])->with('venda')->first();
            if ($assinatura && $assinatura->venda) {
                $pagamento = Pagamento::create([
                    'venda_id' => $assinatura->venda_id,
                    'cliente_id' => $assinatura->venda->cliente_id,
                    'vendedor_id' => $assinatura->venda->vendedor_id,
                    'asaas_payment_id' => $asaasPaymentId,
                    'valor' => $payment['value'] ?? $assinatura->venda->valor_final,
                    'billing_type' => $payment['billingType'] ?? 'BOLETO',
                    'forma_pagamento' => $payment['billingType'] ?? 'BOLETO',
                    'status' => 'PENDING',
                    'data_vencimento' => $payment['dueDate'] ?? null,
                    'invoice_url' => $payment['invoiceUrl'] ?? null,
                    'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
                ]);
                Log::info('Asaas Webhook: pagamento criado via ciclo de assinatura', ['payment_id' => $asaasPaymentId]);
            }
        }

        if (!$pagamento) {
            Log::warning("Asaas Webhook: pagamento não encontrado localmente", [
                'asaas_payment_id' => $asaasPaymentId,
                'external_reference' => $payment['externalReference'] ?? 'n/a',
                'event' => $event
            ]);
            return response()->json(['message' => 'Pagamento não encontrado'], 200); // Retorna 200 para o Asaas não ficar retentando algo que não temos
        }

        $statusAnterior = $pagamento->status;

        // Regra de eventos
        switch ($event) {
            case 'PAYMENT_CREATED':
                // status_interno = AGUARDANDO_PAGAMENTO
                $novoStatusPagamento = 'PENDING';
                $novoStatusVenda = 'Aguardando pagamento';
                break;
            case 'PAYMENT_RECEIVED':
            case 'PAYMENT_CONFIRMED':
                $novoStatusPagamento = 'RECEIVED';
                $novoStatusVenda = 'Pago';
                break;
            case 'PAYMENT_OVERDUE':
                $novoStatusPagamento = 'OVERDUE';
                $novoStatusVenda = 'Vencido';
                break;
            case 'PAYMENT_AWAITING_RISK_ANALYSIS':
                $novoStatusPagamento = 'AWAITING_RISK_ANALYSIS';
                $novoStatusVenda = 'Aguardando pagamento';
                break;
            case 'PAYMENT_DELETED':
            case 'PAYMENT_CANCELED':
            case 'PAYMENT_REFUNDED':
                $novoStatusPagamento = 'CANCELED';
                $novoStatusVenda = 'Cancelado';
                break;
            default:
                // Se for outro evento, mapeia pelo status real do asaas
                $novoStatusPagamento = strtoupper($payment['status'] ?? 'PENDING');
                $novoStatusVenda = AsaasService::mapStatus($novoStatusPagamento);
                break;
        }

        // Atualizar status do pagamento
        $pagamento->status = $novoStatusPagamento;

        // Atualizar data_pagamento quando pago
        if (in_array($novoStatusPagamento, ['RECEIVED', 'CONFIRMED'])) {
            $pagamento->data_pagamento = now();

            if (!empty($payment['identificationField'])) {
                $pagamento->linha_digitavel = $payment['identificationField'];
            }
            if (!empty($payment['transactionReceiptUrl'])) {
                $pagamento->link_pagamento = $payment['transactionReceiptUrl'];
            }
        }

        if (!empty($payment['dueDate'])) {
            $pagamento->data_vencimento = $payment['dueDate'];
        }

        $pagamento->save();

        // Atualizar status da venda vinculada
        $venda = Venda::with(['vendedor.user', 'cliente', 'cobrancas'])->find($pagamento->venda_id);
        if ($venda) {
            $statusVendaAnterior = $venda->status;
            $venda->status = $novoStatusVenda;
            $venda->save();

            // Sincronizar com a tabela 'cobrancas' (usada no Dashboard e Telas Master)
            foreach ($venda->cobrancas as $cobranca) {
                // Se o ID bater ou se a cobrança estiver sem ID (atribui o ID agora)
                if ($cobranca->asaas_id === $asaasPaymentId || !$cobranca->asaas_id) {
                    $cobranca->status = $novoStatusPagamento;
                    if (!$cobranca->asaas_id) $cobranca->asaas_id = $asaasPaymentId;
                    $cobranca->save();
                }
            }

            // Gerar comissão + automações via Service quando confirmado
            if (in_array($novoStatusPagamento, ['RECEIVED', 'CONFIRMED']) && strtoupper($statusVendaAnterior) !== 'PAGO') {
                $pagamentoService = new \App\Services\PagamentoService();
                $pagamentoService->confirmarPagamento($pagamento, $payment);
            }
        }

        // Buscar nota fiscal se disponível
        if (in_array($novoStatusPagamento, ['RECEIVED', 'CONFIRMED']) && $asaasPaymentId) {
            try {
                $asaas = new AsaasService();
                $invoice = $asaas->getInvoice($asaasPaymentId);
                if ($invoice && !empty($invoice['invoiceUrl'])) {
                    $pagamento->nota_fiscal_url    = $invoice['invoiceUrl'];
                    $pagamento->nota_fiscal_status = 'emitida';
                    $pagamento->save();
                }
            } catch (\Exception $e) {
                Log::warning('Asaas Webhook: erro ao buscar NF', ['error' => $e->getMessage()]);
            }
        }

        // Log de evento
        LogEvento::create([
            'usuario_id'  => 1,
            'entidade'    => 'Pagamento',
            'entidade_id' => $pagamento->id,
            'acao'        => "Webhook: {$event}",
            'descricao'   => "Status alterado de '{$statusAnterior}' para '{$novoStatusPagamento}'. Asaas ID: {$asaasPaymentId}",
        ]);

        return response()->json(['message' => 'Webhook processado com sucesso'], 200);
    }
}
