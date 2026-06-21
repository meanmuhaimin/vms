# Dokploy Deployment

This project includes `docker-compose.dokploy.yml` for Dokploy production-style deployments.

If you are new to Dokploy, the minimum setup is:

- Create a Compose app in Dokploy.
- Point it to this repository.
- Set the Compose file to `docker-compose.dokploy.yml`.
- Set the app domain to route to service `api` on port `8000`.
- Copy environment variables from `.env.dokploy.example`.
- Replace `APP_URL`, `APP_KEY`, and `DB_PASSWORD` with real values.

## Deployment Model

Dokploy should host:

- Laravel API
- PostgreSQL
- Redis
- NATS JetStream
- NATS stream provisioning job
- Telemetry pacing agent
- Optional Gatekeeper agent and vLLM runtime

The local Windows bridge should remain outside Dokploy. It depends on Windows `WinSCard.dll`, local USB MyKad readers, and reception-printer network access.

## Dokploy Setup

Create a new Dokploy Compose application using this repository and set the Compose file path to:

```text
docker-compose.dokploy.yml
```

Configure Dokploy to route HTTP traffic to the `api` service on port `8000`.

## Required Environment Variables

Set these in Dokploy before deploying:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=base64:replace_with_laravel_app_key
DB_DATABASE=vms
DB_USERNAME=vms
DB_PASSWORD=replace_with_strong_password
```

You can copy the same template from `.env.dokploy.example`.

Generate an app key locally if needed:

```bash
php artisan key:generate --show
```

## Optional Environment Variables

```env
APP_NAME=NextGen VMS
LOG_CHANNEL=stderr
OTP_TTL_SECONDS=180
OTP_RESEND_LIMIT=3
VMS_DEFAULT_PRINTER_ID=LOBBY_LANE_01_PRINTER
```

For GPU Gatekeeper deployments:

```env
HUGGING_FACE_HUB_TOKEN=replace_with_token
VMS_GATEKEEPER_MODEL=meta-llama/Meta-Llama-3-8B-Instruct
```

Enable the `gpu` profile only when the Dokploy host has NVIDIA GPU support and NVIDIA Container Toolkit configured.

## Services And Storage

The Dokploy Compose file uses named volumes for persistent state:

- `postgres_data`: PostgreSQL data.
- `redis_data`: Redis append-only persistence.
- `nats_data`: JetStream data.
- `api_storage`: Laravel storage directory.
- `api_cache`: Laravel bootstrap cache directory.
- `vllm_cache`: Hugging Face model cache for GPU deployments.

## Post-Deploy Checks

After deployment, verify:

- The `api` service starts successfully and runs migrations.
- Dokploy routes the configured domain to `api:8000`.
- `postgres`, `redis`, and `nats` health checks pass.
- `nats-provision` completes successfully.
- `telemetry-agent` stays running.

If using the reception local bridge, configure it to call the Dokploy HTTPS API URL.
