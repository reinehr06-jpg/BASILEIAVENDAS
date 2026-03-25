<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('nome_igreja')->nullable()->after('nome');
            $table->string('nome_pastor')->nullable()->after('nome_igreja');
            $table->string('localidade')->nullable()->after('nome_pastor');
            $table->string('moeda')->default('BRL')->after('localidade');
            $table->integer('quantidade_membros')->default(0)->after('moeda');
            $table->string('whatsapp')->nullable()->after('contato');
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->string('plano')->nullable()->after('status');
            $table->string('forma_pagamento')->nullable()->after('plano');
            $table->string('tipo_negociacao')->default('mensal')->after('forma_pagamento');
            $table->decimal('desconto', 5, 2)->default(0)->after('tipo_negociacao');
            $table->text('observacao')->nullable()->after('desconto');
            $table->string('origem')->default('manual')->after('observacao');
            $table->date('data_venda')->nullable()->after('origem');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['nome_igreja', 'nome_pastor', 'localidade', 'moeda', 'quantidade_membros', 'whatsapp']);
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['plano', 'forma_pagamento', 'tipo_negociacao', 'desconto', 'observacao', 'origem', 'data_venda']);
        });
    }
};
