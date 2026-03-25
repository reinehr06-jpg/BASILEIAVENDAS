<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CriarCobrancaRequest;
use App\Services\CriarCobrancaService;
use Exception;

class VendaCobrancaController extends Controller
{
    protected CriarCobrancaService $criarCobrancaService;

    public function __construct(CriarCobrancaService $criarCobrancaService)
    {
        $this->criarCobrancaService = $criarCobrancaService;
    }

    /**
     * POST /api/vendas/criar-cobranca
     * Orquestra a criação de cobrança unificada via Asaas
     */
    public function createBilling(CriarCobrancaRequest $request)
    {
        $payload = $request->validated();

        $result = $this->criarCobrancaService->execute($payload);

        if (!$result['success']) {
            $status = $result['http_status'] ?? 422;
            unset($result['http_status']);
            return response()->json($result, $status);
        }

        return response()->json($result, 201);
    }
}
