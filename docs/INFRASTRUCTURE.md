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
- **Password**: `MeetrixMaster2026Sovereign!#` (Set during Phase 5 evolution)

- **Last Update**: 2026-02-19
- **Aesthetic**: Premium Sovereign (Zinc-950/Meetrix-Orange).
- **Auth Recovery**: Master Admin provisioned via `2026_02_19_221420_ensure_master_admin_user.php` migration.
- **Exclude Rules**: The `.agent/` folder is EXCLUDED from server sync via `.gitignore` to maintain server performance and workflow integrity.

## Master Admin (Sovereign Node):
  - Email: `admin@meetrix.pro`
  - Password: `MeetrixMaster2026Sovereign!#`
  - Descrição: Acesso total ao sistema com flag `is_super_admin` ativa.

> [!CAUTION]
> The `.agent/` folder contains local workflow definitions. It is critical that this folder is not uploaded to production to avoid exposing dev scripts.
