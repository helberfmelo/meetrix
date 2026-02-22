# PROMPT DE CONTINUAÇÃO (HANDOFF)

**OBJETIVO ATUAL**: continuar a execução do plano de **Upgrade do Meetrix** a partir do **PR-06**, mantendo disciplina operacional por PR, com gates de deploy e produção.

## Instruções Obrigatórias

1. Leia primeiro:
   - `docs/AI_OPERATIONS_GUIDE.md`
   - `docs/INFRASTRUCTURE.md`
   - `docs/Documentação YouCanBookMe (YCBM).md`
2. Comunicação obrigatória em PT-BR.
3. Em testes E2E, limpar sempre os campos de input antes de digitar.
4. Em caso de deploy, seguir polling de 15s no GitHub Actions e validar em produção.

## Contexto Consolidado

- Fase de recuperação de produção: concluída.
- Plano de Rebranding (Opção B): concluído de PR-00 a PR-06 em `main`, com validações completas.
- Evidências documentadas:
  - `docs/PR_REBRANDING_PR-01.md`
  - `docs/PR_REBRANDING_PR-02.md`
  - `docs/PR_REBRANDING_PR-03.md`
  - `docs/PR_REBRANDING_PR-04.md`
  - `docs/PR_REBRANDING_PR-05.md`
  - `docs/PR_REBRANDING_PR-06.md`
- Principais entregas do ciclo de rebranding:
  - landing reestruturada com narrativa progressiva e 13 seções;
  - planos e exibição monetária por região na camada de conteúdo;
  - onboarding com seleção de modo operacional e microcopy alinhada;
  - SEO técnico atualizado (title/description/canonical/og:url);
  - instrumentação de funil (`cta_main_click`, `path_selected`, `signup_start`, `onboarding_completed`);
  - ajuste de i18n para headline de pricing em todos os idiomas.
- Produção estável e validada:
  - `/`, `/login`, `/onboarding`, `/dashboard`, `/p/helber` com `200`;
  - logs sem `headers already sent` e sem `Fatal error` nas verificações finais;
  - deploys recentes em `success` no GitHub Actions (incluindo commit `273fd61`).
- Plano de Upgrade (Opção A): em execução com PR-00 a PR-05 concluídos em `main`.
  - `PR-00`:
    - tag `snapshot/pre-upgrade-2026-02-21`
    - branch `backup/pre-upgrade-2026-02-21`
    - evidência: `docs/PR_UPGRADE_PR-00.md`
  - `PR-01` (fundação de dados):
    - evidência: `docs/PR_UPGRADE_PR-01.md`
  - `PR-02` (onboarding por modo + upgrade progressivo):
    - evidência: `docs/PR_UPGRADE_PR-02.md`
  - `PR-03` (geo-pricing engine + catálogo):
    - evidência: `docs/PR_UPGRADE_PR-03.md`
  - `PR-04` (Stripe Connect + webhooks + split):
    - evidência: `docs/PR_UPGRADE_PR-04.md`
  - `PR-05` (Conta + Master Admin + lacunas P0 benchmark):
    - evidência: `docs/PR_UPGRADE_PR-05.md`
  - runs principais do upgrade em `success`:
    - `22252825769`, `22252951456`, `22253101995`, `22260660732`, `22261656916`
  - observação operacional do PR-02:
    - foi necessário executar `migrate_sovereign.php` durante o gate funcional de produção para re-sincronizar schema/caches; após isso, validações concluídas.
  - validação funcional de PR-05 concluída em produção:
    - rotas críticas `/`, `/login`, `/onboarding`, `/checkout`, `/dashboard`, `/p/helber` com `200`;
    - APIs validadas com admin: `/api/account/summary`, `/api/account/billing-history`, `/api/super-admin/overview`, `/api/super-admin/payments`, `/api/super-admin/customers`, `/api/super-admin/payments/export`;
    - logs operacionais sem `Fatal error` e sem `headers already sent`.

## Pendências Prioritárias

1. Continuar plano de Upgrade (Opção A) a partir do `PR-06`:
   - referência: `docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`.
   - escopo imediato: KPIs, analytics e observabilidade financeira.
2. Concluir `PR-06` e `PR-07` em ordem estrita:
   - não avançar sem gate/deploy/smoke de produção aprovados na etapa atual.
3. Após concluir `PR-07`, executar validação final obrigatória via navegador:
   - desktop;
   - mobile simulado (viewport mobile + fluxos críticos).
4. Executar roadmap de gaps do benchmark YCBM:
   - referência: `docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`.
5. Expandir cobertura E2E visual para os fluxos novos de landing/onboarding pós-rebranding.
6. Manter plano de pente fino de idioma em stand by até fechamento do upgrade:
   - `docs/PLANO_STANDBY_PENTE_FINO_IDIOMAS.md`

## Notas de Documentação

- Documento de matriz de copy do rebranding:
  - `docs/REBRANDING_COPY_MATRIX.md`
- Este handoff substitui o estado anterior e passa a orientar a continuação a partir do PR-06.
