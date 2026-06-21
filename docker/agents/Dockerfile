FROM python:3.12-slim

WORKDIR /app

RUN pip install --no-cache-dir nats-py

COPY agents ./agents
COPY infra ./infra

ENV PYTHONUNBUFFERED=1

CMD ["python", "agents/telemetry/pacing.py"]
