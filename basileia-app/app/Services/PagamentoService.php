<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Models\Venda;
use App\Models\LogEvento;
use App\Mail\VendaConfirmadaMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\AsaasService;

class PagamentoService
{
    /**
     * Sincroniza o status de um pagamento com o Asaas e atualiza a venda.
     * Retorna true se o pagamento foi confirmado nesta execução.
     */
    public function sync(Pagamento $pagamento): bool
    {
        if (!$pagamento->asaas_payment_id) {
            return false;
        }

        try {
            $asaas = new AsaasService();
            $paymentData = $asaas->getPayment($pagamento->asaas_payment_id);

            if (!$paymentData) return false;

            $statusAsaas = strtoupper($paymentData['status'] ?? '');
            $isPago = in_array($statusAsaas, ['RECEIVED', 'CONFIRMED']);
            $alreadyPago = in_array(strtoupper($pagamento->status), ['RECEIVED', 'CONFIRMED']);

            // Atualiza dados básicos se disponíveis
            if (!empty($paymentData['bankSlipUrl'])) {
                $pagamento->bank_slip_url = $paymentData['bankSlipUrl'];
            }
            if (!empty($paymentData['identificationField'])) {
                $pagamento->linha_digitavel = $paymentData['identificationField'];
            }
            if (!empty($paymentData['transactionReceiptUrl'])) {
                $pagamento->link_pagamento = $paymentData['transactionReceiptUrl'];
            }

            if ($isPago && !$alreadyPago) {
                $this->confirmarPagamento($pagamento, $paymentData);
                return true;
            }

            // Se não mudou para PAGO, apenas atualiza o status se for diferente
            if ($statusAsaas !== strtoupper($pagamento->status)) {
                $pagamento->status = $statusAsaas;
                $pagamento->save();

                $venda = $pagamento->venda;
                if ($venda) {
                    $vendaStatus = AsaasService::mapStatus($statusAsaas);
                    $venda->status = $vendaStatus;

                    // Se cancelado/estornado, remover comissão gerada
                    if (in_array($statusAsaas, ['CANCELED', 'DELETED', 'REFUNDED', 'REFUND_REQUESTED'])) {
                        $venda->comissao_gerada = 0;
                        $venda->valor_comissao = 0;
                    }

                    $venda->save();

                    // Sincronizar cobranças auxiliares
                    foreach ($venda->cobrancas as $cobranca) {
                        $cobranca->status = $statusAsaas;
                        $cobranca->save();
                    }
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('PagamentoService: Erro ao sincronizar', [
                'pagamento_id' => $pagamento->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Marca o pagamento como recebido, gera comissão e dispara automações.
     */
    public function confirmarPagamento(Pagamento $pagamento, array $paymentData = []): void
    {
        $pagamento->status = 'RECEIVED';
        $pagamento->data_pagamento = now();
        $pagamento->save();

        $venda = $pagamento->venda;
        if ($venda) {
            $statusAnterior = $venda->status;
            $venda->status = 'PAGO';
            
            // Gerar Comissão se ainda não existir
            if (!$venda->comissao_gerada || $venda->comissao_gerada <= 0) {
                $vendedor = $venda->vendedor;
                if ($vendedor) {
                    $percentual = $vendedor->percentual_comissao ?: ($vendedor->comissao ?: 10);
                    $comissao = ($pagamento->valor * $percentual) / 100;
                    
                    $venda->comissao_gerada = $comissao;
                    $venda->valor_comissao  = $comissao;
                }
            }
            
            $venda->save();

            // Sincronizar cobranças auxiliares
            foreach ($venda->cobrancas as $cobranca) {
                $cobranca->status = 'RECEIVED';
                $cobranca->save();
            }

            // Automações (Email)
            if (strtoupper($statusAnterior) !== 'PAGO') {
                $this->dispararAutomacoes($venda, $pagamento);
            }

            Log::info("PagamentoService: Venda #{$venda->id} confirmada com sucesso.");
        }

        // Log de Evento
        LogEvento::create([
            'usuario_id'  => 1,
            'entidade'    => 'Pagamento',
            'entidade_id' => $pagamento->id,
            'acao'        => 'Sincronização: Confirmado',
            'descricao'   => 'Pagamento detectado como pago no Asaas durante sincronização.',
        ]);
    }

    private function dispararAutomacoes(Venda $venda, Pagamento $pagamento): void
    {
        try {
            $vendedor = $venda->vendedor;
            if ($vendedor && $vendedor->user && $vendedor->user->email) {
                $comissao = $venda->comissao_gerada ?? 0;
                $linkVenda = url("/vendedor/vendas");
                Mail::to($vendedor->user->email)
                    ->send(new VendaConfirmadaMail($venda, $comissao, $linkVenda));
            }
        } catch (\Exception $e) {
            Log::error('PagamentoService: Falha ao enviar e-mail', ['venda_id' => $venda->id, 'error' => $e->getMessage()]);
        }
    }
}
