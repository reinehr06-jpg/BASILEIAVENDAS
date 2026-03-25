<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integracao extends Model
{
    protected $table = 'integracoes';
    protected $fillable = ['venda_id', 'status', 'retorno_asaas'];
    protected $casts = ['retorno_asaas' => 'array'];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
