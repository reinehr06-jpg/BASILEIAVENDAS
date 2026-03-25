<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = [
        'nome',
        'faixa_min_membros',
        'faixa_max_membros',
        'valor_mensal',
        'valor_anual',
        'status',
    ];

    protected $casts = [
        'faixa_min_membros' => 'integer',
        'faixa_max_membros' => 'integer',
        'valor_mensal' => 'decimal:2',
        'valor_anual' => 'decimal:2',
        'status' => 'boolean',
    ];
}
