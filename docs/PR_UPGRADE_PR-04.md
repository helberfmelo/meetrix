## Objetivo do PR
Implementar `PR-04` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-04`
- Etapa: Stripe Connect + webhooks + split
- Itens implementados:
  - Feature flag operacional de pagamentos (`payments_enabled`) com rollout controlado por conta.
  - Camada Stripe Connect (Account Links + status de conectividade) para contas `scheduling_with_payments`.
  - Webhooks Stripe com assinatura e idempotencia para eventos obrigatorios:
    - `payment_intent.succeeded`
    - `payment_intent.payment_failed`
    - `invoice.payment_succeeded`
    - `invoice.payment_failed`
    - `charge.refunded`
  - Split/take-rate no checkout de booking com metadados financeiros e trilha em tabelas novas (`payment_intents`, `payments`, `transfers`, `subscriptions`, `webhook_events`).
  - Fluxos de pagamento no booking com suporte tecnico para:
    - antecipado (`full`)
    - sinal (`deposit`)
    - pre-autorizacao (`preauth`)
  - Garantia explicita de que `scheduling_only` segue sem dependencia de gateway.

## Fora de escopo (neste PR)
- Self-service de assinatura completo e controles financeiros avancados de conta/admin (PR-05).
- KPIs/observabilidade financeira de produto (PR-06).
- Hardening final de release (PR-07).

## Mudancas tecnicas
- Backend:
  - Novo `PaymentConnectController` com endpoints de status e onboarding Connect.
  - `BookingController` atualizado para feature flag, split e modos de cobranca (`full`, `deposit`, `preauth`).
  - `SubscriptionController` atualizado para persistir trilha em `subscriptions` e metadados de billing.
  - `StripeWebhookController` reescrito para assinatura, idempotencia e eventos financeiros obrigatorios.
  - Novos modelos de dominio financeiro:
    - `ConnectedAccount`, `PaymentIntent`, `Payment`, `Transfer`, `Subscription`, `Payout`, `WebhookEvent`.
  - Novos servicos:
    - `App\Services\Payments\PaymentFeature`
    - `App\Services\Payments\StripeConnectService`
- Frontend:
  - Sem alteracao visual obrigatoria neste PR.
- Banco:
  - Nova migration: `webhook_events` para idempotencia e trilha de processamento de eventos.
- Config/Infra:
  - Novo `config/payments.php`.
  - Atualizacao de `config/services.php` para credenciais Stripe.
  - Atualizacao de `.env.example` com variaveis Stripe/flag de rollout.

## Arquivos principais alterados
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/StripeWebhookController.php`
- `app/Http/Controllers/SubscriptionController.php`
- `app/Http/Controllers/PaymentConnectController.php`
- `app/Services/Payments/PaymentFeature.php`
- `app/Services/Payments/StripeConnectService.php`
- `config/payments.php`
- `database/migrations/2026_02_21_060100_create_webhook_events_table.php`
- `routes/api.php`
- `tests/Feature/StripeWebhookFinancialTest.php`
- `tests/Feature/BookingControllerTest.php`
- `docs/PR_UPGRADE_PR-04.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (logs de comando)

Comandos executados:
```bash
php artisan test tests/Feature/StripeWebhookFinancialTest.php tests/Feature/WebhookTest.php tests/Feature/BookingControllerTest.php tests/Feature/CouponTest.php
php artisan test
npm run build
```

Resultado:
- `pass` - simulacoes de sucesso/falha/refund e idempotencia de webhook aprovadas, suite completa verde (`34 passed`) e build frontend sem erro.

## Gate obrigatorio deste PR
- [x] Simulacoes sandbox de sucesso/falha/refund cobertas por testes automatizados
- [x] Validacao de idempotencia de webhook coberta por teste automatizado
- [x] Confirmado que `scheduling_only` permanece sem dependencia de gateway
- [x] Sem erro critico local

## Deploy e validacao em producao
- [x] GitHub Actions `deploy.yml` verde (`run 22260660732`)
- [x] Validacao em producao concluida
- [x] Polling de workflow a cada 15s realizado

Validado em producao:
- [x] Home
- [x] Login
- [x] Onboarding
- [x] Checkout
- [x] Dashboard
- [x] Pagina publica `/p/{slug}`
- [x] Fluxo com/sem cobranca (quando aplicavel)

Status:
- Implementacao, deploy e smoke funcional concluidos no ciclo do PR-04.
- Producao validada sem regressao nas rotas obrigatorias.

## Rollback
- Tag de referencia: `snapshot/pre-upgrade-2026-02-21`
- Plano de rollback para este PR:
  - Desligar `PAYMENTS_ENABLED` imediatamente (efeito rapido).
  - Esvaziar `PAYMENTS_ROLLOUT_USER_IDS` para remover lote controlado.
  - Reverter PR no GitHub se necessario.
  - Restaurar snapshot/tag em incidente severo.
- [x] Rollback tecnico planejado (feature flag + revert)

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [x] Aprovado para merge/deploy em producao

## Evidencias finais de deploy e smoke
- GitHub Actions:
  - `https://github.com/helberfmelo/meetrix/actions/runs/22260660732` (`success`)
  - Polling manual em intervalos de 15s ate conclusao.
- Smoke HTTP em producao (status `200`):
  - `/`
  - `/login`
  - `/onboarding`
  - `/checkout`
  - `/dashboard`
  - `/p/helber`
