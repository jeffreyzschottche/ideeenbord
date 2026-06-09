# Deployment

ideeenbord.nl runs as two containers (Nuxt 4 frontend + Laravel 12 backend) plus
MySQL, orchestrated with `docker-compose.prod.yml`. Images are built and pushed to
GitHub Container Registry (GHCR) by GitHub Actions on every push to `main`, then
deployed to the VPS over SSH.

## Pipeline

`.github/workflows/deploy.yml`:

1. **build** – builds `Dockerfile.backend` and `Dockerfile.frontend` (context
   `ideeenbord-mvp/`) and pushes them to:
   - `ghcr.io/<owner>/ideeenbord-backend:{latest,<sha>}`
   - `ghcr.io/<owner>/ideeenbord-frontend:{latest,<sha>}`
2. **deploy** – `scp`s `docker-compose.prod.yml` to the server, then SSHes in and
   runs `docker compose pull && up -d` with `IMAGE_TAG=<sha>`.

## Required GitHub Secrets

Set these in **Settings → Secrets and variables → Actions**:

| Secret        | Description                                              |
| ------------- | ------------------------------------------------------- |
| `SSH_HOST`    | VPS hostname or IP                                      |
| `SSH_USER`    | SSH user (e.g. `deploy`)                                |
| `SSH_KEY`     | Private SSH key (PEM) for that user                     |
| `SSH_PORT`    | SSH port (usually `22`)                                 |
| `DEPLOY_PATH` | Absolute path on the VPS holding the compose + env files |

`GITHUB_TOKEN` is provided automatically and is used to push/pull from GHCR.

## One-time server setup

```bash
# 1. Install Docker + compose plugin
# 2. Create the deploy dir (matches DEPLOY_PATH)
mkdir -p /opt/ideeenbord && cd /opt/ideeenbord

# 3. Create the three env files (see *.env.example in the repo root)
cp /path/to/repo/.env.example          .env            # MySQL creds + image refs
cp /path/to/repo/backend.env.example   backend.env     # Laravel config + AI keys
cp /path/to/repo/frontend.env.example  frontend.env    # Nuxt public config
#    ...then fill in real values (APP_KEY, DB_PASSWORD, MAIL_*, AI keys, ...)

# 4. Make the deploy user able to read GHCR (login once; CI re-logs in each run)
echo <PAT_with_read:packages> | docker login ghcr.io -u <owner> --password-stdin
```

Generate `APP_KEY` with `php artisan key:generate --show` (or in any PHP container).

Point your existing reverse proxy (nginx/Caddy/Traefik) at:

- `ideeenbord.nl`     → `127.0.0.1:3000` (frontend)
- `api.ideeenbord.nl` → `127.0.0.1:8000` (backend)

## Manual deploy / rollback

```bash
cd /opt/ideeenbord
IMAGE_TAG=<sha> docker compose -f docker-compose.prod.yml pull
IMAGE_TAG=<sha> docker compose -f docker-compose.prod.yml up -d
```

Migrations run automatically on backend start (`RUN_MIGRATIONS=true`). The queue
worker and scheduler run inside the backend container via supervisor
(`RUN_QUEUE` / `RUN_SCHEDULER`).
