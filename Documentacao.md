# Basiléia Vendas - Documentação do Sistema

## 1. Descrição Geral
O sistema tem como objetivo organizar, automatizar e controlar todo o processo comercial realizado pelos vendedores, desde o cadastro de uma nova venda até a confirmação do pagamento, ativação do cliente, acompanhamento de recorrência e gestão dos indicadores comerciais.
A proposta é centralizar em um único sistema todas as informações de vendas, pagamentos, renovação de clientes, comissões e acompanhamento da equipe comercial, reduzindo processos manuais e melhorando o controle da operação.

### Problema que o sistema resolve
Atualmente, o processo de venda, validação de pagamento, acompanhamento de clientes ativos, renovação e churn ocorre de forma descentralizada e manual, gerando problemas como:
- Falta de controle centralizado sobre as vendas.
- Dificuldade para validar pagamentos.
- Retrabalho no cadastro de clientes.
- Baixa visibilidade do desempenho dos vendedores.
- Dificuldade para acompanhar recorrência mensal.
- Dificuldade para identificar churn.
- Atraso na entrega de materiais ao cliente.
- Inconsistência entre sistemas.
O sistema resolve esses problemas automatizando o fluxo comercial e financeiro.

### Quem utiliza o sistema
**Vendedor**
- Registra vendas.
- Gera cobranças.
- Acompanha status.
- Visualiza comissão.

**Master (Administrador)**
- Gerencia vendedores.
- Acompanha vendas e resultados.
- Define metas.
- Monitora clientes ativos.
- Acompanha recorrência e churn.

**Asaas (Sistema Terceiro)**
- Processa pagamentos.
- Valida cobranças.
- Controla recorrência.
- Fornece dados financeiros.

### Resultado final do sistema
O sistema deve garantir que:
- Vendas sejam registradas corretamente.
- Cobranças sejam geradas automaticamente.
- Pagamentos sejam validados automaticamente.
- Comissões sejam calculadas automaticamente.
- O Master tenha visão completa da operação.
- O cliente receba todos os materiais após o pagamento.
- Dados sejam integrados com outro sistema.

**Resumo:** Criar um sistema que conecte vendas, pagamentos e gestão em um fluxo automatizado, garantindo controle, escalabilidade e redução de erros.

---

## 2. Perfis de Usuário

### Perfil: Vendedor
**Pode:**
- Fazer login.
- Acessar tela de vendas.
- Criar nova venda.
- Gerar cobrança.
- Compartilhar cobrança.
- Visualizar suas vendas.
- Ver status de pagamento.
- Ver comissão.

**Não pode:**
- Ver vendas de outros vendedores.
- Cadastrar vendedores.
- Acessar relatórios gerais.

### Perfil: Master
**Pode:**
- Fazer login.
- Cadastrar vendedores.
- Editar/Inativar vendedores.
- Visualizar todas as vendas.
- Revisar e excluir vendas.
- Definir metas.
- Visualizar relatórios.
- Acompanhar churn e recorrência.
- Visualizar desempenho da equipe.

---

## 3. Fluxo do Sistema

### Fluxo do Vendedor
1. Vendedor faz login.
2. Acessa menu lateral → Vendas.
3. Clica em "+ Nova Venda".
4. Preenche formulário (Campos e Validações):
    - Nome da igreja *(Validar duplicidade e cobrança em aberto)*.
    - Nome do pastor *(Validar cadastro existente)*.
    - Localidade.
    - Moeda.
    - Quantidade de membros *(Sistema sugere planos automaticamente)*.
    - CNPJ/CPF.
    - WhatsApp.
    - Forma de pagamento.
    - Tipo de negociação (mensal/anual).
    - Percentual de desconto.
5. Sistema valida os dados.
6. Sistema cria uma Venda (Fica como "Aguardando pagamento").
7. Sistema gera cobrança via Asaas e salva ID da cobrança.
8. Sistema retorna link de pagamento, boleto ou linha digitável.

### Fluxo do Asaas
1. Recebe a cobrança e processa o pagamento.
2. Retorna eventos (via Webhook): *Pago, Pendente, Vencido, Cancelado, Recorrência ativa/inativa*.

