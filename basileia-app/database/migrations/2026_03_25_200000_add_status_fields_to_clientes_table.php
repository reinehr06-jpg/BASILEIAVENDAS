<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'data_ultimo_pagamento')) {
                $table->date('data_ultimo_pagamento')->nullable()->after('status');
            }
            if (!Schema::hasColumn('clientes', 'proxima_cobranca')) {
                $table->date('proxima_cobranca')->nullable()->after('data_ultimo_pagamento');
            }
            if (!Schema::hasColumn('clientes', 'recorrencia_status')) {
                $table->string('recorrencia_status')->nullable()->after('proxima_cobranca');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'data_ultimo_pagamento',
                'proxima_cobranca',
                'recorrencia_status',
            ]);
        });
    }
};
