## Objetivo do PR
Implementar `PR-05` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-05`
- Etapa: Conta + Master Admin (lacunas P0 benchmark)
- Itens implementados:
  - Self-service de assinatura na area de conta (`upgrade/downgrade/cancelamento`) com trilha em `subscriptions` e `billing_transactions`.
  - Acoes financeiras avancadas no Master Admin:
    - reprocessar pagamento (`retry_payment`)
    - ajuste manual com motivo (`manual_adjustment`)
    - exportacao CSV de pagamentos.
  - UX de erros com codigos funcionais (`error_code`) nos fluxos novos de conta/admin.

## Fora de escopo (neste PR)
- KPIs e observabilidade financeira (PR-06).
- Hardening final e fechamento de release (PR-07).

## Mudancas tecnicas
- Backend:
  - `AccountController` expandido com `changeSubscription` e `cancelSubscription`.
  - `SaasAdminController` expandido com `paymentAction` e `exportPaymentsCsv`.
  - Padronizacao de erro por codigo funcional via `App\Support\ApiError`.
  - `summary` de conta agora inclui `active_subscription` e `subscription_options`.
- Frontend:
  - `AccountView` com modulo de self-service de assinatura (plano/ciclo/cancelamento) e exibicao de `error_code`.
  - `MasterAdminView` com:
    - reprocessamento de pagamento por linha,
    - formulario de ajuste manual,
    - exportacao CSV,
    - exibicao de erro com `error_code`.
- Banco:
  - Sem nova migration neste PR (reuso de estrutura de PR-01/PR-04).
- Config/Infra:
  - Sem alteracao de variaveis sensiveis.

## Arquivos principais alterados
- `app/Http/Controllers/AccountController.php`
- `app/Http/Controllers/SuperAdmin/SaasAdminController.php`
- `app/Support/ApiError.php`
- `routes/api.php`
- `resources/js/Views/AccountView.vue`
- `resources/js/Views/MasterAdminView.vue`
- `tests/Feature/AccountControllerTest.php`
- `tests/Feature/SuperAdminSaasTest.php`
- `docs/PR_UPGRADE_PR-05.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (logs de comando)

Comandos executados:
```bash
php artisan test tests/Feature/AccountControllerTest.php tests/Feature/SuperAdminSaasTest.php
php artisan test
npm run build
```

Resultado:
- `pass` - suite verde (`40 passed`), cobrindo permissao/auditoria/account/admin; build frontend concluido sem erro.

## Gate obrigatorio deste PR
- [x] Testes de permissao e auditoria
- [x] Validacao de historico de billing
- [x] Sem erro critico local
- [ ] E2E completo de conta/admin em producao (pendente gate de deploy)

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
- [ ] Fluxo com/sem cobranca (quando aplicavel)

Status:
- Implementacao e validacao local concluidas.
- A etapa de deploy/producao permanece pendente de push em `main` e gate operacional completo.

## Rollback
- Tag de referencia: `snapshot/pre-upgrade-2026-02-21`
- Plano de rollback para este PR:
  - Reverter PR no GitHub caso falha de gate.
  - Ocultar acoes novas no frontend de conta/admin (rollback de codigo) e retornar comportamento anterior.
  - Em incidente severo, restaurar snapshot/tag e redeploy.
- [x] Rollback tecnico planejado

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [ ] Aprovado para merge/deploy em producao (pendente gate operacional)
