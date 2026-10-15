<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Voter Management - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="member-management">

<div class="sidebar-overlay" aria-hidden="true"></div>

<div class="layout-shell">

  <aside class="sidebar" id="primary-sidebar" aria-hidden="true">

    <div class="sidebar-brand">
      <img src="../assets/amsa-logo.png" class="amsa-logo-sm" alt="AMSA logo">
    </div>

    <div class="sidebar-label">
      Administration
    </div>

    <nav class="sidebar-nav">

      <a class="sidebar-link" data-page="admin-dashboard" href="admin-dashboard.php">
        <i class="fa-solid fa-gauge-high"></i>
        Dashboard
      </a>

      <a class="sidebar-link" data-page="member-management" href="member-management.php">
        <i class="fa-solid fa-users"></i>
        Voter Management
      </a>

      <a class="sidebar-link" data-page="candidate-management" href="candidate-management.php">
        <i class="fa-regular fa-user"></i>
        Candidates
      </a>

      <a class="sidebar-link" data-page="election-control" href="election-control.php">
        <i class="fa-solid fa-sliders"></i>
        Control
      </a>

      <a class="sidebar-link" data-page="admin-results" href="admin-results.php">
        <i class="fa-solid fa-chart-column"></i>
        Results
      </a>

    </nav>

    <div class="sidebar-footer">
      <a class="sidebar-link" href="admin-login.php" data-demo-signout>
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        Sign Out
      </a>
    </div>

  </aside>

  <div class="panel">

    <header class="app-header">

      <div class="d-flex align-items-center gap-3">

        <button class="mobile-menu-btn" type="button" data-menu-toggle aria-controls="primary-sidebar" aria-expanded="false" aria-label="Open navigation menu">
          <i class="fa-solid fa-bars"></i>
        </button>

        <div>
          <div class="fw-bold">AMSA AIU Election 2026</div>
          <div class="small text-gold-soft">Official Election Portal</div>
        </div>

      </div>

      <button class="user-chip">
        <span class="user-icon"><i class="fa-regular fa-user"></i></span>
        <span class="hide-mobile">Administrator</span>
      </button>

    </header>

    <main class="main-content">

      <div class="mx-auto" style="max-width:1220px">

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">Administration</p>

            <h1 class="page-title">
              Voter Management
            </h1>

            <p class="page-description">
              Create election-day voter accounts, search voting status,
              enable or disable voter access, and regenerate unused Voting PINs.
              Voter accounts and one-time PINs are issued securely by authorized election staff.
            </p>

          </div>

          <button
            class="btn-amsa btn-amsa-primary"
            data-bs-toggle="modal"
            data-bs-target="#createVoterModal"
          >
            <i class="fa-solid fa-plus"></i>
            Create Voter
          </button>

        </div>

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">

          <div
            class="input-icon-wrap flex-grow-1"
            style="max-width:430px"
          >

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
              id="voter-search"
              class="form-control"
              placeholder="Search name or Student ID"
            >

          </div>

          <div
            class="d-flex flex-wrap gap-2"
            aria-label="Voting status filter"
          >

            <button
              class="btn-amsa btn-amsa-gold btn-sm-amsa active"
              data-filter="all"
            >
              All
            </button>

            <button
              class="btn-amsa btn-amsa-outline btn-sm-amsa"
              data-filter="not-voted"
            >
              Not Voted
            </button>

            <button
              class="btn-amsa btn-amsa-outline btn-sm-amsa"
              data-filter="voted"
            >
              Voted
            </button>

          </div>

        </div>

        <div id="voter-system-notice" class="notice-paper mb-4 small" role="status" aria-live="polite" hidden></div>

        <div class="amsa-card overflow-hidden">

          <div class="table-responsive">

            <table class="table table-amsa">

              <thead>

                <tr>
                  <th>Name</th>
                  <th>Student ID</th>
                  <th>Voting Status</th>
                  <th>Account</th>
                  <th>Actions</th>
                </tr>

              </thead>

              <tbody id="voter-table-body"></tbody>

            </table>

          </div>

        </div>

      </div>

    </main>

  </div>

</div>

<div
  class="modal fade"
  id="createVoterModal"
  tabindex="-1"
