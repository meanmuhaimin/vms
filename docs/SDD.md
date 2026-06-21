================================================================================
SYSTEM DESIGN DOCUMENT (SDD)
================================================================================
Project: NextGen Supervised Mobile Visitor Management System (VMS)
Document Version: 1.0  
Date: June 2026  
Status: Initial Draft  

--------------------------------------------------------------------------------
1. INTRODUCTION & ARCHITECTURAL PRINCIPLES
--------------------------------------------------------------------------------
This System Design Document (SDD) establishes the technical design, system 
architecture, database schemas, and interface integrations for the NextGen 
Supervised Mobile Visitor Management System (VMS). 

Architectural Core Principles:
* Decoupled Micro-Engines: Separation of client application spaces, asynchronous 
  notification systems, and local hardware interface loops.
* Stateless Web Layer: Client web applications process state transitions via 
  payload passing rather than active session preservation.
* Security-First Enclaves: Strict separation of Personally Identifiable 
  Information (PII) data with granular column-level encryption keys.
* Local Resilience (Fail-Forward): Front-desk workflows fallback gracefully to 
  web-caches if upstream cloud databases drop connectivity.

--------------------------------------------------------------------------------
2. HIGH-LEVEL SYSTEM ARCHITECTURE
--------------------------------------------------------------------------------
The overall system structure is composed of three foundational layers:
1. Client Edge (Visitor & Host Interfaces): Comprising the zero-install mobile 
   web app used by visitors and interactive notification environments (WhatsApp 
   via GOWA).
2. Supervised Core Server Layer: Hosting business engines, security filters, 
   scheduling maps, and analytics tracking databases.
3. Local Hardware Interaction Layer: Bridging desktop dashboard clients to USB 
   peripherals (MyKad smart card readers) and localized network hardware 
   (thermal printers).

Text-Based Architecture Diagram:

   +------------------------+          +-------------------------+
   |   Visitor Mobile App   |          | Host Notification Portal|
   |   (Zero-Install Web)   |          |  (GOWA Deep-Link Web)   |
   +-----------+------------+          +------------+------------+
               |                                    |
               | (HTTPS / TLS 1.3)                  | (HTTPS)
               v                                    v
   +-------------------------------------------------------------+
   |                  API Gateway & Reverse Proxy                |
   +------------------------------+------------------------------+
                                  |
                                  v
   +-------------------------------------------------------------+
   |                    Supervised Core Engines                  |
   |  - Mobile Registration Engine   - Host Routing Engine       |
   |  - OTP & Authentication Gate    - Wayfinding/Directory Map  |
   +------------------------------+------------------------------+
                                  |
         +------------------------+------------------------+
         | (Encrypted Internal Network)                     |
         v                                                 v
+------------------+                              +------------------+
| Relational DB    |                              | Cache & Queue    |
| (PostgreSQL Core)|                              | (Redis State/OTP)|
+------------------+                              +------------------+
                                  ^
                                  | (HTTPS Sync Loop)
                                  v
   +-------------------------------------------------------------+
   |                Reception Desktop Local Bridge               |
   |   - Live Lane Monitoring Feed  - WinSCard DLL Driver Link   |
   +------------------------------+------------------------------+
                                  |
         +------------------------+------------------------+
         | (USB / Local PC/SC)                              | (Local IP / Socket)
         v                                                 v
+------------------+                              +------------------+
| MyKad USB Smart  |                              | Front-Desk Badge |
| Card Reader      |                              | Thermal Printer  |
+------------------+                              +------------------+

--------------------------------------------------------------------------------
3. COMPONENT DESIGN & SYSTEM SEQUENCE
--------------------------------------------------------------------------------
The lifecycle of an ad-hoc checkout visitor moves systematically between the 
mobile app, local PC drivers, and cloud verification webhooks.

