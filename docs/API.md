# JSON API

Endpoint: `../api/index.php?action=NAME` from admin/user pages. Same-origin cookies, GET for reads, POST JSON for changes. POST requires `X-CSRF-Token` from the server-rendered `meta[name="csrf-token"]`. Responses: `{ "ok":true, "data":{...} }` or `{ "ok":false, "error":{"message":"..."} }`.

| Action | Method | Access | Purpose |
|---|---|---|---|
| csrf | GET | everyone | Current CSRF token |
| admin-login, voter-login | POST | everyone | Login (admin: username/password; voter: student_id/pin) |
| me | GET | everyone | Account and round status (when logged in) |
| logout | POST | signed-in | Terminate session |
| ballot | GET | voter | Current round position/candidates and participation status |
| submit-vote | POST | voter | `{choices:{positionId:candidateId}}`; atomic submission |
| results | GET | admin/voter | Admin sees live current round; voter sees only published consolidated results |
| voters | GET | admin | List verified voter records |
| voter-create | POST | officer/super | Create account; PIN returned ONCE; requires registration enabled |
| voter-toggle | POST | officer/super | Enable/disable voter |
| voter-reset | POST | officer/super | Reset PIN when identity verified and not yet voted |
| candidate-list, positions | GET | admin | Candidate/position management data |
| candidate-save, candidate-disable | POST | officer/super | Candidate roster edits, draft main round only |
| election-control | POST | officer/super | registration-open/close; open/close; publish/hide |
| runoff-create | POST | super | New draft runoff when ties exist in closed current round |

`api/upload.php`: separate POST multipart endpoint for candidate image upload with same CSRF protection. No PIN is recoverable; reset creates a new PIN.
