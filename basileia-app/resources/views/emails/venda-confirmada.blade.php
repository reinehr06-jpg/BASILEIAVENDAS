<x-mail::message>
# Pagamento Confirmado! 🎉

Olá **{{ $venda->vendedor->user->name ?? 'Vendedor' }}**,

Sua venda foi confirmada com sucesso. Confira os detalhes:

| Informação | Detalhe |
|:---|:---|
| **Igreja** | {{ $venda->cliente->nome_igreja ?? $venda->cliente->nome }} |
| **Pastor** | {{ $venda->cliente->nome_pastor ?? '—' }} |
| **Plano** | {{ $venda->plano ?? 'N/A' }} |
| **Valor** | R$ {{ number_format($venda->valor, 2, ',', '.') }} |
| **Forma de Pagamento** | {{ $pagamento->forma_pagamento ?? $venda->forma_pagamento ?? '—' }} |
| **Data do Pagamento** | {{ $pagamento->data_pagamento ? $pagamento->data_pagamento->format('d/m/Y') : now()->format('d/m/Y') }} |
| **Comissão Gerada** | R$ {{ number_format($comissao, 2, ',', '.') }} |

<x-mail::button :url="$linkVenda" color="primary">
Visualizar Venda
</x-mail::button>

Obrigado por sua dedicação!

**Equipe Basiléia Vendas**
</x-mail::message>
