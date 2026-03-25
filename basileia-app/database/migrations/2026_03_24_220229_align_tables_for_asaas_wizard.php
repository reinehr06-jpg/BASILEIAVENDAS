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
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'asaas_customer_id')) {
                $table->string('asaas_customer_id')->nullable()->after('status');
            }
        });

        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'modo_cobranca')) {
                $table->string('modo_cobranca')->nullable()->after('tipo_negociacao');
            }
            if (!Schema::hasColumn('vendas', 'valor_desconto')) {
                $table->decimal('valor_desconto', 10, 2)->nullable()->after('percentual_desconto');
            }
            if (!Schema::hasColumn('vendas', 'observacao_interna')) {
                $table->text('observacao_interna')->nullable()->after('observacao');
            }
        });

        Schema::table('pagamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagamentos', 'billing_type')) {
                $table->string('billing_type')->nullable()->after('asaas_payment_id');
            }
            if (!Schema::hasColumn('pagamentos', 'invoice_url')) {
                $table->string('invoice_url')->nullable()->after('link_pagamento');
            }
            if (!Schema::hasColumn('pagamentos', 'bank_slip_url')) {
                $table->string('bank_slip_url')->nullable()->after('invoice_url');
            }
            if (!Schema::hasColumn('pagamentos', 'pix_qrcode')) {
                $table->text('pix_qrcode')->nullable()->after('bank_slip_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['asaas_customer_id']);
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['modo_cobranca', 'valor_desconto', 'observacao_interna']);
        });

        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'invoice_url', 'bank_slip_url', 'pix_qrcode']);
        });
    }
};
