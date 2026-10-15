<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    $statement=db()->prepare('SELECT id FROM admins WHERE id=? AND active=1');
    $statement->execute([$_SESSION['admin_id']]);
    if ($statement->fetchColumn()) {
        header('Location: admin-dashboard.php', true, 302);
        exit;
    }
    unset($_SESSION['admin_id']);
}

header('Location: admin-login.php', true, 302);
exit;
