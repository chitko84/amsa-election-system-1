<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Candidate Management - AMSA AIU Election 2026</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="candidate-management">

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

      <div class="mx-auto" style="max-width:1150px">

        <div class="page-heading-wrap">

          <div>

            <p class="eyebrow mb-2">
              Administration
            </p>

            <h1 class="page-title">
              Candidate Management
            </h1>

            <p class="page-description">
              Maintain candidate information for each of the
              five election positions.
            </p>

          </div>

          <button
            class="btn-amsa btn-amsa-primary"
            data-bs-toggle="modal"
            data-bs-target="#candidateModal"
          >
            <i class="fa-solid fa-plus"></i>
            Add Candidate
          </button>

        </div>

        <div id="candidate-management-list"></div>

      </div>

    </main>

  </div>

</div>

<div
  class="modal fade"
  id="candidateModal"
  tabindex="-1"
>

  <div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

      <form
        action="#"
        method="post"
        enctype="multipart/form-data"
      >

        <input
          type="hidden"
          name="candidate_id"
          id="candidate-id"
        >

        <input
          type="hidden"
          name="current_photo"
          id="candidate-current-photo"
        >

        <div class="modal-header">

          <div>

            <h2 class="h5 fw-bold text-maroon mb-1" id="candidate-modal-title">
              Add Candidate
            </h2>

            <p class="small text-secondary mb-0">
              Add or update a candidate record.
            </p>

          </div>

          <button
            class="btn-close"
            data-bs-dismiss="modal"
          ></button>

        </div>

        <div class="modal-body d-grid gap-3">

          <div>

            <label
              class="form-label"
              for="candidate-name"
            >
              Candidate Name
            </label>

            <input
              class="form-control"
              id="candidate-name"
              name="name"
              required
            >

          </div>

          <div>

            <label
              class="form-label"
              for="candidate-student-id"
            >
              Student ID
            </label>

            <input
              class="form-control"
              id="candidate-student-id"
              name="student_id"
              required
            >

          </div>

          <div>

            <label
              class="form-label"
              for="candidate-position"
            >
              Position
            </label>

            <select
              class="form-select"
              id="candidate-position"
              name="position"
              required
            >

              <option>President</option>
              <option>Vice President</option>
              <option>Secretary</option>
              <option>Male Treasurer</option>
              <option>Female Treasurer</option>

            </select>

          </div>

          <div>

            <label
              class="form-label"
              for="candidate-photo"
            >
              Candidate Photo
            </label>

            <input
              class="form-control"
              id="candidate-photo"
              name="photo"
              type="file"
              accept="image/png,image/jpeg,image/webp"
            >

            <div class="form-text">
              JPG, PNG, or WebP up to 5 MB. Recommended: portrait image, 4:5 ratio.
            </div>

            <div class="candidate-photo-editor mt-3">
              <img id="candidate-photo-preview" src="../assets/candidates/candidate-a.svg" alt="Candidate photo preview">
              <div>
                <button type="button" class="btn-amsa btn-amsa-outline btn-sm-amsa" id="candidate-remove-photo">Remove photo</button>
                <p class="small text-secondary mb-0 mt-2" id="candidate-photo-state">A default avatar is used when no photo is saved.</p>
              </div>
            </div>

          </div>

          <div>

            <label
              class="form-label"
              for="candidate-manifesto"
            >
              Manifesto
            </label>

            <textarea
              class="form-control"
              id="candidate-manifesto"
              name="manifesto"
              rows="4"
              required
            ></textarea>

          </div>

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
            Save Candidate
          </button>

          <div id="candidate-form-error" class="notice-danger d-none w-100" role="alert"></div>

        </div>

      </form>

    </div>

  </div>

</div>

<div class="modal fade candidate-profile-modal" id="candidateDetailModal" tabindex="-1" aria-labelledby="candidate-detail-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <p class="eyebrow mb-1">Candidate Profile</p>
          <h2 class="h5 fw-bold text-maroon mb-0" id="candidate-detail-title">Candidate Details</h2>
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
        <button type="button" class="btn-amsa btn-amsa-outline" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn-amsa btn-amsa-primary" data-detail-edit>
          <i class="fa-regular fa-pen-to-square"></i>
          Edit Candidate
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
