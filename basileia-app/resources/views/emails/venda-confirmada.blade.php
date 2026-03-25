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
| **Comissão** | R$ {{ number_format($comissao, 2, ',', '.') }} |

<x-mail::button :url="$linkVenda" color="primary">
Visualizar Venda
</x-mail::button>

---

### 📦 Pacote do Cliente

Após a confirmação do pagamento, disponibilize os seguintes itens ao cliente:

- 🔗 **Link de cadastro** na plataforma Basiléia
- 🎥 **Link das videoaulas** de onboarding
- 📄 **Nota fiscal** (disponível no painel de pagamentos)
- 📋 **Termos de uso** em PDF

---

Obrigado por sua dedicação!

**Equipe Basiléia Vendas**
</x-mail::message>
