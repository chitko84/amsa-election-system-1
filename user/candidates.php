<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Candidates - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="candidates">

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
            Member
          </span>

        </span>

        <i class="fa-solid fa-chevron-down small"></i>

      </button>

    </header>

    <main class="main-content">

      <div class="mx-auto" style="max-width:1150px">

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">
              Election Candidates
            </p>

            <h1 class="page-title">
              Candidate profiles
            </h1>

            <p class="page-description">
              Browse the official candidates. Select Read More to view
              each candidate&apos;s full profile and manifesto.
            </p>

          </div>

          <a
            href="vote.php"
            class="btn-amsa btn-amsa-primary"
          >
            <i class="fa-solid fa-check-to-slot"></i>
            Cast Your Vote
          </a>

        </div>

        <div id="candidate-public-list"></div>

      </div>

    </main>

  </div>

</div>

<div class="modal fade candidate-profile-modal" id="candidateProfileModal" tabindex="-1" aria-labelledby="candidate-profile-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <p class="eyebrow mb-1">Official Candidate</p>
          <h2 class="h5 fw-bold text-maroon mb-0" id="candidate-profile-title">Candidate Profile</h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="candidate-detail-layout">
          <div class="candidate-detail-photo-panel">
            <img src="" alt="Candidate portrait" data-detail-photo class="candidate-detail-photo">
          </div>

          <div class="candidate-detail-content">
            <p class="eyebrow mb-2" data-detail-position>Position</p>
            <h3 class="candidate-detail-name" data-detail-name>Candidate Name</h3>

            <div class="candidate-detail-meta">
              <span><i class="fa-regular fa-id-card"></i> <strong>Student ID</strong></span>
              <span data-detail-student-id>—</span>
            </div>

            <div class="candidate-manifesto-panel">
              <p class="candidate-detail-label">Manifesto</p>
              <p class="candidate-detail-manifesto" data-detail-manifesto></p>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-amsa btn-amsa-primary" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
