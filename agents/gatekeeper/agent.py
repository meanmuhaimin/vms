#!/usr/bin/env python3
"""Gatekeeper Guard Agent scaffold.

Consumes registration events and publishes security clearance or flagged events.
Requires optional runtime dependencies: nats-py and an OpenAI-compatible vLLM endpoint.
"""

from __future__ import annotations

import asyncio
import json
import os
import urllib.request
from pathlib import Path
from typing import Any


REGISTRATION_SUBJECT = "vms.events.registration.submitted"
CLEARANCE_SUBJECT = "vms.events.security.clearance"
FLAGGED_SUBJECT = "vms.events.security.flagged"


def load_config() -> dict[str, Any]:
    path = Path(os.getenv("VMS_GATEKEEPER_CONFIG", "infra/vllm/gatekeeper.config.json"))
    return json.loads(path.read_text(encoding="utf-8"))


def evaluate_with_vllm(config: dict[str, Any], event: dict[str, Any]) -> dict[str, Any]:
    body = {
        "model": config["model"],
        "temperature": config["execution_temperature"],
        "messages": [
            {"role": "system", "content": config["system_prompt"]},
            {"role": "user", "content": json.dumps(event, separators=(",", ":"))},
        ],
    }

    request = urllib.request.Request(
        config["model_endpoint"],
        data=json.dumps(body).encode("utf-8"),
        headers={"Content-Type": "application/json"},
        method="POST",
    )

    with urllib.request.urlopen(request, timeout=30) as response:
        payload = json.loads(response.read().decode("utf-8"))

    content = payload["choices"][0]["message"]["content"]
    return json.loads(content)


async def main() -> None:
    try:
        from nats.aio.client import Client as NATS
    except ImportError as exc:
        raise SystemExit("Missing dependency: install nats-py before running the Gatekeeper agent.") from exc

    config = load_config()
    nats_url = os.getenv("VMS_NATS_URL", "nats://localhost:4222")
    nc = NATS()
    await nc.connect(nats_url)
    js = nc.jetstream()

    async def handle(message: Any) -> None:
        event = json.loads(message.data.decode("utf-8"))
        determination = evaluate_with_vllm(config, event)
        subject = FLAGGED_SUBJECT if "FLAG_TERMINAL" in determination.get("action", []) else CLEARANCE_SUBJECT
        await js.publish(subject, json.dumps(determination).encode("utf-8"))
        await message.ack()

    await js.subscribe(REGISTRATION_SUBJECT, durable="gatekeeper-guard", cb=handle, manual_ack=True)
    print(f"{config['agent_id']} listening on {REGISTRATION_SUBJECT}")

    while True:
        await asyncio.sleep(3600)


if __name__ == "__main__":
    asyncio.run(main())
