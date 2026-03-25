<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    protected $fillable = ['venda_id', 'asaas_id', 'status', 'link'];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
