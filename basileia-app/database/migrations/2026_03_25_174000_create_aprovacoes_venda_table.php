<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aprovacoes_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            
            // Tipo de aprovação
            $table->enum('tipo_aprovacao', ['DESCONTO', 'REPASSE', 'EXCECAO_COMERCIAL', 'VALOR_PERFORMANCE'])->default('DESCONTO');
            
            // Valores
            $table->decimal('percentual_solicitado', 10, 2);
            $table->decimal('limite_regra', 10, 2)->default(5.00); // Limite que triggerou a aprovação
            
            // Status
            $table->enum('status', ['PENDENTE', 'APROVADO', 'REJEITADO'])->default('PENDENTE');
            
            // Usuários envolvidos
            $table->foreignId('solicitado_por')->constrained('users')->onDelete('cascade');
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->onDelete('set null');
            
            // Observações
            $table->text('observacao')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['venda_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprovacoes_venda');
    }
};
