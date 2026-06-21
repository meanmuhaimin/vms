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
    await nc.connect(nats_url)
    js = nc.jetstream()

    for spec in stream_specs:
        config = StreamConfig(**spec)
        try:
            await js.stream_info(config.name)
            await js.update_stream(config)
            print(f"updated stream {config.name}")
        except Exception:
            await js.add_stream(config)
            print(f"created stream {config.name}")

    await nc.drain()


if __name__ == "__main__":
    asyncio.run(main())
