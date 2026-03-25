<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'venda_id', 'cliente_id', 'vendedor_id', 'asaas_payment_id',
        'valor', 'billing_type', 'forma_pagamento', 'status',
        'data_vencimento', 'data_pagamento',
        'link_pagamento', 'invoice_url', 'bank_slip_url', 'pix_qrcode', 'linha_digitavel',
        'nota_fiscal_url', 'nota_fiscal_status', 'recorrencia_status'
    ];

    protected function casts(): array
    {
        return [
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
}
