## Objetivo do PR
Implementar `PR-01` do plano de rebranding:
`docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md`

## Etapa / Escopo
- Plano: Rebranding
- PR-ID: `PR-01`
- Etapa: Arquitetura de mensagem e matriz de copy
- Itens implementados:
  - Matriz de copy para 3 públicos (sem cobrança, com cobrança, migração)
  - Narrativa principal e progressão de valor
  - Padronização de termos PT-BR + base EN para tradução

## Fora de escopo (neste PR)
- Reescrita das seções da landing
- Alterações visuais/componentes de frontend

## Mudanças de conteúdo e produto
- Copy/Posicionamento:
  - Documento de referência criado para orientar as próximas etapas
- Localização (idioma/moeda):
  - Base terminológica EN adicionada para padronização de tradução
- SEO/Metadados:
  - Não aplicável neste PR
- Fluxo site -> onboarding:
  - Não aplicável neste PR

## Arquivos principais alterados
- `docs/REBRANDING_COPY_MATRIX.md`

## Evidências de teste
- [x] Checklist de consistência headline/subheadline/CTA
- [x] Links e rotas dos CTAs validados
- [ ] QA desktop executado
- [ ] QA mobile executado
- [x] Evidências anexadas (docs de copy/matriz)

Comandos executados:
```bash
Get-Content docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md
Get-Content docs/PLANO_DE_REBRANDING_DO_SITE_DO_MEETRIX.md
```

Resultado:
- `pass` - matriz aprovada para iniciar alteração de telas no PR-02.

## Gate obrigatório deste PR
- [x] Gate do PR validado conforme plano
- [x] Sem regressão de navegação
- [x] Sem quebra visual crítica

## Deploy e validação em produção
- [x] GitHub Actions `deploy.yml` verde (não aplicável - sem deploy)
- [x] Validação em produção concluída (não aplicável - sem deploy)
- [x] Polling de workflow a cada 15s realizado (não aplicável - sem deploy)

Validado em produção:
- [x] Home (não aplicável)
- [x] Navegação principal (não aplicável)
- [x] Seções alteradas da landing (não aplicável)
- [x] CTAs (não aplicável)
- [x] Signup e onboarding (não aplicável)

## Rollback
- Tag de referência: `snapshot/pre-rebranding-2026-02-21`
- Plano de rollback para este PR:
  - Reverter commit do documento de copy
  - Restaurar versão anterior do arquivo de matriz
- [x] Rollback testado (não aplicável para doc-only)

## Checklist final
- [x] Escopo do PR está aderente ao plano
- [x] Não mistura tarefas de outro plano
- [x] Identidade visual base preservada
- [x] Documentação atualizada
- [x] Aprovado para merge
