# Implementation Plan - Meetrix SaaS

## Goal Description
Build "Meetrix", a SaaS scheduling platform inspired by YouCanBookMe (YCBM), using Laravel (Backend), Vue.js (Frontend SPA), and MySQL. The system will feature a public booking page, admin dashboard, multilingual support, and integrations (Stripe, Calendars, Video), deployed via GitHub Actions without Docker.

## User Review Required
> [!IMPORTANT]
> **Infrastructure Dependencies**:
> - **Database**: The local database `meetrix_db` will be used for development. Ensure it exists.
> - **Deploy**: GitHub Secrets (`FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`, SSH Keys) must be configured in the repo settings for the Actions workflow to succeed.
> - **Domain**: Production URL is set to `/home1/opents62/public_html/meetrix/`.
> - **URL test access**: Production URL is set to `https://meetrix.opentshost.com/`.

## Proposed Changes

### [DONE] Phase 1: Foundation & Setup
#### [Laravel Project](file:///d:/Projetos/meetrix/)
- [x] Initialize Laravel 11 project.
- [x] Configure `.env` with local DB credentials.
- [x] Install `laravel/sanctum` for API authentication.
- [x] Install `mcamara/laravel-localization`.

#### [Frontend Project](file:///d:/Projetos/meetrix/resources/js/)
- [x] Initialize Vue 3 + Vite.
- [x] Install `pinia`, `vue-router`, `vue-i18n`.
- [x] Install `tailwindcss`, `postcss`, `autoprefixer`.
- [x] Install `@headlessui/vue` for accessible UI components.

#### [GitHub Actions](file:///d:/Projetos/meetrix/.github/workflows/deploy.yml)
- [x] Create deployment workflow (FTP/CI).

### [DONE] Phase 2: Database Architecture
- [x] Migrations for Tenants, Pages, Availability, Bookings, Teams.

### [DONE] Phase 3: Backend Implementation
- [x] API Routes (Auth, Dashboard, Public).
- [x] Controllers (Page, Booking, Dashboard).

### [DONE] Phase 4: Frontend Implementation
- [x] AdminLayout & PublicLayout.
- [x] Onboarding Protocol (6-Step).
- [x] Dashboard & PagesList Editor.

### [DONE] Phase 5: Aesthetic Sovereign Refinement & I18n
- [x] Font Awesome 6 Integration.
- [x] Premium Multi-language Engine (11 languages).
- [x] Sovereign Color Palette (Zinc/Orange).
- [x] "Naked" Icon Switchers (Theme/Locale).
- [x] Login Layout Optimization.
- [x] Full I18n Localization (11 languages, all modules).

### [IN PROGRESS] Phase 9: Quality, Testing & URL Refinement
- [x] Fix missing Home page feature cards.
- [x] Add tooltips to onboarding flow.
- [x] Update production subdomain to `meetrix.opentshost.com`.
- [/] E2E Testing with `cupom100` and `helberfrancis@gmail.com`.

## Verification Plan

### Automated Tests
- **Backend (PHPUnit)**:
    - Run `php artisan test`.
    - Test Authentication, Tenant isolation, Availability logic.
- **Frontend (Vitest)**:
    - Run `npm run test`.
    - Test Component rendering, Store state changes.

### Manual Verification
1. **Setup**:
    - Run `composer install && npm install`.
    - Run `php artisan migrate:fresh --seed`.
    - Run `npm run dev` and `php artisan serve`.
2. **Onboarding**:
    - Register a new user.
    - Complete the 6-step onboarding.
    - Verify redirection to Dashboard.
3. **Booking**:
    - Open the generated public link.
    - Select a time slot.
    - Verify form validation.
    - Complete booking.
    - Verify email notification (Mailtrap/Log).
    - Verify new booking in Dashboard.
4. **Deploy**:
    - Push to `main`.
    - Verify GitHub Action success.
    - Check production URL.


### Importante
- Comunication here in the chat: PT-BR
- Code: EN
- Texts and labels Front: PT-BR