# Implementation Plan - Meetrix SaaS

## Goal Description
Build "Meetrix", a SaaS scheduling platform inspired by YouCanBookMe (YCBM), using Laravel (Backend), Vue.js (Frontend SPA), and MySQL. The system will feature a public booking page, admin dashboard, multilingual support, and integrations (Stripe, Calendars, Video), deployed via GitHub Actions without Docker.

## User Review Required
> [!IMPORTANT]
> **Infrastructure Dependencies**:
> - **Database**: The local database `meetrix_db` will be used for development. Ensure it exists.
> - **Deploy**: GitHub Secrets (`FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`, SSH Keys) must be configured in the repo settings for the Actions workflow to succeed.
> - **Domain**: Production URL is set to `/home1/opents62/public_html/meetrix/`.
> - **URL test access**: Production URL is set to `https://opentshost.com/meetrix/`.

## Proposed Changes

### Phase 1: Foundation & Setup
#### [NEW] [Laravel Project](file:///d:/Projetos/meetrix/)
- Initialize Laravel 11 project.
- Configure `.env` with local DB credentials (`meetrix_user`, `meetrix_password`, `meetrix_db`).
- Install `laravel/sanctum` for API authentication.
- Install `mcamara/laravel-localization` or similar for backend i18n.

#### [NEW] [Frontend Project](file:///d:/Projetos/meetrix/resources/js/)
- Initialize Vue 3 + Vite.
- Install `pinia` (State), `vue-router` (Routing), `vue-i18n` (Multilingual).
- Install `tailwindcss`, `postcss`, `autoprefixer`.
- Install `@headlessui/vue` for accessible UI components.

#### [NEW] [GitHub Actions](file:///d:/Projetos/meetrix/.github/workflows/deploy.yml)
- Create deployment workflow:
    - Lint & Test (PHP/JS).
    - Build Assets (`npm run build`).
    - Deploy to production server via SSH/FTP (using `shivammathur/setup-php`, `actions/checkout`).

### Phase 2: Database Architecture
#### [NEW] [Migrations](file:///d:/Projetos/meetrix/database/migrations/)
- `create_tenants_table`: Multi-tenant support.
- `create_pages_table`: Booking pages (slug, title, config).
- `create_availability_rules_table`: Availability logic (days, hours).
- `create_bookings_table`: Appointments with status, customer data.
- `create_appointment_types_table`: Services duration/price.
- `create_integrations_table`: Tokens for Google/Outlook/Stripe.

### Phase 3: Backend Implementation
#### [NEW] [API Routes](file:///d:/Projetos/meetrix/routes/api.php)
- Auth routes (Login, Register).
- Dashboard routes (CRUD Pages, Bookings).
- Public routes (Get Page Config, Create Booking).

#### [NEW] [Controllers & Services](file:///d:/Projetos/meetrix/app/Http/Controllers/)
- `PageController`: Manage booking pages.
- `BookingController`: Handle availability checks and booking creation.
- `NotificationService`: Send Emails/SMS (Laravel Queues).

### Phase 4: Frontend Implementation
#### [NEW] [Views & Components](file:///d:/Projetos/meetrix/resources/js/)
- **Layouts**: `AdminLayout` (Sidebar), `PublicLayout` (Booking Page).
- **Pages**:
    - `Onboarding`: 6-step wizard.
    - `Dashboard`: Stats, Bookings List.
    - `PageEditor`: Live preview editor (What, Who, When).
- **Booking Flow**: Public facing calendar and form.

### Phase 5: Integrations & Payments
- **Stripe**: Payment intent creation & webhook handling.
- **Calendars**: integration logic (Google/Outlook adapters).

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