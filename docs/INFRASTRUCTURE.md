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

1. Sem bloqueio de infraestrutura para login, onboarding e página pública de agenda.
2. Rotas públicas `/p/{slug}` validadas.
3. Erro de cabeçalho (`headers already sent`) não detectado nos últimos logs pós-ajustes.
4. APIs novas disponíveis para operação SaaS:
   - `/api/super-admin/*` (visão global, clientes, pagamentos, atividade).
   - `/api/account/*` (perfil, preferências, segurança e cobrança do usuário).

## Pendências Técnicas Relevantes

1. Validar em produção os novos fluxos Master Admin e Conta após deploy.
2. Executar roadmap de gaps do benchmark YCBM documentado em `docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`.

## Credenciais Master Admin (Produção)

- URL: `https://meetrix.opentshost.com/login`
- User: `admin@meetrix.pro`
- Password: `MeetrixMaster2026Sovereign!#`

---
Last update: 2026-02-20 (Master Admin + Account + Booking fix)
