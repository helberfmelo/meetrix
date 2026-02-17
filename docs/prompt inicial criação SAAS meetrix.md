# Prompt para criação do SaaS Meetrix (Laravel + Vue.js + MySQL, sem Docker)

## Contexto e base de referência

Você está criando o **Meetrix**, um SaaS de agendamento inspirado no YouCanBookMe (YCBM). A documentação do YCBM (site + app) já foi levantada e descreve: estrutura do site (Use Cases, Product, Resources, Pricing), fluxo de onboarding, páginas e módulos do app, telas de edição (What, Who, When, Additional options), regras de disponibilidade, tipos de serviço, formulários, notificações por e-mail/SMS, pagamentos, analytics, integrações, embed, reuniões virtuais e sincronização de calendários.

**Objetivo:** construir o Meetrix com identidade visual própria e UI/UX premium, mantendo a mesma lógica principal de produto observada no YCBM.

---

## Stack e restrições obrigatórias (não negociar)

* **Backend:** PHP Laravel (API + painéis)
* **Frontend:** Vue.js (SPA do app/painel)
* **Banco:** MySQL
* **Sem Docker:** Não usar docker-compose, imagens, etc.
* **Deploy:** Repositório GitHub em `helberfmelo/meetrix` com GitHub Actions fazendo deploy automático para servidor de produção já configurado no caminho: `/helberfmelo/meetrix/`

> **Nota de Infraestrutura:** > Github já configurado nessa máquina com a chave SSH. Basta rodar via terminal:
> `git clone git@github.com:helberfmelo/meetrix.git`
> Credenciais de banco local e produção: Veja o arquivo `INFRASTRUCTURE.md`. Você deve preparar o projeto para receber esses dados via `.env` e GitHub Secrets, sem hardcode.

---

## Requisitos de produto (funcionalidades do Meetrix)

### 1) Multilíngue (obrigatório)

O Meetrix deve ser multi-idioma e permitir troca no topo do site e do app, exibindo bandeira + sigla.
**Idiomas:** EN, ES, FR, DE, PT-BR, PT, ZH-CN, JA, KO, IT, RU.

* **Regras:**
* Detectar idioma do navegador como default (com fallback para EN).
* Troca manual persistida (cookie/localStorage no front + preferências no perfil do usuário no backend).
* Tudo deve ser traduzível: UI, rótulos, validações, mensagens do sistema, e-mails, SMS, templates.


* **Implementação sugerida:**
* Vue i18n no front.
* Traduções do backend via Laravel Localization (`arquivos lang/`), incluindo templates de e-mail.



### 2) Site público (marketing)

Replicar a lógica de navegação do YCBM (com marca própria Meetrix):

* Use Cases / Product (Scheduling / Automations / Connect) / Resources / Pricing / Auth (Login / Sign up).
* **Requisitos:** Landing com CTA claro, páginas de produto por módulo, Pricing com comparação de recursos e SEO otimizado.
* **Tecnologia:** Laravel Blade para o site + componentes simples; App separado em Vue SPA.

### 3) App (painel SaaS)

O app deve reproduzir o núcleo do YCBM com estes módulos:

#### 3.1 Onboarding (6 passos)

1. Criar Página (nome, slug/URL). 2. Estilo/Marca. 3. Disponibilidade. 4. Conectar Calendário. 5. Local do encontro. 6. Idioma da página.

* Ao concluir: mostrar link, botões de compartilhar e atalhos de configuração.

#### 3.2 Navegação principal (menu lateral)

* Pages, Bookings, Team (pago), Analytics (pago), Polls.

#### 3.3 Pages (gestão de páginas)

* Listar, criar, copiar link, editar, duplicar, status online/offline.

#### 3.4 Editor da página (estrutura espelhada do YCBM)

Menu lateral: What, Who, When, Additional options, Availability rules, Appointment types, Booking form, Collect payments, Confirmation page, Notifications, Calendar events, Appearance, Language & timezones.

* **Requisito:** UI premium com cards/toggles e salvamento incremental.

#### 3.5 Booking flow (fluxo ponta a ponta)

* Calendário público -> Escolha de horário -> Formulário -> (Opcional) Stripe -> Confirmação -> Disparo de e-mails/SMS e criação de evento no calendário.

#### 3.6 Notificações (e-mail e SMS)

* Modelos personalizáveis com variáveis dinâmicas.
* **Eventos:** Criado, aprovado, reagendado, cancelado, lembrete e follow-up.
* **Implementação:** Laravel Notifications + Mailables + Jobs em fila.

#### 3.7 Pagamentos (Stripe)

* Cobrança por serviço, depósitos, cupons e dashboard de receitas.

#### 3.8 Integrações

* **Calendários:** Google, Microsoft, Apple/CalDAV.
* **Vídeo:** Zoom, Meet, Teams.
* **Webhooks/Conectores:** Zapier/Make.

#### 3.9 Polls (enquetes de horário)

* Criar poll, coletar respostas e confirmar horário final com 1 clique.

#### 3.10 Analytics

* Métricas de bookings, cancelamentos, receita e exportação CSV.

#### 3.11 Team (equipes e permissões)

* Convites, papéis (admin/editor/viewer) e Round-robin.

---

## Arquitetura do sistema

### Backend (Laravel)

* API REST versionada (`/api/v1`) com Laravel Sanctum.
* Domínios: Accounts/Tenants, Users, Pages, Bookings, Payments, etc.
* Async: Laravel Queue + Scheduler (cron).

### Frontend (Vue.js)

* Vue 3 + Vite + Pinia + Vue Router + Vue I18n.
* **UI:** TailwindCSS + Headless UI / Radix Vue.
* **Forms:** VeeValidate + Zod/Yup.

### Banco (MySQL)

* Migrations versionadas e Seeds para dados iniciais.
* **Multi-tenant:** Preparado por `account_id/tenant_id`.

---

## UI/UX premium (regras)

* Design system consistente e totalmente responsivo.
* Acessibilidade (labels, foco, teclado).
* Feedbacks de estado (loading, toast, empty-states).

---

## GitHub Actions (deploy sem Docker) — obrigatório

Configurar pipeline de CI/CD em `helberfmelo/meetrix`:

1. **Jobs:** Lint/Test -> Build frontend -> Deploy produção via SSH.
2. **Ações no Deploy:** Git pull, composer install, npm build, artisan migrate --force, optimize (cache), reiniciar serviços.
3. **Segurança:** Secrets no GitHub para SSH e variáveis sensíveis. **Nunca commitar .env**.

---

## Qualidade e entregáveis

* Código limpo e modular.
* Documentação: README com setup local e checklist de deploy.
* **Resultado esperado:** Usuário deve conseguir operar o fluxo completo de agendamento, pagamentos, notificações e gestão de equipe em múltiplos idiomas.

---

**Instrução Final:** Antes de criar um plano, leia todos os arquivos da pasta `docs` e siga as instruções. Crie o plano e salve na pasta docs. Verifique se o banco de dados já tem tabelas; pode apagar e criar novamente.

---
