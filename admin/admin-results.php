<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Admin Results - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="admin-results">

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
              Live Administration View
            </p>

            <h1 class="page-title">
              Election results
            </h1>

            <p class="page-description">
              Administrators can monitor prototype candidate
              totals. Member results remain hidden until
              publication.
            </p>

          </div>

          <a
            href="result-reveal.php"
            class="btn-amsa btn-amsa-gold"
          >
            <i class="fa-solid fa-circle-play"></i>
            Open Projector Reveal
          </a>

        </div>

        <div class="notice-gold d-flex flex-wrap align-items-center gap-3 mb-4 small text-maroon">

          <i class="fa-regular fa-eye"></i>

          <strong>
            Admin-only live view
          </strong>

          <span class="text-ink-muted">
            Live results are computed from anonymous ballots stored in the database
          </span>

        </div>

        <div id="admin-results-list"></div>

      </div>

    </main>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