Visitor             Receptionist           Local Bridge / MyKad        Core Backend Engine          Host (WhatsApp)
   |                     |                          |                          |                          |
   |-- [01. Scan QR] --->|                          |                          |                          |
   |-- [02. Input Phone]------------------------------------------------------>|                          |
   |<-- [03. Send OTP (GOWA/SMS)]----------------------------------------------|                          |
   |-- [04. Submit OTP Code]-------------------------------------------------->|                          |
   |                     |                          |                          |                          |
   |   (Form Unlocked)   |                          |                          |                          |
   |-- [05. Enters Data + Form Submit]---------------------------------------->|                          |
   |                     |                          |                          |                          |
   |   (Phone Locks)     |                          |                          |                          |
   |                     |-- [06. Insert MyKad] --->|                          |                          |
   |                     |                          |-- [07. Native Chip Read]-|                          |
   |                     |                          |<-- [08. Payload Extract]-|                          |
   |                     |<-- [09. Populate Lane] --|                          |                          |
   |                     |                                                     |                          |
   |                     |-- [10. Click Verify & Release]--------------------->|                          |
   |                     |                                                     |-- [11. Dispatch Alert]-->|
   |                     |                                                     |                          |-- [12. Open Link]|
   |                     |                                                     |                          |-- [13. Approve] ->|
   |                     |<-- [14. Webhook Trigger Badge Print Job]------------|--------------------------|
   |                     |-- [15. Hand Badge to Guest]                         |                          |

--------------------------------------------------------------------------------
4. DATABASE SCHEMA & DATA DICTIONARY
--------------------------------------------------------------------------------
4.1 Data Architecture Principles
* AES-256 Storage Enclaves: Government identification string identifiers 
  (e.g., MyKad IC numbers, passport values) undergo cryptographic encryption 
  before insertion into long-term tables.
* Automated Pruning Lifecycles: Personal Identification Data is targeted for 
  null-masking or absolute purging 90 days following transaction closeout to 
  remain fully aligned with PDPA and GDPR compliance standards.

4.2 Database Tables Entity Definition

Table: visitors
Defines core visitor structural details captured via form entry or native chips.
* visitor_id (UUID, Primary Key): Unique inner tracking identifier string.
* phone_number (VARCHAR(20), Not Null, Indexed): Country-coded sanitized telephone.
* email_address (VARCHAR(255), Not Null): Target digital notification address.
* full_name (VARCHAR(255), Not Null): Given complete identifier string.
* company_name (VARCHAR(255), Nullable): Origin corporate or group entity text.
* id_doc_type (VARCHAR(20), Not Null): [MYKAD, PASSPORT, OTHER_ID]
* encrypted_id_number (BYTEA, Not Null): AES-256 encrypted storage sequence.
* created_at (TIMESTAMP, Default Current): Initial database entry instant.

Table: visitor_logs
Tracks state changes, timestamps, approval hooks, and contextual floor locations.
* log_id (UUID, Primary Key): Session historical entry sequence tracking key.
* visitor_id (UUID, Foreign Key -> visitors.visitor_id): Links log trace to visitor.
* host_employee_id (VARCHAR(100), Not Null, Indexed): Matches target employee index.
* assigned_location_id (VARCHAR(50), Not Null): Spatial identifier code for room maps.
* verification_channel (VARCHAR(30), Not Null): [MOBILE_OCR, CHIP_READ]
* status (VARCHAR(30), Not Null): [PENDING_DESK, PENDING_HOST, APPROVED, DENIED]
* checkin_submit_time (TIMESTAMP, Nullable): Instant client submits payload block.
* desk_release_time (TIMESTAMP, Nullable): Instant desk officer clicks verify.
* host_approval_time (TIMESTAMP, Nullable): Instant employee clicks approval link.
* checkout_time (TIMESTAMP, Nullable): Instant guest scans exit reader array.

Table: otp_ledger
High-speed execution repository tracking short-lived mobile security codes.
* otp_id (BIGSERIAL, Primary Key): Autoincrement indexing sequence wrapper.
* phone_number (VARCHAR(20), Not Null, Indexed): Mobile phone reference string.
* otp_token_hash (VARCHAR(64), Not Null): SHA-256 hash representation of token.
* expiration_time (TIMESTAMP, Not Null): Active system expiration limit window.
* attempts_count (INTEGER, Default 0): Tracks failures to prevent brute-forcing.

--------------------------------------------------------------------------------
5. INTERFACE CONTROL & API SPECIFICATIONS
--------------------------------------------------------------------------------
All system transactions interact through JSON structures over TLS 1.3 pipelines.

5.1 One-Time Password Issuance Endpoint
* HTTP Method: POST
* Path: /api/v1/auth/otp-request
* Request Payload Framework:
  {
    "phone_number": "+60123456789",
    "client_timestamp": "2026-06-21T18:26:00Z"
  }
