<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Member Dashboard - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="member-dashboard">

<div class="sidebar-overlay" aria-hidden="true"></div>

<div class="layout-shell">

  <aside class="sidebar" id="primary-sidebar" aria-hidden="true">

    <div class="sidebar-brand">
      <img src="../assets/amsa-logo.png" class="amsa-logo-sm" alt="AMSA logo">
    </div>

    <div class="sidebar-label">
      Voter Panel
    </div>

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

        <a href="../index.php" class="hide-mobile">
          <img src="../assets/amsa-logo.png" class="amsa-logo-xs" alt="AMSA logo">
        </a>

        <div>
          <div class="fw-bold">
            AMSA AIU Election 2026
          </div>

          <div class="small text-gold-soft">
            Official Election Portal
          </div>
        </div>

      </div>

      <button class="user-chip">

        <span class="user-icon">
          <i class="fa-regular fa-user"></i>
        </span>

        <span class="hide-mobile text-start">

          <span class="d-block small text-white-50">
            Member
          </span>

          <span class="d-block fw-semibold">
            <span data-voter-name>Member</span>
          </span>

        </span>

        <i class="fa-solid fa-chevron-down small text-gold-soft"></i>

      </button>

    </header>

    <main class="main-content">

      <div class="mx-auto" style="max-width:1180px">

        <section class="member-welcome-panel mb-4">
          <div class="member-welcome-copy">
            <p class="eyebrow mb-2">Member Election Portal</p>
            <h1 class="page-title">Welcome back, Member.</h1>
            <p class="page-description">Your AMSA AIU Election 2026 dashboard. Check your voting status, review the election process and continue to your ballot when you are ready.</p>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <span class="status-badge success"><i class="fa-solid fa-circle-check"></i> Voting Open</span>
              <span class="status-badge"><i class="fa-solid fa-user-shield"></i> Eligible Member</span>
              <span class="status-badge" id="dashboard-ballot-status"><i class="fa-regular fa-circle"></i> Ballot Not Submitted</span>
            </div>
          </div>

          <div class="member-welcome-action">
            <div class="welcome-action-icon"><i class="fa-solid fa-check-to-slot"></i></div>
            <small>YOUR BALLOT</small>
            <strong id="dashboard-ballot-heading">Ready to begin</strong>
            <p id="dashboard-ballot-note">Select one candidate for every position, then review everything before final submission.</p>
            <a id="dashboard-ballot-action" href="vote.php" class="btn-amsa btn-amsa-gold w-100">Start Voting <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </section>

        <div class="row g-4">
          <div class="col-md-4">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div><p class="stat-label">Voting Status</p><p class="stat-value fs-4">OPEN</p><p class="stat-detail">Ballot currently available</p></div>
                <div class="stat-icon"><i class="fa-solid fa-check-to-slot"></i></div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div><p class="stat-label">Required Positions</p><p class="stat-value">5</p><p class="stat-detail">One selection per position</p></div>
                <div class="stat-icon"><i class="fa-solid fa-list-check"></i></div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="amsa-card stat-card h-100">
              <div class="d-flex justify-content-between gap-3">
                <div><p class="stat-label">Results</p><p class="stat-value fs-4">HIDDEN</p><p class="stat-detail">Published by administrators</p></div>
                <div class="stat-icon"><i class="fa-regular fa-eye-slash"></i></div>
              </div>
            </div>
          </div>
        </div>

        <section class="amsa-card card-pad mt-4">
          <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
            <div>
              <p class="eyebrow mb-2">Election Journey</p>
              <h2 class="h4 fw-bold text-maroon mb-1">Five clear steps to complete your vote</h2>
              <p class="small text-ink-muted mb-0">You are currently ready to select your candidates.</p>
            </div>
            <a href="candidates.php" class="btn-amsa btn-amsa-outline btn-sm-amsa"><i class="fa-regular fa-user"></i> Review Candidates First</a>
          </div>

          <div class="election-journey">
            <div class="journey-step complete"><span><i class="fa-solid fa-check"></i></span><div><small>Step 1</small><strong>Sign In</strong></div></div>
            <div class="journey-line"></div>
            <div class="journey-step current"><span>2</span><div><small>Step 2</small><strong>Select Candidates</strong></div></div>
            <div class="journey-line"></div>
            <div class="journey-step"><span>3</span><div><small>Step 3</small><strong>Review Ballot</strong></div></div>
            <div class="journey-line"></div>
            <div class="journey-step"><span>4</span><div><small>Step 4</small><strong>Submit Vote</strong></div></div>
            <div class="journey-line"></div>
            <div class="journey-step"><span>5</span><div><small>Step 5</small><strong>Results</strong></div></div>
          </div>
        </section>

        <div class="row g-4 mt-0">
          <div class="col-lg-7">
            <section class="amsa-card card-pad h-100">
              <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                <div><p class="eyebrow mb-1">Ballot Positions</p><h2 class="h5 fw-bold text-maroon mb-0">Top management election</h2></div>
                <span class="status-badge">5 Positions</span>
              </div>
              <div class="dashboard-position-list">
                <a href="vote.php#position-president"><span>01</span><strong>President</strong><i class="fa-solid fa-chevron-right"></i></a>
                <a href="vote.php#position-vice-president"><span>02</span><strong>Vice President</strong><i class="fa-solid fa-chevron-right"></i></a>
                <a href="vote.php#position-secretary"><span>03</span><strong>Secretary</strong><i class="fa-solid fa-chevron-right"></i></a>
                <a href="vote.php#position-male-treasurer"><span>04</span><strong>Male Treasurer</strong><i class="fa-solid fa-chevron-right"></i></a>
                <a href="vote.php#position-female-treasurer"><span>05</span><strong>Female Treasurer</strong><i class="fa-solid fa-chevron-right"></i></a>
              </div>
            </section>
          </div>

          <div class="col-lg-5">
            <section class="amsa-card card-pad h-100">
              <p class="eyebrow mb-2">Important</p>
              <h2 class="h5 fw-bold text-maroon mb-3">Before you submit</h2>
              <div class="d-grid gap-3 small text-ink-muted">
                <div class="notice-paper"><strong class="text-maroon">01.</strong> Select one candidate for each of the five positions.</div>
                <div class="notice-paper"><strong class="text-maroon">02.</strong> Review all selections carefully before final confirmation.</div>
                <div class="notice-gold"><strong class="text-maroon">03.</strong> Your ballot can only be submitted once and cannot be changed afterwards.</div>
              </div>
            </section>
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
