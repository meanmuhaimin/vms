# Telemetry & Pacing Controller

Consumes `vms.events.gowa.outbound.queue_depth` and publishes `vms.events.gowa.dispatch_command`.

Rules implemented:

- Days 1-3: max 50 messages/day.
- Days 4-7: max 150 messages/day.
- Days 8-14: max 300 messages/day.
- Day 15+: standard mode.
- Dispatchable batches receive randomized spacing from 2000ms to 5000ms.
- Overflow traffic is marked for fallback SMS routing.

Run:

```bash
python3 -m pip install nats-py
python3 agents/telemetry/pacing.py
```

Unit tests can run with `pytest` when installed:

```bash
python3 -m pytest agents/telemetry
```
