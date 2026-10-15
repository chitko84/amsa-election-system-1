<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Voter Sign In - AMSA AIU Election 2026</title>

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

<body
  class="amsa-radial"
  data-root=".."
  data-page="login"
>

<main class="auth-page">

  <div class="auth-wrap">

    <a href="../index.php" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      Back to election home
    </a>

    <div class="auth-card">

      <div class="auth-head">

        <img
          src="../assets/amsa-logo.png"
          class="amsa-logo-sm flex-shrink-0"
          alt="AMSA logo"
        >

        <div>

          <h1 class="h2 fw-semibold mb-1">
            Voter sign in
          </h1>

          <p class="small text-white-50 mb-0 lh-lg">
            Enter the credentials issued by the AMSA team.
          </p>

        </div>

      </div>

      <form
        class="auth-body d-grid gap-4"
        id="voter-login-form"
        action="#"
        method="post"
        novalidate
      >

        <div>

          <label
            class="form-label"
            for="student-id"
          >
            Student ID
          </label>

          <div class="input-icon-wrap">

            <i class="fa-solid fa-id-card"></i>

            <input
              class="form-control"
              id="student-id"
              name="student_id"
              autocomplete="username"
              placeholder="AIU24102160"
              required
            >

          </div>

        </div>

        <div>

          <label
            class="form-label"
            for="voting-pin"
          >
            Voting PIN
          </label>

          <div class="input-icon-wrap">

            <i class="fa-solid fa-key"></i>

            <input
              class="form-control pe-5"
              id="voting-pin"
              name="voting_pin"
              type="password"
              inputmode="numeric"
              maxlength="6"
              autocomplete="one-time-code"
              placeholder="••••••"
              required
            >

            <button
              type="button"
              class="input-action"
              data-toggle-pin="#voting-pin"
              aria-label="Show or hide PIN"
            >
              <i class="fa-solid fa-eye"></i>
            </button>

          </div>

        </div>

        <div
          id="login-error"
          class="notice-danger d-none"
          role="alert"
        ></div>

        <button
          class="btn-amsa btn-amsa-primary btn-lg-amsa w-100"
          type="submit"
        >
          Sign In to Vote
          <i class="fa-solid fa-arrow-right"></i>
        </button>

        <div class="auth-link-row">
  <a href="forgot-password.php">
    <i class="fa-regular fa-circle-question"></i>
    Forgot Voting PIN?
  </a>
</div>

        <div class="notice-paper small text-ink-muted lh-lg">

          <p class="mb-2">
            Your Student ID and Voting PIN are provided by the
            AMSA team at the attendance counter.
          </p>

          <p class="fw-bold text-maroon mb-0">
            Need assistance? Please visit the AMSA Help Counter.
          </p>

        </div>

        <div class="border-top pt-4 text-center">

          <p
            class="small fw-bold text-uppercase text-maroon opacity-50"
            style="letter-spacing:.16em"
          >
            Election administration
          </p>

          <a
            href="../admin/admin-login.php"
            class="btn-amsa btn-amsa-gold w-100"
          >
            <i class="fa-solid fa-shield-halved"></i>
            Continue as Admin
          </a>

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
