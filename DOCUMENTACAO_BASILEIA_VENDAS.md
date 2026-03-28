# DOCUMENTAÇÃO COMPLETA — BASILÉIA VENDAS
## Sistema de Gestão Comercial para Basiléia Global

**Versão:** 2.0  
**Data:** 27/03/2026  
**Última Atualização:** Março 2026  
**Tecnologia:** Laravel 11 + PHP 8.2 + SQLite  
**Gateway de Pagamento:** Asaas API v3  
**Design System:** Materio Vuetify-inspired (CSS customizado)  
**Ícones:** Font Awesome 6.5  

---

## SUMÁRIO

1. Visão Geral do Sistema
2. Perfis de Usuário e Permissões
3. Módulo de Autenticação
4. Módulo Dashboard
5. Módulo de Vendedores (Gestão de Equipe)
6. Módulo de Vendas (Criação e Gerenciamento)
7. Módulo de Aprovações Comerciais
8. Módulo de Pagamentos
9. Módulo de Clientes
10. Módulo de Comissões
11. Módulo de Metas
12. Módulo de Relatórios
13. Módulo de Configurações
14. Sistema de Split Asaas
15. Sistema de E-mails Automáticos
16. Integração com Basiléia Church
17. Webhook do Asaas
18. Cancelamento e Exclusão de Vendas
19. Tutoriais Passo a Passo
20. Arquitetura Técnica
21. Tabelas do Banco de Dados
22. Resolução de Problemas Comuns
**Framework Frontend:** Blade Templates (sem SPA)  
**Integração Pagamentos:** Asaas API v3  

---

## 1. VISÃO GERAL

O **Basiléia Vendas** é um sistema completo de gestão comercial desenvolvido para a empresa Basiléia Global. Ele gerencia todo o ciclo de vida de uma venda de software (Basiléia Church), desde o cadastro do cliente até a cobrança recorrente, comissões de vendedores e integração com gateway de pagamento.

### 1.1 Objetivo do Sistema

- Gerenciar vendedores e suas metas comerciais
- Cadastrar clientes (igrejas) e criar vendas
- Gerar cobranças automáticas via Asaas (PIX, Boleto, Cartão de Crédito)
- Controlar pagamentos, comissões e repasses (split)
- Aprovar vendas com descontos especiais
- Enviar e-mails automáticos de confirmação
- Integrar com o Basiléia Church para provisionar contas

### 1.2 Perfis de Usuário

| Perfil | Acesso |
|--------|--------|
| **Master** | Acesso total: gestão de vendedores, vendas, pagamentos, relatórios, configurações |
| **Vendedor** | Acesso às próprias vendas, clientes, comissões e configurações de split |

---

## 2. MÓDULOS DO SISTEMA

### 2.1 AUTENTICAÇÃO

**Tela de Login:** `/login`

**Funcionalidades:**
- Login com e-mail (case-insensitive) e senha
- Redirecionamento automático por perfil (Master → `/master/dashboard`, Vendedor → `/vendedor/dashboard`)
- Logout com invalidação de sessão

**Regras de Negócio:**
- E-mails são convertidos para lowercase antes da autenticação
- Usuários com status diferente de `ativo` não conseguem fazer login

---

### 2.2 DASHBOARD

**Master:** `/master/dashboard`  
**Vendedor:** `/vendedor/dashboard`

**Cards Exibidos (Master):**
1. **Vendas Ativas** — Total de vendas com status ativo
2. **Vendedores Ativos** — Quantidade de vendedores ativos
3. **Comissões Pendentes** — Valor total de comissões não pagas
4. **Total Recebido** — Soma dos pagamentos confirmados no mês
5. **Clientes Ativos** — Quantidade de clientes com status ativo
6. **Churn do Mês** — Clientes que cancelaram no mês atual
7. **Desistências do Mês** — Vendas canceladas no mês
8. **Melhor Janela de Pagamento** — Dia da semana com mais pagamentos confirmados

**Cards Exibidos (Vendedor):**
1. Suas Vendas Ativas
2. Suas Comissões Pendentes

