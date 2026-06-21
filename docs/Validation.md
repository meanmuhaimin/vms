# Runtime Validation

Use `scripts/validate.sh` as the project-wide validation entry point.

It performs:

- Static project structure checks.
- Python syntax checks for Phase 3 agents.
- Telemetry pacing smoke test.
- Laravel install/migration/test checks when `php` and `composer` are installed.
- Local bridge build when `dotnet` is installed, including user-local installs from `~/.dotnet`.
- Docker Compose stack config validation when `docker` is installed.

Current environment limitation:

- `python3` is available.
- `php` is not installed.
- `composer` is not installed.
- `dotnet` is installed user-locally under `~/.dotnet` when `dotnet-install.sh` has been run; bridge build validation passes with SDK 8.0.422.
- Docker CLI is installed and Docker Compose stack validation passes with a user-local Compose plugin.
- Docker daemon access requires adding the WSL user to the `docker` group and restarting the WSL session.
- Passwordless `sudo` is not available, so toolchain installation cannot be completed non-interactively here.

Laravel 11 note:

- Composer currently blocks Laravel 11 installs by default because Packagist reports security advisories for the Laravel 11 line.
- This project keeps Laravel 11 to match the SDD request and sets Composer audit `block-insecure` to `false` so dependencies can install.
- Run `composer audit` after install and plan a Laravel 12 upgrade before production hardening if advisory-free Laravel framework packages are required.

Expected command:

```bash
bash scripts/validate.sh
```

After installing PHP/Composer/.NET/Docker on the target machine, rerun the same command to execute the previously skipped runtime checks.

The validation runner uses `database/validation.sqlite` for Laravel migration checks so local validation does not require a running PostgreSQL server. Production/runtime defaults remain PostgreSQL in `.env.example`.

## WSL Setup

Run this inside WSL with your sudo password:

```bash
sudo apt-get update
sudo apt-get install -y php-cli php-xml php-mbstring php-sqlite3 php-pgsql php-curl php-zip composer docker.io
```

If Docker is installed without Docker Compose support, install the compose plugin or legacy binary from your WSL package sources.

For user-local Docker Compose plugin installation without sudo:

```bash
mkdir -p "$HOME/.docker/cli-plugins"
curl -fL https://github.com/docker/compose/releases/download/v5.1.4/docker-compose-linux-x86_64 -o "$HOME/.docker/cli-plugins/docker-compose"
chmod +x "$HOME/.docker/cli-plugins/docker-compose"
```

To allow Docker daemon access from WSL, run with your sudo password and restart WSL:

```bash
sudo usermod -aG docker "$USER"
```

Then rerun:

```bash
bash scripts/validate.sh
```

.NET SDK may require Microsoft package feed setup if `dotnet-sdk-8.0` is unavailable from your WSL apt sources. Alternatively, install without sudo:

```bash
curl -fsSL https://dot.net/v1/dotnet-install.sh -o /tmp/dotnet-install.sh
bash /tmp/dotnet-install.sh --channel 8.0 --install-dir "$HOME/.dotnet"
```