### Fluxo Pós Pagamento
1. Asaas confirma o pagamento.
2. Sistema recebe webhook, valida o evento e atualiza a venda para "Pago".
3. Calcula a comissão do Vendedor e registrar o pagamento.
4. **Para o vendedor:** Recebe e-mail de confirmação e link da venda.
5. **Para o cliente:** Recebe automaticamente link de cadastro (pré-preenchido), link de videoaulas, termos em PDF e link da nota fiscal (via Asaas).

---

## 4. Nota Fiscal
A nota fiscal será gerada exclusivamente pelo Asaas. O sistema não será responsável pela emissão, apenas por:
- Armazenar o link da nota fiscal.
- Disponibilizar ao cliente.
- Enviar junto com os demais materiais.

**Regras:**
- Se nota fiscal disponível → enviar automaticamente.
- Se não disponível → permitir envio posterior.

**Campos necessários na base:**
- `nota_fiscal_status`: pendente, emitida, erro.
- `nota_fiscal_url`: URL do Asaas.

---

## 5. Regras de Negócio
- Venda só é válida quando o pagamento é confirmado.
- Comissão só é gerada após o pagamento.
- Vendedor só vê suas próprias vendas; Master vê tudo.
- Não permitir duplicidade de cadastro de igreja se possuir cobrança ativa.
- O Plano sugerido depende diretamente da quantidade de membros (vide anexo visual).
- O desconto concedido na venda deve ser validado (limites).
- O status de Cobrança vencida automaticamente altera o status da venda correspondente.
- Um estorno no Asaas (chargeback) automaticamente remove a comissão gerada.
- Todo cliente deve ter um CPF ou CNPJ logicamente válido.

---

## 6. Telas do Sistema
- **Login:** Email, Senha. (Design Roxo e Branco).
- **Dashboard Vendedor:** Vendas do mês, Comissões, Pagamentos.
- **Nova Venda:** Formulário de criação, Botões (gerar cobrança, salvar, cancelar). Grid de planos dinâmicos exibidos em cards (como layout Base/Start/Basic/Core).
- **Dashboard Master:** Total vendido, Quantidade de Vendas, Comissões a pagar, Ranking de vendedores, Indicadores de Churn e Recorrência.

---

## 7. Integrações

**Asaas (Gateway de Pagamento)**
- Funções: Criar cobrança, Validar pagamento, Fornecer recorrência, Fornecer nota fiscal.
- Dados enviados: Cliente, Valor, Vencimento, Descrição.
- Dados recebidos: ID Cobrança, Status, Link, Boleto, Nota Fiscal.

---

## 8. Banco de Dados (Resumo de Tabelas)
- **Usuários:** id, nome, email, senha, perfil.
- **Vendedores:** id, usuario_id, comissao.
- **Clientes:** id, nome, documento, contato.
- **Vendas:** id, cliente_id, vendedor_id, valor, comissao_gerada, status.
- **Cobranças:** id, venda_id, asaas_id, status, link.
- **Integrações:** venda_id, status, retorno_asaas.
- **Logs:** evento, data, usuario_id.

---

## 9. Eventos Automáticos
- **Venda Criada:** Dispara criação de cobrança no Asaas, salvar dados em banco.
- **Pagamento Confirmado:** Atualiza venda para paga, calcula/salva comissão do vendedor, envia notificações por email (Vendedor e Cliente), despacha dados para "outro sistema" de cadastro final.
- **Nota Fiscal Disponível:** Atualiza status (`nota_fiscal_status`), despacha URL para o cliente (e-mail/zap).

---

## 10. Relatórios
- **Master:** Vendas por vendedor, Churn, Recorrência, Resumo de Comissões.
- **Vendedor:** Volume de vendas próprias, Total de Comissão.

---

## 11. Fases do Projeto
- **Fase 1 (Atual):** Configuração em Laravel, Login, Vendedores, Clientes, Vendas (Fluxo central).
- **Fase 2...:** A definir no futuro.
