<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Vendedor;
use App\Models\User;
use App\Models\Equipe;

class MasterPanelController extends Controller
{
    public function vendedores()
    {
        $vendedores = User::whereIn('perfil', ['vendedor', 'gestor'])->with('vendedor')->get();
        $gestores = User::whereHas('vendedor', function($q) { $q->where('is_gestor', true); })->with('vendedor')->get();
        return view('master.vendedores.index', compact('vendedores', 'gestores'));
    }

    public function storeVendedor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telefone' => 'nullable|string|max:20',
            'status' => 'required|in:ativo,inativo,bloqueado',
            'perfil' => 'required|in:vendedor,gestor',
            'comissao_inicial' => 'required|numeric|min:0|max:100',
            'comissao_recorrencia' => 'required|numeric|min:0|max:100',
            'comissao_gestor_primeira' => 'nullable|numeric|min:0|max:100',
            'comissao_gestor_recorrencia' => 'nullable|numeric|min:0|max:100',
            'gestor_id' => 'nullable|exists:users,id',
            'meta_mensal' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('Basileia123'),
                'perfil' => $request->perfil,
                'status' => $request->status,
                'require_password_change' => true,
            ]);

            $vendedor = Vendedor::create([
                'usuario_id' => $user->id,
                'is_gestor' => $request->perfil === 'gestor',
                'gestor_id' => $request->gestor_id,
                'telefone' => $request->telefone,
                'comissao' => $request->comissao_inicial ?? 0,
                'comissao_inicial' => $request->comissao_inicial,
                'comissao_recorrencia' => $request->comissao_recorrencia,
                'comissao_gestor_primeira' => $request->comissao_gestor_primeira ?? 0,
                'comissao_gestor_recorrencia' => $request->comissao_gestor_recorrencia ?? 0,
                'meta_mensal' => $request->meta_mensal ?? 0,
            ]);

            // Se for gestor, criar permissões padrão
            if ($request->perfil === 'gestor') {
                \App\Models\GestorPermissao::create([
                    'user_id' => $user->id,
                    'ver_vendas' => true,
                    'ver_clientes' => true,
                    'ver_comissoes' => true,
                    'ver_pagamentos' => true,
                    'ver_relatorios' => true,
                    'criar_vendas' => true,
                    'cancelar_vendas' => false,
                    'estornar_vendas' => false,
                    'gerenciar_vendedores' => false,
                    'ver_configuracoes' => false,
                ]);

                // Auto-criar equipe para o novo gestor
                EquipeController::autoCriarEquipe($user->id);
            }

            // Se tem gestor, garantir que a equipe existe e associar
            if ($request->gestor_id) {
                $equipe = EquipeController::autoCriarEquipe($request->gestor_id);
                if ($equipe) {
                    $vendedor->update(['equipe_id' => $equipe->id]);
                }
            }

            DB::commit();

            $msg = $request->perfil === 'gestor' ? 'Gestor cadastrado com sucesso!' : 'Vendedor cadastrado com sucesso!';
            return redirect()->route('master.vendedores')->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Falha crítica: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateVendedor(Request $request, $id)
    {
        $user = User::whereIn('perfil', ['vendedor', 'gestor'])->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:ativo,inativo,bloqueado',
            'perfil' => 'required|in:vendedor,gestor',
            'comissao_inicial' => 'required|numeric|min:0|max:100',
            'comissao_recorrencia' => 'required|numeric|min:0|max:100',
            'comissao_gestor_primeira' => 'nullable|numeric|min:0|max:100',
            'comissao_gestor_recorrencia' => 'nullable|numeric|min:0|max:100',
            'gestor_id' => 'nullable|exists:users,id',
            'meta_mensal' => 'nullable|numeric|min:0',
            // Validações de Split
            'asaas_wallet_id' => 'nullable|string|max:255',
            'tipo_split' => 'nullable|in:percentual,fixo',
            'valor_split_inicial' => 'nullable|numeric|min:0',
            'valor_split_recorrencia' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
                'perfil' => $request->perfil,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $user->vendedor()->updateOrCreate(
                ['usuario_id' => $user->id],
                [
                    'is_gestor' => $request->perfil === 'gestor',
                    'gestor_id' => $request->gestor_id,
                    'telefone' => $request->telefone,
                    'comissao' => $request->comissao_inicial ?? 0,
                    'comissao_inicial' => $request->comissao_inicial,
                    'comissao_recorrencia' => $request->comissao_recorrencia,
                    'comissao_gestor_primeira' => $request->comissao_gestor_primeira ?? 0,
                    'comissao_gestor_recorrencia' => $request->comissao_gestor_recorrencia ?? 0,
                    'meta_mensal' => $request->meta_mensal ?? 0,
                    'asaas_wallet_id' => $request->asaas_wallet_id,
                    'split_ativo' => $request->boolean('split_ativo'),
                    'tipo_split' => $request->tipo_split ?? 'percentual',
                    'valor_split_inicial' => $request->valor_split_inicial ?? 0,
                    'valor_split_recorrencia' => $request->valor_split_recorrencia ?? 0,
                ]
            );

            // Auto-criar equipe se gestor foi atribuído
            if ($request->gestor_id) {
                $equipe = EquipeController::autoCriarEquipe($request->gestor_id);
                if ($equipe && $user->vendedor) {
                    $user->vendedor->update(['equipe_id' => $equipe->id]);
                }
            } elseif (!$request->gestor_id && $user->vendedor) {
                $user->vendedor->update(['equipe_id' => null]);
            }

            DB::commit();

            return redirect()->route('master.vendedores')->with('success', 'Vendedor atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Falha ao atualizar: ' . $e->getMessage()])->withInput();
        }
    }

    public function toggleVendedor($id)
    {
        $user = User::whereIn('perfil', ['vendedor', 'gestor'])->findOrFail($id);
        $novoStatus = $user->status === 'ativo' ? 'inativo' : 'ativo';
        $user->update(['status' => $novoStatus]);

        $msg = $novoStatus === 'ativo' ? 'Reativado com sucesso!' : 'Inativado com sucesso!';
        return redirect()->route('master.vendedores')->with('success', $msg);
    }

    public function pagamentos() { return view('placeholder', ['titulo' => 'Controle de Pagamentos']); }
    public function relatorios() { return view('placeholder', ['titulo' => 'Relatórios Consolidados']); }
    public function metas() { return view('placeholder', ['titulo' => 'Metas da Operação']); }
    public function clientes() { return view('placeholder', ['titulo' => 'Gestão de Clientes']); }
    public function configuracoes() { return view('master.configuracoes.index'); }
}
