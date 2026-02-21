# Infrastructure and Deployment

## Repository

- URL: [https://github.com/helberfmelo/meetrix/](https://github.com/helberfmelo/meetrix/)
- Branch principal: `main`
- Pipeline de deploy: `deploy.yml` (GitHub Actions -> FTP HostGator)

## Produção (HostGator / OpenTS Host)

- SPA/API: [https://meetrix.opentshost.com/](https://meetrix.opentshost.com/)
- Raiz do projeto no servidor: `/home1/opents62/public_html/meetrix/`
- Document root público: `/home1/opents62/public_html/meetrix/public`
- Acesso direto a scripts da raiz: `https://opentshost.com/meetrix/`

## URLs Operacionais Importantes

- Login: `https://meetrix.opentshost.com/login`
- Dashboard: `https://meetrix.opentshost.com/dashboard`
- Página pública por slug: `https://meetrix.opentshost.com/p/{slug}`
- Logs: `https://opentshost.com/meetrix/read_logs.php`
- Migração/sync: `https://opentshost.com/meetrix/migrate_sovereign.php`

## Banco de Dados (Produção)

- Host: `localhost`
- Database: `opents62_meetrix`
- Estado de referência: sincronizado com `migrate_sovereign.php` (`migrate:fresh --seed` + limpeza de cache) na recuperação da fase 9.

## Estado Operacional Atual

1. Sem bloqueio de infraestrutura para login, onboarding, dashboard e página pública de agenda.
2. Rotas públicas `/p/{slug}` validadas (ex.: `/p/helber`).
3. Erro de cabeçalho (`headers already sent`) não detectado nas validações recentes.
4. Sem `Fatal error` na leitura operacional final de logs.
5. APIs SaaS disponíveis para operação:
   - `/api/super-admin/*` (visão global, clientes, pagamentos, atividade).
   - `/api/account/*` (perfil, preferências, segurança e cobrança do usuário).
6. Deploys de rebranding validados em produção (2026-02-21):
   - runs `22252110956`, `22252181470`, `22252258268`, `22252327044`, `22252383969`, `22252402577`.
7. Deploy de ajuste i18n final validado em produção:
   - run `22252508806` (`success`, commit `273fd61`).
8. Testes funcionais em produção confirmados:
   - login/admin, account summary, super-admin overview;
   - fluxo de onboarding com seleção de modo operacional;
   - fluxo novo usuário e fluxo usuário existente;
   - criação de agendamento pública em `/api/bookings`;
   - cupom `cupom100` confirmando checkout sem operadora.
   - home rebrand com títulos/cópias atualizadas e CTAs ativos.
9. Upgrade (Opção A) já avançado em produção:
   - PR-00: snapshot/tag/backup publicados (`snapshot/pre-upgrade-2026-02-21`, `backup/pre-upgrade-2026-02-21`).
   - PR-01: fundação de dados e tabelas financeiras.
   - PR-02: onboarding por modo + upgrade progressivo sem perda de histórico.
   - PR-03: catálogo de geo-pricing por região/moeda/modo (`/api/pricing/catalog`).
10. Deploys do ciclo de upgrade validados em `success`:
   - `22252825769`, `22252951456`, `22253101995`.
11. Durante validação do PR-02, produção foi re-sincronizada com:
   - `https://opentshost.com/meetrix/migrate_sovereign.php`
   - execução concluída com migrations + seed + limpeza de cache.

## Pendências Técnicas Relevantes

1. Continuar Upgrade a partir do `PR-04` (Stripe Connect + webhooks + split), mantendo gate completo por etapa.
2. Executar `PR-05`, `PR-06` e `PR-07` do plano de upgrade em sequência estrita.
3. Executar roadmap de gaps do benchmark YCBM documentado em `docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`.
4. Expandir validação E2E visual para landing e onboarding pós-rebranding.

## Credenciais Master Admin (Produção)

- URL: `https://meetrix.opentshost.com/login`
- User: `admin@meetrix.pro`
- Password: `MeetrixMaster2026Sovereign!#`

---
Last update: 2026-02-21 (Upgrade em execução até PR-03, com deploys validados)
