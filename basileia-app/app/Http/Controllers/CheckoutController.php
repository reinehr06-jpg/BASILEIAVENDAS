<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use App\Models\Venda;
use App\Models\Vendedor;
use App\Services\CheckoutService;
use App\Services\CurrencyService;
use App\Services\ExchangeRateService;
use App\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected CheckoutService $checkoutService;
    protected ExchangeRateService $exchangeRateService;

    public function __construct()
    {
        $this->checkoutService = new CheckoutService;
        $this->exchangeRateService = new ExchangeRateService;
    }

    public function show(string $hash, Request $request)
    {
        $venda = Venda::where('checkout_hash', $hash)
            ->with(['cliente', 'vendedor.user', 'plano', 'pagamentos'])
            ->first();

        if (! $venda) {
            abort(404, 'Checkout não encontrado');
        }

        if ($venda->status === 'PAGO') {
            return redirect()->route('checkout.sucesso', $hash);
        }

        // Detectar idioma e moeda
        $language = $request->get('lang') ?? LanguageService::detectLanguage();
        $currency = $request->get('moeda') ?? CurrencyService::detectCurrency();

        // Aplicar locale para traduções via JSON
        $baseLocale = explode('-', $language)[0]; // ex: 'pt' de 'pt-BR'
        \Illuminate\Support\Facades\App::setLocale($baseLocale);

        // Se o idioma foi alterado, usar a moeda associada ao idioma
        if ($request->has('lang') && ! $request->has('moeda')) {
            $currency = LanguageService::getCurrencyForLanguage($language);
        }

        // Obter informações de conversão
        $valorOriginal = $venda->valor_original ?? $venda->valor;
        $valorFinal = $venda->valor_final ?? $venda->valor;

        $taxa = $this->exchangeRateService->getRate('BRL', $currency);
        $valorConvertido = $this->exchangeRateService->convert($valorFinal, 'BRL', $currency);
        $valorOriginalConvertido = $this->exchangeRateService->convert($valorOriginal, 'BRL', $currency);

        // Informações de moeda para formatação
        $currencyInfo = CurrencyService::getCurrencyInfo($currency);

        // Idiomas disponíveis para o seletor
        $availableLanguages = LanguageService::getLanguagesForSelector();

        // Idioma atual
        $currentLanguage = LanguageService::getLanguage($language);

        // Métodos de pagamento disponíveis por moeda
        $paymentMethods = static::getPaymentMethodsForCurrency($currency);

        $planos = Plano::orderBy('membros_minimos')->get();

        // Método de pagamento pré-selecionado via URL
        $preSelectedMethod = $request->get('method');

        return view('checkout.index', compact(
            'venda',
            'planos',
            'currency',
            'currencyInfo',
            'language',
            'availableLanguages',
            'currentLanguage',
            'taxa',
            'valorConvertido',
            'valorOriginalConvertido',
            'paymentMethods',
            'preSelectedMethod'
        ));
    }

    public function processar(Request $request, string $hash)
    {
        $venda = Venda::where('checkout_hash', $hash)->first();

        if (! $venda) {
            return response()->json(['success' => false, 'message' => 'Checkout não encontrado'], 404);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:cartao',
            'plano_id' => 'required|integer|exists:planos,id',
            'quantidade_membros' => 'required|integer|min:1',
            'card_number' => 'required|string',
            'card_name' => 'required|string',
            'card_expiry' => 'required|string',
            'card_cvv' => 'required|string',
            'currency' => 'nullable|string|in:BRL,USD,EUR',
            'language' => 'nullable|string',
        ]);

        $currency = $validated['currency'] ?? 'BRL';

        try {
            // Passar todos os dados validados para o serviço
            $result = $this->checkoutService->criarPagamento($venda, $validated, $currency);

            if ($result['success']) {
                $venda->update([
                    'checkout_status' => 'PROCESSANDO',
                    'asaas_payment_id' => $result['payment_id'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'redirect_url' => $result['redirect_url'] ?? null,
                    'payment_id' => $result['payment_id'],
                    'billing_type' => $result['billing_type'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erro ao processar pagamento',
            ], 400);

        } catch (\Exception $e) {
            Log::error('[Checkout] Erro ao processar pagamento', [
                'venda_id' => $venda->id,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: '.$e->getMessage(),
            ], 500);
        }
    }

    public function sucesso(string $hash, Request $request)
    {
        $venda = Venda::where('checkout_hash', $hash)
            ->with(['cliente', 'vendedor.user', 'plano', 'pagamentos'])
            ->first();

        if (! $venda) {
            abort(404);
        }

        $language = $request->get('lang') ?? LanguageService::detectLanguage();
        $baseLocale = explode('-', $language)[0];
        \App::setLocale($baseLocale ?: $language);
        $currentLanguage = LanguageService::getLanguage($language);
        $availableLanguages = LanguageService::getLanguagesForSelector();

        return view('checkout.sucesso', compact(
            'venda',
            'language',
            'currentLanguage',
            'availableLanguages'
        ));
    }

    public function cancelado(string $hash)
    {
        $venda = Venda::where('checkout_hash', $hash)->first();

        if (! $venda) {
            abort(404);
        }

        return view('checkout.cancelado', compact('venda'));
    }

    public function pix(string $hash, string $pagamentoId)
    {
        $venda = Venda::where('checkout_hash', $hash)->first();

        if (! $venda) {
            abort(404);
        }

        try {
            $pixData = $this->checkoutService->buscarPix($pagamentoId);

            return response()->json($pixData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function indicacao(string $vendedorHash)
    {
        $vendedor = Vendedor::where('hash_indicacao', $vendedorHash)
            ->orWhere('id', $vendedorHash)
            ->with('usuario')
            ->first();

        if (! $vendedor) {
            abort(404, 'Link de indicação inválido');
        }

        $planos = Plano::orderBy('membros_minimos')->get();

        return view('checkout.cadastro', compact('vendedor', 'planos'));
    }

    public function criarVenda(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email',
            'documento' => 'required|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'nome_igreja' => 'nullable|string|max:255',
            'quantidade_membros' => 'nullable|integer|min:1',
            'plano_id' => 'required|exists:planos,id',
            'forma_pagamento' => 'required|in:pix,boleto,cartao',
            'vendedor_id' => 'nullable|exists:vendedores,id',
            'hash_indicacao' => 'nullable|string',
        ]);

        $plano = Plano::findOrFail($validated['plano_id']);

        $vendedorId = $validated['vendedor_id'];
        if (! empty($validated['hash_indicacao'])) {
            $vendedorIndicacao = Vendedor::where('hash_indicacao', $validated['hash_indicacao'])->first();
            if ($vendedorIndicacao) {
                $vendedorId = $vendedorIndicacao->id;
            }
        }

        $clienteData = [
            'nome' => $validated['nome'],
            'email' => $validated['email'],
            'documento' => preg_replace('/\D/', '', $validated['documento']),
            'contato' => $validated['telefone'],
            'nome_igreja' => $validated['nome_igreja'],
            'quantidade_membros' => $validated['quantidade_membros'] ?? 1,
            'status' => 'pendente',
        ];

        $venda = $this->checkoutService->criarVendaECheckout($clienteData, $plano, $vendedorId, $validated['forma_pagamento']);

        return redirect()->route('checkout.show', $venda->checkout_hash);
    }

    /**
     * Métodos de pagamento disponíveis por moeda
     */
    protected static function getPaymentMethodsForCurrency(string $currency): array
    {
        return match ($currency) {
            'BRL' => ['pix', 'boleto', 'cartao'],
            'USD', 'EUR' => ['cartao'],
            default => ['pix', 'boleto', 'cartao'],
        };
    }
}
