## Objetivo do PR
Implementar `PR-06` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-06`
- Etapa: KPIs, analytics e observabilidade financeira
- Itens implementados:
  - Instrumentacao de KPIs financeiros no backend:
    - GMV por pais
    - receita por moeda
    - receita convertida para BRL
    - `%` de atendimentos pagos
    - `%` de upgrade de modo
  - Exposicao dos novos indicadores no endpoint de visao global do Master Admin (`/api/super-admin/overview`).
  - Padronizacao de logs de erro para fluxos financeiros de conta e operacao admin com payload estruturado.
  - Atualizacao do dashboard interno Master Admin com cards e breakdowns financeiros por moeda/pais.

## Fora de escopo (neste PR)
- Hardening final e fechamento da release de upgrade (PR-07).
- Expansao E2E visual e roadmap de gaps estrategicos fora do plano de upgrade.

## Mudancas tecnicas
- Backend:
  - Novo servico `FinancialKpiService` para consolidacao de KPIs financeiros do PR-06.
  - `SaasAdminController@overview` expandido com bloco `financial` e novos KPIs agregados.
  - `AccountController` e `SaasAdminController` com logs padronizados via `FinancialObservability`.
- Frontend:
  - `MasterAdminView` atualizado para mostrar:
    - receita por moeda (com normalizacao BRL),
    - GMV por pais,
    - `%` atendimentos pagos,
    - `%` upgrade de modo.
- Banco:
  - Sem novas migrations neste PR.
- Config/Infra:
  - Novo arquivo `config/analytics.php` com taxas de conversao para BRL.
  - Novas variaveis em `.env.example` para parametrizar FX.

## Arquivos principais alterados
- `.env.example`
- `config/analytics.php`
- `app/Services/FinancialKpiService.php`
- `app/Support/FinancialObservability.php`
- `app/Http/Controllers/SuperAdmin/SaasAdminController.php`
- `app/Http/Controllers/AccountController.php`
- `resources/js/Views/MasterAdminView.vue`
- `tests/Feature/SuperAdminSaasTest.php`
- `docs/PR_UPGRADE_PR-06.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (logs de comando)

Comandos executados:
```bash
php -l app/Services/FinancialKpiService.php
php -l app/Support/FinancialObservability.php
php -l app/Http/Controllers/SuperAdmin/SaasAdminController.php
php -l app/Http/Controllers/AccountController.php
php artisan test tests/Feature/SuperAdminSaasTest.php tests/Feature/AccountControllerTest.php
php artisan test
npm run build
```

Resultado:
- `pass` - suite completa verde (`41 passed`) e build frontend concluido.

## Gate obrigatorio deste PR
- [x] Consistencia eventos vs banco (validada por teste de overview financeiro com dados controlados)
- [x] Painel com dados controlados (Master Admin + teste automatizado de KPI PR-06)
- [x] Sem erro critico local nos fluxos financeiros alterados
- [x] Sem erro critico novo em logs de producao

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
- [x] Fluxo com/sem cobranca (quando aplicavel)

Status:
- Implementacao, deploy e validacao concluida.
- Gate operacional de producao aprovado para liberacao do PR-07.

## Rollback
- Tag de referencia: `snapshot/pre-upgrade-2026-02-21`
- Plano de rollback para este PR:
  - Desligar consumo dos novos KPIs no frontend (rollback de codigo).
  - Reverter PR no GitHub se houver falha de gate.
  - Em incidente severo, restaurar `snapshot/pre-upgrade-2026-02-21` e redeploy.
- [x] Rollback tecnico planejado

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [x] Aprovado para merge/deploy em producao

## Evidencias finais de deploy e smoke
- GitHub Actions:
  - run: `22262303752` (`success`)
  - job: `🎉 Deploy` (`success`)
  - polling manual em intervalos de 15s ate conclusao.
- Smoke HTTP em producao (`200`):
  - `/`
  - `/login`
  - `/onboarding`
  - `/checkout`
  - `/dashboard`
  - `/p/helber`
- APIs autenticadas validadas em producao (`200`):
  - `/api/account/summary`
  - `/api/account/billing-history?per_page=5`
  - `/api/super-admin/overview`
  - `/api/super-admin/payments?per_page=5`
  - `/api/super-admin/customers?per_page=5`
  - `/api/super-admin/payments/export?status=all&source=all`
- Validacao funcional dos novos KPIs:
  - `financial` presente no payload de `/api/super-admin/overview`
  - KPIs presentes: `revenue_converted_brl`, `paid_appointments_rate`, `mode_upgrade_rate`
- Logs operacionais:
  - `https://opentshost.com/meetrix/read_logs.php`
  - sem `Fatal error`
  - sem `headers already sent`
