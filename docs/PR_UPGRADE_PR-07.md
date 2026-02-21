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
- [ ] Logs limpos em producao
- [x] Pronto para deploy final do upgrade

## Deploy e validacao em producao
- [ ] GitHub Actions `deploy.yml` verde
- [ ] Validacao em producao concluida
- [ ] Polling de workflow a cada 15s realizado

Validado em producao:
- [ ] Home
- [ ] Login
- [ ] Onboarding
- [ ] Checkout
- [ ] Dashboard
- [ ] Pagina publica `/p/{slug}`
- [ ] Fluxo com/sem cobranca
- [ ] Validacao via navegador desktop
- [ ] Validacao via navegador mobile simulado

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
- [ ] Aprovado para merge/deploy
