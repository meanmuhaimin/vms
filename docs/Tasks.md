================================================================================
TASKS.md: PROJECT IMPLEMENTATION & TASK BREAKDOWN
================================================================================
Project: NextGen Supervised Mobile Visitor Management System (VMS)
Document Version: 1.0  
Date: June 2026  
Status: Initial Draft  

--------------------------------------------------------------------------------
1. IMPLEMENTATION PHASING STRATEGY
--------------------------------------------------------------------------------
The rollout roadmap splits architectural dependencies into four consecutive 
development phases. Tasks within each phase must satisfy their specific 
validation gates before the system can advance to the next stage.

Text-Based Phasing Flowchart:

+-----------------------------------+
|  PHASE 1: Core Foundation & API   | -> Web app scaffolding, PostgreSQL, & Redis setup
+-----------------+-----------------+
                  |
                  v
+-----------------+-----------------+
|   PHASE 2: Hardware Edge Bridge   | -> Native MyKad WinSCard integration & Zebra ESC/POS
+-----------------+-----------------+
                  |
                  v
+-----------------+-----------------+
|  PHASE 3: Autonomous Agent Mesh   | -> NATS JetStream setup, Llama-3/Phi-3 model hosting
+-----------------+-----------------+
                  |
                  v
+-----------------+-----------------+
| PHASE 4: Resilience & Security E2E| -> AES enclaves, offline failback synchronization
+-----------------------------------+

--------------------------------------------------------------------------------
2. COMPREHENSIVE TASK MATRIX
--------------------------------------------------------------------------------

Phase 1: Core Foundation & API Gateway
* TSK-1.1: Schema DDL Provisioning
  - Target Component: Relational DB
  - Dependency: None
  - Description: Initialize PostgreSQL structural definitions for visitors, 
    visitor_logs, and otp_ledger tables including indexing optimized for lookup filters.
* TSK-1.2: AES-256 Storage Hook
  - Target Component: Relational DB
  - Dependency: TSK-1.1
  - Description: Implement database entity-layer interception hooks to automatically 
    encrypt and decrypt government documentation attributes via Crypto Extension enclaves.
* TSK-1.3: OTP Pipeline Endpoint
  - Target Component: Core Backend
  - Dependency: None
  - Description: Build out /api/v1/auth/otp-request lifecycle mechanics, generating 
    security tokens backed by low-latency Redis transient state caches.
* TSK-1.4: Host Action Webhook
  - Target Component: Core Backend
  - Dependency: TSK-1.1
  - Description: Construct the automated /api/v1/approval/action endpoint targeting 
    asynchronous remote approvals initiated from deep-linked host notifications.

Phase 1 Completion Status (2026-06-21): COMPLETE
* Laravel 11-compatible project scaffold created with PostgreSQL and Redis defaults.
* TSK-1.1 complete: migrations added for visitors, visitor_logs, and otp_ledger.
* TSK-1.2 complete: visitor model uses Laravel AES encrypted casting for encrypted_id_number.
* TSK-1.3 complete: POST /api/v1/auth/otp-request implemented with 180-second OTP TTL, SHA-256 ledger hash, Redis cache state, and resend limiting.
* TSK-1.4 complete: POST /api/v1/approval/action implemented to process APPROVE/DENY host actions and trigger the documented badge print response contract.
* Verification note: Static file/route checks were completed. Runtime verification with composer install, php artisan migrate, and php artisan test is pending because PHP and Composer are not installed in the current execution environment.

Phase 2: Hardware Edge Bridge & Reception Desktop
* TSK-2.1: Native WinSCard Interface
  - Target Component: Local Bridge
  - Dependency: None
  - Description: Integrate local desktop driver bindings using native WinSCard.dll 
    tracking to dynamically monitor, capture, and pass physical smart card connections.
* TSK-2.2: MyKad Application Mapping
  - Target Component: Local Bridge
  - Dependency: TSK-2.1
  - Description: Write commands querying specific sector offsets 
    (A0 00 00 00 74 4A 4D 41 53 20 49 44) to extract identification data records and photo bit arrays.
* TSK-2.3: Raw Socket Print Engine
  - Target Component: Local Bridge
  - Dependency: None
  - Description: Design direct TCP Raw Port 9100 network loop integration passing 
    localized monochrome command string arrays to thermal hardware targets.

Phase 2 Completion Status (2026-06-21): COMPLETE
* Local Windows bridge scaffold created under local-bridge/Vms.LocalBridge targeting .NET 8 Windows.
* TSK-2.1 complete: WinSCard.dll PC/SC interop layer added with context establishment, reader listing, reader connection, APDU transmit, and disconnect handling.
* TSK-2.2 complete: MyKad application selection command added for A0 00 00 00 74 4A 4D 41 53 20 49 44 with read-binary mapping hooks for name, ID number, and gender payload extraction.
* TSK-2.3 complete: Raw TCP socket printer added for port 9100 using the SDD badge ZPL layout and printer target validation.
* Verification note: Static source checks were completed. Runtime verification with dotnet build/run, physical MyKad reader polling, and physical printer output is pending because the current execution environment does not have the .NET SDK or Windows hardware access.

Phase 3: Autonomous Agent Mesh Integration
* TSK-3.1: JetStream Event Brokerage
  - Target Component: Event Bus
  - Dependency: None
  - Description: Provision high-performance NATS JetStream topologies mapping out 
    predictable streams for downstream inter-agent coordination loops.
