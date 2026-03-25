<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriarCobrancaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Cliente
            'cliente.nome_igreja' => 'required|string|max:255',
            'cliente.nome_responsavel' => 'required|string|max:255',
            'cliente.cpf_cnpj' => 'required|string|max:20',
            'cliente.email' => 'nullable|email|max:255',
            'cliente.telefone' => 'required|string|max:20',
            'cliente.localidade' => 'nullable|string|max:255',
            'cliente.moeda' => 'required|string|max:10',
            'cliente.quantidade_membros' => 'required|integer|min:1',

            // Venda
            'venda.vendedor_id' => 'required|integer|exists:vendedores,id',
            'venda.plano_id' => 'required|integer', // |exists:planos,id if you have a planos table
            'venda.tipo_negociacao' => 'required|string|in:AVULSA,MENSAL,ANUAL',
            'venda.valor_original' => 'required|numeric|min:0',
            'venda.percentual_desconto' => 'nullable|numeric|min:0|max:100',
            'venda.valor_desconto' => 'nullable|numeric|min:0',
            'venda.valor_final' => 'required|numeric|min:0.01',
            'venda.observacao_interna' => 'nullable|string|max:1000',
            'venda.origem' => 'required|string|max:50',

            // Cobrança
            'cobranca.modo_cobranca' => 'required|string|in:AVULSA,PARCELADA,ASSINATURA',
            'cobranca.forma_pagamento' => 'required|string|in:BOLETO,PIX,BOLETO_PIX,CARTAO,CLIENTE_ESCOLHE',
            'cobranca.vencimento' => 'required|date',
            'cobranca.parcelas' => 'required|integer|min:1',
            'cobranca.frequencia' => 'required_if:cobranca.modo_cobranca,ASSINATURA|nullable|string|in:WEEKLY,BIWEEKLY,MONTHLY,QUARTERLY,SEMIANNUALLY,YEARLY',
            'cobranca.juros_percentual_mes' => 'nullable|numeric|min:0',
            'cobranca.multa_percentual' => 'nullable|numeric|min:0',
            'cobranca.desconto_antecipado_percentual' => 'nullable|numeric|min:0|max:100',
            'cobranca.dias_antes_desconto' => 'required_with:cobranca.desconto_antecipado_percentual|nullable|integer|min:1',

            // Notificações
            'notificacoes.whatsapp' => 'nullable|boolean',
            'notificacoes.email' => 'nullable|boolean',
            'notificacoes.sms' => 'nullable|boolean',
            'notificacoes.lembrete_automatico' => 'nullable|boolean',
            'notificacoes.dias_antes_vencimento' => 'required_if:notificacoes.lembrete_automatico,true|nullable|integer|min:0',
        ];
    }
}
