<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        // Puxa do banco primeiro, caso não tenha, faz fallback pro env
        $ambiente = \App\Models\Setting::get('asaas_environment', config('services.asaas.ambiente', 'sandbox'));
        
        $this->baseUrl = $ambiente === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';

        $this->apiKey = \App\Models\Setting::get('asaas_api_key', config('services.asaas.api_key', env('ASAAS_API_KEY', '')));
    }

    protected function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    // ============================================
    // 9.2.1 — Criar cliente no Asaas
    // ============================================
    public function findCustomerByCpfCnpj(string $cpfCnpj): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/customers", [
                    'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data']) && count($data['data']) > 0) {
                    return $data['data'][0]; // Retorna o primeiro cliente encontrado
                }
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar cliente por CPF/CNPJ', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function createCustomer(string $name, string $cpfCnpj, ?string $phone = null, ?string $email = null): array
    {
        // Primeiro tenta encontrar cliente existente
        $existing = $this->findCustomerByCpfCnpj($cpfCnpj);
        if ($existing) {
            Log::info('Asaas: cliente já existente, reutilizando', ['id' => $existing['id']]);
            return $existing;
        }

        $payload = [
            'name'    => $name,
            'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
        ];

        if ($phone) $payload['phone'] = preg_replace('/\D/', '', $phone);
        if ($email) $payload['email'] = $email;

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/customers", $payload);

        if ($response->successful()) {
            Log::info('Asaas: cliente criado', ['id' => $response->json()['id'] ?? null]);
            return $response->json();
        }

        Log::error('Asaas: erro ao criar cliente', [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception('Falha ao registrar cliente no Asaas: ' . $response->body());
    }

    // ============================================
    // 9.2.2 — Criar cobrança
    // ============================================
    public function createPayment(
        string $customerAsaasId,
        float $value,
        string $dueDate,
        string $billingType,
        string $description,
        ?string $externalReference = null
    ): array {
        $payload = [
            'customer'    => $customerAsaasId,
            'billingType' => $billingType, // BOLETO, CREDIT_CARD, PIX
            'value'       => $value,
            'dueDate'     => $dueDate,
            'description' => $description,
        ];

        if ($externalReference) {
            $payload['externalReference'] = $externalReference;
        }

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/payments", $payload);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('Asaas: cobrança criada', [
                'id'     => $data['id'] ?? null,
                'status' => $data['status'] ?? null,
                'value'  => $data['value'] ?? null,
            ]);
            return $data;
        }

        Log::error('Asaas: erro ao criar cobrança', [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception('Falha ao gerar cobrança no Asaas: ' . $response->body());
    }

    // ============================================
    // 9.2.3 — Consultar cobrança
    // ============================================
    public function getPayment(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Asaas: pagamento não encontrado', [
                'paymentId' => $paymentId,
                'status'    => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao consultar pagamento', ['error' => $e->getMessage()]);
        }

        return null;
    }

    // ============================================
    // 9.2.4 — Consultar QR Code PIX
    // ============================================
    public function getPixQrCode(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar QR Code PIX', ['error' => $e->getMessage()]);
        }

        return null;
    }

    // ============================================
    // 9.2.5 — Consultar linha digitável (boleto)
    // ============================================
    public function getIdentificationField(string $paymentId): ?string
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}/identificationField");

            if ($response->successful()) {
                return $response->json()['identificationField'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao buscar linha digitável', ['error' => $e->getMessage()]);
        }

        return null;
    }

    // ============================================
    // 9.2.6 — Consultar nota fiscal
    // ============================================
    public function getInvoice(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->get("{$this->baseUrl}/payments/{$paymentId}/fiscalInfo");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Asaas: erro ao consultar nota fiscal', ['error' => $e->getMessage()]);
        }

        return null;
    }

    // ============================================
    // 9.2.7 — Cancelar cobrança
    // ============================================
    public function cancelPayment(string $paymentId): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->delete("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                Log::info('Asaas: cobrança cancelada', ['paymentId' => $paymentId]);
                return true;
            }

            Log::warning('Asaas: falha ao cancelar cobrança', [
                'paymentId' => $paymentId,
                'response'  => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Asaas: erro ao cancelar cobrança', ['error' => $e->getMessage()]);
        }

        return false;
    }

    // ============================================
    // Helper: mapear status do Asaas para status local
    // ============================================
    public static function mapStatus(string $asaasStatus): string
    {
        return match (strtoupper($asaasStatus)) {
            'PENDING', 'AWAITING_RISK_ANALYSIS' => 'Aguardando pagamento',
            'CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH' => 'Pago',
            'OVERDUE' => 'Vencido',
            'REFUNDED', 'REFUND_REQUESTED', 'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE' => 'Estornado',
            'DUNNING_REQUESTED', 'DUNNING_RECEIVED' => 'Inadimplente',
            'CANCELED', 'DELETED' => 'Cancelado',
            default => 'Cancelado',
        };
    }

    // ============================================
    // Generic request for custom payloads
    // ============================================
    public function requestAsaas(string $method, string $endpoint, array $payload = []): array
    {
        $response = Http::withHeaders($this->headers());
        
        if (strtoupper($method) === 'POST') {
            $response = $response->post("{$this->baseUrl}{$endpoint}", $payload);
        } else if (strtoupper($method) === 'GET') {
            $response = $response->get("{$this->baseUrl}{$endpoint}", $payload);
        }

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Asaas: erro ao realizar {$method} {$endpoint}", [
            'request'  => $payload,
            'response' => $response->body(),
            'status'   => $response->status(),
        ]);
        throw new \Exception("Falha na requisição para o Asaas ($endpoint): " . $response->body());
    }
}
