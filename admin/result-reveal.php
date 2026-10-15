<?php require dirname(__DIR__).'/app/page.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>Result Reveal - AMSA AIU Election 2026</title>

  <!-- Bootstrap 5 -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    rel="stylesheet"
  >

  <!-- Main Stylesheet -->
  <link href="../css/style.css" rel="stylesheet">

  <!-- Result Reveal Theme Override -->
  <style>
    .reveal-screen .reveal-inner,
    .reveal-screen .reveal-header,
    .reveal-screen .reveal-main {
      background-color: transparent;
    }
  </style>
<meta name="csrf-token" content="<?= htmlspecialchars(csrf(), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body data-root=".." data-page="result-reveal">

<div class="reveal-screen">

  <div class="amsa-grid"></div>

  <div class="reveal-inner">

    <!-- ==============================
         REVEAL HEADER
    =============================== -->
    <header class="reveal-header">

      <img
        src="../assets/amsa-logo.png"
        class="amsa-logo-sm"
        alt="AMSA logo"
      >

      <div class="d-flex align-items-center gap-2">

        <!-- Exit Reveal -->
        <a
          href="admin-results.php"
          class="btn-amsa btn-amsa-outline"
          style="
            border-color:rgba(255,255,255,.2);
            background:transparent;
            color:#fff
          "
        >
          <i class="fa-solid fa-arrow-left"></i>
          Exit Reveal
        </a>

        <!-- Fullscreen -->
        <button
          type="button"
          class="btn-amsa btn-amsa-outline hide-mobile"
          style="
            border-color:rgba(255,255,255,.2);
            background:transparent;
            color:#fff
          "
          data-fullscreen-toggle
          aria-label="Fullscreen presentation"
        >
          <i class="fa-solid fa-expand"></i>
          <span data-fullscreen-label>Fullscreen</span>
        </button>

      </div>

    </header>

    <!-- ==============================
         REVEAL CONTENT
    =============================== -->
    <main class="reveal-main">

      <div
        id="reveal-content"
        class="w-100"
      ></div>

    </main>

  </div>

</div>

<!-- Bootstrap JS -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<!-- Main Application JS -->
<script src="../js/shell.js"></script>
<script src="../js/result-reveal.js"></script>
<script src="../js/production.js"></script>
</body>
</html>
