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

## Estado Atual em Produção (2026-02-20)

- `deploy.yml` operacional e último deploy validado com sucesso.
- PHP 8.2 funcional na HostGator.
- Erro de bootstrap `headers already sent` não reapareceu nos logs após os ajustes.
- Fluxo de login/onboarding/checkout com cupom total (`cupom100`) validado.
- Página pública `/p/{slug}` restaurada (ex.: `/p/helber`).
- Layout mobile do sistema ajustado no topo direito: tema, idioma e sair.
- Editor de página atualizado para prefixo público `meetrix.opentshost.com/p/`.
- Deploy desta fase concluído em verde no GitHub Actions:
  - run `#94` (feature principal) e run `#95` (hotfix booking-mail).

## Estado da Implementação Atual (2026-02-20)

- Módulo **Master Admin** implementado no backend/frontend:
  - visão geral SaaS, listagem de clientes, detalhe por cliente, pagamentos, cupons e atividade.
  - ações administrativas seguras (`activate`, `deactivate`, `reset_onboarding`) com auditoria.
- Área de **Conta** implementada para todos os usuários:
  - perfil, segurança, preferências e histórico de cobrança.
- Correção aplicada para erro de agendamento (`Falha no agendamento...`):
  - ajuste de schema da tabela `bookings`;
  - validações de consistência entre página e tipo de serviço;
  - testes automatizados cobrindo fluxo com cupom 100% sem gateway;
  - hotfix para não bloquear confirmação quando houver falha de e-mail.
- Validação funcional em produção concluída:
  - `/`, `/login`, `/dashboard`, `/p/helber` respondendo `200`;
  - login Master Admin + APIs `/api/account/summary` e `/api/super-admin/overview` funcionando;
  - criação real de booking em produção confirmada com status `confirmed`;
  - fluxo de novo usuário (registro -> página -> onboarding complete -> checkout cupom100) confirmado.

## Pendências Abertas

1. Avançar roadmap de lacunas mapeadas no benchmark YCBM (`docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`).
2. Evoluir cobertura E2E visual automatizada para os novos módulos (`Master Admin` e `Conta`).

## Credenciais de Referência

### Local

- URL: `http://localhost:8000`
- Login: `admin@meetrix.pro`
- Senha: `MeetrixMaster2026Sovereign!#`

### Produção (Master Admin)

- URL: [https://meetrix.opentshost.com/login](https://meetrix.opentshost.com/login)
- Login: `admin@meetrix.pro`
- Senha: `MeetrixMaster2026Sovereign!#`

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

---
*Última atualização: 2026-02-20 (módulos Master Admin + Conta + correção de booking)*
