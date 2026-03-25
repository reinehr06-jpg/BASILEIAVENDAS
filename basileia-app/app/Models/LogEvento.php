<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEvento extends Model
{
    // A tabela não tem updated_at, o log é imutável
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'entidade',
        'entidade_id',
        'acao',
        'descricao',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
