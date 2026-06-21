================================================================================
AGENTS.md: AI AGENT SYSTEM ARCHITECTURE
================================================================================
Project: NextGen Supervised Mobile Visitor Management System (VMS)
Document Version: 1.0  
Date: June 2026  
Status: Initial Draft  

--------------------------------------------------------------------------------
1. MULTI-AGENT SYSTEM OVERVIEW
--------------------------------------------------------------------------------
The system transitions traditional deterministic business flows into an 
event-driven multi-agent architecture. Agents coordinate over an asynchronous 
event mesh to process unstructured visitor data, flag anomalies, verify identity 
authenticity, balance delivery pipelines, and dynamic-route wayfinding requests.

Text-Based Multi-Agent Architecture Diagram:

                  +----------------------------------------------+
                  |               EVENT BUS (NATS)               |
                  +-------+--------+--------+--------+--------+--+
                          |        ^        |        ^        |
      +-------------------+        |        |        |        +-------------------+
      |                            |        |        |                            |
      v                            |        v        |                            v
+-----------------------+          |  +-----+--------+-----+                +-----+-----------------+
|   Gatekeeper Guard    |----------+  | Identity Discovery |                | Telemetry & Pacing    |
|   Agent (Llama-3b)    |             | Agent (Phi-3)      |                | Agent (Deterministic) |
+-----------------------+             +--------------------+                +-----------------------+
                                                ^
                                                | (Hardware Hook)
                                                v
                                      +--------------------+
                                      | MyKad Smart Card / |
                                      | OCR Extraction     |
                                      +--------------------+

--------------------------------------------------------------------------------
2. CORE AGENT SPECIFICATIONS
--------------------------------------------------------------------------------
2.1 Gatekeeper Guard Agent
* Persona / Role: Autonomous Lobby Safety Auditor & Risk Assessor.
* Operational Objective: Evaluate structural registration details against internal 
  watchlist criteria, historical anomalies, and open check-in queues to flags 
  patterns requiring localized intervention.
* Model Runtime Tier: Localized Sub-Network Deployment (Llama-3-8B-Instruct via 
  vLLM orchestration layer).
* Input Streams: vms.events.registration.submitted, vms.events.blacklist.snapshot.
* Output Channels: vms.events.security.clearance, vms.events.security.flagged.

Core Config Template:
  {
    "agent_id": "AGT-GATEKEEPER-01",
    "system_prompt": "You are the automated Gatekeeper Guard for a high-security corporate perimeter. Analyze structural input payloads for registration context anomalies (e.g., mismatching names, suspicious corporate affiliations, known high-risk entities). Issue a JSON determination containing 'risk_score' (0.0 to 1.0) and an explicit 'action' array containing [ALLOW, MONITOR, FLAG_TERMINAL]. Do not append markdown formatting wrapper structures.",
    "execution_temperature": 0.0
  }

2.2 Identity Discovery & Validation Agent
* Persona / Role: High-Fidelity OCR Data Extraction & Structural Document 
  Compliance Auditor.
* Operational Objective: Handle raw data arrays extracted from incoming 
  multi-format smartphone passport camera snaps or chip read outputs, sanitizing 
  and parsing individual identification records.
* Model Runtime Tier: Edge Acceleration Node (Phi-3-Mini-4k-Instruct execution 
  layer via ONNX runtime).
* Input Streams: vms.events.ocr.raw_payload.
* Output Channels: vms.events.identity.parsed.

2.3 Telemetry & Pacing Controller Agent
* Persona / Role: Adaptive Queue Load Balancer & GOWA Gateway Warm-up Controller.
* Operational Objective: Monitor active messaging velocity over outbound 
  communication pipelines, enforcing random dynamic artificial delay windows 
  between notifications to safely protect active numbers from automated meta-spam 
  network tracking flags.
* Model Runtime Tier: Hybrid Framework (Deterministic Rules Engine linked to a 
  Light-Weight Statistical Predictor Model).
* Input Streams: vms.events.gowa.outbound.queue_depth.
* Output Channels: vms.events.gowa.dispatch_command.

--------------------------------------------------------------------------------
3. COMMUNICATION PROTOCOLS & INTER-AGENT ORCHESTRATION
--------------------------------------------------------------------------------
The system utilizes an asynchronous event-driven layout built over a high-performance 
NATS JetStream messaging framework.

Sequence Flow:
Discovery Agent                     Gatekeeper Agent                    Telemetry Agent
       |                                   |                                   |
       |-- [Publishes Identity Parsed] --->|                                   |
       |   Topic: vms.events.id.parsed     |                                   |
       |                                   |-- [Evaluates Risk Matrix]         |
       |                                   |-- [Publishes Security Clearance]->|
       |                                   |   Topic: vms.events.sec.clear     |
       |                                   |                                   |-- [Calculates Delay Matrix]
       |                                   |                                   |-- [Dispatches Paced GOWA Payload]

Schema Definition: vms.events.identity.parsed
  {
    "$schema": "http://json-schema.org/draft-07/schema#",
    "title": "IdentityParsedEvent",
    "type": "object",
    "properties": {
      "transaction_id": { "type": "string", "format": "uuid" },
      "extracted_name": { "type": "string" },
      "document_type": { "type": "string", "enum": ["MYKAD", "PASSPORT", "OTHER"] },
      "confidence_score": { "type": "number", "minimum": 0.0, "maximum": 1.0 },
      "extraction_timestamp": { "type": "string", "format": "date-time" }
    },
    "required": ["transaction_id", "extracted_name", "document_type", "confidence_score", "extraction_timestamp"]
  }

--------------------------------------------------------------------------------
4. OPERATIONAL GUARDRAILS & CONTROL PARAMETERS
--------------------------------------------------------------------------------
To guarantee structural containment, predictable throughput, and absolute 
deterministic handling of edge data events, the following systemic constraints 
are strictly enforced across