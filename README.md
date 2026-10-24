<div align="center">

# AMSA AIU Election 2026

### Secure Digital Voting & Election Management Platform

**A web-based election system for the AMSA community at Albukhary International University**

PHP · MySQL · JavaScript · Responsive Web Interface

</div>

---

## Executive Overview

**AMSA AIU Election 2026** is a purpose-built digital election platform designed to manage an AMSA student leadership election from candidate preparation and voter registration through ballot submission, turnout oversight, and results presentation.

The system replaces manual ballot handling with a controlled, browser-based workflow. Authorized election personnel register eligible voters at the attendance counter and issue individual six-digit voting PINs. Voters authenticate with their Student ID and PIN, review the candidates, and submit their ballots through a guided interface. Administrators oversee election operations through a dedicated management portal, while a presentation-oriented results interface supports the final announcement ceremony.

The application uses a PHP backend, a MySQL relational database, JSON API endpoints, and responsive HTML, CSS, and JavaScript interfaces. Voting and administrative actions are processed server-side rather than relying on demonstration-only browser data.

## Project Objectives

- **Accessibility:** Deliver a straightforward voting experience across phones, tablets, laptops, and desktop browsers.
- **Eligibility control:** Restrict participation to voters registered by authorized election staff.
- **Ballot integrity:** Enforce one ballot per eligible voter per election round through server-side participation controls.
- **Ballot confidentiality:** Keep ballot selections separate from voter identity records in the application data model.
- **Operational efficiency:** Centralize candidates, voter credentials, election state, turnout information, and results management.
- **Transparent presentation:** Support election-day reporting and a formal, projector-friendly results reveal.

## Election Scope

The system is configured around five AMSA leadership positions:

| Position | Election category |
| --- | --- |
| President | Executive leadership |
| Vice President | Executive leadership |
| Secretary | Administration |
| Male Treasurer | Financial administration |
| Female Treasurer | Financial administration |

The seeded application initializes one main election round, the five positions, and a draft election state. It intentionally contains **no candidate records, voter accounts, or preloaded votes**. Genuine election data must be entered by authorized personnel before voting opens.

## Core Capabilities

### 1. Controlled Voter Onboarding

Voters do not create their own accounts. Election staff register them at the attendance counter, using their Student ID to establish an authorized voter record. The system issues a six-digit Voting PIN, which is handed directly to the voter. Administrative PIN regeneration supports credential recovery when necessary.

This supervised registration workflow helps prevent unrestricted sign-ups and makes voter eligibility an explicit administrative decision.

### 2. Authenticated Digital Voting

Voters enter their Student ID and Voting PIN to access the ballot. The voting experience is designed to be clear and responsive, with candidate information presented within the election interface. The backend records participation and prevents a voter from submitting an additional ballot for the same round.

The application separates voter participation data from the anonymous ballot tables. The stored ballot design does not include a direct voter foreign key, voter IP address, individual ballot timestamp, or voter receipt.

### 3. Candidate & Election Administration

The administrative portal provides the operational interface for preparing and running the election, including candidate management, portrait uploads, voter registration, turnout oversight, and election control. The application supports controlled election states so candidate preparation, registration, voting, and results publication occur in the intended order.

Candidate portrait uploads accept **JPG, PNG, and WebP** files up to **3 MB**.

### 4. Results, Ties & Runoff Support

The data model supports election rounds and tracks voter participation per round. The system includes tie-detection and runoff-related functionality to assist election officers when an additional round is necessary. Final winner publication and combined main/runoff reporting should be reviewed by authorized election officials before public announcement.

### 5. Ceremony-Ready Results Experience

The project includes a results presentation flow intended for the AMSA announcement ceremony. Its visual direction prioritizes candidate recognition, legible on-screen results, and a presentation experience suitable for projection. The application's design uses AMSA-inspired maroon, gold, and cream styling throughout its responsive interface.

## End-to-End Election Workflow

| Phase | Responsible party | System activity |
| --- | --- | --- |
| 01 — Preparation | Election administrators | Configure the election and review the five positions |
| 02 — Candidate setup | Election administrators | Add candidates and upload their photographs |
| 03 — Registration | Authorized election staff | Register eligible voters and issue voting PINs |
| 04 — Voting | Registered voters | Authenticate, review candidates, and submit one ballot |
| 05 — Monitoring | Election administrators | Track participation and supervise election status |
| 06 — Verification | Election officers | Review counts, ties, and any required runoff decisions |
| 07 — Announcement | Authorized presenters | Publish and present verified results |

**Operational principle:** Election results should only be released after the responsible officers have verified the final tally and authorized publication.

## Technical Architecture

The platform follows a straightforward browser–API–database architecture:

