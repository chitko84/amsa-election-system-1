<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';

if (!empty($_SESSION['voter_id'])) {
    $statement=db()->prepare('SELECT pin_version FROM voters WHERE id=? AND active=1');
    $statement->execute([$_SESSION['voter_id']]);
    $version=$statement->fetchColumn();
    if ($version!==false && (int)$version===(int)($_SESSION['voter_pin_version']??-1)) {
        header('Location: member-dashboard.php', true, 302);
        exit;
    }
    unset($_SESSION['voter_id'], $_SESSION['voter_pin_version']);
}

header('Location: login.php', true, 302);
exit;