---

### 2.3 GESTÃO DE VENDEDORES

**Rota:** `/master/vendedores`

#### 2.3.1 Cadastro de Vendedor

**Campos Obrigatórios:**
- Nome Completo
- E-mail (usado para login)
- Telefone
- Senha Provisória
- Status (Ativo/Inativo)
- Comissão Inicial (%)
- Comissão Recorrência (%)

**Regras de Negócio:**
- O e-mail deve ser único no sistema
- A senha é hasheada antes de salvar
- Automaticamente cria um registro na tabela `users` e na tabela `vendedores`

#### 2.3.2 Edição de Vendedor

**Campos Editáveis:**
- Nome, E-mail, Telefone, Status
- Comissão Inicial (%)
- Comissão Recorrência (%)
- Nova Senha (opcional)

**Aba "Comissões e Repasse":**
- Visualização de comissão inicial e recorrência
- Configuração de split Asaas (apenas leitura para valores)

#### 2.3.3 Ativar/Inativar Vendedor

- Botão de toggle que alterna entre ativo e inativo
- Vendedores inativos não conseguem acessar o sistema
- Vendas existentes são mantidas no histórico

---

### 2.4 CRIAÇÃO DE VENDAS

**Rota:** `/vendedor/vendas/nova`

#### 2.4.1 Fluxo de Preenchimento

**Etapa 1 — Identificação do Cliente:**
- Nome da Igreja
- Nome do Pastor Responsável
- Localidade (cidade/estado)
- Moeda (BRL/USD/EUR)
- Quantidade de Membros
- CPF/CNPJ (com máscara automática)
- WhatsApp (com máscara automática)
- E-mail do Cliente

**Etapa 2 — Dados Comerciais:**
- **Plano:** Selecionado automaticamente pela quantidade de membros, ou manual
- **Forma de Pagamento:** PIX, Boleto ou Cartão de Crédito (cards visuais)
- **Tipo de Negociação:** Mensal ou Anual (cards visuais)
- **Parcelas:** Apenas para cartão de crédito (1-12x)
- **Desconto (%):** Máximo definido por configuração

#### 2.4.2 Planos Disponíveis

| Plano | Faixa de Membros | Valor Mensal | Valor Anual |
|-------|------------------|--------------|-------------|
| Essential | 1-50 | R$ 97,00 | R$ 970,00 |
| Essentials Plus | 51-100 | R$ 147,00 | R$ 1.470,00 |
| Growth | 101-200 | R$ 197,00 | R$ 1.970,00 |
| Professional | 201-500 | R$ 297,00 | R$ 2.970,00 |
| Performance | Acima de 500 | Negociável | Negociável |

#### 2.4.3 Regras de Negócio

1. **Seleção Automática de Plano:** Ao digitar a quantidade de membros, o sistema seleciona automaticamente o plano adequado. Planos com menos membros ficam desabilitados.

2. **Plano Performance:** Quando selecionado:
   - Aparece campo para digitar o valor combinado
   - Desaparece o campo de desconto
   - Venda é enviada automaticamente para aprovação
   - Mensagem de aviso é exibida

3. **Desconto:** 
   - Até 5%: aprovado automaticamente
   - Acima de 5%: enviado para aprovação do Master
   - 100%: bloqueado (valor final deve ser maior que zero)

4. **Parcelamento:** Disponível apenas para Cartão de Crédito (1-12x)

5. **Criação no Asaas:**
   - Verifica/cria cliente no Asaas pelo CPF/CNPJ
   - Se o CPF já existe com nome diferente, atualiza o nome
   - Cria cobrança no Asaas com split (se vendedor tiver wallet)

6. **Modos de Cobrança:**
   - `PAYMENT` — Cobrança avulsa (PIX, Boleto avulso, Cartão à vista)
   - `INSTALLMENT` — Parcelado (Cartão em 2-12x)
   - `SUBSCRIPTION` — Assinatura (Boleto mensal recorrente)

---

### 2.5 LISTA DE VENDAS

