# Meetrix.pro — Sovereign Scheduling Protocol

<p align="center">
    <img src="https://meetrix.opentshost.com/images/logo.png" width="200" alt="Meetrix Logo">
</p>

Meetrix is a premium, world-class SaaS scheduling platform (inspired by YouCanBookMe) designed for high-performance teams. Built with a focus on aesthetic excellence, sovereign control, and multi-language accessibility.

## 🚀 Key Features

- **Sovereign Dashboard**: Full control over your booking pages, availability, and bookings.
- **Premium Multi-language Engine**: Native support for 11 languages (EN, ES, FR, DE, PT-BR, PT, ZH, JA, KO, IT, RU).
- **Aesthetic Refinement**: High-contrast, dark-mode-first design using the zinc/orange sovereign palette.
- **Onboarding Protocol**: 6-step guided setup for new users.
- **Public Booking Suite**: Glassmorphic, responsive scheduling pages for clients.
- **Master Admin Access**: Dedicated infrastructure for system-level management.

## 🛠 Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+) with Sanctum API Authentication.
- **Frontend**: Vue 3 (SPA) + Vite + Tailwind CSS 4 + Pinia + Vue-I18n.
- **Database**: MySQL 8.
- **Iconography**: Font Awesome 6 Pro (CDN).
- **Deployment**: automated via GitHub Actions (CI/CD) to HostGator / OpenTS Host.

## 📁 Documentation

Detailed documentation can be found in the `docs/` directory:

- [Technical Concept](file:///d:/Projetos/meetrix/docs/Documento%20T%C3%A9cnico%20Conceitual.md): Comprehensive system architecture and logic.
- [Implementation Plan](file:///d:/Projetos/meetrix/docs/PLAN.md): Roadmap and phase-by-phase development history.
- [Infrastructure](file:///d:/Projetos/meetrix/docs/INFRASTRUCTURE.md): Server details, credentials, and deployment secrets.

## ⚙️ Installation & Development

### Prerequistes
- PHP 8.2+
- Node.js 18+
- MySQL 8

### Setup
1. Clone the repository.
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Configure `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Run migrations:
   ```bash
   php artisan migrate --seed
   ```
5. Start development:
   ```bash
   npm run dev
   php artisan serve
   ```

## 🔐 Security & Protocols

Meetrix follows strict security standards, including JWT-based API protection and centralized master-admin provisioning via database migrations.

---
© 2026 Meetrix | Developed by [OTS - Open Tecnologia e Serviços](https://opents.com.br)
