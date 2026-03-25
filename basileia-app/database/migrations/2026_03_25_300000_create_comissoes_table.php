<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('vendedores')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->string('tipo_comissao')->default('inicial'); // inicial, recorrencia
            $table->decimal('percentual_aplicado', 5, 2)->default(0);
            $table->decimal('valor_venda', 12, 2)->default(0);
            $table->decimal('valor_comissao', 12, 2)->default(0);
            $table->string('status')->default('pendente'); // pendente, confirmada, paga
            $table->date('data_pagamento')->nullable();
            $table->string('competencia')->nullable(); // Y-m
            $table->timestamps();

            $table->index(['vendedor_id', 'competencia']);
            $table->index(['status', 'competencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
