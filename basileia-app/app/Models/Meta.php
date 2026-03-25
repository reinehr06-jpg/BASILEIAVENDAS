<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    const STATUS_NAO_INICIADA = 'não iniciada';
    const STATUS_EM_ANDAMENTO = 'em andamento';
    const STATUS_ATINGIDA   = 'atingida';
    const STATUS_NAO_ATINGIDA = 'não atingida';
    const STATUS_SUPERADA     = 'superada';

    protected $fillable = [
        'vendedor_id',
        'mes_referencia',
        'valor_meta',
        'observacao',
        'status',
    ];

    protected $casts = [
        'valor_meta' => 'decimal:2',
    ];

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
}
