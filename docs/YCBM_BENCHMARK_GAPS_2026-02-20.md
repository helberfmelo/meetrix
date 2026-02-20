# Benchmark Meetrix x YCBM (2026-02-20)

## Escopo comparado

- Gestão de contas SaaS (admin global)
- Gestão de conta do usuário final (perfil, segurança, cobrança)
- Fluxo de agendamento (booking + pagamento + cupom)
- Operação de equipe e governança

## Paridade entregue nesta fase

1. **Admin global SaaS**:
- visão consolidada de clientes, status, times, páginas, pagamentos, cupons e atividade.
- detalhamento por cliente com ações seguras (`activate`, `deactivate`, `reset_onboarding`) e trilha de auditoria.

2. **Conta do usuário**:
- perfil, preferências de idioma/timezone, troca de senha e histórico de cobrança.

3. **Booking resiliente**:
- causa raiz da falha de agendamento corrigida por alinhamento de schema + validações de consistência.
- suporte a cupom 100% sem operadora no fluxo de booking.

## Lacunas priorizadas (produto)

### P0 (próxima execução)

1. **Self-service de assinatura mais completo**:
- upgrade/downgrade/cancelamento direto na área de conta com histórico de mudanças de plano.

2. **Controles avançados de billing no Master Admin**:
- reprocessar pagamento, registrar ajuste manual com motivo, exportação CSV financeira.

3. **UX de erro e observabilidade operacional**:
- códigos de erro funcionais no frontend com mensagens específicas por causa (slot conflito, configuração inválida, falha de gateway).

### P1

1. **Round-robin e roteamento avançado de equipe** (mais próximo do YCBM avançado).
2. **Reagendamento/cancelamento self-service** para convidado com token seguro.
3. **Notificações parametrizadas por evento** com templates por idioma.

### P2

1. **Relatórios SaaS avançados** (MRR, churn, cohort, LTV).
2. **Governança multi-tenant avançada** (impersonation, escopos administrativos granulares).
3. **Marketplace de integrações** (além de Google, incluindo Microsoft e automações externas ampliadas).

## Resultado do benchmark

- **Ganho principal da fase**: fechamento das lacunas críticas operacionais (admin global, conta do usuário e booking estável).
- **Foco recomendado**: avançar de gestão operacional para automação comercial/financeira e capacidades de agendamento em equipe em nível enterprise.
