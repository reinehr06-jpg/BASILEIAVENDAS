<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $cliente_id
 * @property int|null $vendedor_id
 * @property int|null $plano_id
 * @property string $plano
 * @property string $status
 * @property float $valor
 * @property float|null $valor_original
 * @property float|null $valor_final
 * @property string|null $checkout_hash
 * @property string|null $checkout_status
 * @property string|null $asaas_payment_id
 * @property string|null $forma_pagamento
 */
class Venda extends Model
{
    protected $fillable = [
        'cliente_id', 'vendedor_id', 'valor', 'comissao_gerada', 'status',
        'plano', 'plano_id', 'forma_pagamento', 'tipo_negociacao', 'modo_cobranca', 'desconto', 'percentual_desconto',
        'valor_original', 'valor_desconto', 'valor_final', 'valor_comissao', 'observacao', 'observacao_interna', 'observacoes', 'origem', 'data_venda',
        'parcelas',
        // Campos de checkout
        'checkout_hash', 'checkout_status',
        // Campos de aprovação
        'requer_aprovacao', 'status_aprovacao', 'aprovado_por', 'aprovado_em',
        // Campos Asaas
        'modo_cobranca_asaas', 'asaas_subscription_id', 'asaas_installment_id',
        // Campos de email
        'email_vendedor_enviado', 'email_cliente_enviado',
    ];

    protected function casts(): array
    {
        return [
            'data_venda' => 'date',
            'valor' => 'decimal:2',
            'comissao_gerada' => 'decimal:2',
            'desconto' => 'decimal:2',
            'requer_aprovacao' => 'boolean',
            'aprovado_em' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    public function integracoes()
    {
        return $this->hasMany(Integracao::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }
    
    public function participantes()
    {
        return $this->hasMany(VendaParticipante::class);
    }
    
    public function aprovacoes()
    {
        return $this->hasMany(AprovacaoVenda::class);
    }
    
    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }
    
    /**
     * Verifica se é uma venda anual parcelada
     */
    public function isAnualParcelada(): bool
    {
        return $this->tipo_negociacao === 'anual' && $this->parcelas > 1;
    }
    
    /**
     * Retorna a base de cálculo para comissão
     */
    public function getBaseComissao(float $valorPagamento): float
    {
        if ($this->isAnualParcelada()) {
            return $this->valor_final ?? $this->valor;
        }
        return $valorPagamento;
    }
    
    /**
     * Verifica se a venda pode gerar cobrança
     */
    public function podeGerarCobranca(): bool
    {
        if (!$this->requer_aprovacao) {
            return true;
        }
        return $this->status_aprovacao === 'aprovado';
    }

    /**
     * Verifica se é um pagamento parcelado (installment).
     */
    public function isPagamentoParcelado(): bool
    {
        return $this->parcelas > 1;
    }

    /**
     * Retorna a parcela atual paga (quantos pagamentos confirmados existem).
     */
    public function getParcelaAtual(): int
    {
        // The provided snippet seems to be controller logic and not suitable for this model method.
        // To maintain syntactic correctness and the method's original purpose,
        // the original implementation is kept, as the provided snippet would
        // introduce an undefined variable ($hash) and not return an int.
        return $this->pagamentos()
            ->whereIn('status', ['RECEIVED', 'CONFIRMED', 'pago'])
            ->count();
    }

    /**
     * Verifica se todas as parcelas foram pagas.
     */
    public function todasParcelasPagas(): bool
    {
        return $this->getParcelaAtual() >= $this->parcelas;
    }

    /**
     * Retorna o status efetivo considerando parcelas.
     * Para vendas parceladas: se alguma parcela foi paga, mantém "PAGO" até cancelamento.
     */
    public function getStatusEfetivo(): string
    {
        if (in_array($this->status, ['Cancelado', 'Expirado'])) {
            return $this->status;
        }

        if ($this->isPagamentoParcelado()) {
            $parcelaAtual = $this->getParcelaAtual();
            if ($parcelaAtual > 0) {
                if ($this->todasParcelasPagas()) {
                    return 'PAGO';
                }
                return 'PAGO';
            }
        }

        return $this->status;
    }

    /**
     * Retorna texto descritivo do progresso das parcelas.
     */
    public function getProgressoParcelas(): string
    {
        if (!$this->isPagamentoParcelado()) {
            return '';
        }
        return $this->getParcelaAtual() . '/' . $this->parcelas . ' parcelas pagas';
    }
}