* Response Payload Structure (Success):
  {
    "status": "SUCCESS",
    "message": "OTP dispatch action executed successfully via GOWA routing channels.",
    "session_ttl_seconds": 180
  }

5.2 Host Action Hook Webhook Link
* HTTP Method: POST
* Path: /api/v1/approval/action
* Request Payload Framework:
  {
    "log_id": "8f3b9c2a-41d6-4e8c-90b2-7a4c8e1f3d5a",
    "host_action": "APPROVE",
    "assigned_location_id": "CONF_ROOM_12B",
    "auth_token": "crypto_signature_hash_string"
  }
* Response Payload Structure (Success):
  {
    "status": "PROCESSED",
    "action_applied": "APPROVED",
    "print_trigger_dispatched": true,
    "target_printer_id": "LOBBY_LANE_01_PRINTER"
  }

--------------------------------------------------------------------------------
6. HARDWARE INTEGRATION LAYER SPECIFICATIONS
--------------------------------------------------------------------------------
6.1 Native MyKad Smart Card Data Reader Communication Interface
The localized reception desktop application client runs a low-latency driver 
engine that leverages standard WinSCard.dll application programming patterns. 

Reader Interaction Loop Chain:
1. The system listens via long-polling hooks for connection events 
   (SCardEstablishContext).
2. When a card is detected, the card reader executes a standard terminal 
   authentication sequence using internal default keys (APDU arrays).
3. The desktop bridge selects the dedicated MyKad application profile pathway 
   (A0 00 00 00 74 4A 4D 41 53 20 49 44) to initialize memory scanning procedures.
4. It fires programmatic binary read commands to sequential storage block addresses 
   to parse out plain text data properties and extracts the image bit blocks 
   matching the visitor's physical identity card photo.

6.2 Thermal Badge Network Socket Printing Execution
When a host confirms an approval event action hook, the system engine routes raw 
binary printing commands using standard Socket pipelines (TCP Raw Port 9100) 
straight to the front-desk printing device. The dashboard client application 
converts input data patterns into native monochrome command configurations matching 
layout structures similar to the template detailed below:

^XA
^FO50,50^A0N,40,40^FD VISITOR DIGITAL PASS^FS
^FO50,110^A0N,28,28^FD NAME: Jane Doe^FS
^FO50,150^A0N,28,28^FD COMP: Acme Corp^FS
^FO50,190^A0N,28,28^FD HOST: John Smith^FS
^FO50,230^A0N,28,28^FD ROOM: Conf Room 12B^FS
^FO450,70^BQN,2,6^FDQA,8f3b9c2a-41d6-4e8c-90b2-7a4c8e1f3d5a^FS
^XZ

--------------------------------------------------------------------------------
7. SECURITY, WAYFINDING & NETWORK FAILBACK LOGIC
--------------------------------------------------------------------------------
7.1 GOWA Outbound WhatsApp Throttling Control
To prevent programmatic numbers from hitting sudden carrier network spam flags, 
the backend enforces structural send spacing buffers:
* Artificial Send Delays: When multiple concurrent visitor check-ins trigger 
  automated tracking workflows, the background scheduler spaces individual 
  notification dispatches by a randomized micro-duration window spanning 
  between 2000ms and 5000ms.
* Warming-Up Send Limits: Fresh deployment numbers systematically enforce 
  standard daily outbound limits (e.g., maximum 50 tracking actions daily during 
  initial 72 production hours), shifting additional messaging automatically over 
  standard backup SMS pipelines if threshold limits are exceeded.

7.2 Wayfinding Route Mapping Constraint Rules
Upon successful gate clearance, the system engine restricts map generation templates 
based on the destination endpoint parameter values matching the host's room selection:
* The system cross-references the assigned room value (assigned_location_id) 
  against spatial infrastructure blueprint asset dictionaries.
* The active web app view isolates floor mapping configurations dynamically, 
  hiding unrelated structural vectors or higher security data levels from the 
  interactive guest route path display screen.

7.3 Interrupted Network Fallback Processing Matrix

[Local LAN Active?]
       |
       +---> YES: Route standard check-in events straight to Core APIs.
       |
       +---> NO: Fallback locally. Cache registration payloads within local database engines.
                 Queue printing tasks using standard backup visitor token numbers.
                 Flash warning indicators across the receptionist view.
                 Perform atomic push sync once network parameters pass ping sweeps.
================================================================================