>

  <div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

      <form
        id="create-voter-form"
        action="#"
        method="post"
      >

        <div class="modal-header">

          <div>

            <h2 class="h5 fw-bold text-maroon mb-1">
              Create Voter
            </h2>

            <p class="small text-secondary mb-0">
              Create an election-day voter account.
            </p>

          </div>

          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>

        </div>

        <div class="modal-body d-grid gap-3">

          <div>

            <label
              class="form-label"
              for="voter-name"
            >
              Full Name
            </label>

            <input
              class="form-control"
              id="voter-name"
              name="full_name"
              required
            >

          </div>

          <div>

            <label
              class="form-label"
              for="voter-student-id"
            >
              Student ID
            </label>

            <input
              class="form-control"
              id="voter-student-id"
              name="student_id"
              placeholder="AIU24102160"
              pattern="AIU[0-9]+"
              title="Start with AIU followed by digits"
              autocomplete="off"
              required
            >

          </div>

          <p class="small text-secondary mb-0">A secure six-digit Voting PIN is generated by the server and shown once after account creation.</p>
          <div><label for="voter-gender" class="form-label">Gender (optional)</label><select id="voter-gender" class="form-select"><option value="">Select</option><option>Male</option><option>Female</option><option>Other</option><option>Prefer not to say</option></select></div>
          <div><label for="voter-year" class="form-label">Year of Study (optional)</label><input id="voter-year" class="form-control" maxlength="30" placeholder="Year 2"></div>
          <div><label for="voter-major" class="form-label">Major (optional)</label><input id="voter-major" class="form-control" maxlength="160"></div>
          <div><label for="voter-school" class="form-label">School (optional)</label><input id="voter-school" class="form-control" maxlength="160"></div>
          <div><label for="voter-email" class="form-label">Student Email (optional)</label><input id="voter-email" type="email" class="form-control" placeholder="name@student.aiu.edu.my"></div>
          <div id="create-voter-error" class="notice-danger d-none" role="alert"></div>

        </div>

        <div class="modal-footer">

          <button
            type="button"
            class="btn-amsa btn-amsa-outline"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>

          <button
            class="btn-amsa btn-amsa-primary"
            type="submit"
          >
            Create Voter
          </button>

        </div>

      </form>

    </div>

  </div>

</div>

<!-- An issued PIN is displayed only at handoff and cleared on dismissal. -->
<div class="modal fade" id="issuedPinModal" tabindex="-1" aria-labelledby="issued-pin-title">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h2 class="h5 fw-bold text-maroon mb-1" id="issued-pin-title">Voting PIN issued</h2>
          <p class="small text-secondary mb-0">Give this PIN directly to the voter at the counter.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-grid gap-3">
        <div class="notice-gold">
          <div class="small fw-bold text-maroon" id="issued-voter-name"></div>
          <div class="small text-secondary" id="issued-voter-id"></div>
        </div>
        <div class="pin-handoff-card">
          <div class="small text-secondary mb-1">One-time display — Voting PIN</div>
          <div class="pin-handoff-code" id="issued-pin-code" aria-label="Issued PIN"></div>
          <button type="button" class="btn-amsa btn-amsa-outline btn-sm-amsa" id="copy-issued-pin">
            <i class="fa-regular fa-copy"></i> Copy PIN
          </button>
        </div>
        <p class="small text-secondary mb-0">The PIN will not be shown again. If it is lost before voting, use <strong>Reset PIN</strong> to issue a new one.</p>
        <p class="small text-secondary mb-0" id="pin-handoff-mode"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-amsa btn-amsa-primary" data-bs-dismiss="modal">Done — PIN given to voter</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="resetPinModal" tabindex="-1" aria-labelledby="reset-pin-title">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="reset-pin-form" method="post" action="#">
        <div class="modal-header">
          <div>
            <h2 class="h5 fw-bold text-maroon mb-1" id="reset-pin-title">Reset Voting PIN</h2>
            <p class="small text-secondary mb-0" id="reset-pin-voter"></p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body d-grid gap-3">
          <input type="hidden" name="voter_id" id="reset-voter-id">
          <label class="d-flex gap-2 align-items-start small"><input id="identity-verified" type="checkbox" required> I verified the voter’s identity in person at the attendance counter.</label>
          <div id="reset-pin-error" class="notice-danger d-none" role="alert"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-amsa btn-amsa-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-amsa btn-amsa-primary">Issue New PIN</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
