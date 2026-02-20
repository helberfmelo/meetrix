# PROMPT DE CONTINUAÇÃO (HANDOFF)

**OBJETIVO ATUAL**: dar continuidade à evolução do Meetrix pós-recuperação de produção, com foco em melhorias funcionais, gestão SaaS e correção de falhas abertas.

## Instruções Obrigatórias

1. Leia primeiro:
   - `docs/AI_OPERATIONS_GUIDE.md`
   - `docs/INFRASTRUCTURE.md`
   - `docs/Documentação YouCanBookMe (YCBM).md`
2. Comunicação obrigatória em PT-BR.
3. Em testes E2E, limpar sempre os campos de input antes de digitar.
4. Em caso de deploy, seguir polling de 15s no GitHub Actions e validar em produção.

## Contexto Consolidado

- A fase crítica de recuperação já foi concluída.
- Produção está operacional para login/onboarding/checkout e dashboard.
- Ajustes recentes concluídos:
  - rota pública `/p/{slug}` funcionando (ex.: `/p/helber`);
  - header mobile do sistema com controles alinhados à direita (tema, idioma, sair);
  - prefixo de URL pública no editor para `meetrix.opentshost.com/p/`;
  - textos e UX de onboarding refinados;
  - melhoria de contraste em dark mode e cobertura de traduções em páginas-chave;
  - módulo Master Admin entregue (clientes, pagamentos, cupons, atividade e ações administrativas seguras);
  - área de conta entregue para usuários (perfil, senha, preferências e histórico de cobrança);
  - correção da falha de agendamento com atualização de schema e testes.

## Pendências Prioritárias

1. Validar em produção os novos fluxos de Master Admin e Conta após deploy.
2. Evoluir backlog do benchmark YCBM:
   - referência: `docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`.

## Notas de Documentação

- Documentos obsoletos da última implementação foram removidos:
  - `docs/PLAN.md`
  - `docs/TASK.md`
  - `docs/implementation_plan.md`
- Este handoff substitui referências antigas da fase de recuperação.
