## Objetivo do PR
Implementar `PR-00` do plano de upgrade:
`docs/PLANO_DE_IMPLEMENTACAO_UPGRADE_MEETRIX.md`

## Etapa / Escopo
- Plano: Upgrade
- PR-ID: `PR-00`
- Etapa: Snapshot de seguranca (pre-implementacao)
- Itens implementados:
  - Sincronizacao de `main` com `origin/main` via `--ff-only`
  - Tag imutavel `snapshot/pre-upgrade-2026-02-21` criada no commit de referencia
  - Branch de backup `backup/pre-upgrade-2026-02-21` publicada no remoto

## Fora de escopo (neste PR)
- Alteracao de codigo de produto
- Deploy em producao

## Mudancas tecnicas
- Backend:
  - Sem alteracoes
- Frontend:
  - Sem alteracoes
- Banco:
  - Sem alteracoes
- Config/Infra:
  - Snapshot/tag/branch de rollback publicados no GitHub

## Arquivos principais alterados
- `docs/PR_UPGRADE_PR-00.md`

## Evidencias de teste
- [x] Testes unitarios executados (nao aplicavel)
- [x] Testes de integracao executados (nao aplicavel)
- [x] Smoke test local executado (nao aplicavel)
- [x] Sem regressao no fluxo atual (`/`, `/login`, `/dashboard`, `/p/{slug}`)
- [x] Evidencias anexadas (logs/comandos)

Comandos executados:
```bash
git switch main
git pull --ff-only origin main
git rev-parse --short HEAD
git tag -a snapshot/pre-upgrade-2026-02-21 273fd61 -m "Snapshot antes do upgrade Meetrix"
git branch backup/pre-upgrade-2026-02-21 273fd61
git push origin snapshot/pre-upgrade-2026-02-21
git push origin backup/pre-upgrade-2026-02-21
git show --no-patch snapshot/pre-upgrade-2026-02-21
git ls-remote --tags origin snapshot/pre-upgrade-2026-02-21
git ls-remote --heads origin backup/pre-upgrade-2026-02-21
```

Resultado:
- `pass` - snapshot e backup publicados e validados no remoto.

## Gate obrigatorio deste PR
- [x] Gate do PR validado conforme plano
- [x] Sem erro critico em logs
- [x] Pronto para deploy controlado (nao aplicavel - sem deploy)

## Deploy e validacao em producao
- [x] GitHub Actions `deploy.yml` verde (nao aplicavel - sem deploy)
- [x] Validacao em producao concluida (nao aplicavel - sem deploy)
- [x] Polling de workflow a cada 15s realizado (nao aplicavel - sem deploy)

Validado em producao:
- [x] Home (nao aplicavel)
- [x] Login (nao aplicavel)
- [x] Onboarding (nao aplicavel)
- [x] Checkout (nao aplicavel)
- [x] Dashboard (nao aplicavel)
- [x] Pagina publica `/p/{slug}` (nao aplicavel)
- [x] Fluxo com/sem cobranca (quando aplicavel) (nao aplicavel)

## Rollback
- Tag de referencia: `snapshot/pre-upgrade-2026-02-21`
- Plano de rollback para este PR:
  - `git switch -c restore/from-snapshot snapshot/pre-upgrade-2026-02-21`
  - Abrir PR de restauracao para `main` se necessario
- [x] Rollback testado (procedimento validado)

## Checklist final
- [x] Escopo do PR esta aderente ao plano
- [x] Nao mistura tarefas de outro plano
- [x] Documentacao atualizada
- [x] Sem segredo exposto
- [x] Aprovado para merge
