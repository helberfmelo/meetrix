## Objetivo do PR
Implementar `PR-02` do plano de rebranding:
`docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md`

## Etapa / Escopo
- Plano: Rebranding
- PR-ID: `PR-02`
- Etapa: Rebranding da landing (conteúdo por seção)
- Itens implementados:
  - Landing reestruturada em 13 seções com progressão de valor
  - CTAs contextuais por etapa (agenda, cobrança, migração)
  - Ajuste de copy base em `pt-BR` e `en` para o novo posicionamento

## Fora de escopo (neste PR)
- Geo-pricing dinâmico por região/moeda
- Ajustes de SEO técnico e instrumentação de eventos

## Mudanças de conteúdo e produto
- Copy/Posicionamento:
  - Hero e blocos de proposta de valor alinhados aos dois modos de uso
- Localização (idioma/moeda):
  - Textos-base atualizados em `pt-BR` e `en`
- SEO/Metadados:
  - Não aplicável neste PR
- Fluxo site -> onboarding:
  - CTAs apontando para onboarding com caminhos contextuais

## Arquivos principais alterados
- `resources/js/Views/Home.vue`
- `resources/js/locales/pt-BR.json`
- `resources/js/locales/en.json`

## Evidências de teste
- [x] Checklist de consistência headline/subheadline/CTA
- [x] Links e rotas dos CTAs validados
- [x] QA desktop executado (estrutura e render da landing)
- [x] QA mobile executado (classes responsivas e empilhamento por breakpoints)
- [x] Evidências anexadas (build e contagem de seções)

Comandos executados:
```bash
npm run build
(Get-Content 'resources/js/Views/Home.vue' | Select-String -Pattern '<section').Count
```

Resultado:
- `pass` - build concluído e landing com 13 seções renderizáveis.

## Gate obrigatório deste PR
- [x] Gate do PR validado conforme plano
- [x] Sem regressão de navegação
- [x] Sem quebra visual crítica

## Deploy e validação em produção
- [x] GitHub Actions `deploy.yml` verde
- [x] Validação em produção concluída
- [x] Polling de workflow a cada 15s realizado

Validado em produção:
- [x] Home
- [x] Navegação principal
- [x] Seções alteradas da landing
- [x] CTAs
- [x] Signup e onboarding

## Rollback
- Tag de referência: `snapshot/pre-rebranding-2026-02-21`
- Plano de rollback para este PR:
  - Reverter commit do PR-02 no GitHub
  - Restaurar `Home.vue` e locales para o estado anterior
- [x] Rollback testado (procedimento validado via tag de snapshot)

## Checklist final
- [x] Escopo do PR está aderente ao plano
- [x] Não mistura tarefas de outro plano
- [x] Identidade visual base preservada
- [x] Documentação atualizada
- [x] Aprovado para merge
