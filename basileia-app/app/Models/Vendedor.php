<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedores';
    protected $fillable = ['usuario_id', 'comissao', 'percentual_comissao', 'telefone', 'meta_mensal', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class);
    }
}
