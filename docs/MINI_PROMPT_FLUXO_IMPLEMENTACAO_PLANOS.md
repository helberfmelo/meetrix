# Mini Prompt Orientativo - Fluxo Obrigatorio de Leitura e Execucao

Copie e use este prompt no inicio de cada ciclo de implementacao.

```md
Voce vai executar a implementacao dos planos do Meetrix com disciplina operacional.

CONTEXTO ATUAL:
- O plano de Rebranding (Opcao B) ja foi concluido e validado em producao.
- O plano de Upgrade (Opcao A) ja foi iniciado e esta em andamento ate o PR-03.
- O proximo ciclo deve continuar a Opcao A a partir do PR-04, salvo instrucao explicita em contrario.

REGRA PRINCIPAL:
- Implementar UM plano por vez.
- Nao misturar PRs de Upgrade e Rebranding no mesmo ciclo.
- So iniciar o segundo plano depois de concluir e validar o primeiro.

PASSO 1 - Leitura obrigatoria (nesta ordem)
1. `docs/AI_OPERATIONS_GUIDE.md`
2. `docs/INFRASTRUCTURE.md`
3. `docs/HANDOFF_PROMPT.md`
4. `docs/Documentação YouCanBookMe (YCBM).md`
5. `docs/Documento Técnico Conceitual.md`
6. `docs/YCBM_BENCHMARK_GAPS_2026-02-20.md`
7. `docs/PITCH_MEETRIX.md`
8. `docs/PITCH_MEETRIX.pdf`

PASSO 2 - Escolha do plano (apenas 1)
- Opcao A: `docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`
- Opcao B: `docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md`
- Regra de contexto atual: continuar a Opcao A (Upgrade) a partir do PR-04.

ESTADO JA CONCLUIDO (para evitar retrabalho)
- Upgrade:
  - PR-00 concluido (snapshot/tag/backup publicados)
  - PR-01 concluido
  - PR-02 concluido
  - PR-03 concluido
- Evidencias:
  - `docs/PR_UPGRADE_PR-00.md`
  - `docs/PR_UPGRADE_PR-01.md`
  - `docs/PR_UPGRADE_PR-02.md`
  - `docs/PR_UPGRADE_PR-03.md`
- Regra: nao reexecutar PRs concluidos, exceto em caso de hotfix/rollback formal.

PASSO 3 - Seguranca antes de implementar
- Se o plano ainda nao tiver PR-00 executado:
  - executar PR-00 (snapshot/tag/branch de backup) do plano escolhido.
  - validar snapshot no GitHub antes de qualquer mudanca de codigo.
  - usar data corrente no snapshot: `snapshot/pre-<plano>-<YYYY-MM-DD>`.
- Se PR-00 ja estiver concluido e validado:
  - prosseguir para o proximo PR pendente da ordem oficial.

PASSO 4 - Execucao por PR (ordem obrigatoria)
- Seguir estritamente a ordem de PRs definida no plano escolhido.
- Um PR por vez em `main`.
- Nao abrir PR da etapa seguinte sem:
  - gate da etapa atual aprovado
  - deploy verde no GitHub Actions
  - validacao funcional em producao concluida
- Para o contexto atual (Upgrade):
  - proximo PR esperado: `PR-04`

PASSO 5 - Template de PR
- Usar `docs/TEMPLATES_PR_IMPLEMENTACAO_PLANOS.md`.
- Preencher objetivo, escopo, testes, deploy e rollback.

PASSO 6 - Regra de bloqueio
- Se qualquer gate falhar:
  - parar a sequencia
  - corrigir no mesmo PR ou reverter
  - somente depois retomar a ordem

PASSO 7 - Criterio de conclusao
- Concluir todos os PRs do plano escolhido.
- Confirmar criterios de conclusao no proprio plano.
- Registrar evidencias finais (testes, deploy, logs).
```
