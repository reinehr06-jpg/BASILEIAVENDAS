<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->string('asaas_payment_id')->nullable();
            $table->decimal('valor', 10, 2);
            $table->string('forma_pagamento')->default('pix'); // boleto, pix, cartao, recorrente
            $table->string('status')->default('pendente'); // pendente, pago, vencido, cancelado, estornado, inadimplente
            $table->date('data_vencimento')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->string('link_pagamento')->nullable();
            $table->string('linha_digitavel')->nullable();
            $table->string('nota_fiscal_url')->nullable();
            $table->string('nota_fiscal_status')->default('pendente'); // pendente, emitida, erro
            $table->string('recorrencia_status')->nullable(); // ativa, inativa, cancelada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
