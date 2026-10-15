<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Admin Dashboard - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="admin-dashboard">

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

          <div class="fw-bold">
            AMSA AIU Election 2026
          </div>

          <div class="small text-gold-soft">
            Official Election Portal
          </div>

        </div>

      </div>

      <div class="admin-header-actions">
      <button type="button" class="notification-bell" data-scroll-recoveries aria-label="PIN assistance requests">
        <i class="fa-regular fa-bell" aria-hidden="true"></i>
        <span id="recovery-pending-count" class="notification-count" hidden>0</span>
      </button>
      <button class="user-chip" type="button">

        <span class="user-icon">
          <i class="fa-regular fa-user"></i>
        </span>

        <span class="hide-mobile text-start">

          <span class="d-block small text-white-50">
            Administrator
          </span>

          <span class="d-block fw-semibold">
            Administrator
          </span>

        </span>

        <i class="fa-solid fa-chevron-down small"></i>

      </button>
      </div>

    </header>

    <main class="main-content">

      <div class="mx-auto" style="max-width:1220px">

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">
              Administration
            </p>

            <h1 class="page-title">
              Election overview
            </h1>

            <p class="page-description">
              Monitor voter accounts, turnout, candidates, and
              election status from a single control point.
            </p>

          </div>

          <div class="d-flex flex-wrap gap-2">

            <a href="member-management.php" class="btn-amsa btn-amsa-outline">
              <i class="fa-solid fa-users"></i>
              Create Voter Account
            </a>

            <a href="election-control.php" class="btn-amsa btn-amsa-gold">
              <i class="fa-solid fa-sliders"></i>
              Election Control
            </a>

          </div>

        </div>

        <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-5">

          <div class="col">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div>
                  <p class="stat-label">Registered Voters</p>
                  <p class="stat-value">5</p>
                  <p class="stat-detail">Voters registered by election staff</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div>
                  <p class="stat-label">Voted</p>
                  <p class="stat-value">2</p>
                  <p class="stat-detail">40.0% turnout</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div>
                  <p class="stat-label">Not Voted</p>
                  <p class="stat-value">3</p>
                  <p class="stat-detail">Eligible accounts pending</p>
                </div>
                <div class="stat-icon"><i class="fa-regular fa-clock"></i></div>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div>
                  <p class="stat-label">Candidates</p>
                  <p class="stat-value">11</p>
                  <p class="stat-detail">Across five positions</p>
                </div>
                <div class="stat-icon"><i class="fa-regular fa-user"></i></div>
              </div>
            </div>
          </div>

          <div class="col">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div>
                  <p class="stat-label">Voting</p>
                  <p class="stat-value fs-4" id="admin-voting-summary">Loading</p>
                  <p class="stat-detail">Controlled from Election Control</p>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-check-to-slot"></i></div>
              </div>
            </div>
          </div>

        </div>

        <div class="row g-4 mt-2">

          <div class="col-lg-7">

            <div class="amsa-card card-pad h-100">

              <h2 class="card-title-amsa mb-3">
                Election status
              </h2>

              <div class="d-grid gap-3">

                <div class="notice-paper admin-status-row">
                  <span id="admin-registration-message" class="status-message" role="status">Loading registration status...</span>
                  <span id="admin-registration-badge" class="status-badge">Loading</span>
                </div>

                <div class="notice-paper admin-status-row">
                  <span id="admin-voting-message" class="status-message" role="status">Loading voting status...</span>
                  <span id="admin-voting-badge" class="status-badge">Loading</span>
                </div>

                <div class="notice-paper admin-status-row">
                  <span id="admin-results-message" class="status-message" role="status">Loading results status...</span>
                  <span id="admin-results-badge" class="status-badge">Loading</span>
                </div>

              </div>

            </div>

          </div>

          <div class="col-lg-5">

            <div class="amsa-card card-pad h-100">

              <h2 class="card-title-amsa mb-3">
                Quick actions
              </h2>

              <div class="d-grid gap-2">

                <a href="member-management.php" class="btn-amsa btn-amsa-outline justify-content-start">
                  <i class="fa-solid fa-users"></i>
                  Voter Management
                </a>

                <a href="candidate-management.php" class="btn-amsa btn-amsa-outline justify-content-start">
                  <i class="fa-regular fa-user"></i>
                  Manage Candidates
                </a>

                <a href="admin-results.php" class="btn-amsa btn-amsa-outline justify-content-start">
                  <i class="fa-solid fa-chart-column"></i>
                  View Live Results
                </a>

                <a href="result-reveal.php" class="btn-amsa btn-amsa-outline justify-content-start">
                  <i class="fa-solid fa-circle-play"></i>
                  Preview Result Reveal
                </a>

              </div>

            </div>

          </div>

        </div>

        <section class="amsa-card card-pad mt-4" id="pin-recovery-panel" aria-labelledby="pin-recovery-heading">
          <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
            <div>
              <p class="eyebrow mb-1">Help Counter</p>
              <h2 class="card-title-amsa mb-1" id="pin-recovery-heading">PIN assistance requests</h2>
              <p class="small text-secondary mb-0">Verify identity in person before issuing a replacement PIN.</p>
            </div>
            <div class="recovery-filters" aria-label="PIN request status filter">
              <button type="button" class="btn-amsa btn-amsa-gold btn-sm-amsa active" data-recovery-filter="pending">Pending</button>
              <button type="button" class="btn-amsa btn-amsa-outline btn-sm-amsa" data-recovery-filter="resolved">Resolved</button>
              <button type="button" class="btn-amsa btn-amsa-outline btn-sm-amsa" data-recovery-filter="rejected">Rejected</button>
            </div>
          </div>
          <div id="pin-recovery-list" class="recovery-request-list" aria-live="polite"></div>
        </section>

      </div>

    </main>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
