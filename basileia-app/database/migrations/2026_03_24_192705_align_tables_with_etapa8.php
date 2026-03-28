<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Atualizar tabela vendedores
        Schema::table('vendedores', function (Blueprint $table) {
            if (!Schema::hasColumn('vendedores', 'percentual_comissao')) {
                $table->decimal('percentual_comissao', 5, 2)->default(0)->after('comissao');
            }
            if (!Schema::hasColumn('vendedores', 'status')) {
                $table->string('status')->default('ativo')->after('meta_mensal');
            }
        });

        // Atualizar tabela clientes
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'nome_responsavel')) {
                $table->string('nome_responsavel')->nullable()->after('nome_pastor');
            }
            if (!Schema::hasColumn('clientes', 'telefone')) {
                $table->string('telefone')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('clientes', 'status')) {
                $table->string('status')->default('ativo')->after('quantidade_membros');
            }
        });

        // Atualizar tabela vendas
        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'plano_id')) {
                $table->foreignId('plano_id')->nullable()->constrained('planos')->nullOnDelete()->after('vendedor_id');
            }
            if (!Schema::hasColumn('vendas', 'percentual_desconto')) {
                $table->decimal('percentual_desconto', 10, 2)->default(0)->after('desconto');
            }
            if (!Schema::hasColumn('vendas', 'valor_original')) {
                $table->decimal('valor_original', 10, 2)->nullable()->after('valor');
            }
            if (!Schema::hasColumn('vendas', 'valor_final')) {
                $table->decimal('valor_final', 10, 2)->nullable()->after('valor_original');
            }
            if (!Schema::hasColumn('vendas', 'valor_comissao')) {
                $table->decimal('valor_comissao', 10, 2)->nullable()->after('valor_final');
            }
            if (!Schema::hasColumn('vendas', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('observacao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn(['percentual_comissao', 'status']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['nome_responsavel', 'telefone', 'status']);
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropForeign(['plano_id']);
            $table->dropColumn([
                'plano_id', 'percentual_desconto', 'valor_original', 
                'valor_final', 'valor_comissao', 'observacoes'
            ]);
        });
    }
};