```text
┌─────────────────────────────────────────────────────┐
│                  WEB INTERFACES                     │
│  Public pages · Voter pages · Administrator pages   │
│           HTML · CSS · JavaScript · PHP              │
└─────────────────────────┬───────────────────────────┘
                          │ HTTP / JSON
                          ▼
┌─────────────────────────────────────────────────────┐
│                     PHP BACKEND                     │
│   Sessions · Authentication · CSRF · Validation      │
│       Election logic · Voting API · Upload API       │
└─────────────────────────┬───────────────────────────┘
                          │ PDO
                          ▼
┌─────────────────────────────────────────────────────┐
│                    MYSQL DATABASE                   │
│ Election rounds · Positions · Candidates · Voters   │
│         Participation records · Anonymous ballots  │
└─────────────────────────────────────────────────────┘
```

### Technology Stack

| Layer | Technology | Responsibility |
| --- | --- | --- |
| Frontend | HTML5, CSS3, JavaScript | Responsive voter and administrative interfaces |
| Server | PHP 8.1+ | Business logic, sessions, authentication, and API routing |
| Data storage | MySQL / MariaDB | Persistent relational election records |
| Database access | PHP PDO (MySQL driver) | Parameterized database operations |
| Local environment | XAMPP | Apache, PHP, and MySQL development stack |
| Deployment | Apache-based PHP hosting, including Hostinger | Web application and database hosting |

For deployment, **PHP 8.2 or newer** with the PDO MySQL extension is recommended.

### Repository Structure

```text
amsa-election/
├── index.php                  # Public entry point
├── admin/                    # Administrator-facing pages
├── user/                     # Voter-facing pages
├── api/
│   ├── index.php             # JSON API entry point
│   └── upload.php            # Candidate image uploads
├── app/
│   ├── bootstrap.php         # Database, sessions, auth, and CSRF
│   └── config.example.php    # Configuration template
├── database/
│   ├── create_database_local.sql
│   ├── schema.sql            # Relational database schema
│   └── seed.sql              # Initial election and positions
├── js/
│   ├── production.js         # Frontend–API integrations
│   └── shell.js              # Shared interface behavior
├── scripts/
│   └── create_admin.php      # CLI administrator creation
└── docs/
    ├── API.md                # API reference
    └── TESTING.md            # Testing and verification guide
```

The repository also contains the stylesheets, imagery, and other assets required by the page templates. The private `app/config.local.php` file is created locally and must not be committed.

## Local Installation — Windows / XAMPP

### Prerequisites

- XAMPP with Apache, PHP, and MySQL/MariaDB
- PHP 8.1 or newer, with PDO MySQL enabled
- A modern browser
- A terminal or PowerShell session for administrator setup

### 1. Place the application in the XAMPP web directory

Clone or copy the repository so the application files are located at:

```text
C:\xampp\htdocs\amsa-election\
```

The folder must contain `index.php`, `api/`, `app/`, `admin/`, `user/`, and `database/`.

Start **Apache** and **MySQL** using the XAMPP Control Panel.

### 2. Create and initialize the database

Open:

```text
http://localhost/phpmyadmin
```

Create the database `amsa_election` with the `utf8mb4_unicode_ci` collation, or execute:

```text
database/create_database_local.sql
```

Select the new database and import the SQL files in this exact order:

1. `database/schema.sql`
2. `database/seed.sql`

**Import `seed.sql` only once.** The seed creates the base AMSA election in draft, with registration closed, one main round, and the five required positions. It does not add real candidates or eligible voters.

### 3. Configure the application

Copy:

```text
app/config.example.php
```

to:

```text
app/config.local.php
```

Set the MySQL host, database name, username, and password in the local configuration. XAMPP commonly uses `root` with an empty password for local development; these credentials are not appropriate for a public deployment.

Generate a cryptographically random application secret in PowerShell:

```powershell
C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Use that output as the configuration's `secret` value. Keep `config.local.php` private and excluded from version control.

### 4. Create the initial administrator

Open PowerShell and execute:

```powershell
cd C:\xampp\htdocs\amsa-election
C:\xampp\php\php.exe scripts\create_admin.php electionadmin "Election Coordinator" super
```

Enter a unique administrator password of at least 12 characters when prompted. The project does **not** include a default administrator password.

### 5. Launch the system

Open the application:

```text
http://localhost/amsa-election/
```

Open the administrator login:

```text
http://localhost/amsa-election/admin/admin-login.php
```

Use the administrator account created in the previous step. Pages must be served through Apache/PHP; opening PHP files directly using `file://` will not run the application.

### 6. Prepare the election

While the election remains in draft, add legitimate candidate records for every position and verify names and portraits. Open registration through the administrative election controls, register eligible voters at the attendance counter, and distribute their PINs privately. Once the candidate roster and voter eligibility procedures have been checked, authorized administrators can open voting.

