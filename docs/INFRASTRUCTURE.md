# Infrastructure & Deployment

## Repository
- **URL**: [https://github.com/helberfmelo/meetrix/](https://github.com/helberfmelo/meetrix/)

## Production Environment (HostGator / OpenTS Host)
- **Production URL**: [https://meetrix.opentshost.com/](https://meetrix.opentshost.com/)
- **Server Path**: `/home1/opents62/public_html/meetrix/`
- **Deployment Method**: FTP via GitHub Actions

## Database (Production)
- **Host**: `localhost`
- **Database**: `opents62_meetrix`
- **Current State**: CRITICAL. Out of sync with migrations. Requires `migrate:fresh` due to manual migration reordering causing table conflicts.

## Blockers (IMPORTANT)
1. **PHP Execution**: The server currently serves raw PHP source code as text. This was triggered by an attempt to force a PHP handler in `.htaccess`. Reverting this is priority #1.
2. **Database Deadlock**: `bookings` table already exists in production but its migration was reordered. A nuclear reset (`migrate:fresh`) is required in production to align with the stable local schema.

## Master Admin Credentials (Production)
- **URL**: `https://meetrix.opentshost.com/login`
- **User**: `admin@meetrix.pro`
- **Password**: `MeetrixMaster2026Sovereign!#`

- **Last Update**: 2026-02-20
