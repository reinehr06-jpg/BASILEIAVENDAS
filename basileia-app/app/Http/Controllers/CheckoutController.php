<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function show(Request $request, $hash)
    {
        // Alterado para aceitar múltiplos status de pendência (Aguardando pagamento, pendente, etc)
        $venda = Venda::where('checkout_hash', $hash)
            ->whereIn('status', ['pendente', 'Aguardando pagamento', 'AGUARDANDO_PAGAMENTO', 'Aguardando aprovação', 'Aguardando Aprovação', 'pendente_asaas'])
            ->firstOrFail();

        $restritoMetodo = $request->get('method');

        // Se não houver restrição na URL, verifica se o vendedor já travou o método no banco
        if (!$restritoMetodo && !empty($venda->forma_pagamento)) {
            $map = [
                'CREDIT_CARD' => 'credit_card',
                'PIX'         => 'pix',
                'BOLETO'      => 'boleto',
                'cartao'      => 'credit_card'
            ];
            $restritoMetodo = $map[$venda->forma_pagamento] ?? strtolower($venda->forma_pagamento);
        }

        Log::info("Checkout acessado: Hash {$hash} | Venda ID: {$venda->id} | Restrito: {$restritoMetodo}");

        return view('checkout.index', compact('venda', 'restritoMetodo'));
    }

    public function process(Request $request, $hash)
    {
        $venda = Venda::where('checkout_hash', $hash)
            ->whereIn('status', ['pendente', 'Aguardando pagamento', 'AGUARDANDO_PAGAMENTO', 'Aguardando aprovação', 'Aguardando Aprovação', 'pendente_asaas'])
            ->firstOrFail();

        $request->validate([
            'payment_method' => 'required|in:credit_card,boleto,pix',
            'cpf_titular'    => 'required|string',
            'nome_cartao'    => 'required_if:payment_method,credit_card',
            'numero_cartao'  => 'required_if:payment_method,credit_card',
            'cvv'            => 'required_if:payment_method,credit_card',
            'expiry'         => 'required_if:payment_method,credit_card',
        ]);

        try {
            $asaas = app(AsaasService::class);

            // Tenta obter dados do cliente pela relação se não houver campos diretos na venda
            $nomeCliente = $venda->nome_cliente ?? ($venda->cliente->nome ?? ($venda->cliente->nome_fantasia ?? 'Cliente'));
            $emailCliente = $venda->email_cliente ?? ($venda->cliente->email ?? '');
            $telefoneCliente = $venda->telefone_cliente ?? ($venda->cliente->telefone ?? null);

            // 1. Cria ou busca o cliente no Asaas pelo CPF
            $customerId = $asaas->criarOuBuscarCliente([
                'nome'     => $nomeCliente,
                'cpf_cnpj' => $request->cpf_titular,
                'email'    => $emailCliente,
                'telefone' => $telefoneCliente,
            ]);

            // 2. Monta os dados do cartão se for credit_card
            $cartao = null;
            if ($request->payment_method === 'credit_card') {
                // Separa mês e ano da validade (formato MM/AA ou MM/AAAA)
                $partes = explode('/', $request->expiry);
                $cartao = [
                    'nome'   => $request->nome_cartao,
                    'numero' => preg_replace('/\D/', '', $request->numero_cartao),
                    'mes'    => trim($partes[0]),
                    'ano'    => strlen(trim($partes[1] ?? '')) === 2
                                    ? '20' . trim($partes[1])
                                    : trim($partes[1] ?? ''),
                    'cvv'    => $request->cvv,
                ];
            }

            // 3. Mapeia o método de pagamento para o formato do nosso AsaasService
            $tipoPagamento = match($request->payment_method) {
                'credit_card' => 'cartao',
                'pix'         => 'pix',
                'boleto'      => 'boleto',
            };

            // 4. Chama o Asaas e cria a cobrança
            $cobranca = $asaas->criarCobranca($customerId, [
                'id'             => $venda->id,
                'valor_total'    => $venda->valor,
                'tipo_negociacao' => $venda->tipo_negociacao ?? 'mensal',
                'cliente_nome'   => $nomeCliente,
                'cliente_email'  => $emailCliente,
                'cliente_cpf'    => $request->cpf_titular,
                'tipo_pagamento' => $tipoPagamento,
            ], $cartao);

            // 5. Calcula a data de renovação baseada no plano
            $dataInicio    = Carbon::today();
            $dataRenovacao = match($venda->tipo_negociacao ?? 'mensal') {
                'mensal'       => $dataInicio->copy()->addMonth(),
                'anual'        => $dataInicio->copy()->addYear(),
                'anual_avista' => $dataInicio->copy()->addYear(),
                'anual_12x'    => $dataInicio->copy()->addYear(),
                default        => $dataInicio->copy()->addMonth(),
            };

            // 6. Salva tudo na venda
            $venda->update([
                'asaas_payment_id'  => $cobranca['asaas_payment_id'],
                'asaas_customer_id' => $customerId,
                'bank_slip_url'     => $cobranca['bank_slip_url'] ?? null,
                'invoice_url'       => $cobranca['invoice_url'] ?? null,
                'pix_copia_cola'    => $cobranca['pix_copia_cola'] ?? null,
                'pix_qrcode_base64' => $cobranca['pix_qrcode'] ?? null,
                'cartao_token'      => $cobranca['cartao_token'] ?? null,
                'cartao_bandeira'   => $cobranca['cartao_bandeira'] ?? null,
                'cartao_final'      => $cobranca['cartao_final'] ?? null,
                'tipo_pagamento'    => $tipoPagamento,
                'data_inicio'       => $dataInicio,
                'data_renovacao'    => $dataRenovacao,
                'renovacao_ativa'   => true,
                // Status: cartão já fica confirmado. PIX/boleto aguarda confirmação do Asaas.
                'status'            => $tipoPagamento === 'cartao' ? 'Pago' : 'Aguardando pagamento',
            ]);

            Log::info("Checkout processado com sucesso: Venda #{$venda->id} | Método: {$tipoPagamento}");

            return redirect()->route('checkout.success', $hash)
                ->with('venda', $venda)
                ->with('cobranca', $cobranca);

        } catch (\Exception $e) {
            Log::error("Erro no Checkout Venda #{$venda->id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao processar pagamento: ' . $e->getMessage()]);
        }
    }

    public function success($hash)
    {
        $venda = Venda::where('checkout_hash', $hash)->firstOrFail();
        return view('checkout.success', compact('venda'));
    }
}
