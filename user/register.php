<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Voter Registration - AMSA AIU Election 2026</title>

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

<body class="amsa-radial" data-root="..">

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
          class="amsa-logo-sm"
          alt="AMSA logo"
        >

        <div>

          <h1 class="h2 fw-semibold mb-1">
            Voter registration
          </h1>

          <p class="small text-white-50 mb-0 lh-lg">
            Accounts are created at the on-site attendance counter only.
          </p>

        </div>

      </div>

      <div class="auth-body d-grid gap-4">
        <div class="notice-paper small text-ink-muted lh-lg">
          <h2 class="h5 fw-bold text-maroon mb-3">Registration happens in person</h2>
          <p>1. Go to the attendance counter during the physical election event.</p>
          <p>2. After attendance is confirmed, AMSA staff create your account using your name and Student ID.</p>
          <p>3. Staff give you a randomly generated six-digit Voting PIN directly.</p>
          <p class="mb-0">Use that PIN to log in and cast your ballot. There is no public self-registration.</p>
        </div>
        <a class="btn-amsa btn-amsa-primary btn-lg-amsa w-100" href="login.php">Go to Voter Sign In</a>
      </div>

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
