# Database tables and relationship summary

1. `admins`: election staff accounts (super/officer/viewer); created initially via private CLI.
2. `elections`: election metadata and registration/publication flags.
3. `election_rounds`: main or separate runoff round, draft/open/closed.
4. `positions`: five offices in the official fixed order; belong to an election.
5. `round_positions`: required positions in each round; runoff can contain a subset.
6. `voters`: verified event-day account with Student ID, peppered/hashed PIN and optional profile information; candidates may also register as voters.
7. `candidates`: candidates and their corresponding position. No default sample names.
8. `round_candidates`: permitted candidates for the particular round, allowing only tied contestants in a runoff.
9. `voter_participation`: `(round_id,voter_id)` unique; enforces one vote per round and tracks turnout.
10. `anonymous_ballots`: a cryptographically random 32-hex-character ID and round ID; no voter FK, IP, timestamp or participation receipt.
11. `ballot_choices`: `(ballot_id,position_id)` unique and candidate relationship; actual candidate eligibility and position consistency validated by PHP within the same DB transaction.
12. `admin_audit_logs`: admin changes only, never PINs, selections or voter-to-ballot identifiers.
13. `login_attempts`: privacy-preserving HMAC identifiers and timestamps for brute-force throttling.

Voting transaction: lock round status → lock active voter → lock voter participation row → verify required round positions/candidates → write anonymous ballot and all selections → mark participation voted → commit. No changes after submission.

Runoff eligibility: voter must have submitted in the main round. All participants can vote in both treasurer positions during the main round.
