Documentação YouCanBookMe (YCBM)
Visão geral

YouCanBookMe é um serviço SaaS de agendamento online focado em pequenos negócios. A plataforma oferece páginas de agendamento personalizáveis, automações de notificação, integrações com calendários e ferramentas de videoconferência, coleta de pagamentos, relatórios e painéis administrativos. Esta documentação descreve a estrutura do site público e as telas do aplicativo web com base em exploração detalhada do serviço em fevereiro/2026. Ela servirá como base para projetar um novo SaaS denominado Meetrix.

Estrutura do site

O site público (youcanbook.me) está dividido em várias seções, acessíveis via menu principal: Use Cases, Product, Resources e Pricing. Abaixo há um resumo das páginas principais e seus recursos:

Use Cases

Página "Small business scheduling software" – destaca que mais de 1 milhão de usuários confiam na plataforma e apresenta setores atendidos, como beleza e serviços pessoais, coaches, criativos, educação, serviços financeiros, saúde, organizações sem fins lucrativos, profissionais liberais, imobiliárias, varejo e esportes.

Product (Scheduling, Automations, Connect)
Customizable booking pages

Permite personalizar aparência da página de agendamento: logotipo, cores de marca, imagens e rodapé.

Controle de visualização do calendário: dia único, vários dias ou mês.

Possibilidade de adicionar vários tipos de serviços (durações e preços diferentes) e permitir que o cliente selecione mais de um serviço.

Formulários de reserva personalizados com campos adicionais e lógica condicional.

Localização automática do usuário, suporte a mais de 44 idiomas, moedas múltiplas e tratamento de fuso horário.

Embeddable: página pode ser incorporada em websites, exibida como pop‑up ou botão flutuante.

Availability

Conexão com múltiplos calendários (Google, Microsoft, Apple etc.) para atualizar disponibilidade em tempo real.

Opções de agendamento somente mediante solicitação, proteção por senha, janelas de reserva definidas, limite de reservas por dia e intervalos de descanso (padding).

Agendas repetitivas ou disponibilidade gerenciada pelo calendário com intervalos de início personalizáveis.

Links de uso único e aviso mínimo/máximo antes da reserva.

Compartilhamento do link de agendamento via URL, incorporação ou código QR.

Meeting polls

Permite criar uma enquete de horários, compartilhar com participantes e, após receber respostas, confirmar a reunião em um horário em comum. Útil para eliminar idas e vindas ao marcar reuniões de grupo.

Admin controls

Painel central para gerenciar todas as páginas de agendamento, usuários, permissões e dados analíticos.

Duplicação de páginas para acelerar onboarding de novos usuários e round‑robin para distribuição de reuniões entre membros.

Booking flow

Permite configurar múltiplos tipos de compromissos, formularios condicionais, aceitar ou rejeitar reservas e proteger páginas com senha.

Integração de pagamentos (Stripe, cartões, Apple/Google Pay) com preços individuais, cupons e complementos.

Sincronização de calendários e integrações para envio de webhooks ou atualização de CRMs.

Fluxo de comunicação automatizada: confirmações, lembretes e mensagens pós‑evento via email ou SMS.

Email & SMS notifications

Personalização do assunto, conteúdo e hora de envio de emails; uso de variáveis dinâmicas (nome, data etc.).

Envio via Gmail/Outlook, inclusão de links para reagendar ou cancelar.

SMS enviados por números dedicados locais em mais de 200 países, com templates e lembretes automáticos para reduzir ausências.

Payments

Cobranças via Stripe, com suporte a cartões de crédito, Apple Pay, Google Pay e moedas globais.

Permite definir preço para cada serviço, criar códigos promocionais e complementos, e só confirma a reserva após pagamento aprovado.

Analytics

Dashboard de métricas: páginas mais populares, total de encontros, cancelamentos, remarcações, ausências, receita gerada e SMS enviados.

Permite exportar dados via CSV ou agendar exportações periódicas para análises externas.

Integrations

Conectores com Google, Microsoft (Outlook, Teams), Apple, Fastmail e CalDAV para calendários; Zoom, Google Meet e Teams para vídeo; Stripe para pagamentos; Gmail/Outlook para emails; Zapier, HubSpot e outras ferramentas para automações e CRM.

Embed

Código embutido para inserir a página de reservas em sites (inline, pop‑up ou botão flutuante), compatível com plataformas como Wix, Wordpress, Webflow, Shopify etc. Também oferece extensão Chrome e QR code.

Virtual meetings

Geração automática de links de reunião (Zoom, Google Meet, MS Teams) ao agendar; opção de oferecer atendimento presencial, por telefone ou virtual na mesma página.

Calendar sync

Sobreposição de calendários para o cliente ver disponibilidade de todos simultaneamente; sincronização bidirecional em tempo real com até 10 calendários e possibilidade de gerenciar horários diretamente no calendário.

Pricing

O plano Free permite 1 calendário e 1 página de reserva. O plano Individual amplia para 2 calendários e 2 páginas e inclui cores personalizadas. O Professional oferece 6 calendários e 6 páginas, mais recursos avançados (páginas de grupo, proteção por senha, quantidades ilimitadas de automações, códigos de desconto, mensagens offline etc.). Existem preços adicionais para funções de equipe, SMS e integrações.

Estrutura do aplicativo web

Ao acessar app.youcanbook.me com credenciais, o usuário passa por um onboarding dividido em seis etapas:

Nome da página – Cria uma URL personalizada (ex.: bigshop.youcanbook.me).

Estilo – Escolha de cor de marca e layout.

Disponibilidade – Definição da duração da reunião, dias e horários.