**Master:** `/master/vendas`  
**Vendedor:** `/vendedor/vendas`

#### 2.5.1 Colunas Exibidas

- Cliente (nome da igreja + pastor)
- Vendedor (apenas no Master)
- Plano
- Valor
- Status
- Pagamento (link direto do boleto ou copiar link)
- Data
- Ações (cancelar)

#### 2.5.2 Status das Vendas

| Status | Descrição |
|--------|-----------|
| Aguardando pagamento | Cobrança gerada, aguardando pagamento |
| Aguardando aprovação | Venda com desconto/performance aguardando Master |
| Pago | Pagamento confirmado |
| Vencido | Passou do prazo de pagamento |
| Cancelado | Venda cancelada |
| Expirado | Passou 72h sem pagamento |
| Estornado | Pagamento estornado |

#### 2.5.3 Exibição de Pagamento na Tabela

| Forma | O que aparece |
|-------|---------------|
| Boleto | Botão "Baixar Boleto" (abre PDF) |
| Cartão | Botão "Copiar Link" (link de pagamento) |
| PIX | Botão "Copiar Link PIX" |
| Sem link | Texto "Gerando..." |

#### 2.5.4 Expiração Automática

- Vendas com status "Aguardando pagamento" por mais de **72 horas** são automaticamente expiradas
- Executado sempre que o vendedor acessa a lista de vendas

#### 2.5.5 Sincronização com Asaas

- Ao carregar a lista, o sistema sincroniza proativamente vendas pendentes com o Asaas
- Máximo de 10 vendas por requisição
- Apenas vendas dos últimos 7 dias

---

### 2.6 APROVAÇÕES COMERCIAIS

**Rota:** `/master/aprovacoes`

#### 2.6.1 Quando uma Venda Requer Aprovação

1. **Desconto acima de 5%** — Tipo: `DESCONTO`
2. **Plano Performance** — Tipo: `VALOR_PERFORMANCE` (sempre requer)

#### 2.6.2 Fluxo de Aprovação

1. Vendedor cria venda com desconto/performance
2. Venda fica com status "Aguardando aprovação"
3. Master recebe notificação
4. Master aprova ou rejeita na tela de Aprovações
5. Se aprovado: gera cobrança no Asaas, status muda para "Aguardando pagamento"
6. Se rejeitado: status muda para "Cancelado", vendedor é notificado

#### 2.6.3 Campos Exibidos

- Venda #
- Vendedor
- Cliente
- Valor da venda
- Tipo (Desconto/Performance)
- Valor solicitado (% ou R$)
- Data da solicitação

---

### 2.7 CONTROLE DE PAGAMENTOS

**Master:** `/master/pagamentos`  
**Vendedor:** `/vendedor/pagamentos`

#### 2.7.1 Funcionalidades

- Visão consolidada de todas as cobranças e pagamentos
- Filtros por status, forma de pagamento, busca por igreja/vendedor
- Estatísticas: total, pagos, pendentes, valor recebido
- Botões de ação diretos:
  - **Boleto:** Botão que abre o PDF
  - **Cartão/PIX:** Botão que copia o link

#### 2.7.2 Dados Exibidos

- Igreja / Pastor
- Vendedor (apenas Master)
- Valor
- Forma de Pagamento
- Status
- Data de Vencimento
- Data de Pagamento
- Ações

---

### 2.8 GESTÃO DE CLIENTES

**Master:** `/master/clientes`  
**Vendedor:** `/vendedor/clientes`

#### 2.8.1 Cards de Resumo

- Total na Carteira
- Em Dia (ativos)
- Pendentes
- Inadimplentes
- Churn

#### 2.8.2 Status do Cliente

| Status | Regra |
|--------|-------|
| Ativo | Tem venda paga e sem débitos |
| Pendente | Primeira venda aguardando pagamento |
| Inadimplente | Tem cobrança vencida |
| Cancelado | Venda cancelada ou expirada |
| Churn | Cliente que já foi ativo mas perdeu acesso |

#### 2.8.3 Histórico do Cliente

**Rota:** `/vendedor/clientes/{id}` ou `/master/clientes/{id}`

