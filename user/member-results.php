<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Election Results - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="member-results">

<div class="sidebar-overlay" aria-hidden="true"></div>

<div class="layout-shell">

  <aside class="sidebar" id="primary-sidebar" aria-hidden="true">

    <div class="sidebar-brand">
      <img src="../assets/amsa-logo.png" class="amsa-logo-sm" alt="AMSA logo">
    </div>

    <div class="sidebar-label">Voter Panel</div>

    <nav class="sidebar-nav">

      <a class="sidebar-link" data-page="member-dashboard" href="member-dashboard.php">
        <i class="fa-solid fa-house"></i>
        Dashboard
      </a>

      <a class="sidebar-link" data-page="vote" href="vote.php">
        <i class="fa-solid fa-check-to-slot"></i>
        Vote
      </a>

      <a class="sidebar-link" data-page="candidates" href="candidates.php">
        <i class="fa-regular fa-user"></i>
        Candidates
      </a>

      <a class="sidebar-link" data-page="member-results" href="member-results.php">
        <i class="fa-solid fa-chart-column"></i>
        Results
      </a>

    </nav>

    <div class="sidebar-footer">
      <a class="sidebar-link" href="login.php" data-demo-signout>
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
        <span class="hide-mobile">Member</span>
      </button>

    </header>

    <main class="main-content">

      <div class="mx-auto" style="max-width:980px">

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">
              Published Results
            </p>

            <h1 class="page-title">
              Election results
            </h1>

            <p class="page-description">
              Official results become available to members only
              after election administrators publish them.
            </p>

          </div>

        </div>

        <div id="member-results-list"></div>

        <p class="pb-3 text-center small text-secondary">
          Official totals appear only after the election team publishes verified results.
        </p>

      </div>

    </main>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
