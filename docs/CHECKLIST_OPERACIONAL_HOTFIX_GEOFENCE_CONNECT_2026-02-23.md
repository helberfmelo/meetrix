# Checklist Operacional - Hotfix Geo-Fence + Connect Embutido (2026-02-23)

## Contexto
- Hotfix aplicado para remover defaults fixos de `country_code`/`currency` no registro de onboarding.
- Objetivo da rodada: confirmar resolucao do bloqueio de registro (`Regional mismatch`) sem regressao no Stripe Connect embutido.
- Commit do hotfix: `508d1fd`

## Checklist de validacao
- [x] Registro de novo usuario no onboarding sem erro de Geo-Fence.
- [x] Redirect de onboarding inicial (`scheduling_with_payments`) para `/dashboard/connect/onboarding`.
- [x] Gate de dominio para `scheduling_only` mantido.
- [x] Gate de rollout de pagamentos mantido.
- [x] Endpoints Connect operacionais:
  - `GET /api/payments/connect/status`
  - `POST /api/payments/connect/embedded/session`
- [x] Testes automatizados do ciclo executados e verdes.
- [x] Build frontend executado e verde.
- [x] Deploy em producao finalizado com sucesso.
- [x] Rotas criticas em producao retornando `200`.
- [x] Logs operacionais sem `Fatal error` e sem `headers already sent`.

## Evidencias
- Relatorio detalhado de revalidacao:
  - `docs/REVALIDACAO_HOTFIX_GEOFENCE_CONNECT_2026-02-23.md`
- Run de deploy do hotfix:
  - `https://github.com/helberfmelo/meetrix/actions/runs/22284343024` (`success`)
- Testes executados no ciclo:
  - `php artisan test tests/Feature/AuthControllerGeoFenceTest.php tests/Feature/PaymentConnectControllerTest.php tests/Feature/AccountModeWorkflowTest.php`
  - `npm run build`

## Resultado
- Status operacional do hotfix: **GO**
- Status Connect embutido apos revalidacao: **GO**
