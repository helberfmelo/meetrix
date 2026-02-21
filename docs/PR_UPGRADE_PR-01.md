## Objetivo do PR
Implementar `PR-01` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-01`
- Etapa: Fundacao de dados (modos, regiao, moeda, fees)
- Itens implementados:
  - Campos de dominio adicionados em `users` e `tenants`: `account_mode`, `region`, `currency`, `platform_fee_percent`
  - Novas tabelas financeiras criadas: `connected_accounts`, `payments`, `payment_intents`, `transfers`, `subscriptions`, `payouts`, `geo_pricing`
  - Seed de `geo_pricing` populado com matriz BR/USD/EUR para os modos `scheduling_only` e `scheduling_with_payments`

## Fora de escopo (neste PR)
- Integracao Stripe Connect/webhooks
- Mudancas de UX de onboarding por modo (PR-02)

## Mudancas tecnicas
- Backend:
  - Registro de usuario passa a persistir `account_mode`, `region`, `currency` e `platform_fee_percent`
  - `AccountController` expõe os novos campos de conta no summary
- Frontend:
  - Sem alteracao funcional neste PR
- Banco:
  - Migration de fundacao para campos de conta + backfill seguro de contas existentes
  - Migration de tabelas financeiras e tabela de geo-pricing
  - Seeder dedicado para matriz de precificacao regional
- Config/Infra:
  - Sem alteracao de pipeline; release controlada por deploy existente

## Arquivos principais alterados
- `database/migrations/2026_02_21_050900_add_upgrade_account_fields_to_users_and_tenants.php`
- `database/migrations/2026_02_21_051000_create_upgrade_financial_tables.php`
- `database/seeders/GeoPricingSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/AccountController.php`
- `app/Models/User.php`
- `app/Models/Tenant.php`
- `docs/PR_UPGRADE_PR-01.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (prints/logs)

Comandos executados:
```bash
php artisan migrate --force
php artisan db:seed --force
php -d memory_limit=512M artisan tinker --execute="dump(DB::table('geo_pricing')->select('region_code','account_mode','currency','platform_fee_percent')->orderBy('region_code')->orderBy('account_mode')->get()->toArray());"
php artisan test tests/Feature/BackendIntegrationTest.php --filter=test_public_can_book_free_appointment
php artisan test
npm run build
```

Resultado:
- `pass` - migrations aplicadas, seed validado, booking sem pagamento aprovado, suite backend (`19 passed`) e build frontend verde.

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
  - Executar rollback de migration se necessario
- [x] Rollback testado (procedimento validado por snapshot + branch de backup)

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [ ] Aprovado para merge
