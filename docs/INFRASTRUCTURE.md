# Infrastructure & Deployment

## Repository
- **URL**: [https://github.com/helberfmelo/meetrix/](https://github.com/helberfmelo/meetrix/)

## Production Environment (HostGator)
- **Server Path**: `/home1/opents62/public_html/meetrix/`
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

> **SECURITY NOTE**: In the server environment, these credentials must be stored in a secure `.env` file and **NEVER** hardcoded in the application code.
