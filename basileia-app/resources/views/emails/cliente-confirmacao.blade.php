<x-mail::message>
# Compra Confirmada! 🎉

Olá **{{ $cliente->nome_igreja ?? $cliente->nome }}**,

Seu pagamento foi confirmado com sucesso. Bem-vindo à Basiléia!

| Informação | Detalhe |
|:---|:---|
| **Plano** | {{ $venda->plano ?? 'N/A' }} |
| **Valor Pago** | R$ {{ number_format($venda->valor, 2, ',', '.') }} |
| **Forma de Pagamento** | {{ $pagamento->forma_pagamento ?? $venda->forma_pagamento ?? '—' }} |
| **Data do Pagamento** | {{ $pagamento->data_pagamento ? $pagamento->data_pagamento->format('d/m/Y') : now()->format('d/m/Y') }} |

<x-mail::button url="https://app.basileia.com.br/cadastro" color="primary">
Acessar Plataforma
</x-mail::button>

---

### Próximos Passos

<x-mail::button url="https://app.basileia.com.br/videoaulas" color="success">
🎥 Assistir Videoaulas
</x-mail::button>

<x-mail::button url="https://app.basileia.com.br/termos" color="secondary">
📋 Termos de Uso
</x-mail::button>

@if($pagamento->nota_fiscal_url)
<x-mail::button :url="$pagamento->nota_fiscal_url" color="success">
📄 Acessar Nota Fiscal
</x-mail::button>
@endif

---

Caso tenha dúvidas, entre em contato com nosso suporte.

Obrigado por escolher a Basiléia!

**Equipe Basiléia**
</x-mail::message>
