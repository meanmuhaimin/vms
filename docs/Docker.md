# Docker Runtime

The project can run the server-side stack through Docker Compose.

Services included:

- `api`: Laravel 11 API.
- `postgres`: PostgreSQL database.
- `redis`: cache/queue backend.
- `nats`: NATS JetStream event bus.
- `nats-provision`: one-shot stream provisioning job.
- `telemetry-agent`: deterministic GOWA pacing agent.
- `gatekeeper-vllm`: optional GPU-backed vLLM runtime.
- `gatekeeper-agent`: optional Gatekeeper consumer/publisher agent.

The local bridge is intentionally not part of the main Docker stack because production MyKad access depends on Windows `WinSCard.dll`, local USB smart card readers, and reception-printer network access.

## Start Core Stack

```bash
docker compose up --build
```

Laravel API:

```text
http://localhost:8000
```

NATS monitor:

```text
http://localhost:8222
```

## Start With GPU Gatekeeper

Requires NVIDIA Container Toolkit, GPU access, and Hugging Face model access.

```bash
export HUGGING_FACE_HUB_TOKEN=your_token
docker compose --profile gpu up --build
```

Alternatively:

```bash
docker compose -f docker-compose.yml -f docker-compose.gpu.yml up --build
```

## Common Commands

Run migrations manually:

```bash
docker compose exec api php artisan migrate
```

Run tests:

```bash
docker compose exec api php artisan test
```

Stop stack:

```bash
docker compose down
```

Reset data volumes:

```bash
docker compose down -v
```
