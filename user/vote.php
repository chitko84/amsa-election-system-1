<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Cast Your Vote - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="vote">

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

      <div
        id="vote-main"
        class="mx-auto"
        style="max-width:1150px"
      >

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">
              Voter Ballot
            </p>

            <h1 class="page-title">
              Cast your vote
            </h1>

            <p class="page-description">
              Select one candidate for each of the five positions.
              All positions must be completed before the ballot
              can be reviewed.
            </p>

          </div>

        </div>

        <div class="row g-3 align-items-center mb-4">

          <div class="col-lg">

            <div class="notice-gold d-flex gap-3">

              <i class="fa-solid fa-triangle-exclamation mt-1 text-maroon"></i>

              <div>

                <p class="small fw-bold text-maroon mb-1">
                  Final submission cannot be changed
                </p>

                <p class="small text-ink-muted mb-0">
                  You will review all five selections before
                  the final confirmation.
                </p>

              </div>

            </div>

          </div>

          <div class="col-lg-auto">

            <div
              class="amsa-card p-3"
              style="min-width:208px"
            >

              <div class="d-flex justify-content-between small">

                <span class="text-secondary">
                  Ballot progress
                </span>

                <strong
                  id="ballot-progress-value"
                  class="text-maroon"
                >
                  0 of 5
                </strong>

              </div>

              <div class="progress-amsa mt-2">

                <div
                  id="ballot-progress-bar"
                  class="bar"
                  style="width:0"
                ></div>

              </div>

            </div>

          </div>

        </div>

        <form
          id="ballot-form"
          action="#"
          method="post"
        >

          <div id="vote-sections"></div>

          <div class="ballot-sticky">

            <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-center justify-content-between">

              <div class="d-flex align-items-center gap-3">

                <i class="fa-solid fa-check-to-slot text-maroon"></i>

                <div>

                  <p
                    id="ballot-remaining"
                    class="small fw-bold text-maroon mb-1"
                  >
                    5 positions remaining
                  </p>

                  <p class="small text-secondary mb-0">
                    Review is required before final submission.
                  </p>

                </div>

              </div>

              <button
                id="review-ballot-btn"
                type="button"
                class="btn-amsa btn-amsa-primary btn-lg-amsa"
                data-bs-toggle="modal"
                data-bs-target="#reviewModal"
                disabled
              >
                Review Ballot
              </button>

            </div>

          </div>

        </form>

      </div>

      <div
        id="vote-success"
        class="d-none mx-auto py-5"
        style="max-width:760px"
      >

        <div class="amsa-card text-center p-5">

          <div
            class="mx-auto d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
            style="width:64px;height:64px"
          >
            <i class="fa-regular fa-circle-check fs-2"></i>
          </div>

          <p class="eyebrow text-success mt-4">
            Ballot Submitted
          </p>

          <h1 class="h2 fw-semibold text-maroon">
            Thank you for participating
          </h1>

          <p
            class="text-ink-muted lh-lg mx-auto"
            style="max-width:540px"
          >
            Your anonymous ballot was recorded and your voter account is marked VOTED. You cannot submit another ballot in this round.
          </p>

          <a
            class="btn-amsa btn-amsa-primary mt-3"
            href="login.php"
          >
            Sign Out
          </a>

        </div>

      </div>

    </main>

  </div>

</div>

<div
  class="modal fade"
  id="reviewModal"
  tabindex="-1"
  aria-hidden="true"
>

  <div class="modal-dialog modal-dialog-centered modal-lg">

    <div class="modal-content">

      <div class="modal-header">

        <div>

          <h2 class="h4 fw-semibold text-maroon mb-1">
            Review Your Ballot
          </h2>

          <p class="small text-secondary mb-0">
            Check every selection carefully before final submission.
          </p>

        </div>

        <button
          class="btn-close"
          data-bs-dismiss="modal"
        ></button>

      </div>

      <div class="modal-body">

        <div
          id="review-list"
          class="border rounded-4 overflow-hidden"
        ></div>

        <div class="notice-gold mt-3 small fw-bold text-maroon">
          Once submitted, your ballot cannot be changed.
        </div>

      </div>

      <div class="modal-footer">

        <button
          class="btn-amsa btn-amsa-outline"
          data-bs-dismiss="modal"
        >
          <i class="fa-solid fa-arrow-left"></i>
          Back to Ballot
        </button>

        <button
          class="btn-amsa btn-amsa-primary"
          id="submit-final-ballot"
        >
          Submit Final Ballot
        </button>

      </div>

    </div>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
