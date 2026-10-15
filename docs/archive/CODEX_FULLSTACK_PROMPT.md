# Codex task: AMSA AIU Election 2026 — PHP/MySQL full-stack implementation

You are editing the frontend project in the current repository. **Implement working code, not just a plan.** Inspect files first. Preserve all existing page designs, layout, CSS, images, branding, and URLs wherever possible. No React/Next.js, Node backend, Laravel, Supabase or external hosted DB. Target PHP 8.2+, MySQL 8 / compatible MariaDB, PDO, Apache, XAMPP locally and Hostinger PHP hosting later. Use plain JavaScript + current HTML/CSS/Bootstrap. Do not connect to a real DB until the user configures local credentials; supply SQL, example config, and instructions.

## Non-negotiable election flow

At the physical event, attendance is confirmed OFFLINE. An authorized admin at the counter then creates a voter with full name + unique AIU Student ID. The SERVER generates a cryptographically secure 6-digit PIN, saves only a `password_hash`, and returns the PIN ONCE for handoff directly to the voter. Voter logs in using Student ID + PIN, selects one candidate for every required position, reviews, confirms, then permanently has status VOTED for that round. One submission per voter per round, anonymous choices, no modifications after submission. Candidates may vote. No public self-registration or email-based PIN reset; admins may issue a new PIN only after identity verification and before voting in the current round.

Main-round ballot order is **Female Treasurer → Male Treasurer → Secretary → Vice President → President**. Everyone votes in both treasurer positions. Admins can view real-time results; voters/public can see results only when expressly published. Final result reveal retains the existing animated/scrolling design. A tied position triggers a new runoff round including only tied candidates and tied positions; authorized voters get one new submission in that round, without modifying previous votes/results.

## STEP 1 — Design and create the MySQL schema FIRST

Create `database/schema.sql` (utf8mb4, InnoDB, foreign keys, indexes) and safe seed/migration approach. Include:
- `admins` (unique username, `password_hash`, role superadmin/admin, active flag);
- `elections` and `election_rounds` (main/runoff, draft/open/closed, registration control, result publication);
- `positions` seeded in the five-position order above, and `round_positions` (allow runoff subset);
- `candidates` (name, student_id when known, position, manifesto, validated photo path, active flag, election association; uniqueness rules; allow candidate to be a voter);
- `voters` (name, unique student_id, PIN hash, active/disabled, attendance-verified admin, timestamps; no public registration);
- `voter_round_status` with UNIQUE (voter_id, round_id), containing voted flag/at; do not rely solely on a global `has_voted` flag;
- **anonymous** `ballots` (random opaque ID, round_id, NO voter_id, NO student_id, NO source IP); `ballot_choices` (ballot_id, position_id, candidate_id, UNIQUE(ballot_id,position_id));
- `login_attempts` for persistent per-voter/IP rate limits (store identifiers minimally/safely; prune old records) and admin audit logs for management actions ONLY (never PINs, ballot choices, or voter-to-ballot associations).

Important: do not add a relation or audit trail that connects ballot choices to a voter. Avoid precise ballot timestamps if they would reveal order. Constrain positions/candidates correctly, validate selections on the SERVER. Provide `database/seed_demo.sql` with clearly fictional sample candidates/election, never real voter details. Avoid dropping production tables on re-import.

## STEP 2 — Build PHP backend files to connect to those tables

Add a small organized PHP structure (`api/`, `app/`, `database/`, `config/` or equivalent). `config/database.php` uses environment/local non-committed credentials; `.env.example` is safe; PDO prepared statements, exception handling and JSON responses. Protect internal config from HTTP access (outside public webroot or deny access via Apache). No hardcoded real secrets or admin credentials. Provide CLI-only `scripts/create_admin.php`, and an install guide for XAMPP/phpMyAdmin and Hostinger.

