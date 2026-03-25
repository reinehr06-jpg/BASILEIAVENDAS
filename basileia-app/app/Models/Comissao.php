<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    protected $table = 'comissoes';

    protected $fillable = [
        'vendedor_id', 'cliente_id', 'venda_id',
        'tipo_comissao', 'percentual_aplicado',
        'valor_venda', 'valor_comissao',
        'status', 'data_pagamento', 'competencia',
    ];

    protected function casts(): array
    {
        return [
            'percentual_aplicado' => 'decimal:2',
            'valor_venda' => 'decimal:2',
            'valor_comissao' => 'decimal:2',
            'data_pagamento' => 'date',
        ];
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
