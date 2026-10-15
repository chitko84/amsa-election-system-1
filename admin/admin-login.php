<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Sign In - AMSA AIU Election 2026</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="amsa-radial" data-root=".." data-page="admin-login">
  <main class="auth-page">
    <div class="auth-wrap">
      <a class="back-link" href="../user/login.php"><i class="fa-solid fa-arrow-left"></i> Back to voter sign in</a>
      <div class="auth-card">
        <div class="auth-head">
          <img src="../assets/amsa-logo.png" class="amsa-logo-sm flex-shrink-0" alt="AMSA logo">
          <div>
            <h1 class="h2 fw-semibold mb-1">Administrator sign in</h1>
            <p class="small text-white-50 mb-0 lh-lg">Sign in to the secure election administration portal.</p>
          </div>
        </div>
        <form id="admin-login-form" class="auth-body d-grid gap-4" action="#" method="post">
          <div><label for="admin-username" class="form-label">Username</label>
            <input id="admin-username" name="username" class="form-control" autocomplete="username" required></div>
          <div><label for="admin-password" class="form-label">Password</label>
            <input id="admin-password" name="password" type="password" class="form-control" autocomplete="current-password" required></div>
          <div id="admin-login-error" class="notice-danger d-none" role="alert"></div>
          <button class="btn-amsa btn-amsa-primary btn-lg-amsa w-100" type="submit">Sign In to Admin Panel <i class="fa-solid fa-arrow-right"></i></button>
          <p class="small text-secondary mb-0">Administrator accounts are created securely using the command-line setup tool.</p>
        </form>
      </div>
    </div>
  </main>

<script src="../js/shell.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