## Deployment — Apache / Hostinger

The system can be hosted on PHP/MySQL infrastructure, including Hostinger's Apache-based hosting environment.

1. Create a production MySQL database and database user through the hosting control panel.
2. Import `database/schema.sql` followed by `database/seed.sql`, once, into the production database.
3. Configure `app/config.local.php` using the production database credentials and a **new, independent production application secret**.
4. Enable HTTPS and confirm that all login and voting pages are available only over a secure connection.
5. Confirm the server applies `.htaccess` access restrictions to sensitive application, database, and script locations. Where possible, place private configuration and internal code outside the public document root.
6. Disable directory listing, block executable uploads, restrict file permissions, and enable PHP's PDO MySQL extension.
7. Create secure administrative credentials and verify authentication, authorization, candidate uploads, registration, vote submission, and tally reporting using the deployment environment.
8. Back up the database, perform a test restoration, and complete the checks in `docs/TESTING.md` before conducting a live election.

Do not migrate demonstration credentials or local development secrets to a public server.

## Security & Ballot Privacy

Election integrity requires controls at both the software and operational levels.

| Area | Approach |
| --- | --- |
| Eligibility | Staff-controlled registration; no public voter sign-up |
| Voter authentication | Student ID and individual six-digit Voting PIN |
| Credential handling | Hashed PIN verification using application-secret protection |
| Session protection | Server-managed PHP authentication sessions |
| Request protection | CSRF protections in backend application bootstrap |
| Duplicate prevention | Server-side per-round voter participation checks |
| Ballot data model | Anonymous ballot records separated from voter identity |
| Administration | Protected administrator pages and authorized election controls |
| Transport | HTTPS required for a live deployment |
| Operational continuity | Database backup and restoration procedures |

**Privacy qualification:** Anonymous ballot tables reduce direct linkage between identity and selections, but they do not guarantee anonymity against all infrastructure-level threats. Server logs, database logs, physical observation, and administrator access still require careful management. Six-digit PINs also make login throttling, private credential distribution, HTTPS, and active incident response important. The system must not be represented as independently audited or security-certified.

## Quality Assurance & Verification

Development work has included PHP syntax checks, frontend integration work, election workflow validation, and automated testing. A later project progress report recorded **262 passing tests, 0 failing tests, and one blocked external CDN check**. These figures are a reported development snapshot, not an independent certification or a substitute for testing the exact release on its target server.

Before an actual election, officers and technical administrators should execute `docs/TESTING.md` against the deployed environment, including:

- Candidate completeness and correctness across all five positions.
- Voter account creation, PIN issue/regeneration, and authorization behavior.
- Invalid-login handling, session expiry, and duplicate-vote rejection.
- Accurate ballot persistence, turnout tracking, and tally verification.
- Tie and runoff scenarios where applicable.
- Controlled results publication and projector/display checks.
- Phone and desktop browser testing on the actual election network.
- Permission boundaries, backup restoration, and recovery procedures.

Formal election authorization should follow successful environment-specific checks and verification by designated election officers.

## Operations & Maintenance

**Before election day:** Verify the roster, credentials, candidate portraits, election state, administrative access, HTTPS, recovery procedures, and backup integrity.

**During voting:** Staff should verify voter eligibility at registration, issue PINs confidentially, monitor turnout, assist with access problems without viewing selections, and escalate suspicious login or ballot activity.

**After voting:** Close voting, reconcile participation and ballot totals, examine any tied outcomes, obtain the responsible officers' approval, and only then publish or present the final results. Protect and retain election records according to the organizers' applicable retention policy.

## Technical References

- [`docs/API.md`](docs/API.md) — JSON API action reference.
- [`docs/TESTING.md`](docs/TESTING.md) — verification checklist and testing guidance.
- [`database/schema.sql`](database/schema.sql) — relational data structure.
- [`database/seed.sql`](database/seed.sql) — initial election setup.
- [`app/config.example.php`](app/config.example.php) — application configuration structure.

## Project Outcome

AMSA AIU Election 2026 brings voter administration, authenticated balloting, candidate management, election oversight, and formal results presentation into one dedicated platform. Its strongest contribution is an end-to-end digital workflow tailored to an on-site student election: eligibility is verified by election staff, ballot submission is processed by the backend, participation is controlled by the database, and results are prepared for an organized public announcement.

The platform is intended to support a more efficient, accessible, and accountable election process while keeping final governance, verification, and certification of the election with the authorized AMSA election officers.

---

<div align="center">

**AMSA AIU Election 2026**  
*Digital voting · Responsible election administration · Clear results presentation*

</div>
