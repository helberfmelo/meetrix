## Objetivo do PR
Implementar `PR-07` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-07`
- Etapa: Hardening final e fechamento de release
- Itens implementados:
  - Hardening de performance e resiliencia para KPIs financeiros (cache controlado, limite de breakdown e fallback seguro).
  - Documentacao operacional e de incidentes do fechamento do upgrade.
  - Checklist final de QA para fluxos com e sem cobranca, incluindo validacao via navegador desktop/mobile.

## Fora de escopo (neste PR)
- Novas funcionalidades de produto fora do plano de upgrade.
- Roadmap de gaps estrategicos (YCBM, E2E visual expandido, pente fino de idiomas).

## Mudancas tecnicas
- Backend:
  - `FinancialKpiService` com cache de snapshot, limite de pais e modo degradado resiliente.
  - `SaasAdminController@overview` com suporte a refresh forçado de KPI (`refresh_kpis`).
- Frontend:
  - Sem nova feature visual; reuso do painel financeiro entregue no PR-06.
- Banco:
  - Sem migrations.
- Config/Infra:
  - Novas chaves `ANALYTICS_KPI_CACHE_TTL_SECONDS` e `ANALYTICS_KPI_COUNTRY_LIMIT`.

## Arquivos principais alterados
- `.env.example`
- `config/analytics.php`
- `app/Services/FinancialKpiService.php`
- `app/Http/Controllers/SuperAdmin/SaasAdminController.php`
- `tests/Unit/FinancialKpiServiceTest.php`
- `docs/UPGRADE_RUNBOOK_PR-07.md`
- `docs/PR_UPGRADE_PR-06.md`
- `docs/PR_UPGRADE_PR-07.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (prints/logs)

Comandos executados:
```bash
php -l app/Services/FinancialKpiService.php
php -l app/Http/Controllers/SuperAdmin/SaasAdminController.php
php -l tests/Unit/FinancialKpiServiceTest.php
php -l config/analytics.php
php artisan test tests/Unit/FinancialKpiServiceTest.php tests/Feature/SuperAdminSaasTest.php tests/Feature/AccountControllerTest.php
php artisan test
npm run build
```

Resultado:
- `pass` - suite completa verde (`43 passed`) e build frontend concluido.

## Gate obrigatorio deste PR
- [x] Build, testes e smoke completos aprovados
- [x] Logs limpos em producao
- [x] Pronto para deploy final do upgrade

## Deploy e validacao em producao
- [x] GitHub Actions `deploy.yml` verde
- [x] Validacao em producao concluida
- [x] Polling de workflow a cada 15s realizado

Validado em producao:
- [x] Home
- [x] Login
- [x] Onboarding
- [x] Checkout
- [x] Dashboard
- [x] Pagina publica `/p/{slug}`
- [x] Fluxo com/sem cobranca
- [x] Validacao via navegador desktop
- [x] Validacao via navegador mobile simulado

## Rollback
- Tag de referencia: `snapshot/pre-upgrade-2026-02-21`
- Plano de rollback para este PR:
  - Reverter PR no GitHub.
  - Em incidente severo, restaurar snapshot e redeploy do estado estavel.
  - Manter operacao principal com hardening desligado se necessario.
- [x] Rollback tecnico planejado

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [x] Aprovado para merge/deploy

## Evidencias finais de deploy e smoke
- GitHub Actions:
  - run: `22262815572` (`success`)
  - job: `🎉 Deploy` (`success`)
  - polling manual a cada 15s ate conclusao.
- Smoke HTTP em producao (`200`):
  - `/`
  - `/login`
  - `/onboarding`
  - `/checkout`
  - `/dashboard`
  - `/p/helber`
- APIs autenticadas em producao (`200`):
  - `/api/account/summary`
  - `/api/account/billing-history?per_page=5`
  - `/api/super-admin/overview?refresh_kpis=1`
  - `/api/super-admin/payments?per_page=5`
  - `/api/super-admin/customers?per_page=5`
  - `/api/super-admin/payments/export?status=all&source=all`
- Validacao de fluxo com/sem cobranca:
  - `/api/pricing/catalog?country=BR&locale=pt-BR` com `plans.scheduling_only` e `plans.scheduling_with_payments` presentes.
- Validacao de navegador (Playwright):
  - desktop: login admin + navegacao `/`, `/onboarding`, `/checkout`, `/dashboard`, `/p/helber` com `200`.
  - mobile simulado (390x844): login admin + navegacao `/`, `/onboarding`, `/checkout`, `/dashboard`, `/p/helber` com `200`.
- Logs operacionais:
  - `https://opentshost.com/meetrix/read_logs.php`
  - sem `Fatal error`
  - sem `headers already sent`
- KPI financeiro hardening:
  - `financial.degraded_mode=false` no overview apos deploy.
