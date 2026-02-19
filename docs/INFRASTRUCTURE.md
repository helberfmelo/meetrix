# Infrastructure & Deployment

## Repository
- **URL**: [https://github.com/helberfmelo/meetrix/](https://github.com/helberfmelo/meetrix/)

## Production Environment (HostGator / OpenTS Host)
- **Production URL**: [https://meetrix.opentshost.com/](https://meetrix.opentshost.com/)
- **Server Path**: `/home1/opents62/public_html/meetrix/` (Standard HostGator mapping)
- **Deployment Method**: FTP via GitHub Actions

## CI/CD Secrets
The following secrets are configured in the GitHub Repository for deployment:
- `FTP_HOST`
- `FTP_USER`
- `FTP_PASSWORD`

## Database (Local Development)
- **User**: `meetrix_user`
- **Password**: `meetrix_password`
- **Root Password**: `carbonos`
- **Database**: `meetrix_db`
- **Port**: `3306`

## Database (Production)
- **Host**: `localhost`
- **Database**: `opents62_meetrix`
- **User**: `opents62_meetrix`
- **Password**: `Ok2oR3CVvMz7SJCW5o`

## Master Admin Credentials (Production)
- **URL**: `https://meetrix.opentshost.com/login`
- **User**: `admin@meetrix.pro`
- **Password**: `MeetrixMaster2026!#` (Set during Phase 5 evolution)

## Project Evolution
### 2026-02-19: Initial Footers & Admin Setup
- Updated footers to institutional style.
- Created Master Admin user via temporary setup route.
- Verified production URL: `https://meetrix.opentshost.com/`.

## Master Admin Credentials (Production)
- **URL**: `https://meetrix.opentshost.com/login`
- **User**: `admin@meetrix.pro`
- **Password**: `MeetrixMaster2026!#` (Set during Phase 5 evolution)

> **SECURITY NOTE**: In the server environment, these credentials must be stored in a secure `.env` file and **NEVER** hardcoded in the application code.
