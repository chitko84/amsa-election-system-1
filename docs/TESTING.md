# Mandatory XAMPP acceptance checklist

## Tasks 10–14 automated suites

The additional isolated suites are:

```powershell
node tests/tasks_10_14_e2e.mjs
node tests/browser_ui.mjs
node tests/election_rehearsal_50.mjs
node tests/load_smoke.mjs
```

`tasks_10_14_e2e.mjs` covers candidate image security and lifecycle, PIN assistance enumeration resistance, authorization, CSRF, concurrent resets, old-PIN invalidation, and voting-status preservation. `browser_ui.mjs` renders widths from 320px through 1920px and saves screenshots in `tests/artifacts`. `election_rehearsal_50.mjs` completes 50 fictional five-position ballots (250 choices) only in `amsa_election_test`.

## Automated local rehearsal

The included test harnesses are restricted to a database whose name begins with
`amsa_election_test`. They do not use or reset the configured election database.

1. Recreate `amsa_election_test`, then import `database/schema.sql` and
   `database/seed.sql`.
2. Run `C:\xampp\php\php.exe tests\bootstrap_test.php` with
   `AMSA_TEST_DB=amsa_election_test` in the environment.
3. Start a PHP development server with the same environment value and a writable
   `session.save_path`, then run `node tests\http_e2e.mjs`.
4. Run `node tests\load_smoke.mjs` for the read-only 60-request smoke test.
5. Run `node tests\browser_ui.mjs` where Chromium headless rendering is supported.
   It covers 320, 360, 375, 390, 414, 430, 768, 1024, and 1440 pixel widths and
   writes screenshots to `tests/artifacts/`.

Never point these harnesses at the live election database.

**Not yet run against a live database.** Perform these tests in a disposable database before handling real voter records.

- [ ] Import schema + seed in MySQL/MariaDB without errors and check all 13 tables/foreign keys.
- [ ] Configure local config and use CLI to create an admin; verify there is no public default password.
- [ ] Admin login, wrong password, repeated wrong password rate limit, logout, protected admin pages.
- [ ] Open registration; create a voter with ID `AIU` + digits and leading-zero 6-digit PIN; verify `pin_hash` is not the actual PIN.
- [ ] Create duplicate ID and reject; close registration and reject new voter account creation.
- [ ] Voter login correctly, wrong PIN and rate limit; verify disabled voter cannot log in.
- [ ] Verify staff can reset PIN after in-person identity verification, old PIN stops working and old session expires.
- [ ] Create candidate(s) for each position, upload image, edit/deactivate only before voting.
- [ ] Voting cannot open with empty required position; once open, candidate edits are prohibited.
- [ ] Voter sees all 5 positions in order, including both treasurers.
- [ ] Missing, extra, mismatched or inactive candidate choice rejected by server; malformed request rejected.
- [ ] Successful complete ballot increments 5 candidate counts and the turnout by 1; no voter ID on ballots.
- [ ] Double-click, repeat POST and two simultaneous login sessions cannot cast a second ballot.
- [ ] Disabled voter or closed election rejects submissions; failed transaction does not leave incomplete ballots.
- [ ] Voters cannot access unpublished result totals even by directly calling API.
- [ ] Admin sees current counts; close election then publish; vote counts appear correctly to voter.
- [ ] Tie blocks publication; runoff contains only tied candidates/positions; only main-round participants eligible; one vote in runoff.
- [ ] Admin viewer cannot mutate anything; CSRF missing/wrong fails on all POST endpoints.
- [ ] Check paths, HTTPS, redirects and file uploads on Hostinger; test fresh browser/incognito and different devices.
- [ ] Obtain independent security/privacy review, backup and restoration drill before election day.
