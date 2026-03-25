<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Vendedor;
use App\Models\User;

class MasterPanelController extends Controller
{
    public function vendedores()
    {
        $vendedores = User::where('perfil', 'vendedor')->with('vendedor')->get();
        return view('master.vendedores.index', compact('vendedores'));
    }

    public function storeVendedor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telefone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'status' => 'required|in:ativo,inativo,bloqueado',
            'comissao' => 'required|numeric|min:0|max:100',
            'meta_mensal' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'perfil' => 'vendedor',
                'status' => $request->status,
            ]);

            Vendedor::create([
                'usuario_id' => $user->id,
                'telefone' => $request->telefone,
                'comissao' => $request->comissao,
                'meta_mensal' => $request->meta_mensal ?? 0,
            ]);

            DB::commit();

            return redirect()->route('master.vendedores')->with('success', 'Vendedor cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Falha crítica: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateVendedor(Request $request, $id)
    {
        $user = User::where('perfil', 'vendedor')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:ativo,inativo,bloqueado',
            'comissao' => 'required|numeric|min:0|max:100',
            'meta_mensal' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $user->vendedor()->updateOrCreate(
                ['usuario_id' => $user->id],
                [
                    'telefone' => $request->telefone,
                    'comissao' => $request->comissao,
                    'meta_mensal' => $request->meta_mensal ?? 0,
                ]
            );

            DB::commit();

            return redirect()->route('master.vendedores')->with('success', 'Vendedor atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Falha ao atualizar: ' . $e->getMessage()])->withInput();
        }
    }

    public function toggleVendedor($id)
    {
        $user = User::where('perfil', 'vendedor')->findOrFail($id);
        $novoStatus = $user->status === 'ativo' ? 'inativo' : 'ativo';
        $user->update(['status' => $novoStatus]);

        $msg = $novoStatus === 'ativo' ? 'Vendedor reativado com sucesso!' : 'Vendedor inativado com sucesso!';
        return redirect()->route('master.vendedores')->with('success', $msg);
    }

    public function pagamentos() { return view('placeholder', ['titulo' => 'Controle de Pagamentos']); }
    public function relatorios() { return view('placeholder', ['titulo' => 'Relatórios Consolidados']); }
    public function metas() { return view('placeholder', ['titulo' => 'Metas da Operação']); }
    public function clientes() { return view('placeholder', ['titulo' => 'Gestão de Clientes']); }
    public function configuracoes() { return view('master.configuracoes.index'); }
}
