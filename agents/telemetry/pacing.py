#!/usr/bin/env python3
"""Telemetry & Pacing Controller Agent.

Applies GOWA warm-up limits and randomized dispatch spacing to outbound queues.
"""

from __future__ import annotations

import asyncio
import json
import os
import random
from dataclasses import dataclass
from datetime import UTC, datetime
from typing import Any


QUEUE_DEPTH_SUBJECT = "vms.events.gowa.outbound.queue_depth"
DISPATCH_SUBJECT = "vms.events.gowa.dispatch_command"


@dataclass(frozen=True)
class WarmupTier:
    start_day: int
    end_day: int | None
    daily_limit: int | None


WARMUP_TIERS = [
    WarmupTier(start_day=1, end_day=3, daily_limit=50),
    WarmupTier(start_day=4, end_day=7, daily_limit=150),
    WarmupTier(start_day=8, end_day=14, daily_limit=300),
    WarmupTier(start_day=15, end_day=None, daily_limit=None),
]


def active_tier(account_age_days: int) -> WarmupTier:
    for tier in WARMUP_TIERS:
        if account_age_days >= tier.start_day and (tier.end_day is None or account_age_days <= tier.end_day):
            return tier
    return WARMUP_TIERS[0]


def calculate_dispatch(event: dict[str, Any]) -> dict[str, Any]:
    queue_depth = int(event.get("queue_depth", 0))
    sent_today = int(event.get("sent_today", 0))
    account_age_days = int(event.get("account_age_days", 1))
    requested_batch_size = int(event.get("requested_batch_size", 1))
    tier = active_tier(account_age_days)

    if queue_depth <= 0:
        allowed_count = 0
    elif tier.daily_limit is None:
        allowed_count = min(queue_depth, requested_batch_size)
    else:
        remaining_today = max(tier.daily_limit - sent_today, 0)
        allowed_count = min(queue_depth, requested_batch_size, remaining_today)

    delay_ms = random.randint(2000, 5000) if allowed_count > 0 else None
    fallback_to_sms = allowed_count < min(queue_depth, requested_batch_size)

    return {
        "dispatch_id": event.get("dispatch_id"),
        "allowed_count": allowed_count,
        "delay_ms": delay_ms,
        "fallback_to_sms": fallback_to_sms,
        "tier_daily_limit": tier.daily_limit,
        "calculated_at": datetime.now(UTC).isoformat(),
    }


async def main() -> None:
    try:
        from nats.aio.client import Client as NATS
    except ImportError as exc:
        raise SystemExit("Missing dependency: install nats-py before running telemetry pacing.") from exc

    nats_url = os.getenv("VMS_NATS_URL", "nats://localhost:4222")
    nc = NATS()
    await nc.connect(nats_url)
    js = nc.jetstream()

    async def handle(message: Any) -> None:
        event = json.loads(message.data.decode("utf-8"))
        command = calculate_dispatch(event)
        await js.publish(DISPATCH_SUBJECT, json.dumps(command).encode("utf-8"))
        await message.ack()

    await js.subscribe(QUEUE_DEPTH_SUBJECT, durable="telemetry-pacing", cb=handle, manual_ack=True)
    print(f"Telemetry pacing listening on {QUEUE_DEPTH_SUBJECT}")

    while True:
        await asyncio.sleep(3600)


if __name__ == "__main__":
    asyncio.run(main())
