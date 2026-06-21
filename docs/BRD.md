To save this fully updated Business Requirements Document (BRD) as a text file, copy the raw markdown text blocks below and save it as a `.txt` file (e.g., `VMS_BRD_v1.6.txt`) using any text editor like Notepad, TextEdit, or VS Code.

```text
================================================================================
BUSINESS REQUIREMENTS DOCUMENT (BRD)
================================================================================
Project: NextGen Supervised Mobile Visitor Management System (VMS)
Document Version: 1.6  
Date: June 2026  
Status: Baseline Draft  

--------------------------------------------------------------------------------
TABLE OF CONTENTS
--------------------------------------------------------------------------------
1. Introduction
2. Business Background
3. Problem Statement
4. Business Objectives
5. Project Scope
6. Stakeholders
7. Current Business Process (AS-IS)
8. Future Business Process (TO-BE)
9. Functional Requirements
10. Non-Functional Requirements
11. Business Rules
12. User Roles
13. Data Requirements
14. Integration Requirements
15. Security Requirements
16. Reporting Requirements
17. Acceptance Criteria
18. Risks & Assumptions
19. Appendices

--------------------------------------------------------------------------------
1. INTRODUCTION
--------------------------------------------------------------------------------
The purpose of this Business Requirements Document (BRD) is to specify the 
functional, non-functional, and operational requirements for the deployment 
of the NextGen Visitor Management System (VMS). This system transitions facility 
entry management away from traditional pen-and-paper tracking or completely 
unverified self-service terminal kiosks, moving to a secure, human-verified, 
mobile-first process.

--------------------------------------------------------------------------------
2. BUSINESS BACKGROUND
--------------------------------------------------------------------------------
Managing external foot traffic across modern corporate spaces, multi-tenant 
commercial centers, and event venues requires balancing corporate hospitality 
with strict safety controls. Historically, organizations logged guests using 
physical books or unattended tablets. As operations scale and data compliance 
regulations tighten worldwide, a modernized technical infrastructure is required 
to verify identities while maintaining smooth lobby performance.

--------------------------------------------------------------------------------
3. PROBLEM STATEMENT
--------------------------------------------------------------------------------
The current reliance on traditional paper logbooks or completely unguided 
self-service kiosks introduces significant operational vulnerabilities:
* Security Vulnerabilities: Manual logs allow visitors to enter false names, 
  spoof details, or bypass identity validation.
* Lobby Bottlenecks: Data entry performed entirely by a front-desk receptionist 
  or unguided typing creates extensive wait times during peak morning arrival 
  windows.
* Compliance Risks: Physical paper logs display private visitor data openly to 
  anyone standing at the counter, directly violating data privacy frameworks 
  such as GDPR and CCPA.

--------------------------------------------------------------------------------
4. BUSINESS OBJECTIVES
--------------------------------------------------------------------------------
* Operational Efficiency: Offload 90% of data entry workloads onto the visitor’s 
  own smartphone, reducing front-desk handling times to under 30 seconds per guest.
* Human Oversight: Eliminate unverified self-check-ins by enforcing a physical 
  gatekeeper review step at the lobby counter before facility access is granted.
* Data Integrity & Privacy: Securely log personal data, execute optical 
  document parsing safely, and adhere to global privacy frameworks via automated 
  retention and data masking lifecycles.

--------------------------------------------------------------------------------
5. PROJECT SCOPE
--------------------------------------------------------------------------------
In Scope:
* Zero-install Mobile Web App (https://checkin.yourdomain.com) for visitor 
  self-registration.
* A Mobile Number Verification Engine (OTP via GOWA / SMS) for first-time visitors.
* An interactive digital Office Directory and Wayfinding module integrated 
  directly within the visitor's mobile interface.
* Counter-anchored dynamic or static QR interaction layers.
* Real-time desktop dashboard for Receptionists and Security Guards to oversee 
  live counter lanes and view incoming arrivals.
* Automated notification pipelines via SMS, Email, and GOWA (Go WhatsApp API 
  REST gateway) with deep-linked host approval portals.
* A backend GOWA Warming-Up Configuration Engine to safely throttle and pace 
  messages sent from new accounts.
* Integration with smart card readers at the reception counter to read and 
  parse MyKad data directly into the system.
* Direct network driver integration for localized front-desk thermal badge printers.

Out of Scope:
* Workplace collaboration chat tools (e.g., Slack, Microsoft Teams).
* Physical turnstile or biometric access gate hardware integration (Deferred to 
  Phase 2).
* Employee time, attendance, and payroll tracking modules.

--------------------------------------------------------------------------------
6. STAKEHOLDERS
--------------------------------------------------------------------------------
* Executive Sponsor: Funds the project and ensures alignment with corporate 
  real estate and safety milestones.
* Facilities & Operations Manager: Oversees lobby traffic throughput, physical 
  front-desk layout changes, and badge hardware procurement.
* Information Security (InfoSec) Officer: Validates data encryption, network 
  isolation, Identity Provider (IdP) integration, and compliance rules.
* Front-Desk Receptionists & Guards: The primary operational end-users 
  executing daily check-in verifications and crisis management.
* Internal Employees (Hosts): Receive incoming guest notifications over 
  WhatsApp/GOWA and authorize entries remotely from their mobile devices or 
  workstations.

--------------------------------------------------------------------------------
7. CURRENT BUSINESS PROCESS (AS-IS)
--------------------------------------------------------------------------------
1. The visitor arrives at the lobby and waits in line at the main counter.
2. The receptionist manually asks for the visitor's name, company, and host name.
3. The receptionist manually types these details into a local spreadsheet or 
   writes them down in a paper logbook.
4. The receptionist calls the internal employee (Host) via desk phone to confirm 
   the appointment.
5. If the host answers, the visitor is given a generic, handwritten badge and 
   permitted to enter.
6. Upon leaving, the visitor must return to the desk to be manually crossed 
   off the list.

--------------------------------------------------------------------------------
8. FUTURE BUSINESS PROCESS (TO-BE)
--------------------------------------------------------------------------------
[ENTRY] -> Step 01: Scan Counter QR -> Step 02: Mobile OTP Gate -> Mobile Self Check-In OR Counter MyKad Scan
                                                                                                 |
 [EXIT] <- Step 06: Scan Exit QR <- Step 05: Meeting + Wayfinding Navigation <- Step 03 & 04: Host Release <-

On-Site Arrival Phase:
1. Arrival: The visitor enters the lobby and approaches a specific counter lane.
2. Step 01 (Scan ID/QR): The visitor scans a desk-anchored onboarding QR to 
   launch the registration view.
3. Step 02 (OTP Verification Gate): The visitor enters their mobile phone 
   number and passes an encrypted 6-digit OTP verification constraint delivered 
   via GOWA or SMS.
4. Step 02 (Check-in Form & Data Source Processing): 
   * Option A (Standard Mobile Web OCR): The visitor manually fills in their 
     personal details and email details within the web form, then snaps a clear 
     photo of their physical ID or Passport. The web app extracts complementary 
     verification details using OCR.
   * Option B (MyKad Native Chip Read): Alternatively, Malaysian citizens 
     present their physical MyKad. The receptionist inserts it into the counter 
     smart card reader, instantly pulling verified government data into the 
     active workspace.
5. Verification Gate: The receptionist cross-references the physical 
   credentials with the data on their desktop dashboard, then clicks 
   [Verify & Release].
6. Step 03 (Notification Sent): The system pushes an automated API payload 
   through the GOWA service, delivering a real-time instant message directly to 
   the Host's WhatsApp account.
7. Step 04 (Host Approves): The host clicks the deep link in their WhatsApp 
   message, selecting the exact Meeting Location details. A physical badge 
   prints behind the receptionist's counter, and the receptionist hands it 
   to the guest.
8. Step 05 & 06 (Meeting, Wayfinding Navigation & Check-out): The visitor's 
   mobile interface dynamically switches to a digital pass view featuring an 
   Office Directory Map and Wayfinding route pointing directly to their 
   authorized meeting location. Upon completion, the visitor checks out by 
   scanning their mobile digital pass or printed badge at an exit lane reader.

--------------------------------------------------------------------------------
9. FUNCTIONAL REQUIREMENTS
--------------------------------------------------------------------------------
9.1 Mobile Self-Registration Interface
* FR-1.1: The mobile web application must launch immediately upon scanning a 
  counter QR code without requiring an app store download.
* FR-1.2: The web application must mandate fields for the visitor to manually 
  fill in their Personal Details (Full Name, Company) and Email Address details.
* FR-1.3: The web application must request device camera access to capture a 
  clear photo of a physical ID or passport page.
* FR-1.4: The system must process the uploaded ID via an automated OCR engine 
  to parse and cross-verify document criteria against the visitor's form entries.

9.2 MyKad Hardware Integration Layer
* FR-1.5: The receptionist desktop dashboard must interface with connected 
  PC/SC-compliant smart card readers.
* FR-1.6: Upon insertion of a Malaysian MyKad, the desktop client must extract 
  data fields without manual typing: Full Name (as per MyKad), MyKad Number 
  (IC), Gender, and the embedded digital photo bitmap.
* FR-1.7: The system must bind the extracted MyKad profile text to the active 
  visitor profile workspace automatically within < 1.5 seconds of insertion.

9.3 Receptionist Supervision Layer
* FR-2.1: The system must display a live incoming dashboard feed tracking 
  "In-Progress" mobile sessions and raw hardware reader inputs grouped by desk lane.
* FR-2.2: The system must prevent any notifications from being sent to hosts 
  until the receptionist triggers a manual validation override command 
  [Verify & Release].

9.4 Host Notification Routing
* FR-3.1: Upon receptionist clearance, the system must parse the employee 
  directory to pull the host's primary mobile phone number.
* FR-3.2: The system must fire a structural POST request to the internal GOWA 
  endpoint (/send/message) containing the visitor summary details and a 
  localized approval action link.

9.5 Mobile One-Time Password (OTP) Engine
* FR-4.1: The system must mandate mobile number validation before granting 
  access to the visitor details registration form.
* FR-4.2: The system must generate a cryptographically random, numerical 
  6-digit OTP token with a tight time-to-live (TTL) expiration window of 
  3 minutes (180 seconds).
* FR-4.3: The backend must route the OTP token as a priority message via 
  GOWA (WhatsApp) or fallback standard SMS gateway.

9.6 Interactive Office Directory & Meeting Location Module
* FR-5.1: The system must include a centrally managed Office Directory database 
  that stores building levels, department zones, floor maps, and individual 
  conference room assets (e.g., "Meeting Room 3A, Level 12").
* FR-5.2: During the host approval phase (FR-3.2), the host must explicitly 
  select or assign a Meeting Location from the verified asset dropdown list.
* FR-5.3: Upon host approval, the visitor's active mobile web layout must 
  dynamically expose a digital Wayfinding Screen showing the exact destination 
  room, tower floor, and basic spatial access directions from the main lobby counter.
* FR-5.4 (Public Directory View): The mobile web landing page must feature an 
  accessible search directory enabling guests to look up general public 
  departments or facility zones without compromising restricted zone structural 
  blueprints.

--------------------------------------------------------------------------------
10. NON-FUNCTIONAL REQUIREMENTS
--------------------------------------------------------------------------------
10.1 Performance & Scalability
* NFR-1.1: Real-time data synchronization between the visitor's smartphone 
  input or MyKad smart card reader and the receptionist's dashboard interface 
  must complete within a sub-second (< 1000ms) processing window.
* NFR-1.2: Map asset delivery and wayfinding renders on the visitor's phone 
  must open within < 2.0 seconds from the event confirmation trigger.

10.2 Reliability & Fault Tolerance
* NFR-2.1: If local network access drops, the receptionist dashboard must fall 
  back to a localized web-cache queue mode, preserving entries and syncing them 
  back to the cloud database once connectivity returns.

--------------------------------------------------------------------------------
11. BUSINESS RULES
--------------------------------------------------------------------------------
* BR-1.1 (Escalation Window): If a host fails to respond via the interactive 
  approval portal within seven (7) minutes of initial check-in, the system must 
  automatically escalate the alert to the host's designated backup contact.
* BR-1.2 (Denied Status Action): If a host triggers a [DENY ENTRY] response 
  command, the receptionist dashboard must silently flag that visitor profile 
  with a red status indicator, and the visitor's mobile interface must display 
  a generic prompt directing them to speak with the desk officer.
* BR-1.3 (GOWA Safe-Pacing & Rate Throttling): When a new WhatsApp number 
  instance is registered in the system, it must be flagged with a status of 
  "Warming Up". The system will enforce a progressive sending tier limit over 
  its first 14 days of live production activity.
* BR-1.4 (Burst Protection Spacing): To avoid triggering automated pattern 
  detection, the system must inject a variable artificial delay of 2,000ms to 
  5,000ms (randomized) between concurrent GOWA API messages if multiple 
  check-ins trigger simultaneously.
* BR-1.5 (OTP Generation Resend Limit): A visitor can request an OTP "Resend 
  Code" a maximum of three (3) times within a single check-in session. If 
  exceeded, the visitor's mobile interface will block further attempts and 
  instruct them to proceed with a manual MyKad/Passport check-in directly via 
  the receptionist.
* BR-1.6 (Location Access Enforcement): A visitor’s digital pass must only 
  reflect maps and directories associated with the explicit building sector 
  housing their assigned meeting room asset. Global floor layouts of 
  administrative security zones must remain hidden.

--------------------------------------------------------------------------------
12. USER ROLES
--------------------------------------------------------------------------------
The application enforces strict Role-Based Access Control (RBAC) profiles:

* System Administrator: Global configuration control, facility structure/floor 
  map schema updates, security setting updates, full user provisioning, and raw 
  log system auditing.
* Lobby Receptionist: Live workspace dashboard access, manual verification 
  control, MyKad reader triggers, printing management, and check-in/out override tools.
* Security Officer: Real-time building occupancy views, emergency roll-call list 
  compilation broken down by floor/meeting room layout, and watchlist monitoring.
* Internal Host: Multi-channel communication receipt controls, meeting room 
  booking allocation, and mobile action portals.

--------------------------------------------------------------------------------
13. DATA REQUIREMENTS
--------------------------------------------------------------------------------
* DR-1.1 (Required Core Schema Fields): Every visitor log record must capture 
  and map the following schema attributes: Unique Visitor ID, Full Name, Email 
  Address, Company Name, Captured Selfie Image/MyKad Photo BLOB, Verified 
  Identity Source Flag (OCR vs. MyKad Chip), Target Host Employee ID, Assigned 
  Meeting Location Asset ID (Floor/Room Reference), Check-In Verification 
  Timestamp, Host Approval Timestamp, and Checked-Out Logged Timestamp.
* DR-1.2 (Encrypted Key Indexes): Identification document values and parsed 
  identity numbers must be encrypted at the column level within storage volumes 
  using AES-256 standard protocols.

--------------------------------------------------------------------------------
14. INTEGRATION REQUIREMENTS
--------------------------------------------------------------------------------
* IR-1.1 (Identity Provider System Integration): The platform must execute 
  nightly unidirectional synchronization with corporate directories (Active 
  Directory / Okta) via secure SCIM APIs to keep host records, reporting lines, 
  and mobile numbers up to date.
* IR-1.2 (GOWA WhatsApp API Gateway & Warm-up Schedule): The VMS backend must 
  route JSON payloads to a containerized GOWA multi-device deployment. The 
  system database must maintain a Warm-up Multi-Tier Schedule that limits 
  total outbound messages per instance as detailed below:
  - Days 1 - 3: Max 50 messages / day. Fallback route via SMS.
  - Days 4 - 7: Max 150 messages / day. Fallback route via SMS.
  - Days 8 - 14: Max 300 messages / day. Fallback route via SMS.
  - Day 15+: Unlimited / Standard Mode. Shifts fully to real-time webhook throttling.
* IR-1.3 (MyKad Smart Card Reader Client Drivers): The local reception desktop 
  shell application must interface via a low-level WinSCard DLL or a Chrome 
  hardware bridge extension to communicate directly with USB smart card 
  peripheral hardware.
* IR-1.4 (Hardware Printer Interfaces): The desktop dashboard layer must 
  communicate directly via localized network sockets with thermal badge 
  printing devices (Zebra/Brother standard developer drivers).
* IR-1.5 (Facility Asset Management Sync): The VMS mapping engine must optionally 
  integrate with third-party meeting space booking APIs (e.g., Microsoft 
  Outlook Room Calendars or Google Workspace Resources) to cross-verify meeting 
  location asset names and availability states in real time.
* IR-1.6 (OTP Messaging Routing Client): The VMS backend engine must utilize 
  specialized messaging endpoints (/send/otp) configured inside GOWA to handle 
  higher priority routing cues.

--------------------------------------------------------------------------------
15. SECURITY REQUIREMENTS
--------------------------------------------------------------------------------
* SR-1.1 (Data Retention Compliance): To maintain compliance with personal data 
  protection frameworks (such as PDPA, GDPR), all personally identifiable 
  information (PII)—including scanned document images, selfies, MyKad photos, 
  and parsed ID details—must be obfuscated or permanently purged from active 
  tables after 90 days of inactivity, unless flagged by security.
* SR-1.2 (In-Transit Security): All transactional connections across visitor 
  mobile phones, host devices, hardware reader APIs, wayfinding engine updates, 
  and admin screens must be forced over secure TLS 1.3 protocol layers.

--------------------------------------------------------------------------------
16. REPORTING REQUIREMENTS
--------------------------------------------------------------------------------
* RR-1.1 (Emergency Roll-Call Ledger): The system must feature a single-click 
  emergency report option that compiles and exports a clean PDF listing every 
  un-cleared visitor currently inside the facility boundaries, categorized 
  strictly by Current Assigned Floor / Meeting Location.
* RR-1.2 (Asset Utilization Analytics): The system must track room metric values 
  detailing which conference rooms or company directory sectors register the 
  highest density of external guest visits over a rolling 30-day index.

--------------------------------------------------------------------------------
17. ACCEPTANCE CRITERIA
--------------------------------------------------------------------------------
* AC-1 (Supervised Holding Block): Given a visitor fills out their data via 
  their phone; When they hit submit; Then the mobile web pass must lock, and it 
  must not alert the host until the receptionist clicks verify.
* AC-2 (MyKad Native Read Acceleration): Given a visitor presents a valid 
  physical Malaysian identity card; When the receptionist inserts the token 
  into the USB smart card reader; Then the dashboard must automatically parse 
  out name, photo, and IC details without manual text entry.
* AC-3 (Automated Badge Printing): Given a pending receptionist-cleared 
  visitor; When the targeted host presses [APPROVE] on their WhatsApp 
  deep-link portal; Then the local counter printer must immediately spit out 
  the thermal security badge.
* AC-4 (Mobile Number Authenticity - OTP): Given a new visitor lands on the 
  check-in web application; When they type their mobile phone number and click 
  "Send Code"; Then they must receive a unique 6-digit code via WhatsApp/SMS, 
  and the system must reject any form entries until that exact token is verified on-screen.
* AC-5 (Contextual Wayfinding Display): Given a visitor has successfully 
  completed check-in and the host has approved the meeting location room code; 
  When the digital pass renders on the visitor's smartphone browser; Then it 
  must automatically surface the dynamic structural route link to that specific 
  target meeting location.

--------------------------------------------------------------------------------
18. RISKS & ASSUMPTIONS
--------------------------------------------------------------------------------
Risks:
* WhatsApp Spam Trigger via Initial High-Volume Bursting: Deploying a brand-new 
  corporate phone number onto GOWA and immediately routing morning check-in 
  alerts through it will trigger instant automated algorithmic account locks. 
  Mitigation: The phased GOWA Warming-Up schedule controls traffic exposure 
  during initialization.
* Inaccurate Floor Asset Mapping Changes: Physical remodeling or naming swaps of 
  conference rooms without notifying administrators can lead to misdirected 
  visitors through outdated mobile wayfinding maps. Mitigation: Provide simple 
  UI dashboard canvas override utilities directly inside the admin console to 
  update corporate structures within minutes.
* MyKad Smart Card Reader Connection Failures: Unplugged USB cables or physical 
  chip corruption on worn identity cards can interrupt the card reader interface. 
  Mitigation: Ensure the mobile web app QR check-in flow remains fully available 
  as an alternative fallback entry lane if hardware errors occur.

Assumptions:
* All internal hosts possess active corporate accounts linked to smartphones 
  capable of receiving WhatsApp notifications via standard cellular coverage or 
  facility Wi-Fi networks.
* The physical reception counters are outfitted with compatible PC/SC USB smart 
  card reader devices alongside standard label printers.

--------------------------------------------------------------------------------
19. APPENDICES
--------------------------------------------------------------------------------
Appendix A: Key Glossary Definitions
* VMS: Visitor Management System.
* OCR: Optical Character Recognition.
* GOWA: Go WhatsApp API Gateway framework.
* MyKad: Government-issued National Smart Identity Card of Malaysia.
* PII: Personally Identifiable Information.
* RBAC: Role-Based Access Control.
* Meeting Location / Office Directory: The static structure index representing 
  authorized, bookable spatial locations inside the administrative facility borders.

Appendix B: Verification Step Mockup
The following schematic details the dual-screen synchronization process that 
occurs during Step 02 of the workflow:

VISITOR'S PHONE SCREEN                         RECEPTIONIST'S DASHBOARD
+-----------------------------+               +--------------------------------------+
|                             |               | Lane 1: Jane Doe (Acme Corp)         |
|  ⏳ VERIFYING WITH          |               | [Photo ID Match]  [Selfie Match]     |
|     RECEPTIONIST...         |               |                                      |
|                             |               |  Action:                             |
|  Please present your        |======(LAN)====>  [✖ Reject]    [✔ Verify & Release]   |
|  physical ID to the officer |               |                                      |
|  at the counter to complete |               | Clicking Release fires the alert     |
|  your check-in.             |               | to the Host instantly (Step 03).     |
+-----------------------------+               +--------------------------------------+
================================================================================

```