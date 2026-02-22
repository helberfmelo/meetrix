# Backlog Futuro: Checkout, Split, Taxas e Comissoes

## Objetivo
Implementar uma gestao profissional e simples para checkout com split, taxas por moeda/forma de pagamento e comissao da Meetrix por plano, garantindo transparencia operacional no admin master e experiencia clara no site/checkout.

## Epic 1: Validacao de Checkout e Split

### Item 1.1 (P0) - Plano de validacao funcional
- Definir cenarios de teste para checkout de assinatura e checkout de agendamento pago.
- Cobrir fluxo completo: criacao de pagamento, split, confirmacao, webhook e conciliacao.
- Registrar matriz de testes por moeda (BRL, USD, EUR) e por forma de pagamento.

### Item 1.2 (P0) - Testes com ambiente de homologacao
- Executar testes com chaves de teste e metodos de pagamento de teste.
- Validar split em todos os cenarios planejados.
- Registrar evidencias por cenario (request, response, valores de split, status final).

## Epic 2: Admin Master de Taxas e Comissoes por Plano

### Item 2.1 (P0) - Inputs por plano, moeda e forma de pagamento
- Na tela de planos do admin master, adicionar configuracao de comissao da Meetrix por:
- plano
- moeda
- forma de pagamento

### Item 2.2 (P0) - Carga automatica de taxas do gateway por moeda/forma
- Exibir taxa operacional aplicavel para cada moeda e forma de pagamento.
- Se houver diferenca por moeda, refletir na interface automaticamente.

### Item 2.3 (P0) - Transparencia de composicao de taxa
- Exibir lado a lado:
- percentual da Meetrix
- taxa operacional
- total (Meetrix + taxa operacional)

### Item 2.4 (P1) - Ajuste de nomenclatura
- Remover a marca do provedor da interface administrativa e do checkout.
- Usar termos neutros: "Taxa operacional", "Taxa da plataforma", "Total de taxas".

## Epic 3: UX de Formas de Pagamento no Site e Checkout

### Item 3.1 (P1) - Icones de bandeiras de cartao
- Exibir bandeiras aceitas no site e no checkout.
- Usar icones monocromaticos.

### Item 3.2 (P0) - BRL com suporte visual e funcional a PIX
- Para moeda BRL, exibir icones e opcao de PIX no checkout.
- Ativar a opcao somente quando habilitada para a conta/tenant.

## Epic 4: Compatibilidade de PIX com Split

### Item 4.1 (P0) - Validacao de capacidade de split no PIX
- Confirmar compatibilidade tecnica de split para PIX.
- Documentar limitacoes e pre-requisitos por tipo de conta.

### Item 4.2 (P0) - Testes de ponta a ponta para agendas com cobranca
- Validar checkout do Meetrix e checkout em paginas de agendamento pagas.
- Cobrir cenarios BRL + PIX e BRL + cartao com split.

## Epic 5: Go-Live e Producao

### Item 5.1 (P0) - Cutover de homologacao para producao
- Checklist para troca de chaves e configuracoes de producao.
- Validacao de webhooks, credenciais, contas conectadas e regras de split.

### Item 5.2 (P0) - Revisao final de seguranca e operacao
- Conferir logs, alertas, idempotencia e tratamento de falhas.
- Validar reconciliacao financeira antes da liberacao geral.

## Criterios de aceite globais
- Configuracoes de planos, comissoes, taxas e formas de pagamento centralizadas no admin master.
- Checkout exibindo metodos corretos por moeda/tenant.
- Calculo de taxas transparente e auditavel.
- Fluxos de teste homologados com evidencias.
- Processo de entrada em producao documentado e reproduzivel.
