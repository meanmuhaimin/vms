#!/usr/bin/env python3
"""Provision VMS JetStream streams from streams.json.

Requires a running NATS server and the optional nats-py package:
    python -m pip install nats-py
"""

from __future__ import annotations

import asyncio
import json
import os
from pathlib import Path


MAX_ATTEMPTS = 30
RETRY_SECONDS = 2


async def main() -> None:
    try:
        from nats.aio.client import Client as NATS
    except ImportError as exc:
        raise SystemExit("Missing dependency: install nats-py before provisioning streams.") from exc

    nats_url = os.getenv("VMS_NATS_URL", "nats://localhost:4222")
    streams_path = Path(__file__).with_name("streams.json")
    stream_specs = json.loads(streams_path.read_text(encoding="utf-8"))

    nc = NATS()

    for attempt in range(1, MAX_ATTEMPTS + 1):
        try:
            await nc.connect(nats_url)
            break
        except Exception as exc:
            if attempt == MAX_ATTEMPTS:
                raise SystemExit(f"Could not connect to NATS at {nats_url}: {exc}") from exc

            print(f"waiting for NATS at {nats_url} ({attempt}/{MAX_ATTEMPTS})")
            await asyncio.sleep(RETRY_SECONDS)

    async def jetstream_request(subject: str, payload: dict[str, object]) -> dict[str, object]:
        message = await nc.request(subject, json.dumps(payload).encode("utf-8"), timeout=5)
        response = json.loads(message.data.decode("utf-8"))

        error = response.get("error")
        if isinstance(error, dict):
            description = error.get("description", "unknown JetStream API error")
            code = error.get("code", "unknown")
            raise RuntimeError(f"JetStream API error {code}: {description}")

        return response

    async def stream_exists(name: str) -> bool:
        try:
            await jetstream_request(f"$JS.API.STREAM.INFO.{name}", {})
            return True
        except RuntimeError as exc:
            if "not found" in str(exc).lower():
                return False
            raise

    for attempt in range(1, MAX_ATTEMPTS + 1):
        try:
            for spec in stream_specs:
                name = str(spec["name"])
                if await stream_exists(name):
                    await jetstream_request(f"$JS.API.STREAM.UPDATE.{name}", spec)
                    print(f"updated stream {name}")
                else:
                    await jetstream_request(f"$JS.API.STREAM.CREATE.{name}", spec)
                    print(f"created stream {name}")
            break
        except Exception as exc:
            if attempt == MAX_ATTEMPTS:
                raise SystemExit(f"Could not provision JetStream streams: {exc}") from exc

            print(f"waiting for JetStream readiness ({attempt}/{MAX_ATTEMPTS}): {exc}")
            await asyncio.sleep(RETRY_SECONDS)

    await nc.drain()


if __name__ == "__main__":
    asyncio.run(main())
