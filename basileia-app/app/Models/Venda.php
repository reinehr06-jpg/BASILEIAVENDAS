<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $fillable = [
        'cliente_id', 'vendedor_id', 'valor', 'comissao_gerada', 'status',
        'plano', 'plano_id', 'forma_pagamento', 'tipo_negociacao', 'modo_cobranca', 'desconto', 'percentual_desconto',
        'valor_original', 'valor_desconto', 'valor_final', 'valor_comissao', 'observacao', 'observacao_interna', 'observacoes', 'origem', 'data_venda'
    ];

    protected function casts(): array
    {
        return [
            'data_venda' => 'date',
            'valor' => 'decimal:2',
            'comissao_gerada' => 'decimal:2',
            'desconto' => 'decimal:2',
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
}
