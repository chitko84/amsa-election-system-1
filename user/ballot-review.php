<?php require dirname(__DIR__).'/app/page.php'; ?>
<!DOCTYPE html>

<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
<meta charset="utf-8"/>
<meta content="width=device-width,initial-scale=1" name="viewport"/>
<title>Ballot Review - AMSA AIU Election 2026</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet"/>
<link href="../css/style.css" rel="stylesheet"/>
<meta content="&lt;?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?&gt;" name="csrf-token"/>
</head>
<body data-page="vote" data-root="..">
<div class="sidebar-overlay" aria-hidden="true"></div>
<div class="layout-shell">
<aside class="sidebar" id="primary-sidebar" aria-hidden="true">
<div class="sidebar-brand">
<img alt="AMSA logo" class="amsa-logo-sm" src="../assets/amsa-logo.png"/>
</div>
<div class="sidebar-label">
      Voter Panel
    </div>
<nav class="sidebar-nav">
<a class="sidebar-link" href="member-dashboard.php">
<i class="fa-solid fa-house"></i>
        Dashboard
      </a>
<a class="sidebar-link active" href="vote.php">
<i class="fa-solid fa-check-to-slot"></i>
        Vote
      </a>
<a class="sidebar-link" href="candidates.php">
<i class="fa-regular fa-user"></i>
        Candidates
      </a>
<a class="sidebar-link" href="member-results.php">
<i class="fa-solid fa-chart-column"></i>
        Results
      </a>
</nav>
<div class="sidebar-footer">
<a class="sidebar-link" data-demo-signout="" href="login.php">
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
<div class="mx-auto" style="max-width:850px">
<div class="page-heading-wrap">
<div>
<p class="eyebrow mb-2">Voter Ballot</p>
<h1 class="page-title">
              Review Your Ballot
            </h1>
<p class="page-description">
              Check every selection carefully before final submission.
            </p>
</div>
</div>
<form action="#" id="ballot-review-form" method="post"><div class="notice-paper mb-4">For ballot privacy, review your selections in the Vote page before final submission.</div><a class="btn-amsa btn-amsa-primary" href="vote.php">Return to ballot</a></form>
</div>
</main>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
