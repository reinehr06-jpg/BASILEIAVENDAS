<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Venda;
use App\Services\AsaasService;

class PagamentoBoletoController extends Controller
{
    /**
     * GET /vendedor/vendas/{id}/boleto
     *
     * Busca o URL/dados do boleto gerado no Asaas e retorna JSON.
     * O frontend usa essa resposta para abrir o boleto DENTRO do painel,
     * sem redirecionar o usuário para uma página externa.
     */
    public function download(int $id)
    {
        $user    = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil de vendedor não encontrado.',
            ], 403);
        }

        // Garante que a venda pertence ao vendedor logado
        $venda = Venda::where('vendedor_id', $vendedor->id)
            ->with(['pagamentos'])
            ->find($id);

        if (!$venda) {
            return response()->json([
                'success' => false,
                'message' => 'Venda não encontrada.',
            ], 404);
        }

        $pagamento = $venda->pagamentos->first();

        if (!$pagamento) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma cobrança registrada para esta venda.',
            ], 404);
        }

        // -------------------------------------------------------
        // 1. Verifica se o banco_slip_url já está salvo localmente
        // -------------------------------------------------------
        $boletoUrl     = $pagamento->bank_slip_url ?? null;
        $linhaDigitavel = $pagamento->linha_digitavel ?? null;

        // -------------------------------------------------------
        // 2. Se não tiver local, busca on-demand no Asaas
        // -------------------------------------------------------
        if (!$boletoUrl && $pagamento->asaas_payment_id) {
            try {
                $asaas       = new AsaasService();
                $paymentData = $asaas->getPayment($pagamento->asaas_payment_id);

                if ($paymentData) {
                    $boletoUrl = $paymentData['bankSlipUrl'] ?? $paymentData['transactionReceiptUrl'] ?? null;

                    // Persiste para não precisar chamar o Asaas novamente
                    if (!empty($paymentData['bankSlipUrl'])) {
                        $pagamento->bank_slip_url = $paymentData['bankSlipUrl'];
                    }

                    // Atualiza a linha digitável se vier no payload
                    if (!empty($paymentData['nossoNumero']) || !empty($paymentData['identificationField'])) {
                        $linhaDigitavel = $paymentData['identificationField'] ?? $linhaDigitavel;
                        $pagamento->linha_digitavel = $linhaDigitavel;
                    }

                    $pagamento->save();

                    // Self-healing: Se o Asaas diz que tá pago, mas o banco local não (webhook falhou/bloqueado)
                    $statusAsaas = $paymentData['status'] ?? '';
                    if (in_array($statusAsaas, ['RECEIVED', 'CONFIRMED']) && $pagamento->status !== 'RECEIVED') {
                        $pagamento->status = 'RECEIVED';
                        $pagamento->data_pagamento = now();
                        if (!empty($paymentData['transactionReceiptUrl'])) {
                            $pagamento->link_pagamento = $paymentData['transactionReceiptUrl'];
                            $boletoUrl = $paymentData['transactionReceiptUrl'];
                        }
                        $pagamento->save();

                        if ($venda->status !== 'PAGO') {
                            $pagamentoService = new \App\Services\PagamentoService();
                            $pagamentoService->confirmarPagamento($pagamento, $paymentData);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('PagamentoBoletoController: erro ao buscar boleto no Asaas', [
                    'venda_id'         => $venda->id,
                    'asaas_payment_id' => $pagamento->asaas_payment_id,
                    'error'            => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível buscar o boleto no Asaas. Tente novamente em instantes.',
                ], 502);
            }
        }

        // -------------------------------------------------------
        // 3. Sem ID do Asaas e sem URL local → boleto não gerado
        // -------------------------------------------------------
        if (!$pagamento->asaas_payment_id && !$boletoUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cobrança ainda não possui um boleto gerado.',
            ], 404);
        }

        // -------------------------------------------------------
        // 4. Boleto não encontrado mesmo após consulta ao Asaas
        // -------------------------------------------------------
        if (!$boletoUrl) {
            return response()->json([
                'success' => false,
                'message' => 'O boleto ainda não está disponível. Aguarde alguns instantes e tente novamente.',
            ], 404);
        }

        return response()->json([
            'success'        => true,
            'url'            => $boletoUrl,
            'linha_digitavel' => $linhaDigitavel,
        ]);
    }
    /**
     * GET /vendedor/vendas/{id}/boleto/baixar
     *
     * Faz o download direto do PDF do boleto com o nome do cliente no arquivo.
     */
    public function forceDownload(int $id)
    {
        $user    = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) abort(403, 'Perfil de vendedor não encontrado.');

        $venda = Venda::where('vendedor_id', $vendedor->id)
            ->with(['cliente', 'pagamentos'])
            ->findOrFail($id);

        $pagamento = $venda->pagamentos->first();
        if (!$pagamento) abort(404, 'Nenhuma cobrança registrada.');

        // Sincronizar status antes de qualquer coisa para garantir dados novos
        $pagamentoService = new \App\Services\PagamentoService();
        $pagamentoService->sync($pagamento);

        $boletoUrl = $pagamento->bank_slip_url;

        // Se CONTINUAR sem URL salva, tenta buscar no Asaas on-demand (fallback)
        if (!$boletoUrl && $pagamento->asaas_payment_id) {
            try {
                $asaas = new AsaasService();
                $paymentData = $asaas->getPayment($pagamento->asaas_payment_id);
                $boletoUrl = $paymentData['bankSlipUrl'] ?? $paymentData['transactionReceiptUrl'] ?? null;
                
                if ($boletoUrl) {
                    $pagamento->bank_slip_url = $boletoUrl;
                    $pagamento->save();
                }
            } catch (\Exception $e) {
                Log::error('Erro ao buscar boleto para forceDownload', ['error' => $e->getMessage()]);
            }
        }

        if (!$boletoUrl) abort(404, 'Boleto não disponível no momento.');

        $clientName = $venda->cliente->nome_igreja ?? $venda->cliente->nome ?? 'Cliente';
        // Remover caracteres especiais do nome para o arquivo
        $safeName = preg_replace('/[^A-Za-z0-9\- ]/', '', $clientName);
        $filename = "Boleto - " . $safeName . ".pdf";

        try {
            $content = file_get_contents($boletoUrl);
            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Erro ao baixar PDF do Asaas', ['error' => $e->getMessage()]);
            return redirect()->away($boletoUrl); // Fallback: redireciona para a URL original
        }
    }
}
