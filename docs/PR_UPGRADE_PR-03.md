## Objetivo do PR
Implementar `PR-03` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-03`
- Etapa: Geo-pricing engine + catalogo de planos
- Itens implementados:
  - Servico de resolucao de regiao por pais/locale com fallback seguro
  - Catalogo publico de precificacao por modo (`scheduling_only` e `scheduling_with_payments`)
  - Home consumindo catalogo dinamico para moeda/preco/fee por regiao

## Fora de escopo (neste PR)
- Integracao Stripe Connect e webhooks (PR-04)
- Acoes financeiras avancadas de conta/admin (PR-05)

## Mudancas tecnicas
- Backend:
  - Novo `GeoPricingCatalogService` com mapeamento BR/USD/EUR
  - Novo `PricingCatalogController` com endpoint publico `GET /api/pricing/catalog`
- Frontend:
  - `Home.vue` troca tabela hardcoded por fetch dinamico de catalogo
  - Fallback local mantido para resiliencia
- Banco:
  - Reuso da tabela `geo_pricing` criada no PR-01
- Config/Infra:
  - Sem alteracao de pipeline

## Arquivos principais alterados
- `app/Services/GeoPricingCatalogService.php`
- `app/Http/Controllers/PricingCatalogController.php`
- `routes/api.php`
- `resources/js/Views/Home.vue`
- `tests/Unit/GeoPricingCatalogServiceTest.php`
- `tests/Feature/PricingCatalogTest.php`
- `docs/PR_UPGRADE_PR-03.md`

## Evidencias de teste
- [x] Testes unitarios executados
- [x] Testes de integracao executados
- [x] Smoke test local executado
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (prints/logs)

Comandos executados:
```bash
php artisan test tests/Unit/GeoPricingCatalogServiceTest.php
php artisan test tests/Feature/PricingCatalogTest.php
php artisan test
npm run build
```

Resultado:
- `pass` - mapeamento regiao/moeda/fee validado em unitario, catalogo por regiao validado em integracao e build frontend verde.

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
  - Fallback local de pricing permanece ativo no frontend
- [x] Rollback testado (procedimento validado por snapshot + branch de backup)

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [ ] Aprovado para merge