**Aba "Histórico de Vendas":**
- Data da venda
- Plano
- Recorrência (mensal/anual + parcelas)
- Valor da parcela (não o total)
- Status
- Progresso de parcelas (ex: "3/12 parcelas pagas")

**Aba "Faturas Associadas":**
- Lista de todos os pagamentos (pagos e pendentes)
- Vencimento
- Forma de pagamento
- Valor
- Status
- Data de pagamento

---

### 2.9 COMISSÕES

**Master:** `/master/comissoes`  
**Vendedor:** `/vendedor/comissoes`

#### 2.9.1 Tipos de Comissão

| Tipo | Descrição |
|------|-----------|
| Inicial | Comissão sobre a primeira venda do cliente |
| Recorrência | Comissão sobre renovações e parcelas seguintes |

#### 2.9.2 Regras de Comissão

- Comissão é gerada automaticamente quando o pagamento é confirmado
- O percentual é definido pelo Master no cadastro do vendedor
- Para vendas parceladas: a primeira parcela usa `comissao_inicial`, as demais usam `comissao_recorrencia`
- Para assinaturas: mesma lógica de parcelas

#### 2.9.3 Cards de Resumo

- Pendente
- Confirmada
- Paga
- Total do Mês

#### 2.9.4 Filtros

- Mês de referência
- Tipo (inicial/recorrência)
- Status (pendente/confirmada/paga)
- Vendedor (apenas Master)

#### 2.9.5 Exportação

- Botão "Exportar Excel" gera arquivo CSV com todas as comissões filtradas

---

### 2.10 METAS E OBJETIVOS

**Rota:** `/master/metas`

#### 2.10.1 Funcionalidades

- Criar metas mensais por vendedor
- Acompanhar performance em tempo real
- Definir valor da meta e status

#### 2.10.2 Dados de Performance

- Valor Vendido (soma das vendas no mês)
- Valor Recebido (soma dos pagamentos confirmados)
- Clientes Ativos
- Percentual de Atingimento

#### 2.10.3 Status da Meta

| Status | Descrição |
|--------|-----------|
| Não iniciada | Meta criada, mês ainda não começou |
| Em andamento | Mês em curso |
| Atingida | Meta alcançada (100%) |
| Não atingida | Meta não alcançada |
| Superada | Meta ultrapassada |

---

### 2.11 RELATÓRIOS GERENCIAIS

**Rota:** `/master/relatorios`

#### 2.11.1 Filtros Disponíveis

- Período (data início/fim)
- Vendedor
- Status da venda
- Forma de pagamento
- Tipo de negociação
- Cliente
- Tipo de recorrência

#### 2.11.2 Seções do Relatório

1. **Resumo Geral:** Total de vendas, valor vendido/recebido, comissões, clientes ativos, churn, desistências, renovações, ticket médio

2. **Vendas por Vendedor:** Ranking de vendedores com metas e percentual de atingimento

3. **Pagamentos por Período:** Evolução de pagamentos (total, pagos, pendentes, vencidos)

4. **Churn e Renovações:** Taxa de churn, total de renovações, ticket médio

5. **Formas de Pagamento:** Distribuição por PIX, Boleto, Cartão

#### 2.11.3 Exportação

- Botão "Exportar CSV" gera arquivo com todas as vendas filtradas

---

### 2.12 CONFIGURAÇÕES DO SISTEMA

**Rota:** `/master/configuracoes/integracoes`

#### 2.12.1 Integração Asaas

| Campo | Descrição |
|-------|-----------|
| Ambiente | Sandbox (testes) ou Produção |
| API Key | Token de acesso à API do Asaas |
| Webhook Token | Token para validar webhooks recebidos |
| URL de Callback | URL para receber notificações |

**Botão "Testar Conexão":** Verifica se a API Key é válida

#### 2.12.2 Configurações de Split

| Campo | Descrição |
|-------|-----------|
| Split Global Ativo | Habilita/desabilita split no sistema |
| Juros Padrão | Taxa de juros para pagamentos em atraso |
| Multa Padrão | Percentual de multa para atraso |

