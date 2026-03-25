<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegracaoAsaasLog extends Model
{
    protected $table = 'integracao_asaas_logs';

    protected $fillable = [
        'entidade', 'entidade_id', 'acao', 'request_payload', 'response_payload',
        'status_http', 'status_integracao'
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'status_http' => 'integer',
        ];
    }
}
