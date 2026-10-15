# AMSA AIU Election 2026 — PHP + MySQL project

This package converts all original HTML pages to PHP and supplies a MySQL schema, real PHP JSON API, and JavaScript frontend integrations. It preserves the original CSS, assets, navigation and overall page design. The demo-only browser PINs, fake voter sessions and preset vote counts have been removed from active code.

**Important:** The PHP/JS files pass syntax checks, but an end-to-end database/browser test has NOT been performed here because XAMPP/MySQL is not running in the build environment. Do not use for a real election until the checks in `docs/TESTING.md` pass on your local server and you have reviewed security and privacy risks.

## XAMPP installation (Windows)

1. Extract the project folder to `C:\xampp\htdocs\amsa-election\` (the folder should contain `index.php`, `api/`, `app/`, `admin/`, `user/` and `database/`).
2. In XAMPP start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin` and create a database called `amsa_election` using `utf8mb4_unicode_ci`, or run `database/create_database_local.sql` under phpMyAdmin SQL.
4. Select `amsa_election`. Import `database/schema.sql` **first**, then `database/seed.sql` **once**. Seed starts election in draft with registration closed and the five required positions; there are no candidates, voter accounts, or demo votes. **Do not re-run seed.sql twice.**
5. Copy `app/config.example.php` to `app/config.local.php`; update DB username/password (XAMPP defaults to `root`/empty password, only for development). Generate a random app secret in a local terminal:
   ```powershell
   C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```
   Replace `secret` in the local config with this value. Do not publish or commit this configuration file.
6. Create the first administrator from a Windows terminal:
   ```powershell
   cd C:\xampp\htdocs\amsa-election
   C:\xampp\php\php.exe scripts\create_admin.php electionadmin "Election Coordinator" super
   ```
   Enter a unique password (12+ characters) when prompted. **No default admin password exists.**
7. Open `http://localhost/amsa-election/` (PHP is required; do **not** open pages with `file://`). Sign in at `/admin/admin-login.php`.
8. With the election in draft, add **at least one legitimate candidate to each of the five positions**, then open registration under Admin → Election Control. Create voter accounts at the attendance counter and hand each voter their one-time six-digit PIN. Open voting only after the candidate roster has been verified.

## API and UI layout

- `index.php`, `admin/*.php`, `user/*.php`: preserved responsive UI with server-side session guard on protected pages.
- `database/schema.sql`: full relational schema, including per-round voter participation and anonymous ballot tables.
- `database/seed.sql`: AMSA election, one draft main round, and five positions (Female Treasurer → Male Treasurer → Secretary → Vice President → President).
- `app/config.example.php`: database/secret template; `app/config.local.php`: local private configuration.
- `app/bootstrap.php`: PDO connection, secure PHP sessions, CSRF, admin/voter authentication, PIN hashing.
- `api/index.php`: JSON API. Actions documented in `docs/API.md`.
- `api/upload.php`: candidate portrait upload (JPG/PNG/WebP up to 3 MB).
- `js/production.js`: real API page bindings. `js/shell.js`: navigation/display helpers.
- `scripts/create_admin.php`: administrator bootstrap, command line only.

## Hostinger deployment

Create a MySQL database through hPanel and import `schema.sql` and `seed.sql` in that database. Update the private config with Hostinger's DB host/database/user/password and a different production `secret`. Enable HTTPS before allowing any login. Ensure Apache honors `.htaccess` denies for `app/`, `database/`, `scripts/` and uploaded executable files; ideally keep internal PHP/config outside the web-accessible document root. Disable directory listings, configure PHP 8.2+, enable PDO MySQL, and use restricted file permissions. Change all initial admin passwords from any development credentials. Back up the database and test a restore before election day.

## Known limitations / checks before real election

- MySQL schema and integration endpoints were not exercised against an actual MySQL server here. Run the full checklist in `docs/TESTING.md` first.
- Additional admin accounts can be created with the CLI command; there is no graphical admin management screen yet.
- Runoff rounds and tie detection are included, but an election officer must verify the published combined main/runoff winners and turnout presentation; an independent audit is recommended.
- Secrets with only six digits require strong rate limiting, physical PIN handoff and HTTPS. The API stores only a peppered password hash and logs failed attempts, but needs a real-world load/security review.
- Ballots have no voter foreign key, IP, timestamp or receipt. Database administrators could still infer timing from server/access logs, transaction logs or physical supervision; strict operational protections are necessary.
- The `index.php` static design remains, but prior fancy animated reveal stages have been replaced with a simpler live results reveal. Further design restoration is optional after safe data integration.
- Do not treat the source as independently certified, penetration-tested or election-ready.
