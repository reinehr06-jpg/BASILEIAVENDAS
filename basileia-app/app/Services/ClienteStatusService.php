<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

class ClienteStatusService
{
    /**
     * Calcula o status do cliente com base nos seus pagamentos e vendas.
     *
     * Regras:
     *  - ATIVO:       último pagamento RECEIVED/CONFIRMED, sem cobrança vencida
     *  - PENDENTE:    cobrança PENDING dentro do prazo
     *  - INADIMPLENTE: cobrança OVERDUE ou vencida sem pagamento
     *  - CANCELADO:   pagamento CANCELED/DELETED
     *  - CHURN:       teve pagamento no passado mas perdeu renovação (>30 dias atrasado)
     */
    public static function calcularStatus(Cliente $cliente): string
    {
        // Buscar última venda ativa (não cancelada/expirada)
        $ultimaVenda = $cliente->vendas()
            ->whereNotIn('status', ['Cancelado', 'Expirado'])
            ->with('pagamentos')
            ->orderByDesc('created_at')
            ->first();

        // Sem venda ativa → verificar se já teve alguma venda paga
        if (!$ultimaVenda) {
            $teveVendaCancelada = $cliente->vendas()
                ->whereIn('status', ['Cancelado', 'Expirado'])
                ->exists();

            return $teveVendaCancelada ? 'cancelado' : 'pendente';
        }

        $ultimoPagamento = $ultimaVenda->pagamentos()
            ->orderByDesc('created_at')
            ->first();

        // Venda sem pagamento gerado → pendente
        if (!$ultimoPagamento) {
            return 'pendente';
        }

        $statusPagamento = strtoupper($ultimoPagamento->status);
        $vencimento = $ultimoPagamento->data_vencimento;
        $jafoiPago = $cliente->vendas()
            ->whereHas('pagamentos', fn ($q) => $q->whereIn('status', ['RECEIVED', 'CONFIRMED']))
            ->exists();

        // 1. Pago e em dia → ATIVO
        if (in_array($statusPagamento, ['RECEIVED', 'CONFIRMED'])) {
            return 'ativo';
        }

        // 2. Cancelado / deletado no Asaas → CANCELADO
        if (in_array($statusPagamento, ['CANCELED', 'DELETED'])) {
            return 'cancelado';
        }

        // 3. Vencido no Asaas ou vencimento passou → INADIMPLENTE ou CHURN
        $isOverdue = $statusPagamento === 'OVERDUE'
            || ($vencimento && $vencimento->isPast() && !in_array($statusPagamento, ['RECEIVED', 'CONFIRMED']));

        if ($isOverdue) {
            // Se já foi pago no passado e o atraso é > 30 dias → CHURN
            if ($jafoiPago && $vencimento && $vencimento->diffInDays(now()) > 30) {
                return 'churn';
            }
            return 'inadimplente';
        }

        // 4. Pendente dentro do prazo
        if ($statusPagamento === 'PENDING' || $statusPagamento === 'AWAITING_RISK_ANALYSIS') {
            // Se já foi pago no passado e está pendente há muito tempo → churn
            if ($jafoiPago && $vencimento && $vencimento->diffInDays(now()) > 30) {
                return 'churn';
            }
            return 'pendente';
        }

        return 'pendente';
    }

    /**
     * Sincroniza o status de todos os clientes (ou um subconjunto) no banco.
     * Usado antes de listar clientes na tela.
     */
    public static function sincronizarStatus($clientes = null): void
    {
        try {
            if ($clientes === null) {
                $clientes = Cliente::with(['vendas.pagamentos'])->get();
            }

            foreach ($clientes as $cliente) {
                $novoStatus = self::calcularStatus($cliente);

                // Atualizar data_ultimo_pagamento e proxima_cobranca
                $ultimaVenda = $cliente->vendas()
                    ->with('pagamentos')
                    ->orderByDesc('created_at')
                    ->first();

                $dataUltimoPagamento = null;
                $proximaCobranca = null;
                $recorrenciaStatus = null;

                if ($ultimaVenda) {
                    $ultimoPagoConfirmado = $ultimaVenda->pagamentos()
                        ->whereIn('status', ['RECEIVED', 'CONFIRMED'])
                        ->orderByDesc('data_pagamento')
                        ->first();

                    if ($ultimoPagoConfirmado && $ultimoPagoConfirmado->data_pagamento) {
                        $dataUltimoPagamento = $ultimoPagoConfirmado->data_pagamento->format('Y-m-d');
                    }

                    $proxCobranca = $ultimaVenda->pagamentos()
                        ->whereNotIn('status', ['RECEIVED', 'CONFIRMED', 'CANCELED', 'DELETED'])
                        ->orderBy('data_vencimento')
                        ->first();

                    if ($proxCobranca && $proxCobranca->data_vencimento) {
                        $proximaCobranca = $proxCobranca->data_vencimento->format('Y-m-d');
                    }

                    $recorrenciaStatus = $ultimaVenda->status;
                }

                $cliente->status = $novoStatus;
                $cliente->data_ultimo_pagamento = $dataUltimoPagamento;
                $cliente->proxima_cobranca = $proximaCobranca;
                $cliente->recorrencia_status = $recorrenciaStatus;
                $cliente->save();
            }
        } catch (\Exception $e) {
            Log::warning('ClienteStatusService: Erro ao sincronizar status', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Atualiza o status de um único cliente.
     */
    public static function atualizarCliente(Cliente $cliente): void
    {
        $cliente->load('vendas.pagamentos');
        $novoStatus = self::calcularStatus($cliente);

        $ultimaVenda = $cliente->vendas()
            ->with('pagamentos')
            ->orderByDesc('created_at')
            ->first();

        $dataUltimoPagamento = null;
        $proximaCobranca = null;
        $recorrenciaStatus = null;

        if ($ultimaVenda) {
            $ultimoPagoConfirmado = $ultimaVenda->pagamentos()
                ->whereIn('status', ['RECEIVED', 'CONFIRMED'])
                ->orderByDesc('data_pagamento')
                ->first();

            if ($ultimoPagoConfirmado && $ultimoPagoConfirmado->data_pagamento) {
                $dataUltimoPagamento = $ultimoPagoConfirmado->data_pagamento->format('Y-m-d');
            }

            $proxCobranca = $ultimaVenda->pagamentos()
                ->whereNotIn('status', ['RECEIVED', 'CONFIRMED', 'CANCELED', 'DELETED'])
                ->orderBy('data_vencimento')
                ->first();

            if ($proxCobranca && $proxCobranca->data_vencimento) {
                $proximaCobranca = $proxCobranca->data_vencimento->format('Y-m-d');
            }

            $recorrenciaStatus = $ultimaVenda->status;
        }

        $cliente->status = $novoStatus;
        $cliente->data_ultimo_pagamento = $dataUltimoPagamento;
        $cliente->proxima_cobranca = $proximaCobranca;
        $cliente->recorrencia_status = $recorrenciaStatus;
        $cliente->save();
    }
}
