<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class IntegracaoController extends Controller
{
    /**
     * Display the integrations settings page.
     */
    public function index()
    {
        $asaasApiKey = Setting::get('asaas_api_key', '');
        $asaasWebhookToken = Setting::get('asaas_webhook_token', '');
        $asaasEnvironment = Setting::get('asaas_environment', 'sandbox');

        return view('master.configuracoes.integracoes', compact('asaasApiKey', 'asaasWebhookToken', 'asaasEnvironment'));
    }

    /**
     * Update the integrations settings in the database.
     */
    public function update(Request $request)
    {
        $request->validate([
            'asaas_api_key' => 'nullable|string|max:255',
            'asaas_webhook_token' => 'nullable|string|max:255',
            'asaas_environment' => 'required|in:sandbox,production',
        ]);

        Setting::set('asaas_api_key', $request->input('asaas_api_key'));
        Setting::set('asaas_webhook_token', $request->input('asaas_webhook_token'));
        Setting::set('asaas_environment', $request->input('asaas_environment'));

        return redirect()->route('master.configuracoes.integracoes')
                         ->with('success', 'Configurações de integração atualizadas com sucesso.');
    }
}