#### 2.12.3 Configurações de E-mail

| Campo | Descrição |
|-------|-----------|
| Remetente Vendedor | E-mail "de" para vendedores |
| Remetente Cliente | E-mail "de" para clientes |
| E-mail Suporte | E-mail de suporte |
| WhatsApp Suporte | Número do WhatsApp de suporte |

#### 2.12.4 Integração Basiléia Church

| Campo | Descrição |
|-------|-----------|
| Webhook URL | URL do webhook do Church |
| Webhook Token | Token de autenticação |

#### 2.12.5 Google Calendar

- Configurações OAuth para integração com Google Calendar

#### 2.12.6 Google Gmail

- Configurações OAuth para envio de e-mails via Gmail

---

### 2.13 CONFIGURAÇÕES DO VENDEDOR (Split e Repasse)

**Rota:** `/vendedor/configuracoes`

#### 2.13.1 O que o Vendedor pode fazer

- **Ativar/Desativar Split Automático**
- **Inserir/Atualizar Wallet ID do Asaas**

#### 2.13.2 O que é Somente Leitura

- Comissão Inicial (%) — definida pelo Master
- Comissão Recorrência (%) — definida pelo Master
- Tipo de Repasse — definido pelo Master
- Valor Repasse Inicial — definido pelo Master
- Valor Repasse Recorrência — definido pelo Master

#### 2.13.3 Fluxo de Validação do Wallet

1. Vendedor insere Wallet ID do Asaas
2. Sistema valida no Asaas se o Wallet existe
3. Status muda para "Aguardando Validação"
4. Master valida manualmente na tela de Vendedores
5. Status muda para "Validado"
6. Após validado, campos ficam travados

#### 2.13.4 Split no Asaas

- Quando uma venda é criada e o vendedor tem split válido:
  - O sistema inclui o parâmetro `split` na criação da cobrança
  - O Asaas repassa automaticamente o percentual/valor para a carteira do vendedor
  - O restante fica na conta principal da Basiléia

---

### 2.14 SPLIT ASAAS — DETALHAMENTO TÉCNICO

#### 2.14.1 Como o Split Funciona

Quando uma cobrança é criada no Asaas com split, o gateway automaticamente divide o valor:

```json
{
  "split": [
    {
      "walletId": "wallet_do_vendedor",
      "fixedValue": 10.00  // ou "percentualValue": 10
    }
  ]
}
```

#### 2.14.2 Tipos de Split

| Tipo | Descrição |
|------|-----------|
| Percentual | Percentual do valor da venda vai para o vendedor |
| Fixo | Valor fixo vai para o vendedor |

#### 2.14.3 Quando o Split é Aplicado

- Apenas quando `split_ativo = true`
- Apenas quando `asaas_wallet_id` está preenchido
- Apenas quando `wallet_status = 'validado'`
- Usa `comissao_inicial` para primeira venda, `comissao_recorrencia` para recorrência

---

### 2.15 E-MAILS AUTOMÁTICOS

#### 2.15.1 Quando são Enviados

Os e-mails são disparados **apenas quando o pagamento é confirmado** (webhook PAYMENT_RECEIVED do Asaas), nunca quando a venda é criada.

#### 2.15.2 E-mail do Vendedor

**Destinatário:** Vendedor que realizou a venda  
**Assunto:** "✅ Pagamento Confirmado — [Nome da Igreja]"

**Conteúdo:**
- Nome da igreja
- Nome do responsável
- Plano contratado
- Valor da venda
- Comissão gerada
- Forma de pagamento
- Data do pagamento

#### 2.15.3 E-mail do Cliente

**Destinatário:** E-mail do cliente  
**Assunto:** "🎉 Bem-vindo(a) ao Basiléia Global!"

**Conteúdo:**
- Confirmação da compra
- Nome do plano
- Valor pago
- Botão: "Acessar Minha Conta" (link para login)
- Botão: "Vídeos de Implementação"
- Botão: "Falar com o Suporte" (WhatsApp)

