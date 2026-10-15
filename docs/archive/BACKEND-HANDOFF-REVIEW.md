# Engineering review: before building the production election backend

## Current state
This ZIP is a frontend-only PIN demo (no PHP or MySQL). It is suitable as a UI starting point, not as an election system. Voter records including cleartext PINs, login, and a simulated `VOTED` flag currently exist in browser storage. Candidate names, results, election controls and charts remain placeholders. User-facing files include obsolete register/forgot/reset password designs that MUST NOT be wired to public self-registration.

## Changes made for a better handoff
- Aligned ballot page and dashboard position numbering with official order: Female Treasurer, Male Treasurer, Secretary, Vice President, President.
- Voters who already cast the *demo* ballot may sign in again to view the correct `VOTED` state; the demo submission handler prevents a second submission.
- Reset PIN rejects reusing the old demo PIN.
- Added a browser-only demo admin navigation guard. This is for demonstration UX, **not security**.
- Kept all existing site visuals and layout.

## Major production hazards to address
1. Frontend data/state and all mock result totals must come from authenticated PHP/MySQL endpoints.
2. The PIN must be generated server-side, cryptographically and formatted with leading zeros; hashed before storage; shown only once. Six-digit secrets need persistent per-account/IP rate limiting.
3. Auth must rely on HTTP-only PHP sessions, proper admin/voter permission checks, CSRF tokens, and deployment HTTPS. Browser guards are not security.
4. Enforce single ballot submission using a database transaction and row-level lock; vote choices must be stored without a voter foreign key.
5. Store `has_voted` **per round**, not merely per voter, to allow tied-position runoffs without revising the original main ballot.
6. Election close and result-publication states must live in MySQL and be checked server-side on every request.
7. Remove public self-registration and password reset; counter-only accounts and admin PIN reset after identity verification.
8. Server must validate every position and candidate regardless of what the client submits.
9. Avoid exposing voter-to-ballot correlations in the database, timestamps, logs, URLs, or admin views.
10. Candidate editing and deletion must be restricted after the round is open; voter and candidate status must be consistently handled.

## Suggested API contract
Use one JSON pattern: `{ "ok": true, "data": {...} }` on success, `{ "ok": false, "error": { "code": "...", "message": "..." } }` on failure, and appropriate 2xx/4xx/5xx codes. Keep a single `js/api.js` for fetch wrappers. Keep the public API same-origin to simplify XAMPP and Hostinger. Use PHP 8.2+ and MySQL 8/MariaDB with PDO.

The full task specification is in `CODEX_FULLSTACK_PROMPT.md`.
