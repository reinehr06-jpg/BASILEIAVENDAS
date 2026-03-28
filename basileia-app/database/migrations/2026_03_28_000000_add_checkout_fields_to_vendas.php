<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'checkout_hash')) {
                $table->string('checkout_hash')->unique()->nullable()->after('data_venda');
            }
            if (!Schema::hasColumn('vendas', 'checkout_status')) {
                $table->string('checkout_status')->default('PENDENTE')->after('checkout_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['checkout_hash', 'checkout_status']);
        });
    }
};
