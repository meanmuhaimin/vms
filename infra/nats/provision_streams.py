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
        from nats.js.api import StreamConfig
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

    js = nc.jetstream()

    def is_not_found(exc: Exception) -> bool:
        value = str(exc).lower()
        return exc.__class__.__name__ == "NotFoundError" or "not found" in value

    def is_already_exists(exc: Exception) -> bool:
        value = str(exc).lower()
        return "already" in value and "stream" in value

    for attempt in range(1, MAX_ATTEMPTS + 1):
        try:
            for spec in stream_specs:
                config = StreamConfig(**spec)
                try:
                    await js.stream_info(config.name)
                    await js.update_stream(config)
                    print(f"updated stream {config.name}")
                except Exception as exc:
                    if not is_not_found(exc):
                        raise

                    try:
                        await js.add_stream(config)
                        print(f"created stream {config.name}")
                    except Exception as add_exc:
                        if not is_already_exists(add_exc):
                            raise

                        await js.update_stream(config)
                        print(f"updated stream {config.name}")
            break
        except Exception as exc:
            if attempt == MAX_ATTEMPTS:
                raise SystemExit(f"Could not provision JetStream streams: {exc}") from exc

            print(f"waiting for JetStream readiness ({attempt}/{MAX_ATTEMPTS}): {exc}")
            await asyncio.sleep(RETRY_SECONDS)

    await nc.drain()


if __name__ == "__main__":
    asyncio.run(main())
