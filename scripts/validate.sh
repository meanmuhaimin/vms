#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR" || exit 1

export PATH="$HOME/.dotnet:$PATH"

failures=0
skips=0

section() {
  printf '\n== %s ==\n' "$1"
}

run_check() {
  local name="$1"
  shift

  printf '%s... ' "$name"
  if "$@" >/tmp/vms-validate.out 2>/tmp/vms-validate.err; then
    printf 'PASS\n'
  else
    printf 'FAIL\n'
    sed 's/^/  /' /tmp/vms-validate.err
    failures=$((failures + 1))
  fi
}

run_optional_check() {
  local name="$1"
  shift

  printf '%s... ' "$name"
  if "$@" >/tmp/vms-validate.out 2>/tmp/vms-validate.err; then
    printf 'PASS\n'
    return 0
  fi

  printf 'FAIL\n'
  sed 's/^/  /' /tmp/vms-validate.err
  failures=$((failures + 1))
  return 1
}

skip_if_missing() {
  local binary="$1"
  local label="$2"

  if ! command -v "$binary" >/dev/null 2>&1; then
    printf '%s... SKIP (%s not installed)\n' "$label" "$binary"
    skips=$((skips + 1))
    return 1
  fi

  return 0
}

section "Static Project Checks"
run_check "Composer manifest present" test -f composer.json
run_check "Laravel routes present" test -f routes/api.php
run_check "Local bridge project present" test -f local-bridge/Vms.LocalBridge/Vms.LocalBridge.csproj
run_check "NATS stream config present" test -f infra/nats/streams.json
run_check "Docker Compose stack present" test -f docker-compose.yml

section "Python Agent Checks"
if skip_if_missing python3 "Python syntax compilation"; then
  run_check "Python syntax compilation" python3 -m py_compile \
    infra/nats/provision_streams.py \
    agents/gatekeeper/agent.py \
    agents/telemetry/pacing.py \
    agents/telemetry/test_pacing.py
  run_check "Telemetry pacing smoke test" python3 -c "import sys; sys.path.insert(0, 'agents/telemetry'); from pacing import calculate_dispatch; c=calculate_dispatch({'queue_depth':80,'requested_batch_size':80,'sent_today':0,'account_age_days':1}); assert c['allowed_count']==50; assert c['fallback_to_sms'] is True; assert 2000 <= c['delay_ms'] <= 5000"
fi

section "Laravel Runtime Checks"
if skip_if_missing php "PHP runtime" && skip_if_missing composer "Composer runtime"; then
  if run_optional_check "Composer install" composer install --no-interaction; then
    mkdir -p database
    : > database/validation.sqlite
    run_check "Laravel migrations" env APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= DB_CONNECTION=sqlite DB_DATABASE=database/validation.sqlite php artisan migrate:fresh --force
    run_check "Laravel tests" php artisan test
  else
    printf 'Laravel migrations... SKIP (composer install failed)\n'
    printf 'Laravel tests... SKIP (composer install failed)\n'
  fi
fi

section "Local Bridge Runtime Checks"
if skip_if_missing dotnet "dotnet runtime"; then
  run_check "Local bridge build" dotnet build local-bridge/Vms.LocalBridge/Vms.LocalBridge.csproj -p:EnableWindowsTargeting=true
fi

section "Agent Infrastructure Runtime Checks"
if skip_if_missing docker "Docker runtime"; then
  if docker compose version >/dev/null 2>&1; then
    run_check "Docker Compose stack validation" docker compose config
  elif command -v docker-compose >/dev/null 2>&1; then
    run_check "Docker Compose stack validation" docker-compose config
  else
    printf 'Docker Compose stack validation... SKIP (Docker Compose plugin/binary not installed)\n'
    skips=$((skips + 1))
  fi
fi

rm -f /tmp/vms-validate.out /tmp/vms-validate.err

if [ "$failures" -gt 0 ]; then
  printf '\nValidation completed with %s failure(s).\n' "$failures"
  exit 1
fi

if [ "$skips" -gt 0 ]; then
  printf '\nValidation completed without executable check failures. Skipped checks: %s.\n' "$skips"
else
  printf '\nValidation completed without executable check failures.\n'
fi
