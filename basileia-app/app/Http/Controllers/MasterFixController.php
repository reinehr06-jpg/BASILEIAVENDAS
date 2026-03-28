<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MasterFixController extends Controller
{
    /**
     * Rota de emergência para restaurar o acesso Master no servidor AWS.
     * 
     * Rota: GET /master-recovery-fix
     */
    public function fix()
    {
        Log::info('Master Recovery: Tentativa de restauração de conta Master.');

        $email = 'Basileia.vendas@Basileia.com';
        $password = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';

        // 1. Localizar ou criar o usuário
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = new User();
            $user->name = 'Administrador Master';
            $user->email = $email;
            $user->role = 'master'; // Define como Master
            $user->perfil = 'master'; // Alguns sistemas usam perfil em vez de role
        }

        // 2. Definir senha e garantir acesso
        $user->password = Hash::make($password);
        $user->status = 'ativo';
        $user->save();

        Log::info('Master Recovery: Conta Master restaurada com sucesso!', ['user_id' => $user->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Conta Master restaurada com sucesso!',
            'email' => $email,
            'password' => 'Senha definida para Basileia.vendas@Basileia.com',
            'note' => 'Por motivos de segurança, você deve deletar esta rota de routes/web.php após o uso.'
        ]);
    }
}
