# NextGen Visitor Management System

NextGen VMS is a supervised, mobile-first Visitor Management System for secure facility check-ins. It combines a Laravel API, local Windows hardware bridge, NATS-based agent mesh, MyKad smart card integration, badge printing, OTP verification, host approvals, wayfinding, and automated visitor PII pruning.

## Components

- `app/`, `routes/`, `database/`: Laravel 11 API, models, migrations, and tests.
- `local-bridge/`: .NET 8 Windows bridge for MyKad PC/SC reads, offline queueing, and raw socket badge printing.
- `agents/`: Python Gatekeeper and telemetry pacing agents.
- `infra/nats/`: NATS JetStream stream definitions and provisioning scripts.
- `infra/vllm/`: GPU-backed vLLM scaffold for the Gatekeeper Guard model runtime.
- `docker/`, `docker-compose.yml`: Containerized API, Redis, PostgreSQL, NATS, and agent services.
- `docs/`: BRD, SDD, task status, Docker guide, and validation notes.

## Requirements

- PHP 8.2+
- Composer
- Python 3
- .NET SDK 8 for the local bridge
- Docker and Docker Compose for the containerized stack
- PostgreSQL and Redis for non-Docker runtime

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

Run the API locally:

```bash
php artisan serve
```

Run the Docker stack:

```bash
docker compose up --build
```

API endpoint:

```text
http://localhost:8000
```

NATS monitor:

```text
http://localhost:8222
```

## Validation

Use the project validation runner:

```bash
bash scripts/validate.sh
```

The runner checks project structure, Python agent syntax, telemetry pacing, Laravel install/migrations/tests, local bridge build, and Docker Compose configuration when the required tools are available.

## Key API Areas

- OTP request lifecycle: `/api/v1/auth/otp-request`
- Host approval action webhook: `/api/v1/approval/action`
- Wayfinding routes and public location metadata
- Scheduled visitor PII pruning via `vms:prune-visitor-pii`

## Documentation

- `docs/BRD.md`: Business requirements.
- `docs/SDD.md`: System design details.
- `docs/Tasks.md`: Implementation phases and completion status.
- `docs/Docker.md`: Docker runtime guide.
- `docs/Validation.md`: Validation prerequisites and command details.
