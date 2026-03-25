<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nome', 'nome_igreja', 'nome_pastor', 'nome_responsavel', 'localidade',
        'moeda', 'quantidade_membros', 'documento', 'contato', 'whatsapp', 'telefone', 'email', 'status', 'asaas_customer_id'
    ];

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

    /**
     * Check if this client has any active (unpaid) billing
     */
    public function temCobrancaAberta(): bool
    {
        return $this->vendas()
            ->whereIn('status', ['Aguardando pagamento', 'Pendente'])
            ->exists();
    }
}