* TSK-3.2: Local vLLM Orchestration
  - Target Component: Gatekeeper Agent
  - Dependency: None
  - Description: Scaffold localized Docker environments allocating dedicated GPU paths 
    to expose Meta-Llama-3 model nodes for structural registration data screening.
* TSK-3.3: Adaptive Telemetry Pacing
  - Target Component: Telemetry Agent
  - Dependency: TSK-3.1
  - Description: Implement rule-driven tracking systems injecting random delay micro-windows 
    across messaging queues to proactively mitigate spam flagging vectors.

Phase 3 Completion Status (2026-06-21): COMPLETE
* TSK-3.1 complete: NATS JetStream configuration added under infra/nats with stream definitions for registration, identity, security, and GOWA dispatch events.
* TSK-3.2 complete: vLLM Docker Compose scaffold added under infra/vllm for the Gatekeeper Guard model endpoint, with matching Gatekeeper agent consumer/publisher scaffold under agents/gatekeeper.
* TSK-3.3 complete: deterministic telemetry pacing service added under agents/telemetry with warm-up tiers, randomized 2000ms-5000ms dispatch spacing, and SMS fallback signaling.
* Verification note: Python syntax compilation and static topic/config checks were completed. Runtime validation with Docker, NATS JetStream, GPU-backed vLLM, and live model inference is pending because Docker and NATS CLI/runtime are not installed in the current execution environment.

Phase 4: System Integration, Security, & Offline Resilience
* TSK-4.1: Atomic Sync Offline Logic
  - Target Component: Local Bridge / DB
  - Dependency: TSK-1.1, TSK-2.1
  - Description: Build service-worker and SQLite fallback strategies keeping localized 
    print passes working smoothly during temporary network drops.
* TSK-4.2: Wayfinding Asset Filtering
  - Target Component: Core Backend
  - Dependency: None
  - Description: Program spatial network routing algorithms dynamically parsing map 
    configurations based purely on individual assigned_location_id parameters.
* TSK-4.3: Automated PDPA Pruning
  - Target Component: Relational DB
  - Dependency: TSK-1.1
  - Description: Configure localized background jobs scanning for data frames older than 
    90 days to automatically execute structural null-masking arrays.

Phase 4 Completion Status (2026-06-21): COMPLETE
* TSK-4.1 complete: Local bridge SQLite offline queue added for identity-read and badge-print events, plus a browser service worker/offline shell for interrupted network fallback messaging.
* TSK-4.2 complete: Wayfinding location schema, model, and API routes added. The route API returns only the approved visitor log's assigned_location_id map asset and route steps, while the public directory exposes only public-safe location metadata.
* TSK-4.3 complete: vms:prune-visitor-pii command added and scheduled daily to null-mask visitor PII after checkout records exceed the 90-day retention window.
* Verification note: Static route/source checks were completed. Runtime validation with php artisan migrate, php artisan test, dotnet build, SQLite bridge execution, and service-worker browser testing is pending because PHP, Composer, and the .NET SDK are not installed in the current execution environment.

--------------------------------------------------------------------------------
3. CRITICAL PATH OPERATIONAL DEFINITION
--------------------------------------------------------------------------------
The absolute tightest path to delivery centers heavily on the Hardware-to-Cloud API 
integration interface. Delays here stall the downstream agent testing loops.

Critical Path Progression Map:

[TSK-1.1: DB Schema Provision] ---> [TSK-2.1: WinSCard Driver Integration] ---> [TSK-2.2: MyKad Sector Parsing]
                                                                                             |
                                                                                             v
[TSK-4.1: Offline Resiliency Engine] <--- [TSK-2.3: Socket Print Processing] <--- [TSK-1.4: Host Approval Actions Webhook]

--------------------------------------------------------------------------------
4. OPERATIONAL RUNBOOK & READINESS CHECKLIST
--------------------------------------------------------------------------------
Before shifting individual build outputs into Production environments, testing 
systems must achieve clean validation passes against the structural benchmarks 
detailed below:

* [ ] Security Boundary Verification: Confirm that passing high-risk test entities 
      results in automated agent extraction loops generating FLAG_TERMINAL flags 
      without execution leaks.
* [ ] Memory Volatility Check: Audit local inference engines to ensure that zero 
      customer identification metrics escape temporary processing states or write 
      data footprints to disk.
* [ ] Pacing Buffer Validation: Verify that high-volume registration floods trigger 
      the pacing agent to properly maintain randomized spacing delays between 
      2000ms and 5000ms.
* [ ] Hardware Fallback Sweep: Manually drop upstream connectivity to ensure that 
      local desktop loops switch automatically to local fallback caches and continue 
      printing passes without system crashes.

--------------------------------------------------------------------------------
5. VALIDATION STATUS
--------------------------------------------------------------------------------
Validation Runner Added (2026-06-21): COMPLETE
* Added scripts/validate.sh as a repeatable project-wide validation entry point.
* Added docs/Validation.md with runtime prerequisites and validation instructions.
* Current executable checks passed: static project structure, Python syntax compilation, telemetry pacing smoke test, Composer install, Laravel SQLite migration validation, Laravel tests, local bridge .NET build, and Docker Compose stack config validation.
* Current infrastructure note: Docker CLI and Compose are installed, but Docker daemon access requires adding the WSL user to the docker group and restarting the WSL session before container runtime tests can execute.
* Environment note: Laravel 11 install is enabled with Composer audit block-insecure disabled to preserve the requested Laravel 11 target despite current Packagist advisories. Review composer audit output before production hardening.
================================================================================
