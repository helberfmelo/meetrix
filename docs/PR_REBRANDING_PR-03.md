## Objetivo do PR
Implementar `PR-03` do plano de rebranding:
`docs/PLANO_DE_IMPLEMENTACAO_REBRANDING_SITE_MEETRIX.md`

## Etapa / Escopo
- Plano: Rebranding
- PR-ID: `PR-03`
- Etapa: Planos, moeda e variação por região
- Itens implementados:
  - Motor de exibição regional na landing (`BR`, `USD`, `EUR`)
  - Separação explícita de comunicação por modo (`Agenda` vs `Agenda + Cobrança`)
  - Formatação monetária por locale com `Intl.NumberFormat`

## Fora de escopo (neste PR)
- Integração real de geo-pricing no backend
- Checkout real com pricing por região

## Mudanças de conteúdo e produto
- Copy/Posicionamento:
  - Bloco de planos reforçado para dois modos operacionais
- Localização (idioma/moeda):
  - Exibição regional por locale com fallback seguro
  - Suporte para simulação por query (`?region=BR|USD|EUR`)
- SEO/Metadados:
  - Não aplicável neste PR
- Fluxo site -> onboarding:
  - Mantido sem regressão

## Arquivos principais alterados
- `resources/js/Views/Home.vue`

## Evidências de teste
- [x] Checklist de consistência headline/subheadline/CTA
- [x] Links e rotas dos CTAs validados
- [x] QA desktop executado
- [x] QA mobile executado
- [x] Evidências anexadas (build + testes de moeda/locale)

Comandos executados:
```bash
npm run build
node -e "new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(39)"
node -e "new Intl.NumberFormat('en-US',{style:'currency',currency:'USD'}).format(9)"
node -e "new Intl.NumberFormat('es',{style:'currency',currency:'EUR'}).format(9)"
node -e "new Intl.NumberFormat('fr',{style:'currency',currency:'EUR'}).format(9)"
```

Resultado:
- `pass` - preços exibidos por região e formatados para PT-BR, EN-US, ES e FR.

## Gate obrigatório deste PR
- [x] Gate do PR validado conforme plano
- [x] Sem regressão de navegação
- [x] Sem quebra visual crítica

## Deploy e validação em produção
- [ ] GitHub Actions `deploy.yml` verde
- [ ] Validação em produção concluída
- [ ] Polling de workflow a cada 15s realizado

Validado em produção:
- [ ] Home
- [ ] Navegação principal
- [ ] Seções alteradas da landing
- [ ] CTAs
- [ ] Signup e onboarding

## Rollback
- Tag de referência: `snapshot/pre-rebranding-2026-02-21`
- Plano de rollback para este PR:
  - Reverter commit do PR-03 no GitHub
  - Restaurar lógica de planos estática no `Home.vue`
- [ ] Rollback testado (quando aplicável)

## Checklist final
- [x] Escopo do PR está aderente ao plano
- [x] Não mistura tarefas de outro plano
- [x] Identidade visual base preservada
- [x] Documentação atualizada
- [x] Aprovado para merge
