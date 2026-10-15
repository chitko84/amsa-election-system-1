<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Election Control - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="election-control">

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

      <div class="mx-auto" style="max-width:1050px">

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">
              Administration
            </p>

            <h1 class="page-title">
              Election Control
            </h1>

            <p class="page-description">
              Open or close voting and control when official
              results become visible to members.
            </p>

          </div>

        </div>

        <div class="row g-4">

          <div class="col-md-6">

            <div class="amsa-card card-pad h-100">

              <div class="d-flex align-items-start justify-content-between gap-3">

                <h2 class="h5 fw-bold text-maroon">
                  Voting access
                </h2>

                <span
                  id="voting-status"
                  class="status-badge success"
                >
                  <i class="fa-solid fa-circle-check"></i>
                  Open
                </span>

              </div>

              <p class="small text-ink-muted lh-lg mt-3">
                Controls whether verified members can access
                and submit the ballot.
              </p>
              <p id="voting-status-message" class="status-message" role="status">Loading voting status...</p>

              <div class="row g-2 mt-2">

                <div class="col-6">

                  <button
                    class="btn-amsa btn-amsa-gold w-100"
                    data-control="open"
                  >
                    <i class="fa-solid fa-play"></i>
                    Open Voting
                  </button>

                </div>

                <div class="col-6">

                  <button
                    class="btn-amsa btn-amsa-primary w-100"
                    data-control="close"
                  >
                    <i class="fa-solid fa-stop"></i>
                    Close Voting
                  </button>

                </div>

              </div>

            </div>

          </div>

          <div class="col-md-6">

            <div class="amsa-card card-pad h-100">

              <div class="d-flex align-items-start justify-content-between gap-3">

                <h2 class="h5 fw-bold text-maroon">
                  Member results
                </h2>

                <span
                  id="publish-status"
                  class="status-badge"
                >
                  <i class="fa-regular fa-eye-slash"></i>
                  Hidden
                </span>

              </div>

              <p class="small text-ink-muted lh-lg mt-3">
                Controls whether official election results
                are visible to member accounts.
              </p>
              <p id="results-status-message" class="status-message" role="status">Loading results status...</p>

              <div class="row g-2 mt-2">

                <div class="col-6">

                  <button
                    class="btn-amsa btn-amsa-gold w-100"
                    data-control="publish"
                  >
                    <i class="fa-regular fa-eye"></i>
                    Publish Results
                  </button>

                </div>

                <div class="col-6">

                  <button
                    class="btn-amsa btn-amsa-primary w-100"
                    data-control="hide"
                  >
                    <i class="fa-regular fa-eye-slash"></i>
                    Hide Results
                  </button>

                </div>

              </div>

            </div>

          </div>

        </div>

        <div class="amsa-card card-pad mt-4" id="registration-card"><h2 class="h5 fw-bold text-maroon">Attendance counter registration</h2><p class="small text-secondary">Allow authorized staff to register verified voters on election day.</p><div id="registration-status" class="status-message mb-3" role="status">Loading registration status...</div><div class="control-button-grid"><button class="btn-amsa btn-amsa-gold" data-registration="registration-open">Open registration</button><button class="btn-amsa btn-amsa-outline" data-registration="registration-close">Close registration</button></div></div>
        <div class="amsa-card card-pad mt-4"><h2 class="h5 fw-bold text-maroon">Tie resolution</h2><p class="small text-secondary">If a closed round has tied candidates, an authorized super administrator can start a runoff for the tied positions.</p><button class="btn-amsa btn-amsa-outline" id="create-runoff-btn">Create runoff from ties</button></div>
        <div class="amsa-card card-pad mt-4">

          <h2 class="card-title-amsa">
            Live turnout
          </h2>

          <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-4 mt-4">

            <div>

              <div class="d-flex align-items-center gap-2 text-maroon">

                <i class="fa-solid fa-users"></i>

                <span class="display-6 fw-semibold">
                  2
                </span>

                <span class="fs-5 text-secondary">
                  / 5
                </span>

              </div>

              <p class="small text-ink-muted mb-0">
                Registered voters who have submitted a ballot
              </p>

            </div>

            <div class="text-sm-end">

              <p class="h2 fw-semibold text-maroon mb-1">
                40.0%
              </p>

              <p class="small text-secondary mb-0">
                Verified current-round turnout
              </p>

            </div>

          </div>

          <div
            class="progress-amsa mt-4"
            style="height:12px"
          >

            <div
              class="bar"
              style="width:40%"
            ></div>

          </div>

          <div class="notice-paper d-flex gap-3 mt-4">

            <i class="fa-solid fa-lock mt-1 text-maroon"></i>

            <p class="small text-ink-muted lh-lg mb-0">
              All election settings and ballots are managed on the server. Voters cannot access live vote totals until results are published.
            </p>

          </div>

        </div>

      </div>

    </main>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