#### 2.15.4 Controle de Duplicidade

- Cada e-mail tem uma flag no banco: `email_vendedor_enviado` e `email_cliente_enviado`
- Os Jobs verificam a flag antes de enviar
- Flags são resetadas quando um e-mail é reenviado manualmente

---

### 2.16 INTEGRAÇÃO COM BASILÉIA CHURCH

#### 2.16.1 Provisionamento Automático

Quando um pagamento é confirmado, o sistema automaticamente cria uma conta no Basiléia Church:

1. Envia POST para `/api/provisioning/create-account` do Church
2. Dados enviados: nome, e-mail, documento, telefone, senha provisória, plano
3. Church retorna o `user_id` da conta criada
4. O `church_user_id` é salvo no cliente

#### 2.16.2 Verificação de Status

O Basiléia Church pode consultar o status do cliente:

```
GET /api/client-status/{venda_id}
Header: Authorization: Bearer {CHURCH_API_SECRET}
```

**Resposta:**
```json
{
  "venda_id": 42,
  "cliente_nome": "Igreja Exemplo",
  "status": "active",  // active | suspended | inactive
  "plano": "Growth",
  "valor": 197.00,
  "atualizado_em": "2026-03-27T10:00:00"
}
```

#### 2.16.3 Mapeamento de Status

| Status Venda | Status Church |
|--------------|---------------|
| Pago | active |
| Vencido | suspended |
| Cancelado/Expirado/Estornado | inactive |

---

### 2.17 WEBHOOK ASAAS

**Rota:** `/api/asaas/webhook` (POST)

#### 2.17.1 Validação

- Token enviado no header `asaas-access-token` deve bater com `asaas_webhook_token` salvo nas configurações

#### 2.17.2 Eventos Processados

| Evento | Ação |
|--------|------|
| PAYMENT_CREATED | Atualiza status para "Aguardando pagamento" |
| PAYMENT_RECEIVED | Confirma pagamento, gera comissão, envia e-mails, provisiona Church |
| PAYMENT_CONFIRMED | Mesmo que PAYMENT_RECEIVED |
| PAYMENT_OVERDUE | Marca como "Vencido" |
| PAYMENT_DELETED/CANCELED/REFUNDED | Marca como "Cancelado" |

#### 2.17.3 Fluxo ao Receber PAYMENT_RECEIVED

1. Localiza pagamento pelo `asaas_payment_id` (ou `subscription` ID)
2. Atualiza status do pagamento para RECEIVED
3. Salva data de pagamento, URLs de comprovante
4. Atualiza status da venda para "Pago"
5. Sincroniza tabela de cobranças
6. Busca nota fiscal no Asaas
7. Gera registro de comissão
8. Chama `PagamentoService::confirmarPagamento()`
9. Provisiona conta no Basiléia Church
10. Envia e-mails automáticos
11. Registra log do evento

#### 2.17.4 Self-Healing

Quando o vendedor acessa o boleto ou detalhes da cobrança, o sistema:
1. Busca o status atual no Asaas
2. Se o Asaas diz "RECEIVED" mas o banco local não, atualiza automaticamente
3. Dispara todas as automações (comissão, e-mails, Church)

---

### 2.18 CANCELAMENTO DE VENDAS

#### 2.18.1 Quem pode Cancelar

- **Vendedor:** Apenas suas próprias vendas não pagas
- **Master:** Qualquer venda não paga

#### 2.18.2 Regras

- Vendas com status "Pago" **não podem** ser canceladas
- Vendas "Aguardando aprovação" são canceladas apenas localmente (sem cancelar no Asaas)
- Cancelamento tenta cancelar no Asaas primeiro, depois atualiza localmente

#### 2.18.3 Cancelamento no Asaas

| Tipo de Cobrança | Endpoint Usado |
|------------------|----------------|
| Parcelado (INSTALLMENT) | `DELETE /installments/{id}` — cancela TODAS as parcelas |
| Assinatura (SUBSCRIPTION) | `DELETE /subscriptions/{id}` — cancela a assinatura |
| Avulso (PAYMENT) | `POST /payments/{id}/cancel` — cancela a cobrança |

