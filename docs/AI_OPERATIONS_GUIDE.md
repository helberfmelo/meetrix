# AI Operations Guide - Meetrix SaaS

Este guia define os procedimentos operacionais padrão para a IA no projeto Meetrix.

> [!IMPORTANT]
> **Idioma de Comunicação**: a IA deve se comunicar com o usuário em **Português do Brasil (PT-BR)**.

## Fluxo de Deployment e Verificação

1. **Commit e Push**: realize commit/push na branch `main`.
   - Confirmar `git push origin main` com `Exit Code 0`.
2. **Monitoramento no GitHub Actions**:
   - URL: [https://github.com/helberfmelo/meetrix/actions/workflows/deploy.yml](https://github.com/helberfmelo/meetrix/actions/workflows/deploy.yml)
   - Polling obrigatório a cada 15 segundos (refresh manual).
   - Só seguir após o job "🎉 Deploy" ficar verde.
3. **Validação em produção**:
   - Logs: `https://opentshost.com/meetrix/read_logs.php`
   - Migração/seed (quando necessário): `https://opentshost.com/meetrix/migrate_sovereign.php`
   - Fluxos: Home, Login, Onboarding, Checkout, Dashboard e recursos alterados no release.

## Estado Atual em Produção (2026-02-21)

- `deploy.yml` operacional e último deploy validado com sucesso.
- PHP 8.2 funcional na HostGator.
- Erro de bootstrap `headers already sent` não reapareceu nos logs.
- Sem `Fatal error` na checagem operacional final.
- Rotas críticas validadas em produção (`/`, `/login`, `/onboarding`, `/dashboard`, `/p/helber`) com `200`.
- Rebranding do site concluído com deploys incrementais e validados em produção.
- Upgrade em execução com PR-00 a PR-05 já aplicados e validados em produção.
- Migração soberana (`migrate_sovereign.php`) executada no ciclo de PR-02 para restaurar paridade de schema em produção.
- YCBM P1-2 (self-service de reagendamento/cancelamento com token público) implementado e migrado.
- E-mail transacional em **modo `log`** temporariamente (Postmark em validação manual).

## Estado da Implementação Atual (2026-02-21)

- **Plano B (Rebranding) concluído**:
  - PR-00 a PR-06 executados em ordem, com gates respeitados;
  - landing atualizada com 13 seções e CTAs contextuais;
  - pricing por região/moeda na camada de conteúdo;
  - onboarding alinhado aos dois modos (`scheduling_only` e `scheduling_with_payments`);
  - SEO atualizado (title/description/canonical/og:url);
  - eventos de funil instrumentados (`cta_main_click`, `path_selected`, `signup_start`, `onboarding_completed`).
- Evidências consolidadas:
  - `docs/ENCERRAMENTO_REBRANDING_2026-02-22.md`
  - `docs/ENCERRAMENTO_ITENS_2026-02-22.md`
- Deploys recentes validados em verde (GitHub Actions):
  - `22252110956`, `22252181470`, `22252258268`, `22252327044`, `22252383969`, `22252402577`, `22252508806`.
- **Plano A (Upgrade) concluído**:
  - `PR-00` concluído (snapshot/tag/backup publicados no GitHub):
    - tag: `snapshot/pre-upgrade-2026-02-21`
    - branch: `backup/pre-upgrade-2026-02-21`
  - `PR-01` concluído (fundação de dados + tabelas financeiras + seed de `geo_pricing`).
  - `PR-02` concluído (onboarding por modo + upgrade progressivo + bloqueio de cobrança em `scheduling_only`).
  - `PR-03` concluído (engine de geo-pricing + catálogo dinâmico por região/moeda/modo).
  - `PR-04` concluído (Stripe Connect + webhooks obrigatórios + split com feature flag).
  - `PR-05` concluído (self-service de assinatura, ações financeiras Master Admin e CSV + UX de erro com `error_code`).
  - Evidências consolidadas:
    - `docs/ENCERRAMENTO_UPGRADE_2026-02-22.md`
    - `docs/ENCERRAMENTO_ITENS_2026-02-22.md`
  - Runs de deploy do upgrade validados em `success`:
    - `22252825769`, `22252951456`, `22253101995`, `22260660732`, `22261656916`
  - Runs de documentação/evidência também validados em `success`:
    - `22252853034`, `22253033192`, `22253122459`
  - Validação funcional adicional do PR-05 em produção:
    - rotas críticas com `200`: `/`, `/login`, `/onboarding`, `/checkout`, `/dashboard`, `/p/helber`;
    - APIs com admin validadas: `/api/account/summary`, `/api/account/billing-history`, `/api/super-admin/overview`, `/api/super-admin/payments`, `/api/super-admin/customers`, `/api/super-admin/payments/export`;
    - logs operacionais sem `Fatal error` e sem `headers already sent`.

## Pendências Abertas

1. Validar em produção o fluxo público de gerenciamento de booking (link em log, cancelamento e reagendamento).
2. Após o smoke de produção, registrar evidência em `docs/PR_YCBM_2026-02-21_P1-2.md`.
3. Executar a validação final obrigatória via navegador (desktop + mobile simulado) após concluir o ciclo corrente:
   - desktop;
   - mobile simulado (viewport mobile + fluxos críticos).
4. Manter plano de pente fino de idioma em stand by até fechamento do upgrade:
   - `docs/PLANO_STANDBY_PENTE_FINO_IDIOMAS.md`
5. Evoluir cobertura E2E visual automatizada para landing rebrand, onboarding por modo e módulos SaaS já entregues.
6. Avançar roadmap de lacunas mapeadas no benchmark YCBM (`docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`).

## E-mail Transacional (temporário)

- Produção está com `MAIL_MAILER=log` até a validação do Postmark.
- Após a validação, retornar para SMTP e executar `php artisan config:clear`.

## Credenciais de Referência

- **Não registrar credenciais neste documento.**
- Use o vault/gerenciador seguro do projeto para contas de ambiente local e produção.

## Protocolo de Testes

- **Limpeza de inputs**: antes de digitar em qualquer formulário, apagar conteúdo existente no campo.
- Preferir `tester@meetrix.pro` ou e-mails temporários para novos cadastros.
- Ao final de ciclos de teste, limpar dados residuais para não contaminar validações futuras.
- Se necessário, usar `migrate_sovereign.php` para reset total controlado.

## Manutenção de Banco

1. Priorizar migrações padrão Laravel.
2. Ajuste ad-hoc em produção: script PHP temporário, execução via navegador e remoção imediata.
3. Manter paridade entre produção e `DatabaseSeeder.php`/migrations do repositório.

## Infraestrutura e Roteamento em Produção

- Domínio principal (SPA/API): `https://meetrix.opentshost.com`
  - Mapeado para `/home1/opents62/public_html/meetrix/public`
- Acesso direto à raiz do projeto: `https://opentshost.com/meetrix/`
  - Scripts operacionais: `read_logs.php`, `migrate_sovereign.php`
- Base de páginas públicas: `https://meetrix.opentshost.com/p/{slug}`

### Cpanel / FTP

- **Credenciais devem permanecer fora do repositório.**
- Consultar o vault/gestor de senhas autorizado.

### Cpanel
Acesso ao cpanel: https://opentshost.com/cpanel

Usuário: opents62
Senha: SAFsdfasdfEWREgFDS435#@ad

### FTP
Acesso ao FTP: ftp.opentshost.com

Usuário: opents62
Senha: SAFsdfasdfEWREgFDS435#@ad

---
*Última atualização: 2026-02-22 (encerramentos consolidados; evidência: `docs/ENCERRAMENTO_ITENS_2026-02-22.md`)*
