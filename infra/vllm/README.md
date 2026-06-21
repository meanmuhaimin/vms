# Gatekeeper vLLM Runtime

Phase 3 local model orchestration scaffold for the Gatekeeper Guard Agent.

Start when Docker, NVIDIA runtime, and model access are available:

```bash
cd infra/vllm
docker compose up -d
```

The OpenAI-compatible endpoint is exposed at `http://localhost:8001/v1/chat/completions` and served as model `vms-gatekeeper`.