#### 2.18.4 Descoberta de Installment ID

Se a venda foi parcelada mas o `asaas_installment_id` não foi salvo:
1. Busca o primeiro pagamento da venda
2. Consulta `GET /payments/{id}` no Asaas
3. Pega o campo `installment` da resposta
4. Salva para futuras referências
5. Usa para cancelar todas as parcelas

---

## 3. TABELAS DO BANCO DE DADOS

### 3.1 Principais Tabelas

| Tabela | Descrição |
|--------|-----------|
| `users` | Usuários do sistema (Master/Vendedor) |
| `vendedores` | Perfil dos vendedores (comissão, split, meta) |
| `clientes` | Igrejas/clientes cadastrados |
| `vendas` | Vendas realizadas |
| `pagamentos` | Registros de pagamento individuais |
| `cobrancas` | Cobranças criadas no Asaas |
| `comissoes` | Comissões geradas |
| `aprovacoes_venda` | Solicitações de aprovação |
| `notificacoes` | Notificações do sistema |
| `metas` | Metas mensais por vendedor |
| `log_eventos` | Log de eventos do sistema |

### 3.2 Tabelas Auxiliares

| Tabela | Descrição |
|--------|-----------|
| `settings` | Configurações do sistema |
| `planos` | Planos disponíveis |
| `assinaturas` | Assinaturas Asaas |
| `integracoes` | Integrações por venda |
| `integracao_asaas_logs` | Log de chamadas à API Asaas |
| `venda_participantes` | Participantes de uma venda (split) |
| `cache` | Cache do sistema |
| `sessions` | Sessões de usuários |
| `jobs` | Fila de jobs |
| `failed_jobs` | Jobs falhados |

---

## 4. FILA DE JOBS

| Job | Descrição | Retries | Timeout |
|-----|-----------|---------|---------|
| `SendEmailVendedorJob` | Envia e-mail de confirmação para o vendedor | 3 | 60s |
| `SendEmailClienteJob` | Envia e-mail de boas-vindas para o cliente | 3 | 60s |

---

## 5. COMANDOS ARTISAN

| Comando | Descrição |
|---------|-----------|
| `vendas:gerar-renovacao-anual` | Gera automaticamente cobranças de renovação anual para vendas que completaram 1 ano |
| `email:test-flow` | Testa o fluxo completo de e-mails (cria dados de teste e envia) |

---

## 6. MIDDLEWARE

| Middleware | Descrição |
|------------|-----------|
| `CheckMaster` | Verifica se o usuário é Master |
| `CheckVendedor` | Verifica se o usuário é Vendedor |
| `ClearStaleCache` | Limpa cache automaticamente |

---

## 7. TUTORIAL DE USO

### 7.1 Como Cadastrar um Vendedor (Master)

1. Acesse **Equipe de Vendas** no menu lateral
2. Clique em **Novo Vendedor**
3. Preencha nome, e-mail, telefone, senha provisória
4. Defina a **Comissão Inicial** (ex: 10%)
5. Defina a **Comissão Recorrência** (ex: 5%)
6. Clique em **Registrar**
7. O vendedor receberá o e-mail e senha para acessar o sistema

### 7.2 Como Criar uma Venda (Vendedor)

1. Acesse **Vendas Realizadas** → **Nova Venda**
2. Preencha os dados da igreja (nome, pastor, localidade, membros)
3. O sistema seleciona automaticamente o plano baseado nos membros
4. Escolha a forma de pagamento (PIX/Boleto/Cartão)
5. Escolha o tipo (Mensal/Anual)
6. Se Cartão, selecione o número de parcelas
7. Aplique desconto se necessário (até 5%)
8. Clique em **Gerar Cobrança e Salvar**
9. O link do boleto ou pagamento aparece diretamente na tabela

### 7.3 Como Aprovar uma Venda (Master)

