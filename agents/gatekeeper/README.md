# Gatekeeper Guard Agent

Consumes `vms.events.registration.submitted` and publishes either:

- `vms.events.security.clearance`
- `vms.events.security.flagged`

Runtime requirements:

- NATS JetStream
- `nats-py`
- vLLM OpenAI-compatible endpoint from `infra/vllm`

Run:

```bash
python3 -m pip install nats-py
python3 agents/gatekeeper/agent.py
```