Conexão de calendário – Conectar Google, Microsoft, iCloud, Fastmail, Fruux ou CalDAV.

Localização – Selecionar tipo de reunião: Zoom, presencial, telefonema, local personalizado ou sem local
app.youcanbook.me
.

Idioma – Escolher idioma da página (Português – pt‑BR ou Inglês).

Após o onboarding, a página de destino exibe um cartão com o link de agendamento, botões para copiar ou compartilhar e opção de editar. Também apresenta painel de reservas (vazio inicialmente) e links úteis para integração de pagamentos, extensão do Chrome e criação de links de reuniões.

Navegação principal

O menu lateral do aplicativo inclui as seções Pages, Bookings, Team, Analytics e Polls. A maioria dos recursos avançados requer upgrade para planos pagos.

Pages

Lista todas as páginas de reserva criadas. Cada página possui ícones para copiar link, compartilhar e acessar a tela de edição. Há botão para criar nova página e opção de integrar com Slack para notificações. Clicar no nome da página abre a página pública, com calendário e horários disponíveis para os clientes escolherem.

Edição de página de reservas

Ao editar uma página, um painel lateral organiza configurações em quatro grupos: What, Who, When e Additional options. O painel principal mostra uma prévia da página.

What

Nome e link da página: permite alterar título e URL.

Local do evento: escolher entre Zoom, Google Meet, Microsoft Teams, reunião presencial, ligação telefônica, solicitar local ao cliente, local customizado ou sem local. Também é possível adicionar descrição introdutória e alternar status online/offline.

Who

Calendário: conectar calendário para sincronizar eventos e bloquear horários
app.youcanbook.me
.

Teams: recursos de equipe (pagos), permitindo que cada membro receba reservas independentemente, que o cliente escolha membro ou distribuição por prioridade (round‑robin). Inclui botão para convidar membros
app.youcanbook.me
.

When

Duração: define duração padrão do compromisso e permite que o cliente escolha a duração (opcional). Escolhe tipo de disponibilidade: repetitiva ou gerenciada pelo calendário. Ajusta horários diários, dias da semana e intervalo de início (ex.: a cada 30 minutos)
app.youcanbook.me
.

Additional options

Subseções acessíveis no painel:

Sub-seção	Recursos principais
Availability rules	Ajuste de intervalos entre reservas (padding), sessões de grupo com capacidade para até 50 pessoas, limite de reservas por dia, datas de início/fim da agenda, aviso mínimo/máximo para agendar e links de uso único
app.youcanbook.me

Appointment types	Permite criar vários serviços com durações, preços e locais diferentes; clientes podem selecionar mais de um serviço na mesma reserva
app.youcanbook.me

Booking form	Configura campos obrigatórios e adicionais. Campo padrão inclui Nome, Apelido e Email. Pode adicionar perguntas personalizadas e ativar CAPTCHA
app.youcanbook.me

Collect payments	Integra Stripe para aceitar cartões, Apple/Google Pay; define valor, aceita depósitos e reduz faltas
app.youcanbook.me

Confirmation page	Define mensagem final exibida ao cliente ou redireciona para URL externa após a reserva
app.youcanbook.me

Notifications	Gerencia gatilhos e mensagens (novas reservas, aprovações, remarcações, cancelamentos, lembretes, pós‑consulta, ausências), podendo enviar emails e SMS customizados
app.youcanbook.me

Calendar events	Personaliza título e descrição do evento criado no calendário; adiciona links para cancelamento e reagendamento
app.youcanbook.me

Appearance	Escolha de tema e paleta de cores, fotos de capa, layout vertical ou horizontal, rodapé personalizado e mensagem quando offline
app.youcanbook.me

Language & timezones	Define fuso horário padrão, detecção automática e exibição de fuso para o cliente; escolhe idioma da página em lista de múltiplas opções
app.youcanbook.me
app.youcanbook.me
Bookings

Listas de reservas com filtros por status (próximas, passadas, canceladas) e exportação para CSV. Mostra nome, data/hora e evento agendado. Nesta exploração não havia reservas, então a tabela estava vazia
app.youcanbook.me
.

Team

Recurso premium para gerenciar usuários e equipes. A tela apresenta promoção de upgrade e opção para convidar usuários via email
app.youcanbook.me
.

Analytics

Disponível apenas no plano Professional. Exibe dados de reservas, receita e métricas gerais. A página de exemplo mostra mensagem explicando necessidade de upgrade
app.youcanbook.me
.

Polls

Permite criar enquetes de reunião (meeting polls) e gerenciar links. Oferece botão para criar enquetes e links para artigos de ajuda
app.youcanbook.me
.

Principais recursos e boas práticas

Personalização completa – páginas de reservas permitem customizar aparência, formulários, tipos de serviços, idioma, fuso horário, mensagens de confirmação e e‑mails/SMS.

Automação – lembretes e confirmações automáticos por email/SMS; cancelamento e remarcação com um clique; webhooks e integrações para CRM.

Pagamento integrado – aceitação de cartões e carteiras digitais via Stripe, preços por serviço, cupons e complementos, com confirmação apenas após pagamento.

Analytics – painel de métricas, exportação de dados e insights para melhorar fluxo de reservas e receita.

Integrações – suporte a calendários, videoconferência, plataformas de email e automação (Zapier, HubSpot, etc.).

Flexibilidade – enquetes de horários, reservas de grupo, limites de reservas, proteção por senha, links de uso único, padding e regras de disponibilidade.

Esta documentação oferece visão abrangente do YCBM para embasar a criação de um SaaS similar chamado Meetrix com interface e marca próprias.