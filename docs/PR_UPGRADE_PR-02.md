## Objetivo do PR
Implementar `PR-02` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-02`
- Etapa: Onboarding por modo + upgrade progressivo
- Itens implementados:
  - Persistencia de `account_mode` no fechamento do onboarding (`/api/onboarding/complete`)
  - Fluxo de upgrade `scheduling_only -> scheduling_with_payments` na area de conta (`PATCH /api/account/mode`)
  - Bloqueio tecnico de cobranca em `scheduling_only` no editor (frontend + backend)

## Fora de escopo (neste PR)
- Motor de geo-pricing exibindo catalogo dinâmico por regiao (PR-03)
- Integracao Stripe Connect/webhooks (PR-04)

## Mudancas tecnicas
- Backend:
  - `OnboardingController` passa a exigir e persistir `account_mode`
  - `AccountController` recebe endpoint de troca de modo com fee regional
  - `AppointmentTypeController` força preco `0` para contas `scheduling_only`
  - Nova rota autenticada `PATCH /api/account/mode`
- Frontend:
  - `OnboardingWizard` inicializa modo por query/user, persiste no backend e redireciona:
    - `scheduling_only` -> `/dashboard`
    - `scheduling_with_payments` -> `/checkout`
  - `AccountView` ganha acao de upgrade para modo com cobranca
  - `PageEditor`/`EditorSectionTypes` ocultam efetivamente configuracao de preco em `scheduling_only`
  - `Checkout` bloqueia acesso para contas sem modo de cobranca
- Banco:
  - Sem novas migrations nesta etapa
- Config/Infra:
  - Sem alteracao de pipeline

## Arquivos principais alterados
- `app/Http/Controllers/OnboardingController.php`
- `app/Http/Controllers/AccountController.php`
- `app/Http/Controllers/AppointmentTypeController.php`
- `routes/api.php`
- `resources/js/Views/OnboardingWizard.vue`
- `resources/js/Views/AccountView.vue`
- `resources/js/Views/PageEditor.vue`
- `resources/js/Components/Editor/EditorSectionTypes.vue`
- `resources/js/Views/Checkout.vue`
- `tests/Feature/AccountModeWorkflowTest.php`
- `docs/PR_UPGRADE_PR-02.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (prints/logs)

Comandos executados:
```bash
php artisan test tests/Feature/AccountModeWorkflowTest.php
php artisan test
npm run build
```

Resultado:
- `pass` - onboarding por modo validado, upgrade progressivo sem perda de dados validado por teste, suite completa backend (`23 passed`) e build frontend verde.

## Gate obrigatorio deste PR
- [x] Gate do PR validado conforme plano
- [x] Sem erro critico em logs
- [x] Pronto para deploy controlado

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

## Rollback
- Tag de referencia: `snapshot/pre-upgrade-2026-02-21`
- Plano de rollback para este PR:
  - Reverter PR no GitHub
  - Desativar transicao via endpoint `/api/account/mode` em hotfix se necessario
- [x] Rollback testado (procedimento validado por snapshot + branch de backup)

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [ ] Aprovado para merge