Create same-origin JSON endpoints (GET/POST; sensible names and consistent `{ok,data,error}` envelope):
- `auth/admin-login`, `auth/voter-login`, `auth/me`, `auth/logout`, CSRF/session endpoint;
- admin voter list/search/create/disable/enable/reset-PIN, registration-open control, full name / Student ID validation and duplicate rejection;
- admin candidate CRUD + safe image uploads; edits/deletion locked once ballots are possible;
- admin election and round open/close, registration enable/disable, result publish/hide, create/approve tied-position runoff;
- voter candidate/ballot metadata fetch, ballot preview, one final submission, personal vote status (not their stored vote choices), restricted published results;
- admin live aggregate counts, eligible count, turnout and tied-position detection; public reveal endpoints return data only after publication.

SECURITY: secure HttpOnly SameSite cookies; HTTPS-only Secure flag on deployed HTTPS; session ID regenerate at login; separate enforced admin/voter roles; CSRF token checked on mutations; no state changes via GET; strict input checks; output escaping; no SQL text concatenation from user inputs; generic login errors; persistent rate limits by student ID plus IP (6-digit PIN can be brute-forced otherwise); server generated PIN via `random_int(0, 999999)` formatted with leading zeros; `password_hash/password_verify`; deny login for disabled voters; never log or return existing PIN; regenerate/reset only when eligible. Never use frontend localStorage as authorization. Upload MIME/size/path allowlists; prevent code execution in uploads.

CRITICAL ATOMIC VOTING: in one InnoDB transaction, lock current round/election state and the voter’s per-round status row (`SELECT ... FOR UPDATE`); initialize that status row safely under a unique key before locking if absent; reject if closed, disabled, ineligible or already voted; re-read server-side required positions/candidates; require EXACTLY one valid active candidate for each required position and no extras; create an independent random opaque ballot ID with NO voter FK; insert all choices; mark voter-round status VOTED; commit. Roll back everything on any error. Handle rapid double-click, concurrent tabs/devices, retries, zero candidates, bad candidate IDs and closing election mid-submission. Do not expose voter-to-ballot relationships via logs, responses, ordering or timestamps.

## STEP 3 — Connect EVERY existing frontend screen

Inspect `admin/*.html`, `user/*.html`, `js/app.js`, `js/demo-store.js`, `js/auth.js`, `js/member-session.js`, `js/pin-management.js`. Replace demo storage and hardcoded candidate vote counts with a small reusable `js/api.js` (`fetch`, `credentials:'same-origin'`, JSON and CSRF handling) and real server data. Keep all existing styling/layout. Admin Create Voter shows server-generated PIN ONCE, Copy/Done; reset issues a different PIN. Registration is admin-only on election day. No manual PIN setting on production. Wire candidate management, election control, live admin results, vote/review/submit (positions must be dynamic for runoff rounds), dashboard VOTED state, gated results/reveal, logout. Hide/replace the public `user/register.html`, `forgot-password.html`, `reset-password.html` self-service workflows: send users to the physical help counter, do not create public registration/reset endpoints. Remove mock votes, `demo-admin` login, and any fake completion alerts from the production path. Handle loading, validation, error, empty and already-voted states accessibly.

## STEP 4 — Verify, document and deliver

Provide `README.md` (Windows XAMPP setup: project path, Apache/MySQL, phpMyAdmin SQL import, DB credentials, CLI admin setup, local URL, deployment checklist). Provide `docs/API.md`, `docs/DB-DESIGN.md` and sample API responses (never expose PIN except one-time creation/reset response). Add automated PHP/API integration tests or reproducible test scripts (use isolated test DB) for valid registration/login, wrong PIN, duplicated ID, inactive account, CSRF/role checks, five positions, invalid/duplicate candidates, atomicity, double submit from concurrent sessions, anonymity (no voter reference on ballots), result hiding, PIN reset, and runoff. Include a manual end-to-end testing checklist. Run available tests and PHP syntax checks; report what passed and what still needs MySQL/XAMPP testing. Never claim the election is deployment-ready without end-to-end tests.

**Work sequence:** inspect → implement SQL schema → implement PDO connection + migrations/seeds → PHP auth and voter APIs → candidate/election APIs → transactional vote API → results/runoff APIs → JS integration → tests/docs. After each major stage, summarize changed files and any blocker, then continue automatically. Do not stop after schema or return only pseudocode; deliver the finished code files, preserving the current design.