1. Acesse **Aprovações Pendentes** no menu
2. Veja a lista de vendas aguardando aprovação
3. Analise o desconto ou valor do plano Performance
4. Clique em **Aprovar** ou **Rejeitar**
5. Adicione uma observação se necessário
6. Se aprovado, a cobrança é gerada automaticamente no Asaas

### 7.4 Como Configurar Split (Vendedor)

1. Acesse **Split e Repasse** no menu
2. Ative o **Split Automático**
3. Cole seu **Wallet ID** do Asaas
   - Para obter: acesse Asaas → Minha Conta → Integrações → Wallet ID
4. Clique em **Salvar Configurações**
5. Aguarde o **Master validar** sua carteira
6. Após validado, seus repasses começarão automaticamente

### 7.5 Como Validar Wallet (Master)

1. Acesse **Equipe de Vendas**
2. Clique em **Editar** no vendedor
3. Na aba **Comissões e Repasse**, verifique o Wallet ID
4. Clique em **Validar Wallet** nas Configurações de Integração
5. O status muda para "Validado"

### 7.6 Como Ver Relatórios (Master)

1. Acesse **Relatórios Gerenciais** no menu
2. Selecione o período, vendedor, status e outros filtros
3. Clique em **Filtrar**
4. Veja os cards de resumo, gráficos e tabelas
5. Clique em **Exportar CSV** para baixar os dados

### 7.7 Como Cancelar uma Venda

1. Acesse a lista de vendas
2. Encontre a venda desejada
3. Clique no ícone de lixeira (🗑️)
4. Confirma a ação no popup
5. O sistema cancela no Asaas e localmente

### 7.8 Como Configurar Integrações (Master)

1. Acesse **Configurações Gerais** no menu
2. Na aba **Integrações**:
   - **Asaas:** Insira API Key, Webhook Token, selecione ambiente
   - **Split:** Ative/desative split global, configure juros e multa
   - **E-mail:** Configure remetentes e suporte
   - **Church:** Configure URL e token do webhook
3. Clique em **Testar Conexão** para verificar se Asaas está funcionando

---

## 8. ARQUITETURA TÉCNICA

### 8.1 Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Banco de Dados:** SQLite
- **Frontend:** Blade Templates + CSS puro + JavaScript vanilla
- **Fila:** Database (jobs em background)
- **Cache:** Database
- **Sessão:** Database
- **Ícones:** Font Awesome 6.5
- **Fonte:** Inter (Google Fonts)

### 8.2 Estrutura de Pastas

```
app/
├── Console/Commands/     # Comandos Artisan
├── Http/
│   ├── Controllers/      # Controladores
│   ├── Middleware/        # Middlewares
│   └── Requests/         # Form Requests
├── Jobs/                 # Jobs da fila
├── Mail/                 # Classes de e-mail
├── Models/               # Modelos Eloquent
├── Providers/            # Service Providers
└── Services/             # Serviços de negócio
database/
├── migrations/           # Migrações do banco
resources/
├── views/                # Templates Blade
│   ├── layouts/          # Layout principal
│   ├── master/           # Telas do Master
│   ├── vendedor/         # Telas do Vendedor
│   ├── emails/           # Templates de e-mail
│   └── auth/             # Telas de autenticação
routes/
├── web.php               # Rotas web
└── api.php               # Rotas de API
public/
├── css/                  # CSS global
└── js/                   # JavaScript global
```

### 8.3 Fluxo de Dados

```
Vendedor cria venda
    ↓
Sistema cria cliente no Asaas (se não existe)
    ↓
Sistema cria cobrança no Asaas (PAYMENT/INSTALLMENT/SUBSCRIPTION)
    ↓
Salva no banco: Venda + Pagamento + Cobranca
    ↓
Se desconto > 5% ou Performance → vai para Aprovação
    ↓
Master aprova → gera cobrança no Asaas
    ↓
Asaas envia webhook PAYMENT_RECEIVED
    ↓
Sistema atualiza status → Gera comissão → Envia e-mails → Provisiona Church
```

---

**FIM DA DOCUMENTAÇÃO**

Sistema Basiléia Vendas — Desenvolvido pela Basiléia Global
