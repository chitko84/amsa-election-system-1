<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Forgot Voting PIN - AMSA AIU Election 2026</title>

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    rel="stylesheet"
  >

  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body class="amsa-radial" data-root=".." data-page="forgot-password">

<main class="auth-page">

  <div class="auth-wrap">

    <a href="login.php" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      Back to sign in
    </a>

    <div class="auth-card">

      <div class="auth-head">

        <img
          src="../assets/amsa-logo.png"
          class="amsa-logo-sm"
          alt="AMSA logo"
        >

        <div>

          <h1 class="h2 fw-semibold mb-1">
            Forgot Voting PIN
          </h1>

          <p class="small text-white-50 mb-0 lh-lg">
            Request assistance for voter access.
          </p>

        </div>

      </div>

      <form
        class="auth-body d-grid gap-4"
        id="forgot-pin-form"
        action="#"
        method="post"
      >

        <div>

          <label
            class="form-label"
            for="forgot-student-id"
          >
            Student ID
          </label>

          <div class="input-icon-wrap">

            <i class="fa-solid fa-id-card"></i>

            <input
              class="form-control"
              id="forgot-student-id"
              name="student_id"
              placeholder="AIU24102160"
              required
            >

          </div>

        </div>

        <button
          class="btn-amsa btn-amsa-primary btn-lg-amsa w-100"
          type="submit"
        >
          Request Assistance
        </button>

        <div id="forgot-pin-feedback" class="notice-paper d-none" role="status" aria-live="polite"></div>

        <div class="notice-paper small text-ink-muted lh-lg">
          For election-day security, PIN recovery should be
          verified by the AMSA Help Counter before a new PIN
          is issued.
        </div>

      </form>

    </div>

  </div>

</main>

<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
