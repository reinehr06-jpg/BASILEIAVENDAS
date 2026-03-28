<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DatabaseResetController extends Controller
{
    /**
     * Rota de emergência para limpar o banco de dados e criar um novo admin fácil.
     * 
     * Rota: GET /emergency-database-reset-2026
     */
    public function reset()
    {
        Log::warning('Database Reset: Iniciando limpeza completa do banco de dados.');

        try {
            DB::beginTransaction();

            // 1. Desativar chaves estrangeiras para permitir truncate
            Schema::disableForeignKeyConstraints();

            // 2. Lista de tabelas para limpar (Truncate)
            $tables = [
                'users',
                'vendas',
                'clientes',
                'vendedores',
                'comissoes',
                'pagamentos',
                'metas',
                'notificacoes',
                'aprovacoes_venda',
                'equipes',
                'notas_fiscais',
                'checkout_sessions',
                'payment_events',
                'commission_rules',
                'legacy_commissions',
                'legacy_customer_imports',
                'legacy_customer_payments',
                'webhook_eventos',
                'log_eventos',
                'integracao_asaas_logs',
                'system_logs',
                'assinaturas',
                'cobrancas',
                'integracaos',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    Log::info("Database Reset: Tabela [$table] limpa.");
                }
            }

            // 3. Criar o novo Admin fácil
            $admin = User::create([
                'name' => 'Administrador Master',
                'email' => 'Basileia.vendas@Basileia.com',
                'password' => Hash::make('B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0'),
                'perfil' => 'master',
                'status' => 'ativo',
                'require_password_change' => false,
                'two_factor_enabled' => false,
            ]);

            Log::info('Database Reset: Novo Admin criado com sucesso.', ['id' => $admin->id]);

            // 4. Reativar chaves estrangeiras
            Schema::enableForeignKeyConstraints();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Banco de dados limpo com sucesso!',
                'new_admin' => [
                    'email' => 'Basileia.vendas@Basileia.com',
                    'password' => 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0',
                    'role' => 'master'
                ],
                'note' => 'Todas as tabelas transacionais foram resetadas. Planos e configurações básicas foram mantidos (se existiam).'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            
            Log::error('Database Reset: Erro crítico ao resetar banco.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao resetar banco: ' . $e->getMessage()
            ], 500);
        }
    }
}
