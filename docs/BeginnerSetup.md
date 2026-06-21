# Beginner Setup

This guide is the shortest path to run the system without understanding every internal component first.

## Pick One Setup Path

Use Docker if you are testing on your own computer.

Use Dokploy if you want to deploy the backend to a server with a domain name.

The Windows local bridge is only needed when you connect real MyKad readers or badge printers at the reception counter.

## Option 1: Run Locally With Docker

Requirements:

- Docker
- Docker Compose

Start the system:

```bash
docker compose up --build
```

Open the API:

```text
http://localhost:8000
```

Open the NATS monitor:

```text
http://localhost:8222
```

Stop the system:

```bash
docker compose down
```

Reset all local Docker data:

```bash
docker compose down -v
```

## Option 2: Run Locally Without Docker

Requirements:

- PHP 8.2+
- Composer
- PostgreSQL
- Redis

Install and prepare Laravel:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

Run the API:

```bash
php artisan serve
```

## Option 3: Deploy On Dokploy

Use the Dokploy Compose file:

```text
docker-compose.dokploy.yml
```

In Dokploy, route HTTP traffic to:

```text
api:8000
```

Copy environment variables from:

```text
.env.dokploy.example
```

Generate a real Laravel app key before deployment:

```bash
php artisan key:generate --show
```

If you do not have PHP locally, you can generate the key inside any Laravel/PHP container or create it from a machine that has Composer and this project checked out.

## Validate The Project

Run:

```bash
bash scripts/validate.sh
```

This checks the available tools on your machine and skips checks for tools that are not installed.

## What Runs Where

- Dokploy or Docker server: Laravel API, PostgreSQL, Redis, NATS, telemetry agent, optional Gatekeeper agent.
- Reception Windows PC: local bridge, MyKad reader, badge printer access.
- Visitor phone: mobile web check-in flow.
- Host phone or browser: approval action links.

## Common Problems

- If the API cannot connect to the database, check `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
- If Laravel says no application encryption key is set, set `APP_KEY` to a real `base64:` key.
- If Docker keeps old data, run `docker compose down -v` and start again.
- If the local bridge cannot read MyKad, run it on Windows with the smart card reader drivers installed.
- If badge printing fails, confirm the printer IP, port `9100`, and local network access from the reception PC.
