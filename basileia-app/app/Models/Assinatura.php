<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    protected $fillable = [
        'venda_id', 'asaas_subscription_id', 'cycle', 'next_due_date', 'status'
    ];

    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
        ];
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
