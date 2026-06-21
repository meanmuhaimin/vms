# NATS JetStream Topology

Phase 3 event bus configuration for VMS agent coordination.

Streams:

- `VMS_REGISTRATION`: visitor registration submissions.
- `VMS_IDENTITY`: OCR/chip extraction and parsed identity events.
- `VMS_SECURITY`: blacklist snapshots, security clearance, and flags.
- `VMS_GOWA`: outbound WhatsApp/GOWA queue depth and dispatch commands.

Provisioning after NATS is running:

```bash
python3 -m pip install nats-py
VMS_NATS_URL=nats://localhost:4222 python3 infra/nats/provision_streams.py
```
